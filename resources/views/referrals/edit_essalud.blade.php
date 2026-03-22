@extends('layouts.app')

@section('content')
<style>
    /* Estilos de validación y Select2 */
    .is-invalid { border: 1px solid #dc3545 !important; }
    .is-valid { border: 1px solid #198754 !important; }
    .invalid-feedback { display: block; font-size: 0.7rem; font-weight: bold; }
    .select2-container--bootstrap-5 .select2-selection { border: 1px solid #ced4da !important; height: 38px !important; display: flex !important; align-items: center !important; }
    
    /* Colores Institucionales EsSalud */
    .card-header { background: #0056b3; color: white; font-weight: bold; border-bottom: 3px solid #00aae4; }
    .section-label { background: #e7f1ff; border-left: 4px solid #0056b3; padding: 5px 10px; font-weight: bold; margin-bottom: 15px; text-transform: uppercase; font-size: 0.85rem; color: #0056b3; }
    .snapshot-card { border: 1px solid #dee2e6; border-left: 5px solid #00aae4; background: #fff; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
    
    .data-title { font-size: 0.7rem; color: #6c757d; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
    .data-value { font-weight: bold; color: #333; }
    .btn-essalud { background: #0056b3; color: white; border: none; transition: 0.3s; }
    .btn-essalud:hover { background: #004494; color: white; box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
</style>

<div class="container-fluid px-4 py-3">
    <div class="mb-3">
        <a href="{{ route('referrals.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> ← Volver al Listado
        </a>
    </div>

    <form action="{{ route('referrals.update', $referral->id) }}" method="POST">
        @csrf
        <input type="hidden" name="coverage_type" value="ESSALUD">


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
            <div class="card-header py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-edit"></i> EDITAR REFERENCIA #{{ $referral->id }} - FORMATO ESSALUD</h5>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="small fw-bold text-white">ESTADO:</span>
                        <span class="badge bg-light text-dark fs-6">{{ $referral->status ?? 'REGISTRADO' }}</span>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="section-label">1. Identificación del Asegurado e IPRESS</div>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="data-title">Asegurado (DNI / Autogenerado)</label>
                        <select id="patient_search" name="patient_id" class="form-control @error('patient_id') is-invalid @enderror" required>
                            <option value="{{ $referral->patient_id }}" selected>
                                {{ trim(($referral->patient->first_name ?? '') . ' ' . ($referral->patient->last_name ?? '')) }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="data-title">IPRESS de Origen</label>
                        <input type="text" name="origin_facility" class="form-control bg-light" value="{{ $referral->origin_facility }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="data-title">Centro Asistencial Destino</label>
                        <input type="text" name="destination_facility" class="form-control @error('destination_facility') is-invalid @enderror" value="{{ old('destination_facility', $referral->destination_facility) }}" required>
                    </div>
                </div>

                {{-- El panel snapshot se llena vía JS al cargar --}}
                <div id="snapshot_panel" class="snapshot-card">
                    <div class="row g-3">
                        <div class="col-md-2"><label class="data-title">H.C. / Autogenerado</label><div class="data-value"><span id="v_hc"></span> / <span id="v_aff"></span></div></div>
                        <div class="col-md-2 text-center"><label class="data-title">Acreditación</label><div id="v_insured" class="data-value text-success"></div></div>
                        <div class="col-md-2 text-center"><label class="data-title">Tipo Seguro</label><div id="v_regime" class="data-value"></div></div>
                        <div class="col-md-3"><label class="data-title">Apellidos y Nombres</label><div id="v_name" class="data-value text-uppercase text-primary"></div></div>
                        <div class="col-md-2"><label class="data-title">Edad / Sexo</label><div class="data-value"><span id="v_age"></span> años / <span id="v_sex"></span></div></div>
                        <div class="col-12 mt-2 pt-2 border-top"><label class="data-title">Dirección Declarada</label><div id="v_address" class="data-value small text-muted"></div></div>
                    </div>
                </div>

                <div class="section-label">2. Resumen de Historia Clínica y Signos Vitales</div>
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="data-title">Anamnesis</label>
                        <textarea name="anamnesis" class="form-control" rows="2">{{ old('anamnesis', $referral->anamnesis) }}</textarea>
                    </div>
                    <div class="col-md-2"><label class="data-title">P.A. (mmHg)</label><input type="text" name="blood_pressure" class="form-control" value="{{ old('blood_pressure', $referral->blood_pressure) }}"></div>
                    <div class="col-md-2"><label class="data-title">F.C. (x')</label><input type="text" name="heart_rate" class="form-control" value="{{ old('heart_rate', $referral->heart_rate) }}"></div>
                    <div class="col-md-2"><label class="data-title">F.R. (x')</label><input type="text" name="respiratory_rate" class="form-control" value="{{ old('respiratory_rate', $referral->respiratory_rate) }}"></div>
                    <div class="col-md-2"><label class="data-title">T° (°C)</label><input type="text" name="temperature" class="form-control" value="{{ old('temperature', $referral->temperature) }}"></div>
                    <div class="col-md-2"><label class="data-title">Sat.O2 (%)</label><input type="text" name="oxygen_saturation" class="form-control" value="{{ old('oxygen_saturation', $referral->oxygen_saturation) }}"></div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6"><label class="data-title">Estado general:</label><input type="text" name="general_state" class="form-control" value="{{ old('general_state', $referral->general_state) }}"></div>
                    <div class="col-md-6"><label class="data-title">Aparato Respiratorio</label><input type="text" name="lungs" class="form-control" value="{{ old('lungs', $referral->lungs) }}"></div>
                    <div class="col-md-6"><label class="data-title">Cardiovascular</label><input type="text" name="cardiovascular" class="form-control" value="{{ old('cardiovascular', $referral->cardiovascular) }}"></div>
                    <div class="col-md-6"><label class="data-title">Otros</label><input type="text" name="others" class="form-control" value="{{ old('others', $referral->others) }}"></div>
                    {{-- <div class="col-md-6"><label class="data-title">Piel y TCSC</label><input type="text" name="skin_subcutaneous" class="form-control" value="{{ old('skin_subcutaneous', $referral->skin_subcutaneous) }}"></div>
                    <div class="col-md-6"><label class="data-title">Neurológico</label><input type="text" name="neurological" class="form-control" value="{{ old('neurological', $referral->neurological) }}"></div> --}}
                    <div class="col-12">
                        <label class="data-title">Examenes auxiliares</label>
                        <textarea name="auxiliary_exams" class="form-control" rows="2">{{ old('auxiliary_exams', $referral->auxiliary_exams) }}</textarea>
                    </div>
                </div>
                <div class="section-label">3. Diagnósticos (CIE-10)</div>
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
                    <button type="button" @click="rows.push({ icd_10_code: '', diagnosis: '', D: false, P: false, R: false })" class="btn btn-sm btn-outline-primary">+ Agregar Diagnóstico</button>

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

                <div class="section-label mt-5">4. Datos de la Referencia y Responsables</div>
                <div class="row g-3">
                    <div class="col-md-3"><label class="data-title">Fecha Cita</label><input type="date" name="appointment_date" class="form-control @error('appointment_date') is-invalid @enderror" value="{{ old('appointment_date', $referral->appointment_date) }}"></div>
                    <div class="col-md-3"><label class="data-title">Hora Cita</label><input type="time" name="appointment_time" class="form-control @error('appointment_time') is-invalid @enderror" value="{{ old('appointment_time', $referral->appointment_time ? \Carbon\Carbon::parse($referral->appointment_time)->format('H:i') : null) }}"></div>
                    <div class="col-md-3"><label class="data-title">Nombre de quien atenderá</label><input type="text" name="attending_physician_name" class="form-control @error('attending_physician_name') is-invalid @enderror" value="{{ old('attending_physician_name', $referral->attending_physician_name) }}"></div>
                    <div class="col-md-3"><label class="data-title">Coordinado con</label><input type="text" name="coordination_name" class="form-control @error('coordination_name') is-invalid @enderror" value="{{ old('coordination_name', $referral->coordination_name) }}"></div>
                    <div class="col-md-3">
                        <label class="data-title">Prioridad de Referencia</label>
                        <select name="referral_type" class="form-select @error('referral_type') is-invalid @enderror" required>
                            <option value="EMERGENCIA" {{ old('referral_type', $referral->referral_type) == 'EMERGENCIA' ? 'selected' : '' }}>I - EMERGENCIA</option>
                            <option value="CONSULTA EXTERNA" {{ old('referral_type', $referral->referral_type) == 'CONSULTA EXTERNA' ? 'selected' : '' }}>II - CONSULTA EXTERNA</option>
                            <option value="APOYO AL DX" {{ old('referral_type', $referral->referral_type) == 'APOYO AL DX' ? 'selected' : '' }}>III - APOYO AL DIAGNÓSTICO</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="data-title">Especialidad Destino</label>
                        <input type="text" name="destination_specialty" class="form-control @error('destination_specialty') is-invalid @enderror" required value="{{ old('destination_specialty', $referral->destination_specialty) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="data-title">Condición de Inicio</label>
                        <select name="patient_condition" class="form-select @error('patient_condition') is-invalid @enderror" required>
                            <option value="ESTABLE" {{ old('patient_condition', $referral->patient_condition) == 'ESTABLE' ? 'selected' : '' }}>ESTABLE</option>
                            <option value="MAL ESTADO" {{ old('patient_condition', $referral->patient_condition) == 'MAL ESTADO' ? 'selected' : '' }}>MAL ESTADO</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="data-title">Condición de Llegada</label>
                        <select name="arrival_condition" class="form-select @error('arrival_condition') is-invalid @enderror" required>
                            <option value="ESTABLE" {{ old('arrival_condition', $referral->arrival_condition) == 'ESTABLE' ? 'selected' : '' }}>ESTABLE</option>
                            <option value="MAL ESTADO" {{ old('arrival_condition', $referral->arrival_condition) == 'MAL ESTADO' ? 'selected' : '' }}>MAL ESTADO</option>
                            <option value="FALLECIDO" {{ old('arrival_condition', $referral->arrival_condition) == 'FALLECIDO' ? 'selected' : '' }}>FALLECIDO</option>
                        </select>
                    </div>

                    @php
                        $responsibles = [
                            'referral_responsible_id' => 'Médico Tratante',
                            'facility_responsible_id' => 'Jefe de IPRESS',
                            'escort_staff_id' => 'Personal Acompañante',
                            'receiving_staff_id' => 'Responsable Recepción'
                        ];
                    @endphp

                    @foreach($responsibles as $name => $label)
                    <div class="col-md-3">
                        <label class="data-title">{{ $label }}</label>
                        <select name="{{ $name }}" class="form-select @error($name) is-invalid @enderror" {{ in_array($name, ['referral_responsible_id', 'facility_responsible_id']) ? 'required' : '' }}>
                            <option value="">Seleccione personal...</option>
                            @foreach($staff as $user) 
                                <option value="{{ $user->id }}" {{ old($name, $referral->$name) == $user->id ? 'selected':'' }}>
                                    {{ $user->name }}
                                </option> 
                            @endforeach
                        </select>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="card-footer bg-light p-4 text-center">
                <button type="submit" class="btn btn-essalud btn-lg px-5 shadow">
                    <i class="fas fa-sync-alt"></i> ACTUALIZAR REFERENCIA
                </button>
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
        // Función para llenar el panel de datos del paciente
        function updateSnapshot(p) {
            if(!p) return;
            $('#snapshot_panel').removeClass('d-none');
            $('#v_hc').text(p.medical_history_number || p.dni || 'S/N');
            $('#v_aff').text(p.affiliation_code || p.dni);
            $('#v_insured').text(p.is_insured ? 'SÍ (ACTIVA)' : 'NO ACREDITADO');
            $('#v_regime').text(p.insurance_regime || 'TITULAR');
            const fullName = `${p.first_name || ''} ${p.last_name || ''}`.replace(/\s+/g, ' ').trim();
            $('#v_name').text(fullName.toUpperCase());
            $('#v_age').text(p.age || '-');
            $('#v_sex').text(p.gender == 'F' ? 'FEMENINO' : 'MASCULINO');
            $('#v_address').text(`${p.address || ''} - ${p.district || ''}`.toUpperCase());
        }

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
            updateSnapshot(e.params.data);
        });

        // Cargar datos del paciente actual al iniciar (PHP a JS)
        const currentPatient = @json($referral->patient);
        if(currentPatient) {
            updateSnapshot(currentPatient);
        }
    });
</script>
@endpush
@endsection
