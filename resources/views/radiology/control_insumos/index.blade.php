@extends('layouts.app')

@section('title', 'Control de Insumos de Tomografía')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h3 class="fw-bold text-primary mb-1"><i class="bi bi-boxes me-2"></i>Control de Insumos de Tomografía</h3>
            <p class="text-muted mb-0">Control exclusivo de placas e iopamidol consumidos por órdenes y resultados.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('control-insumos.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Desde</label>
                    <input type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
                </div>
                <div class="col-md-4 d-grid">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted d-block">Placas</small>
                    <div class="d-flex justify-content-between"><span>Entradas</span><strong>{{ number_format($summary['plates_in']) }}</strong></div>
                    <div class="d-flex justify-content-between"><span>Salidas</span><strong>{{ number_format($summary['plates_out']) }}</strong></div>
                    <div class="d-flex justify-content-between"><span>Saldo</span><strong class="{{ $summary['plates_balance'] < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($summary['plates_balance']) }}</strong></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted d-block">Iopamidol (ml)</small>
                    <div class="d-flex justify-content-between"><span>Entradas</span><strong>{{ number_format($summary['iopamidol_in'], 2) }}</strong></div>
                    <div class="d-flex justify-content-between"><span>Salidas</span><strong>{{ number_format($summary['iopamidol_out'], 2) }}</strong></div>
                    <div class="d-flex justify-content-between"><span>Saldo</span><strong class="{{ $summary['iopamidol_balance'] < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($summary['iopamidol_balance'], 2) }}</strong></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted d-block">Consumo del periodo</small>
                    <div class="d-flex justify-content-between"><span>Órdenes de tomografía</span><strong>{{ number_format($summary['orders_count']) }}</strong></div>
                    <div class="d-flex justify-content-between"><span>Resultados emitidos</span><strong>{{ number_format($summary['results_count']) }}</strong></div>
                    <small class="text-muted d-block mt-2">Las placas se consumen por órdenes + resultados. El iopamidol por resultados.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="bi bi-plus-circle me-1"></i>Registrar entrada</h6>
                    <form action="{{ route('control-insumos.store') }}" method="POST" class="row g-2">
                        @csrf
                        <div class="col-12">
                            <label class="form-label">Placas (entrada)</label>
                            <input type="number" min="0" class="form-control @error('plates_in') is-invalid @enderror" name="plates_in" value="{{ old('plates_in', 0) }}">
                            @error('plates_in')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Iopamidol (ml)</label>
                            <input type="number" min="0" step="0.01" class="form-control @error('iopamidol_in') is-invalid @enderror" name="iopamidol_in" value="{{ old('iopamidol_in', 0) }}">
                            @error('iopamidol_in')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notas</label>
                            <textarea class="form-control" name="notes" rows="2" placeholder="Ej. reposición semanal">{{ old('notes') }}</textarea>
                        </div>
                        <div class="col-12 d-grid">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-save me-1"></i>Guardar entrada</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-1"></i>Últimos registros de entrada</h6>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th class="text-end">Placas +</th>
                                    <th class="text-end">Iopamidol +</th>
                                    <th>Notas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entries as $entry)
                                    <tr>
                                        <td>{{ $entry->created_at?->format('d/m/Y H:i') }}</td>
                                        <td class="text-end">{{ number_format($entry->plates_in) }}</td>
                                        <td class="text-end">{{ number_format($entry->iopamidol_in, 2) }}</td>
                                        <td>{{ $entry->notes ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Sin registros de entrada.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
