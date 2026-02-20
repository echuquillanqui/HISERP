@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<style>
    /* FIX para que el buscador no se vaya al fondo */
    .ts-dropdown { z-index: 2000 !important; position: absolute !important; }
    .card, .table-responsive { overflow: visible !important; }
</style>

<div class="container py-4" x-data="orderSystem()">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('orders.store') }}" method="POST">
        @csrf
        
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom text-primary fw-bold">
                        DATOS DEL PACIENTE
                    </div>
                    <div class="card-body">
                        <select id="patient_select" name="patient_id" required></select>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom text-primary fw-bold">
                        BÚSQUEDA DE EXÁMENES Y PERFILES
                    </div>
                    <div class="card-body">
                        <select id="item_select" class="mb-4" placeholder="Buscar examen..."></select>
                        
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
                                                <span class="fw-bold text-uppercase" style="color: #0d6efd;" x-text="' [' + item.area + ']'"></span> </div>
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
                    <div class="card-header bg-primary text-white py-3 text-center fw-bold">
                        RESUMEN DE COBRO
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">MÉTODO DE PAGO</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="yape_plim">Yape / Plim</option>
                                <option value="tarjeta">Tarjeta</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">ESTADO DE PAGO</label>
                            <select name="payment_status" class="form-select fw-bold">
                                <option value="pagado">PAGADO</option>
                                <option value="pendiente">PENDIENTE</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">N° OPERACIÓN</label>
                            <input type="text" name="operation_number" class="form-control" placeholder="Ej: 123456">
                        </div>

                        <div class="bg-light p-3 rounded mb-4 border text-center">
                            <h2 class="fw-bold text-primary mb-0">S/ <span x-text="total().toFixed(2)"></span></h2>
                            <input type="hidden" name="total_amount" :value="total()">
                        </div>

                        <input type="hidden" name="history_price" value="0">

                        <button type="submit" class="btn btn-primary w-100 py-3 shadow fw-bold" :disabled="cart.length === 0">
                            CONFIRMAR Y GUARDAR
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
        cart: [],
        init() {
            // Buscador de Pacientes
            new TomSelect("#patient_select", {
                valueField: 'id', labelField: 'display', searchField: ['dni', 'display'],
                load: (q, cb) => {
                    if(!q.length) return cb();
                    fetch(`/search-patients?q=${encodeURIComponent(q)}`)
                        .then(r=>r.json())
                        .then(j=>cb(j.map(p=>({...p, display: p.dni+' - '+p.last_name+' '+p.first_name}))))
                        .catch(()=>cb());
                }
            });
            // Buscador de Análisis
            const itemSelect = new TomSelect("#item_select", {
                valueField: 'uid', 
                labelField: 'name', 
                searchField: 'name',
                // Personalizamos la vista en el buscador
                render: {
                    option: function(data, escape) {
                        return `<div>
                            <span class="fw-bold">${escape(data.name)}</span>
                            <span style="color: #0d6efd; font-size: 0.75rem; font-weight: bold;">[${escape(data.area)}]</span>
                        </div>`;
                    },
                    item: function(data, escape) {
                        return `<div>${escape(data.name)} <span style="color: #0d6efd; font-weight: bold;">[${escape(data.area)}]</span></div>`;
                    }
                },
                load: (q, cb) => {
                    if(!q.length) return cb();
                    fetch(`/search-items?q=${encodeURIComponent(q)}`)
                        .then(r=>r.json())
                        .then(j=>cb(j.map(i=>({...i, uid: i.type+i.id}))))
                        .catch(()=>cb());
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