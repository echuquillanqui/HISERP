@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-primary mb-0"><i class="bi bi-journal-plus me-2"></i>Registros de movimientos</h3>
            <p class="text-muted mb-0">Registro manual de entradas y salidas de medicamentos.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('products.kardex.records.pdf', request()->query()) }}" class="btn btn-outline-danger"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
            <a href="{{ route('products.kardex.records.excel', request()->query()) }}" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold">Registrar movimiento manual</div>
        <div class="card-body">
            <form action="{{ route('products.kardex.movement') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">Medicamento</label>
                    <select name="product_id" class="form-select ts-select ts-product-select" required>
                        <option value="">Seleccione...</option>
                        @foreach($products as $product)
                            <option
                                value="{{ $product->id }}"
                                data-code="{{ $product->code }}"
                                data-name="{{ $product->name }}"
                                data-concentration="{{ $product->concentration }}"
                                data-presentation="{{ $product->presentation }}"
                                data-stock="{{ $product->stock }}"
                            >
                                {{ $product->code }} - {{ $product->name }}
                                @if($product->concentration) ({{ $product->concentration }}) @endif
                                @if($product->presentation) - {{ $product->presentation }} @endif
                                (Stock: {{ $product->stock }})
                            </option>
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
                <div class="col-md-6">
                    <label class="form-label">Orden relacionada (logística)</label>
                    <select name="order_id" class="form-select ts-select">
                        <option value="">Sin orden</option>
                        @foreach($orders as $order)
                            <option value="{{ $order->id }}">{{ $order->code ?? ('#'.$order->id) }} - {{ trim(($order->patient?->first_name ?? '').' '.($order->patient?->last_name ?? '')) ?: 'Paciente no registrado' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
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
        <div class="card-header bg-white fw-bold">Filtros</div>
        <div class="card-body">
            <form method="GET" action="{{ route('products.kardex.records') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Medicamento</label>
                    <select name="product_id" class="form-select ts-select ts-product-select">
                        <option value="">Todos</option>
                        @foreach($products as $product)
                            <option
                                value="{{ $product->id }}"
                                data-code="{{ $product->code }}"
                                data-name="{{ $product->name }}"
                                data-concentration="{{ $product->concentration }}"
                                data-presentation="{{ $product->presentation }}"
                                {{ (string)($filters['product_id'] ?? '') === (string)$product->id ? 'selected' : '' }}
                            >
                                {{ $product->code }} - {{ $product->name }}
                                @if($product->concentration) ({{ $product->concentration }}) @endif
                                @if($product->presentation) - {{ $product->presentation }} @endif
                            </option>
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
                <div class="col-md-2"><label class="form-label">Desde</label><input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="form-control"></div>
                <div class="col-md-2"><label class="form-label">Hasta</label><input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="form-control"></div>
                <div class="col-md-2 d-flex align-items-end"><button class="btn btn-outline-primary w-100">Filtrar</button></div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold">Listado de registros</div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Fecha</th><th>Medicamento</th><th>Tipo</th><th class="text-end">Cant.</th><th class="text-end">Antes</th><th class="text-end">Después</th><th>Origen</th><th>Orden</th></tr></thead>
                <tbody>
                    @forelse($movements as $move)
                        <tr>
                            <td>{{ optional($move->movement_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                {{ $move->product?->name }}
                                @if($move->product?->concentration)
                                    <span class="text-muted">({{ $move->product->concentration }})</span>
                                @endif
                                @if($move->product?->presentation)
                                    <span class="text-secondary">- {{ $move->product->presentation }}</span>
                                @endif
                            </td>
                            <td><span class="badge {{ $move->movement_type === 'entrada' ? 'bg-success' : 'bg-danger' }}">{{ strtoupper($move->movement_type) }}</span></td>
                            <td class="text-end">{{ $move->quantity }}</td>
                            <td class="text-end">{{ $move->stock_before }}</td>
                            <td class="text-end">{{ $move->stock_after }}</td>
                            <td>{{ strtoupper($move->source) }}</td>
                            <td>{{ $move->order?->code ?? ($move->order_id ? '#'.$move->order_id : '-') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No hay registros para mostrar.</td></tr>
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
            if (!el.tomselect) {
                const isProductSelect = el.classList.contains('ts-product-select');
                new TomSelect(el, {
                    create: false,
                    allowEmptyOption: true,
                    searchConjunction: 'and',
                    searchField: isProductSelect
                        ? ['text', 'code', 'name', 'concentration', 'presentation', 'stock']
                        : ['text'],
                    plugins: {
                        clear_button: { title: 'Limpiar selección' }
                    },
                    placeholder: isProductSelect
                        ? 'Buscar medicamento (nombre, concentración, presentación o código)...'
                        : 'Seleccione una opción...',
                    maxOptions: isProductSelect ? 30 : 50,
                    render: isProductSelect
                        ? {
                            option: function(data, escape) {
                                if (!data.value) {
                                    return `<div class="py-1 px-1">${escape(data.text)}</div>`;
                                }

                                const code = data.code ? `<span class="badge text-bg-light border">${escape(data.code)}</span>` : '';
                                const concentration = data.concentration ? `<span class="badge rounded-pill text-bg-info-subtle text-info-emphasis border">${escape(data.concentration)}</span>` : '';
                                const presentation = data.presentation ? `<span class="badge rounded-pill text-bg-secondary-subtle text-secondary-emphasis border">${escape(data.presentation)}</span>` : '';
                                const stock = data.stock !== undefined && data.stock !== null && data.stock !== ''
                                    ? `<small class="d-block text-muted mt-1">Stock actual: ${escape(String(data.stock))}</small>`
                                    : '';

                                return `
                                    <div class="py-2 px-1">
                                        <div class="d-flex flex-wrap gap-1 align-items-center mb-1">
                                            ${code}
                                            ${concentration}
                                            ${presentation}
                                        </div>
                                        <div class="fw-semibold">${escape(data.name || data.text)}</div>
                                        ${stock}
                                    </div>
                                `;
                            },
                            item: function(data, escape) {
                                if (!data.value) {
                                    return `<div>${escape(data.text)}</div>`;
                                }

                                const concentration = data.concentration ? ` · ${escape(data.concentration)}` : '';
                                const presentation = data.presentation ? ` · ${escape(data.presentation)}` : '';
                                return `<div>${escape(data.name || data.text)}${concentration}${presentation}</div>`;
                            }
                        }
                        : undefined,
                    emptyOptionLabel: 'Sin resultados',
                    onDropdownClose: function () {
                        this.clearActiveOption();
                        this.setTextboxValue('');
                        this.refreshOptions(false);
                    }
                });
            }
        });
    });
</script>
@endpush
