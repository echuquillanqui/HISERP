<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PatientController extends Controller
{
    /**
     * Muestra el listado de pacientes.
     * Soporta búsqueda asíncrona vía AJAX/JSON para Alpine.js.
     */
    public function index(Request $request)
    {
        $query = Patient::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('dni', 'LIKE', "%{$search}%");
            });
        }

        $patients = $query->latest()->get();

        // Respuesta para Alpine.js (Búsqueda dinámica)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($patients);
        }

        return view('admin.patients.index', compact('patients'));
    }

    /**
     * Muestra el formulario de creación.
     */
    public function create()
    {
        return view('admin.patients.create');
    }

    /**
     * Almacena un nuevo paciente siguiendo los campos de la migración.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dni'        => 'required|string|unique:patients,dni',
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'gender'     => 'nullable|in:M,F,Otro',
            'phone'      => 'nullable|string',
            'email'      => 'nullable|email|max:255',
            'address'    => 'nullable|string',
        ]);

        Patient::create($validated);

        return redirect()->route('patients.index')
            ->with('success', 'Paciente registrado correctamente.');
    }

    /**
     * Muestra el formulario de edición cargando los datos del paciente.
     */
    public function edit($id)
    {
        $patient = Patient::findOrFail($id);
        return view('admin.patients.edit', compact('patient'));
    }

    /**
     * Actualiza los datos del paciente en la base de datos.
     */
    public function update(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);

        $validated = $request->validate([
            'dni'        => 'required|string|unique:patients,dni,' . $patient->id,
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'gender'     => 'nullable|in:M,F,Otro',
            'phone'      => 'nullable|string',
            'email'      => 'nullable|email|max:255',
            'address'    => 'nullable|string',
        ]);

        $patient->update($validated);

        return redirect()->route('patients.index')
            ->with('success', 'Información del paciente actualizada.');
    }

    /**
     * Elimina al paciente del sistema.
     */
    public function destroy($id)
    {
        $patient = Patient::findOrFail($id);
        $patient->delete();

        return redirect()->route('patients.index')
            ->with('success', 'Paciente eliminado del sistema.');
    }
}