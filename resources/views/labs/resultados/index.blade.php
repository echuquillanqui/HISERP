@extends('layouts.app')

@section('content')
<div class="container" x-data="{ 
    filterDate: '{{ $date }}', 
    filterStatus: '{{ $status }}',
    search: '{{ $search }}',
    activeTab: '{{ $activeTab }}',
    apply(tab = null) {
        if (tab) {
            this.activeTab = tab;
            if (tab === 'historial' && !this.filterDate) {
                this.filterStatus = '';
            }
        }
        let url = new URL(window.location.href);
        url.searchParams.set('tab', this.activeTab);
        if (this.filterDate) {
            url.searchParams.set('date', this.filterDate);
        } else {
            url.searchParams.delete('date');
        }
        url.searchParams.set('status', this.filterStatus);
        url.searchParams.set('search', this.search);
        window.location.href = url.href;
    }
}">

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-2">
            <ul class="nav nav-pills nav-fill gap-2" role="tablist">
                <li class="nav-item" role="presentation">
                    <button type="button"
                            class="nav-link py-3 {{ $activeTab === 'actual' ? 'active' : '' }}"
                            @click="apply('actual')">
                        <i class="bi bi-clipboard-pulse me-2"></i>Control actual
                        <span class="d-block small {{ $activeTab === 'actual' ? 'text-white-50' : 'text-muted' }}">Órdenes con resultados pendientes por rellenar</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button type="button"
                            class="nav-link py-3 {{ $activeTab === 'historial' ? 'active' : '' }}"
                            @click="apply('historial')">
                        <i class="bi bi-clock-history me-2"></i>Historial de exámenes
                        <span class="d-block small {{ $activeTab === 'historial' ? 'text-white-50' : 'text-muted' }}">Órdenes con todos los resultados rellenados</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>

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
                    <div class="form-text" x-show="activeTab === 'historial'">Déjalo vacío para ver todo el historial.</div>
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
                        <th class="text-center">ESTADO</th>
                        <th class="text-center">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
    @forelse($orders as $order)
        @php
            // 1. Obtenemos y filtramos los resultados
            $allResults = $order->details->flatMap->labResults->filter(function($res) {
                $areaNombre = strtoupper($res->catalog->area->name ?? '');
                return !in_array($areaNombre, ['MEDICINA', 'ADICIONALES']);
            });

            $total = $allResults->count();
        @endphp

        {{-- SI NO HAY EXÁMENES VISIBLES, SALTAMOS ESTA ORDEN --}}
        @if($total == 0)
            @continue
        @endif

        @php
            // 2. Solo si hay exámenes, calculamos el resto
            $completed = $allResults->filter(function ($res) {
                return $res->result_value !== null && trim((string) $res->result_value) !== '';
            })->count();
            $percent = ($completed / $total) * 100;

            $statusLab = match (true) {
                $completed === 0 => 'pendiente',
                $completed < $total => 'procesando',
                default => 'completado',
            };

            $badgeColor = match ($statusLab) {
                'completado' => 'success',
                'procesando' => 'info',
                default => 'warning',
            };
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
                <span class="badge bg-{{ $badgeColor }}-subtle text-{{ $badgeColor }} border border-{{ $badgeColor }} text-uppercase">
                    {{ $statusLab }}
                </span>
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
    @empty
        <tr>
            <td colspan="5" class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                No hay órdenes para esta pestaña con los filtros seleccionados.
            </td>
        </tr>
    @endforelse
</tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $orders->links() }}
    </div>
</div>
@endsection
