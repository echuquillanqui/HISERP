@extends('layouts.app')

@section('content')
<div class="container py-4" x-data="serviceReportForm()">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark">Redacción de Informe</h2>
            <p class="text-muted mb-0">
                Paciente:
                <span class="fw-bold">{{ $detail->order->patient->first_name }} {{ $detail->order->patient->last_name }}</span>
                | Servicio: {{ $detail->name }}
            </p>
        </div>
        <div>
            <a href="{{ route('serviceresults.index') }}" class="btn btn-outline-secondary shadow-sm">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
            <button type="button" onclick="document.getElementById('formInforme').submit()" class="btn btn-primary shadow-sm">
                <i class="bi bi-save"></i> Guardar Informe Final
            </button>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form id="formInforme" action="{{ route('services.guardar', $detail->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="template_id" value="{{ $template->id }}">
                        <input type="hidden" name="resultados_json" :value="jsonPayload">

                        <textarea id="reportEditor" name="html_final" class="form-control font-monospace" rows="28">{!! $htmlContent !!}</textarea>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-3" x-show="fieldsSchema.length > 0">
            <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white fw-bold border-0">
                    Campos de la plantilla (JSON)
                </div>
                <div class="card-body">
                    <template x-for="field in fieldsSchema" :key="field.key">
                        <div class="mb-3">
                            <label class="form-label fw-bold mb-1" x-text="field.label"></label>

                            <template x-if="field.type === 'textarea'">
                                <textarea class="form-control" rows="3" x-model="fieldValues[field.key]"></textarea>
                            </template>

                            <template x-if="field.type === 'number'">
                                <input type="number" class="form-control" x-model="fieldValues[field.key]">
                            </template>

                            <template x-if="field.type === 'date'">
                                <input type="date" class="form-control" x-model="fieldValues[field.key]">
                            </template>

                            <template x-if="field.type === 'select'">
                                <select class="form-select" x-model="fieldValues[field.key]">
                                    <option value="">Seleccione...</option>
                                    <template x-for="option in field.options || []" :key="option">
                                        <option :value="option" x-text="option"></option>
                                    </template>
                                </select>
                            </template>

                            <template x-if="!['textarea', 'number', 'date', 'select'].includes(field.type)">
                                <input type="text" class="form-control" x-model="fieldValues[field.key]">
                            </template>

                            <small class="text-muted">Token: <code x-text="`@{{campo:${field.key}}}`"></code></small>
                        </div>
                    </template>

                    <button type="button" class="btn btn-outline-primary btn-sm w-100" @click="applyValuesToEditor()">
                        Aplicar campos al informe
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function serviceReportForm() {
        return {
            fieldsSchema: @json($fieldsSchema ?? []),
            fieldValues: @json($resultadosJson ?? []),
            get jsonPayload() {
                return JSON.stringify(this.fieldValues || {});
            },
            applyValuesToEditor() {
                const editor = document.getElementById('reportEditor');
                if (!editor) return;

                let html = editor.value;

                for (const [key, value] of Object.entries(this.fieldValues || {})) {
                    const safeValue = value ?? '';
                    const pattern = new RegExp(`\\{\\{campo:${key}\\}\\}`, 'g');
                    html = html.replace(pattern, safeValue);
                }

                editor.value = html;
            }
        }
    }
</script>
@endsection
