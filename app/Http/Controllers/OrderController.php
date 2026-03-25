<?php

namespace App\Http\Controllers;

use App\Models\{Order, History, Patient, Catalog, Profile, Product, LabResult, OrderDetail, Service, Branch, Package, Template, ReportService, InventoryMovement};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Log, Cache, Auth};
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Carbon\Carbon;




class OrderController extends Controller
{
    private const HISTORY_BENEFIT_CUTOFF_DATE = '2026-03-25';
    private const LEGACY_HISTORY_BENEFIT_DAYS = 30;
    private const NEW_HISTORY_BENEFIT_DAYS = 15;

    /**
     * Mostrar listado de órdenes
     */
    public function index()
    {
        $orders = Order::with('patient')->latest()->paginate(15);
        return view('atenciones.orders.index', compact('orders'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('atenciones.orders.create');
    }

    /**
     * Cargar la orden con sus relaciones para la edición
     */
    public function edit(Order $order)
    {
        $order->load(['patient', 'details.itemable']);

        // Buscamos el detalle que se llama "HISTORIA" para obtener su precio
        $historyDetail = $order->details->where('name', 'HISTORIA')->first();
        
        // Pasamos el valor explícitamente a la vista
        $historyPrice = $historyDetail ? $historyDetail->price : 0;

        return view('atenciones.orders.edit', compact('order', 'historyPrice'));
    }

    /**
     * Actualizar la orden y sus detalles
     */
    /**
     * Actualizar la orden y sus detalles de forma segura
     */
    public function update(Request $request, Order $order)
    {
        $palabrasClave = ['HISTORIA', 'CONSULTA', 'EXTERNA', 'C. EXTERNA'];

        $request->validate([
            'items' => 'required|array|min:1',
        ]);

        try {
            return DB::transaction(function () use ($request, $order, $palabrasClave) {
                $detallesAnteriores = $order->details()->get();
                $totalReal = 0;
                $detallesIdsActuales = [];

                foreach ($request->items as $item) {
                    $tipo = strtolower($item['type'] ?? '');
                    $modelType = null;

                    if ($tipo === 'service') {
                        $modelType = \App\Models\Service::class;
                    } elseif ($tipo === 'catalog') {
                        $modelType = \App\Models\Catalog::class;
                    } elseif ($tipo === 'profile') {
                        $modelType = \App\Models\Profile::class;
                    } elseif ($tipo === 'product') {
                        $modelType = \App\Models\Product::class;
                    }

                    if (!$modelType) {
                        Log::error("Tipo de ítem no reconocido: " . $tipo . " para el ítem: " . $item['name']);
                        continue;
                    }

                    $cantidadNueva = (int) ($item['quantity'] ?? 1);
                    $detalleAnterior = $detallesAnteriores->first(function ($detail) use ($modelType, $item) {
                        return $detail->itemable_type === $modelType
                            && (int) $detail->itemable_id === (int) $item['id'];
                    });
                    $cantidadAnterior = (int) ($detalleAnterior->quantity ?? 0);

                    $detail = OrderDetail::updateOrCreate(
                        [
                            'order_id' => $order->id,
                            'itemable_id' => $item['id'],
                            'itemable_type' => $modelType
                        ],
                        [
                            'name' => $item['name'],
                            'quantity' => $cantidadNueva,
                            'price' => (float)($item['price'] ?? 0)
                        ]
                    );

                    $detallesIdsActuales[] = $detail->id;
                    $totalReal += $detail->price;

                    if ($tipo === 'product') {
                        $delta = $cantidadNueva - $cantidadAnterior;
                        if ($delta > 0) {
                            $this->registerOrderProductMovement($order, $detail, (int) $item['id'], $delta, 'salida', 'Salida por edición de orden');
                        } elseif ($delta < 0) {
                            $this->registerOrderProductMovement($order, $detail, (int) $item['id'], abs($delta), 'entrada', 'Reingreso por edición de orden');
                        }
                    }

                    $esAdministrativo = Str::contains(strtoupper($item['name']), $palabrasClave);
                    if (in_array($tipo, ['catalog', 'profile'], true) && !$esAdministrativo) {
                        $this->sincronizarResultadosLab($detail, $item);
                    }

                    if ($tipo === 'service' && !$esAdministrativo) {
                        $this->ensureServiceTemplate($detail, $order->loadMissing('patient'));
                    }
                }

                $detallesEliminados = $detallesAnteriores->whereNotIn('id', $detallesIdsActuales);
                foreach ($detallesEliminados as $detalleEliminado) {
                    if ($detalleEliminado->itemable_type === Product::class) {
                        $this->registerOrderProductMovement(
                            $order,
                            $detalleEliminado,
                            (int) $detalleEliminado->itemable_id,
                            (int) $detalleEliminado->quantity,
                            'entrada',
                            'Reingreso por eliminación de ítem en orden'
                        );
                    }
                }

                OrderDetail::where('order_id', $order->id)
                    ->whereNotIn('id', $detallesIdsActuales)
                    ->delete();

                $order->update(['total' => $totalReal]);

                return redirect()->route('orders.index')->with('success', 'Orden actualizada correctamente.');
            });
        } catch (\Exception $e) {
            Log::error("Error crítico en actualización de orden ID {$order->id}: " . $e->getMessage());
            return back()->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }

    // Función auxiliar para no repetir código de LabResults
    private function createLabResultFromItem($detail, $item) 
{
    $isProfile = ($item['type'] === 'profile' || $item['type'] === 'perfil');
    if ($isProfile) {
        $profile = \App\Models\Profile::with('catalogs')->find($item['id']);
        if ($profile) {
            foreach ($profile->catalogs as $cat) {
                $this->createLabResult($detail->id, $cat);
            }
        }
    } else {
        $catalog = \App\Models\Catalog::find($item['id']);
        if ($catalog) {
            $this->createLabResult($detail->id, $catalog);
        }
    }
}

    /**
     * Buscador AJAX para TomSelect (Pacientes)
     */
    public function searchPatients(Request $request)
    {
        $q = $request->input('q');
        return Patient::where('dni', 'LIKE', "%$q%")
            ->orWhere('first_name', 'LIKE', "%$q%")
            ->orWhere('last_name', 'LIKE', "%$q%")
            ->limit(10)
            ->get(['id', 'dni', 'first_name', 'last_name']);
    }

    /**
     * Buscador AJAX para TomSelect (Exámenes y Perfiles)
     */
    public function searchItems(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $cacheKey = 'search_items:' . md5(mb_strtolower($q));

        $result = Cache::remember($cacheKey, now()->addSeconds(45), function () use ($q) {
            $catalogs = Catalog::query()
                ->select(['catalogs.id', 'catalogs.name', 'catalogs.price', 'catalogs.reference_range', 'catalogs.unit', 'areas.name as area_name'])
                ->leftJoin('areas', 'areas.id', '=', 'catalogs.area_id')
                ->where(function ($query) use ($q) {
                    $this->applyFlexibleSearch($query, 'catalogs.name', $q);
                })
                ->orderBy('catalogs.name')
                ->limit(8)
                ->get()
                ->map(fn($i) => [
                    'id' => $i->id,
                    'name' => $i->name,
                    'area' => $i->area_name ? strtoupper($i->area_name) : 'SIN ÁREA',
                    'price' => $i->price,
                    'type' => 'catalog',
                    'reference_range' => $i->reference_range, // ASEGÚRATE DE ENVIARLO
                    'unit' => $i->unit
                ]);

            $profiles = Profile::query()
                ->select(['profiles.id', 'profiles.name', 'profiles.price', 'areas.name as area_name'])
                ->leftJoin('areas', 'areas.id', '=', 'profiles.area_id')
                ->where(function ($query) use ($q) {
                    $this->applyFlexibleSearch($query, 'profiles.name', $q);
                })
                ->orderBy('profiles.name')
                ->limit(8)
                ->get()
                ->map(fn($i) => [
                    'id' => $i->id,
                    'name' => $i->name,
                    'area' => $i->area_name ? strtoupper($i->area_name) : 'SIN ÁREA',
                    'price' => $i->price,
                    'type' => 'profile',
                ]);


            $services = Service::query()
                ->where(function ($query) use ($q) {
                    $this->applyFlexibleSearch($query, 'nombre', $q);
                })
                ->orderBy('nombre')
                ->limit(8)
                ->get()
                ->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->nombre,
                        'price' => $service->precio,
                        'type' => 'service',
                        'area' => 'SERVICIO',
                    ];
                });

            $products = Product::query()
                ->select(['id', 'name', 'concentration', 'selling_price'])
                ->where('is_active', true)
                ->where(function ($query) use ($q) {
                    $this->applyFlexibleSearch($query, 'name', $q);
                })
                ->orderBy('name')
                ->limit(8)
                ->get()
                ->map(fn($product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'concentration' => $product->concentration,
                    'price' => $product->selling_price ?? 0,
                    'type' => 'product',
                    'area' => 'PRODUCTO',
                ]);

            $packages = Package::query()
                ->with('items.itemable')
                ->where('is_active', true)
                ->where(function ($query) use ($q) {
                    $this->applyFlexibleSearch($query, 'name', $q);
                    $this->applyFlexibleSearch($query, 'code', $q);
                })
                ->orderBy('name')
                ->limit(8)
                ->get()
                ->map(function ($package) {
                    $items = $package->items->map(function ($item) {
                        $itemable = $item->itemable;

                        if (!$itemable) {
                            return null;
                        }

                        $type = match ($item->itemable_type) {
                            Catalog::class => 'catalog',
                            Profile::class => 'profile',
                            Service::class => 'service',
                            Product::class => 'product',
                            default => null,
                        };

                        if (!$type) {
                            return null;
                        }

                        $name = $type === 'service' ? $itemable->nombre : $itemable->name;

                        return [
                            'id' => $itemable->id,
                            'name' => $name,
                            'type' => $type,
                            'quantity' => (int) $item->quantity,
                            'unit_price' => (float) $item->unit_price,
                            'area' => 'PAQUETE',
                        ];
                    })->filter()->values();

                    return [
                        'id' => $package->id,
                        'name' => $package->name,
                        'price' => (float) $package->price,
                        'type' => 'package',
                        'area' => 'PAQUETE',
                        'package_items' => $items,
                    ];
                });

            return $catalogs->merge($profiles)->merge($services)->merge($products)->merge($packages)->values();
        });

        return response()->json($result);
    }

