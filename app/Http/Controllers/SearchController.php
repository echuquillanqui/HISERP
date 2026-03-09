<?php

namespace App\Http\Controllers;

use App\Models\Cie10;
use App\Models\Product;
use App\Models\Catalog;
use App\Models\Profile;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function cie10(Request $request)
    {
        $q = $request->get('q');
        
        // Si no hay texto, devolvemos un array vacío para que NO cargue todo
        if (empty($q)) {
            return response()->json([]);
        }

        // Buscamos por código (empezando con) o descripción (en cualquier parte)
        return \App\Models\Cie10::where('codigo', 'like', $q . '%')
            ->orWhere('descripcion', 'like', '%' . $q . '%')
            ->select('id', 'codigo', 'descripcion')
            ->limit(15) // Limitar siempre para velocidad
            ->get();
    }

    public function products(Request $request)
    {
        $q = $request->get('q');
        if (!$q) return response()->json([]);

        return Product::where('name', 'like', "%$q%")
            ->select('id', 'name', 'concentration', 'presentation')
            ->limit(10)
            ->get();
    }

    public function lab(Request $request)
    {
        $q = $request->get('q');
        if (!$q) return response()->json([]);

        // Buscamos en Catalog y Profile según tus migraciones
        $exams = Catalog::where('name', 'like', "%$q%")
            ->select('id', 'name')
            ->limit(10)
            ->get()
            ->map(fn($item) => ['uid' => 'catalog-' . $item->id, 'name' => '[EXAMEN] ' . $item->name]);

        $profiles = Profile::where('name', 'like', "%$q%")
            ->select('id', 'name')
            ->limit(10)
            ->get()
            ->map(fn($item) => ['uid' => 'profile-' . $item->id, 'name' => '[PERFIL] ' . $item->name]);

        return $exams->concat($profiles)->take(10);
    }

    public function quickStoreProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'concentration' => 'nullable|string|max:255',
            'presentation' => 'nullable|string|max:255',
        ]);

        $product = Product::create([
            'code' => 'AUTO-' . Str::upper(Str::random(8)),
            'name' => $validated['name'],
            'concentration' => $validated['concentration'] ?? null,
            'presentation' => $validated['presentation'] ?? null,
            'stock' => 0,
            'min_stock' => 0,
            'purchase_price' => 0,
            'selling_price' => 0,
            'is_active' => true,
        ]);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'concentration' => $product->concentration,
            'presentation' => $product->presentation,
        ], 201);
    }
}