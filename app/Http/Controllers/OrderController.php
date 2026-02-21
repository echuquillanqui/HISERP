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
        // 1. Definimos las mismas palabras clave que en el store para mantener la consistencia
        $palabrasClave = ['HISTORIA', 'CONSULTA', 'EXTERNA', 'C. EXTERNA'];

        $request->validate([
            'patient_id' => 'required',
            'payment_status' => 'required|in:pendiente,pagado,anulado',
            'items' => 'required|array|min:1',
            'total_amount' => 'required|numeric'
        ]);

        try {
            return DB::transaction(function () use ($request, $order, $palabrasClave) {
                
                // Actualizar datos principales de la orden
                $order->update([
                    'patient_id'       => $request->patient_id,
                    'payment_status'   => $request->payment_status,
                    'payment_method'   => $request->payment_method,
                    'operation_number' => $request->operation_number,
                    'total'            => $request->total_amount,
                ]);

                $incomingItems = collect($request->input('items', []));
                $generarRegistroHistoria = false; 
                
                // Mapear UIDs para limpieza
                $incomingUids = $incomingItems->map(fn($item) => 
                    (($item['type'] === 'profile' || $item['type'] === 'perfil') ? 'profile' : 'catalog') . $item['id']
                );

                // LIMPIEZA: Eliminar lo que el usuario quitó
                foreach ($order->details as $detail) {
                    $currentUid = (str_contains($detail->itemable_type, 'Profile') ? 'profile' : 'catalog') . $detail->itemable_id;
                    
                    if (!$incomingUids->contains($currentUid)) {
                        $detail->delete(); 
                    }
                }

                // SINCRONIZACIÓN: Agregar nuevos y detectar servicios administrativos
                foreach ($incomingItems as $item) {
                    $type = ($item['type'] === 'profile' || $item['type'] === 'perfil') ? 'profile' : 'catalog';
                    $modelType = ($type === 'profile') ? \App\Models\Profile::class : \App\Models\Catalog::class;
                    $nombreItemActual = strtoupper($item['name']);

                    // 2. CAMBIO AQUÍ: Usamos la lógica flexible con el array de palabras clave
                    $esAdministrativo = \Illuminate\Support\Str::contains($nombreItemActual, $palabrasClave);

                    if ($esAdministrativo) {
                        $generarRegistroHistoria = true;
                    }

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

                        // 3. CAMBIO AQUÍ: Generar resultados solo si NO es administrativo
                        if (!$esAdministrativo) {
                            $this->createLabResultFromItem($newDetail, $item);
                        }
                    }
                }

                // LÓGICA DE HISTORIA
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
        
        // Cargamos la relación 'area' para Catálogos
        $catalogs = Catalog::with('area')->where('name', 'LIKE', "%$q%")
            ->limit(10)->get()
            ->map(fn($i) => [
                'id' => $i->id, 
                'name' => $i->name, 
                'area' => $i->area ? strtoupper($i->area->name) : 'SIN ÁREA', // Obtenemos el nombre del área
                'price' => $i->price, 
                'type' => 'catalog'
            ]);

        // Cargamos la relación 'area' para Perfiles
        $profiles = Profile::with('area')->where('name', 'LIKE', "%$q%")
            ->limit(10)->get()
            ->map(fn($i) => [
                'id' => $i->id, 
                'name' => $i->name, 
                'area' => $i->area ? strtoupper($i->area->name) : 'SIN ÁREA', // Obtenemos el nombre del área
                'price' => $i->price, 
                'type' => 'profile'
            ]);

        return response()->json($catalogs->merge($profiles));
    }

    /**
     * Guardar nueva Orden
     */
    // ... dentro de OrderController.php

/**
 * Guardar nueva Orden
 */
    public function store(Request $request)
    {
        $palabrasClave = ['HISTORIA', 'CONSULTA', 'EXTERNA', 'C. EXTERNA'];

        $request->validate([
            'patient_id' => 'required',
            'items' => 'required|array|min:1',
            'total_amount' => 'required|numeric',
        ]);

        // LÓGICA NUEVA: Verificar historia en los últimos 30 días
        $tieneHistoriaReciente = \App\Models\History::where('patient_id', $request->patient_id)
            ->where('created_at', '>=', now()->subDays(30))
            ->exists();

        try {
            return DB::transaction(function () use ($request, $palabrasClave, $tieneHistoriaReciente) {
                $codigo = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(4));
                
                // Inicializamos el total real de la orden (por si cambia el precio a 0)
                $totalReal = 0;

                // Primero creamos la orden con total 0, luego lo actualizamos o calculamos antes
                $order = Order::create([
                    'code' => $codigo,
                    'patient_id' => $request->patient_id,
                    'total' => 0, // Se actualizará al final del ciclo
                    'payment_status' => $request->payment_status ?? 'pendiente',
                    'payment_method' => $request->payment_method ?? 'efectivo',
                    'operation_number' => $request->operation_number,
                    'user_id' => auth()->id(),
                    'ip_address' => $request->ip(),
                ]);

                $generarRegistroHistoria = false;

                foreach ($request->items as $item) {
                    $nombreItemActual = strtoupper($item['name']);
                    $esAdministrativo = Str::contains($nombreItemActual, $palabrasClave);
                    
                    // REGLA DE NEGOCIO: Si es administrativo y tiene historia reciente, precio = 0
                    $precioAplicado = ($esAdministrativo && $tieneHistoriaReciente) ? 0 : $item['price'];
                    $totalReal += $precioAplicado;

                    $detail = OrderDetail::create([
                        'order_id' => $order->id,
                        'itemable_id' => $item['id'],
                        'itemable_type' => ($item['type'] === 'catalog') ? \App\Models\Catalog::class : \App\Models\Profile::class,
                        'name' => $item['name'],
                        'price' => $precioAplicado,
                    ]);

                    if ($esAdministrativo) {
                        $generarRegistroHistoria = true;
                    } else {
                        // Procesar Laboratorio...
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

                // Actualizamos el total definitivo de la orden
                $order->update(['total' => $totalReal]);

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
            dd("Error: " . $e->getMessage());
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
        LabResult::create([
            'lab_item_id'     => $orderDetailId,
            'catalog_id'      => $catalog->id,
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
}