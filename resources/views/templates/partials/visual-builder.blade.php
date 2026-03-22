@verbatim
<script>
    function templateVisualBuilder(initialHtml = '', initialFields = []) {
        return {
            blocks: [],
            fields: [],
            fieldTypeOptions: [
                { value: 'text', label: 'Texto corto' },
                { value: 'textarea', label: 'Texto largo' },
                { value: 'number', label: 'Número' },
                { value: 'date', label: 'Fecha' },
                { value: 'time', label: 'Hora' },
                { value: 'datetime-local', label: 'Fecha y hora' },
                { value: 'select', label: 'Lista (selección)' },
                { value: 'email', label: 'Correo' },
                { value: 'tel', label: 'Teléfono' },
                { value: 'checkbox', label: 'Casilla Sí / No' }
            ],
            tokenOptions: [
                '{{nombre_paciente}}',
                '{{dni_paciente}}',
                '{{edad_paciente}}',
                '{{sexo_paciente}}',
                '{{fecha_actual}}',
                '{{codigo_orden}}',
                '{{regimen_aseguramiento}}',
                '{{codigo_afiliacion}}',
                '{{firma_medico}}',
                '{{#if_hombre}}',
                '{{/if_hombre}}',
                '{{#if_mujer}}',
                '{{/if_mujer}}'
            ],
            get allTokenOptions() {
                const dynamicTokens = this.fields.map(field => this.buildFieldToken(field.key));
                return [...this.tokenOptions, ...dynamicTokens];
            },
            init() {
                this.fields = this.normalizeFields(initialFields);
                if (initialHtml && initialHtml.trim().length > 0) {
                    this.blocks = [{ type: 'html', title: 'Contenido existente', html: initialHtml }];
                } else {
                    this.blocks = [
                        { type: 'heading', level: 'h3', text: 'Título del informe' },
                        { type: 'paragraph', text: 'Escribe aquí el contenido del informe...' }
                    ];
                }
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
                const next = this.fields.length + 1;
                const label = `Nuevo campo ${next}`;
                this.fields.push({
                    key: this.slugify(label),
                    label,
                    type: 'text',
                    required: false,
                    optionsText: ''
                });
                this.syncFieldsSchema();
            },
            removeField(index) {
                this.fields.splice(index, 1);
                this.syncFieldsSchema();
            },
            insertFieldToken(field) {
                if (!field?.key) return;
                this.blocks.push({ type: 'token', token: this.buildFieldToken(field.key) });
                this.syncHtml();
            },
            syncFieldIdentity(field) {
                if (!field.label) {
                    this.syncFieldsSchema();
                    return;
                }
                if (!field.key || field.key.startsWith('campo_') || field.key.startsWith('nuevo_campo')) {
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
                            ? (field.optionsText || '')
                                .split('\n')
                                .map(option => option.trim())
                                .filter(Boolean)
                            : [];

                        field.key = key;
                        field.label = label;

                        return {
                            key,
                            label,
                            type,
                            required: Boolean(field.required),
                            options
                        };
                    });

                if (this.$refs.fieldsSchema) {
                    this.$refs.fieldsSchema.value = JSON.stringify(schema);
                }
            },
            addBlock(type) {
                const defaults = {
                    heading: { type: 'heading', level: 'h3', text: 'Nuevo título' },
                    paragraph: { type: 'paragraph', text: 'Nuevo párrafo' },
                    token: { type: 'token', token: '{{nombre_paciente}}' },
                    divider: { type: 'divider' },
                    spacer: { type: 'spacer', height: 16 },
                    conditional: { type: 'conditional', gender: 'hombre', content: '<p>Contenido condicional</p>' },
                    html: { type: 'html', title: 'Bloque HTML libre', html: '<div>HTML personalizado</div>' }
                };

                this.blocks.push(defaults[type]);
                this.syncHtml();
            },
            moveUp(index) {
                if (index === 0) return;
                [this.blocks[index - 1], this.blocks[index]] = [this.blocks[index], this.blocks[index - 1]];
                this.syncHtml();
            },
            moveDown(index) {
                if (index >= this.blocks.length - 1) return;
                [this.blocks[index + 1], this.blocks[index]] = [this.blocks[index], this.blocks[index + 1]];
                this.syncHtml();
            },
            removeBlock(index) {
                this.blocks.splice(index, 1);
                this.syncHtml();
            },
            escapeHtml(value) {
                const div = document.createElement('div');
                div.innerText = value ?? '';
                return div.innerHTML;
            },
            renderBlock(block) {
                switch (block.type) {
                    case 'heading':
                        return `<${block.level}>${this.escapeHtml(block.text)}</${block.level}>`;
                    case 'paragraph':
                        return `<p>${this.escapeHtml(block.text).replace(/\n/g, '<br>')}</p>`;
                    case 'token':
                        return `<p>${block.token}</p>`;
                    case 'divider':
                        return '<hr>';
                    case 'spacer':
                        return `<div style="height:${Number(block.height || 0)}px"></div>`;
                    case 'conditional':
                        if (block.gender === 'hombre') {
                            return `{{#if_hombre}}${block.content || ''}{{/if_hombre}}`;
                        }
                        return `{{#if_mujer}}${block.content || ''}{{/if_mujer}}`;
                    case 'html':
                        return block.html || '';
                    default:
                        return '';
                }
            },
            syncHtml() {
                const html = this.blocks.map((block) => this.renderBlock(block)).join('\n');
                this.$refs.htmlContent.value = html;
                this.$refs.preview.innerHTML = html;
                this.syncFieldsSchema();
            }
        }
    }
</script>
@endverbatim

<style>
    .template-preview-sheet {
        min-height: 700px;
        max-height: 900px;
        overflow: auto;
        background: #fff;
        border: 1px solid #d1d7de;
        border-radius: .5rem;
        padding: 20mm 16mm;
        font-family: 'Times New Roman', serif;
        font-size: 12pt;
        line-height: 1.25;
        box-shadow: 0 0 10px rgba(0,0,0,.06);
    }
</style>
