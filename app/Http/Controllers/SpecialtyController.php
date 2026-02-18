<?php

namespace App\Http\Controllers;

use App\Models\Specialty;
use Illuminate\Http\Request;


class SpecialtyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtenemos el término de búsqueda
        $search = $request->input('search');

        $query = Specialty::query();

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $specialties = $query->latest()->get();

        // Si es una petición AJAX (JavaScript fetch)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($specialties);
        }

        return view('admin.specialties.index', compact('specialties'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:specialties,name|max:255']);
        
        Specialty::create($request->all());

        return response()->json(['success' => 'Especialidad creada con éxito.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Specialty $specialty)
    {
        return response()->json($specialty);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Specialty $specialty)
    {
        $request->validate(['name' => 'required|max:255|unique:specialties,name,' . $specialty->id]);
        
        $specialty->update($request->all());

        return response()->json(['success' => 'Especialidad actualizada.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Specialty $specialty)
    {
        $specialty->delete();
        return response()->json(['success' => 'Eliminado correctamente.']);
    }
}
