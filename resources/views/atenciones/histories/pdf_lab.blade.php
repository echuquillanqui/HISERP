<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Laboratorio</title>
    <style>
        @page { margin: 2cm; }
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.4; }
        
        /* Encabezado Institucional */
        .header-table { width: 100%; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; margin-bottom: 20px; }
        .branch-logo { max-height: 80px; max-width: 150px; }
        .branch-info h2 { margin: 0; color: #2c3e50; font-size: 18px; text-transform: uppercase; }
        .branch-details { font-size: 11px; color: #555; }

        /* Título del Documento */
        .doc-title { text-align: right; vertical-align: middle; }
        .doc-title h3 { margin: 0; color: #3498db; font-size: 17px; text-transform: uppercase; }
        .doc-number { font-size: 13px; font-weight: bold; color: #2c3e50; }

        /* Cuadro de Paciente en Columnas */
        .patient-box { 
            width: 100%; border: 1.5px solid #2c3e50; border-radius: 8px; 
            padding: 15px; margin-bottom: 25px; background-color: #fcfcfc;
        }
        .patient-table { width: 100%; border-collapse: collapse; }
        .label { font-weight: bold; color: #2c3e50; font-size: 11px; display: block; text-transform: uppercase; margin-bottom: 2px; }
        .data { font-size: 14px; font-weight: bold; color: #000; }

        /* Secciones de Laboratorio */
        .area-section { margin-bottom: 20px; }
        .area-title { 
            background: #3498db; color: white; padding: 6px 12px; 
            font-weight: bold; border-radius: 4px; margin-bottom: 10px; 
            text-transform: uppercase; font-size: 12px;
        }
        .exam-item { 
            padding: 7px 0 7px 15px; border-bottom: 1px solid #eee; 
            font-size: 13px; display: block;
        }
        .check-mark { color: #3498db; font-weight: bold; margin-right: 10px; font-size: 16px; }

        /* Firma */
        .signature-container { margin-top: 80px; text-align: center; }
        .signature-line { width: 250px; border-top: 1.5px solid #2c3e50; margin: 0 auto; padding-top: 5px; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td width="20%">
                @if($branch && $branch->logo)
                    {{-- storage_path genera: C:\laragon\www\tu_proyecto\storage\app\public\logos/... --}}
                    <img src="{{ storage_path('app/public/' . $branch->logo) }}" style="max-height: 80px; max-width: 150px;">
                @else
                    <div style="width: 80px; height: 50px; background: #eee; border: 1px solid #ccc; text-align: center; line-height: 50px; font-size: 9px;">SIN LOGO</div>
                @endif
            </td>
            <td width="40%" style="padding-left: 10px;">
                @if($branch)
                    <div class="branch-info">
                        <h2>{{ $branch->razon_social }}</h2>
                        <div class="branch-details">
                            <strong>RUC:</strong> {{ $branch->ruc }}<br>
                            <strong>DIR:</strong> {{ $branch->direccion }}<br>
                            <strong>TEL:</strong> {{ $branch->telefono ?? 'S/N' }}
                        </div>
                    </div>
                @endif
            </td>
            <td width="40%" align="right">
                <h3 style="margin:0; color: #3498db; font-size: 15px; text-transform: uppercase;">Orden de Laboratorio</h3>
                <div style="font-size: 13px; font-weight: bold;">N° {{ str_pad($history->id, 6, '0', STR_PAD_LEFT) }}</div>
            </td>
        </tr>
    </table>

    <div class="patient-box">
        <table class="patient-table">
            <tr>
                <td width="45%">
                    <span class="label">Nombre del Paciente</span>
                    <span class="data">{{ strtoupper($history->patient->first_name) }} {{ strtoupper($history->patient->last_name) }}</span>
                </td>
                <td width="25%">
                    <span class="label">DNI / Documento</span>
                    <span class="data">{{ $history->patient->dni }}</span>
                </td>
                <td width="30%">
                    <span class="label">Edad / Sexo</span>
                    <span class="data">
                        {{ $history->patient->age_detail }} / 
                        {{ $history->patient->gender == 'M' ? 'MASC' : 'FEM' }}
                    </span>
                </td>
            </tr>
            <tr>
                <td style="padding-top: 10px;">
                    <span class="label">Fecha de Solicitud</span>
                    <span class="data">{{ $history->created_at->format('d/m/Y H:i A') }}</span>
                </td>
                <td colspan="2" style="padding-top: 10px;">
                    <span class="label">Médico Tratante</span>
                    <span class="data">DR. {{ strtoupper($history->user->name) }}</span>
                </td>
            </tr>
        </table>
    </div>

    @forelse($groupedLabs as $areaName => $items)
        <div class="area-section">
            <div class="area-title">EXAMENES SOLICITADOS</div>
            
            @foreach($items as $lab)
                <div class="exam-item">
                    <span class="check-mark">*</span>
                    @php
                        // Obtenemos el nombre desde itemable o directamente del item
                        $name = $lab->itemable->name ?? ($lab->name ?? 'EXAMEN');

                        // LIMPIEZA: Quitamos [PERFIL], [EXAMEN], [examen]
                        $search = ['[PERFIL]', '[EXAMEN]', '[examen]'];
                        $cleanName = trim(str_ireplace($search, '', $name));
                    @endphp
                    <strong>{{ strtoupper($cleanName) }}</strong>
                </div>
            @endforeach
        </div>
    @empty
        <div style="text-align: center; margin-top: 50px; color: #999;">
            <h3>No se encontraron exámenes registrados.</h3>
        </div>
    @endforelse

    <div class="signature-container">
        <div class="signature-line">
            <strong style="font-size: 14px;">DR. {{ strtoupper($history->user->name) }}</strong><br>
            <span style="font-size: 11px; color: #666;">Firma y Sello Médico</span>
        </div>
    </div>

    <div class="footer">
        Este documento es una orden médica oficial generada el {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>