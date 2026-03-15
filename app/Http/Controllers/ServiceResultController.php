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
        $report = ReportService::findOrFail($reportId);
        $detail = $report->orderDetail;
        $patient = $detail->order->patient;
        $branch = \App\Models\Branch::first();
        
        $logoBase64 = $this->convertirImagenABase64($branch->logo ?? null);
        $firmaBase64 = $this->convertirImagenABase64(auth()->user()->firma ?? null);

        $pdf = PDF::loadView('atenciones.servicios.pdf_informe', compact('report', 'detail', 'patient', 'branch', 'logoBase64', 'firmaBase64'))
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
        $placeholders = ['{{nombre_paciente}}', '{{dni_paciente}}', '{{fecha_actual}}'];
        $valores = [
            $order->patient->first_name . ' ' . $order->patient->last_name,
            $order->patient->dni,
            now()->format('d/m/Y')
        ];
        return str_replace($placeholders, $valores, $html);
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
