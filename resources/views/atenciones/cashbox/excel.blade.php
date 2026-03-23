<table>
    <tr>
        <td colspan="4"><strong>CUADRE DE CAJA - {{ $rangeLabel }}</strong></td>
    </tr>
    <tr>
        <td colspan="4">Desde: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - Hasta: {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</td>
    </tr>
    <tr></tr>
    <tr>
        <td><strong>Total Ingresos</strong></td>
        <td>{{ number_format($totalIngresos, 2, '.', '') }}</td>
        <td><strong>Total Egresos</strong></td>
        <td>{{ number_format($totalEgresos, 2, '.', '') }}</td>
    </tr>
    <tr>
        <td><strong>Saldo Neto</strong></td>
        <td>{{ number_format($saldoCaja, 2, '.', '') }}</td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th colspan="3"><strong>Ingresos</strong></th>
        </tr>
        <tr>
            <th>Orden</th>
            <th>Paciente</th>
            <th>Monto</th>
        </tr>
    </thead>
    <tbody>
        @forelse($ordenes as $o)
            <tr>
                <td>#{{ $o->id }}</td>
                <td>{{ $o->patient->last_name ?? '' }} {{ $o->patient->first_name ?? '' }}</td>
                <td>{{ number_format($o->total, 2, '.', '') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3">Sin ingresos en este período</td>
            </tr>
        @endforelse
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th colspan="3"><strong>Egresos</strong></th>
        </tr>
        <tr>
            <th>Tipo</th>
            <th>Descripción</th>
            <th>Monto</th>
        </tr>
    </thead>
    <tbody>
        @forelse($egresos as $e)
            <tr>
                <td>{{ $e->voucher_type }}</td>
                <td>{{ $e->description }}</td>
                <td>{{ number_format($e->amount, 2, '.', '') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3">Sin egresos en este período</td>
            </tr>
        @endforelse
    </tbody>
</table>
