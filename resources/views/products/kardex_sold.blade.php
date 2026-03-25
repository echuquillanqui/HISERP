@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-primary mb-0"><i class="bi bi-capsule me-2"></i>Medicamentos vendidos</h3>
            <p class="text-muted mb-0">Consolidado de ventas de medicamentos registradas en órdenes.</p>
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
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ (string)($filters['product_id'] ?? '') === (string)$product->id ? 'selected' : '' }}>{{ $product->code }} - {{ $product->name }}</option>
                        @endforeach
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
                <thead><tr><th>Medicamento</th><th>Código</th><th class="text-end">Unid. vendidas</th><th class="text-end">Total vendido</th></tr></thead>
                <tbody>
                    @forelse($salesReport as $row)
                        <tr>
                            <td>{{ $row->product?->name ?? 'N/A' }}</td>
                            <td>{{ $row->product?->code ?? '-' }}</td>
                            <td class="text-end">{{ (int) $row->sold_units }}</td>
                            <td class="text-end">S/ {{ number_format((float) $row->sold_total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Sin ventas en el rango seleccionado.</td></tr>
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
            if (!el.tomselect) new TomSelect(el, { create: false, allowEmptyOption: true });
        });
    });
</script>
@endpush
