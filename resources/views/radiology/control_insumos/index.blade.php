@extends('layouts.app')

@section('title', 'Control de Insumos')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h3 class="fw-bold text-primary mb-1"><i class="bi bi-boxes me-2"></i>Control de Insumos</h3>
            <p class="text-muted mb-0">Monitorea stock, productos críticos y últimos movimientos del almacén.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-capsule me-1"></i> Ver productos
            </a>
            <a href="{{ route('products.kardex.records') }}" class="btn btn-primary">
                <i class="bi bi-journal-check me-1"></i> Ir a Kardex
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted d-block">Total de insumos</small>
                    <h4 class="mb-0">{{ number_format($kpis['total']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted d-block">Stock bajo</small>
                    <h4 class="mb-0 text-warning">{{ number_format($kpis['low']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted d-block">Sin stock</small>
                    <h4 class="mb-0 text-danger">{{ number_format($kpis['out']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted d-block">Movimientos (7 días)</small>
                    <h4 class="mb-0 text-info">{{ number_format($kpis['recentMovements']) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ url('/control-insumos') }}" class="row g-2 align-items-end mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Buscar insumo</label>
                            <input type="text" name="q" class="form-control" value="{{ $filters['q'] ?? '' }}" placeholder="Código, nombre o presentación">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado de stock</label>
                            <select name="status" class="form-select">
                                <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>Todos</option>
                                <option value="low" @selected(($filters['status'] ?? 'all') === 'low')>Stock bajo</option>
                                <option value="out" @selected(($filters['status'] ?? 'all') === 'out')>Sin stock</option>
                                <option value="ok" @selected(($filters['status'] ?? 'all') === 'ok')>Stock normal</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Filtrar</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Insumo</th>
                                    <th class="text-end">Stock</th>
                                    <th class="text-end">Stock mín.</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                    @php
                                        $isOut = (int) $product->stock <= 0;
                                        $isLow = !$isOut && (int) $product->stock <= (int) $product->min_stock;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $product->name }}</div>
                                            <small class="text-muted">{{ $product->code }} · {{ $product->concentration }} {{ $product->presentation }}</small>
                                        </td>
                                        <td class="text-end">{{ $product->stock }}</td>
                                        <td class="text-end">{{ $product->min_stock }}</td>
                                        <td>
                                            @if($isOut)
                                                <span class="badge bg-danger">Sin stock</span>
                                            @elseif($isLow)
                                                <span class="badge bg-warning text-dark">Stock bajo</span>
                                            @else
                                                <span class="badge bg-success">Normal</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No se encontraron insumos.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $products->links() }}
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="bi bi-plus-circle me-1"></i>Registrar movimiento rápido</h6>
                    <form action="{{ route('products.kardex.movement') }}" method="POST" class="row g-2">
                        @csrf
                        <div class="col-12">
                            <label class="form-label">Insumo</label>
                            <select class="form-select" name="product_id" required>
                                <option value="">Seleccione...</option>
                                @foreach($manualProducts as $product)
                                    <option value="{{ $product->id }}">{{ $product->code }} - {{ $product->name }} (Stock: {{ $product->stock }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Tipo</label>
                            <select name="movement_type" class="form-select" required>
                                <option value="entrada">Entrada</option>
                                <option value="salida">Salida</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Cantidad</label>
                            <input type="number" min="1" class="form-control" name="quantity" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Costo unitario</label>
                            <input type="number" min="0" step="0.01" class="form-control" name="unit_cost">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Fecha y hora</label>
                            <input type="datetime-local" class="form-control" name="movement_at" value="{{ now()->format('Y-m-d\TH:i') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notas</label>
                            <textarea class="form-control" name="notes" rows="2" placeholder="Motivo del movimiento"></textarea>
                        </div>
                        <div class="col-12 d-grid">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-save me-1"></i>Guardar movimiento</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-1"></i>Últimos movimientos</h6>
                    <ul class="list-group list-group-flush">
                        @forelse($recentMovements as $move)
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-semibold">{{ $move->product->name ?? 'Insumo eliminado' }}</span>
                                    <span class="badge {{ $move->movement_type === 'entrada' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $move->movement_type === 'entrada' ? '+' : '-' }}{{ $move->quantity }}
                                    </span>
                                </div>
                                <small class="text-muted d-block">
                                    {{ optional($move->movement_at)->format('d/m/Y H:i') }} · {{ $move->user->name ?? 'Sistema' }}
                                </small>
                            </li>
                        @empty
                            <li class="list-group-item px-0 text-muted">Sin movimientos recientes.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
