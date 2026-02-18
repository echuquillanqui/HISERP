@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    .content-compact { font-size: 0.9rem; }
    .table th, .table td { padding: 0.5rem; vertical-align: middle; border: 1px solid #dee2e6; }
    .card-resumen { padding: 1rem; border-radius: 8px; color: white; margin-bottom: 1rem; }
    .btn-xs { padding: 0.1rem 0.4rem; font-size: 0.75rem; }
</style>

<div class="container py-4 content-compact">
    
    <div class="row align-items-center mb-3 bg-white p-3 shadow-sm rounded border">
        <div class="col-md-6">
            <h4 class="mb-0 fw-bold text-primary">CUADRE DE CAJA</h4>
            <small class="text-muted">Visualizando: {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</small>
        </div>
        <div class="col-md-6 d-flex justify-content-end align-items-center gap-2">
            <form action="{{ route('cashbox.index') }}" method="GET" class="d-flex m-0">
                <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm me-2">
                <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
            </form>
            <a href="{{ route('cashbox.pdf', ['date' => $date]) }}" class="btn btn-sm btn-danger"><i class="bi bi-file-pdf"></i> PDF</a>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-md-4">
            <div class="card-resumen bg-success shadow-sm">
                <small class="d-block opacity-75">TOTAL INGRESOS</small>
                <h3 class="fw-bold mb-0">S/ {{ number_format($totalIngresos, 2) }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-resumen bg-danger shadow-sm">
                <small class="d-block opacity-75">TOTAL EGRESOS</small>
                <h3 class="fw-bold mb-0">S/ {{ number_format($totalEgresos, 2) }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-resumen bg-dark shadow-sm border-start border-primary border-5">
                <small class="d-block opacity-75 text-primary fw-bold">SALDO EN EFECTIVO</small>
                <h3 class="fw-bold mb-0">S/ {{ number_format($saldoCaja, 2) }}</h3>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white fw-bold">REGISTRAR GASTO</div>
                <div class="card-body">
                    <form action="{{ route('cashbox.expense') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Descripción</label>
                            <input type="text" name="description" class="form-control" placeholder="Ej: Pago de agua" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold small">Comprobante</label>
                                <select name="voucher_type" class="form-select">
                                    <option value="BOLETA">BOLETA</option>
                                    <option value="FACTURA">FACTURA</option>
                                    <option value="OTROS" selected>OTROS</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small">Monto S/</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Adjuntar archivo</label>
                            <input type="file" name="document" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold">GUARDAR MOVIMIENTO</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold text-success border-bottom"><i class="bi bi-arrow-up-circle"></i> VENTAS DEL DÍA</div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Orden</th><th>Paciente</th><th class="text-end">Total</th><th class="text-center">Detalle</th></tr>
                        </thead>
                        <tbody>
                            @foreach($ordenes as $o)
                            <tr>
                                <td class="fw-bold">#{{ $o->id }}</td>
                                <td>{{ $o->patient->last_name }} {{ $o->patient->first_name }}</td>
                                <td class="text-end fw-bold text-success">S/ {{ number_format($o->total, 2) }}</td>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-outline-success" onclick="verOrden({{ $o->id }}, '{{ $o->patient->last_name }}', {{ json_encode($o->details) }})">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold text-danger border-bottom"><i class="bi bi-arrow-down-circle"></i> GASTOS REALIZADOS</div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 text-center">
                        <thead class="table-light">
                            <tr><th class="text-start">Descripción</th><th>Tipo</th><th class="text-end">Monto</th><th>Acciones</th></tr>
                        </thead>
                        <tbody>
                            @foreach($egresos as $e)
                            <tr>
                                <td class="text-start">{{ $e->description }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $e->voucher_type }}</span></td>
                                <td class="text-end fw-bold text-danger">S/ {{ number_format($e->amount, 2) }}</td>
                                <td>
                                    <div class="btn-group">
                                        @if($e->file_path)
                                            <button class="btn btn-xs btn-info text-white mx-1" onclick="abrirDoc('{{ asset('storage/'.$e->file_path) }}')"><i class="bi bi-image"></i></button>
                                        @endif
                                        <button class="btn btn-xs btn-warning btn-edit" 
                                            data-id="{{ $e->id }}" data-desc="{{ $e->description }}" 
                                            data-amount="{{ $e->amount }}" data-type="{{ $e->voucher_type }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"> <div class="modal-content">
            <form id="formEdit" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square"></i> Editar Gasto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="fw-bold">Descripción</label><input type="text" name="description" id="ed_desc" class="form-control" required></div>
                    <div class="row g-3">
                        <div class="col-6 mb-3"><label class="fw-bold">Tipo Comprobante</label><select name="voucher_type" id="ed_type" class="form-select"><option value="BOLETA">BOLETA</option><option value="FACTURA">FACTURA</option><option value="OTROS">OTROS</option></select></div>
                        <div class="col-6 mb-3"><label class="fw-bold">Monto S/</label><input type="number" step="0.01" name="amount" id="ed_amount" class="form-control" required></div>
                    </div>
                    <div class="mb-3"><label class="fw-bold">Cambiar Comprobante</label><input type="file" name="document" class="form-control"></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary fw-bold w-100">ACTUALIZAR DATOS</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalOrden" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white"><h5 class="modal-title" id="orderTitle"></h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-0">
                <table class="table mb-0">
                    <thead class="table-light"><tr><th>Examen</th><th class="text-end">Precio</th></tr></thead>
                    <tbody id="orderBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDoc" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content"> <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-file-earmark-text"></i> Visualizar Comprobante</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center bg-light">
                <div id="docContent" style="min-height: 200px; display: flex; align-items: center; justify-content: center;">
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Función para ver detalle de orden
    function verOrden(id, paciente, detalles) {
        document.getElementById('orderTitle').innerText = `Orden #${id} - ${paciente}`;
        let html = '';
        detalles.forEach(d => {
            html += `<tr><td>${d.name}</td><td class="text-end fw-bold">S/ ${parseFloat(d.price).toFixed(2)}</td></tr>`;
        });
        document.getElementById('orderBody').innerHTML = html;
        new bootstrap.Modal(document.getElementById('modalOrden')).show();
    }

    // Editar Gasto
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.onclick = function() {
            document.getElementById('ed_desc').value = this.dataset.desc;
            document.getElementById('ed_amount').value = this.dataset.amount;
            document.getElementById('ed_type').value = this.dataset.type;
            document.getElementById('formEdit').action = `/cashbox/expense/${this.dataset.id}`;
            new bootstrap.Modal(document.getElementById('modalEditar')).show();
        }
    });

    // Ver Imagen/PDF
    function abrirDoc(url) {
    const content = document.getElementById('docContent');
    if (url.toLowerCase().endsWith('.pdf')) {
        content.innerHTML = `<iframe src="${url}" width="100%" height="500px" style="border:none;"></iframe>`;
    } else {
        // Añadimos max-height para que la imagen no rompa el modal
        content.innerHTML = `<img src="${url}" class="img-fluid rounded shadow" style="max-height: 70vh;">`;
    }
    new bootstrap.Modal(document.getElementById('modalDoc')).show();
}
</script>
@endsection