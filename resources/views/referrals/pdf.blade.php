<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0.45cm 0.45cm 1cm 1.8cm; size: A4 portrait; }
        body { font-family: 'Helvetica', sans-serif; font-size: 7px; line-height: 1.0; color: #000; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 7px; table-layout: fixed; }
        .table th, .table td { border: 1px solid #000; padding: 4px; vertical-align: top; word-wrap: break-word; }
        
        /* MANTENEMOS COLORES ORIGINALES SIS */
        .header-title { text-align: center; font-size: 14px; font-weight: bold; text-decoration: underline; text-transform: uppercase; }
        .section-header { background-color: #f0f0f0; font-weight: bold; padding: 6px; border: 1px solid #000; text-transform: uppercase; font-size: 8px; margin-top: 5px; }
        
        .label { font-weight: bold; font-size: 8px; display: block; text-transform: uppercase; margin-bottom: 2px; }
        .data-text { font-size: 9px; text-transform: uppercase; font-weight: normal; }
        .text-center { text-align: center; }
    </style>
</head>
<body>

    <table style="width: 100%; border: none; margin-bottom: 5px;">
        <tr>
            <td style="border: none; width: 30%;">
                <img src="{{ public_path('logo/logo_03.jpeg') }}" style="height: 48px;">
            </td>
            <td style="border: none; width: 40%; text-align: center;"><div class="header-title">HOJA DE REFERENCIA</div></td>
            <td style="border: none; width: 30%; text-align: right;">
                <div style="border: 1px solid #0056b3; padding: 5px; display: inline-block;">
                    <span class="label">N° REFERENCIA</span>
                    <span style="font-size: 12px; font-weight: bold;">{{ $referral->referral_code }}</span>
                </div>
            </td>
        </tr>
    </table>
    <div class="section-header">1. DATOS GENERALES</div>
    <table class="table">
        <tr>
            <td style="width: 20%;"><span class="label">Fecha</span><span class="data-text">{{ $referral->created_at->format('d/m/Y') }}</span></td>
            <td style="width: 20%;"><span class="label">Hora</span><span class="data-text">{{ $referral->created_at->format('H:i') }}</span></td>
            <td style="width: 30%;"><span class="label">Asegurado</span><span class="data-text">{{ $referral->patient->is_insured == 1 ? 'SI' : 'NO' }}</span></td>
            <td style="width: 30%;"><span class="label">Regimen de aseguramiento</span><span class="data-text">{{ $referral->patient->insurance_regime }}</span></td>
        </tr>
        <tr>
            <td colspan="2"><span class="label">Establecimiento Destino</span><span class="data-text">{{ strtoupper($referral->destination_facility) }}</span></td>
            <td colspan="2"><span class="label">Establecimiento de Origen</span><span class="data-text">{{ strtoupper($referral->origin_facility) }}</span></td>
        </tr>
    </table>

    <div class="section-header">2. IDENTIFICACION DEL USUARIO</div>
    <table class="table">
        <tr>
            <td style="width: 35%;"><span class="label">Apellidos y Nombres</span><span class="data-text"><strong>{{ strtoupper($referral->patient->surname . ' ' . $referral->patient->last_name . ', ' . $referral->patient->first_name) }}</strong></span></td>
            <td style="width: 25%;"><span class="label">Codgio de Afliciación al SIS</span><span class="data-text">{{ $referral->patient->affiliation_code }}</span></td>
            <td style="width: 20%;"><span class="label">N° Historia Clínica</span><span class="data-text">{{ strtoupper($referral->patient->medical_history_number) }}</span></td>
            <td style="width: 10%;"><span class="label">Edad</span><span class="data-text">{{ $referral->patient->calculated_age }}</span></td>
            <td style="width: 10%;"><span class="label">Sexo</span><span class="data-text">{{ strtoupper($referral->patient->gender) }}</span></td>
        </tr>

        <tr>
            <td colspan="5"><span class="label">Dirección</span><span class="data-text">{{ strtoupper($referral->patient->address) }}</span></td>
        </tr>
    </table>

    <div class="section-header">3. RESUMEN DE HISTORIA CLINICA</div>
    <table class="table">
        <tr><td style="height: 30px;"><span class="label">Anamnesis / Relato</span><span class="data-text">{{ strtoupper($referral->anamnesis) }}</span></td></tr>
    </table>
    
    <div style="font-weight: bold; font-size: 7px; margin: 4px 0;">EXAMEN FÍSICO / SIGNOS VITALES:</div>
    <table class="table">
        <tr>
            <td><span class="label">PA(mmHg)</span><span class="data-text">{{ $referral->blood_pressure }}</span></td>
            <td><span class="label">FC(Ixmin)</span><span class="data-text">{{ $referral->heart_rate }}</span></td>
            <td><span class="label">FR(Xmin)</span><span class="data-text">{{ $referral->respiratory_rate }}</span></td>
            <td><span class="label">T°(°C)</span><span class="data-text">{{ $referral->temperature }}</span></td>
            <td><span class="label">SaO2(%)</span><span class="data-text">{{ $referral->oxygen_saturation }}</span></td>
        </tr>
    </table>

    <table class="table" style="margin-top: -1px;">
        <tr>
            <td style="width: 50%;"><span class="label">Estado General</span><span class="data-text">{{ strtoupper($referral->general_state ?? 'ESTABLE') }}</span></td>
            <td style="width: 50%;"><span class="label">Pulmones</span><span class="data-text">{{ strtoupper($referral->respiratory_system ?? 'NORMAL') }}</span></td>
        </tr>
        <tr>
            <td style="width: 50%;"><span class="label">CV (Cardiovascular)</span><span class="data-text">{{ strtoupper($referral->cardiovascular ) }}</span></td>
            <td style="width: 50%;"><span class="label">Otros</span><span class="data-text">{{ strtoupper($referral->neurological ?? 'SIN HALLAZGOS') }}</span></td>
        </tr>
    </table>

    <table class="table">
        <tr><td style="height: 30px;"><span class="label">Examenes auxiliares</span><span class="data-text">{{ strtoupper($referral->auxiliary_exams ?? 'SIN EXAMENES') }}</span></td></tr>
    </table>

    <table class="table" style="margin-top: 5px;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="width: 40%;">Diagnóstico (CIE-10)</th>
                <th style="width: 13%;">Código</th>
                <th style="width: 4%;">D</th>
                <th style="width: 4%;">P</th>
                <th style="width: 4%;">R</th>
            </tr>
        </thead>
        <tbody>
            @foreach($referral->diagnosisTreatments as $dt)
            <tr>
                <td class="data-text">{{ strtoupper($dt->diagnosis) }}</td>
                <td class="text-center data-text">{{ $dt->icd_10_code }}</td>
                <td class="text-center data-text">{{ filled($dt->D) ? 'X' : '' }}</td>
                <td class="text-center data-text">{{ filled($dt->P) ? 'X' : '' }}</td>
                <td class="text-center data-text">{{ filled($dt->R) ? 'X' : '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="table" style="margin-top: 5px;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="width: 35%; text-align: left;">Tratamiento</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($referral->treatments ?? []) as $treatment)
            <tr>
                <td class="data-text">{{ strtoupper($treatment) }}</td>
            </tr>
            @empty
            <tr><td class="data-text">SIN TRATAMIENTO REGISTRADO</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-header">4. DATOS DE LA REFERENCIA</div>
    <table class="table">
        <tr>
            <td rowspan="2" style="width: 35%; font-weight: bold; vertical-align: middle;">Coordinación de la referencia</td>
            <td colspan="3" style="text-align: center; font-weight: bold; background-color: #f0f0f0;">UPS Destino de la Referencia</td>
        </tr>
        <tr>
            <td style="font-size: 8px;">Emergencia {{ $referral->referral_type == 'EMERGENCIA' ? '[ X ]' : '[   ]' }}</td>
            <td style="font-size: 8px;">Apoyo al Dx {{ $referral->referral_type == 'APOYO AL DX' ? '[ X ]' : '[   ]' }}</td>
            <td style="font-size: 8px;">Consulta Externa {{ $referral->referral_type == 'CONSULTA EXTERNA' ? '[ X ]' : '[   ]' }}</td>
        </tr>
        <tr><td>Fecha en que será atendido</td><td colspan="3"></td></tr>
        <tr><td>Hora en que será atendido</td><td colspan="3"></td></tr>
        <tr><td>Nombre de quien lo atenderá</td><td colspan="3"></td></tr>
        <tr><td>Nombre con quien se coordinó</td><td colspan="3"></td></tr>
        <tr>
            <td><span class="label">Especialidad Destino</span><span class="data-text">{{ strtoupper($referral->destination_specialty) }}</span></td>
            <td colspan="3">
                <span class="label">Condición del paciente al inicio del traslado</span>
                <span class="data-text">
                    {{ $referral->patient_condition == 'ESTABLE' ? '[ x ]' : '[  ]' }} ESTABLE &nbsp;&nbsp;
                    {{ $referral->patient_condition == 'MAL ESTADO' ? '[ x ]' : '[  ]' }} MAL ESTADO
                </span>
            </td>
        </tr>
    </table>

    <table class="table" style="margin-top: 10px; width: 100%; table-layout: fixed;">
    <tr>
        @php
            $roles = [
                ['l' => 'Responsable RF', 'u' => $referral->referralResponsible],
                ['l' => 'Resp. Establecimiento', 'u' => $referral->facilityResponsible],
                ['l' => 'Personal Acompaña', 'u' => $referral->escortStaff],
                ['l' => 'Personal Recibe', 'u' => $referral->receivingStaff],
            ];
        @endphp

        @foreach($roles as $r)
        <td style="height: 150px; text-align: center; vertical-align: bottom; position: relative; padding: 5px;">
            <div style="border-top: 1px solid #000; padding-top: 5px; min-height: 60px;">
                
                <span class="label" style="font-size: 7px; font-weight: bold; display: block; margin-bottom: 3px;">
                    {{ $r['l'] }}
                </span>

                <span style="font-size: 7px; display: block; line-height: 1.2; min-height: 30px;">
                    {{-- Nombre del usuario o espacio en blanco --}}
                    <strong>{{ strtoupper($r['u']->name ?? ' ') }}</strong><br>
                    
                    {{-- Profesión o espacio en blanco --}}
                    {{ strtoupper($r['u']->profession ?? ' ') }}
                    
                    {{-- Colegiatura (solo si no es el último y existe el dato) --}}
                    @if(!$loop->last)
                        <br>{{ $r['u']->license_number ?? ' ' }}
                    @endif
                </span>

                {{-- Espacio para Fecha y Hora solo en el último recuadro --}}
                @if($loop->last)
                    <div style="text-align: right; font-size: 7px; margin-top: 5px; font-weight: bold;">
                        FECHA: ____/____/____ &nbsp; HORA: ____:____
                    </div>
                @else
                    {{-- Espaciador para mantener simetría con el último cuadro --}}
                    <div style="height: 12px;"></div> 
                @endif
            </div>
        </td>
        @endforeach
    </tr>
</table>

    <div class="section-header">RECEPCIÓN (Condición Final)</div>
    <table class="table">
        <tr>
            <td style="padding: 10px;">
                <span class="label">Condición del Paciente a la llegada al Establecimiento Destino:</span>
                <span class="data-text">
                    {{ $referral->arrival_condition == 'ESTABLE' ? '[ X ]' : '[   ]' }} ESTABLE &nbsp;&nbsp; 
                    {{ $referral->arrival_condition == 'MAL ESTADO' ? '[ X ]' : '[   ]' }} MAL ESTADO &nbsp;&nbsp; 
                    {{ $referral->arrival_condition == 'FALLECIDO' ? '[ X ]' : '[   ]' }} FALLECIDO
                </span>
            </td>
        </tr>
    </table>
</body>
</html>