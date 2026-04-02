@extends('layouts.app')

@section('title', 'Control de Insumos de Tomografía')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h3 class="fw-bold text-primary mb-1"><i class="bi bi-boxes me-2"></i>Control de Insumos de Tomografía</h3>
            <p class="text-muted mb-0">Control exclusivo de placas e iopamidol consumidos por resultados de tomografía.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('control-insumos.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Periodo</label>
                    <select name="period" id="period" class="form-select">
                        <option value="daily" {{ ($filters['period'] ?? 'daily') === 'daily' ? 'selected' : '' }}>Diario</option>
                        <option value="weekly" {{ ($filters['period'] ?? '') === 'weekly' ? 'selected' : '' }}>Semanal</option>
                        <option value="biweekly" {{ ($filters['period'] ?? '') === 'biweekly' ? 'selected' : '' }}>Quincenal</option>
                        <option value="monthly" {{ ($filters['period'] ?? '') === 'monthly' ? 'selected' : '' }}>Mensual</option>
                        <option value="range" {{ ($filters['period'] ?? '') === 'range' ? 'selected' : '' }}>Rango de fechas</option>
                    </select>
                </div>
                <div class="col-md-3" id="dailyDateWrapper">
                    <label class="form-label">Fecha</label>
                    <input type="date" name="date" class="form-control" value="{{ $filters['date'] ?? '' }}">
                </div>
                <div class="col-md-3 range-date">
                    <label class="form-label">Desde</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] ?? '' }}">
                </div>
                <div class="col-md-3 range-date">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] ?? '' }}">
                </div>
                <div class="col-md-12 d-flex flex-wrap gap-2 justify-content-end mt-2">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Filtrar</button>
                    <a class="btn btn-danger" href="{{ route('control-insumos.report.pdf', ['period' => $filters['period'] ?? 'daily', 'date' => $filters['date'] ?? '', 'start_date' => $filters['start_date'] ?? '', 'end_date' => $filters['end_date'] ?? '']) }}">
                        <i class="bi bi-filetype-pdf me-1"></i>PDF
                    </a>
                    <a class="btn btn-success" href="{{ route('control-insumos.report.excel', ['period' => $filters['period'] ?? 'daily', 'date' => $filters['date'] ?? '', 'start_date' => $filters['start_date'] ?? '', 'end_date' => $filters['end_date'] ?? '']) }}">
                        <i class="bi bi-file-earmark-excel me-1"></i>Excel
                    </a>
                </div>
            </form>
            <small class="text-muted d-block mt-2">Periodo {{ $filters['range_label'] ?? 'Diario' }}: {{ \Carbon\Carbon::parse($filters['from'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filters['to'])->format('d/m/Y') }}</small>
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
                    <small class="text-muted d-block mt-2">El gasto de placas e iopamidol se calcula desde resultados de tomografía.</small>
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
                    <h6 class="fw-bold mb-3"><i class="bi bi-journal-text me-1"></i>Reporte de movimientos (últimos 50)</h6>
                    <ul class="nav nav-tabs report-tabs mb-3" id="movementTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="plates-in-tab" data-bs-toggle="tab" data-bs-target="#plates-in-pane" type="button" role="tab" aria-controls="plates-in-pane" aria-selected="true">
                                Entradas Placas
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="plates-out-tab" data-bs-toggle="tab" data-bs-target="#plates-out-pane" type="button" role="tab" aria-controls="plates-out-pane" aria-selected="false">
                                Salidas Placas
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="iopamidol-in-tab" data-bs-toggle="tab" data-bs-target="#iopamidol-in-pane" type="button" role="tab" aria-controls="iopamidol-in-pane" aria-selected="false">
                                Entradas Iopamidol
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="iopamidol-out-tab" data-bs-toggle="tab" data-bs-target="#iopamidol-out-pane" type="button" role="tab" aria-controls="iopamidol-out-pane" aria-selected="false">
                                Salidas Iopamidol
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="movementTabsContent">
                        <div class="tab-pane fade show active" id="plates-in-pane" role="tabpanel" aria-labelledby="plates-in-tab" tabindex="0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle report-table">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th class="text-end">Placas +</th>
                                            <th>Notas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php($platesEntries = $entries->filter(fn($entry) => (int) $entry->plates_in > 0))
                                        @forelse($platesEntries as $entry)
                                            <tr>
                                                <td>{{ $entry->created_at?->format('d/m/Y H:i') }}</td>
                                                <td class="text-end fw-semibold text-success">{{ number_format($entry->plates_in) }}</td>
                                                <td>{{ $entry->notes ?: '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">Sin entradas de placas para el periodo.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="plates-out-pane" role="tabpanel" aria-labelledby="plates-out-tab" tabindex="0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle report-table">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Orden</th>
                                            <th>Paciente</th>
                                            <th class="text-end">Placas -</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($platesOutputs as $result)
                                            <tr>
                                                <td>{{ optional($result->result_date)->format('d/m/Y') }}</td>
                                                <td>{{ $result->orderTomography->code ?? '—' }}</td>
                                                <td>{{ trim(($result->patient->last_name ?? '') . ' ' . ($result->patient->first_name ?? '')) ?: '—' }}</td>
                                                <td class="text-end fw-semibold text-danger">{{ number_format($result->plates_used) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">Sin salidas de placas para el periodo.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="iopamidol-in-pane" role="tabpanel" aria-labelledby="iopamidol-in-tab" tabindex="0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle report-table">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th class="text-end">Iopamidol + (ml)</th>
                                            <th>Notas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php($iopamidolEntries = $entries->filter(fn($entry) => (float) $entry->iopamidol_in > 0))
                                        @forelse($iopamidolEntries as $entry)
                                            <tr>
                                                <td>{{ $entry->created_at?->format('d/m/Y H:i') }}</td>
                                                <td class="text-end fw-semibold text-success">{{ number_format($entry->iopamidol_in, 2) }}</td>
                                                <td>{{ $entry->notes ?: '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">Sin entradas de iopamidol para el periodo.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="iopamidol-out-pane" role="tabpanel" aria-labelledby="iopamidol-out-tab" tabindex="0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle report-table">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Orden</th>
                                            <th>Paciente</th>
                                            <th class="text-end">Iopamidol - (ml)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($iopamidolOutputs as $result)
                                            <tr>
                                                <td>{{ optional($result->result_date)->format('d/m/Y') }}</td>
                                                <td>{{ $result->orderTomography->code ?? '—' }}</td>
                                                <td>{{ trim(($result->patient->last_name ?? '') . ' ' . ($result->patient->first_name ?? '')) ?: '—' }}</td>
                                                <td class="text-end fw-semibold text-danger">{{ number_format($result->iopamidol_used, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">Sin salidas de iopamidol para el periodo.</td>
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
    </div>
</div>
@endsection

@push('styles')
<style>
    .report-tabs .nav-link {
        color: #0d6efd;
        border-radius: .6rem .6rem 0 0;
        font-weight: 600;
    }
    .report-tabs .nav-link.active {
        color: #fff;
        background: linear-gradient(90deg, #0d6efd, #20c997);
        border-color: #0d6efd #0d6efd transparent;
    }
    .report-table thead th {
        border-top: 2px solid #20c997;
        border-bottom: 2px solid #0d6efd;
        background-color: #eef7ff;
        color: #0f2f57;
        font-size: .84rem;
        letter-spacing: .02em;
    }
</style>
@endpush


@push('scripts')
<script>
    (function () {
        const periodSelect = document.getElementById('period');
        const rangeDateBlocks = document.querySelectorAll('.range-date');
        const dailyWrapper = document.getElementById('dailyDateWrapper');

        function toggleControlDateFilters() {
            if (!periodSelect) return;
            const isRange = periodSelect.value === 'range';
            rangeDateBlocks.forEach((el) => el.classList.toggle('d-none', !isRange));
            if (dailyWrapper) {
                dailyWrapper.classList.toggle('d-none', isRange);
            }
        }

        if (periodSelect) {
            periodSelect.addEventListener('change', toggleControlDateFilters);
            toggleControlDateFilters();
        }
    })();
</script>
@endpush
