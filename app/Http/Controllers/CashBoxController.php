<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Expense;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Branch;

class CashBoxController extends Controller
{
    public function index(Request $request)
    {
        // 1. Obtener la fecha del filtro o usar la de hoy
        $date = $request->get('date', Carbon::today()->toDateString());

        // 2. Cargar Órdenes con sus relaciones (Paciente y Detalles de Exámenes)
        // El 'details' es vital para que funcione el modal de la lupa
        $ordenes = Order::with(['patient', 'details'])
            ->whereDate('created_at', $date)
            ->get();

        // 3. Cargar Egresos
        $egresos = Expense::whereDate('created_at', $date)->get();

        // 4. Cálculos para las tarjetas de resumen
        $totalIngresos = $ordenes->sum('total');
        $totalEgresos = $egresos->sum('amount');
        $saldoCaja = $totalIngresos - $totalEgresos;

        return view('atenciones.cashbox.index', compact(
            'ordenes', 
            'egresos', 
            'totalIngresos', 
            'totalEgresos', 
            'saldoCaja', 
            'date'
        ));
    }

    // Método para guardar un egreso nuevo
    public function storeExpense(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'voucher_type' => 'required',
            'amount' => 'required|numeric|min:0',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
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
            'user_id' => auth()->id(), // Asumiendo que rastreas quién registró el gasto
        ]);

        return back()->with('success', 'Gasto registrado correctamente.');
    }

    // Método para editar un egreso existente
    public function updateExpense(Request $request, Expense $expense)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'voucher_type' => 'required',
            'amount' => 'required|numeric|min:0',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        $data = $request->only(['description', 'voucher_type', 'amount']);

        if ($request->hasFile('document')) {
            // Borrar el archivo anterior para no llenar el disco de basura
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
        $date = $request->get('date', now()->toDateString());
        
        // Obtenemos la sucursal activa (puedes ajustar el ID según tu lógica)
        $branch = Branch::first(); 

        $ordenes = Order::with(['patient', 'details'])->whereDate('created_at', $date)->get();
        $egresos = Expense::whereDate('created_at', $date)->get();
        
        $totalIngresos = $ordenes->sum('total');
        $totalEgresos = $egresos->sum('amount');
        $saldoCaja = $totalIngresos - $totalEgresos;

        $data = compact('ordenes', 'egresos', 'totalIngresos', 'totalEgresos', 'saldoCaja', 'date', 'branch');

        $pdf = Pdf::loadView('atenciones.cashbox.pdf', $data);
        
        return $pdf->download("cuadre_caja_{$date}.pdf");
    }
}
