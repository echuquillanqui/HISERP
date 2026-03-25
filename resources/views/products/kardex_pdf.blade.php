<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Reporte de Kardex' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { margin: 0 0 6px 0; font-size: 20px; }
        .meta { margin-bottom: 14px; color: #4b5563; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px; font-size: 10px; }
        th { background: #f3f4f6; text-align: left; }
    </style>
</head>
<body>
    <h1>{{ $title ?? 'Reporte de Entradas y Salidas (Kardex)' }}</h1>
    <div class="meta">
        Sede: {{ $branch?->name ?? 'No configurada' }}<br>
        Generado: {{ $generatedAt->format('d/m/Y H:i') }}<br>
        Producto: {{ !empty($filters['product_id']) ? ($movements->first()?->product?->name ?? 'Filtrado') : 'Todos' }}<br>
        Desde: {{ $filters['from_date'] ?? '---' }} | Hasta: {{ $filters['to_date'] ?? '---' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Producto</th>
                <th>Tipo</th>
                <th>Cant.</th>
                <th>Antes</th>
                <th>Después</th>
                <th>Origen</th>
                <th>Orden</th>
                <th>Observación</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $move)
                <tr>
                    <td>{{ optional($move->movement_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $move->product?->name }}</td>
                    <td>{{ strtoupper($move->movement_type) }}</td>
                    <td>{{ $move->quantity }}</td>
                    <td>{{ $move->stock_before }}</td>
                    <td>{{ $move->stock_after }}</td>
                    <td>{{ strtoupper($move->source) }}</td>
                    <td>{{ $move->order?->code ?? ($move->order_id ? '#'.$move->order_id : '-') }}</td>
                    <td>{{ $move->notes }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center;">No hay movimientos para mostrar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
