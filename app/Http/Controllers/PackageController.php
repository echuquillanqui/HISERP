<?php

namespace App\Http\Controllers;

use App\Models\Catalog;
use App\Models\Package;
use App\Models\Product;
use App\Models\Profile;
use App\Models\Service;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::with('items')->latest()->paginate(12);
        return view('packages.index', compact('packages'));
    }

    public function create()
    {
        return view('packages.create', [
            'catalogs' => Catalog::orderBy('name')->get(['id', 'name', 'price']),
            'profiles' => Profile::orderBy('name')->get(['id', 'name', 'price']),
            'services' => Service::orderBy('nombre')->get(['id', 'nombre', 'precio']),
            'products' => Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'selling_price']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePackage($request);

        $package = Package::create($this->extractPackageData($data));
        $this->syncItems($package, $data['package_items'] ?? []);

        return redirect()->route('packages.index')->with('success', 'Paquete creado correctamente.');
    }

    public function edit(Package $package)
    {
        $package->load('items');

        return view('packages.edit', [
            'package' => $package,
            'catalogs' => Catalog::orderBy('name')->get(['id', 'name', 'price']),
            'profiles' => Profile::orderBy('name')->get(['id', 'name', 'price']),
            'services' => Service::orderBy('nombre')->get(['id', 'nombre', 'precio']),
            'products' => Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'selling_price']),
        ]);
    }

    public function update(Request $request, Package $package)
    {
        $data = $this->validatePackage($request, $package->id);

        $package->update($this->extractPackageData($data));
        $this->syncItems($package, $data['package_items'] ?? []);

        return redirect()->route('packages.index')->with('success', 'Paquete actualizado correctamente.');
    }

    public function destroy(Package $package)
    {
        $package->delete();

        return redirect()->route('packages.index')->with('success', 'Paquete eliminado correctamente.');
    }

    private function validatePackage(Request $request, ?int $packageId = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:packages,code,' . $packageId,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'package_items' => 'nullable|array',
            'package_items.*.itemable_type' => 'required_with:package_items.*.itemable_id|in:catalog,profile,service,product',
            'package_items.*.itemable_id' => 'required_with:package_items.*.itemable_type|integer',
            'package_items.*.quantity' => 'nullable|integer|min:1',
            'package_items.*.unit_price' => 'nullable|numeric|min:0',
        ]);
    }

    private function extractPackageData(array $data): array
    {
        return [
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'cost' => $data['cost'] ?? null,
            'is_active' => (bool)($data['is_active'] ?? false),
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
        ];
    }

    private function syncItems(Package $package, array $items): void
    {
        $typeMap = [
            'catalog' => Catalog::class,
            'profile' => Profile::class,
            'service' => Service::class,
            'product' => Product::class,
        ];

        $package->items()->delete();

        foreach ($items as $item) {
            if (empty($item['itemable_type']) || empty($item['itemable_id'])) {
                continue;
            }

            $modelClass = $typeMap[$item['itemable_type']] ?? null;
            if (!$modelClass || !$modelClass::whereKey($item['itemable_id'])->exists()) {
                continue;
            }

            $package->items()->create([
                'itemable_type' => $modelClass,
                'itemable_id' => $item['itemable_id'],
                'quantity' => max(1, (int)($item['quantity'] ?? 1)),
                'unit_price' => (float)($item['unit_price'] ?? 0),
            ]);
        }
    }
}
