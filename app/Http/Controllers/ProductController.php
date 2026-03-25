<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
                        ->orWhere('concentration', 'like', "%{$q}%");
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
        $filters = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        $query = InventoryMovement::query()
            ->with(['product', 'order.patient'])
            ->orderByDesc('movement_at')
            ->orderByDesc('id');

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('movement_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('movement_at', '<=', $filters['to_date']);
        }

        $movements = $query->paginate(30)->withQueryString();

        $salesReport = InventoryMovement::query()
            ->selectRaw('product_id, SUM(quantity) as sold_units, SUM(quantity * COALESCE(unit_price, 0)) as sold_total')
            ->with('product')
            ->where('source', 'orden')
            ->where('movement_type', 'salida')
            ->when(!empty($filters['product_id']), fn ($q) => $q->where('product_id', $filters['product_id']))
            ->when(!empty($filters['from_date']), fn ($q) => $q->whereDate('movement_at', '>=', $filters['from_date']))
            ->when(!empty($filters['to_date']), fn ($q) => $q->whereDate('movement_at', '<=', $filters['to_date']))
            ->groupBy('product_id')
            ->orderByDesc('sold_units')
            ->get();

        $products = Product::orderBy('name')->get(['id', 'name', 'code', 'stock']);

        return view('products.kardex', compact('movements', 'products', 'salesReport', 'filters'));
    }

    public function storeMovement(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'movement_type' => 'required|in:entrada,salida',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'movement_at' => 'nullable|date',
        ]);

        DB::transaction(function () use ($validated) {
            $product = Product::lockForUpdate()->findOrFail($validated['product_id']);

            $this->registerMovement(
                product: $product,
                movementType: $validated['movement_type'],
                quantity: (int) $validated['quantity'],
                source: 'manual',
                orderId: null,
                orderDetailId: null,
                unitCost: $validated['unit_cost'] ?? null,
                unitPrice: $product->selling_price,
                notes: $validated['notes'] ?? 'Movimiento manual de almacén',
                movementAt: !empty($validated['movement_at']) ? $validated['movement_at'] : now(),
                stockBefore: (int) $product->stock
            );
        });

        return redirect()->route('products.kardex')->with('success', 'Movimiento de inventario registrado correctamente.');
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
