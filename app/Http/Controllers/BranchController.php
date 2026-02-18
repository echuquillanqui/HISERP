<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use Illuminate\Support\Facades\Storage;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branches = Branch::all();
        return view('branches.index', compact('branches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('branches.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'ruc' => 'required|unique:branches,ruc|max:11',
            'razon_social' => 'required|string|max:255',
            'direccion' => 'required|string',
            'correo' => 'nullable|email',
            'telefono' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'estado'       => 'required|boolean', // Agregado
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        Branch::create($data);

        return redirect()->route('branches.index')->with('success', 'Sucursal creada con éxito');
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
    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'ruc' => 'required|max:11|unique:branches,ruc,' . $branch->id,
            'razon_social' => 'required|string|max:255',
            'direccion' => 'required|string',
            'correo' => 'nullable|email',
            'telefono' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'estado'       => 'required|boolean', // Agregado
        ]);

        if ($request->hasFile('logo')) {
            if ($branch->logo) { Storage::disk('public')->delete($branch->logo); }
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $branch->update($data);

        return redirect()->route('branches.index')->with('success', 'Sucursal actualizada');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch)
    {
        try {
            // 1. Eliminar el logo del disco si existe
            if ($branch->logo) {
                Storage::disk('public')->delete($branch->logo);
            }

            // 2. Eliminar el registro de la base de datos
            $branch->delete();

            return redirect()->route('branches.index')->with('success', 'Sucursal eliminada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('branches.index')->with('error', 'No se puede eliminar la sucursal porque tiene registros asociados.');
        }
    }
}
