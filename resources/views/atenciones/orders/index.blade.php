@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    .table-fixed { table-layout: fixed; width: 100%; }
    .table-fixed td { word-wrap: break-word; overflow-wrap: break-word; white-space: normal; border: 1px solid #dee2e6; }
    .card-resumen { border: none; border-radius: 10px; color: white; }
</style>

<div class="container-fluid py-4">
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body d-flex justify-content-between align-items-center">
            <h4 class="fw-bold text-primary mb-0">MÓDULO DE CAJA</h4>
            <form action="{{ route('cashbox.index') }}" method="GET" class="d-flex">
                <input type="date" name="date" value="{{ $date }}" class="form-control me-2">
                <button class="btn btn-primary">Filtrar</button>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4"><div class="card card-resumen bg-success p-3"><h6>INGRESOS</h6><h3>S/ {{ number_format($totalIngresos, 2) }}</h3></div></div>
        <div class="col-md-4"><div class="card card-resumen bg-danger p-3"><h6>EGRESOS</h6><h3>S/ {{ number_format($totalEgresos, 2) }}</h3></div></div>
        <div class="col-md-4"><div class="card card-resumen bg-dark p-3"><h6>SALDO NETO</h6><h3>S/ {{ number_format($saldoCaja, 2) }}</h3></div></div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold">REGISTRAR EGRESO</div>
                <div class="card-body">
                    <form action="{{ route('cashbox.expense') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3"><label>Descripción</label><input type="text" name="description" class="form-control" required></div>
                        <div class="row">
                            <div class="col-6 mb-3"><label>Tipo</label>
                                <select name="voucher_type" class="form-select">
                                    <option value="BOLETA">BOLETA</option><option value="FACTURA">FACTURA</option><option value="OTROS" selected>OTROS</option>
                                </select>
                            </div>
                            <div class="col-6 mb-3"><label>Monto</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
                        </div>
                        <div class="mb-3"><label>Documento</label><input type="file" name="document" class="form-control"></div>
                        <button class="btn btn-primary w-100 fw-bold">GUARDAR EGRESO</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold text-success">VENTAS (INGRESOS)</div>
                <table class="table table-fixed mb-0">
                    <thead class="table-light">
                        <tr><th width="20%">Orden</th><th width="50%">Paciente</th><th width="20%">Total</th><th width="10%"></th></tr>
                    </thead>
                    <tbody>
                        @foreach($ordenes as $o)
                        <tr>
                            <td>#{{ $o->id }}</td>
                            <td>{{ $o->patient->last_name }} {{ $o->patient->first_name }}</td>
                            <td class="text-end fw-bold">S/ {{ number_format($o->total, 2) }}</td>
                            <td><button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#order{{ $o->id }}"><i class="bi bi-eye"></i></button></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold text-danger">GASTOS (EGRESOS)</div>
                <table class="table table-fixed mb-0">
                    <thead class="table-light">
                        <tr><th width="45%">Descripción</th><th width="25%">Monto</th><th width="30%">Acciones</th></tr>
                    </thead>
                    <tbody>
                        @foreach($egresos as $e)
                        <tr>
                            <td>{{ $e->description }} <br><small class="text-muted">{{ $e->voucher_type }}</small></td>
                            <td class="text-danger fw-bold text-end">S/ {{ number_format($e->amount, 2) }}</td>
                            <td>
                                <div class="btn-group">
                                    @if($e->file_path)
                                    <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#doc{{ $e->id }}"><i class="bi bi-file-image"></i></button>
                                    @endif
                                    <button class="btn btn-sm btn-warning text-white" data-bs-toggle="modal" data-bs-target="#edit{{ $e->id }}"><i class="bi bi-pencil"></i></button>
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

@foreach($egresos as $e)
    <div class="modal fade" id="doc{{ $e->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog"><div class="modal-content"><div class="modal-body text-center">
            @if(Str::endsWith($e->file_path, '.pdf')) <iframe src="{{ asset('storage/'.$e->file_path) }}" width="100%" height="400px"></iframe>
            @else <img src="{{ asset('storage/'.$e->file_path) }}" class="img-fluid"> @endif
        </div></div></div>
    </div>
    <div class="modal fade" id="edit{{ $e->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog"><div class="modal-content">
            <form action="{{ route('cashbox.expense.update', $e->id) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3"><label>Descripción</label><input type="text" name="description" class="form-control" value="{{ $e->description }}"></div>
                    <div class="mb-3"><label>Monto</label><input type="number" step="0.01" name="amount" class="form-control" value="{{ $e->amount }}"></div>
                    <div class="mb-3"><label>Comprobante</label><input type="file" name="document" class="form-control"></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Actualizar</button></div>
            </form>
        </div></div>
    </div>
@endforeach

@foreach($ordenes as $o)
    <div class="modal fade" id="order{{ $o->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header bg-success text-white">Detalle Orden #{{ $o->id }}</div>
            <div class="modal-body p-0">
                <table class="table mb-0">
                    @foreach($o->details as $d)
                    <tr><td>{{ $d->name }}</td><td class="text-end">S/ {{ number_format($d->price, 2) }}</td></tr>
                    @endforeach
                </table>
            </div>
        </div></div>
    </div>
@endforeach

@endsection