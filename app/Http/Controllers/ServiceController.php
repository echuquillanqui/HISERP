<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return response()->json(Service::where('nombre', 'like', "%{$request->search}%")->get());
        }
        return view('services.index');
    }

    public function store(Request $request)
    {
        // Validación: 'required' evita campos vacíos
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0'
        ]);

        Service::create($data);
        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0'
        ]);

        Service::findOrFail($id)->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        // 1. Verificamos si existe algún template asociado a este servicio
        $hasTemplates = \App\Models\Template::where('service_id', $id)->exists();

        if ($hasTemplates) {
            return response()->json([
                'success' => false, 
                'message' => 'No se puede eliminar: Este servicio tiene plantillas asignadas.'
            ], 409);
        }

        // 2. Si no tiene templates, procedemos a borrar
        \App\Models\Service::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}