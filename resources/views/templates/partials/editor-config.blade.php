<script>
    const TEMPLATE_TOKENS = [
        '@{{nombre_paciente}}',
        '@{{dni_paciente}}',
        '@{{sexo_paciente}}',
        '@{{fecha_actual}}',
        '@{{codigo_orden}}',
        '@{{regimen_aseguramiento}}',
        '@{{codigo_afiliacion}}',
        '@{{firma_medico}}'
    ];

    const TEMPLATE_BLOCKS = [
        {
            label: 'Bloque hombre',
            content: '<p><span class="tpl-token" contenteditable="false" data-token="@{{#if_hombre}}">@{{#if_hombre}}</span>&nbsp;Texto aquí&nbsp;<span class="tpl-token" contenteditable="false" data-token="@{{/if_hombre}}">@{{/if_hombre}}</span></p>'
        },
        {
            label: 'Bloque mujer',
            content: '<p><span class="tpl-token" contenteditable="false" data-token="@{{#if_mujer}}">@{{#if_mujer}}</span>&nbsp;Texto aquí&nbsp;<span class="tpl-token" contenteditable="false" data-token="@{{/if_mujer}}">@{{/if_mujer}}</span></p>'
        }
    ];

    function escapeForRegex(value) {
        return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function tokenToChipHtml(token) {
        return `<span class="tpl-token" contenteditable="false" data-token="${token}">${token}</span>`;
    }

    function transformTokensToChips(html) {
        let transformed = html;

        [...TEMPLATE_TOKENS, '@{{#if_hombre}}', '@{{/if_hombre}}', '@{{#if_mujer}}', '@{{/if_mujer}}'].forEach((token) => {
            const tokenRegex = new RegExp(escapeForRegex(token), 'g');
            transformed = transformed.replace(tokenRegex, tokenToChipHtml(token));
        });

        return transformed;
    }

    function transformChipsToTokens(html) {
        const container = document.createElement('div');
        container.innerHTML = html;

        container.querySelectorAll('.tpl-token').forEach((chip) => {
            const token = chip.getAttribute('data-token') || chip.textContent;
            chip.replaceWith(document.createTextNode(token));
        });

        return container.innerHTML;
    }

    function insertVar(variable) {
        const editor = tinymce.get('tinyEditor');
        if (!editor) return;

        editor.insertContent(`${tokenToChipHtml(variable)}&nbsp;`);
    }

    tinymce.init({
        selector: '#tinyEditor',
        license_key: 'gpl',
        height: 540,
        menubar: 'edit view insert format table tools',
        plugins: 'advlist autolink lists link charmap preview searchreplace visualblocks code fullscreen table template autoresize',
        toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | table link | templateVars templateBlocks | removeformat code preview',
        content_style: `
            .tpl-token {
                background-color: #f1f3f5;
                border: 1px solid #adb5bd;
                border-radius: 12px;
                color: #212529;
                display: inline-block;
                font-family: monospace;
                font-size: 12px;
                padding: 1px 8px;
                user-select: all;
                white-space: nowrap;
            }
        `,
        setup: function(editor) {
            editor.ui.registry.addMenuButton('templateVars', {
                text: 'Variables',
                fetch: function(callback) {
                    const items = TEMPLATE_TOKENS.map((token) => ({
                        type: 'menuitem',
                        text: token,
                        onAction: () => editor.insertContent(`${tokenToChipHtml(token)}&nbsp;`)
                    }));
                    callback(items);
                }
            });

            editor.ui.registry.addMenuButton('templateBlocks', {
                text: 'Bloques',
                fetch: function(callback) {
                    const items = TEMPLATE_BLOCKS.map((block) => ({
                        type: 'menuitem',
                        text: block.label,
                        onAction: () => editor.insertContent(block.content)
                    }));
                    callback(items);
                }
            });

            editor.on('init', () => {
                const currentContent = editor.getContent();
                editor.setContent(transformTokensToChips(currentContent));
            });
        }
    });

    document.querySelector('form').addEventListener('submit', function() {
        const editor = tinymce.get('tinyEditor');
        if (!editor) return;

        const rawContent = transformChipsToTokens(editor.getContent());
        editor.setContent(rawContent);
    });
</script>
