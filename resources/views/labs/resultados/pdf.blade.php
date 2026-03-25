<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Resultados - {{ $order->code }}</title>
    <style>
        @page { margin: 20px 22px; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #2d4059;
            font-size: 11px;
            margin: 0;
        }

        .header {
            width: 100%;
            margin-bottom: 8px;
        }

        .header td {
            vertical-align: top;
        }

        .logo-box {
            width: 120px;
            height: 60px;
        }

        .lab-title {
            font-size: 26px;
            font-weight: 700;
            color: #1f4f9a;
            line-height: 1;
        }

        .lab-subtitle {
            color: #4f79bd;
            font-size: 12px;
            margin-top: 3px;
        }

        .doctors {
            text-align: right;
            color: #4f79bd;
            font-size: 11px;
            line-height: 1.35;
        }

        .meta {
            width: 100%;
            margin: 4px 0 8px;
            font-size: 11px;
        }

        .meta td {
            padding: 2px 4px;
            vertical-align: top;
        }

        .box {
            border: 1px solid #7f9bc5;
            border-radius: 4px;
            padding: 4px 6px;
            min-height: 14px;
            color: #2d4059;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .2px;
        }

        .label {
            color: #5e7aa8;
            font-size: 10px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .divider {
            border-top: 2px solid #5e7aa8;
            margin: 7px 0 5px;
        }

        table.results {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.results thead th {
            color: #4e6f9d;
            font-size: 10px;
            text-transform: uppercase;
            border-bottom: 1px solid #7f9bc5;
            padding: 5px 6px;
            text-align: left;
        }

        table.results tbody td {
            padding: 4px 6px;
            border-bottom: 1px solid #e7eef7;
            vertical-align: top;
            word-wrap: break-word;
        }

        .section-row td {
            background: #eef4fb;
            color: #274f87;
            font-weight: 700;
            text-transform: uppercase;
            border-bottom: 1px solid #d3e0f1;
            padding-top: 6px;
            padding-bottom: 6px;
        }

        .result-value {
            color: #000000;
            font-weight: 700;
        }

        .pending {
            color: #7a7a7a;
            font-style: italic;
        }

        .obs {
            margin-top: 2px;
            color: #5b6b80;
            font-size: 10px;
            font-style: italic;
        }

        .footer {
            position: fixed;
            bottom: 10px;
            left: 22px;
            right: 22px;
            font-size: 9px;
            color: #7588a6;
        }

        .signature-wrap {
            margin-top: 30px;
            display: table;
            width: 100%;
        }

        .signature-item {
            display: table-cell;
            width: 50%;
            text-align: center;
        }

        .signature-item img {
            width: 160px;
            max-height: 80px;
        }

        .signature-name {
            font-size: 10px;
            color: #4f6d96;
            font-weight: 700;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td width="42%">
                <table>
                    <tr>
                        <td width="70">
                            @if($branch && $branch->logo)
                                <img class="logo-box" src="{{ storage_path('app/public/' . $branch->logo) }}" alt="Logo">
                            @else
                                <div class="logo-box" style="border:1px solid #c8d5ea;"></div>
                            @endif
                        </td>
                        <td>
                            <div class="lab-title">{{ strtoupper($branch->razon_social ?? 'LABORATORIO') }}</div>
                            <div class="lab-subtitle">Laboratorio</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td width="58%" class="doctors">
                <div>Dr(a). {{ strtoupper($profesionalSalud->name ?? $order->user->name ?? 'MÉDICO TRATANTE') }}</div>
                <div>CMP. {{ $profesionalSalud->colegiatura ?? '--' }}</div>
                <div>Informe de resultados clínicos</div>
                <div>Código de atención: <strong>{{ $order->code }}</strong></div>
                <div>Fecha: <strong>{{ $order->created_at->format('d M Y') }}</strong></div>
            </td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <td width="40%">
                <div class="label">Paciente</div>
                <div class="box">{{ $order->patient->last_name }} {{ $order->patient->first_name }}</div>
            </td>
            <td width="20%">
                <div class="label">DNI</div>
                <div class="box">{{ $order->patient->dni }}</div>
            </td>
            <td width="20%">
                <div class="label">Edad</div>
                <div class="box">
                    {{ $order->patient->birth_date ? \Carbon\Carbon::parse($order->patient->birth_date)->age . ' años' : '--' }}
                </div>
            </td>
            <td width="20%">
                <div class="label">Fecha de emisión</div>
                <div class="box">{{ now()->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <table class="results">
        <thead>
            <tr>
                <th width="36%">Análisis</th>
                <th width="16%">Resultado</th>
                <th width="30%">Rango de referencia</th>
                <th width="18%">Unidades</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groupedLabs as $areaName => $items)
                @php
                    $areaUpper = strtoupper($areaName);
                @endphp

                @if($areaUpper === 'MEDICINA' || $areaUpper === 'ADICIONALES')
                    @continue
                @endif

                <tr class="section-row">
                    <td colspan="4">{{ $areaUpper }}</td>
                </tr>

                @foreach($items as $res)
                    @php
                        $hasValue = $res->result_value !== null && trim((string) $res->result_value) !== '';
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $res->catalog->name }}</strong>
                            @if($res->observations)
                                <div class="obs">Observación: {{ $res->observations }}</div>
                            @endif
                        </td>
                        <td class="{{ $hasValue ? 'result-value' : 'pending' }}">
                            {{ $hasValue ? $res->result_value : 'En proceso' }}
                        </td>
                        <td>{{ $res->reference_range ?: '--' }}</td>
                        <td>{{ $res->unit ?: '--' }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <div class="signature-wrap">
        <div class="signature-item">
            @if(isset($tecnologo) && $tecnologo->firma)
                <img src="{{ public_path('storage/' . $tecnologo->firma) }}" alt="Firma tecnólogo">
            @endif
        </div>
    </div>

    <div class="footer">
        Documento emitido electrónicamente · {{ now()->format('d/m/Y H:i') }} · {{ $branch->correo ?? '' }}
    </div>
</body>
</html>
