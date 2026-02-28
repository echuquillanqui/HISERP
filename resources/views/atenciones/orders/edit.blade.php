@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<style>
    .ts-dropdown { z-index: 2000 !important; position: absolute !important; }
    .card, .table-responsive { overflow: visible !important; }
</style>

<div class="container py-4" x-data="orderSystem()">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary">
            <i class="bi bi-pencil-square me-2"></i>Editar Orden: {{ $order->code }}
        </h3>
        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('orders.update', $order) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom text-primary fw-bold">
                        DATOS DEL PACIENTE
                    </div>
                    <div class="card-body">
                        <select id="patient_select" name="patient_id" required></select>
                        <small class="text-muted mt-2 d-block">Paciente actual: <strong>{{ $order->patient->dni }} - {{ $order->patient->last_name }} {{ $order->patient->first_name }}</strong></small>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom text-primary fw-bold">
                        EXÁMENES Y PERFILES SELECCIONADOS
                    </div>
                    <div class="card-body">
                        <select id="item_select" class="mb-4" placeholder="Agregar más exámenes..."></select>
                        
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr class="small text-muted">
                                        <th>DESCRIPCIÓN</th>
                                        <th class="text-end">PRECIO</th>
                                        <th class="text-center">ACCION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(item, index) in cart" :key="index">
                                        <tr>
                                            <td>
                                                <div class="fw-bold" x-text="item.name"></div>
                                                <span class="fw-bold" style="color: #0d6efd;" x-text="' [' + item.area + ']'"></span> </div>
                                                <input type="hidden" :name="'items['+index+'][id]'" :value="item.id">
                                                <input type="hidden" :name="'items['+index+'][type]'" :value="item.type">
                                                <input type="hidden" :name="'items['+index+'][name]'" :value="item.name">
                                                <input type="hidden" :name="'items['+index+'][price]'" :value="item.price">
                                            </td>
                                            <td class="text-end fw-bold">S/ <span x-text="parseFloat(item.price).toFixed(2)"></span></td>
                                            <td class="text-center">
                                                <button type="button" @click="remove(index)" class="btn btn-sm btn-outline-danger border-0">
                                                    <i class="bi bi-trash3-fill"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-primary text-white py-3 text-center fw-bold text-uppercase">
                        Resumen de Cobro
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">MÉTODO DE PAGO</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="efectivo" {{ $order->payment_method == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                <option value="transferencia" {{ $order->payment_method == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                                <option value="yape_plim" {{ $order->payment_method == 'yape_plim' ? 'selected' : '' }}>Yape / Plim</option>
                                <option value="tarjeta" {{ $order->payment_method == 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">ESTADO DE PAGO</label>
                            <select name="payment_status" class="form-select fw-bold">
                                <option value="pagado" {{ $order->payment_status == 'pagado' ? 'selected' : '' }}>PAGADO</option>
                                <option value="pendiente" {{ $order->payment_status == 'pendiente' ? 'selected' : '' }}>PENDIENTE</option>
                                <option value="anulado" {{ $order->payment_status == 'anulado' ? 'selected' : '' }}>ANULADO</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">N° OPERACIÓN</label>
                            <input type="text" name="operation_number" class="form-control" value="{{ $order->operation_number }}" placeholder="Ej: 123456">
                        </div>

                        <div class="bg-light p-3 rounded mb-4 border text-center">
                            <h2 class="fw-bold text-primary mb-0">S/ <span x-text="total().toFixed(2)"></span></h2>
                            <input type="hidden" name="total_amount" :value="total()">
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-3 shadow fw-bold">
                            <i class="bi bi-check-circle me-1"></i> ACTUALIZAR ORDEN
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function orderSystem() {
    return {
        cart: [
            @foreach($order->details as $detail)
            {
                id: "{{ $detail->itemable_id }}",
                type: "{{ str_contains($detail->itemable_type, 'Profile') ? 'profile' : 'catalog' }}",
                name: "{{ $detail->name }}",
                // Accedemos al área a través de la relación polimórfica itemable
                area: "{{ $detail->itemable && $detail->itemable->area ? strtoupper($detail->itemable->area->name) : 'SIN ÁREA' }}",
                price: "{{ $detail->price }}",
                uid: "{{ (str_contains($detail->itemable_type, 'Profile') ? 'profile' : 'catalog') . $detail->itemable_id }}"
            },
            @endforeach
        ],
        init() {
            const patientSelect = new TomSelect("#patient_select", {
                valueField: 'id', labelField: 'display', searchField: ['dni', 'display'],
                load: (q, cb) => {
                    if(!q.length) return cb();
                    fetch(`/search-patients?q=${encodeURIComponent(q)}`)
                        .then(r=>r.json())
                        .then(j=>cb(j.map(p=>({...p, display: p.dni+' - '+p.last_name+' '+p.first_name}))))
                        .catch(()=>cb());
                }
            });
            // Precarga paciente
            patientSelect.addOption({
                id: "{{ $order->patient_id }}", 
                display: "{{ $order->patient->dni }} - {{ $order->patient->last_name }} {{ $order->patient->first_name }}"
            });
            patientSelect.setValue("{{ $order->patient_id }}");

            const itemSelect = new TomSelect("#item_select", {
                valueField: 'uid',
                labelField: 'display_name',
                searchField: ['name', 'area', 'display_name'],
                load: (q, cb) => {
                    if(!q.length) return cb();
                    fetch(`/search-items?q=${encodeURIComponent(q)}`)
                        .then(r=>r.json())
                        .then(j=>cb(j.map(i=>({
                            ...i,
                            uid: i.type+i.id,
                            area: i.area || 'SIN ÁREA',
                            display_name: `${i.name} [${i.area || 'SIN ÁREA'}]`
                        }))))
                        .catch(()=>cb());
                },
                render: {
                    option: (data, escape) => `<div>${escape(data.name)} <span class="text-primary fw-bold">[${escape(data.area || 'SIN ÁREA')}]</span></div>`,
                    item: (data, escape) => `<div>${escape(data.name)} <span class="text-primary fw-bold">[${escape(data.area || 'SIN ÁREA')}]</span></div>`
                },
                onChange: (v) => {
                    if(!v) return;
                    const item = itemSelect.options[v];
                    if(!this.cart.find(i=>i.uid === item.uid)) this.cart.push(item);
                    itemSelect.clear();
                }
            });
        },
        remove(i) { this.cart.splice(i, 1); },
        total() { return this.cart.reduce((s, i) => s + parseFloat(i.price || 0), 0); }
    }
}
</script>
@endsection