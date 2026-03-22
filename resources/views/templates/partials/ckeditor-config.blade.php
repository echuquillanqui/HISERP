<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/super-build/ckeditor.js"></script>
<script>
    const TEMPLATE_TOKENS = [
        '@{{nombre_paciente}}',
        '@{{dni_paciente}}',
        '@{{edad_paciente}}',
        '@{{sexo_paciente}}',
        '@{{fecha_actual}}',
        '@{{codigo_orden}}',
        '@{{regimen_aseguramiento}}',
        '@{{codigo_afiliacion}}',
        '@{{firma_medico}}',
        '@{{#if_hombre}}',
        '@{{/if_hombre}}',
        '@{{#if_mujer}}',
        '@{{/if_mujer}}'
    ];

    let reportEditor = null;

    function insertVar(variable) {
        if (!reportEditor) return;
        reportEditor.model.change(writer => {
            reportEditor.model.insertContent(writer.createText(`${variable} `), reportEditor.model.document.selection);
        });
    }

    CKEDITOR.ClassicEditor
        .create(document.querySelector('#reportEditor'), {
            toolbar: {
                items: [
                    'undo', 'redo', '|',
                    'heading', '|',
                    'bold', 'italic', 'underline', '|',
                    'alignment', '|',
                    'bulletedList', 'numberedList', '|',
                    'outdent', 'indent', '|',
                    'insertTable', 'horizontalLine', '|',
                    'fontSize', 'fontFamily', 'fontColor', '|',
                    'removeFormat'
                ],
                shouldNotGroupWhenFull: true
            },
            fontSize: {
                options: [10, 11, 12, 14, 16, 18, 20]
            },
            table: {
                contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells', 'tableProperties', 'tableCellProperties']
            },
            htmlSupport: {
                allow: [
                    {
                        name: /.*/,
                        attributes: true,
                        classes: true,
                        styles: true
                    }
                ]
            },
            removePlugins: [
                'AIAssistant',
                'CKBox',
                'CKFinder',
                'DocumentOutline',
                'EasyImage',
                'RealTimeCollaborativeComments',
                'RealTimeCollaborativeTrackChanges',
                'RealTimeCollaborativeRevisionHistory',
                'PresenceList',
                'Comments',
                'TrackChanges',
                'TrackChangesData',
                'RevisionHistory',
                'Pagination',
                'WProofreader',
                'MathType'
            ]
        })
        .then(editor => {
            reportEditor = editor;
            const editable = editor.ui.view.editable.element;
            editable.classList.add('report-sheet');
        })
        .catch(error => {
            console.error(error);
        });
</script>

<style>
    .ck-editor__editable_inline.report-sheet {
        min-height: 900px;
        max-width: 840px;
        margin: 0 auto;
        padding: 25mm 18mm;
        background: #fff;
        border: 1px solid #ced4da;
        box-shadow: 0 0 12px rgba(0, 0, 0, 0.08);
        font-family: "Times New Roman", serif;
        font-size: 12pt;
        line-height: 1.25;
    }
</style>
