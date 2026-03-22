<?php

namespace App\Http\Controllers;

use App\Models\{Referral, Patient, User, Cie10};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReferralController extends Controller
{
    public function index(Request $request)
    {
        $query = Referral::with(['patient', 'referralResponsible']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('referral_code', 'LIKE', "%{$search}%")
                ->orWhere('origin_facility', 'LIKE', "%{$search}%")
                ->orWhere('destination_facility', 'LIKE', "%{$search}%")
                ->orWhereHas('patient', function($p) use ($search) {
                    $p->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%")
                        ->orWhere('dni', 'LIKE', "%{$search}%");
                });
            });
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->from_date)->startOfDay(),
                Carbon::parse($request->to_date)->endOfDay()
            ]);
        }

        $referrals = $query->latest()->get();

        if ($request->ajax()) {
            return view('referrals.partials.table_rows', compact('referrals'))->render();
        }

        return view('referrals.index', compact('referrals'));
    }

    public function create(Request $request)
    {
        $type = strtoupper($request->query('type', 'SIS'));
        $patients = Patient::all();
        $staff = User::all();

        $view = ($type === 'SIS') ? 'referrals.create_sis' : 'referrals.create_essalud';

        return view($view, compact('patients', 'staff', 'type'));
    }

    public function searchCie10(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        $query = Cie10::query();

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('codigo', 'like', "%{$term}%")
                    ->orWhere('descripcion', 'like', "%{$term}%");
            });
        }

        $rows = $query->orderBy('codigo')->limit(20)->get(['id', 'codigo', 'descripcion']);

        return response()->json($rows->map(function ($item) {
            return [
                'id' => $item->id,
                'codigo' => $item->codigo,
                'descripcion' => $item->descripcion,
                'text' => trim($item->codigo . ' - ' . $item->descripcion),
            ];
        })->values());
    }

    public function store(Request $request)
    {
        $validated = $this->validateReferral($request);

        try {
            return DB::transaction(function () use ($validated) {
                $referral = Referral::create(collect($validated)->except('diagnoses')->toArray());

                foreach ($validated['diagnoses'] ?? [] as $row) {
                    if (blank($row['icd_10_code'] ?? null) && blank($row['diagnosis'] ?? null)) {
                        continue;
                    }

                    $referral->diagnosisTreatments()->create([
                        'icd_10_code' => $row['icd_10_code'] ?? null,
                        'diagnosis' => $row['diagnosis'] ?? null,
                        'D' => isset($row['D']) ? 'X' : null,
                        'P' => isset($row['P']) ? 'X' : null,
                        'R' => isset($row['R']) ? 'X' : null,
                    ]);
                }

                return redirect()->route('referrals.index')
                    ->with('success', "Referencia {$referral->referral_code} creada exitosamente.");
            });
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function show()
    {
    }

    public function downloadPdf($id)
    {
        $referral = Referral::with(['patient', 'diagnosisTreatments', 'referralResponsible'])
            ->findOrFail($id);

        if ($referral->patient && $referral->patient->birth_date) {
            $referral->patient->calculated_age = Carbon::parse($referral->patient->birth_date)
                ->diffInYears($referral->created_at);
        } else {
            $referral->patient->calculated_age = 'N/A';
        }

        $pdf = Pdf::loadView('referrals.pdf', compact('referral'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Referencia_' . $referral->referral_code . '.pdf');
    }

    public function downloadPdfEssalud($id)
    {
        $referral = Referral::with([
            'patient',
            'diagnosisTreatments',
            'referralResponsible',
            'facilityResponsible',
            'escortStaff'
        ])->findOrFail($id);

        if ($referral->patient && $referral->patient->birth_date) {
            $referral->patient->calculated_age = Carbon::parse($referral->patient->birth_date)
                ->diffInYears($referral->created_at);
        } else {
            $referral->patient->calculated_age = 'N/A';
        }

        $pdf = Pdf::loadView('referrals.pdf_essalud', compact('referral'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream("Referencia_EsSalud_{$referral->referral_code}.pdf");
    }

    public function edit(Request $request, Referral $referral)
    {
        $referral->load(['patient', 'diagnosisTreatments']);
        $staff = User::all();

        $type = strtoupper($request->query('type', 'SIS'));
        $view = ($type === 'ESSALUD') ? 'referrals.edit_essalud' : 'referrals.edit_sis';

        return view($view, compact('referral', 'staff', 'type'));
    }

    public function update(Request $request, $id)
    {
        $referral = Referral::findOrFail($id);
        $validated = $this->validateReferral($request);

        $referral->update(collect($validated)->except('diagnoses')->toArray());
        $referral->diagnosisTreatments()->delete();

        foreach ($validated['diagnoses'] ?? [] as $row) {
            if (blank($row['icd_10_code'] ?? null) && blank($row['diagnosis'] ?? null)) {
                continue;
            }

            $referral->diagnosisTreatments()->create([
                'icd_10_code' => $row['icd_10_code'] ?? null,
                'diagnosis' => $row['diagnosis'] ?? null,
                'D' => isset($row['D']) ? 'X' : null,
                'P' => isset($row['P']) ? 'X' : null,
                'R' => isset($row['R']) ? 'X' : null,
            ]);
        }

        return redirect()->route('referrals.edit', $referral->id)->with('success', 'Referencia actualizada.');
    }

    public function destroy(Referral $referral)
    {
        $referral->delete();
        return redirect()->back()->with('success', 'Referencia eliminada.');
    }

    protected function validateReferral(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'referral_type' => 'required|in:EMERGENCIA,CONSULTA EXTERNA,APOYO AL DX',
            'origin_facility' => 'required|string|max:255',
            'destination_facility' => 'required|string|max:255',
            'destination_specialty' => 'required|string|max:255',
            'anamnesis' => 'required|string',
            'general_state' => 'nullable|string|max:255',
            'temperature' => 'required|string|max:255',
            'blood_pressure' => 'required|string|max:255',
            'respiratory_rate' => 'required|string|max:255',
            'heart_rate' => 'required|string|max:255',
            'oxygen_saturation' => 'required|string|max:255',
            'skin_subcutaneous' => 'nullable|string|max:255',
            'lungs' => 'required|string|max:255',
            'cardiovascular' => 'required|string|max:255',
            'neurological' => 'nullable|string|max:255',
            'auxiliary_exams' => 'nullable|string',
            'others' => 'nullable|string',
            'appointment_date' => 'nullable|date',
            'appointment_time' => 'nullable|date_format:H:i,H:i:s',
            'attending_physician_name' => 'nullable|string|max:255',
            'coordination_name' => 'nullable|string|max:255',
            'patient_condition' => 'required|in:ESTABLE,MAL ESTADO',
            'arrival_condition' => 'required|in:ESTABLE,MAL ESTADO,FALLECIDO',
            'diagnoses' => 'nullable|array',
            'diagnoses.*.icd_10_code' => 'nullable|string|max:10',
            'diagnoses.*.diagnosis' => 'nullable|string',
            'diagnoses.*.D' => 'nullable',
            'diagnoses.*.P' => 'nullable',
            'diagnoses.*.R' => 'nullable',
            'treatments' => 'nullable|array',
            'treatments.*' => 'nullable|string',
            'referral_responsible_id' => 'required|exists:users,id',
            'facility_responsible_id' => 'required|exists:users,id',
            'escort_staff_id' => 'nullable|exists:users,id',
            'receiving_staff_id' => 'nullable|exists:users,id',
        ], [
            'required' => 'El campo :attribute es obligatorio.',
        ], [
            'patient_id' => 'paciente',
            'referral_type' => 'tipo de referencia',
            'origin_facility' => 'establecimiento de origen',
            'destination_facility' => 'establecimiento destino',
            'destination_specialty' => 'especialidad destino',
            'patient_condition' => 'condición de inicio',
            'arrival_condition' => 'condición de llegada',
            'referral_responsible_id' => 'responsable de referencia',
            'facility_responsible_id' => 'responsable del establecimiento',
            'escort_staff_id' => 'personal acompañante',
            'receiving_staff_id' => 'responsable de recepción',
            'anamnesis' => 'anamnesis',
            'general_state' => 'estado general',
            'temperature' => 'temperatura',
            'blood_pressure' => 'presión arterial',
            'respiratory_rate' => 'frecuencia respiratoria',
            'heart_rate' => 'frecuencia cardíaca',
            'oxygen_saturation' => 'saturación de oxígeno',
            'skin_subcutaneous' => 'piel y tejido subcutáneo',
            'lungs' => 'pulmones',
            'cardiovascular' => 'cardiovascular',
            'neurological' => 'neurológico',
            'auxiliary_exams' => 'exámenes auxiliares',
            'others' => 'otros',
            'appointment_date' => 'fecha de cita',
            'appointment_time' => 'hora de cita',
            'attending_physician_name' => 'nombre de quien atenderá',
            'coordination_name' => 'nombre con quien se coordinó',
        ]);

        $validated['treatments'] = collect($validated['treatments'] ?? [])
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();

        return $validated;
    }
}
