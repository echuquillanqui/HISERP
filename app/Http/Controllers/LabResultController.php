<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\LabResult;
use App\Models\OrderDetail;
use App\Models\User;
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

    $profesionalesSalud = User::where('role', 'medicina')
        ->where('status', true)
        ->orderBy('name')
        ->get(['id', 'name']);

    $tecnologos = User::where('role', 'laboratorio')
        ->where('status', true)
        ->orderBy('name')
        ->get(['id', 'name']);

    $selectedProfessionalId = (string) ($resultados->first()->profesional_id ?? $order->user->id ?? '');
    $selectedTechnologistId = (string) ($resultados->first()->tecnologo_id ?? auth()->id() ?? '');

    return view('labs.resultados.edit', compact(
        'resultadosAgrupados',
        'order',
        'id',
        'profesionalesSalud',
        'tecnologos',
        'selectedProfessionalId',
        'selectedTechnologistId'
    ));
}

    /**
     * Actualiza los registros en la tabla lab_results
     */
    public function update(Request $request, $id)
    {
        $data = $request->input('results', []);
        $profesionalId = $request->input('profesional_id');
        $tecnologoId = $request->input('tecnologo_id');

        foreach ($data as $resId => $values) {
            $labResult = LabResult::findOrFail($resId);

            $rawValue = $values['value'] ?? null;
            $normalizedValue = is_string($rawValue) ? trim($rawValue) : $rawValue;
            $hasValue = $normalizedValue !== null && $normalizedValue !== '';

            $labResult->update([
                'result_value' => $hasValue ? (string) $normalizedValue : null,
                'observations' => $values['observations'] ?? null,
                'status'       => $hasValue ? 'completado' : 'pendiente',
                'profesional_id' => $profesionalId ?: null,
                'tecnologo_id' => $tecnologoId ?: null,
            ]);
        }

        return redirect()->route('lab-results.index')->with('success', 'Resultados guardados correctamente');
    }

    public function show(Request $request, $id)
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

        $selectedProfesionalId = $request->query('profesional_id') ?: $resultados->first()->profesional_id;
        $selectedTecnologoId = $request->query('tecnologo_id') ?: $resultados->first()->tecnologo_id;

        $profesionalSalud = User::where('role', 'medicina')
            ->whereKey($selectedProfesionalId)
            ->first();

        $tecnologo = User::where('role', 'laboratorio')
            ->whereKey($selectedTecnologoId)
            ->first();

        if (!$profesionalSalud) {
            $profesionalSalud = $order->user;
        }

        if (!$tecnologo) {
            $tecnologo = auth()->user();
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('labs.resultados.pdf', [
            'groupedLabs' => $resultados->groupBy(fn($item) => $item->catalog->area->name ?? 'GENERAL'),
            'order'       => $resultados->first()->orderDetail->order,
            'profesionalSalud' => $profesionalSalud,
            'tecnologo'   => $tecnologo, // PASAMOS AL TECNÓLOGO A LA VISTA
            'branch'      => \App\Models\Branch::where('estado', true)->first()
        ]);

        return $pdf->stream("Resultado_Lab_{$order->code}.pdf");
    }
    
}
