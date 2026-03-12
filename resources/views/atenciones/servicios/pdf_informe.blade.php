<div style="font-family: Arial, sans-serif; padding: 40px;">
    <table style="width: 100%; border-bottom: 2px solid #333; margin-bottom: 30px;">
        <tr>
            <td style="width: 20%;">
                <img src="{{ public_path('storage/' . $branch->logo) }}" style="max-width: 100px;">
            </td>
            <td style="text-align: right;">
                <h2 style="margin: 0;">{{ $branch->razon_social }}</h2>
                <p style="margin: 0; font-size: 12px;">RUC: {{ $branch->ruc }}</p>
                <p style="margin: 0; font-size: 12px;">{{ $branch->direccion }}</p>
            </td>
        </tr>
    </table>

    <div style="padding: 20px;">
        {!! $report->html_final !!}
    </div>

    <div style="margin-top: 50px; text-align: center;">
        @if(auth()->user()->firma)
            <img src="{{ public_path('storage/' . auth()->user()->firma) }}" style="max-width: 200px;"><br>
        @endif
    </div>
</div>