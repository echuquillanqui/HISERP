<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Area;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Cargamos las áreas con el conteo de sus relaciones para el Dashboard
        $areas = Area::withCount(['catalogs', 'profiles'])->get();
        return view('labs.areas.index', compact('areas'));
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
        $request->validate(['name' => 'required|unique:areas,name']);
        Area::create($request->all());
        return back()->with('success', 'Área creada correctamente');
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Area $area)
    {
        $request->validate(['name' => 'required|unique:areas,name,' . $area->id]);
        $area->update($request->all());
        return back()->with('success', 'Área actualizada');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getDetails(Area $area)
    {
        // Cargamos el catálogo y los perfiles (incluyendo qué exámenes ya tienen asociados)
        return response()->json(
            $area->load(['catalogs', 'profiles.catalogs'])
        );
    }
}
