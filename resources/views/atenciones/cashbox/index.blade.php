@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Cuadre de Caja: {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h4>
        <form action="{{ route('cashbox.index') }}" method="GET" class="d-flex">
            <input type="date" name="date" value="{{ $date }}" class="form-control me-2">
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </form>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body">
                    <h6>(+) INGRESOS (Ventas)</h6>
                    <h3>S/ {{ number_format($totalIngresos, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white shadow-sm">
                <div class="card-body">
                    <h6>(-) EGRESOS</h6>
                    <h3>S/ {{ number_format($totalEgresos, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-dark text-white shadow-sm">
                <div class="card-body">
                    <h6>(=) SALDO EN CAJA</h6>
                    <h3>S/ {{ number_format($saldoCaja, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">NUEVO EGRESO</div>
                <div class="card-body">
                    <form action="{{ route('cashbox.expense') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label>Descripción</label>
                            <input type="text" name="description" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Comprobante</label>
                            <select name="voucher_type" class="form-select">
                                <option value="BOLETA">BOLETA</option>
                                <option value="FACTURA">FACTURA</option>
                                <option value="OTROS">OTROS</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Monto (S/)</label>
                            <input type="number" step="0.01" name="amount" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Adjuntar Foto/PDF</label>
                            <input type="file" name="file_path" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-danger w-100">Guardar Egreso</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">INGRESOS DEL DÍA</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead><tr><th>Orden</th><th>Paciente</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                            @foreach($ordenes as $o)
                            <tr><td>#{{ $o->id }}</td><td>{{ $o->patient->last_name }}</td><td class="text-end">S/ {{ number_format($o->total, 2) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">EGRESOS DEL DÍA</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead><tr><th>Gasto</th><th>Tipo</th><th>Monto</th><th>Doc</th></tr></thead>
                        <tbody>
                            @foreach($egresos as $e)
                            <tr>
                                <td>{{ $e->description }}</td>
                                <td><small class="badge bg-light text-dark">{{ $e->voucher_type }}</small></td>
                                <td class="text-danger">S/ {{ number_format($e->amount, 2) }}</td>
                                <td>
                                    @if($e->file_path)
                                        <a href="{{ asset('storage/'.$e->file_path) }}" target="_blank" class="btn btn-sm btn-link p-0">Ver</a>
                                    @endif
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
@endsection