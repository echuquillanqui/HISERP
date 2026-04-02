<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Radiography;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RadiographyController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->indexJson($request);
        }

        return view('radiology.radiographies.index');
    }

    public function create()
    {
        return view('radiology.radiographies.create', [
            'agreements' => Agreement::where('status', 'ACTIVE')->orderBy('description')->get(['id', 'description']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $radiography = Radiography::create([
            'description' => $data['description'],
            'private_price' => $data['private_price'] ?? null,
            'plate_usage' => $data['plate_usage'] ?? 0,
        ]);

        $this->syncAgreementPrices($radiography, $data['agreement_prices'] ?? []);

        return redirect()->route('radiographies.index')->with('success', 'Radiografía registrada correctamente.');
    }

    public function edit(Radiography $radiography)
    {
        return view('radiology.radiographies.edit', [
            'radiography' => $radiography->load('agreementPrices'),
            'agreements' => Agreement::where('status', 'ACTIVE')->orderBy('description')->get(['id', 'description']),
        ]);
    }

    public function update(Request $request, Radiography $radiography)
    {
        $data = $this->validateData($request, $radiography->id);

        $radiography->update([
            'description' => $data['description'],
            'private_price' => $data['private_price'] ?? null,
            'plate_usage' => $data['plate_usage'] ?? 0,
        ]);

        $this->syncAgreementPrices($radiography, $data['agreement_prices'] ?? []);

        return redirect()->route('radiographies.index')->with('success', 'Radiografía actualizada correctamente.');
    }

    public function destroy(Radiography $radiography)
    {
        if ($radiography->orderTomographies()->exists()) {
            return redirect()->route('radiographies.index')->with(
                'error',
                'No se puede eliminar la radiografía porque está asociada a una o más órdenes de tomografía.'
            );
        }

        $radiography->delete();

        return redirect()->route('radiographies.index')->with('success', 'Radiografía eliminada correctamente.');
    }

    private function indexJson(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('search', ''));
        $perPage = (int) $request->input('per_page', 10);

        $radiographies = Radiography::query()
            ->withCount('agreementPrices')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('description', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage);

        return response()->json($radiographies);
    }

    private function validateData(Request $request, ?int $radiographyId = null): array
    {
        return $request->validate([
            'description' => ['required', 'string', 'max:255', 'unique:radiographies,description,' . $radiographyId],
            'private_price' => ['nullable', 'numeric', 'min:0'],
            'plate_usage' => ['nullable', 'integer', 'min:0'],
            'agreement_prices' => ['nullable', 'array'],
            'agreement_prices.*.agreement_id' => ['required_with:agreement_prices', 'integer', 'exists:agreements,id', 'distinct'],
            'agreement_prices.*.price' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    private function syncAgreementPrices(Radiography $radiography, array $agreementPrices): void
    {
        $syncData = collect($agreementPrices)
            ->filter(function (array $item) {
                return isset($item['agreement_id'])
                    && array_key_exists('price', $item)
                    && $item['price'] !== null
                    && $item['price'] !== '';
            })
            ->mapWithKeys(function (array $item) {
                return [
                    (int) $item['agreement_id'] => ['price' => $item['price']],
                ];
            })
            ->all();

        $radiography->agreements()->sync($syncData);
    }
}
