<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cuadre de Caja - {{ $date }}</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.4; font-size: 12px; }
        
        /* Encabezado Institucional idéntico a tu ejemplo */
        .header-table { width: 100%; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; margin-bottom: 20px; }
        .branch-info h2 { margin: 0; color: #2c3e50; font-size: 18px; text-transform: uppercase; }
        .branch-details { font-size: 11px; color: #555; }

        /* Resumen de Totales */
        .summary-box { 
            width: 100%; border: 1.5px solid #2c3e50; border-radius: 8px; 
            padding: 10px; margin-bottom: 20px; background-color: #fcfcfc;
        }
        .summary-table { width: 100%; text-align: center; }
        .label { font-weight: bold; color: #2c3e50; font-size: 10px; text-transform: uppercase; }
        .data { font-size: 16px; font-weight: bold; }

        /* Tablas de Detalles */
        .section-title { 
            background: #3498db; color: white; padding: 5px 10px; 
            font-weight: bold; border-radius: 4px; margin-bottom: 8px; 
            text-transform: uppercase; font-size: 11px;
        }
        table.detail-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.detail-table th { background: #f2f2f2; border: 1px solid #ddd; padding: 6px; font-size: 10px; text-transform: uppercase; }
        table.detail-table td { border: 1px solid #ddd; padding: 6px; font-size: 11px; }
        
        .text-right { text-align: right; }
        .text-success { color: #27ae60; }
        .text-danger { color: #e74c3c; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td width="20%">
                @if($branch && $branch->logo)
                    <img src="{{ storage_path('app/public/' . $branch->logo) }}" style="max-height: 70px;">
                @else
                    <div style="width: 70px; height: 40px; background: #eee; border: 1px solid #ccc; text-align: center; line-height: 40px; font-size: 8px;">SIN LOGO</div>
                @endif
            </td>
            <td width="50%">
                <div class="branch-info">
                    <h2>{{ $branch->razon_social ?? 'SISTEMA MÉDICO' }}</h2>
                    <div class="branch-details">
                        <strong>RUC:</strong> {{ $branch->ruc ?? '---' }}<br>
                        <strong>DIR:</strong> {{ $branch->direccion ?? '---' }}
                    </div>
                </div>
            </td>
            <td width="30%" align="right">
                <h3 style="margin:0; color: #3498db; font-size: 14px;">CUADRE DE CAJA</h3>
                <div style="font-size: 12px; font-weight: bold;">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    <div class="summary-box">
        <table class="summary-table">
            <tr>
                <td width="33%">
                    <span class="label">Total Ingresos</span><br>
                    <span class="data text-success">S/ {{ number_format($totalIngresos, 2) }}</span>
                </td>
                <td width="33%" style="border-left: 1px solid #ddd; border-right: 1px solid #ddd;">
                    <span class="label">Total Egresos</span><br>
                    <span class="data text-danger">S/ {{ number_format($totalEgresos, 2) }}</span>
                </td>
                <td width="33%">
                    <span class="label">Saldo Neto</span><br>
                    <span class="data">S/ {{ number_format($saldoCaja, 2) }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Detalle de Ingresos (Ventas)</div>
    <table class="detail-table">
        <thead>
            <tr>
                <th width="15%">Orden</th>
                <th width="65%">Paciente / Descripción</th>
                <th width="20%" class="text-right">Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ordenes as $o)
            <tr>
                <td>#{{ $o->id }}</td>
                <td>{{ strtoupper($o->patient->last_name) }}, {{ strtoupper($o->patient->first_name) }}</td>
                <td class="text-right">S/ {{ number_format($o->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Detalle de Egresos (Gastos)</div>
    <table class="detail-table">
        <thead>
            <tr>
                <th width="15%">Tipo</th>
                <th width="65%">Descripción del Gasto</th>
                <th width="20%" class="text-right">Monto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($egresos as $e)
            <tr>
                <td>{{ $e->voucher_type }}</td>
                <td>{{ strtoupper($e->description) }}</td>
                <td class="text-right text-danger">S/ {{ number_format($e->amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align: center; color: #999;">No se registraron egresos en esta fecha.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Reporte generado automáticamente el {{ now()->format('d/m/Y H:i A') }}
    </div>

</body>
</html>