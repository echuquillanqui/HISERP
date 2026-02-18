<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historia Clínica</title>
    <style>
        @page { margin: 0.8cm; }
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.3; font-size: 11px; }
        
        /* Encabezado */
        .header-table { width: 100%; border-bottom: 2px solid #2c3e50; padding-bottom: 5px; margin-bottom: 10px; }
        .branch-info h2 { margin: 0; color: #2c3e50; font-size: 16px; text-transform: uppercase; }
        .branch-details { font-size: 9px; color: #555; }

        /* Cuadro Paciente */
        .patient-box { 
            width: 100%; border: 1.5px solid #2c3e50; border-radius: 6px; 
            padding: 10px; margin-bottom: 12px; background-color: #fcfcfc;
        }
        .label { font-weight: bold; color: #2c3e50; font-size: 9px; text-transform: uppercase; }
        .data { font-size: 12px; font-weight: bold; color: #000; }

        /* Secciones */
        .section-header { 
            background: #3498db; color: white; padding: 4px 10px; 
            font-weight: bold; font-size: 10.5px; text-transform: uppercase; margin-top: 10px;
        }
        .section-content { padding: 8px; border: 1px solid #eee; border-top: none; background: #fff; }

        /* Grid de 3 Columnas para Antecedentes */
        .grid-3 { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .grid-3 td { vertical-align: top; padding: 5px; border: 1px solid #f2f2f2; }

        /* Funciones Vitales */
        .vital-table { width: 100%; border-collapse: collapse; }
        .vital-table td { border: 1px solid #ddd; padding: 6px; text-align: center; }
        .v-label { font-size: 8px; color: #666; font-weight: bold; display: block; text-transform: uppercase; }
        .v-value { font-size: 11px; font-weight: bold; }

        /* Tabla Diagnósticos */
        .diag-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .diag-table th { background: #f8f9fa; font-size: 9px; border: 1px solid #ddd; padding: 5px; text-align: left; }
        .diag-table td { border: 1px solid #eee; padding: 6px; font-size: 10.5px; vertical-align: top; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td width="20%">
                @if($branch && $branch->logo)
                    <img src="{{ storage_path('app/public/' . $branch->logo) }}" style="max-height: 55px;">
                @endif
            </td>
            <td width="50%">
                <div class="branch-info">
                    <h2>{{ $branch->razon_social }}</h2>
                    <div class="branch-details">RUC: {{ $branch->ruc }} | DIR: {{ $branch->direccion }}</div>
                </div>
            </td>
            <td width="30%" align="right">
                <h3 style="margin:0; color: #3498db; font-size: 14px;">HISTORIA CLÍNICA</h3>
                <div style="font-weight: bold; font-size: 12px;">N° {{ str_pad($history->id, 6, '0', STR_PAD_LEFT) }}</div>
            </td>
        </tr>
    </table>

    <div class="patient-box">
        <table width="100%">
            <tr>
                <td width="40%"><span class="label">Paciente:</span><br><span class="data">{{ strtoupper($history->patient->first_name) }} {{ strtoupper($history->patient->last_name) }}</span></td>
                <td width="20%"><span class="label">DNI:</span><br><span class="data">{{ $history->patient->dni }}</span></td>
                <td width="20%"><span class="label">Sexo:</span><br><span class="data">{{ $history->patient->gender == 'M' ? 'MASCULINO' : 'FEMENINO' }}</span></td>
                <td width="20%"><span class="label">F. Atención:</span><br><span class="data">{{ $history->created_at->format('d/m/Y') }}</span></td>
            </tr>
        </table>
    </div>

    <div class="section-header">1. Antecedentes Personales y Familiares</div>
    <div class="section-content" style="padding: 0;">
        <table class="grid-3">
            <tr>
                <td>
                    <span class="label">Alergias</span><br>
                    {{ strtoupper($history->alergias ?? 'Ninguna') }}
                </td>
                <td>
                    <span class="label">Antecedentes Familiares</span><br>
                    {{ strtoupper($history->antecedentes_familiares ?? 'Sin registros') }}
                </td>
                <td>
                    <span class="label">Otros Antecedentes</span><br>
                    {{ strtoupper($history->antecedentes_otros ?? 'Ninguno') }}
                </td>
            </tr>
        </table>
        <div style="padding: 8px; border-top: 1px solid #f2f2f2; background: #fafafa;">
            <strong>HÁBITOS:</strong> &nbsp;&nbsp;
            Tabaco: <strong>{{ $history->habito_tabaco ? 'SÍ' : 'NO' }}</strong> &nbsp;|&nbsp; 
            Alcohol: <strong>{{ $history->habito_alcohol ? 'SÍ' : 'NO' }}</strong> &nbsp;|&nbsp; 
            Coca: <strong>{{ $history->habito_coca ? 'SÍ' : 'NO' }}</strong>
        </div>
    </div>

    <div class="section-header">2. Anamnesis / Motivo de Consulta</div>
    <div class="section-content">
        {{ strtoupper($history->anamnesis ?? 'NO REFIERE SÍNTOMAS ESPECÍFICOS.') }}
    </div>

    <div class="section-header">3. Funciones Vitales y Examen Físico</div>
    <div style="border: 1px solid #eee; border-top: none;">
        <table class="vital-table">
            <tr>
                <td><span class="v-label">P.A. (mmHg)</span><span class="v-value">{{ $history->pa ?? '--' }}</span></td>
                <td><span class="v-label">F.C. (LPM)</span><span class="v-value">{{ $history->fc ?? '--' }}</span></td>
                <td><span class="v-label">F.R. (RPM)</span><span class="v-value">{{ $history->fr ?? '--' }}</span></td>
                <td><span class="v-label">Temp. (°C)</span><span class="v-value">{{ $history->temp ?? '--' }}</span></td>
                <td><span class="v-label">Sat O2 (%)</span><span class="v-value">{{ $history->so2 ?? '--' }}</span></td>
                <td><span class="v-label">Peso (Kg)</span><span class="v-value">{{ $history->peso ?? '--' }}</span></td>
                <td><span class="v-label">Talla (Cm)</span><span class="v-value">{{ $history->talla ?? '--' }}</span></td>
                <td><span class="v-label">I.M.C.</span><span class="v-value">{{ $history->imc ?? '--' }}</span></td>
            </tr>
        </table>
        <div style="padding: 10px;">
            <strong>EXAMEN FÍSICO DETALLADO:</strong><br>
            {{ strtoupper($history->examen_fisico_detalle ?? 'SIN HALLAZGOS PATOLÓGICOS.') }}
        </div>
    </div>

    <div class="section-header">4. Diagnósticos y Plan de Tratamiento</div>
<div style="border: 1px solid #eee; border-top: none;">
    <table class="diag-table">
        <thead>
            <tr>
                <th width="15%">CÓDIGO</th>
                <th width="35%">DIAGNÓSTICO</th>
                <th width="50%">TRATAMIENTO / RECOMENDACIONES</th>
            </tr>
        </thead>
        <tbody>
            @forelse($history->diagnostics as $diag)
                <tr>
                    <td style="text-align: center;">
                        <strong style="color: #2c3e50;">{{ $diag->cie10->codigo ?? 'S/C' }}</strong>
                    </td>
                    <td>
                        {{-- Usamos el diagnóstico de la tabla history_diagnostics o el de la tabla cie10 --}}
                        <strong>{{ strtoupper($diag->diagnostico) }}</strong>
                    </td>
                    <td>{{ strtoupper($diag->tratamiento) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #999; padding: 10px;">
                        NO HAY DIAGNÓSTICOS REGISTRADOS EN ESTA ATENCIÓN.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

    <div style="margin-top: 60px; text-align: center;">
        <div style="width: 220px; border-top: 1.5px solid #2c3e50; margin: 0 auto; padding-top: 5px;">
            <strong>DR. {{ strtoupper($history->user->name) }}</strong><br>
            <span style="font-size: 9px; color: #666;">Firma y Sello del Médico Evaluador</span>
        </div>
    </div>

    <div class="footer">
        Documento médico generado el {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>