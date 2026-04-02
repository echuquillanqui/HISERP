<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Operativo Tomografía</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        h3 { margin: 0 0 4px 0; }
        .meta { margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 3px; text-align: left; }
        th { background: #d9ead3; font-size: 8px; }
    </style>
</head>
<body>
    <h3>Reporte Operativo de Tomografía</h3>
    <div class="meta">
        Periodo {{ $rangeLabel }}: {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Fecha</th>
                <th>Paciente</th>
                <th>DNI</th>
                <th>Orden servicio</th>
                <th>Tipo tomografía</th>
                <th>S/C C/C</th>
                <th>Uso Iopamidol</th>
                <th>Convenio</th>
                <th>Servicio</th>
                <th>Efectivo</th>
                <th>Yape</th>
                <th>Transf.</th>
                <th>Por cobrar</th>
                <th>Placas entregadas</th>
                <th>Saldo placas</th>
                <th>Saldo iopamidol</th>
                <th>Médico solicitante</th>
                <th>Doctor informe</th>
                <th>Medio</th>
                <th>Boleta/Factura</th>
                <th>N° BV/FAC</th>
                <th>Recepción</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['numero'] }}</td>
                    <td>{{ $row['fecha'] }}</td>
                    <td>{{ $row['paciente'] }}</td>
                    <td>{{ $row['dni'] }}</td>
                    <td>{{ $row['orden_servicio'] }}</td>
                    <td>{{ $row['tipo_tomografia'] }}</td>
                    <td>{{ $row['sc_cc'] }}</td>
                    <td>{{ number_format($row['uso_iopamidol'], 2) }}</td>
                    <td>{{ $row['convenio'] }}</td>
                    <td>{{ $row['servicio'] }}</td>
                    <td>{{ number_format($row['efectivo'], 2) }}</td>
                    <td>{{ number_format($row['yape'], 2) }}</td>
                    <td>{{ number_format($row['transferencia'], 2) }}</td>
                    <td>{{ number_format($row['por_cobrar'], 2) }}</td>
                    <td>{{ $row['placas_entregadas'] }}</td>
                    <td>{{ $row['saldo_placas'] }}</td>
                    <td>{{ number_format($row['saldo_iopamidol'], 2) }}</td>
                    <td>{{ $row['medico_solicitante'] }}</td>
                    <td>{{ $row['doctor_informe'] }}</td>
                    <td>{{ $row['medio'] }}</td>
                    <td>{{ $row['boleta_factura'] }}</td>
                    <td>{{ $row['numero_doc'] }}</td>
                    <td>{{ $row['recepcion'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="23">No hay registros en el rango seleccionado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
