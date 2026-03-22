@verbatim
<script>
function templateVisualBuilder(initialHtml = '', initialFields = []) {
    return {
        fields: [],
        documentTemplate: '',
        fieldTypeOptions: [
            { value: 'text', label: 'Texto corto' },
            { value: 'textarea', label: 'Texto largo' },
            { value: 'number', label: 'Número' },
            { value: 'date', label: 'Fecha' },
            { value: 'time', label: 'Hora' },
            { value: 'datetime-local', label: 'Fecha y hora' },
            { value: 'select', label: 'Lista' },
            { value: 'email', label: 'Correo' },
            { value: 'tel', label: 'Teléfono' },
            { value: 'checkbox', label: 'Casilla Sí/No' }
        ],
        systemPlaceholders: [
            { label: 'Paciente', value: '{{nombre_paciente}}' },
            { label: 'DNI', value: '{{dni_paciente}}' },
            { label: 'Edad', value: '{{edad_paciente}}' },
            { label: 'Sexo', value: '{{sexo_paciente}}' },
            { label: 'Fecha actual', value: '{{fecha_actual}}' },
            { label: 'Código orden', value: '{{codigo_orden}}' },
            { label: 'Régimen', value: '{{regimen_aseguramiento}}' },
            { label: 'Cod. afiliación', value: '{{codigo_afiliacion}}' },
            { label: 'Firma médico', value: '{{firma_medico}}' }
        ],
        init() {
            this.fields = this.normalizeFields(initialFields);
            this.documentTemplate = (initialHtml && initialHtml.trim().length > 0)
                ? initialHtml
                : 'INFORME MÉDICO\n\nPaciente: {{nombre_paciente}}\nDNI: {{dni_paciente}}\nFecha: {{fecha_actual}}\n\nDiagnóstico: {{campo:diagnostico}}\nIndicaciones: {{campo:indicaciones}}\n\nFirma: {{firma_medico}}';

            this.syncHtml();
        },
        normalizeFields(rawFields = []) {
            return (rawFields || []).map((field, index) => {
                const label = (field?.label || '').trim() || `Campo ${index + 1}`;
                return {
                    key: (field?.key || this.slugify(label)).trim(),
                    label,
                    type: (field?.type || 'text').trim().toLowerCase(),
                    required: Boolean(field?.required),
                    optionsText: Array.isArray(field?.options) ? field.options.join('\n') : ''
                };
            }).filter(field => field.key);
        },
        slugify(text) {
            return (text || '')
                .toString()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '') || `campo_${Date.now()}`;
        },
        buildFieldToken(key) {
            return `{{campo:${key}}}`;
        },
        addField() {
            const label = `Campo ${this.fields.length + 1}`;
            this.fields.push({ key: this.slugify(label), label, type: 'text', required: false, optionsText: '' });
            this.syncFieldsSchema();
        },
        removeField(index) {
            this.fields.splice(index, 1);
            this.syncFieldsSchema();
        },
        insertFieldToken(field) {
            if (!field?.key) return;
            this.insertTextAtCursor(this.buildFieldToken(field.key));
        },
        insertTextAtCursor(text) {
            const editor = this.$refs.editor;

            if (!editor) {
                this.documentTemplate += text;
                this.syncHtml();
                return;
            }

            const start = editor.selectionStart ?? this.documentTemplate.length;
            const end = editor.selectionEnd ?? this.documentTemplate.length;
            const current = this.documentTemplate || '';

            this.documentTemplate = `${current.slice(0, start)}${text}${current.slice(end)}`;

            this.$nextTick(() => {
                editor.focus();
                const cursor = start + text.length;
                editor.setSelectionRange(cursor, cursor);
            });

            this.syncHtml();
        },
        syncFieldIdentity(field) {
            if (field.label && (!field.key || field.key.startsWith('campo_'))) {
                field.key = this.slugify(field.label);
            }
            this.syncFieldsSchema();
        },
        syncFieldsSchema() {
            const allowedTypes = this.fieldTypeOptions.map(item => item.value);
            const schema = this.fields
                .filter(field => (field.key || '').trim().length > 0)
                .map((field, index) => {
                    const label = (field.label || '').trim() || `Campo ${index + 1}`;
                    const key = (field.key || this.slugify(label)).trim();
                    const type = allowedTypes.includes(field.type) ? field.type : 'text';
                    const options = type === 'select'
                        ? (field.optionsText || '').split('\n').map(option => option.trim()).filter(Boolean)
                        : [];

                    field.key = key;
                    field.label = label;

                    return { key, label, type, required: Boolean(field.required), options };
                });

            if (this.$refs.fieldsSchema) {
                this.$refs.fieldsSchema.value = JSON.stringify(schema);
            }
        },
        syncHtml() {
            const html = (this.documentTemplate || '').replace(/\n/g, '<br>');
            if (this.$refs.htmlContent) this.$refs.htmlContent.value = html;
            if (this.$refs.preview) this.$refs.preview.innerHTML = html;
            this.syncFieldsSchema();
        }
    }
}
</script>
@endverbatim

<style>
.template-preview-sheet {
    background: #fff;
    width: 210mm;
    min-height: 297mm;
    margin: 0 auto;
    padding: 15mm;
    box-sizing: border-box;
    border: 1px solid #d8dee4;
    box-shadow: 0 4px 16px rgba(0, 0, 0, .08);
    font-family: 'Times New Roman', serif;
    line-height: 1.3;
    font-size: 12pt;
}
.template-preview-wrapper {
    background: #f1f3f5;
    padding: 16px;
    border-radius: .5rem;
}
</style>
