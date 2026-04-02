<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use Illuminate\Http\Request;

class AgreementController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $search = trim((string) $request->input('search', ''));
            $perPage = (int) $request->input('per_page', 10);

            $agreements = Agreement::query()
                ->when($search !== '', function ($query) use ($search) {
                    $query->where('description', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                })
                ->latest()
                ->paginate($perPage)
                ->through(function (Agreement $agreement) {
                    return [
                        'id' => $agreement->id,
                        'description' => $agreement->description,
                        'status' => $agreement->status,
                        'can_edit' => auth()->user()?->can('update', $agreement) ?? false,
                        'can_delete' => auth()->user()?->can('delete', $agreement) ?? false,
                    ];
                });

            return response()->json($agreements);
        }

        return view('radiology.agreements.index', [
            'canCreate' => $request->user()->can('create', Agreement::class),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Agreement::class);

        return view('radiology.agreements.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Agreement::class);

        $data = $request->validate([
            'description' => ['required', 'string', 'max:255', 'unique:agreements,description'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);

        Agreement::create($data);

        return redirect()->route('convenios.index')->with('success', 'Convenio registrado correctamente.');
    }

    public function edit(Agreement $convenio)
    {
        $this->authorize('update', $convenio);

        return view('radiology.agreements.edit', ['agreement' => $convenio]);
    }

    public function update(Request $request, Agreement $convenio)
    {
        $this->authorize('update', $convenio);

        $data = $request->validate([
            'description' => ['required', 'string', 'max:255', 'unique:agreements,description,' . $convenio->id],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);

        $convenio->update($data);

        return redirect()->route('convenios.index')->with('success', 'Convenio actualizado correctamente.');
    }

    public function destroy(Agreement $convenio)
    {
        $this->authorize('delete', $convenio);

        if ($convenio->orderTomographies()->exists()) {
            return redirect()->route('convenios.index')->with(
                'error',
                'No se puede eliminar el convenio porque está asociado a una o más tomografías.'
            );
        }

        $convenio->delete();

        return redirect()->route('convenios.index')->with('success', 'Convenio eliminado correctamente.');
    }
}
