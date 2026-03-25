@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-primary mb-0"><i class="bi bi-journal-text me-2"></i>Kardex de Medicamentos</h3>
            <p class="text-muted mb-0">Control logístico completo (entradas, salidas y ventas desde órdenes).</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-light">Volver a productos</a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold">Registrar movimiento manual</div>
        <div class="card-body">
            <form action="{{ route('products.kardex.movement') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">Medicamento</label>
                    <select name="product_id" class="form-select" required>
                        <option value="">Seleccione...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->code }} - {{ $product->name }} (Stock: {{ $product->stock }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tipo</label>
                    <select name="movement_type" class="form-select" required>
                        <option value="entrada">Entrada</option>
                        <option value="salida">Salida</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Cantidad</label>
                    <input type="number" min="1" name="quantity" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Costo unitario</label>
                    <input type="number" min="0" step="0.01" name="unit_cost" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Fecha/Hora</label>
                    <input type="datetime-local" name="movement_at" class="form-control">
                </div>
                <div class="col-md-10">
                    <label class="form-label">Observación</label>
                    <input type="text" name="notes" class="form-control" placeholder="Compra, ajuste, devolución, vencimiento, etc.">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold">Filtros de reporte</div>
        <div class="card-body">
            <form method="GET" action="{{ route('products.kardex') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Medicamento</label>
                    <select name="product_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ (string)($filters['product_id'] ?? '') === (string)$product->id ? 'selected' : '' }}>
                                {{ $product->code }} - {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Desde</label>
                    <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-outline-primary w-100">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold">Medicamentos vendidos (según órdenes)</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Medicamento</th>
                                <th class="text-end">Unid.</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salesReport as $row)
                                <tr>
                                    <td>{{ $row->product?->name ?? 'N/A' }}</td>
                                    <td class="text-end">{{ (int) $row->sold_units }}</td>
                                    <td class="text-end">S/ {{ number_format((float) $row->sold_total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Sin ventas en el rango seleccionado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold">Movimientos del Kardex</div>
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
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movements as $move)
                                <tr>
                                    <td>{{ optional($move->movement_at)->format('d/m/Y H:i') }}</td>
                                    <td>{{ $move->product?->name }}</td>
                                    <td>
                                        <span class="badge {{ $move->movement_type === 'entrada' ? 'bg-success' : 'bg-danger' }}">
                                            {{ strtoupper($move->movement_type) }}
                                        </span>
                                    </td>
                                    <td class="text-end">{{ $move->quantity }}</td>
                                    <td class="text-end">{{ $move->stock_before }}</td>
                                    <td class="text-end">{{ $move->stock_after }}</td>
                                    <td>{{ strtoupper($move->source) }}</td>
                                    <td>
                                        @if($move->order_id)
                                            #{{ $move->order_id }}<br><small>{{ $move->order?->patient?->first_name }} {{ $move->order?->patient?->last_name }}</small>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No hay movimientos para mostrar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white">
                    {{ $movements->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
