@extends('layouts.app')

@section('content')
<div class="container py-4" x-data="signatureSelector({
    defaultDoctorId: '{{ $selectedDoctorId }}',
    defaultSignerId: '{{ $selectedSignerId }}'
})">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-primary text-white d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0"><i class="bi bi-person-circle me-2"></i>{{ $resultado->patient->first_name }} {{ $resultado->patient->last_name }}</h4>
                <small class="opacity-75">Orden: {{ $resultado->code }} | DNI: {{ $resultado->patient->dni }}</small>
            </div>
            <div class="text-end"><span class="badge bg-white text-primary px-3 py-2">MÓDULO TOMOGRAFÍA</span></div>
        </div>
    </div>

    <form action="{{ route('tomography-results.update', $resultado) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light py-3">
                <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-pen me-2"></i>Datos del informe</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Fecha de resultado</label>
                        <input type="date" name="result_date" class="form-control" value="{{ old('result_date', optional($result?->result_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Cantidad de placas usadas</label>
                        <input type="number" min="0" name="plates_used" class="form-control" value="{{ old('plates_used', $result->plates_used ?? $suggestedPlates) }}" required>
                        <small class="text-muted">Sugerido por orden: {{ $suggestedPlates }} (editable).</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Marca de iopamidol</label>
                        <select name="iopamidol_brand_id" class="form-select @error('iopamidol_brand_id') is-invalid @enderror">
                            <option value="">Sin contraste</option>
                            @foreach($iopamidolBrands as $brand)
                                <option value="{{ $brand->id }}" {{ (string) old('iopamidol_brand_id', $result->iopamidol_brand_id ?? '') === (string) $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('iopamidol_brand_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Presentación de iopamidol</label>
                        <select name="iopamidol_presentation_ml" class="form-select @error('iopamidol_presentation_ml') is-invalid @enderror">
                            <option value="">Sin contraste</option>
                            <option value="50" {{ (string) old('iopamidol_presentation_ml', $result->iopamidol_presentation_ml ?? '') === '50' ? 'selected' : '' }}>50 ml</option>
                            <option value="100" {{ (string) old('iopamidol_presentation_ml', $result->iopamidol_presentation_ml ?? '') === '100' ? 'selected' : '' }}>100 ml</option>
                        </select>
                        @error('iopamidol_presentation_ml')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Frascos de iopamidol usados</label>
                        <input type="number" min="0" name="iopamidol_units" class="form-control @error('iopamidol_units') is-invalid @enderror" value="{{ old('iopamidol_units', $result->iopamidol_units ?? 0) }}">
                        @error('iopamidol_units')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Médico solicitante</label>
                        <input type="text" class="form-control" list="doctorsList" placeholder="Buscar médico..." x-model="doctorName" @input="syncDoctorId">
                        <datalist id="doctorsList">
                            @foreach($professionals as $doctor)
                                <option value="{{ $doctor->name }}"></option>
                            @endforeach
                        </datalist>
                        <input type="hidden" name="requesting_doctor_id" x-model="doctorId">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Firma informe (tecnólogo/radiólogo)</label>
                        <input type="text" class="form-control" list="signersList" placeholder="Buscar responsable..." x-model="signerName" @input="syncSignerId">
                        <datalist id="signersList">
                            @foreach($technologists as $signer)
                                <option value="{{ $signer->name }}"></option>
                            @endforeach
                        </datalist>
                        <input type="hidden" name="report_signer_id" x-model="signerId">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Descripción general</label>
                        <textarea name="general_description" class="form-control" rows="2">{{ old('general_description', $result->general_description ?? '') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Descripción del resultado</label>
                        <textarea name="result_description" class="form-control" rows="6">{{ old('result_description', $result->result_description ?? '') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Conclusión</label>
                        <textarea name="conclusion" class="form-control" rows="3">{{ old('conclusion', $result->conclusion ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="sticky-bottom bg-white p-3 border-top shadow-lg d-flex justify-content-end gap-2">
            <a href="{{ route('tomography-results.index') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
            @if($result)
                <a href="{{ route('tomography-results.show', $resultado) }}" target="_blank" class="btn btn-outline-danger px-4">
                    <i class="bi bi-file-earmark-pdf me-2"></i>Ver PDF
                </a>
            @endif
            <button type="submit" class="btn btn-primary px-5 fw-bold">
                <i class="bi bi-check-all me-2"></i> GUARDAR RESULTADO
            </button>
        </div>
    </form>
</div>

<script>
function signatureSelector(config) {
    const doctors = @json($professionals->map(fn ($user) => ['id' => (string) $user->id, 'name' => $user->name])->values());
    const signers = @json($technologists->map(fn ($user) => ['id' => (string) $user->id, 'name' => $user->name])->values());

    const defaultDoctor = doctors.find(user => user.id === config.defaultDoctorId) || null;
    const defaultSigner = signers.find(user => user.id === config.defaultSignerId) || null;

    return {
        doctorId: defaultDoctor?.id || '',
        doctorName: defaultDoctor?.name || '',
        signerId: defaultSigner?.id || '',
        signerName: defaultSigner?.name || '',

        syncDoctorId() {
            const found = doctors.find(user => user.name === this.doctorName);
            this.doctorId = found ? found.id : '';
        },
        syncSignerId() {
            const found = signers.find(user => user.name === this.signerName);
            this.signerId = found ? found.id : '';
        }
    }
}
</script>
@endsection
