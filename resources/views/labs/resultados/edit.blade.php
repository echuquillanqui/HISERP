@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-primary text-white d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0"><i class="bi bi-person-circle me-2"></i>{{ $order->patient->first_name }} {{ $order->patient->last_name }}</h4>
                <small class="opacity-75">Orden: {{ $order->code }} | DNI: {{ $order->patient->dni }}</small>
            </div>
            <div class="text-end">
                <span class="badge bg-white text-primary px-3 py-2">MODULO DE LABORATORIO</span>
            </div>
        </div>
    </div>

    <form action="{{ route('lab-results.update', $id) }}" method="POST">
        @csrf
        @method('PUT')

        @foreach($resultadosAgrupados as $areaNombre => $examenes)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-dark text-white py-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-folder2-open me-2"></i>ÁREA: {{ strtoupper($areaNombre) }}
                    </h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light small">
                            <tr>
                                <th class="ps-4" style="width: 30%;">ANÁLISIS</th>
                                <th style="width: 20%;">RESULTADO</th>
                                <th style="width: 15%;">UNIDAD</th>
                                <th style="width: 15%;">VALOR REFERENCIAL</th>
                                <th class="pe-4">OBSERVACIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($examenes as $res)
                                <tr>
                                    <td class="ps-4 fw-bold text-secondary">{{ $res->catalog->name }}</td>
                                    <td>
                                        <input type="text" 
                                               name="results[{{ $res->id }}][value]" 
                                               value="{{ $res->result_value }}" 
                                               class="form-control form-control-sm border-{{ $res->result_value ? 'success' : 'primary' }}" 
                                               placeholder="Ingresar dato...">
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ $res->unit }}</span></td>
                                    <td class="small text-muted">{{ $res->reference_range }}</td>
                                    <td class="pe-4">
                                        <input type="text" 
                                               name="results[{{ $res->id }}][observations]" 
                                               value="{{ $res->observations }}" 
                                               class="form-control form-control-sm" 
                                               placeholder="Opcional">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        <div class="sticky-bottom bg-white p-3 border-top shadow-lg d-flex justify-content-end gap-2">
            <a href="{{ route('lab-results.index') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
            <button type="submit" class="btn btn-primary px-5 fw-bold">
                <i class="bi bi-check-all me-2"></i> GUARDAR RESULTADOS
            </button>
        </div>
    </form>
</div>
@endsection