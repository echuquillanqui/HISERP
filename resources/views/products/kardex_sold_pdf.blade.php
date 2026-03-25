<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Medicamentos Vendidos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { margin: 0 0 6px 0; font-size: 20px; }
        .meta { margin-bottom: 14px; color: #4b5563; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px; font-size: 10px; }
        th { background: #f3f4f6; text-align: left; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>Reporte de medicamentos vendidos</h1>
    <div class="meta">
        Sede: {{ $branch?->name ?? 'No configurada' }}<br>
        Generado: {{ $generatedAt->format('d/m/Y H:i') }}<br>
        Desde: {{ $filters['from_date'] ?? '---' }} | Hasta: {{ $filters['to_date'] ?? '---' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Medicamento</th>
                <th>Código</th>
                <th class="right">Unidades vendidas</th>
                <th class="right">Total vendido</th>
            </tr>
        </thead>
        <tbody>
            @forelse($salesReport as $row)
                <tr>
                    <td>{{ $row->product?->name ?? 'N/A' }}</td>
                    <td>{{ $row->product?->code ?? '-' }}</td>
                    <td class="right">{{ (int) $row->sold_units }}</td>
                    <td class="right">S/ {{ number_format((float) $row->sold_total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">Sin ventas en el rango seleccionado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
