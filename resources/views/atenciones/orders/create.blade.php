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
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <span class="text-primary fw-bold">DATOS DEL PACIENTE</span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" @click="openPatientModal('create')">
                                <i class="bi bi-person-plus me-1"></i> Nuevo paciente
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" @click="openPatientModal('edit')" :disabled="!selectedPatientId">
                                <i class="bi bi-pencil-square me-1"></i> Editar seleccionado
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <select id="patient_select" name="patient_id" required></select>
                        
                        <template x-if="historyInfo">
                            <div :class="historyInfo.is_free ? 'alert alert-success' : 'alert alert-info'" class="mt-3 py-2 shadow-sm border-0 d-flex align-items-center">
                                <i :class="historyInfo.is_free ? 'bi bi-check-circle-fill' : 'bi bi-info-circle-fill'" class="fs-4 me-2"></i>
                                <div>
                                    <span x-text="'El paciente tiene ' + historyInfo.days + ' días desde su última historia (' + historyInfo.date + ').'"></span>
                                    <template x-if="historyInfo.is_free">
                                        <strong class="d-block text-uppercase small">¡Cuenta con el Beneficio de atención gratuita!</strong>
                                    </template>
                                </div>
                            </div>
                        </template>
                        @if(isset($order)) <small class="text-muted mt-2 d-block">Paciente actual: <strong>{{ $order->patient->dni }} - {{ $order->patient->last_name }} {{ $order->patient->first_name }}</strong></small>
                        @endif
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom text-primary fw-bold">
                        BÚSQUEDA DE EXÁMENES Y PERFILES
                    </div>
                    <div class="card-body">
                        <select id="item_select" class="mb-4" placeholder="Buscar exámenes y perfiles... (mínimo 2 letras)"></select>
                        
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr class="small text-muted">
                                        <th>DESCRIPCIÓN</th>
                                        <th class="text-center">CANT.</th>
                                        <th class="text-end">PRECIO UNIT.</th>
                                        <th class="text-end">SUBTOTAL</th>
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
                                                <input type="hidden" :name="'items['+index+'][unit_price]'" :value="item.unit_price">
                                                <input type="hidden" :name="'items['+index+'][quantity]'" :value="item.quantity">
                                                <input type="hidden" :name="'items['+index+'][price]'" :value="subtotal(item)">
                                            </td>
                                            <td class="text-center" style="max-width: 100px;">
                                                <input type="number" min="1" class="form-control form-control-sm text-center" x-model.number="item.quantity">
                                            </td>
                                            <td class="text-end fw-bold">S/ <span x-text="parseFloat(item.unit_price).toFixed(2)"></span></td>
                                            <td class="text-end fw-bold">S/ <span x-text="subtotal(item).toFixed(2)"></span></td>
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

    <div class="modal fade" id="patientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" x-text="patientModalMode === 'create' ? 'Nuevo paciente' : 'Editar paciente'"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label small fw-bold">DNI *</label><input type="text" class="form-control" x-model="patientForm.dni"></div>
                        <div class="col-md-4"><label class="form-label small fw-bold">Nombres *</label><input type="text" class="form-control" x-model="patientForm.first_name"></div>
                        <div class="col-md-4"><label class="form-label small fw-bold">Apellidos *</label><input type="text" class="form-control" x-model="patientForm.last_name"></div>
                        <div class="col-md-4"><label class="form-label small fw-bold">Fecha nacimiento</label><input type="date" class="form-control" x-model="patientForm.birth_date"></div>
                        <div class="col-md-4"><label class="form-label small fw-bold">Género</label><select class="form-select" x-model="patientForm.gender"><option value="">Seleccione...</option><option value="M">Masculino</option><option value="F">Femenino</option><option value="Otro">Otro</option></select></div>
                        <div class="col-md-4"><label class="form-label small fw-bold">Teléfono</label><input type="text" class="form-control" x-model="patientForm.phone"></div>
                        <div class="col-md-6"><label class="form-label small fw-bold">Correo</label><input type="email" class="form-control" x-model="patientForm.email"></div>
                        <div class="col-md-6"><label class="form-label small fw-bold">Dirección</label><input type="text" class="form-control" x-model="patientForm.address"></div>
                    </div>
                    <template x-if="patientFormError"><div class="alert alert-danger mt-3 mb-0 py-2" x-text="patientFormError"></div></template>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" :disabled="patientFormLoading" @click="savePatientFromModal()">
                        <span x-show="!patientFormLoading">Guardar</span>
                        <span x-show="patientFormLoading">Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function orderSystem() {
    return {
        cart: [

            @if(isset($order))
                @foreach($order->details as $detail)
                {
                    id: "{{ $detail->itemable_id }}",
                    type: "{{ str_contains($detail->itemable_type, 'Profile') ? 'profile' : 'catalog' }}",
                    name: "{{ $detail->name }}",
                    area: "{{ $detail->itemable && $detail->itemable->area ? strtoupper($detail->itemable->area->name) : 'SIN ÁREA' }}",
                    quantity: {{ $detail->quantity ?? 1 }},
                    unit_price: {{ $detail->quantity ? ($detail->price / $detail->quantity) : $detail->price }},
                    uid: "{{ (str_contains($detail->itemable_type, 'Profile') ? 'profile' : 'catalog') . $detail->itemable_id }}"
                },
                @endforeach
            @endif
        ],
        historyInfo: null,
        selectedPatientId: null,
        patientSelect: null,
        itemSelect: null,
        itemSearchController: null,
        patientModal: null,
        patientModalMode: 'create',
        patientFormLoading: false,
        patientFormError: '',
        patientForm: { id: null, dni: '', first_name: '', last_name: '', birth_date: '', gender: '', phone: '', email: '', address: '' },

        init() {
            this.patientModal = new bootstrap.Modal(document.getElementById('patientModal'));
            this.initPatientSelect();
            this.initItemSelect();
        },
        initPatientSelect() {
            const self = this;

            // Buscador de Pacientes
            this.patientSelect = new TomSelect('#patient_select', {
                valueField: 'id', labelField: 'display', searchField: ['dni', 'display'],
                preload: true,
                maxOptions: 20,
                loadThrottle: 350,
                shouldLoad: (q) => q.length >= 2 || q.length === 0,
                load: (q, cb) => {
                    fetch(`/search-patients?q=${encodeURIComponent(q || '')}`)
                        .then(r => r.json())
                        .then(j => cb(j.map(p => ({...p, display: `${p.dni} - ${p.last_name} ${p.first_name}`}))))
                        .catch(() => cb());
                },
                onChange: (id) => this.onPatientChange(id)
            });

            @if(isset($order))
                this.patientSelect.addOption({
                    id: "{{ $order->patient_id }}"
                    display: "{{ $order->patient->dni }} - {{ $order->patient->last_name }} {{ $order->patient->first_name }}"
                });
                this.patientSelect.setValue("{{ $order->patient_id }}");
            @endif

            // Buscador de Análisis
            },
        onPatientChange(id) {
            this.selectedPatientId = id || null;
            if(!id) { this.historyInfo = null; return; }
            fetch(`/check-patient-history/${id}`)
                .then(r => r.json())
                .then(data => {
                    if(data.has_history) {
                        this.historyInfo = data;
                        this.applyHistoryDiscount(data.is_free);
                    } else {
                        this.historyInfo = null;
                    }
                });
        },
        initItemSelect() {
            this.itemSelect = new TomSelect('#item_select', {
                valueField: 'uid',
                labelField: 'display_name',
                searchField: ['name', 'area', 'display_name'],
                maxOptions: 30,
                loadThrottle: 350,
                shouldLoad: (q) => q.length >= 2,
                load: (q, cb) => {
                    if (this.itemSearchController) this.itemSearchController.abort();
                    this.itemSearchController = new AbortController();
                    fetch(`/search-items?q=${encodeURIComponent(q)}`, { signal: this.itemSearchController.signal })
                        .then(r => r.json())
                        .then(j => cb(j.map(i => ({ ...i, uid: i.type+i.id, display_name: `${i.name} [${i.area || 'SIN ÁREA'}]` }))))
                        .catch(() => cb());
                },
                render: {
                    option: (data, escape) => `<div>${escape(data.name)} <span class="text-primary fw-bold">[${escape(data.area || 'SIN ÁREA')}]</span></div>`,
                    item: (data, escape) => `<div>${escape(data.name)} <span class="text-primary fw-bold">[${escape(data.area || 'SIN ÁREA')}]</span></div>`
                },
                onChange: (v) => {
                    if(!v) return;
                    const item = this.itemSelect.options[v];
                    if(!this.cart.find(i=>i.uid === item.uid)) {
                        
                        if(this.historyInfo && this.historyInfo.is_free) {
                            const palabras = ['HISTORIA', 'CONSULTA', 'EXTERNA', 'C. EXTERNA'];
                            if(palabras.some(p => item.name.toUpperCase().includes(p))) item.unit_price = 0;
                        }
                        this.cart.push({ ...item, quantity: 1, unit_price: parseFloat(item.unit_price ?? item.price ?? 0) });
                    }
                    this.itemSelect.clear();
                }
            });
        },

        openPatientModal(mode) {
            this.patientModalMode = mode;
            this.patientFormError = '';
            if (mode === 'create') {
                this.patientForm = { id: null, dni: '', first_name: '', last_name: '', birth_date: '', gender: '', phone: '', email: '', address: '' };
                this.patientModal.show();
                return;
            }
            if (!this.selectedPatientId) return;
            fetch(`/orders/patients/${this.selectedPatientId}`)
                .then(r => r.json())
                .then(patient => {
                    this.patientForm = {
                        id: patient.id, dni: patient.dni ?? '', first_name: patient.first_name ?? '', last_name: patient.last_name ?? '',
                        birth_date: patient.birth_date ?? '', gender: patient.gender ?? '', phone: patient.phone ?? '',
                        email: patient.email ?? '', address: patient.address ?? ''
                    };
                    this.patientModal.show();
                });
        },
        savePatientFromModal() {
            this.patientFormError = '';
            this.patientFormLoading = true;
            const isEdit = this.patientModalMode === 'edit' && this.patientForm.id;
            const url = isEdit ? `/orders/patients/${this.patientForm.id}` : '/orders/patients';
            const method = isEdit ? 'PUT' : 'POST';
            fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify(this.patientForm)
            }).then(async r => {
                const data = await r.json();
                if (!r.ok) throw data;
                const display = `${data.dni} - ${data.last_name} ${data.first_name}`;
                this.patientSelect.addOption({ ...data, display });
                this.patientSelect.setValue(String(data.id));
                this.selectedPatientId = String(data.id);
                this.patientModal.hide();
            }).catch(err => {
                this.patientFormError = err?.message || Object.values(err?.errors || {}).flat()[0] || 'No se pudo guardar el paciente.';
            }).finally(() => this.patientFormLoading = false);
        },

        applyHistoryDiscount(isFree) {
            const palabras = ['HISTORIA', 'CONSULTA', 'EXTERNA', 'C. EXTERNA'];
            this.cart.forEach(item => {
                if (isFree && palabras.some(p => item.name.toUpperCase().includes(p))) item.unit_price = 0;
            });
        },

        remove(i) { this.cart.splice(i, 1); },
        subtotal(item) { const cantidad = Math.max(1, parseInt(item.quantity || 1, 10)); item.quantity = cantidad; return cantidad * parseFloat(item.unit_price || 0); },
        total() { return this.cart.reduce((s, i) => s + this.subtotal(i), 0); }
    }
}
</script>
@endsection