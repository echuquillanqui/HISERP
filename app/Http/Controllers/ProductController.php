<?php

namespace App\Http\Controllers;

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
        ->when($q, function($query) use ($q) {
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('concentration', 'like', "%{$q}%");
            });
        })
        ->when($status !== null && $status !== '', function($query) use ($status) {
            $query->where('is_active', $status);
        })
        ->select(['id', 'code', 'name', 'concentration', 'presentation', 'stock', 'selling_price', 'is_active'])
        ->latest()
        ->paginate(10); // Cambiamos a paginate

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
            'code'           => 'required|string|max:50|unique:products,code',
            'name'           => 'required|string|max:255',
            'concentration'  => 'nullable|string|max:255',
            'presentation'   => 'nullable|string|max:255',
            'stock'          => 'required|integer|min:0',
            'min_stock'      => 'nullable|integer|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price'  => 'required|numeric|min:0',
            'expiration_date'=> 'nullable|date|after_or_equal:today',
            'is_active'      => 'boolean'
        ]);

        try {
            DB::beginTransaction();
            Product::create($validated);
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
            'code'           => ['required', 'string', 'max:50', Rule::unique('products')->ignore($product->id)],
            'name'           => 'required|string|max:255',
            'concentration'  => 'nullable|string|max:255',
            'presentation'   => 'nullable|string|max:255',
            'stock'          => 'required|integer|min:0',
            'selling_price'  => 'required|numeric|min:0',
            'expiration_date'=> 'nullable|date',
            'is_active'      => 'boolean'
        ]);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('info', 'Producto actualizado correctamente.');
    }

    /**
     * Eliminación lógica o física
     */
    public function destroy(Product $product)
    {
        try {
            // Podrías usar $product->update(['is_active' => false]) para borrado lógico
            $product->delete();
            return redirect()->route('products.index')
                ->with('warning', 'Producto eliminado del sistema.');
        } catch (\Exception $e) {
            return back()->with('error', 'No se puede eliminar un producto con historial de ventas/recetas.');
        }
    }
}