@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">Gestión de Plantillas Médicas</h2>
        <a href="{{ route('templates.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nueva Plantilla
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Nombre de la Plantilla</th>
                            <th>Servicio Asociado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $template->nombre_plantilla }}</td>
                            <td>
                                <span class="badge bg-info text-dark">{{ $template->service->nombre ?? 'Sin Servicio' }}</span>
                            </td>
                            <td class="text-end pe-4">
                                {{-- Pasamos el HTML codificado en Base64 para evitar errores de caracteres --}}
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        onclick="openPreview('{{ base64_encode($template->html_content) }}')">
                                    <i class="bi bi-eye"></i> Vista Previa
                                </button>
                                
                                <a href="{{ route('templates.edit', $template->id) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-4">No hay plantillas creadas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@verbatim
<script>
function openPreview(encodedHtml) {
    // 1. Decodificar el HTML desde Base64
    let contenido = atob(encodedHtml);
    
    // 2. Reemplazar variables de prueba
    contenido = contenido.replace(/{{nombre_paciente}}/g, 'JUAN PÉREZ GARCÍA');
    contenido = contenido.replace(/{{dni_paciente}}/g, '78945612');
    contenido = contenido.replace(/{{fecha_actual}}/g, '12/03/2026');
    contenido = contenido.replace(/{{codigo_orden}}/g, 'ORD-2026-0001');
    contenido = contenido.replace(/{{regimen_aseguramiento}}/g, '--');
    contenido = contenido.replace(/{{codigo_afiliacion}}/g, '--');
    contenido = contenido.replace(/{{firma_medico}}/g, '<div style="text-align:center;margin-top:30px;"><div style="border-top:1px solid #000;width:260px;margin:0 auto 8px auto;"></div><div style="font-weight:bold;">NOMBRE DEL PROFESIONAL</div><div>MÉDICO</div><div>COL. --</div></div>');

    // 3. Abrir ventana con estilos de documento A4
    const nuevaVentana = window.open("", "_blank", "width=900,height=900");
    nuevaVentana.document.write(`
        <html>
            <head>
                <title>Previsualización de Plantilla</title>
                <style>
                    body { background-color: #525659; margin: 0; padding: 20px; font-family: sans-serif; }
                    .hoja { 
                        background: white; 
                        width: 210mm; 
                        min-height: 297mm; 
                        margin: 0 auto; 
                        padding: 20mm; 
                        box-shadow: 0 0 10px rgba(0,0,0,0.5);
                        box-sizing: border-box;
                    }
                </style>
            </head>
            <body>
                <div class="hoja">${contenido}</div>
            </body>
        </html>
    `);
    nuevaVentana.document.close();
}
</script>
@endverbatim
@endsection
