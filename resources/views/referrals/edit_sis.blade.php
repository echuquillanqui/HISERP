@extends('layouts.app')

@section('content')
<style>
    /* ... (Mantén tus estilos actuales igual) ... */
    .is-invalid { border: 1px solid #dc3545 !important; }
    .is-valid { border: 1px solid #198754 !important; }
    .invalid-feedback { display: block; font-size: 0.7rem; font-weight: bold; }
    .select2-container--bootstrap-5 .select2-selection { border: 1px solid #ced4da !important; height: 38px !important; display: flex !important; align-items: center !important; }
    .card-header { background: #198754; color: white; font-weight: bold; }
    .section-label { background: #f8f9fa; border-left: 4px solid #198754; padding: 5px 10px; font-weight: bold; margin-bottom: 15px; text-transform: uppercase; font-size: 0.85rem; }
    .snapshot-card { border: 1px solid #dee2e6; border-left: 5px solid #198754; background: #fff; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
    .data-title { font-size: 0.7rem; color: #6c757d; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
    .data-value { font-weight: bold; color: #333; }
</style>

<div class="container-fluid px-4 py-3">
    <div class="mb-3">
        <a href="{{ route('referrals.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> ← Volver al Listado
        </a>
    </div>

    {{-- 1. CAMBIO DE RUTA Y MÉTODO --}}
    <form action="{{ route('referrals.update', $referral->id) }}" method="POST">
        @csrf
         @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Se encontraron errores en el formulario:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @method('PUT')
        
        <div class="card shadow-sm border-0">
            <div class="card-header bg-success text-white py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0 fw-bold">EDITAR REFERENCIA #{{ $referral->id }} - FORMATO SIS</h5>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="small fw-bold">TIPO SEGURO:</span>
                        <span class="badge bg-white text-success fs-6 p-2 px-3">SIS</span>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="section-label">1. Identificación del Paciente</div>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="data-title">Paciente</label>
                        {{-- Select2 cargará el paciente actual mediante JS --}}
                        <select id="patient_search" name="patient_id" class="form-control @error('patient_id') is-invalid @enderror" required>
                            <option value="{{ $referral->patient_id }}" selected>
                                {{ $referral->patient->full_name ?? $referral->patient->surname . ' ' . $referral->patient->last_name . ', ' . $referral->patient->first_name }} {{ $referral->patient->other_names }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="data-title">Establecimiento de Origen</label>
                        <input type="text" name="origin_facility" class="form-control bg-light" value="{{ $referral->origin_facility }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="data-title">Establecimiento Destino</label>
                        <input type="text" name="destination_facility" class="form-control @error('destination_facility') is-invalid @enderror" value="{{ old('destination_facility', $referral->destination_facility) }}" required>
                    </div>
                </div>

                {{-- Panel de información (visible por defecto en edición) --}}
                <div id="snapshot_panel" class="snapshot-card">
                    <div class="row g-3">
                        <div class="col-md-2"><label class="data-title">H.C. / Código SIS</label><div class="data-value"><span id="v_hc">{{ $referral->patient->medical_history_number }}</span> / <span id="v_aff">{{ $referral->patient->affiliation_code }}</span></div></div>
                        <div class="col-md-2 text-center"><label class="data-title">¿Asegurado?</label><div id="v_insured" class="data-value">{{ $referral->patient->is_insured ? 'SÍ' : 'NO' }}</div></div>
                        <div class="col-md-2 text-center"><label class="data-title">Regimen</label><div id="v_regime" class="data-value">{{ $referral->patient->insurance_regime }}</div></div>
                        <div class="col-md-3"><label class="data-title">Nombres y Apellidos</label><div id="v_name" class="data-value text-uppercase">{{ $referral->patient->surname }} {{ $referral->patient->last_name }}, {{ $referral->patient->first_name }} {{ $referral->patient->other_names }}</div></div>
                        <div class="col-md-2"><label class="data-title">Edad / Sexo</label><div class="data-value"><span id="v_age">{{ $referral->patient->age }}</span> años / <span id="v_sex">{{ $referral->patient->gender == 'F' ? 'F' : 'M' }}</span></div></div>
                    </div>
                </div>

                <div class="section-label">2. Examen Clínico</div>
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="data-title">Anamnesis</label>
                        <textarea name="anamnesis" class="form-control" rows="3">{{ old('anamnesis', $referral->anamnesis) }}</textarea>
                    </div>
                    <div class="col-md-3"><label class="data-title">PA</label><input type="text" name="blood_pressure" class="form-control" value="{{ old('blood_pressure', $referral->blood_pressure) }}"></div>
                    <div class="col-md-2"><label class="data-title">T°</label><input type="text" name="temperature" class="form-control" value="{{ old('temperature', $referral->temperature) }}"></div>
                    <div class="col-md-2"><label class="data-title">FR</label><input type="text" name="respiratory_rate" class="form-control" value="{{ old('respiratory_rate', $referral->respiratory_rate) }}"></div>
                    <div class="col-md-2"><label class="data-title">FC</label><input type="text" name="heart_rate" class="form-control" value="{{ old('heart_rate', $referral->heart_rate) }}"></div>
                    <div class="col-md-3"><label class="data-title">SAT</label><input type="text" name="oxygen_saturation" class="form-control" value="{{ old('oxygen_saturation', $referral->oxygen_saturation) }}"></div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6"><label class="data-title">Estado General</label><input type="text" name="general_state" class="form-control" value="{{ old('general_state', $referral->general_state) }}"></div>
                    <div class="col-md-6"><label class="data-title">Piel y TCSC</label><input type="text" name="skin_subcutaneous" class="form-control" value="{{ old('skin_subcutaneous', $referral->skin_subcutaneous) }}"></div>
                    <div class="col-md-6"><label class="data-title">Pulmones</label><input type="text" name="lungs" class="form-control" value="{{ old('lungs', $referral->lungs) }}"></div>
                    <div class="col-md-6"><label class="data-title">Cardiovascular</label><input type="text" name="cardiovascular" class="form-control" value="{{ old('cardiovascular', $referral->cardiovascular) }}"></div>
                    <div class="col-md-6"><label class="data-title">Neurológico</label><input type="text" name="neurological" class="form-control" value="{{ old('neurological', $referral->neurological) }}"></div>
                    <div class="col-md-6"><label class="data-title">Otros</label><input type="text" name="others" class="form-control" value="{{ old('others', $referral->others) }}"></div>
                </div>

                <div class="section-label">Exámenes Auxiliares</div>
                <div class="row mb-4">
                    <div class="col-12">
                        <textarea name="auxiliary_exams" class="form-control" rows="2">{{ old('auxiliary_exams', $referral->auxiliary_exams) }}</textarea>
                    </div>
                </div>
                <div class="section-label">3. Diagnósticos</div>
                <div x-data="diagnosisTreatmentForm({
                    diagnoses: {{ json_encode(old('diagnoses', $referral->diagnosisTreatments->map(fn($item) => ['icd_10_code' => $item->icd_10_code, 'diagnosis' => $item->diagnosis, 'D' => $item->D === 'X', 'P' => $item->P === 'X', 'R' => $item->R === 'X'])->values()->all() ?: [['icd_10_code' => 'N18.9', 'diagnosis' => 'INSUFICIENCIA RENAL CRÓNICA TERMINAL', 'D' => true, 'P' => false, 'R' => false]])) }},
                    treatments: {{ json_encode(old('treatments', $referral->treatments ?? [''])) }}
                })" x-init="init()">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light text-center small">
                            <tr><th width="20%">CIE-10</th><th width="58%">Diagnóstico</th><th width="4%">D</th><th width="4%">P</th><th width="4%">R</th><th width="5%"></th></tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in rows" :key="index">
                                <tr>
                                    <td>
                                        <input type="text" :name="`diagnoses[${index}][icd_10_code]`" x-model="row.icd_10_code" class="form-control form-control-sm cie-code-input" :data-row="index" list="cie-options" placeholder="Código CIE-10">
                                    </td>
                                    <td>
                                        <input type="text" :name="`diagnoses[${index}][diagnosis]`" x-model="row.diagnosis" class="form-control form-control-sm cie-desc-input" :data-row="index" list="cie-options" placeholder="Descripción CIE-10">
                                    </td>
                                    <td class="text-center"><input type="checkbox" :name="`diagnoses[${index}][D]`" value="X" :checked="row.D"></td>
                                    <td class="text-center"><input type="checkbox" :name="`diagnoses[${index}][P]`" value="X" :checked="row.P"></td>
                                    <td class="text-center"><input type="checkbox" :name="`diagnoses[${index}][R]`" value="X" :checked="row.R"></td>
                                    <td class="text-center"><button type="button" @click="rows.splice(index, 1)" class="btn btn-sm text-danger" x-show="rows.length > 1">×</button></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <button type="button" @click="rows.push({ icd_10_code: '', diagnosis: '', D: false, P: false, R: false })" class="btn btn-sm btn-outline-success">+ Agregar Diagnóstico</button>

                    <div class="section-label mt-4">Tratamiento / Recomendaciones</div>
                    <template x-for="(treatment, tIndex) in treatments" :key="`tr-${tIndex}`">
                        <div class="d-flex gap-2 mb-2">
                            <input type="text" :name="`treatments[${tIndex}]`" x-model="treatments[tIndex]" class="form-control form-control-sm" placeholder="Tratamiento o recomendación (opcional)">
                            <button type="button" class="btn btn-sm btn-outline-danger" @click="treatments.splice(tIndex, 1)" x-show="treatments.length > 1">×</button>
                        </div>
                    </template>
                    <button type="button" @click="treatments.push('')" class="btn btn-sm btn-outline-secondary">+ Agregar Tratamiento</button>
                    <datalist id="cie-options"></datalist>
                </div>

                <div class="section-label mt-5">4. Datos de Referencia</div>
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="data-title">Tipo de Referencia</label>
                        <select name="referral_type" class="form-select @error('referral_type') is-invalid @enderror" required>
                            @foreach(['EMERGENCIA', 'CONSULTA EXTERNA', 'APOYO AL DX'] as $type)
                                <option value="{{ $type }}" {{ old('referral_type', $referral->referral_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2"><label class="data-title">Fecha Cita</label><input type="date" name="appointment_date" class="form-control" value="{{ old('appointment_date', $referral->appointment_date) }}"></div>
                    <div class="col-md-2"><label class="data-title">Hora Cita</label><input type="time" name="appointment_time" class="form-control" value="{{ old('appointment_time', $referral->appointment_time ? \Carbon\Carbon::parse($referral->appointment_time)->format('H:i') : null) }}"></div>
                    <div class="col-md-3"><label class="data-title">Atenderá</label><input type="text" name="attending_physician_name" class="form-control" value="{{ old('attending_physician_name', $referral->attending_physician_name) }}"></div>
                    <div class="col-md-3"><label class="data-title">Coordinado con</label><input type="text" name="coordination_name" class="form-control" value="{{ old('coordination_name', $referral->coordination_name) }}"></div>
                    <div class="col-md-6"><label class="data-title">Especialidad Destino</label><input type="text" name="destination_specialty" class="form-control @error('destination_specialty') is-invalid @enderror" required value="{{ old('destination_specialty', $referral->destination_specialty) }}"></div>
                    
                    <div class="col-md-3">
                        <label class="data-title">Condición Inicio</label>
                        <select name="patient_condition" class="form-select @error('patient_condition') is-invalid @enderror" required>
                        <option value="ESTABLE" {{ old('patient_condition', $referral->patient_condition) == 'ESTABLE' ? 'selected' : '' }}>ESTABLE</option>
                        <option value="MAL ESTADO" {{ old('patient_condition', $referral->patient_condition) == 'MAL ESTADO' ? 'selected' : '' }}>MAL ESTADO</option>
                    </select></div>

                    <div class="col-md-3"><label class="data-title">Condición Llegada</label><select name="arrival_condition" class="form-select @error('arrival_condition') is-invalid @enderror" required>
                        <option value="ESTABLE" {{ old('arrival_condition', $referral->arrival_condition) == 'ESTABLE' ? 'selected' : '' }}>ESTABLE</option>
                        <option value="MAL ESTADO" {{ old('arrival_condition', $referral->arrival_condition) == 'MAL ESTADO' ? 'selected' : '' }}>MAL ESTADO</option>
                        <option value="FALLECIDO" {{ old('arrival_condition', $referral->arrival_condition) == 'FALLECIDO' ? 'selected' : '' }}>FALLECIDO</option>
                    </select></div>
                    
                    @php
                        $staff_fields = [
                            'referral_responsible_id' => 'Responsable RF',
                            'facility_responsible_id' => 'Resp. Establ.',
                            'escort_staff_id' => 'Acompañante',
                            'receiving_staff_id' => 'Recibe'
                        ];
                    @endphp

                    @foreach($staff_fields as $field => $label)
                    <div class="col-md-3">
                        <label class="data-title">{{ $label }}</label>
                        <select name="{{ $field }}" class="form-select @error($field) is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach($staff as $user)
                                <option value="{{ $user->id }}" {{ old($field, $referral->$field) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="card-footer bg-light p-4 text-end">
                <button type="submit" class="btn btn-primary btn-lg px-5">ACTUALIZAR REFERENCIA SIS</button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    
            window.diagnosisTreatmentForm = function(config) {
                return {
                    rows: config.diagnoses?.length ? config.diagnoses : [{ icd_10_code: '', diagnosis: '', D: false, P: false, R: false }],
                    treatments: config.treatments?.length ? config.treatments : [''],
                    cieResults: [],
                    init() {
                        this.bindCieSearch();
                    },
                    bindCieSearch() {
                        const fetchCie = async (term) => {
                            if (!term || term.length < 2) return [];
                            const response = await fetch(`{{ route('referrals.cie10.search') }}?q=${encodeURIComponent(term)}`);
                            return await response.json();
                        };

                        document.addEventListener('input', async (event) => {
                            const target = event.target;
                            if (!target.classList.contains('cie-code-input') && !target.classList.contains('cie-desc-input')) {
                                return;
                            }

                            const rowIndex = Number(target.dataset.row);
                            const list = document.getElementById('cie-options');
                            const results = await fetchCie(target.value);
                            this.cieResults = results;
                            list.innerHTML = results.map(item => `<option value="${target.classList.contains('cie-code-input') ? item.codigo : item.descripcion}"></option>`).join('');

                            const selected = results.find(item =>
                                item.codigo?.toLowerCase() === target.value.toLowerCase() ||
                                item.descripcion?.toLowerCase() === target.value.toLowerCase()
                            );

                            if (selected && this.rows[rowIndex]) {
                                this.rows[rowIndex].icd_10_code = selected.codigo;
                                this.rows[rowIndex].diagnosis = selected.descripcion;
                            }
                        }, { passive: true });
                    }
                }
            }

    $(document).ready(function() {
        // Inicializar Select2
        $('#patient_search').select2({
            theme: 'bootstrap-5',
            ajax: { 
                url: "{{ route('referrals.patients.search') }}", 
                dataType: 'json', 
                delay: 300, 
                data: params => ({ q: params.term }), 
                processResults: data => data 
            },
            minimumInputLength: 2
        }).on('select2:select', function (e) {
            let p = e.params.data;
            $('#snapshot_panel').removeClass('d-none');
            $('#v_hc').text(p.medical_history_number || 'S/N');
            $('#v_aff').text(p.affiliation_code || 'S/C');
            $('#v_insured').text(p.is_insured ? 'SÍ' : 'NO');
            $('#v_regime').text(p.insurance_regime || 'SUBSIDIADO');
            const fullName = `${p.surname || ''} ${p.last_name || ''}, ${p.first_name || ''}`.replace(/\s+/g, ' ').replace(' ,', ',').trim();
            $('#v_name').text(fullName.toUpperCase());
            $('#v_age').text(p.age || '-');
            $('#v_sex').text(p.gender == 'F' ? 'FEMENINO' : 'MASCULINO');
        });
    });
</script>
@endpush
@endsection
