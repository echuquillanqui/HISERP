<!DOCTYPE html>
<html>
<head>
    <style>
        @page { margin: 50px; } /* Ajustado para aprovechar más espacio */
        body { font-family: Arial, sans-serif; font-size: 11px; }
        
        /* Contenedor del encabezado */
        .header { 
            width: 100%; 
            border-bottom: 2px solid #333; 
            margin-bottom: 10px; 
            padding-bottom: 5px;
        }
        
        /* Compactar etiquetas de texto */
        .header h3, .header h4 { 
            margin: 5px; 
            padding: 0; 
            line-height: 1.1; /* Reduce el espacio entre líneas */
        }
        
        .footer { margin-top: 10px; text-align: center; }
        .contenido { width: 100%; }
        
        /* Asegurar que el contenido del editor sea compacto */
        .contenido p { margin: 2px 0 !important; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td style="width: 20%; vertical-align: top;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" style="max-width: 100px;">
                @endif
            </td>
            <td style="text-align: right; vertical-align: top;">
                <h3 style="font-size: 14px;">{{ $branch->razon_social ?? '' }}</h3>
                <h4 style="font-size: 10px; font-weight: normal;">{{ $branch->direccion ?? '' }}</h4>
                <h4 style="font-size: 10px; font-weight: normal;">Tel: {{ $branch->telefono ?? '' }} | {{ $branch->correo ?? '' }}</h4>
            </td>
        </tr>
    </table>

    <div class="contenido">
        {!! $report->html_final !!}
    </div>

    <div class="footer">
        @if($firmaBase64)
            <img src="{{ $firmaBase64 }}" style="max-width: 160px; margin-bottom: -5px;"><br>
        @endif
    </div>
</body>
</html>
