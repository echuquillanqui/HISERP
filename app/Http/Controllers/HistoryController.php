<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\HistoryDiagnostic;
use App\Models\Profile;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

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
    /**
 * Muestra el formulario de edición de la historia clínica.
 */
    public function edit(History $history)
    {
        // 1. Cargamos las relaciones, incluyendo 'labItems' para poder verificar 
        // en la vista qué exámenes fueron guardados previamente por nombre.
        $history->load([
            'patient', 
            'diagnostics.cie10', 
            'prescription.items.product', 
            'labItems'
        ]);

        $lastGeneratedHistory = History::where('patient_id', $history->patient_id)
            ->where('id', '!=', $history->id)
            ->where('created_at', '<', now())
            ->latest('created_at')
            ->first();
        
        // 2. Historial clínico/laboratorio del paciente (ordenado por fecha desc + paginación)
        $patientHistoryTimeline = History::where('patient_id', $history->patient_id)
            ->with([
                'user:id,name',
                'labItems:id,history_id,name',
                'order.details.labResults.catalog:id,name,unit,reference_range',
                'order.details.service:id,name',
                'order.details.reportService:id,order_detail_id',
                'prescription:id,history_id',
                'prescription.items:id,prescription_id',
            ])
            ->latest()
            ->paginate(10, ['*'], 'history_page');

        $patientHistoryTimeline->getCollection()->transform(function ($timelineHistory) {
            $timelineHistory->normalized_lab_names = $this->normalizeLabNames(
                $timelineHistory->labItems->pluck('name')
            );

            return $timelineHistory;
        });

        // 3. Resultados de laboratorio previos para referencia visual
        $orderLabResults = optional($history->order)
            ?->details
            ->flatMap(fn($detail) => $detail->labResults)
            ->sortBy(fn($result) => $result->catalog->name ?? '')
            ->values();

        // 4. Lógica de Laboratorio: Obtenemos las áreas jerárquicas
        // Excluimos áreas que no son de laboratorio clínico
        $areasExcluidas = ['MEDICINA', 'ADICIONALES', 'MEDICAMENTOS', 'HEMODIALISIS'];

        $areasConContenido = \App\Models\Area::whereNotIn('name', $areasExcluidas)
            ->with([
                // Traemos catálogos sueltos (que no pertenecen a un perfil)
                'catalogs' => function($q) {
                    $q->whereDoesntHave('profiles');
                },
                // Traemos perfiles con sus exámenes hijos
                'profiles.catalogs'
            ])
            ->get();

        // 
        
        // 5. Retornamos la vista con las variables necesarias
        return view('atenciones.histories.edit', compact(
            'history', 
            'lastGeneratedHistory',
            'patientHistoryTimeline', 
            'orderLabResults', 
            'areasConContenido'
        ));
    }

    /**
     * Procesa el llenado de la historia, diagnósticos, receta y laboratorio
     */
    public function update(Request $request, History $history)
{
    DB::beginTransaction();
    try {
        // 1. Actualizar datos principales de la historia
        $history->update($request->only([
            'anamnesis', 'pa', 'fc', 'temp', 'fr', 'so2', 'peso', 'talla', 
            'habito_tabaco', 'habito_alcohol', 'habito_coca', 'alergias', 
            'antecedentes_familiares', 'antecedentes_otros', 'examen_fisico_detalle', 'imc',
            'tiempo_enfermedad', 'signos_sintomas'
        ]));

        // 2. Sincronizar Diagnósticos (Independiente)
        // Borramos los anteriores y creamos los nuevos que vienen del formulario
        $history->diagnostics()->delete();
        if ($request->has('diagnostics')) {
            foreach ($request->diagnostics as $dx) {
                // Asegúrate que los campos coincidan con tu modelo HistoryDiagnostic
                $history->diagnostics()->create([
                    'cie10_id'    => $dx['cie10_id'],
                    'diagnostico' => $dx['descripcion'],
                    'tratamiento' => $dx['tratamiento'] ?? '',
                    'clasificacion' => in_array(($dx['clasificacion'] ?? null), ['P', 'D', 'R'], true)
                        ? $dx['clasificacion']
                        : null,
                ]);
            }
        }

        // 3. Sincronizar Prescripción (Independiente)
        // Primero obtenemos o creamos la prescripción asociada a esta historia
        $prescription = \App\Models\Prescription::firstOrCreate(
            ['history_id' => $history->id],
            ['patient_id' => $history->patient_id, 'user_id' => auth()->id()]
        );
        $prescription->update([
            'fecha_sig_cita' => $request->filled('fecha_sig_cita') ? $request->fecha_sig_cita : null,
        ]);

        // Borramos items anteriores y creamos los nuevos
        $prescription->items()->delete();
        if ($request->has('prescription')) {
            foreach ($request->prescription as $rx) {
                $prescription->items()->create([
                    'product_id'    => $rx['product_id'],
                    'cantidad'           => $rx['qty'],
                    'indicaciones'         => $rx['notes']
                ]);
            }
        }

        // 4. Sincronizar LabItems (Ya lo teníamos independiente)
        $history->labItems()->delete();
        if ($request->has('lab_names')) {
            $normalizedLabNames = $this->normalizeLabNames(collect($request->lab_names));
            foreach ($normalizedLabNames as $name) {
                $history->labItems()->create(['name' => $name]);
            }
        }

        DB::commit();
        return redirect()->route('histories.index')->with('success', 'Historia clínica actualizada correctamente.');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Error al actualizar historia: " . $e->getMessage());
        return back()->with('error', 'Error al guardar: ' . $e->getMessage())->withInput();
    }
}

    /**
     * Métodos para impresión (Llaman a sus propias vistas de PDF)
     */
    // Imprimir Historia Completa
    public function printHistory(History $history) 
{
    $branch = \App\Models\Branch::where('estado', true)->first();
    
    // Carga necesaria y limpia
    $history->load([
        'patient', 
        'user', 
        'diagnostics.cie10'
    ]);
    
    // Obtenemos los exámenes de laboratorio del historial
    $orderLabExams = $history->labItems; 

    return \Barryvdh\DomPDF\Facade\Pdf::loadView('atenciones.histories.pdf_full', compact('history', 'branch', 'orderLabExams'))
            ->setPaper('a4')
            ->stream("Historia_{$history->id}.pdf");
}

    // Imprimir Receta
    public function printPrescription(History $history) 
{
    // Cargamos la prescripción y sus productos asociados
    $history->load([
        'patient', 
        'user', 
        'prescription.items.product' 
    ]);
    
    $branch = \App\Models\Branch::where('estado', true)->first();

    return \Barryvdh\DomPDF\Facade\Pdf::loadView('atenciones.histories.pdf_prescription', compact('history', 'branch'))
            ->setPaper('a4')
            ->stream("Receta_{$history->id}.pdf");
}

    // Imprimir Laboratorio
