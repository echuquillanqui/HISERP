@verbatim
<script>
    function templateVisualBuilder(initialHtml = '') {
        return {
            blocks: [],
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
            init() {
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
