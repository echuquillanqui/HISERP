<table>
    <thead>
        <tr>
            <th colspan="23">Reporte Operativo de Tomografía - {{ $rangeLabel }} ({{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }})</th>
        </tr>
        <tr>
            <th>N°</th>
            <th>Fecha</th>
            <th>Nombre y apellido del paciente</th>
            <th>DNI</th>
            <th>ORDEN DE SERVICIO</th>
            <th>Tipo de tomografía</th>
            <th>S/C C/C</th>
            <th>USO IOPAMIDOL</th>
            <th>CONVENIO</th>
            <th>SERVICIO</th>
            <th>EFECTIVO</th>
            <th>YAPE</th>
            <th>TRANSF. BANCARIA</th>
            <th>POR COBRAR</th>
            <th>PLACAS ENTREGADAS</th>
            <th>SALDO PLACAS</th>
            <th>SALDO IOPAMIDOL</th>
            <th>MEDICO SOLICITANTE</th>
            <th>Doctor (informe)</th>
            <th>MEDIO</th>
            <th>BOLETA O FACTURA</th>
            <th>N° BV Y FAC</th>
            <th>RECEPCION</th>
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
                <td>{{ number_format($row['uso_iopamidol'], 2, '.', '') }}</td>
                <td>{{ $row['convenio'] }}</td>
                <td>{{ $row['servicio'] }}</td>
                <td>{{ number_format($row['efectivo'], 2, '.', '') }}</td>
                <td>{{ number_format($row['yape'], 2, '.', '') }}</td>
                <td>{{ number_format($row['transferencia'], 2, '.', '') }}</td>
                <td>{{ number_format($row['por_cobrar'], 2, '.', '') }}</td>
                <td>{{ $row['placas_entregadas'] }}</td>
                <td>{{ $row['saldo_placas'] }}</td>
                <td>{{ number_format($row['saldo_iopamidol'], 2, '.', '') }}</td>
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
