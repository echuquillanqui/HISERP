<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Expense;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class CashBoxController extends Controller
{
    public function index(Request $request)
    {
        // Si no se elige fecha, usamos la de hoy
        $date = $request->get('date', Carbon::today()->toDateString());

        // 1. Ingresos: Sumamos el total de la tabla 'orders' generadas hoy
        $totalIngresos = Order::whereDate('created_at', $date)->sum('total');

        // 2. Egresos: Sumamos los gastos registrados hoy
        $totalEgresos = Expense::whereDate('created_at', $date)->sum('amount');

        // 3. Detalle para las tablas
        $ordenes = Order::with('patient')->whereDate('created_at', $date)->get();
        $egresos = Expense::whereDate('created_at', $date)->get();

        $saldoCaja = $totalIngresos - $totalEgresos;

        return view('atenciones.cashbox.index', compact(
            'totalIngresos', 'totalEgresos', 'saldoCaja', 'ordenes', 'egresos', 'date'
        ));
    }

    public function storeExpense(Request $request)
    {
        $request->validate([
            'description' => 'required',
            'voucher_type' => 'required',
            'amount' => 'required|numeric',
            'file_path' => 'nullable|file|mimes:jpg,png,pdf|max:2048'
        ]);

        $data = $request->all();
        $data['user_id'] = auth()->id();

        if ($request->hasFile('file_path')) {
            // Guarda el archivo en storage/app/public/expenses
            $data['file_path'] = $request->file('file_path')->store('expenses', 'public');
        }

        Expense::create($data);

        return back()->with('success', 'Egreso registrado');
    }

    public function updateExpense(Request $request, Expense $expense)
{
    $request->validate([
        'description' => 'required',
        'voucher_type' => 'required',
        'amount' => 'required|numeric',
        'document' => 'nullable|file|mimes:jpg,png,pdf|max:2048'
    ]);

    $data = $request->except('document');

    if ($request->hasFile('document')) {
        // Eliminar el archivo antiguo si existe
        if ($expense->file_path) {
            Storage::disk('public')->delete($expense->file_path);
        }
        // Guardar el nuevo
        $data['file_path'] = $request->file('document')->store('expenses', 'public');
    }

    $expense->update($data);

    return back()->with('success', 'Egreso actualizado correctamente');
}
}