public function printLab(History $history) 
{
    // 1. Cargamos solo lo necesario
    $history->load(['patient', 'user', 'labItems']);
    
    // 2. Obtenemos la sucursal activa
    $branch = \App\Models\Branch::where('estado', true)->first();

    // 3. Agrupamos directamente por el nombre del labItem
    // Nota: Como ya no tienes la relación 'area', si necesitas agrupar por área, 
    // lo ideal sería que 'LabItem' tuviera un campo 'area_name' o simplemente 
    // listar todo bajo una categoría general.
    $normalizedLabItems = $this->normalizeLabNames($history->labItems->pluck('name'))
        ->map(fn($name) => (object) ['name' => $name]);

    $groupedLabs = $normalizedLabItems->groupBy(function($item) {
        return 'EXÁMENES SOLICITADOS'; // O puedes dejarlo como estaba si tenías un campo area_name
    });

    return \Barryvdh\DomPDF\Facade\Pdf::loadView('atenciones.histories.pdf_lab', compact('history', 'groupedLabs', 'branch'))
                    ->setPaper('a4')
                    ->stream();
}

    private function getOrderLabExams(History $history): Collection
    {
        if (!$history->order) {
            return collect();
        }

        return $history->order->details
            ->filter(function ($detail) {
                $areaName = strtoupper($detail->itemable->area->name ?? '');
                return !in_array($areaName, ['MEDICINA', 'ADICIONALES']);
            })
            ->map(function ($detail) {
                $name = $detail->itemable->name ?? $detail->name ?? '';
                $search = ['[PERFIL]', '[EXAMEN]', '[examen]'];
                return trim(str_ireplace($search, '', $name));
            })
            ->filter()
            ->unique()
            ->values();
    }

    private function normalizeLabNames(Collection $labNames): Collection
    {
        $selectedNames = $labNames
            ->map(fn($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->values();

        if ($selectedNames->isEmpty()) {
            return collect();
        }

        $selectedProfiles = Profile::with('catalogs:id,name')
            ->whereIn('name', $selectedNames)
            ->get();

        $catalogNamesBelongingToSelectedProfiles = $selectedProfiles
            ->flatMap(fn($profile) => $profile->catalogs->pluck('name'))
            ->unique();

        return $selectedNames
            ->reject(fn($name) => $catalogNamesBelongingToSelectedProfiles->contains($name))
            ->values();
    }
}
