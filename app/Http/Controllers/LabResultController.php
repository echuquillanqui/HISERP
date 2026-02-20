<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\LabResult;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Order;


class LabResultController extends Controller
{
    /**
     * Mostrar listado de órdenes con filtros
     */
    public function index(Request $request)
    {
        // 1. Capturar filtros (Por defecto hoy)
        $date = $request->input('date', now()->format('Y-m-d'));
        $status = $request->input('status');
        $search = $request->input('search');

        // 2. Consulta base con relaciones
        $query = Order::with(['patient', 'details.labResults']);

        // 3. Filtro por Fecha de creación de la Orden
        if ($date) {
            $query->whereDate('created_at', $date);
        }

        // 4. Filtro por STATUS de la tabla LabResults (tu migración)
        if ($status) {
            $query->whereHas('details.labResults', function($q) use ($status) {
                $q->where('status', $status);
            });
        }

        // 5. Búsqueda por DNI, Nombre o Código
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                ->orWhereHas('patient', function($p) use ($search) {
                    $p->where('dni', 'LIKE', "%{$search}%")
                        ->orWhere('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%");
                });
            });
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        // IMPORTANTE: Los nombres aquí deben coincidir con x-data en la vista
        return view('labs.resultados.index', compact('orders', 'date', 'status', 'search'));
    }

    // En OrderController.php (o el controlador que prefieras para Laboratorio)

    public function edit($id)
{
    // Buscamos los resultados de la orden e incluimos la relación con el catálogo y su área
    $resultados = \App\Models\LabResult::whereHas('orderDetail', function($q) use ($id) {
        $q->where('order_id', $id);
    })->with(['catalog.area', 'orderDetail.order.patient'])->get();

    if ($resultados->isEmpty()) {
        return redirect()->route('lab-results.index')->with('error', 'No hay exámenes.');
    }

    $order = $resultados->first()->orderDetail->order;

    // --- FILTRADO DE ÁREAS ---
    // Agregamos este filtro para excluir Medicina y Adicionales antes de agrupar
    $resultadosFiltrados = $resultados->filter(function($res) {
        $areaNombre = strtoupper($res->catalog->area->name ?? '');
        return !in_array($areaNombre, ['MEDICINA', 'ADICIONALES']);
    });

    // --- AGRUPACIÓN POR ÁREA ---
    // Usamos la colección filtrada para crear los bloques en la vista
    $resultadosAgrupados = $resultadosFiltrados->groupBy(function($res) {
        return $res->catalog->area->name ?? 'GENERAL';
    });

    return view('labs.resultados.edit', compact('resultadosAgrupados', 'order', 'id'));
}

    /**
     * Actualiza los registros en la tabla lab_results
     */
    public function update(Request $request, $id)
    {
        $data = $request->input('results', []);

        foreach ($data as $resId => $values) {
            $labResult = LabResult::findOrFail($resId);
            
            // Lógica de estado según el contenido del valor
            $nuevoStatus = (empty($values['value'])) ? 'pendiente' : 'completado';

            $labResult->update([
                'result_value' => $values['value'],
                'observations' => $values['observations'] ?? null,
                'status'       => $nuevoStatus
            ]);
        }

        return redirect()->route('lab-results.index')->with('success', 'Resultados guardados correctamente');
    }

    public function show($id)
{
    // Carga de datos con relaciones para evitar múltiples consultas (Eager Loading)
    $resultados = \App\Models\LabResult::whereHas('orderDetail', function($q) use ($id) {
        $q->where('order_id', $id);
    })->with(['catalog.area', 'orderDetail.order.patient', 'orderDetail.order.user'])->get();

    if ($resultados->isEmpty()) {
        return redirect()->back()->with('error', 'No hay resultados registrados para esta orden.');
    }

    $order = $resultados->first()->orderDetail->order;
    $branch = \App\Models\Branch::where('estado', true)->first();

    // Agrupamos por el nombre del área del catálogo
    $groupedLabs = $resultados->groupBy(function($item) {
        return $item->catalog->area->name ?? 'GENERAL';
    });

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('labs.resultados.pdf', compact('groupedLabs', 'order', 'branch'))
                ->setPaper('a4');

    return $pdf->stream("Resultado_Lab_{$order->code}.pdf");
}
    
}