@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-5">
            <h2 class="fw-bold text-primary mb-0">Gestión de Resultados Médicos</h2>
            <p class="text-muted small">
                Atenciones de servicios (Ecografías, Rayos X) del día: 
                {{ \Carbon\Carbon::parse(request('date', now()))->format('d/m/Y') }}
            </p>
        </div>
        
        <div class="col-md-7">
            <form action="{{ route('serviceresults.index') }}" method="GET" class="row g-2 justify-content-md-end">
                <div class="col-sm-5 col-md-5">
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
                        <i class="bi bi-filter"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small uppercase">
                            <th class="ps-4">Paciente</th>
                            <th>Servicio / Examen</th>
                            <th>Código</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($details as $detail)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">
                                    {{ $detail->order->patient->last_name }}, {{ $detail->order->patient->first_name }}
                                </div>
                                <small class="text-muted"><i class="bi bi-person-badge"></i> {{ $detail->order->patient->dni }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $detail->name }}</span>
                            </td>
                            <td>
                                <span class="text-primary fw-bold font-monospace">{{ $detail->order->code }}</span>
                            </td>
                            <td class="text-center">
                                @if($detail->reportService)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-check-circle-fill me-1"></i>Finalizado
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                        <i class="bi bi-clock-fill me-1"></i>Pendiente
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
    @if($detail->reportService)
        <a href="{{ route('services.imprimir', $detail->reportService->id) }}" target="_blank" class="btn btn-sm btn-outline-info">
            <i class="bi bi-printer"></i>
        </a>
        <a href="{{ route('services.atender', $detail->id) }}" class="btn btn-sm btn-outline-warning">
            <i class="bi bi-pencil"></i> Editar
        </a>
    @else
        <a href="{{ route('services.atender', $detail->id) }}" class="btn btn-sm btn-primary">
            <i class="bi bi-pencil-square"></i> Redactar
        </a>
    @endif
</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-clipboard-x fs-1 d-block mb-2"></i>
                                No se encontraron servicios para la fecha seleccionada.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top">
                {{ $details->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection