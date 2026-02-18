@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold" style="color: var(--azul-clinico)">Gestión de Sucursales</h4>
        <a href="{{ route('branches.create') }}" class="btn btn-primary-custom">
            <i class="bi bi-plus-circle me-1"></i> Nueva Sucursal
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Logo</th>
                        <th>RUC / Razón Social</th>
                        <th>Contacto</th>
                        <th>Estado</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($branches as $branch)
                    <tr>
                        <td class="ps-4">
                            @if($branch->logo)
                                <img src="{{ asset('storage/' . $branch->logo) }}" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" width="40" height="40">
                                    <i class="bi bi-building text-muted"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $branch->razon_social }}</div>
                            <small class="text-muted">RUC: {{ $branch->ruc }}</small>
                        </td>
                        <td>
                            <div class="small"><i class="bi bi-geo-alt me-1"></i> {{ $branch->direccion }}</div>
                            <div class="small text-muted"><i class="bi bi-telephone me-1"></i> {{ $branch->telefono }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $branch->estado ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} px-3">
                                {{ $branch->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('branches.edit', $branch) }}" class="btn btn-sm btn-outline-primary border-0">
                                <i class="bi bi-pencil-square"></i>
                            </a>

                            <button type="button" class="btn btn-sm btn-white text-danger border" 
                                    title="Eliminar"
                                    onclick="confirmDelete({{ $branch->id }}, '{{ $branch->razon_social }}')">
                                <i class="bi bi-trash3"></i>
                            </button>
                            <form id="delete-form-{{ $branch->id }}" action="{{ route('branches.destroy', $branch) }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

<script>
    function confirmDelete(id, name) {
        // Sonido de advertencia/peligro
        const alertSound = new Audio('https://assets.mixkit.co/active_storage/sfx/2359/2359-preview.mp3');
        alertSound.play();

        Swal.fire({
            title: '¿Eliminar sucursal?',
            text: `Vas a eliminar a "${name}". Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33', // Rojo para peligro
            cancelButtonColor: '#2d406b', // Tu azul profesional para cancelar
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>