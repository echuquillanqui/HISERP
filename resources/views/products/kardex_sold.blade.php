@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-primary mb-0"><i class="bi bi-capsule me-2"></i>Medicamentos vendidos</h3>
            <p class="text-muted mb-0">Consolidado de salidas, devoluciones y neto de medicamentos registrados en órdenes.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('products.kardex.sold.pdf', request()->query()) }}" class="btn btn-outline-danger"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
            <a href="{{ route('products.kardex.sold.excel', request()->query()) }}" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold">Filtros</div>
        <div class="card-body">
            <form method="GET" action="{{ route('products.kardex.sold') }}" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Medicamento</label>
                    <select name="product_id" class="form-select ts-select">
                        <option value="">Todos</option>
                        @if($selectedProduct)
                            <option value="{{ $selectedProduct->id }}" selected>
                                {{ $selectedProduct->code }} - {{ $selectedProduct->name }}
                                @if($selectedProduct->concentration)
                                    ({{ $selectedProduct->concentration }})
                                @endif
                                @if($selectedProduct->presentation)
                                    - {{ $selectedProduct->presentation }}
                                @endif
                            </option>
                        @endif
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label">Desde</label><input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Hasta</label><input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="form-control"></div>
                <div class="col-md-1 d-flex align-items-end"><button class="btn btn-outline-primary w-100">OK</button></div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead><tr><th>Medicamento</th><th>Código</th><th class="text-end">Unid. vendidas</th><th class="text-end">Total vendido</th><th class="text-end">Unid. devueltas</th><th class="text-end">Total devuelto</th><th class="text-end">Unid. netas</th><th class="text-end">Total neto</th></tr></thead>
                <tbody>
                    @forelse($salesReport as $row)
                        <tr>
                            <td>
                                {{ $row->product?->name ?? 'N/A' }}
                                @if($row->product?->concentration)
                                    <span class="text-muted">({{ $row->product->concentration }})</span>
                                @endif
                                @if($row->product?->presentation)
                                    <span class="text-secondary">- {{ $row->product->presentation }}</span>
                                @endif
                            </td>
                            <td>{{ $row->product?->code ?? '-' }}</td>
                            <td class="text-end">{{ (int) $row->sold_units }}</td>
                            <td class="text-end">S/ {{ number_format((float) $row->sold_total, 2) }}</td>
                            <td class="text-end text-warning">{{ (int) $row->returned_units }}</td>
                            <td class="text-end text-warning">S/ {{ number_format((float) $row->returned_total, 2) }}</td>
                            <td class="text-end fw-semibold">{{ (int) $row->net_units }}</td>
                            <td class="text-end fw-semibold">S/ {{ number_format((float) $row->net_total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Sin ventas en el rango seleccionado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $salesReport->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof TomSelect === 'undefined') return;
        document.querySelectorAll('.ts-select').forEach((el) => {
            if (el.tomselect) return;

            new TomSelect(el, {
                valueField: 'id',
                labelField: 'display',
                searchField: ['code', 'name', 'concentration', 'presentation', 'display'],
                create: false,
                allowEmptyOption: true,
                preload: false,
                maxOptions: 30,
                loadThrottle: 350,
                shouldLoad: (query) => query.length >= 2,
                placeholder: 'Buscar medicamento (nombre, concentración, presentación o código)...',
                load: function (query, callback) {
                    fetch(`/api/products/search?q=${encodeURIComponent(query || '')}`)
                        .then(response => response.json())
                        .then(json => {
                            const rows = (json.data || []).map((product) => ({
                                ...product,
                                value: product.id,
                                text: product.name,
                                display: `${product.code} - ${product.name}${product.concentration ? ` (${product.concentration})` : ''}${product.presentation ? ` - ${product.presentation}` : ''}`,
                            }));
                            callback(rows);
                        })
                        .catch(() => callback());
                },
                render: {
                    option: function (data, escape) {
                        if (!data.id && !data.value) {
                            return `<div class="py-1 px-1">${escape(data.text || '')}</div>`;
                        }

                        const code = data.code ? `<span class="badge text-bg-light border">${escape(data.code)}</span>` : '';
                        const concentration = data.concentration ? `<span class="badge rounded-pill text-bg-info-subtle text-info-emphasis border">${escape(data.concentration)}</span>` : '';
                        const presentation = data.presentation ? `<span class="badge rounded-pill text-bg-secondary-subtle text-secondary-emphasis border">${escape(data.presentation)}</span>` : '';
                        const name = data.name || data.display || data.text || '';

                        return `
                            <div class="py-2 px-1">
                                <div class="d-flex flex-wrap gap-1 align-items-center mb-1">
                                    ${code}
                                    ${concentration}
                                    ${presentation}
                                </div>
                                <div class="fw-semibold">${escape(name)}</div>
                            </div>
                        `;
                    },
                    item: function (data, escape) {
                        if (!data.id && !data.value) {
                            return `<div>${escape(data.text || '')}</div>`;
                        }

                        const name = data.name || data.display || data.text || '';
                        const concentration = data.concentration ? ` · ${escape(data.concentration)}` : '';
                        const presentation = data.presentation ? ` · ${escape(data.presentation)}` : '';
                        return `<div>${escape(name)}${concentration}${presentation}</div>`;
                    }
                }
            });
        });
    });
</script>
@endpush
