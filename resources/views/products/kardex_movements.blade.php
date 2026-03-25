@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-primary mb-0"><i class="bi bi-journal-text me-2"></i>Movimientos del kardex</h3>
            <p class="text-muted mb-0">Consulta general de entradas y salidas del inventario.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('products.kardex.movements.pdf', request()->query()) }}" class="btn btn-outline-danger"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
            <a href="{{ route('products.kardex.movements.excel', request()->query()) }}" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold">Filtros</div>
        <div class="card-body">
            <form method="GET" action="{{ route('products.kardex.movements') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Medicamento</label>
                    <select name="product_id" class="form-select ts-select">
                        <option value="">Todos</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ (string)($filters['product_id'] ?? '') === (string)$product->id ? 'selected' : '' }}>{{ $product->code }} - {{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tipo</label>
                    <select name="movement_type" class="form-select">
                        <option value="">Todos</option>
                        <option value="entrada" {{ ($filters['movement_type'] ?? '') === 'entrada' ? 'selected' : '' }}>Entrada</option>
                        <option value="salida" {{ ($filters['movement_type'] ?? '') === 'salida' ? 'selected' : '' }}>Salida</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Origen</label>
                    <select name="source" class="form-select">
                        <option value="">Todos</option>
                        <option value="manual" {{ ($filters['source'] ?? '') === 'manual' ? 'selected' : '' }}>Manual</option>
                        <option value="ajuste" {{ ($filters['source'] ?? '') === 'ajuste' ? 'selected' : '' }}>Ajuste</option>
                        <option value="orden" {{ ($filters['source'] ?? '') === 'orden' ? 'selected' : '' }}>Orden</option>
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label">Desde</label><input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="form-control"></div>
                <div class="col-md-2"><label class="form-label">Hasta</label><input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="form-control"></div>
                <div class="col-md-2 d-flex align-items-end"><button class="btn btn-outline-primary w-100">Filtrar</button></div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Medicamento</th>
                        <th>Tipo</th>
                        <th class="text-end">Cant.</th>
                        <th class="text-end">Antes</th>
                        <th class="text-end">Después</th>
                        <th>Origen</th>
                        <th>Orden</th>
                        <th>Observación</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($movements as $move)
                    <tr>
                        <td>{{ optional($move->movement_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ $move->product?->name }}</td>
                        <td><span class="badge {{ $move->movement_type === 'entrada' ? 'bg-success' : 'bg-danger' }}">{{ strtoupper($move->movement_type) }}</span></td>
                        <td class="text-end">{{ $move->quantity }}</td>
                        <td class="text-end">{{ $move->stock_before }}</td>
                        <td class="text-end">{{ $move->stock_after }}</td>
                        <td>{{ strtoupper($move->source) }}</td>
                        <td>@if($move->order_id){{ $move->order?->code ?? '#'.$move->order_id }}@else-@endif</td>
                        <td>{{ $move->notes }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No hay movimientos para mostrar.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $movements->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof TomSelect === 'undefined') return;
        document.querySelectorAll('.ts-select').forEach((el) => {
            if (!el.tomselect) new TomSelect(el, { create: false, allowEmptyOption: true });
        });
    });
</script>
@endpush
