<?php

namespace App\Exports;

use App\Models\Expense;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CashBoxExport implements FromView
{
    public function __construct(
        private Carbon $startDate,
        private Carbon $endDate,
        private string $rangeLabel
    ) {
    }

    public function view(): View
    {
        $ordenes = Order::with(['patient'])
            ->whereBetween('created_at', [$this->startDate->copy()->startOfDay(), $this->endDate->copy()->endOfDay()])
            ->get();

        $egresos = Expense::whereBetween('created_at', [$this->startDate->copy()->startOfDay(), $this->endDate->copy()->endOfDay()])
            ->get();

        $totalIngresos = $ordenes->sum('total');
        $totalEgresos = $egresos->sum('amount');
        $saldoCaja = $totalIngresos - $totalEgresos;

        return view('atenciones.cashbox.excel', [
            'ordenes' => $ordenes,
            'egresos' => $egresos,
            'totalIngresos' => $totalIngresos,
            'totalEgresos' => $totalEgresos,
            'saldoCaja' => $saldoCaja,
            'rangeLabel' => $this->rangeLabel,
            'startDate' => $this->startDate->toDateString(),
            'endDate' => $this->endDate->toDateString(),
        ]);
    }
}
