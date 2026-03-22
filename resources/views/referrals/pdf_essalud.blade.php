<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0.45cm 0.45cm 1cm 1.8cm; size: A4 portrait; }
        body { font-family: 'Helvetica', sans-serif; font-size: 7px; line-height: 1.0; color: #000; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 5px; table-layout: fixed; }
        .table th, .table td { border: 1px solid #000; padding: 4px; vertical-align: top; word-wrap: break-word; }
        .header-title { text-align: center; font-size: 12px; font-weight: bold; color: #0056b3; text-transform: uppercase; }
        .section-header { background-color: #e7f1ff; font-weight: bold; padding: 5px; border: 1px solid #000; text-transform: uppercase; font-size: 8px; color: #0056b3; margin-top: 5px; }
        .label { font-weight: bold; font-size: 6px; display: block; text-transform: uppercase; margin-bottom: 2px; color: #333; }
        .data-text { font-size: 8px; text-transform: uppercase; font-weight: normal; color: #000; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <table style="width: 100%; border: none; margin-bottom: 5px;">
        <tr>
            <td style="border: none; width: 30%;">
                <img src="{{ isset($branch) && $branch->logo ? public_path('storage/' . $branch->logo) : public_path('logo/logo_03.jpeg') }}" 
                    style="height: 60px;">
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
            <td style="width: 20%;"><span class="label">Hora</span><span class="data-text">{{ $referral->created_at->format('h:i a') }}</span></td>
            <td style="width: 60%;"><span class="label">Tipo de Seguro</span><span class="data-text"> -------</span></td>
        </tr>
        <tr>
            <td colspan="2" style="width:60%;">
                <span class="label">Establecimiento de Origen</span>
                <span class="data-text">{{ strtoupper($referral->origin_facility) }}</span>
            </td>
            <td style="width:40%;">
                <span class="label">Establecimiento Destino</span>
                <span class="data-text">{{ strtoupper($referral->destination_facility) }}</span>
            </td>
        </tr>
    </table>

    <div class="section-header">2. IDENTIFICACION DEL USUARIO</div>
    <table class="table">
        <tr>
            <td style="width: 20%;"><span class="label">N° DNI</span><span class="data-text">{{ $referral->patient->dni }}</span></td>
            <td style="width: 20%;"><span class="label">N° Historia Clínica</span><span class="data-text">{{ strtoupper($referral->patient->dni ?? '') }}</span></td>
            <td style="width: 35%;"><span class="label">Apellidos y Nombres</span><span class="data-text"><strong>{{ strtoupper(trim(($referral->patient->first_name ?? '') . ' ' . ($referral->patient->last_name ?? ''))) }}</strong></span></td>
        </tr>
    </table>
    <table class="table" style="margin-top: -1px;">
        <tr>
            
        </tr>
    </table>
    <table class="table" style="margin-top: -1px;">
        <tr>
            <td style="width: 20%;"><span class="label">Sexo</span><span class="data-text">{{ $referral->patient->gender == 'F' ? 'FEMENINO' : 'MASCULINO' }}</span></td>
            <td style="width: 20%;"><span class="label">Edad (Años)</span><span class="data-text">{{ $referral->patient->calculated_age }}</span></td>
            <td style="width: 60%;"><span class="label">Dirección</span><span class="data-text">{{ strtoupper($referral->patient->address) }}</span></td>
        </tr>
    </table>

    <div class="section-header">3. RESUMEN DE HISTORIA CLINICA</div>
    <table class="table">
        <tr><td style="height: 30px;"><span class="label">Anamnesis / Relato</span><span class="data-text">{{ strtoupper($referral->anamnesis) }}</span></td></tr>
    </table>
    
    <div style="font-weight: bold; font-size: 8px; margin: 4px 0; color: #0056b3;">EXAMEN FÍSICO / SIGNOS VITALES:</div>
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
            <td style="width: 50%;"><span class="label">Pulmones</span><span class="data-text">{{ strtoupper($referral->lungs ?? 'NORMAL') }}</span></td>
        </tr>
        <tr>
            <td style="width: 50%;"><span class="label">CV (Cardiovascular)</span><span class="data-text">{{ strtoupper($referral->cardiovascular ?? 'RNRNR') }}</span></td>
            <td style="width: 50%;"><span class="label">Otros</span><span class="data-text">{{ strtoupper($referral->others ?? 'SIN HALLAZGOS') }}</span></td>
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

    <div class="section-header">4. DATOS DE LA REFERENCIA:</div>
    <table class="table">
        <tr>
            <td rowspan="2" style="width: 35%; font-weight: bold;">Coordinación de la referencia</td>
            <td colspan="3" style="text-align: center; font-weight: bold; background-color: #f9f9f9;">UPS Destino de la Referencia</td>
        </tr>
        <tr>
            <td style="font-size: 8px;">Emergencia {{ $referral->referral_type == 'EMERGENCIA' ? '[ x ]' : '[  ]' }}</td>
            <td style="font-size: 8px;">Apoyo al diagnóstico {{ $referral->referral_type == 'APOYO AL DX' ? '[ x ]' : '[  ]' }}</td>
            <td style="font-size: 8px;">Consulta externa {{ $referral->referral_type == 'CONSULTA EXTERNA' ? '[ x ]' : '[  ]' }}</td>
        </tr>
        <tr>
            <td>Fecha en que será atendido</td><td colspan="3">{{ $referral->appointment_date }}</td></tr>
            <tr><td>Hora en que será atendido</td><td colspan="3">{{ $referral->appointment_time }}</td></tr>
            <tr><td>Nombre de quien lo atenderá</td><td colspan="3">{{ $referral->attending_physician_name }}</td></tr>
            <tr><td>Nombre con quien se coordinó la atención</td><td colspan="3">{{ $referral->coordination_name }}</td></tr>
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
                ['label' => 'Responsable RF', 'user' => $referral->referralResponsible],
                ['label' => 'Resp. Establecimiento', 'user' => $referral->facilityResponsible],
                ['label' => 'Personal Acompaña', 'user' => $referral->escortStaff],
                ['label' => 'Personal Recibe', 'user' => $referral->receivingStaff],
            ];
        @endphp

        @foreach($roles as $role)
        <td style="height: 180px; text-align: center; vertical-align: bottom; position: relative; padding: 5px;">
            
            <div style="border-top: 1px solid #000; padding-top: 5px;">
                
                {{-- Etiqueta del Rol --}}
                <span class="label" style="font-size: 7px; display: block; font-weight: bold; margin-bottom: 2px;">
                    {{ $role['label'] }}
                </span>

                {{-- Información del Usuario --}}
                <span style="font-size: 7px; display: block; line-height: 1.1; min-height: 25px;">
                    <strong>{{ strtoupper($role['user']->name ?? ' ') }}</strong><br>
                    Colegiatura: {{ strtoupper($role['user']->colegiatura ?? ' ') }} | RNE: {{ strtoupper($role['user']->rne ?? ' ') }}
                    <br>
                    
                    {{-- Muestra la colegiatura solo si NO es la última columna --}}
                    @if(!$loop->last)
                        {{ $role['user']->license_number ?? ' ' }}
                    @else
                        {{-- Espacio invisible para mantener la altura si es el último --}}
                        &nbsp;
                    @endif
                </span>

                {{-- Solo en la última columna (Fecha y Hora) --}}
                @if($loop->last)
                    <span style="display:block; text-align:right; font-size:7px; margin-top:5px; font-weight: bold;">
                        FECHA: ____/____/____ &nbsp; HORA: ____:____
                    </span>
                @else
                    {{-- Div de compensación para que las líneas de firma queden niveladas --}}
                    <div style="height: 10px;"></div>
                @endif

            </div>
        </td>
        @endforeach
    </tr>
</table>



    <div class="section-header">RECEPCIÓN (Condición Final)</div>
    <table class="table">
        <tr>
            <td style="padding: 8px;">
                <span class="label">Condición del Paciente a la llegada al Establecimiento Destino:</span>
                <span class="data-text">
                    {{ $referral->arrival_condition == 'ESTABLE' ? '[ x ]' : '[  ]' }} ESTABLE &nbsp;&nbsp; 
                    {{ $referral->arrival_condition == 'MAL ESTADO' ? '[ x ]' : '[  ]' }} MAL ESTADO &nbsp;&nbsp; 
                    {{ $referral->arrival_condition == 'FALLECIDO' ? '[ x ]' : '[  ]' }} FALLECIDO
                </span>
            </td>
        </tr>
    </table>
</body>
</html>