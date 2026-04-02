<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\OrderTomography;
use App\Models\Patient;
use App\Models\Radiography;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderTomographyController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->indexJson($request);
        }

        return view('radiology.order_tomographies.index');
    }

    public function create()
    {
        $selectedPatientId = old('patient_id');

        return view('radiology.order_tomographies.create', [
            'radiographies' => Radiography::with('agreementPrices')->orderBy('description')->get(['id', 'description', 'private_price']),
            'agreements' => Agreement::where('status', 'ACTIVE')->orderBy('description')->get(['id', 'description']),
            'selectedPatient' => $selectedPatientId
                ? Patient::find($selectedPatientId, ['id', 'dni', 'first_name', 'last_name'])
                : null,
            'nextCode' => $this->generateCode(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $order = OrderTomography::create([
            'code' => $this->generateCode(),
            'patient_id' => $data['patient_id'],
            'radiography_id' => $data['radiography_id'],
            'agreement_id' => $data['agreement_id'] ?? null,
            'user_id' => $request->user()->id,
            'service_type' => $data['service_type'],
            'total' => $this->resolveTotal($data),
            'payment_type' => $data['payment_type'],
            'care_medium' => $data['care_medium'],
            'document_type' => $data['document_type'] ?? null,
            'document_number' => $data['document_number'] ?? null,
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('order-tomografia.index')
            ->with('success', "Orden de tomografía {$order->code} registrada correctamente.");
    }

    public function edit(OrderTomography $order_tomografium)
    {
        $selectedPatientId = old('patient_id', $order_tomografium->patient_id);

        return view('radiology.order_tomographies.edit', [
            'orderTomography' => $order_tomografium,
            'radiographies' => Radiography::with('agreementPrices')->orderBy('description')->get(['id', 'description', 'private_price']),
            'agreements' => Agreement::where('status', 'ACTIVE')->orderBy('description')->get(['id', 'description']),
            'selectedPatient' => $selectedPatientId
                ? Patient::find($selectedPatientId, ['id', 'dni', 'first_name', 'last_name'])
                : null,
        ]);
    }

    public function update(Request $request, OrderTomography $order_tomografium)
    {
        $data = $this->validateData($request);

        $order_tomografium->update([
            'patient_id' => $data['patient_id'],
            'radiography_id' => $data['radiography_id'],
            'agreement_id' => $data['agreement_id'] ?? null,
            'service_type' => $data['service_type'],
            'total' => $this->resolveTotal($data),
            'payment_type' => $data['payment_type'],
            'care_medium' => $data['care_medium'],
            'document_type' => $data['document_type'] ?? null,
            'document_number' => $data['document_number'] ?? null,
        ]);

        return redirect()->route('order-tomografia.index')->with('success', 'Orden de tomografía actualizada correctamente.');
    }

    public function destroy(OrderTomography $order_tomografium)
    {
        $order_tomografium->delete();

        return redirect()->route('order-tomografia.index')->with('success', 'Orden de tomografía eliminada correctamente.');
    }

    private function indexJson(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('search', ''));
        $perPage = (int) $request->input('per_page', 10);

        $orders = OrderTomography::query()
            ->with(['patient:id,dni,first_name,last_name', 'radiography:id,description', 'agreement:id,description'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where('code', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($patientQuery) use ($search) {
                        $patientQuery->where('dni', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate($perPage);

        return response()->json($orders);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'radiography_id' => ['required', 'integer', 'exists:radiographies,id'],
            'agreement_id' => ['nullable', 'integer', 'exists:agreements,id'],
            'service_type' => ['required', 'in:EMERGENCY,PRIVATE,AGREEMENT'],
            'payment_type' => ['required', 'in:PENDING_PAYMENT,TRANSFER,YAPE,CASH'],
            'care_medium' => ['required', 'in:AMBULANCE,OUTPATIENT'],
            'document_type' => ['nullable', 'in:RECEIPT,INVOICE'],
            'document_number' => ['nullable', 'string', 'max:50'],
            'total' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    private function resolveTotal(array $data): float
    {
        if (isset($data['total']) && $data['total'] !== null && $data['total'] !== '') {
            return (float) $data['total'];
        }

        $radiography = Radiography::with('agreementPrices')->findOrFail($data['radiography_id']);

        if (!empty($data['agreement_id'])) {
            $agreementPrice = $radiography->agreementPrices->firstWhere('agreement_id', (int) $data['agreement_id']);
            if ($agreementPrice && $agreementPrice->price !== null) {
                return (float) $agreementPrice->price;
            }
        }

        return (float) ($radiography->private_price ?? 0);
    }

    private function generateCode(): string
    {
        $latestId = (int) (OrderTomography::max('id') ?? 0) + 1;

        return 'OT-' . str_pad((string) $latestId, 6, '0', STR_PAD_LEFT);
    }
}
