<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\Request;

class ControlInsumoController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:all,low,out,ok'],
        ]);

        $search = trim((string) ($filters['q'] ?? ''));
        $status = $filters['status'] ?? 'all';

        $productsQuery = Product::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('presentation', 'like', "%{$search}%")
                        ->orWhere('concentration', 'like', "%{$search}%");
                });
            })
            ->when($status === 'low', fn ($query) => $query->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0))
            ->when($status === 'out', fn ($query) => $query->where('stock', '<=', 0))
            ->when($status === 'ok', fn ($query) => $query->whereColumn('stock', '>', 'min_stock'))
            ->orderBy('name');

        $products = (clone $productsQuery)
            ->paginate(20)
            ->withQueryString();

        $kpis = [
            'total' => Product::count(),
            'low' => Product::whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0)->count(),
            'out' => Product::where('stock', '<=', 0)->count(),
            'recentMovements' => InventoryMovement::whereDate('movement_at', '>=', now()->subDays(7))->count(),
        ];

        $recentMovements = InventoryMovement::query()
            ->with(['product:id,name,code', 'user:id,name'])
            ->latest('movement_at')
            ->latest('id')
            ->limit(12)
            ->get();

        $manualProducts = Product::query()
            ->select(['id', 'name', 'code', 'stock', 'concentration', 'presentation'])
            ->orderBy('name')
            ->limit(300)
            ->get();

        return view('radiology.control_insumos.index', compact(
            'products',
            'kpis',
            'recentMovements',
            'filters',
            'manualProducts'
        ));
    }
}
