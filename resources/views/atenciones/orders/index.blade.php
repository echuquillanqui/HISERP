@extends('layouts.app')

@section('content')
<div class="container" x-data="{ filterDate: '{{ date('Y-m-d') }}', search: '' }">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h3 class="fw-bold" style="color: var(--azul-clinico)">
                <i class="bi bi-receipt-cutoff me-2"></i>Órdenes de Servicio
            </h3>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="{{ route('orders.create') }}" class="btn btn-primary-custom shadow-sm px-4">
                <i class="bi bi-plus-lg me-1"></i> Nueva Orden
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="p-4 border-bottom bg-light-subtle">
                <div class="row g-3">
                    <div class="col-md-8">
                        <input type="text" x-model="search" class="form-control shadow-sm" placeholder="Buscar por código, DNI o nombre del paciente...">
                    </div>
                    <div class="col-md-4">
                        <input type="date" x-model="filterDate" class="form-control shadow-sm">
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover">
                    <thead class="bg-light text-muted small">
                        <tr>
                            <th class="ps-4">CÓDIGO</th>
                            <th>PACIENTE</th>
                            <th>DNI</th>
                            <th>TOTAL</th>
                            <th>ESTADO</th>
                            <th class="text-center" style="width: 150px;">ACCIONES</th>
                            <th class="text-center" style="width: 200px;">VER SOLICITUDES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        @php
                            $fullName = ($order->patient->first_name ?? '') . ' ' . ($order->patient->last_name ?? '');
                            $dni = $order->patient->dni ?? 'N/A';
                            $dateStr = $order->created_at->format('Y-m-d');
                            // Lógica de permisos
                            $isSuperAdmin = auth()->user()->role === 'superadmin';
                            $canModify = $isSuperAdmin || $order->payment_status !== 'pagado';
                        @endphp
                        <tr x-show="('{{ strtolower($order->code) }} {{ strtolower($fullName) }} {{ $dni }}'.includes(search.toLowerCase())) && (filterDate === '' || '{{ $dateStr }}' === filterDate)">
                            <td class="ps-4 fw-bold text-primary">{{ $order->code }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $fullName }}</div>
                                <div class="small text-muted"><i class="bi bi-person-badge me-1"></i>{{ $order->user->name ?? 'Sistema' }}</div>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $dni }}</span></td>
                            <td class="fw-bold text-dark">S/ {{ number_format($order->total, 2) }}</td>
                            <td>
                                @php
                                    $badgeClass = match($order->payment_status) {
                                        'pagado' => 'bg-success-subtle text-success border-success',
                                        'anulado' => 'bg-danger-subtle text-danger border-danger',
                                        default => 'bg-warning-subtle text-warning border-warning'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} border text-uppercase" style="font-size: 0.7rem; padding: 0.5em 0.8em;">
                                    {{ $order->payment_status ?? 'PENDIENTE' }}
                                </span>
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex justify-content-center gap-2">
                                    {{-- Botón Ver: Siempre disponible --}}
                                    <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-secondary" title="Ver Detalle">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>

                                    @if($canModify)
                                        {{-- Botón Editar: Superadmin o No pagados --}}
                                        <a href="{{ route('orders.edit', $order) }}" class="btn btn-sm btn-outline-primary" title="Editar Orden">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        {{-- Botón Eliminar: Superadmin o No pagados --}}
                                        <form action="{{ route('orders.destroy', $order) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar esta orden? Esta acción no se puede deshacer.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-trash3-fill"></i>
                                            </button>
                                        </form>
                                    @else
                                        {{-- Bloqueado para usuarios normales si ya está pagado --}}
                                        <button class="btn btn-sm btn-light border text-muted" disabled title="Orden pagada (Solo Superadmin puede editar)">
                                            <i class="bi bi-lock-fill"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>

                            <td class="text-center">

                                @if($order->history)
                                    <a href="{{ route('histories.print_history', $order->history->id) }}" 
                                        target="_blank" class="btn btn-sm btn-outline-primary mx-2" title="HISTORIA">
                                            <i class="bi bi-file-earmark-medical"></i> 
                                    </a>
                                
                                    <a href="{{ route('histories.print-prescription', $order->history->id) }}" 
                                        target="_blank" class="btn btn-sm btn-outline-success" title="RECETA">
                                            <i class="bi bi-capsule"></i>
                                    </a>

                                    <a href="{{ route('histories.print', $order->history->id) }}" 
                                        target="_blank" class="btn btn-sm btn-outline-info mx-2" title="LABORATORIO">
                                            <i class="bi bi-droplet"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($orders->count() == 0)
                <div class="text-center py-5">
                    <i class="bi bi-search text-muted display-1"></i>
                    <p class="mt-3 text-muted">No se encontraron órdenes para los criterios seleccionados.</p>
                </div>
            @endif

            @if($orders->hasPages())
            <div class="p-4 border-top">
                {{ $orders->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection