<?php

namespace App\Http\Controllers;

use App\Models\{Order, History, Patient, Catalog, Profile, Product, LabResult, OrderDetail, Service, Branch};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Log, Cache, Auth};
use Illuminate\Support\Str;




class OrderController extends Controller
{
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
                
                $totalReal = 0;
                $detallesIdsActuales = [];

                foreach ($request->items as $item) {
                    // 1. IDENTIFICACIÓN ESTRICTA (EVITAMOS ASIGNACIONES ERRÓNEAS)
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

                    // 2. ACTUALIZACIÓN O CREACIÓN SEGURA
                    $detail = OrderDetail::updateOrCreate(
                        [
                            'order_id'      => $order->id,
                            'itemable_id'   => $item['id'],
                            'itemable_type' => $modelType // Se asegura de mantener el tipo correcto
                        ],
                        [
                            'name'     => $item['name'],
                            'quantity' => (int)($item['quantity'] ?? 1),
                            'price'    => (float)($item['price'] ?? 0)
                        ]
                    );

                    $detallesIdsActuales[] = $detail->id;
                    $totalReal += $detail->price;

                    // 3. SINCRONIZACIÓN DE LABORATORIO (Solo si no es servicio ni administrativo)
                    $esAdministrativo = Str::contains(strtoupper($item['name']), $palabrasClave);
                    if (in_array($tipo, ['catalog', 'profile'], true) && !$esAdministrativo) {
                        $this->sincronizarResultadosLab($detail, $item);
                    }
                }

                // 4. ELIMINACIÓN DE ÍTEMS QUE YA NO ESTÁN EN EL REQUEST
                OrderDetail::where('order_id', $order->id)
                    ->whereNotIn('id', $detallesIdsActuales)
                    ->delete();

                // 5. ACTUALIZAR TOTAL
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
                ->select(['catalogs.id', 'catalogs.name', 'catalogs.price', 'areas.name as area_name'])
                ->leftJoin('areas', 'areas.id', '=', 'catalogs.area_id')
                ->where('catalogs.name', 'LIKE', "%{$q}%")
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
                ->where('profiles.name', 'LIKE', "%{$q}%")
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
    ->where('nombre', 'LIKE', "%{$q}%")
    ->get()
    ->map(function($service) {
        return [
            'id'    => $service->id,
            'name'  => $service->nombre,
            'price' => $service->precio,
            'type'  => 'service',
            'area'  => null // <--- Opcional: Esto indica explícitamente que no tiene área
        ];
    });

            $products = Product::query()
                ->select(['id', 'name', 'selling_price'])
                ->where('is_active', true)
                ->where('name', 'LIKE', "%{$q}%")
                ->orderBy('name')
                ->limit(8)
                ->get()
                ->map(fn($product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->selling_price ?? 0,
                    'type' => 'product',
                    'area' => 'PRODUCTO',
                ]);

            return $catalogs->merge($profiles)->merge($services)->merge($products)->values();
        });

        return response()->json($result);
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
                
                $order = Order::create([
                    'code' => 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                    'patient_id' => $request->patient_id,
                    'total' => 0,
                    'payment_status' => $request->payment_status ?? 'pendiente',
                    'user_id' => Auth::id(),
                ]);

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

                    // CORRECCIÓN LÓGICA:
                    if ($esAdministrativo) {
                        $generarRegistroHistoria = true; // Si es HISTORIA, activamos la bandera
                    } else {
                        // Solo procesar laboratorio si NO es un servicio administrativo
                        if (in_array($item['type'], ['catalog', 'profile'], true)) {
                            $this->processLabResults($detail, $item);
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
        } catch (\Exception $e) {
            Log::error("Error al crear orden: " . $e->getMessage());
            return back()->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
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

    public function checkHistory(Patient $patient)
    {
        $lastHistory = \App\Models\History::where('patient_id', $patient->id)
            ->latest()
            ->first();

        if (!$lastHistory) {
            return response()->json(['has_history' => false]);
        }

        $daysDiff = now()->diffInDays($lastHistory->created_at);

        return response()->json([
            'has_history' => true,
            'days' => $daysDiff,
            'date' => $lastHistory->created_at->format('d/m/Y'),
            'is_free' => $daysDiff <= 30
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
