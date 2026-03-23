<?php

namespace App\Http\Controllers;

use App\Exports\CashBoxExport;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class CashBoxController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->resolveDateRange($request);

        $ordenes = Order::with(['patient', 'details'])
            ->whereBetween('created_at', [$filters['startDate']->copy()->startOfDay(), $filters['endDate']->copy()->endOfDay()])
            ->get();

        $egresos = Expense::whereBetween('created_at', [$filters['startDate']->copy()->startOfDay(), $filters['endDate']->copy()->endOfDay()])
            ->get();

        $totalIngresos = $ordenes->sum('total');
        $totalEgresos = $egresos->sum('amount');
        $saldoCaja = $totalIngresos - $totalEgresos;

        return view('atenciones.cashbox.index', [
            'ordenes' => $ordenes,
            'egresos' => $egresos,
            'totalIngresos' => $totalIngresos,
            'totalEgresos' => $totalEgresos,
            'saldoCaja' => $saldoCaja,
            'period' => $filters['period'],
            'startDate' => $filters['startDate']->toDateString(),
            'endDate' => $filters['endDate']->toDateString(),
            'rangeLabel' => $filters['label'],
        ]);
    }

    public function storeExpense(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'voucher_type' => 'required',
            'amount' => 'required|numeric|min:0',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('expenses', 'public');
        }

        Expense::create([
            'description' => $request->description,
            'voucher_type' => $request->voucher_type,
            'amount' => $request->amount,
            'file_path' => $path,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Gasto registrado correctamente.');
    }

    public function updateExpense(Request $request, Expense $expense)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'voucher_type' => 'required',
            'amount' => 'required|numeric|min:0',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $data = $request->only(['description', 'voucher_type', 'amount']);

        if ($request->hasFile('document')) {
            if ($expense->file_path) {
                Storage::disk('public')->delete($expense->file_path);
            }
            $data['file_path'] = $request->file('document')->store('expenses', 'public');
        }

        $expense->update($data);

        return back()->with('success', 'Gasto actualizado correctamente.');
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->resolveDateRange($request);
        $branch = Branch::first();

        $ordenes = Order::with(['patient', 'details'])
            ->whereBetween('created_at', [$filters['startDate']->copy()->startOfDay(), $filters['endDate']->copy()->endOfDay()])
            ->get();

        $egresos = Expense::whereBetween('created_at', [$filters['startDate']->copy()->startOfDay(), $filters['endDate']->copy()->endOfDay()])
            ->get();

        $totalIngresos = $ordenes->sum('total');
        $totalEgresos = $egresos->sum('amount');
        $saldoCaja = $totalIngresos - $totalEgresos;

        $data = [
            'ordenes' => $ordenes,
            'egresos' => $egresos,
            'totalIngresos' => $totalIngresos,
            'totalEgresos' => $totalEgresos,
            'saldoCaja' => $saldoCaja,
            'branch' => $branch,
            'rangeLabel' => $filters['label'],
            'startDate' => $filters['startDate']->toDateString(),
            'endDate' => $filters['endDate']->toDateString(),
        ];

        $pdf = Pdf::loadView('atenciones.cashbox.pdf', $data);

        return $pdf->download(sprintf('cuadre_caja_%s_a_%s.pdf', $filters['startDate']->toDateString(), $filters['endDate']->toDateString()));
    }

    public function exportExcel(Request $request)
    {
        $filters = $this->resolveDateRange($request);

        return Excel::download(
            new CashBoxExport($filters['startDate'], $filters['endDate'], $filters['label']),
            sprintf('cuadre_caja_%s_a_%s.xlsx', $filters['startDate']->toDateString(), $filters['endDate']->toDateString())
        );
    }

    private function resolveDateRange(Request $request): array
    {
        $period = $request->get('period', 'daily');
        $today = Carbon::today();

        switch ($period) {
            case 'weekly':
                $startDate = $today->copy()->startOfWeek(Carbon::MONDAY);
                $endDate = $today->copy()->endOfWeek(Carbon::SUNDAY);
                $label = 'Semanal';
                break;
            case 'biweekly':
                if ($today->day <= 15) {
                    $startDate = $today->copy()->startOfMonth();
                    $endDate = $today->copy()->startOfMonth()->addDays(14);
                } else {
                    $startDate = $today->copy()->startOfMonth()->addDays(15);
                    $endDate = $today->copy()->endOfMonth();
                }
                $label = 'Quincenal';
                break;
            case 'monthly':
                $startDate = $today->copy()->startOfMonth();
                $endDate = $today->copy()->endOfMonth();
                $label = 'Mensual';
                break;
            case 'range':
                $startDate = $request->filled('start_date')
                    ? Carbon::parse($request->get('start_date'))
                    : $today->copy();
                $endDate = $request->filled('end_date')
                    ? Carbon::parse($request->get('end_date'))
                    : $startDate->copy();

                if ($endDate->lt($startDate)) {
                    [$startDate, $endDate] = [$endDate, $startDate];
                }

                $label = 'Rango Personalizado';
                break;
            case 'daily':
            default:
                $day = $request->get('date', $today->toDateString());
                $startDate = Carbon::parse($day);
                $endDate = Carbon::parse($day);
                $label = 'Diario';
                $period = 'daily';
                break;
        }

        return compact('period', 'startDate', 'endDate', 'label');
    }
}
