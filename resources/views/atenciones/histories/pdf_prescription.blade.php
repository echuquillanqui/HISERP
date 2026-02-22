<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Receta Médica</title>
    <style>
        @page { margin: 2cm; }
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.4; }
        
        /* Encabezado Institucional (Recuperado de Lab) */
        .header-table { width: 100%; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; margin-bottom: 20px; }
        .branch-logo { max-height: 80px; max-width: 150px; }
        .branch-info h2 { margin: 0; color: #2c3e50; font-size: 18px; text-transform: uppercase; }
        .branch-details { font-size: 11px; color: #555; }

        /* Título del Documento */
        .doc-title { text-align: right; vertical-align: middle; }
        .doc-title h3 { margin: 0; color: #e67e22; font-size: 17px; text-transform: uppercase; }
        .doc-number { font-size: 13px; font-weight: bold; color: #2c3e50; }

        /* Cuadro de Paciente en 3 Columnas (Igual que Lab) */
        .patient-box { 
            width: 100%; border: 1.5px solid #2c3e50; border-radius: 8px; 
            padding: 15px; margin-bottom: 25px; background-color: #fcfcfc;
        }
        .patient-table { width: 100%; border-collapse: collapse; }
        .label { font-weight: bold; color: #2c3e50; font-size: 11px; display: block; text-transform: uppercase; margin-bottom: 2px; }
        .data { font-size: 14px; font-weight: bold; color: #000; }

        /* TABLA DE MEDICAMENTOS */
        .prescription-container { min-height: 400px; }
        .med-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .med-table th { 
            background-color: #f8f9fa; 
            color: #2c3e50; 
            text-align: left; 
            padding: 10px; 
            font-size: 11px; 
            border-bottom: 2px solid #e67e22;
            text-transform: uppercase;
        }
        .med-table td { 
            padding: 12px 10px; 
            font-size: 12px; 
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        .med-name { font-weight: bold; color: #2c3e50; text-transform: uppercase; }
        .med-qty { font-weight: bold; color: #e67e22; text-align: center; }

        /* Firma y Footer */
        .signature-container { margin-top: 80px; text-align: center; }
        .signature-line { width: 250px; border-top: 1.5px solid #2c3e50; margin: 0 auto; padding-top: 5px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td width="20%">
                @if($branch && $branch->logo)
                    <img src="{{ storage_path('app/public/' . $branch->logo) }}" class="branch-logo">
                @else
                    <div style="width: 80px; height: 60px; background: #eee; border: 1px solid #ccc; text-align: center; line-height: 60px; font-size: 10px;">LOGO</div>
                @endif
            </td>
            <td width="50%" style="padding-left: 10px;">
                <div class="branch-info">
                    @if($branch)
                        <h2>{{ $branch->razon_social }}</h2>
                        <div class="branch-details">
                            <strong>RUC:</strong> {{ $branch->ruc }}<br>
                            <strong>Dirección:</strong> {{ $branch->direccion }}<br>
                            <strong>Teléfono:</strong> {{ $branch->telefono ?? 'S/N' }}
                        </div>
                    @endif
                </div>
            </td>
            <td width="30%" class="doc-title">
                <h3>RECETA MÉDICA</h3>
                <div class="doc-number">Atención N° {{ str_pad($history->id, 6, '0', STR_PAD_LEFT) }}</div>
            </td>
        </tr>
    </table>

    <div class="patient-box">
        <table class="patient-table">
            <tr>
                <td width="45%">
                    <span class="label">Nombre del Paciente</span>
                    <span class="data">{{ strtoupper($history->patient->first_name) }} {{ strtoupper($history->patient->last_name) }}</span>
                </td>
                <td width="25%">
                    <span class="label">DNI / Documento</span>
                    <span class="data">{{ $history->patient->dni }}</span>
                </td>
                <td width="30%">
                    <span class="label">Edad / Sexo</span>
                    <span class="data">
                        {{ $history->patient->age_detail }} / 
                        {{ $history->patient->gender == 'M' ? 'MASC' : 'FEM' }}
                    </span>
                </td>
            </tr>
            <tr>
                <td style="padding-top: 10px;">
                    <span class="label">Fecha de Emisión</span>
                    <span class="data">{{ $history->created_at->format('d/m/Y H:i A') }}</span>
                </td>
                <td colspan="2" style="padding-top: 10px;">
                    <span class="label">Médico Tratante</span>
                    <span class="data">DR. {{ strtoupper($history->user->name) }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="prescription-container">
        <div style="background: #e67e22; color: white; padding: 6px 12px; font-weight: bold; border-radius: 4px; display: inline-block; margin-bottom: 10px; font-size: 12px;">
            RP / PRESCRIPCIÓN
        </div>

        <table class="med-table">
            <thead>
                <tr>
                    <th width="35%">Medicamento / Producto</th>
                    <th width="50%">Indicaciones</th>
                    <th width="15%" style="text-align: center;">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history->prescriptionItems as $item)
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">
                            <strong style="color: #2c3e50; font-size: 13px;">{{ $item->product->name }}</strong><br>
                            <small style="color: #7f8c8d;">
                                {{ $item->product->concentration }} | {{ $item->product->presentation }}
                            </small>
                        </td>
                        <td style="text-transform: uppercase;">
                            {{ $item->indicaciones ?? 'SEGÚN CRITERIO MÉDICO' }}
                        </td>
                        <td class="med-qty">
                            {{ $item->cantidad }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="text-align: center; color: #999; padding: 30px;">
                            No hay medicamentos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="background: #f8f9fa; border: 1px solid #dee2e6; color: #2c3e50; padding: 5px 12px; font-weight: 600; border-radius: 20px; display: inline-flex; align-items: center; margin-bottom: 10px; margin-top: 20px; font-size: 14px;">
            <span class="badge rounded-pill me-2" style="background: #e67e22;">
                <i class="bi bi-calendar-check"></i>
            </span>
            Próxima Cita: 
            <span class="text-primary ms-1">
                {{ $history->prescription?->fecha_sig_cita ? \Carbon\Carbon::parse($history->prescription->fecha_sig_cita)->format('d/m/Y') : 'Pendiente' }}
            </span>
        </div>
    </div>

    <div class="signature-container">
        <div class="signature-line">
            <strong style="font-size: 14px;">DR. {{ strtoupper($history->user->name) }}</strong><br>
            <span style="font-size: 11px; color: #666;">Firma y Sello Médico</span>
        </div>
    </div>

    <div class="footer">
        Este documento es una receta médica oficial generada el {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>