@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-4">
            <h2 class="fw-bold text-primary mb-0">Atenciones Médicas</h2>
            <p class="text-muted small">Visualizando atenciones del día: {{ \Carbon\Carbon::parse(request('date', now()))->format('d/m/Y') }}</p>
        </div>
        
        <div class="col-md-8">
            <form action="{{ route('histories.index') }}" method="GET" class="row g-2 justify-content-md-end">
                <div class="col-sm-5 col-md-4">
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Paciente o DNI..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-sm-4 col-md-3">
                    <input type="date" name="date" class="form-control shadow-sm" 
                           value="{{ request('date', now()->toDateString()) }}">
                </div>

                <div class="col-sm-3 col-md-auto">
                    <button type="submit" class="btn btn-dark shadow-sm w-100">
                        <i class="bi bi-filter me-1"></i> Filtrar
                    </button>
                </div>

                <div class="col-sm-12 col-md-auto">
                    <a href="{{ route('patients.index') }}" class="btn btn-primary shadow-sm w-100">
                        <i class="bi bi-plus-circle me-2"></i>Nueva Atención
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-visible">
        <div class="card-body p-0">
            <div class="table-responsive" style="min-height: 400px;"> 
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small uppercase">
                            <th class="ps-4">Fecha/Hora</th>
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
                                    <span>{{ $history->user->name }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="btn-group shadow-sm" role="group">
                                    <a href="{{ route('histories.print_history', $history->id) }}" 
                                    target="_blank" class="btn btn-sm btn-outline-primary mx-1">
                                        <i class="bi bi-file-earmark-medical"></i> Historia
                                    </a>
                                    
                                    @if($history->prescription)
                                        <a href="{{ route('histories.print-prescription', $history->id) }}" 
                                        target="_blank" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-capsule"></i> Receta
                                        </a>
                                    @endif

                                    @if($history->labItems->count() > 0)
                                        <a href="{{ route('histories.print-lab', $history->id) }}" 
                                        target="_blank" class="btn btn-sm btn-outline-info mx-1">
                                            <i class="bi bi-droplet"></i> Lab
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown shadow-none">
                                    <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical fs-5"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                        <li><a class="dropdown-item py-2" href="{{ route('histories.edit', $history->id) }}"><i class="bi bi-pencil me-2 text-warning"></i>Editar Historia</a></li>
                                        @if(auth()->user()->role == 'superadmin')
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('histories.destroy', $history->id) }}" method="POST" onsubmit="return confirm('¿Está seguro?')">
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
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-clipboard-x fs-1 d-block mb-2"></i>
                                No se encontraron atenciones para los filtros seleccionados.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top">
                {{ $histories->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection