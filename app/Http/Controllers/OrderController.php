<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\History;
use App\Models\Patient;
use App\Models\Catalog;
use App\Models\Profile;
use App\Models\LabResult;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
    public function update(Request $request, Order $order)
{
    $request->validate([
        'patient_id' => 'required',
        'payment_status' => 'required|in:pendiente,pagado,anulado',
        'items' => 'required|array|min:1',
        'total_amount' => 'required|numeric'
    ]);

    try {
        return DB::transaction(function () use ($request, $order) {
            
            // 1. Actualizar datos principales de la orden
            $order->update([
                'patient_id'       => $request->patient_id,
                'payment_status'   => $request->payment_status,
                'payment_method'   => $request->payment_method,
                'operation_number' => $request->operation_number,
                'total'            => $request->total_amount,
            ]);

            $incomingItems = collect($request->input('items', []));
            $generarRegistroHistoria = false; // Flag para rastrear si hay "HISTORIA"
            
            // 2. Mapear UIDs que vienen del formulario (tipo + id)
            $incomingUids = $incomingItems->map(fn($item) => 
                (($item['type'] === 'profile' || $item['type'] === 'perfil') ? 'profile' : 'catalog') . $item['id']
            );

            // 3. LIMPIEZA: Eliminar solo lo que el usuario quitó en la vista
            foreach ($order->details as $detail) {
                $currentUid = (str_contains($detail->itemable_type, 'Profile') ? 'profile' : 'catalog') . $detail->itemable_id;
                
                if (!$incomingUids->contains($currentUid)) {
                    $detail->delete(); 
                }
            }

            // 4. SINCRONIZACIÓN: Agregar nuevos y detectar Historia
            foreach ($incomingItems as $item) {
                $type = ($item['type'] === 'profile' || $item['type'] === 'perfil') ? 'profile' : 'catalog';
                $modelType = ($type === 'profile') ? \App\Models\Profile::class : \App\Models\Catalog::class;

                // Si el item se llama HISTORIA, marcamos para crear/actualizar registro
                if (str_contains(strtoupper($item['name']), 'HISTORIA')) {
                    $generarRegistroHistoria = true;
                }

                // Verificamos si ya existe para no duplicar ni perder resultados antiguos
                $exists = $order->details()
                    ->where('itemable_id', $item['id'])
                    ->where('itemable_type', $modelType)
                    ->exists();

                if (!$exists) {
                    $newDetail = OrderDetail::create([
                        'order_id' => $order->id,
                        'itemable_id' => $item['id'],
                        'itemable_type' => $modelType,
                        'name' => $item['name'],
                        'price' => $item['price'],
                    ]);

                    // Generar resultados solo si NO es historia
                    if (!str_contains(strtoupper($item['name']), 'HISTORIA')) {
                        $this->createLabResultFromItem($newDetail, $item);
                    }
                }
            }

            // 5. LÓGICA DE HISTORIA (Actualiza si existe, crea si es nueva, borra si se quitó)
            if ($generarRegistroHistoria) {
                \App\Models\History::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'patient_id' => $request->patient_id,
                        'user_id' => auth()->id()
                    ]
                );
            } else {
                \App\Models\History::where('order_id', $order->id)->delete();
            }

            return redirect()->route('orders.index')->with('success', 'Orden e Historia sincronizadas correctamente');
        });
    } catch (\Exception $e) {
        return back()->withErrors(['error' => 'Error: ' . $e->getMessage()])->withInput();
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
        $q = $request->input('q');
        
        $catalogs = Catalog::where('name', 'LIKE', "%$q%")
            ->limit(10)->get(['id', 'name', 'price'])
            ->map(fn($i) => ['id' => $i->id, 'name' => $i->name, 'price' => $i->price, 'type' => 'catalog']);

        $profiles = Profile::where('name', 'LIKE', "%$q%")
            ->limit(10)->get(['id', 'name', 'price'])
            ->map(fn($i) => ['id' => $i->id, 'name' => $i->name, 'price' => $i->price, 'type' => 'profile']);

        return response()->json($catalogs->merge($profiles));
    }

    /**
     * Guardar nueva Orden
     */
    public function store(Request $request)
    {
        // PASO 1: Ver si los datos llegan al servidor
        // Si al dar click ves una pantalla negra con datos, el formulario está bien.
        // Si la página se recarga de frente, el problema es el Validador.
        // dd($request->all()); 

        $request->validate([
            'patient_id' => 'required',
            'items' => 'required|array|min:1',
            'total_amount' => 'required|numeric',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // Generar código único si no lo tienes en el request
                $codigo = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(4));

                // PASO 2: Intentar crear la orden
                $order = Order::create([
                    'code' => $codigo, // Asegúrate que tu migración tenga 'code'
                    'patient_id' => $request->patient_id,
                    'total' => $request->total_amount,
                    'payment_status' => $request->payment_status ?? 'pendiente',
                    'payment_method' => $request->payment_method ?? 'efectivo',
                    'operation_number' => $request->operation_number,
                    'user_id' => auth()->id(),
                    'ip_address' => $request->ip(),
                ]);

                $generarRegistroHistoria = false;

                foreach ($request->items as $item) {
                    // PASO 3: Verificar los ítems
                    $detail = OrderDetail::create([
                        'order_id' => $order->id,
                        'itemable_id' => $item['id'],
                        'itemable_type' => ($item['type'] === 'catalog') ? \App\Models\Catalog::class : \App\Models\Profile::class,
                        'name' => $item['name'],
                        'price' => $item['price'],
                    ]);

                    if (str_contains(strtoupper($item['name']), 'HISTORIA')) {
                        $generarRegistroHistoria = true;
                    } else {
                        // Lógica de laboratorio
                        if ($item['type'] === 'profile') {
                            $profile = \App\Models\Profile::with('catalogs')->find($item['id']);
                            foreach ($profile->catalogs as $catalogExam) {
                                $this->createLabResult($detail->id, $catalogExam);
                            }
                        } else {
                            $catalogExam = \App\Models\Catalog::find($item['id']);
                            $this->createLabResult($detail->id, $catalogExam);
                        }
                    }
                }

                if ($generarRegistroHistoria) {
                    \App\Models\History::create([
                        'patient_id' => $request->patient_id,
                        'user_id' => auth()->id(),
                        'order_id' => $order->id,
                    ]);
                }

                return redirect()->route('orders.index')->with('success', 'Orden guardada con éxito');
            });
        } catch (\Exception $e) {
            // PASO 4: Si algo falla, NO recargues, muestra el error real
            dd("Error en el guardado: " . $e->getMessage(), $e);
        }
    }

    public function show(Order $order)
    {
        $order->load(['patient', 'details.labResults.catalog', 'user']);
        return view('atenciones.orders.show', compact('order'));
    }

    private function createLabResult($orderDetailId, $catalog)
{
    LabResult::create([
        'lab_item_id'     => $orderDetailId,
        'catalog_id'      => $catalog->id,
        'reference_range' => $catalog->reference_range,
        'unit'            => $catalog->unit,
        'status'          => 'pendiente'
    ]);
}
}