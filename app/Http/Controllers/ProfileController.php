<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profile;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'name' => 'required',
            'price' => 'required|numeric'
        ]);

        Profile::create($request->all());
        return back()->with('success', 'Perfil creado');
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
    public function update(Request $request, Profile $profile)
    {
        $profile->update($request->all());
        return back()->with('success', 'Perfil actualizado');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function sync(Request $request, Profile $profile) 
    {
        // Si usas el toggleExam del script:
        $profile->catalogs()->toggle($request->catalog_id);
        return response()->json(['success' => true]);
    }

    public function toggleExam(Request $request, Profile $profile)
    {
        // toggle() inserta si no existe, o elimina si ya existe en la tabla pivote
        $profile->catalogs()->toggle($request->catalog_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Relación actualizada correctamente'
        ]);
    }
}
