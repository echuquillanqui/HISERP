<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket</title>
    <style>
        @page { margin: 0; }
        body { 
            font-family: 'Courier', monospace; 
            width: 75mm; 
            margin: 0; 
            /* MARGENES LATERALES: Todo se queda dentro de estos 6mm */
            padding: 2mm 3mm; 
            font-size: 11px;
            color: #000;
            box-sizing: border-box; /* Evita que el body sea más ancho de 80mm */
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        
        .divider { 
            border-top: 1px dashed #000; 
            margin: 5px 0; 
            width: 100%;
        }

        .logo { 
            max-width: 25mm; 
            height: auto; 
            margin-bottom: 2px; 
            filter: grayscale(100%); 
        }

        table { 
            width: 100%; 
            border-collapse: collapse;
            /* OBLIGA A LA TABLA A RESPETAR LOS MÁRGENES */
            table-layout: fixed; 
        }

        /* Estilos para que la tabla tenga bordes y respete los márgenes */
    .table-bordered {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed; /* Obliga a respetar el ancho de las celdas */
        margin-bottom: 5px;
    }

    .table-bordered th, 
    .table-bordered td {
        border: 1px solid #000; /* Borde negro sólido */
        padding: 4px;           /* Espacio interno para que el texto no toque el borde */
        word-wrap: break-word;  /* Salto de línea si el texto es muy largo */
        overflow-wrap: break-word;
    }

        .item-row td {
            padding: 4px 0;
            vertical-align: top;
            /* SI EL TEXTO ES LARGO, SALTA A LA SIGUIENTE LÍNEA */
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .price-text {
            font-size: 11px;
            font-weight: bold;
            padding-left: 5px;
        }
    </style>
</head>
<body>

    <div class="text-center">
        @if($branch && $branch->logo)
            <img src="{{ public_path('storage/' . $branch->logo) }}" class="logo">
        @endif
        <br>
        <strong style="font-size: 12px;">{{ $branch->razon_social ?? 'EMPRESA' }}</strong><br>
        <strong>{{ $branch->direccion ?? '' }}</strong><br>
        <strong> Tel: {{ $branch->telefono ?? '' }}</strong><br>
    </div>

    <div class="divider"></div>

    <div style="margin-bottom: 5px;">
        <strong>TICKET: {{ str_pad($order->id, 8, '0', STR_PAD_LEFT) }}</strong><br>
        <strong>FECHA: {{ $order->created_at->format('d/m/Y H:i A') }}</strong><br>
        <strong>CLIENTE: {{ $order->patient->last_name }} {{ $order->patient->first_name }}</strong><br>
        <strong>DNI: {{ $order->patient->dni }}</strong>
    </div>

    <div class="divider"></div>

    <table class="table-bordered">
    <thead>
        <tr>
            <th align="center" style="font-size: 10px; width: 70%;">EXAMENES</th>
            <th align="center" style="font-size: 10px; width: 30%;">IMPORTE</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->details as $detail)
            <tr class="item-row">
                <td align="center" class="bold">{{ $detail->name }}</td>
                <td align="center" class="price-text">
                    S/. {{ number_format($detail->price, 2) }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

    <div class="divider" style="border-top-style: solid;"></div>

    <table>
        <tr class="bold">
            <td align="right" style="font-size: 16px; width: 70%;">TOTAL S/. </td>
            <td align="left" style="font-size: 16px; width: 30%; padding-left: 5px;">
                {{ number_format($order->total, 2) }}
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="text-center" style="margin-top: 5px;">
        <span class="bold">¡GRACIAS POR SU VISITA!</span><br>
    </div>

    <div style="margin-bottom: 20px;">.</div>

</body>
</html>