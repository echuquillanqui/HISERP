<?php

namespace App\Http\Controllers;

use App\Exports\InventoryMovementsExport;
use App\Exports\SoldMedicinesExport;
use App\Models\Branch;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    /**
     * Mostrar la lista (Vista Interactiva Alpine.js)
     */
    public function index()
    {
        return view('products.index');
    }

    /**
     * API para búsqueda interactiva
     * Seguridad: Limitación de resultados y sanitización de query
     */
    public function search(Request $request)
    {
        $q = trim($request->get('q'));
        $status = $request->get('status');

        $products = Product::query()
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%")
                        ->orWhere('concentration', 'like', "%{$q}%")
                        ->orWhere('presentation', 'like', "%{$q}%");
                });
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('is_active', $status);
            })
            ->select(['id', 'code', 'name', 'concentration', 'presentation', 'stock', 'selling_price', 'is_active'])
            ->latest()
            ->paginate(10);

        return response()->json($products);
    }

    public function create()
    {
        return view('products.create');
    }

    /**
     * Almacenar con validación estricta
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:products,code',
            'name' => 'required|string|max:255',
            'concentration' => 'nullable|string|max:255',
            'presentation' => 'nullable|string|max:255',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'expiration_date' => 'nullable|date|after_or_equal:today',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();
            $product = Product::create($validated);

            if ((int) $product->stock > 0) {
                $this->registerMovement(
                    product: $product,
                    movementType: 'entrada',
                    quantity: (int) $product->stock,
                    source: 'ajuste',
                    orderId: null,
                    orderDetailId: null,
                    unitCost: $product->purchase_price,
                    unitPrice: $product->selling_price,
                    notes: 'Stock inicial al crear medicamento',
                    movementAt: now(),
                    stockBefore: 0
                );
            }

            DB::commit();

            return redirect()->route('products.index')
                ->with('success', 'Medicamento registrado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al guardar el producto.');
        }
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Actualizar con validación de unicidad ignorando el ID actual
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('products')->ignore($product->id)],
            'name' => 'required|string|max:255',
            'concentration' => 'nullable|string|max:255',
            'presentation' => 'nullable|string|max:255',
            'stock' => 'required|integer|min:0',
            'selling_price' => 'required|numeric|min:0',
            'expiration_date' => 'nullable|date',
            'is_active' => 'boolean'
        ]);

        $stockAnterior = (int) $product->stock;
        $stockNuevo = (int) $validated['stock'];

        DB::transaction(function () use ($product, $validated, $stockAnterior, $stockNuevo) {
            $product->update($validated);

            if ($stockAnterior !== $stockNuevo) {
                $diferencia = abs($stockNuevo - $stockAnterior);
                $esEntrada = $stockNuevo > $stockAnterior;

                $this->registerMovement(
                    product: $product,
                    movementType: $esEntrada ? 'entrada' : 'salida',
                    quantity: $diferencia,
                    source: 'ajuste',
                    orderId: null,
                    orderDetailId: null,
                    unitCost: $product->purchase_price,
                    unitPrice: $product->selling_price,
                    notes: 'Ajuste manual desde edición de producto',
                    movementAt: now(),
                    stockBefore: $stockAnterior
                );
            }
        });

        return redirect()->route('products.index')
            ->with('info', 'Producto actualizado correctamente.');
    }

    public function kardex(Request $request)
    {
        return redirect()->route('products.kardex.records', $request->query());
    }

    public function movementRecords(Request $request)
    {
        $filters = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'movement_type' => 'nullable|in:entrada,salida',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        $movements = $this->baseKardexQuery($filters, ['manual', 'ajuste'])
            ->paginate(30)
            ->withQueryString();

        $orders = Order::with('patient:id,first_name,last_name')
            ->latest('id')
            ->limit(150)
            ->get(['id', 'code', 'patient_id', 'created_at']);

        $selectedFilterProduct = null;
        if (!empty($filters['product_id'])) {
            $selectedFilterProduct = Product::query()
                ->select(['id', 'code', 'name', 'concentration', 'presentation'])
                ->find($filters['product_id']);
        }

        $selectedManualProduct = null;
        if (old('product_id')) {
            $selectedManualProduct = Product::query()
                ->select(['id', 'code', 'name', 'concentration', 'presentation', 'stock'])
                ->find((int) old('product_id'));
        }

        return view('products.kardex_records', compact(
            'movements',
            'filters',
            'orders',
            'selectedFilterProduct',
            'selectedManualProduct'
        ));
    }

    public function kardexMovements(Request $request)
    {
        $filters = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'movement_type' => 'nullable|in:entrada,salida',
            'source' => 'nullable|in:manual,ajuste,orden',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        $movements = $this->baseKardexQuery($filters)
            ->paginate(30)
            ->withQueryString();

        $selectedProduct = null;
        if (!empty($filters['product_id'])) {
            $selectedProduct = Product::query()
                ->select(['id', 'code', 'name', 'concentration', 'presentation'])
                ->find($filters['product_id']);
        }

        return view('products.kardex_movements', compact('movements', 'filters', 'selectedProduct'));
    }

    public function soldMedicines(Request $request)
    {
        $filters = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        $salesReport = $this->salesReportQuery($filters)
            ->paginate(30)
            ->withQueryString();

        $selectedProduct = null;
        if (!empty($filters['product_id'])) {
            $selectedProduct = Product::query()
                ->select(['id', 'code', 'name', 'concentration', 'presentation'])
                ->find($filters['product_id']);
        }

        return view('products.kardex_sold', compact('salesReport', 'filters', 'selectedProduct'));
    }

    public function exportKardexPdf(Request $request)
    {
        return $this->exportMovementRecordsPdf($request);
    }

    public function exportMovementRecordsPdf(Request $request)
    {
        $filters = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'movement_type' => 'nullable|in:entrada,salida',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        $movements = $this->baseKardexQuery($filters, ['manual', 'ajuste'])->get();
        $branch = Branch::first();

        $pdf = Pdf::loadView('products.kardex_pdf', [
            'movements' => $movements,
            'filters' => $filters,
            'branch' => $branch,
            'generatedAt' => now(),
            'title' => 'Reporte de registros de movimientos',
        ]);

        return $pdf->download(sprintf('reporte_registros_movimientos_%s.pdf', now()->format('Ymd_His')));
    }

    public function exportKardexExcel(Request $request)
    {
        return $this->exportMovementRecordsExcel($request);
    }

    public function exportMovementRecordsExcel(Request $request)
    {
        $filters = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'movement_type' => 'nullable|in:entrada,salida',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        return Excel::download(
            new InventoryMovementsExport($filters + ['sources' => ['manual', 'ajuste']]),
            sprintf('reporte_registros_movimientos_%s.xlsx', now()->format('Ymd_His'))
        );
    }

    public function exportKardexMovementsPdf(Request $request)
    {
        $filters = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'movement_type' => 'nullable|in:entrada,salida',
            'source' => 'nullable|in:manual,ajuste,orden',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        $movements = $this->baseKardexQuery($filters)->get();
        $branch = Branch::first();

        $pdf = Pdf::loadView('products.kardex_pdf', [
            'movements' => $movements,
            'filters' => $filters,
            'branch' => $branch,
            'generatedAt' => now(),
            'title' => 'Reporte de movimientos del kardex',
        ]);

        return $pdf->download(sprintf('reporte_movimientos_kardex_%s.pdf', now()->format('Ymd_His')));
    }

    public function exportKardexMovementsExcel(Request $request)
    {
        $filters = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'movement_type' => 'nullable|in:entrada,salida',
            'source' => 'nullable|in:manual,ajuste,orden',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        return Excel::download(
            new InventoryMovementsExport($filters),
            sprintf('reporte_movimientos_kardex_%s.xlsx', now()->format('Ymd_His'))
        );
    }

    public function exportSoldMedicinesPdf(Request $request)
    {
        $filters = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        $salesReport = $this->salesReportQuery($filters)->get();
        $branch = Branch::first();

        $pdf = Pdf::loadView('products.kardex_sold_pdf', [
            'salesReport' => $salesReport,
            'filters' => $filters,
            'branch' => $branch,
            'generatedAt' => now(),
        ]);

        return $pdf->download(sprintf('reporte_medicamentos_vendidos_%s.pdf', now()->format('Ymd_His')));
    }

    public function exportSoldMedicinesExcel(Request $request)
    {
        $filters = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        return Excel::download(
            new SoldMedicinesExport($filters),
            sprintf('reporte_medicamentos_vendidos_%s.xlsx', now()->format('Ymd_His'))
        );
    }

    public function storeMovement(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'movement_type' => 'required|in:entrada,salida',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'nullable|numeric|min:0',
            'order_id' => 'nullable|exists:orders,id',
            'notes' => 'nullable|string|max:1000',
            'movement_at' => 'nullable|date',
        ]);

        DB::transaction(function () use ($validated) {
            $product = Product::lockForUpdate()->findOrFail($validated['product_id']);
            $linkedOrderId = !empty($validated['order_id']) ? (int) $validated['order_id'] : null;

            $this->registerMovement(
                product: $product,
                movementType: $validated['movement_type'],
                quantity: (int) $validated['quantity'],
                source: $linkedOrderId ? 'orden' : 'manual',
                orderId: $linkedOrderId,
                orderDetailId: null,
                unitCost: $validated['unit_cost'] ?? null,
                unitPrice: $product->selling_price,
                notes: $validated['notes'] ?? ($linkedOrderId
                    ? 'Movimiento manual asociado a orden para logística'
                    : 'Movimiento manual de almacén'),
                movementAt: !empty($validated['movement_at']) ? $validated['movement_at'] : now(),
                stockBefore: (int) $product->stock
            );
        });

        return redirect()->route('products.kardex.records')->with('success', 'Movimiento de inventario registrado correctamente.');
    }

    /**
     * Eliminación lógica o física
     */
    public function destroy(Product $product)
    {
        try {
            $product->delete();
            return redirect()->route('products.index')
                ->with('warning', 'Producto eliminado del sistema.');
        } catch (\Exception $e) {
            return back()->with('error', 'No se puede eliminar un producto con historial de ventas/recetas.');
        }
    }

    private function baseKardexQuery(array $filters, ?array $sources = null)
    {
        $sources = $sources ?? (!empty($filters['source']) ? [$filters['source']] : null);

        return InventoryMovement::query()
            ->with(['product', 'order.patient'])
            ->when(!empty($filters['product_id']), fn ($q) => $q->where('product_id', $filters['product_id']))
            ->when(!empty($filters['movement_type']), fn ($q) => $q->where('movement_type', $filters['movement_type']))
            ->when(!empty($sources), fn ($q) => $q->whereIn('source', $sources))
            ->when(!empty($filters['from_date']), fn ($q) => $q->whereDate('movement_at', '>=', $filters['from_date']))
            ->when(!empty($filters['to_date']), fn ($q) => $q->whereDate('movement_at', '<=', $filters['to_date']))
            ->orderByDesc('movement_at')
            ->orderByDesc('id');
    }

    private function salesReportQuery(array $filters)
    {
        return InventoryMovement::query()
            ->selectRaw("
                product_id,
                SUM(CASE WHEN movement_type = 'salida' THEN quantity ELSE 0 END) as sold_units,
                SUM(CASE WHEN movement_type = 'salida' THEN quantity * COALESCE(unit_price, 0) ELSE 0 END) as sold_total,
                SUM(CASE WHEN movement_type = 'entrada' THEN quantity ELSE 0 END) as returned_units,
                SUM(CASE WHEN movement_type = 'entrada' THEN quantity * COALESCE(unit_price, 0) ELSE 0 END) as returned_total,
                SUM(CASE WHEN movement_type = 'salida' THEN quantity ELSE -quantity END) as net_units,
                SUM(CASE WHEN movement_type = 'salida' THEN quantity * COALESCE(unit_price, 0) ELSE -(quantity * COALESCE(unit_price, 0)) END) as net_total
            " )
            ->with('product')
            ->where('source', 'orden')
            ->whereIn('movement_type', ['salida', 'entrada'])
            ->when(!empty($filters['product_id']), fn ($q) => $q->where('product_id', $filters['product_id']))
            ->when(!empty($filters['from_date']), fn ($q) => $q->whereDate('movement_at', '>=', $filters['from_date']))
            ->when(!empty($filters['to_date']), fn ($q) => $q->whereDate('movement_at', '<=', $filters['to_date']))
            ->groupBy('product_id')
            ->orderByDesc('net_units');
    }

    private function registerMovement(
        Product $product,
        string $movementType,
        int $quantity,
        string $source,
        ?int $orderId,
        ?int $orderDetailId,
        $unitCost,
        $unitPrice,
        string $notes,
        $movementAt,
        ?int $stockBefore = null
    ): InventoryMovement {
        $stockBefore = $stockBefore ?? (int) $product->stock;

        $stockAfter = $movementType === 'entrada'
            ? $stockBefore + $quantity
            : $stockBefore - $quantity;

        if ($stockAfter < 0) {
            throw new \RuntimeException("Stock insuficiente para {$product->name}. Stock actual: {$stockBefore}, solicitado: {$quantity}.");
        }

        $product->update(['stock' => $stockAfter]);

        return InventoryMovement::create([
            'product_id' => $product->id,
            'order_id' => $orderId,
            'order_detail_id' => $orderDetailId,
            'user_id' => auth()->id(),
            'movement_type' => $movementType,
            'source' => $source,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'unit_cost' => $unitCost,
            'unit_price' => $unitPrice,
            'notes' => $notes,
            'movement_at' => $movementAt,
        ]);
    }
}
