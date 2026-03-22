<?php

namespace App\Http\Controllers;

use App\Models\OrderDetail;
use App\Models\Template;
use App\Models\ReportService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class ServiceResultController extends Controller
{
    // Listado principal con botones dinámicos
    public function index(Request $request)
    {
        $fecha = $request->input('date', now()->toDateString());
        $query = OrderDetail::where('itemable_type', \App\Models\Service::class)
            ->whereExists(function ($q) {
                $q->selectRaw('1')
                    ->from('templates')
                    ->whereColumn('templates.service_id', 'order_details.itemable_id');
            })
            ->with(['order.patient', 'reportService'])
            ->whereHas('order', function($q) use ($fecha) {
                $q->whereDate('created_at', $fecha);
            })->latest();

        if ($request->has('search') && !empty($request->search)) {
            $q = $request->search;
            $query->whereHas('order.patient', function($qBuilder) use ($q) {
                $qBuilder->where('first_name', 'LIKE', "%$q%")->orWhere('last_name', 'LIKE', "%$q%")->orWhere('dni', 'LIKE', "%$q%");
            });
        }
        $details = $query->paginate(15);

        $serviceIds = $details->pluck('itemable_id')->filter()->unique()->values();
        $templatesByService = Template::whereIn('service_id', $serviceIds)
            ->get(['id', 'service_id', 'html_content'])
            ->keyBy('service_id');

        $details->getCollection()->transform(function ($detail) use ($templatesByService) {
            $template = $templatesByService->get($detail->itemable_id);
            $report = $detail->reportService;

            if (!$template || !$report) {
                $detail->report_completed = false;
                return $detail;
            }

            $initialHtml = $this->prepararContenidoInicial($template->html_content, $detail->order->loadMissing('patient'));
            $detail->report_completed = trim((string) $report->html_final) !== trim((string) $initialHtml);

            return $detail;
        });

        return view('atenciones.servicios.index', compact('details'));
    }

    // Carga el editor (Redactar o Editar)
    public function atenderServicio(OrderDetail $detail) 
    {
        $report = ReportService::where('order_detail_id', $detail->id)->first();
        $template = Template::where('service_id', $detail->itemable_id)->first();
        
        if (!$template) return back()->with('error', 'Sin plantilla configurada.');

        // Si ya hay reporte, cargamos el HTML guardado. Si no, la plantilla inicial.
        $htmlContent = $report ? $report->html_final : $this->prepararContenidoInicial($template->html_content, $detail->order->load('patient'));

        return view('atenciones.servicios.form_informe', compact('detail', 'htmlContent', 'template'));
    }

    // Guardado (Crea si es nuevo, Actualiza si es edición)
    public function guardarInforme(Request $request, $detailId)
    {
        $request->validate(['html_final' => 'required', 'template_id' => 'required']);

        ReportService::updateOrCreate(
            ['order_detail_id' => $detailId],
            [
                'template_id'     => $request->template_id,
                'html_final'      => $request->html_final,
                'user_id'         => auth()->id(),
                'resultados_json' => '{}' // Valor por defecto para evitar el error 1364
            ]
        );

        return redirect()->route('serviceresults.index')->with('success', 'Informe guardado exitosamente.');
    }

    // Impresión usando Base64 para evitar errores de imagen
    public function imprimirReporte($reportId)
    {
        $report = ReportService::with('user')->findOrFail($reportId);
        $detail = $report->orderDetail->loadMissing('order.patient');
        $patient = $detail->order->patient;
        $branch = \App\Models\Branch::first();
        $professional = $report->user;
        
        $logoBase64 = $this->convertirImagenABase64($branch->logo ?? null);
        $firmaBase64 = $this->convertirImagenABase64($professional->firma ?? null);
        $firmaMeta = $this->buildFirmaMetadata($professional);

        $pdf = PDF::loadView('atenciones.servicios.pdf_informe', compact('report', 'detail', 'patient', 'branch', 'logoBase64', 'firmaBase64', 'firmaMeta'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true, 
                'isRemoteEnabled' => true,
                'margin_top' => 10, 'margin_right' => 10, 'margin_bottom' => 10, 'margin_left' => 10,
            ]);
        
        return $pdf->stream('informe_' . $patient->dni . '.pdf');
    }

    private function prepararContenidoInicial($html, $order) 
    {
        $patient = $order->patient;
        $currentUser = auth()->user();
        $firmaMeta = $this->buildFirmaMetadata($currentUser);
        $sexo = $this->normalizeGender(data_get($patient, 'gender'));

        $replacements = [
            '{{nombre_paciente}}'      => trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')),
            '{{paciente}}'             => trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')),
            '{{dni_paciente}}'         => $patient->dni ?? '--',
            '{{sexo_paciente}}'        => $sexo ?: '--',
            '{{fecha_actual}}'         => now()->format('d/m/Y'),
            '{{codigo_orden}}'         => $order->code ?? '--',
            '{{regimen_aseguramiento}}'=> data_get($patient, 'insurance_regime', '--') ?: '--',
            '{{codigo_afiliacion}}'    => data_get($patient, 'insurance_code', '--') ?: '--',
            '{{firma_medico}}'         => $this->buildFirmaHtml($firmaMeta),
        ];

        $html = $this->applyConditionalBlocks($html, $sexo);

        return str_replace(array_keys($replacements), array_values($replacements), $html);
    }

    private function applyConditionalBlocks(string $html, ?string $sexo): string
    {
        $isMale = $sexo === 'M';
        $isFemale = $sexo === 'F';

        $patterns = [
            '/\{\{#if_hombre\}\}(.*?)\{\{\/if_hombre\}\}/is' => $isMale ? '$1' : '',
            '/\{\{#if_mujer\}\}(.*?)\{\{\/if_mujer\}\}/is' => $isFemale ? '$1' : '',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $html = preg_replace($pattern, $replacement, $html) ?? $html;
        }

        return $html;
    }

    private function normalizeGender(?string $gender): ?string
    {
        if (!$gender) {
            return null;
        }

        $gender = mb_strtolower(trim($gender));

        return match ($gender) {
            'm', 'masculino', 'male', 'hombre' => 'M',
            'f', 'femenino', 'female', 'mujer' => 'F',
            default => null,
        };
    }

    private function buildFirmaMetadata($user): array
    {
        $profession = $user && $user->role === 'medicina' ? 'MÉDICO' : 'LICENCIADO';

        return [
            'nombre' => $user->name ?? '--',
            'colegiatura' => $user->colegiatura ?? '--',
            'profesion' => $profession,
        ];
    }

    private function buildFirmaHtml(array $firmaMeta): string
    {
        return '<div style="text-align:center;margin-top:30px;">'
            . '<div style="border-top:1px solid #000;width:260px;margin:0 auto 8px auto;"></div>'
            . '<div style="font-weight:bold;">' . e($firmaMeta['nombre']) . '</div>'
            . '<div>' . e($firmaMeta['profesion']) . '</div>'
            . '<div>COL. ' . e($firmaMeta['colegiatura']) . '</div>'
            . '</div>';
    }

    private function convertirImagenABase64($path)
    {
        if (!$path) return null;
        $fullPath = storage_path('app/public/' . $path);
        if (!file_exists($fullPath)) return null;
        $type = pathinfo($fullPath, PATHINFO_EXTENSION);
        return 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($fullPath));
    }
}
