@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary mb-0">Atenciones Médicas</h2>
        <a href="{{ route('patients.index') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-circle me-2"></i>Nueva Atención
        </a>
    </div>

    <div class="card border-0 shadow-sm overflow-visible">
        <div class="card-body p-0">
            <div class="table-responsive" style="min-height: 400px;"> <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small uppercase">
                            <th class="ps-4">Fecha</th>
                            <th>Paciente / DNI</th>
                            <th>Código Orden</th>
                            <th>Médico Tratante</th>
                            <th class="text-center">Documentos (PDF)</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($histories as $history)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold">{{ $history->created_at->format('d/m/Y') }}</span><br>
                                <small class="text-muted">{{ $history->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $history->patient->last_name }}, {{ $history->patient->first_name }}</div>
                                <div class="small text-muted"><i class="bi bi-card-text me-1"></i>{{ $history->patient->dni }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-primary border border-primary-subtle">
                                    {{ $history->order ? $history->order->code : 'SIN ORDEN' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 12px;">
                                        {{ substr($history->user->name, 0, 1) }}
                                    </div>
                                    <span>{{ $history->user->name }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="btn-group shadow-sm" role="group">
                                    
                                    <a href="{{ route('histories.print_history', $history->id) }}" 
                                    target="_blank" class="btn btn-sm btn-outline-primary mx-2">
                                        <i class="bi bi-file-earmark-medical"></i> Historia
                                    </a>
                                    
                                    @if($history->prescription)
                                        <a href="{{ route('histories.print-prescription', $history->id) }}" 
                                        target="_blank" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-capsule"></i> Receta
                                        </a>
                                    @endif

                                    {{-- Cambia 'labs' por el nombre exacto de la relación en tu modelo History --}}
                                    @if($history->labItems)
                                        <a href="{{ route('histories.print', $history->id) }}" 
                                        target="_blank" class="btn btn-sm btn-outline-info mx-2">
                                            <i class="bi bi-droplet"></i> Lab
                                        </a>
                                    @endif

                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown shadow-none">
                                    <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical fs-5"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                        <li><a class="dropdown-item py-2" href="{{ route('histories.edit', $history->id) }}"><i class="bi bi-pencil me-2 text-warning"></i>Editar Historia</a></li>
                                        @if(auth()->user()->role == 'superadmin')
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('histories.destroy', $history->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar esta atención?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-trash me-2"></i>Eliminar</button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No se encontraron atenciones médicas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    /* Asegura que el dropdown sea visible sobre el borde de la tabla */
    .table-responsive {
        overflow: visible !important;
    }
    
    .dropdown-menu {
        z-index: 1050;
    }

    .btn-group .btn {
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge {
        font-weight: 500;
    }
</style>
@endsection