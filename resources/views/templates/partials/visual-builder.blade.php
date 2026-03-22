@verbatim
<script>
function templateVisualBuilder(initialHtml = '', initialFields = []) {
    return {
        fields: [],
        initialHtml: initialHtml || '',
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
            this.initTinyMce();
            this.syncFieldsSchema();
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
            const instance = tinymce.get('templateEditor');
            if (!instance) {
                return;
            }

            instance.focus();
            instance.execCommand('mceInsertContent', false, text);
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
            const instance = tinymce.get('templateEditor');
            const html = instance ? instance.getContent() : (this.initialHtml || '');
            if (this.$refs.htmlContent) this.$refs.htmlContent.value = html;
            this.syncFieldsSchema();
        },
        initTinyMce() {
            const content = this.initialHtml && this.initialHtml.trim().length > 0
                ? this.initialHtml
                : '<p>INFORME MÉDICO</p><p>Paciente: {{nombre_paciente}}</p><p>DNI: {{dni_paciente}}</p><p>Fecha: {{fecha_actual}}</p><p>Diagnóstico: {{campo:diagnostico}}</p><p>Indicaciones: {{campo:indicaciones}}</p><p>Firma: {{firma_medico}}</p>';

            tinymce.init({
                selector: '#templateEditor',
                license_key: 'gpl',
                height: 620,
                menubar: 'file edit view insert format table tools',
                plugins: 'advlist autolink lists link charmap preview searchreplace visualblocks code fullscreen table autoresize',
                toolbar: 'undo redo | blocks styles | bold italic underline | alignleft aligncenter alignright alignjustify | outdent indent | bullist numlist | table | removeformat code',
                content_style: 'body { font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.45; } p { margin: 0.5rem 0; }',
                setup: (editor) => {
                    editor.on('init', () => {
                        editor.setContent(content);
                        this.syncHtml();
                    });
                    editor.on('change keyup input undo redo', () => {
                        this.syncHtml();
                    });
                }
            });
        }
    }
}
</script>
@endverbatim

<script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
