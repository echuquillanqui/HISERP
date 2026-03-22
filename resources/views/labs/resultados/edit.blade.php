@extends('layouts.app')

@section('content')
<div class="container py-4" x-data="signatureSelector({
    defaultProfessionalId: '{{ $selectedProfessionalId }}',
    defaultTechnologistId: '{{ $selectedTechnologistId }}'
})">
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

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light py-3">
                <h6 class="mb-0 fw-bold text-primary">
                    <i class="bi bi-pen me-2"></i>Firmas del informe
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Profesional de la salud</label>
                        <input type="text"
                               class="form-control"
                               list="profesionalesSaludList"
                               placeholder="Buscar médico/especialista..."
                               x-model="professionalName"
                               @input="syncProfessionalId">
                        <datalist id="profesionalesSaludList">
                            @foreach($profesionalesSalud as $profesional)
                                <option value="{{ $profesional->name }}" data-id="{{ $profesional->id }}"></option>
                            @endforeach
                        </datalist>
                        <input type="hidden" name="profesional_id" x-model="professionalId">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tecnólogo de laboratorio</label>
                        <input type="text"
                               class="form-control"
                               list="tecnologosList"
                               placeholder="Buscar tecnólogo..."
                               x-model="technologistName"
                               @input="syncTechnologistId">
                        <datalist id="tecnologosList">
                            @foreach($tecnologos as $tecnologo)
                                <option value="{{ $tecnologo->name }}" data-id="{{ $tecnologo->id }}"></option>
                            @endforeach
                        </datalist>
                        <input type="hidden" name="tecnologo_id" x-model="technologistId">
                    </div>
                </div>
            </div>
        </div>

        @foreach($resultadosAgrupados as $areaNombre => $examenes)
    {{-- 1. Agregamos la lógica de filtrado al inicio del bucle --}}
    @php
        $areaUpper = strtoupper($areaNombre);
    @endphp

    @if($areaUpper === 'MEDICINA' || $areaUpper === 'ADICIONALES')
        @continue {{-- Esto hace que el bucle ignore estas áreas y pase a la siguiente --}}
    @endif

    {{-- 2. El resto de tu código de la tarjeta (Card) se mantiene igual --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-dark text-white py-3">
            <h6 class="mb-0 fw-bold">
                <i class="bi bi-folder2-open me-2"></i>ÁREA: {{ $areaUpper }}
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
                                @php
                                    $hasResultValue = $res->result_value !== null && trim((string) $res->result_value) !== '';
                                @endphp
                                <input type="text" 
                                       name="results[{{ $res->id }}][value]" 
                                       value="{{ $res->result_value }}" 
                                       class="form-control form-control-sm border-{{ $hasResultValue ? 'success' : 'primary' }}" 
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
            <a :href="pdfUrl" target="_blank" class="btn btn-outline-danger px-4">
                <i class="bi bi-file-earmark-pdf me-2"></i>Ver PDF
            </a>
            <a href="{{ route('lab-results.index') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
            <button type="submit" class="btn btn-primary px-5 fw-bold">
                <i class="bi bi-check-all me-2"></i> GUARDAR RESULTADOS
            </button>
        </div>
    </form>
</div>

<script>
function signatureSelector(config) {
    const profesionales = @json($profesionalesSalud->map(fn ($user) => ['id' => (string) $user->id, 'name' => $user->name])->values());
    const tecnologos = @json($tecnologos->map(fn ($user) => ['id' => (string) $user->id, 'name' => $user->name])->values());

    const defaultProfessional = profesionales.find(user => user.id === config.defaultProfessionalId) || null;
    const defaultTechnologist = tecnologos.find(user => user.id === config.defaultTechnologistId) || null;

    return {
        professionalId: defaultProfessional?.id || '',
        professionalName: defaultProfessional?.name || '',
        technologistId: defaultTechnologist?.id || '',
        technologistName: defaultTechnologist?.name || '',

        syncProfessionalId() {
            const found = profesionales.find(user => user.name === this.professionalName);
            this.professionalId = found ? found.id : '';
        },
        syncTechnologistId() {
            const found = tecnologos.find(user => user.name === this.technologistName);
            this.technologistId = found ? found.id : '';
        },
        get pdfUrl() {
            const url = new URL('{{ route('lab-results.show', $id) }}', window.location.origin);
            if (this.professionalId) {
                url.searchParams.set('profesional_id', this.professionalId);
            }
            if (this.technologistId) {
                url.searchParams.set('tecnologo_id', this.technologistId);
            }
            return url.toString();
        }
    };
}
</script>
@endsection
