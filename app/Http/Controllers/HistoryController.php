<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\HistoryDiagnostic;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class HistoryController extends Controller
{
    /**
     * Listado de atenciones para el médico
     */
    public function index(Request $request)
    {
        $query = History::with(['patient', 'user', 'order', 'diagnostics']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', function($q) use ($search) {
                $q->where('dni', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        // Por defecto, ver las de hoy
        $date = $request->get('date', now()->toDateString());
        $query->whereDate('created_at', $date);

        $histories = $query->latest()->paginate(15);
        return view('atenciones.histories.index', compact('histories'));
    }

    /**
     * El médico entra aquí para rellenar la historia
     */
    public function edit(History $history)
    {
        // Cargamos las relaciones necesarias para que el formulario tenga datos
        $history->load(['patient', 'order', 'diagnostics', 'prescription.items.product']);
        
        return view('atenciones.histories.edit', compact('history'));
    }

    /**
     * Procesa el llenado de la historia, diagnósticos, receta y laboratorio
     */
    public function update(Request $request, History $history)
    {
        // 1. Comando de depuración (Opcional): 
        // Si quieres ver qué datos están llegando EXACTAMENTE antes de procesar, 
        // descomenta la siguiente línea (luego bórrala):
        // dd($request->all()); 

        $request->validate([
            'anamnesis' => 'required|string',
            'pa' => 'nullable|string',
            'fc' => 'nullable|string',
            'temp' => 'nullable|string',
            'fr' => 'nullable|string',
            'so2' => 'nullable|string',
            'peso' => 'nullable|string', // Cambiado a string para coincidir con tu migración
            'talla' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // 2. Actualización de la Historia
            $history->update([
                'user_id' => auth()->id(),
                'habito_tabaco' => $request->has('habito_tabaco'),
                'habito_alcohol' => $request->has('habito_alcohol'),
                'habito_coca' => $request->has('habito_coca'),
                'alergias' => $request->alergias,
                'antecedentes_familiares' => $request->antecedentes_familiares,
                'antecedentes_otros' => $request->antecedentes_otros,
                'anamnesis' => $request->anamnesis,
                'pa' => $request->pa,
                'fc' => $request->fc,
                'temp' => $request->temp,
                'fr' => $request->fr,
                'so2' => $request->so2,
                'peso' => $request->peso,
                'talla' => $request->talla,
                'examen_fisico_detalle' => $request->examen_fisico_detalle,
                'imc' => $request->imc, // Asegúrate de tener esta columna en tu tabla histories
            ]);

            // 3. Sincronizar Diagnósticos
            $history->diagnostics()->delete();
            if ($request->has('diagnostics')) {
                foreach ($request->diagnostics as $dx) {
                    \App\Models\HistoryDiagnostic::create([
                        'history_id' => $history->id,
                        'cie10_id' => $dx['cie10_id'],
                        'diagnostico' => $dx['descripcion'],
                        'tratamiento' => $dx['tratamiento'] ?? '',
                    ]);
                }
            }

            // 4. Sincronizar Receta
            if ($request->filled('prescription')) {
                $prescription = Prescription::updateOrCreate(
                    ['history_id' => $history->id],
                    ['patient_id' => $history->patient_id, 'user_id' => auth()->id(), 'fecha_sig_cita'=> $request->fecha_sig_cita]
                );

                $prescription->items()->delete();

                foreach ($request->prescription as $item) {
                    // Verificamos que product_id tenga valor antes de intentar guardar
                    if (!empty($item['product_id'])) {
                        \App\Models\PrescriptionItem::create([
                            'prescription_id' => $prescription->id,
                            'product_id'      => $item['product_id'],
                            'cantidad'        => $item['qty'] ?? 1,
                            'indicaciones'    => $item['notes'] ?? '',
                        ]);
                    }
                }
            }

            // 5. Sincronizar Laboratorios (LabItem)
            $history->labItems()->delete(); // Esto borra lo anterior de la tabla lab_items
            if ($request->has('lab_exams')) {
                foreach ($request->lab_exams as $examName) {
                    // Solo guardamos si no está vacío
                    if (!empty($examName)) {
                        \App\Models\LabItem::create([
                            'history_id' => $history->id,
                            'name' => $examName,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('histories.index')->with('success', 'Historia Clínica guardada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            // Esto devolverá el error exacto de SQL o PHP si algo falla
            return back()->with('error', 'Error en base de datos: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Métodos para impresión (Llaman a sus propias vistas de PDF)
     */
    // Imprimir Historia Completa
    public function printHistory(History $history) 
    {
        $branch = \App\Models\Branch::where('estado', true)->first();
        $history->load(['patient', 'user', 'diagnostics.cie10']);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('atenciones.histories.pdf_full', compact('history', 'branch'))
                    ->setPaper('a4')->stream("Historia_{$history->id}.pdf");
    }

    // Imprimir Receta
    public function printPrescription(History $history) 
    {
        // Cargamos los ítems y sus productos relacionados
        $history->load(['patient', 'user', 'prescriptionItems.product']); 
        $branch = \App\Models\Branch::where('estado', true)->first();

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('atenciones.histories.pdf_prescription', compact('history', 'branch'))
                    ->setPaper('a4')
                    ->stream("Receta_{$history->id}.pdf");
    }

    // Imprimir Laboratorio
    public function printLab(History $history) 
    {
        $history->load(['patient', 'user', 'labItems.itemable.area']);
        
        // Obtenemos la sucursal activa
        $branch = \App\Models\Branch::where('estado', true)->first();

        $groupedLabs = $history->labItems->groupBy(function($item) {
            return $item->itemable->area->name ?? 'GENERAL';
        });

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('atenciones.histories.pdf_lab', compact('history', 'groupedLabs', 'branch'))
                    ->setPaper('a4')
                    ->stream();
    }
    
}