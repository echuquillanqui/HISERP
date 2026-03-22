@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" x-data="templateRenderer()" x-init="init()">
    <div class="d-flex justify-content-between align-items-center mb-3 px-2">
        <div>
            <h3 class="fw-bold mb-1">Rellenar plantilla: {{ $template->nombre_plantilla }}</h3>
            <p class="text-muted mb-0">Completa campos, visualiza el resultado en HTML e imprime en formato A4.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('templates.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
            <button class="btn btn-primary" @click="printDocument()"><i class="bi bi-printer"></i> Imprimir plantilla</button>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4" x-show="fieldsSchema.length > 0">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold">Campos generados</div>
                <div class="card-body">
                    <template x-for="field in fieldsSchema" :key="field.key">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" x-text="field.label"></label>

                            <template x-if="field.type === 'textarea'"><textarea class="form-control" rows="3" x-model="values[field.key]" @input="renderHtml()"></textarea></template>
                            <template x-if="field.type === 'number'"><input type="number" class="form-control" x-model="values[field.key]" @input="renderHtml()"></template>
                            <template x-if="field.type === 'date'"><input type="date" class="form-control" x-model="values[field.key]" @input="renderHtml()"></template>
                            <template x-if="field.type === 'time'"><input type="time" class="form-control" x-model="values[field.key]" @input="renderHtml()"></template>
                            <template x-if="field.type === 'datetime-local'"><input type="datetime-local" class="form-control" x-model="values[field.key]" @input="renderHtml()"></template>
                            <template x-if="field.type === 'email'"><input type="email" class="form-control" x-model="values[field.key]" @input="renderHtml()"></template>
                            <template x-if="field.type === 'tel'"><input type="tel" class="form-control" x-model="values[field.key]" @input="renderHtml()"></template>
                            <template x-if="field.type === 'checkbox'"><div class="form-check"><input type="checkbox" class="form-check-input" x-model="values[field.key]" @change="renderHtml()"><label class="form-check-label">Sí</label></div></template>
                            <template x-if="field.type === 'select'"><select class="form-select" x-model="values[field.key]" @change="renderHtml()"><option value="">Seleccione...</option><template x-for="opt in field.options || []" :key="opt"><option :value="opt" x-text="opt"></option></template></select></template>
                            <template x-if="!['textarea','number','date','time','datetime-local','email','tel','checkbox','select'].includes(field.type)"><input type="text" class="form-control" x-model="values[field.key]" @input="renderHtml()"></template>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div :class="fieldsSchema.length ? 'col-lg-8' : 'col-12'">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Documento final (HTML)</div>
                <div class="card-body bg-light">
                    <div class="template-preview-sheet" x-ref="preview"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function templateRenderer() {
    return {
        templateHtml: @js($template->html_content),
        fieldsSchema: @json($fieldsSchema ?? []),
        values: {},
        init() {
            this.fieldsSchema.forEach(field => {
                this.values[field.key] = field.type === 'checkbox' ? false : '';
            });
            this.renderHtml();
        },
        renderHtml() {
            let html = this.templateHtml;

            Object.entries(this.values).forEach(([key, value]) => {
                const printable = typeof value === 'boolean' ? (value ? 'Sí' : 'No') : (value ?? '');
                html = html.replace(new RegExp(`\\{\\{campo:${key}\\}\\}`, 'g'), printable);
            });

            this.$refs.preview.innerHTML = html;
        },
        printDocument() {
            const printable = this.$refs.preview?.innerHTML || '';
            const popup = window.open('', '_blank', 'width=900,height=900');
            if (!popup) return;

            popup.document.write(`
                <html>
                    <head>
                        <title>Impresión de plantilla</title>
                        <style>
                            body { background:#f1f3f5; margin:0; padding:16px; }
                            .sheet { background:#fff; width:210mm; min-height:297mm; margin:0 auto; padding:15mm; box-sizing:border-box; box-shadow:0 0 8px rgba(0,0,0,.15); font-family:'Times New Roman', serif; font-size:12pt; line-height:1.3; }
                            @media print { body { background:#fff; padding:0; } .sheet { width:auto; min-height:auto; margin:0; box-shadow:none; padding:0; } }
                        </style>
                    </head>
                    <body>
                        <div class="sheet">${printable}</div>
                        <script>window.onload = function(){ window.print(); }<\/script>
                    </body>
                </html>
            `);
            popup.document.close();
        }
    }
}
</script>
@endsection
