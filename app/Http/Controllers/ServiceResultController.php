<?php

namespace App\Http\Controllers;

use App\Models\OrderDetail;
use App\Models\Template;
use App\Models\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;


class ServiceResultController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Si no hay fecha en el request, usamos hoy
        $fecha = $request->input('date', now()->toDateString());

        $query = \App\Models\OrderDetail::where('itemable_type', \App\Models\Service::class)
            ->with(['order.patient', 'reportService'])
            ->whereHas('order', function($q) use ($fecha) {
                // Filtramos las órdenes creadas en la fecha seleccionada
                $q->whereDate('created_at', $fecha);
            })
            ->latest();

        // Filtro por búsqueda (nombre o DNI)
        if ($request->has('search') && !empty($request->search)) {
            $q = $request->search;
            $query->whereHas('order.patient', function($qBuilder) use ($q) {
                $qBuilder->where('first_name', 'LIKE', "%$q%")
                        ->orWhere('last_name', 'LIKE', "%$q%")
                        ->orWhere('dni', 'LIKE', "%$q%");
            });
        }

        $details = $query->paginate(15);
        return view('atenciones.servicios.index', compact('details', 'fecha'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function atenderServicio(OrderDetail $detail) 
    {
        $template = Template::where('service_id', $detail->itemable_id)->first();
        
        if (!$template) return back()->with('error', 'Sin plantilla configurada.');

        $order = $detail->order->load('patient');
        
        // CORRECCIÓN: Llama al método privado en lugar de hacer el replace manual
        $htmlContent = $this->prepararContenidoInicial($template->html_content, $order);

        return view('atenciones.servicios.form_informe', compact('detail', 'htmlContent', 'template'));
    }

    public function guardarInforme(Request $request, OrderDetail $detail) 
    {
        // 1. Validar datos mínimos
        $request->validate([
            'html_final'  => 'required',
            'template_id' => 'required|exists:templates,id'
        ]);

        // 2. Actualizar o crear
        \App\Models\ReportService::updateOrCreate(
            ['order_detail_id' => $detail->id],
            [
                'template_id'      => $request->template_id,
                'html_final'       => $request->html_final,
                // Si el frontend envía datos estructurados, los guardamos aquí
                'resultados_json'  => json_encode($request->resultados ?? []),
            ]
        );

        // 3. Redirección lógica: al listado de atenciones, no al de ventas
        return redirect()->route('serviceresults.index')
                        ->with('success', 'El informe médico ha sido guardado correctamente.');
    }

    public function imprimirReporte($reportId)
    {
        $report = \App\Models\ReportService::findOrFail($reportId);
        $detail = $report->orderDetail;
        $patient = $detail->order->patient;
        $branch = \App\Models\Branch::first(); // Asegúrate de enviarlo

        $pdf = \PDF::loadView('atenciones.servicios.pdf_informe', compact('report', 'detail', 'patient', 'branch'));
        
        return $pdf->stream('informe_' . $patient->dni . '.pdf');
    }

    private function prepararContenidoInicial($html, $order) 
    {
        $branch = \App\Models\Branch::first(); 
        
        // Si no hay sucursal, evitamos que el sistema colapse
        $razonSocial = $branch ? $branch->razon_social : 'Nombre de Empresa';
        $direccion = $branch ? $branch->direccion : 'Dirección no definida';
        
        return str_replace(
            ['{{nombre_paciente}}', '{{dni_paciente}}', '{{fecha_actual}}', '{{empresa}}', '{{direccion}}'],
            [
                $order->patient->first_name . ' ' . $order->patient->last_name, 
                $order->patient->dni, 
                date('d/m/Y'),
                $razonSocial,
                $direccion
            ],
            $html
        );
    }

    
}
