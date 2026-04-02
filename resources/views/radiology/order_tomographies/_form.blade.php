@php
    $selectedRadiography = old('radiography_id', $orderTomography->radiography_id);
    $selectedAgreement = old('agreement_id', $orderTomography->agreement_id);
@endphp

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Código</label>
        <input type="text" class="form-control" value="{{ $orderTomography->code ?? $nextCode ?? 'Se generará automáticamente' }}" disabled>
    </div>
    <div class="col-md-4">
        <label class="form-label">Tipo de Servicio</label>
        <select name="service_type" class="form-select" required>
            @foreach(['EMERGENCY' => 'Emergencia', 'PRIVATE' => 'Particular', 'AGREEMENT' => 'Convenio'] as $key => $label)
                <option value="{{ $key }}" @selected(old('service_type', $orderTomography->service_type ?? 'PRIVATE') === $key)>{{ $label }}</option>
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
        <select name="patient_id" class="form-select" required>
            <option value="">Seleccione un paciente</option>
            @foreach($patients as $patient)
                <option value="{{ $patient->id }}" @selected(old('patient_id', $orderTomography->patient_id) == $patient->id)>
                    {{ $patient->dni }} - {{ $patient->last_name }} {{ $patient->first_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Radiografía / Estudio</label>
        <select name="radiography_id" id="radiography_id" class="form-select" required>
            <option value="">Seleccione estudio</option>
            @foreach($radiographies as $radiography)
                <option value="{{ $radiography->id }}"
                        data-private-price="{{ $radiography->private_price ?? 0 }}"
                        data-agreement-prices='@json($radiography->agreementPrices->map(fn($price) => ["agreement_id" => $price->agreement_id, "price" => $price->price])->values())'
                        @selected($selectedRadiography == $radiography->id)>
                    {{ $radiography->description }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Convenio (opcional)</label>
        <select name="agreement_id" id="agreement_id" class="form-select">
            <option value="">Sin convenio</option>
            @foreach($agreements as $agreement)
                <option value="{{ $agreement->id }}" @selected($selectedAgreement == $agreement->id)>{{ $agreement->description }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Tipo de Pago</label>
        <select name="payment_type" class="form-select" required>
            @foreach(['CASH' => 'Efectivo', 'YAPE' => 'Yape', 'TRANSFER' => 'Transferencia', 'PENDING_PAYMENT' => 'Pago pendiente'] as $key => $label)
                <option value="{{ $key }}" @selected(old('payment_type', $orderTomography->payment_type ?? 'CASH') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Total (S/)</label>
        <input type="number" name="total" id="total" class="form-control" step="0.01" min="0" value="{{ old('total', $orderTomography->total) }}" placeholder="Auto si se deja vacío">
        <small class="text-muted">Se autocompleta con el precio del estudio.</small>
    </div>

    <div class="col-md-3">
        <label class="form-label">Tipo de Documento</label>
        <select name="document_type" class="form-select">
            <option value="">Sin documento</option>
            <option value="RECEIPT" @selected(old('document_type', $orderTomography->document_type) === 'RECEIPT')>Boleta</option>
            <option value="INVOICE" @selected(old('document_type', $orderTomography->document_type) === 'INVOICE')>Factura</option>
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">N° Documento</label>
        <input type="text" name="document_number" class="form-control" value="{{ old('document_number', $orderTomography->document_number) }}" maxlength="50">
    </div>
</div>

<script>
(function () {
    const radiography = document.getElementById('radiography_id');
    const agreement = document.getElementById('agreement_id');
    const total = document.getElementById('total');

    if (!radiography || !agreement || !total) return;

    function updateTotal() {
        if (total.value !== '') return;

        const selected = radiography.options[radiography.selectedIndex];
        if (!selected || !selected.value) return;

        const privatePrice = Number(selected.dataset.privatePrice || 0);
        const agreementPrices = JSON.parse(selected.dataset.agreementPrices || '[]');
        const agreementId = Number(agreement.value || 0);

        const agreementPrice = agreementPrices.find((item) => Number(item.agreement_id) === agreementId);
        const price = agreementPrice ? Number(agreementPrice.price || 0) : privatePrice;

        total.value = price.toFixed(2);
    }

    radiography.addEventListener('change', updateTotal);
    agreement.addEventListener('change', updateTotal);
    updateTotal();
})();
</script>
