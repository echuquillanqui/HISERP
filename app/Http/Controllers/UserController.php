<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $search = $request->input('search');
            
            $users = User::with('specialty')
                ->where(function($query) use ($search) {
                    $query->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('username', 'LIKE', "%{$search}%")
                          ->orWhere('dni', 'LIKE', "%{$search}%")
                          ->orWhere('email', 'LIKE', "%{$search}%");
                })
                ->latest()
                ->get();

            return response()->json($users);
        }

        return view('admin.users.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $specialties = Specialty::orderBy('name', 'asc')->get();
        return view('admin.users.create', compact('specialties'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'username'     => 'required|string|unique:users,username',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:8',
            'dni'          => 'nullable|string|digits:8|unique:users,dni',
            'role'         => 'required|in:superadmin,administracion,medicina,laboratorio',
            'specialty_id' => 'nullable|exists:specialties,id',
            'colegiatura'  => 'nullable|string|unique:users,colegiatura',
            'rne'          => 'nullable|string|unique:users,rne',
            'firma'        => 'nullable|image|mimes:jpg,jpeg,png|max:1024', // Max 1MB
        ]);

        $data = $request->all();
        $data['password'] = Hash::make($request->password);

        if ($request->hasFile('firma')) {
            $data['firma'] = $request->file('firma')->store('firmas', 'public');
        }

        User::create($data);

        return redirect()->route('users.index')
            ->with('success', 'Usuario registrado correctamente.');
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
    public function edit(User $user)
    {
        $specialties = Specialty::orderBy('name', 'asc')->get();
        return view('admin.users.edit', compact('user', 'specialties'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'username'     => ['required', Rule::unique('users')->ignore($user->id)],
            'email'        => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'dni'          => ['nullable', 'digits:8', Rule::unique('users')->ignore($user->id)],
            'role'         => 'required|in:superadmin,administracion,medicina,laboratorio',
            'specialty_id' => 'nullable|exists:specialties,id',
            'colegiatura'  => ['nullable', Rule::unique('users')->ignore($user->id)],
            'rne'          => ['nullable', Rule::unique('users')->ignore($user->id)],
            'firma'        => 'nullable|image|mimes:jpg,jpeg,png|max:1024',
            'status'       => 'required|boolean', // Validación del estado
        ]);

        $data = $request->except(['password']);

        // Actualizar contraseña solo si se proporciona una nueva
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('firma')) {
            // Eliminar firma anterior si existe
            if ($user->firma) {
                Storage::disk('public')->delete($user->firma);
            }
            $data['firma'] = $request->file('firma')->store('firmas', 'public');
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'Datos de usuario actualizados.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->firma) {
            Storage::disk('public')->delete($user->firma);
        }
        
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuario eliminado del sistema.');
    }
}
