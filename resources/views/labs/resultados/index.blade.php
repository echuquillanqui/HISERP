@extends('layouts.app')

@section('content')
<div class="container" x-data="{ 
    filterDate: '{{ $date }}', 
    filterStatus: '{{ $status }}',
    search: '{{ $search }}',
    apply() {
        let url = new URL(window.location.href);
        url.searchParams.set('date', this.filterDate);
        url.searchParams.set('status', this.filterStatus);
        url.searchParams.set('search', this.search);
        window.location.href = url.href;
    }
}">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="small fw-bold text-muted">BUSCAR PACIENTE / CÓDIGO</label>
                    <input type="text" x-model="search" @keyup.enter="apply()" class="form-control" placeholder="DNI o Nombre...">
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold text-muted">FECHA DE ORDEN</label>
                    <input type="date" x-model="filterDate" @change="apply()" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold text-muted">ESTADO DE LABORATORIO</label>
                    <select x-model="filterStatus" @change="apply()" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">PENDIENTE</option>
                        <option value="procesando">PROCESANDO</option>
                        <option value="completado">COMPLETADO</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button @click="apply()" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Filtrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead class="bg-light text-muted small">
                    <tr>
                        <th class="ps-4">CÓDIGO</th>
                        <th>PACIENTE</th>
                        <th>PROGRESO LAB</th>
                        <th class="text-center">ESTADO (MIGRACIÓN)</th>
                        <th class="text-center">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    @php
                        // Lógica para determinar el estado general basado en la tabla lab_results
                        $allResults = $order->details->flatMap->labResults;
                        $total = $allResults->count();
                        $completed = $allResults->where('status', 'completado')->count();
                        $percent = $total > 0 ? ($completed / $total) * 100 : 0;
                    @endphp
                    <tr>
                        <td class="ps-4 fw-bold text-primary">{{ $order->code }}</td>
                        <td>
                            <div class="fw-bold">{{ $order->patient->first_name }} {{ $order->patient->last_name }}</div>
                            <div class="small text-muted">{{ $order->patient->dni }}</div>
                        </td>
                        <td style="width: 200px;">
                            <div class="small text-muted mb-1">{{ $completed }}/{{ $total }} Exámenes</div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: {{ $percent }}%"></div>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($total > 0)
                                @php
                                    // Tomamos el estado del primer resultado como referencia o definimos uno general
                                    $statusLab = $allResults->first()->status; 
                                    $badgeColor = match($statusLab) {
                                        'completado' => 'success',
                                        'pendiente' => 'warning',
                                        default => 'info'
                                    };
                                @endphp
                                <span class="badge bg-{{ $badgeColor }}-subtle text-{{ $badgeColor }} border border-{{ $badgeColor }} text-uppercase">
                                    {{ $statusLab }}
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary">SIN EXAMENES</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="{{ route('lab-results.edit', $order->id) }}" class="btn btn-sm btn-outline-primary shadow-sm mx-1">
                                    <i class="bi bi-pencil-square me-1"></i>
                                </a>
                                
                                <a href="{{ route('lab-results.show', $order->id) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-file-pdf"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection