<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Tomografía - {{ $order->code }}</title>
    <style>
        @page { margin: 20px 22px; }
        body { font-family: Arial, Helvetica, sans-serif; color: #2d4059; font-size: 11px; margin: 0; }
        .header { width: 100%; margin-bottom: 8px; }
        .header td { vertical-align: top; }
        .logo-box { width: 120px; height: 60px; }
        .lab-title { font-size: 22px; font-weight: 700; color: #1f4f9a; line-height: 1; }
        .lab-subtitle { color: #4f79bd; font-size: 12px; margin-top: 3px; }
        .doctors { text-align: right; color: #4f79bd; font-size: 11px; line-height: 1.35; }
        .meta { width: 100%; margin: 4px 0 8px; font-size: 11px; }
        .meta td { padding: 2px 4px; vertical-align: top; }
        .box { border: 1px solid #7f9bc5; border-radius: 4px; padding: 4px 6px; min-height: 14px; color: #2d4059; font-weight: 700; text-transform: uppercase; }
        .label { color: #5e7aa8; font-size: 10px; text-transform: uppercase; margin-bottom: 2px; }
        .section { border: 1px solid #d3e0f1; border-radius: 5px; padding: 8px; margin-bottom: 8px; }
        .section-title { color: #274f87; font-weight: 700; text-transform: uppercase; margin-bottom: 4px; }
        .footer { position: fixed; bottom: 10px; left: 22px; right: 22px; font-size: 9px; color: #7588a6; }
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
                            <div class="lab-title">{{ strtoupper($branch->razon_social ?? 'INFORME TOMOGRAFÍA') }}</div>
                            <div class="lab-subtitle">Informe Tomografía</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td width="58%" class="doctors">
                <div>Médico solicitante: <strong>{{ strtoupper($requestingDoctor->name ?? $order->user->name ?? 'NO REGISTRADO') }}</strong></div>
                <div>Firmado por: <strong>{{ strtoupper($reportSigner->name ?? 'NO REGISTRADO') }}</strong></div>
                <div>Código de atención: <strong>{{ $order->code }}</strong></div>
                <div>Fecha resultado: <strong>{{ optional($result->result_date)->format('d/m/Y') }}</strong></div>
            </td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <td width="40%"><div class="label">Paciente</div><div class="box">{{ $order->patient->last_name }} {{ $order->patient->first_name }}</div></td>
            <td width="20%"><div class="label">DNI</div><div class="box">{{ $order->patient->dni }}</div></td>
            <td width="20%"><div class="label">Placas usadas</div><div class="box">{{ $result->plates_used }}</div></td>
            <td width="20%"><div class="label">Iopamidol</div><div class="box">{{ number_format((float) $result->iopamidol_used, 2) }} ml</div></td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">Descripción general</div>
        <div>{{ $result->general_description ?: '---' }}</div>
    </div>

    <div class="section">
        <div class="section-title">Descripción del resultado</div>
        <div>{!! nl2br(e($result->result_description ?: '---')) !!}</div>
    </div>

    <div class="section">
        <div class="section-title">Conclusión</div>
        <div>{!! nl2br(e($result->conclusion ?: '---')) !!}</div>
    </div>

    <div class="footer">Documento emitido electrónicamente · {{ now()->format('d/m/Y H:i') }} · {{ $branch->correo ?? '' }}</div>
</body>
</html>
