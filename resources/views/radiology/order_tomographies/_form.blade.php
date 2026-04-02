@php
    $selectedAgreement = old('agreement_id', $orderTomography->agreement_id);

    $initialItems = collect(old('items', []));

    if ($initialItems->isEmpty() && $orderTomography->exists) {
        if ($orderTomography->relationLoaded('items') && $orderTomography->items->isNotEmpty()) {
            $initialItems = $orderTomography->items->map(fn($item) => [
                'radiography_id' => $item->radiography_id,
                'quantity' => $item->quantity,
            ]);
        } elseif ($orderTomography->radiography_id) {
            $initialItems = collect([[
                'radiography_id' => $orderTomography->radiography_id,
                'quantity' => 1,
            ]]);
        }
    }
@endphp

<div x-data="tomographyOrderForm()" class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom text-primary fw-bold">
                DATOS DE LA ORDEN
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Código</label>
                        <input type="text" class="form-control" value="{{ $orderTomography->code ?? $nextCode ?? 'Se generará automáticamente' }}" disabled>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tipo de Servicio</label>
                        <select name="service_type" x-model="serviceType" class="form-select" required>
                            @foreach(['EMERGENCY' => 'Emergencia', 'PRIVATE' => 'Particular', 'AGREEMENT' => 'Convenio'] as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Medio de Atención</label>
                        <select name="care_medium" class="form-select" required>
                            @foreach(['OUTPATIENT' => 'Ambulatorio', 'AMBULANCE' => 'Ambulancia'] as $key => $label)
                                <option value="{{ $key }}" @selected(old('care_medium', $orderTomography->care_medium ?? 'OUTPATIENT') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Paciente</label>
                        <select name="patient_id" id="patient_id" class="form-select" required></select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Convenio (opcional)</label>
                        <select name="agreement_id" id="agreement_id" x-model="agreementId" @change="onAgreementChange" class="form-select">
                            <option value="">Particular (sin convenio)</option>
                            @foreach($agreements as $agreement)
                                <option value="{{ $agreement->id }}">{{ $agreement->description }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white py-3 border-bottom text-primary fw-bold">
                SELECCIÓN DE TOMOGRAFÍAS
            </div>
            <div class="card-body">
                <div class="row g-2 align-items-end mb-3">
                    <div class="col-md-8">
                        <label class="form-label">Tomografía / Estudio</label>
                        <select id="radiography_selector" class="form-select" x-model="selectedRadiographyId">
                            <option value="">Seleccione estudio</option>
                            @foreach($radiographies as $radiography)
                                <option value="{{ $radiography->id }}">{{ $radiography->description }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Cantidad</label>
                        <input type="number" min="1" x-model.number="selectedQuantity" class="form-control">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="button" class="btn btn-outline-primary" @click="addItem()">Agregar</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                        <tr class="small text-muted">
                            <th>DESCRIPCIÓN</th>
                            <th class="text-center">CANT.</th>
                            <th class="text-end">PRECIO UNIT.</th>
                            <th class="text-end">SUBTOTAL</th>
                            <th class="text-center">ACCIÓN</th>
                        </tr>
                        </thead>
                        <tbody>
                        <template x-for="(item, idx) in items" :key="item.uid">
                            <tr>
                                <td>
                                    <div class="fw-semibold" x-text="item.description"></div>
                                    <input type="hidden" :name="`items[${idx}][radiography_id]`" :value="item.radiography_id">
                                    <input type="hidden" :name="`items[${idx}][quantity]`" :value="item.quantity">
                                </td>
                                <td class="text-center" style="max-width: 90px;">
                                    <input type="number" min="1" class="form-control form-control-sm text-center" x-model.number="item.quantity" @input="refreshTotal()">
                                </td>
                                <td class="text-end">S/ <span x-text="item.unit_price.toFixed(2)"></span></td>
                                <td class="text-end fw-bold">S/ <span x-text="(item.unit_price * item.quantity).toFixed(2)"></span></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger border-0" @click="removeItem(item.uid)">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="items.length === 0">
                            <td colspan="5" class="text-center text-muted py-3">No hay tomografías seleccionadas.</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
            <div class="card-header bg-primary text-white py-3 text-center fw-bold">RESUMEN DE COBRO</div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label">Tipo de Pago</label>
                    <select name="payment_type" class="form-select" required>
                        @foreach(['CASH' => 'Efectivo', 'YAPE' => 'Yape', 'TRANSFER' => 'Transferencia', 'PENDING_PAYMENT' => 'Pago pendiente'] as $key => $label)
                            <option value="{{ $key }}" @selected(old('payment_type', $orderTomography->payment_type ?? 'CASH') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tipo de Documento</label>
                    <select name="document_type" class="form-select">
                        <option value="">Sin documento</option>
                        <option value="RECEIPT" @selected(old('document_type', $orderTomography->document_type) === 'RECEIPT')>Boleta</option>
                        <option value="INVOICE" @selected(old('document_type', $orderTomography->document_type) === 'INVOICE')>Factura</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">N° Documento</label>
                    <input type="text" name="document_number" class="form-control" value="{{ old('document_number', $orderTomography->document_number) }}" maxlength="50">
                </div>

                <div class="bg-light p-3 rounded mb-3 border text-center">
                    <h3 class="fw-bold text-primary mb-0">S/ <span x-text="total.toFixed(2)"></span></h3>
                    <input type="hidden" name="total" :value="total.toFixed(2)">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function tomographyOrderForm() {
    const radiographies = @json($radiographies->map(fn($radiography) => [
        'id' => $radiography->id,
        'description' => $radiography->description,
        'private_price' => (float) ($radiography->private_price ?? 0),
        'agreement_prices' => $radiography->agreementPrices->map(fn($price) => [
            'agreement_id' => (int) $price->agreement_id,
            'price' => (float) ($price->price ?? 0),
        ])->values(),
    ])->values());

    return {
        selectedRadiographyId: '',
        selectedQuantity: 1,
        items: [],
        total: 0,
        agreementId: @json((string) ($selectedAgreement ?? '')),
        serviceType: @json(old('service_type', $orderTomography->service_type ?? 'PRIVATE')),

        init() {
            this.initPatientSelect();
            this.loadInitialItems();
            this.refreshPrices();
            this.refreshTotal();
        },

        initPatientSelect() {
            const selectedPatient = @json($selectedPatient);
            const selectedPatientId = @json((string) old('patient_id', $orderTomography->patient_id ?? ''));

            const patientSelect = new TomSelect('#patient_id', {
                valueField: 'id',
                labelField: 'display',
                searchField: ['dni', 'display'],
                preload: true,
                maxOptions: 20,
                loadThrottle: 350,
                shouldLoad: (query) => query.length >= 2 || query.length === 0,
                load: (query, callback) => {
                    fetch(`/search-patients?q=${encodeURIComponent(query || '')}`)
                        .then((response) => response.json())
                        .then((patients) => callback(
                            patients.map((patient) => ({
                                ...patient,
                                display: `${patient.dni} - ${patient.last_name} ${patient.first_name}`,
                            }))
                        ))
                        .catch(() => callback());
                },
            });

            if (selectedPatient?.id) {
                patientSelect.addOption({
                    ...selectedPatient,
                    display: `${selectedPatient.dni} - ${selectedPatient.last_name} ${selectedPatient.first_name}`,
                });
            }

            if (selectedPatientId) {
                patientSelect.setValue(String(selectedPatientId), true);
            }
        },

        loadInitialItems() {
            const initialItems = @json($initialItems->values());
            this.items = initialItems
                .map((item, idx) => {
                    const radiography = this.findRadiography(item.radiography_id);
                    if (!radiography) return null;

                    return {
                        uid: `${radiography.id}-${idx}-${Date.now()}`,
                        radiography_id: Number(radiography.id),
                        description: radiography.description,
                        quantity: Number(item.quantity || 1),
                        unit_price: this.resolveUnitPrice(radiography),
                    };
                })
                .filter(Boolean);
        },

        findRadiography(id) {
            return radiographies.find((item) => Number(item.id) === Number(id));
        },

        resolveUnitPrice(radiography) {
            const agreementId = Number(this.agreementId || 0);
            if (!agreementId) {
                return Number(radiography.private_price || 0);
            }

            const agreementPrice = (radiography.agreement_prices || []).find((item) => Number(item.agreement_id) === agreementId);
            return Number(agreementPrice?.price ?? radiography.private_price ?? 0);
        },

        addItem() {
            if (!this.selectedRadiographyId) return;

            const radiography = this.findRadiography(this.selectedRadiographyId);
            if (!radiography) return;

            const existing = this.items.find((item) => item.radiography_id === Number(radiography.id));
            if (existing) {
                existing.quantity += Math.max(1, Number(this.selectedQuantity || 1));
            } else {
                this.items.push({
                    uid: `${radiography.id}-${Date.now()}`,
                    radiography_id: Number(radiography.id),
                    description: radiography.description,
                    quantity: Math.max(1, Number(this.selectedQuantity || 1)),
                    unit_price: this.resolveUnitPrice(radiography),
                });
            }

            this.selectedRadiographyId = '';
            this.selectedQuantity = 1;
            this.refreshTotal();
        },

        removeItem(uid) {
            this.items = this.items.filter((item) => item.uid !== uid);
            this.refreshTotal();
        },

        onAgreementChange() {
            if (this.agreementId) {
                this.serviceType = 'AGREEMENT';
            } else if (this.serviceType === 'AGREEMENT') {
                this.serviceType = 'PRIVATE';
            }

            this.refreshPrices();
            this.refreshTotal();
        },

        refreshPrices() {
            this.items = this.items.map((item) => {
                const radiography = this.findRadiography(item.radiography_id);
                return {
                    ...item,
                    unit_price: radiography ? this.resolveUnitPrice(radiography) : Number(item.unit_price || 0),
                };
            });
        },

        refreshTotal() {
            this.total = this.items.reduce((sum, item) => sum + (Number(item.unit_price || 0) * Math.max(1, Number(item.quantity || 1))), 0);
        },
    };
}
</script>
