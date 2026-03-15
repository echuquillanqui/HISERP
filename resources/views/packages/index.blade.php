@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Paquetes</h4>
        <a href="{{ route('packages.create') }}" class="btn btn-primary">Nuevo paquete</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Código</th>
                        <th class="text-end">Precio</th>
                        <th class="text-center">Ítems</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packages as $package)
                        <tr>
                            <td class="fw-semibold">{{ $package->name }}</td>
                            <td>{{ $package->code ?: '-' }}</td>
                            <td class="text-end">S/ {{ number_format((float)$package->price, 2) }}</td>
                            <td class="text-center">{{ $package->items->count() }}</td>
                            <td class="text-center">
                                <span class="badge {{ $package->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $package->is_active ? 'Activo' : 'Inactivo' }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('packages.edit', $package) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                <form action="{{ route('packages.destroy', $package) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar paquete?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No hay paquetes registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $packages->links() }}</div>
</div>
@endsection
