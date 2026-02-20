<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Resultados - {{ $order->code }}</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.4; font-size: 11px; }
        
        /* Estilos Institucionales tomados de tus ejemplos */
        .header-table { width: 100%; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; margin-bottom: 15px; }
        .branch-info h2 { margin: 0; color: #2c3e50; font-size: 17px; text-transform: uppercase; }
        .branch-details { font-size: 10px; color: #555; }

        .patient-box { 
            width: 100%; border: 1px solid #2c3e50; border-radius: 5px; 
            padding: 10px; margin-bottom: 15px; background-color: #f9f9f9;
        }
        .label { font-weight: bold; color: #2c3e50; font-size: 9px; text-transform: uppercase; display: block; }
        .data { font-size: 11px; font-weight: bold; color: #000; }

        /* Estructura de Resultados */
        .area-section { margin-bottom: 20px; }
        .area-title { 
            background: #2c3e50; color: white; padding: 4px 10px; 
            font-weight: bold; margin-bottom: 5px; font-size: 11px;
            text-transform: uppercase;
        }
        
        table.results-table { width: 100%; border-collapse: collapse; }
        table.results-table th { 
            border-bottom: 1px solid #2c3e50; color: #2c3e50; 
            text-align: left; padding: 6px; font-size: 10px;
        }
        table.results-table td { padding: 6px; border-bottom: 0.5px solid #eee; vertical-align: top; }
        
        .result-value { font-weight: bold; font-size: 12px; }
        .observations { font-style: italic; color: #666; font-size: 9px; }

        .signature-container { margin-top: 40px; text-align: center; }
        .signature-line { width: 220px; border-top: 1px solid #333; margin: 0 auto; padding-top: 5px; }
    </style>
</head>
<body>

    <table class="header-table">
    <tr>
        <td width="20%">
            @if($branch && $branch->logo)
                {{-- Se usa storage_path para acceder a la ruta física del logo --}}
                <img src="{{ storage_path('app/public/' . $branch->logo) }}" style="max-height: 80px; max-width: 150px;">
            @else
                {{-- Cuadro de respaldo si no hay logo configurado --}}
                <div style="width: 80px; height: 50px; background: #eee; border: 1px solid #ccc; text-align: center; line-height: 50px; font-size: 9px; color: #666;">
                    SIN LOGO
                </div>
            @endif
        </td>
        <td width="40%" style="padding-left: 15px;">
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
            <h3 style="margin:0; color: #3498db; font-size: 16px; text-transform: uppercase;">Informe de Resultados</h3>
            <div style="font-size: 13px; font-weight: bold; color: #2c3e50;">N° {{ $order->code }}</div>
        </td>
    </tr>
</table>

    <div class="patient-box">
        <table width="100%">
            <tr>
                <td width="40%">
                    <span class="label">Paciente</span>
                    <span class="data">{{ strtoupper($order->patient->last_name) }}, {{ strtoupper($order->patient->first_name) }}</span>
                </td>
                <td width="20%">
                    <span class="label">DNI</span>
                    <span class="data">{{ $order->patient->dni }}</span>
                </td>
                <td width="20%">
                    <span class="label">Edad</span>
                    <span class="data">{{ \Carbon\Carbon::parse($order->patient->birth_date)->age }} años</span>
                </td>
                <td width="20%">
                    <span class="label">Fecha</span>
                    <span class="data">{{ $order->created_at->format('d/m/Y') }}</span>
                </td>
            </tr>
        </table>
    </div>


@foreach($groupedLabs as $areaName => $items)
    @php
        // Convertimos a mayúsculas para asegurar la comparación
        $areaUpper = strtoupper($areaName);
    @endphp
    
    {{-- Si el área es Medicina o Adicionales, saltamos este ciclo --}}
    @if($areaUpper === 'MEDICINA' || $areaUpper === 'ADICIONALES')
        @continue
    @endif

    <div class="area-section">
        <div class="area-title">{{ $areaName }}</div>
        <table class="results-table">
            <thead>
                <tr>
                    <th width="35%">PRUEBA / ANÁLISIS</th>
                    <th width="20%">RESULTADO</th>
                    <th width="15%">UNIDADES</th>
                    <th width="30%">VALORES DE REFERENCIA</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $res)
                    <tr>
                        <td>
                            <strong>{{ $res->catalog->name }}</strong>
                            @if($res->observations)
                                <div class="observations">Nota: {{ $res->observations }}</div>
                            @endif
                        </td>
                        <td class="result-value">{{ $res->result_value ?? 'Pendiente' }}</td>
                        <td>{{ $res->unit }}</td>
                        <td style="font-size: 10px;">{!! nl2br(e($res->reference_range)) !!}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endforeach

    <div class="signature-container" style="margin-top: 60px;">
        <div class="signature-line">
            <strong>LABORATORIO CLÍNICO</strong><br>
            <span style="font-size: 10px;">Responsable de Análisis</span>
        </div>
    </div>

    <div style="position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #aaa;">
        Impreso por: {{ auth()->user()->name }} | Fecha y Hora: {{ now()->format('d/m/Y H:i:s') }}
    </div>

</body>
</html>