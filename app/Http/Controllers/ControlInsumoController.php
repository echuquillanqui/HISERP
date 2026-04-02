<?php

namespace App\Http\Controllers;

use App\Models\OrderTomography;
use App\Models\TomographyResult;
use App\Models\TomographySupplyControl;
use Illuminate\Http\Request;

class ControlInsumoController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');

        $ordersQuery = OrderTomography::query()
            ->with(['items.radiography:id,plate_usage'])
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('created_at', '<=', $to));

        $resultsQuery = TomographyResult::query()
            ->when($from, fn ($query) => $query->whereDate('result_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('result_date', '<=', $to));

        $entriesQuery = TomographySupplyControl::query()
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('created_at', '<=', $to));

        $platesIn = (int) (clone $entriesQuery)->sum('plates_in');
        $iopamidolIn = (float) (clone $entriesQuery)->sum('iopamidol_in');

        $platesOutOrders = (clone $ordersQuery)->get()->sum(function (OrderTomography $order) {
            return $order->items->sum(function ($item) {
                $plateUsage = (int) ($item->radiography->plate_usage ?? 0);
                return ((int) $item->quantity) * $plateUsage;
            });
        });

        $platesOutResults = (int) (clone $resultsQuery)->sum('plates_used');
        $iopamidolOutResults = (float) (clone $resultsQuery)->sum('iopamidol_used');

        $summary = [
            'plates_in' => $platesIn,
            'plates_out' => (int) $platesOutOrders + $platesOutResults,
            'plates_balance' => $platesIn - ((int) $platesOutOrders + $platesOutResults),
            'iopamidol_in' => round($iopamidolIn, 2),
            'iopamidol_out' => round($iopamidolOutResults, 2),
            'iopamidol_balance' => round($iopamidolIn - $iopamidolOutResults, 2),
            'orders_count' => (clone $ordersQuery)->count(),
            'results_count' => (clone $resultsQuery)->count(),
        ];

        $entries = TomographySupplyControl::query()
            ->latest('id')
            ->limit(20)
            ->get();

        return view('radiology.control_insumos.index', [
            'summary' => $summary,
            'entries' => $entries,
            'filters' => ['from' => $from, 'to' => $to],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'plates_in' => ['nullable', 'integer', 'min:0'],
            'iopamidol_in' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $platesIn = (int) ($data['plates_in'] ?? 0);
        $iopamidolIn = (float) ($data['iopamidol_in'] ?? 0);

        if ($platesIn === 0 && $iopamidolIn === 0.0) {
            return back()->withErrors(['plates_in' => 'Debe registrar al menos una entrada de placas o iopamidol.'])->withInput();
        }

        $currentPlatesBalance = (int) TomographySupplyControl::sum('plates_in')
            - ((int) OrderTomography::with(['items.radiography:id,plate_usage'])->get()->sum(function (OrderTomography $order) {
                return $order->items->sum(function ($item) {
                    return ((int) $item->quantity) * ((int) ($item->radiography->plate_usage ?? 0));
                });
            }) + (int) TomographyResult::sum('plates_used'));

        $currentIopamidolBalance = (float) TomographySupplyControl::sum('iopamidol_in') - (float) TomographyResult::sum('iopamidol_used');

        TomographySupplyControl::create([
            'plates_in' => $platesIn,
            'iopamidol_in' => $iopamidolIn,
            'plates_balance' => $currentPlatesBalance + $platesIn,
            'iopamidol_balance' => round($currentIopamidolBalance + $iopamidolIn, 2),
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('control-insumos.index')->with('success', 'Entrada registrada correctamente.');
    }
}