    private function applyFlexibleSearch($query, string $field, string $rawQuery): void
    {
        $normalizedQuery = trim($rawQuery);
        $terms = collect(preg_split('/\s+/u', mb_strtolower($normalizedQuery), -1, PREG_SPLIT_NO_EMPTY))
            ->filter(fn ($term) => mb_strlen($term) >= 2)
            ->take(5)
            ->values();

        if ($normalizedQuery !== '') {
            $query->orWhere($field, 'LIKE', "%{$normalizedQuery}%");
        }

        foreach ($terms as $term) {
            $query->orWhere($field, 'LIKE', "%{$term}%");

            if (mb_strlen($term) >= 5) {
                $query->orWhere($field, 'LIKE', '%' . mb_substr($term, 0, -1) . '%');
            }
        }
    }

    public function getPatient(Patient $patient)
    {
        return response()->json($patient);
    }

    public function quickStorePatient(Request $request)
    {
        $validated = $request->validate([
            'dni' => 'required|string|unique:patients,dni',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:M,F,Otro',
            'phone' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $patient = Patient::create($validated);

        return response()->json($patient, 201);
    }

    public function quickUpdatePatient(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'dni' => 'required|string|unique:patients,dni,' . $patient->id,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:M,F,Otro',
            'phone' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $patient->update($validated);

        return response()->json($patient);
    }

    /**
     * Guardar nueva Orden
     */
    // ... dentro de OrderController.php
    public function store(Request $request)
    {
        $palabrasClave = ['HISTORIA', 'CONSULTA', 'EXTERNA', 'C. EXTERNA'];

        $request->validate([
            'patient_id' => 'required',
            'items' => 'required|array|min:1',
            'total_amount' => 'required|numeric',
        ]);

        $tieneHistoriaReciente = History::where('patient_id', $request->patient_id)
            ->where('created_at', '>=', now()->subDays(30))
            ->exists();

        try {
            return DB::transaction(function () use ($request, $palabrasClave, $tieneHistoriaReciente) {
                
                $order = $this->createOrderWithUniqueCode($request);

                $totalReal = 0;
                $generarRegistroHistoria = false;

                foreach ($request->items as $item) {
                    $nombreItemActual = strtoupper($item['name']);
                    $esAdministrativo = Str::contains($nombreItemActual, $palabrasClave);
                    $cantidad = max(1, (int) ($item['quantity'] ?? 1));
                    $precioUnitario = (float) ($item['unit_price'] ?? $item['price'] ?? 0);
                    
                    $precioAplicado = ($esAdministrativo && $tieneHistoriaReciente) ? 0 : ($precioUnitario * $cantidad);
                    $totalReal += $precioAplicado;

                    $modelType = match($item['type']) {
                        'catalog' => Catalog::class,
                        'profile' => Profile::class,
                        'service' => Service::class,
                        'product' => Product::class,
                    };

                    $detail = OrderDetail::create([
                        'order_id' => $order->id,
                        'itemable_id' => $item['id'],
                        'itemable_type' => $modelType,
                        'name' => $item['name'],
                        'quantity' => $cantidad,
                        'price' => $precioAplicado,
                    ]);

                    if ($item['type'] === 'product') {
                        $this->registerOrderProductMovement($order, $detail, (int) $item['id'], $cantidad, 'salida', 'Salida por venta desde orden');
                    }

                    // CORRECCIÓN LÓGICA:
                    if ($esAdministrativo) {
                        $generarRegistroHistoria = true; // Si es HISTORIA, activamos la bandera
                    } else {
                        // Solo procesar laboratorio si NO es un servicio administrativo
                        if (in_array($item['type'], ['catalog', 'profile'], true)) {
                            $this->processLabResults($detail, $item);
                        }

                        if ($item['type'] === 'service') {
                            $this->ensureServiceTemplate($detail);
                        }
                    }
                }

                $order->update(['total' => $totalReal]);

                if ($generarRegistroHistoria) {
                    History::create([
                        'patient_id' => $request->patient_id,
                        'user_id' => Auth::id(),
                        'order_id' => $order->id,
                    ]);
                }

                return redirect()->route('orders.index')->with('success', 'Orden guardada con éxito');
            });
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                Log::warning('Intento duplicado de creación de orden detectado', [
                    'patient_id' => $request->patient_id,
                    'user_id' => Auth::id(),
                ]);

                return redirect()->route('orders.create')->withErrors([
                    'error' => 'Se detectó un envío duplicado del formulario. Verifique el listado de órdenes e intente nuevamente.'
                ]);
            }

            Log::error("Error SQL al crear orden: " . $e->getMessage());
            return back()->withErrors(['error' => 'Error de base de datos al crear la orden.']);
        } catch (\Exception $e) {
            Log::error("Error al crear orden: " . $e->getMessage());
            return back()->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    private function createOrderWithUniqueCode(Request $request): Order
    {
        $attempts = 0;

        do {
            $attempts++;
            $code = 'ORD-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));

            try {
                return Order::create([
                    'code' => $code,
                    'patient_id' => $request->patient_id,
                    'total' => 0,
                    'payment_status' => $request->payment_status ?? 'pendiente',
                    'user_id' => Auth::id(),
                ]);
            } catch (QueryException $e) {
                $isDuplicateCode = (int) ($e->errorInfo[1] ?? 0) === 1062;

                if (!$isDuplicateCode || $attempts >= 5) {
                    throw $e;
                }
            }
        } while ($attempts < 5);

        throw new \RuntimeException('No se pudo generar un código único de orden.');
    }
/**
 * Guardar nueva Orden
 */
    private function processLabResults($detail, $item)
    {
        if ($item['type'] === 'profile') {
            $profile = Profile::with('catalogs')->find($item['id']);
            if ($profile) {
                foreach ($profile->catalogs as $catalog) {
                    $this->createLabResult($detail->id, $catalog);
                }
            }
        } elseif ($item['type'] === 'catalog') {
            $catalog = Catalog::find($item['id']);
            if ($catalog) {
                $this->createLabResult($detail->id, $catalog);
            }
        }
    }

    private function ensureServiceTemplate(OrderDetail $detail): void
    {
        $template = Template::where('service_id', $detail->itemable_id)->first();

        if (!$template) {
            if (!str_contains($detail->name, '[SIN PLANTILLA]')) {
                $detail->update([
                    'name' => $detail->name . ' [SIN PLANTILLA]'
                ]);
            }

            return;
        }
    }

    public function show(Order $order)
    {
        // 1. Cargamos las relaciones (incluyendo el producto dentro de details)
        $order->load(['patient', 'details.itemable', 'user']);

        // 2. Traemos la sucursal activa para el logo y datos (RUC, dirección, etc)
        $branch = \App\Models\Branch::where('estado', true)->first();

        // 3. Configuramos DomPDF para 80mm (226.7pt) y alto dinámico
        // El alto 800 es suficiente para la mayoría de tickets
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('atenciones.orders.ticket', compact('order', 'branch'))
                ->setPaper([0, 0, 226.77, 800], 'portrait');

        return $pdf->stream("Ticket_ORD-{$order->id}.pdf");
    }

    private function createLabResult($orderDetailId, $catalog)
    {
        // Verifica si ya existe antes de crear
        LabResult::firstOrCreate([
            'lab_item_id' => $orderDetailId,
            'catalog_id'  => $catalog->id
        ], [
            'reference_range' => $catalog->reference_range,
            'unit'            => $catalog->unit,
            'status'          => 'pendiente'
        ]);
    }

/**
 * Eliminar la orden y sus registros relacionados (Detalles, Resultados y Historia)
 */
    public function destroy(Order $order)
    {
        try {
            return DB::transaction(function () use ($order) {
                
                // 1. Eliminar la Historia Clínica si existe
                // La relación 'history' está definida en el modelo Order
                if ($order->history) {
                    $order->history->delete();
                }

                // 2. Eliminar los detalles de la orden
                // Al ejecutar delete() en cada detalle, se dispara el evento 'deleting' 
                // definido en OrderDetail.php que limpia los LabResult asociados.
                foreach ($order->details as $detail) {
                    if ($detail->itemable_type === Product::class) {
                        $this->registerOrderProductMovement(
                            $order,
                            $detail,
                            (int) $detail->itemable_id,
                            (int) $detail->quantity,
                            'entrada',
                            'Reingreso por anulación de orden'
                        );
                    }
                    $detail->delete(); 
                }

                // 3. Finalmente eliminar la orden principal
                $order->delete();

                return redirect()->route('orders.index')
                    ->with('success', 'Orden, resultados de laboratorio e historial eliminados correctamente.');
            });
        } catch (\Exception $e) {
            // En caso de error, la transacción hace rollback automático
            return back()->withErrors(['error' => 'Error al eliminar la orden: ' . $e->getMessage()]);
        }
    }

    private function registerOrderProductMovement(
        Order $order,
        OrderDetail $detail,
        int $productId,
        int $quantity,
        string $movementType,
        string $notes
    ): void {
        $quantity = max(1, $quantity);
        $product = Product::lockForUpdate()->findOrFail($productId);
        $stockBefore = (int) $product->stock;

        $stockAfter = $movementType === 'entrada'
            ? $stockBefore + $quantity
            : $stockBefore - $quantity;

        if ($stockAfter < 0) {
            throw new \RuntimeException("Stock insuficiente para {$product->name}. Stock actual: {$stockBefore}, solicitado: {$quantity}.");
        }

        $product->update(['stock' => $stockAfter]);

        InventoryMovement::create([
            'product_id' => $product->id,
            'order_id' => $order->id,
            'order_detail_id' => $detail->id,
            'user_id' => Auth::id(),
            'movement_type' => $movementType,
            'source' => 'orden',
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'unit_cost' => $product->purchase_price,
            'unit_price' => $product->selling_price,
            'notes' => $notes,
            'movement_at' => now(),
        ]);
    }

    public function checkHistory(Patient $patient)
    {
        $lastHistory = \App\Models\History::where('patient_id', $patient->id)
            ->latest()
            ->first();

        if (!$lastHistory) {
            return response()->json(['has_history' => false]);
        }

        $daysDiff = now()->diffInDays($lastHistory->created_at);
        $cutoffDate = Carbon::parse(self::HISTORY_BENEFIT_CUTOFF_DATE)->startOfDay();

        $hasLegacyBenefit = \App\Models\History::where('patient_id', $patient->id)
            ->where('created_at', '<', $cutoffDate)
            ->exists();

        $benefitDays = $hasLegacyBenefit
            ? self::LEGACY_HISTORY_BENEFIT_DAYS
            : self::NEW_HISTORY_BENEFIT_DAYS;

        $benefitType = $hasLegacyBenefit ? 'anterior' : 'nuevo';

        return response()->json([
            'has_history' => true,
            'days' => $daysDiff,
            'date' => $lastHistory->created_at->format('d/m/Y'),
            'is_free' => $daysDiff <= $benefitDays,
            'benefit_type' => $benefitType,
            'benefit_days' => $benefitDays,
            'benefit_label' => sprintf('Beneficio %s (%d días)', ucfirst($benefitType), $benefitDays)
        ]);
    }

    private function sincronizarResultadosLab($detail, $item)
    {
        // 1. Identificar si es perfil o catálogo individual
        $catalogIds = [];
        if ($item['type'] === 'profile') {
            $catalogIds = Profile::find($item['id'])->catalogs()->pluck('catalogs.id')->toArray();
        } else {
            $catalogIds = [$item['id']];
        }

        // 2. Iterar y crear con los datos reales del catálogo
        foreach ($catalogIds as $catId) {
            // Obtenemos el catálogo real para extraer los valores de referencia
            $catalog = Catalog::find($catId);
            
            if ($catalog) {
                LabResult::firstOrCreate(
                    [
                        'lab_item_id' => $detail->id, 
                        'catalog_id'  => $catId
                    ],
                    [
                        'status'          => 'pendiente',
                        // AQUÍ ESTÁ LA CORRECCIÓN: asignamos los valores del catálogo
                        'reference_range' => $catalog->reference_range ?? 'N/A',
                        'unit'            => $catalog->unit ?? ''
                    ]
                );
            }
        }
    }
}
