@extends('layouts.app')

@section('content')
<div class="container" x-data="{ 
    filterDate: '{{ $date }}', 
    filterStatus: '{{ $status }}',
    search: '{{ $search }}',
    apply() {
        const url = new URL(window.location.href);
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
                    <label class="small fw-bold text-muted">ESTADO RESULTADO</label>
                    <select x-model="filterStatus" @change="apply()" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">PENDIENTE</option>
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
                        <th>ESTUDIOS</th>
                        <th class="text-center">PLACAS (RESULTADO)</th>
                        <th class="text-center">ESTADO</th>
                        <th class="text-center">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($orders as $order)
                    @php
                        $isCompleted = $order->result !== null;
                    @endphp
                    <tr>
                        <td class="ps-4 fw-bold text-primary">{{ $order->code }}</td>
                        <td>
                            <div class="fw-bold">{{ $order->patient->first_name }} {{ $order->patient->last_name }}</div>
                            <div class="small text-muted">{{ $order->patient->dni }}</div>
                        </td>
                        <td>{{ $order->items->pluck('radiography.description')->filter()->join(', ') ?: '-' }}</td>
                        <td class="text-center fw-semibold">{{ $order->result->plates_used ?? 0 }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $isCompleted ? 'success' : 'warning' }}-subtle text-{{ $isCompleted ? 'success' : 'warning' }} border border-{{ $isCompleted ? 'success' : 'warning' }} text-uppercase">
                                {{ $isCompleted ? 'completado' : 'pendiente' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('tomography-results.edit', $order) }}" class="btn btn-sm btn-outline-primary shadow-sm mx-1">
                                <i class="bi bi-pencil-square me-1"></i>
                            </a>
                            @if($isCompleted)
                                <a href="{{ route('tomography-results.show', $order) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-file-pdf"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No hay órdenes para el filtro seleccionado.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
