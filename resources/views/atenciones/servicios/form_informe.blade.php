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
            <button type="button" onclick="submitInformeForm()" class="btn btn-primary shadow-sm">
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

                        <textarea id="tinyEditor" name="html_final" class="form-control font-monospace" rows="28">{!! $htmlContent !!}</textarea>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-3" x-show="fieldsSchema.length > 0">
            <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white fw-bold border-0">
                    Campos para completar el informe
                </div>
                <div class="card-body">
                    <template x-for="field in fieldsSchema" :key="field.key">
                        <div class="mb-3">
                            <label class="form-label fw-bold mb-1" x-text="field.label"></label>

                            <template x-if="field.type === 'textarea'">
                                <textarea class="form-control" rows="3" x-model="fieldValues[field.key]" :required="field.required"></textarea>
                            </template>

                            <template x-if="field.type === 'number'">
                                <input type="number" class="form-control" x-model="fieldValues[field.key]" :required="field.required">
                            </template>

                            <template x-if="field.type === 'date'">
                                <input type="date" class="form-control" x-model="fieldValues[field.key]" :required="field.required">
                            </template>

                            <template x-if="field.type === 'time'">
                                <input type="time" class="form-control" x-model="fieldValues[field.key]" :required="field.required">
                            </template>

                            <template x-if="field.type === 'datetime-local'">
                                <input type="datetime-local" class="form-control" x-model="fieldValues[field.key]" :required="field.required">
                            </template>

                            <template x-if="field.type === 'email'">
                                <input type="email" class="form-control" x-model="fieldValues[field.key]" :required="field.required">
                            </template>

                            <template x-if="field.type === 'tel'">
                                <input type="tel" class="form-control" x-model="fieldValues[field.key]" :required="field.required">
                            </template>

                            <template x-if="field.type === 'checkbox'">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" :id="`field_${field.key}`" x-model="fieldValues[field.key]">
                                    <label class="form-check-label" :for="`field_${field.key}`">Sí</label>
                                </div>
                            </template>

                            <template x-if="field.type === 'select'">
                                <select class="form-select" x-model="fieldValues[field.key]" :required="field.required">
                                    <option value="">Seleccione...</option>
                                    <template x-for="option in field.options || []" :key="option">
                                        <option :value="option" x-text="option"></option>
                                    </template>
                                </select>
                            </template>

                            <template x-if="!['textarea', 'number', 'date', 'time', 'datetime-local', 'email', 'tel', 'checkbox', 'select'].includes(field.type)">
                                <input type="text" class="form-control" x-model="fieldValues[field.key]" :required="field.required">
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
            getEditorHtml() {
                const instance = tinymce.get('tinyEditor');
                if (instance) {
                    return instance.getContent();
                }

                const editor = document.getElementById('tinyEditor');
                return editor ? editor.value : '';
            },
            setEditorHtml(content) {
                const instance = tinymce.get('tinyEditor');
                if (instance) {
                    instance.setContent(content ?? '');
                    return;
                }

                const editor = document.getElementById('tinyEditor');
                if (editor) {
                    editor.value = content ?? '';
                }
            },
            applyValuesToEditor() {
                let html = this.getEditorHtml();

                for (const [key, value] of Object.entries(this.fieldValues || {})) {
                    const safeValue = typeof value === 'boolean' ? (value ? 'Sí' : 'No') : (value ?? '');
                    const pattern = new RegExp(`\\{\\{campo:${key}\\}\\}`, 'g');
                    html = html.replace(pattern, safeValue);
                }

                this.setEditorHtml(html);
            }
        }
    }
</script>

<script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
<script>
    function submitInformeForm() {
        tinymce.triggerSave();
        document.getElementById('formInforme').submit();
    }

    document.addEventListener('DOMContentLoaded', function () {
        tinymce.init({
            selector: '#tinyEditor',
            license_key: 'gpl',
            height: 560,
            menubar: 'file edit view insert format table tools',
            plugins: 'advlist autolink lists link charmap preview searchreplace visualblocks code fullscreen table autoresize',
            toolbar: 'undo redo | blocks styles | bold italic underline | alignleft aligncenter alignright alignjustify | outdent indent | bullist numlist | table | removeformat code',
            style_formats: [
                {
                    title: 'Márgenes de párrafo',
                    items: [
                        { title: 'Sin margen', format: 'margin_none' },
                        { title: 'Margen pequeño', format: 'margin_sm' },
                        { title: 'Margen mediano', format: 'margin_md' },
                        { title: 'Margen grande', format: 'margin_lg' }
                    ]
                }
            ],
            formats: {
                margin_none: { block: 'p', styles: { margin: '0' } },
                margin_sm: { block: 'p', styles: { margin: '0.5rem 0' } },
                margin_md: { block: 'p', styles: { margin: '1rem 0' } },
                margin_lg: { block: 'p', styles: { margin: '1.5rem 0' } }
            },
            content_style: 'body { font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.45; } p { margin: 0.5rem 0; }',
            setup: function (editor) {
                editor.on('change keyup', function () {
                    editor.save();
                });
            }
        });
    });
</script>
@endsection
