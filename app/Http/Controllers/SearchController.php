<?php

namespace App\Http\Controllers;

use App\Models\Cie10;
use App\Models\Product;
use App\Models\Catalog;
use App\Models\Profile;
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
            ->select('id', 'name', 'presentation')
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
}