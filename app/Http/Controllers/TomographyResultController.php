<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\IopamidolBrand;
use App\Models\OrderTomography;
use App\Models\TomographyResult;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TomographyResultController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        $status = $request->input('status', '');
        $search = trim((string) $request->input('search', ''));

        $query = OrderTomography::query()
            ->with(['patient:id,dni,first_name,last_name', 'items.radiography:id,description,plate_usage', 'result'])
            ->when($date, fn ($q) => $q->whereDate('created_at', $date))
            ->when($search !== '', function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($patientQuery) use ($search) {
                        $patientQuery->where('dni', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });

        if ($status !== '') {
            if ($status === 'pendiente') {
                $query->whereDoesntHave('result');
            }

            if ($status === 'completado') {
                $query->whereHas('result');
            }
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('radiology.resultados.index', compact('orders', 'date', 'status', 'search'));
    }

    public function edit(OrderTomography $resultado)
    {
        $resultado->load(['patient', 'items.radiography', 'result', 'user']);

        $suggestedPlates = (int) $resultado->items->sum(function ($item) {
            return ((int) $item->quantity) * ((int) ($item->radiography->plate_usage ?? 0));
        });

        $result = $resultado->result;

        $professionals = User::where('role', 'medicina')
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $technologists = User::whereIn('role', ['radiologia', 'laboratorio'])
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $selectedDoctorId = (string) ($result->requesting_doctor_id ?? $resultado->user_id ?? '');
        $selectedSignerId = (string) ($result->report_signer_id ?? auth()->id() ?? '');

        $iopamidolBrands = IopamidolBrand::query()->orderBy('name')->get(['id', 'name']);

        return view('radiology.resultados.edit', compact(
            'resultado',
            'result',
            'suggestedPlates',
            'professionals',
            'technologists',
            'selectedDoctorId',
            'selectedSignerId',
            'iopamidolBrands'
        ));
    }

    public function update(Request $request, OrderTomography $resultado)
    {
        $data = $request->validate([
            'requesting_doctor_id' => ['nullable', 'integer', 'exists:users,id'],
            'report_signer_id' => ['nullable', 'integer', 'exists:users,id'],
            'result_date' => ['required', 'date'],
            'plates_used' => ['required', 'integer', 'min:0'],
            'iopamidol_brand_id' => ['nullable', 'integer', 'exists:iopamidol_brands,id'],
            'iopamidol_presentation_ml' => ['nullable', 'integer', 'in:50,100'],
            'iopamidol_units' => ['nullable', 'integer', 'min:0'],
            'general_description' => ['nullable', 'string'],
            'result_description' => ['nullable', 'string'],
            'conclusion' => ['nullable', 'string'],
        ]);

        $iopamidolPresentation = isset($data['iopamidol_presentation_ml']) ? (int) $data['iopamidol_presentation_ml'] : null;
        $iopamidolUnits = (int) ($data['iopamidol_units'] ?? 0);

        if ($iopamidolUnits > 0 && (!$iopamidolPresentation || empty($data['iopamidol_brand_id']))) {
            return back()
                ->withErrors(['iopamidol_brand_id' => 'Seleccione marca y presentación para descontar iopamidol.'])
                ->withInput();
        }

        if ($iopamidolUnits === 0) {
            $iopamidolPresentation = null;
        }

        $payload = [
            'order_tomography_id' => $resultado->id,
            'patient_id' => $resultado->patient_id,
            'requesting_doctor_id' => $data['requesting_doctor_id'] ?? null,
            'report_signer_id' => $data['report_signer_id'] ?? null,
            'result_date' => $data['result_date'],
            'plates_used' => (int) $data['plates_used'],
            'iopamidol_brand_id' => $iopamidolUnits > 0 ? ($data['iopamidol_brand_id'] ?? null) : null,
            'iopamidol_presentation_ml' => $iopamidolPresentation,
            'iopamidol_units' => $iopamidolUnits,
            'iopamidol_used' => (float) (($iopamidolPresentation && $iopamidolUnits > 0) ? $iopamidolPresentation * $iopamidolUnits : 0),
            'general_description' => $data['general_description'] ?? null,
            'result_description' => $data['result_description'] ?? null,
            'conclusion' => $data['conclusion'] ?? null,
            'result_text' => collect([
                $data['general_description'] ?? null,
                $data['result_description'] ?? null,
                $data['conclusion'] ?? null,
            ])->filter()->implode("\n\n"),
        ];

        TomographyResult::updateOrCreate(
            ['order_tomography_id' => $resultado->id],
            $payload
        );

        return redirect()->route('tomography-results.index')->with('success', 'Resultado de tomografía guardado correctamente.');
    }

    public function show(OrderTomography $resultado)
    {
        $resultado->load(['patient', 'items.radiography', 'result', 'user']);

        if (!$resultado->result) {
            return redirect()->route('tomography-results.edit', $resultado)->with('error', 'Primero debe registrar el resultado.');
        }

        $requestingDoctor = User::find($resultado->result->requesting_doctor_id);
        $reportSigner = User::find($resultado->result->report_signer_id);

        $pdf = Pdf::loadView('radiology.resultados.pdf', [
            'order' => $resultado,
            'result' => $resultado->result,
            'requestingDoctor' => $requestingDoctor,
            'reportSigner' => $reportSigner,
            'branch' => Branch::where('estado', true)->first(),
        ]);

        return $pdf->stream("Resultado_Tomografia_{$resultado->code}.pdf");
    }
}
