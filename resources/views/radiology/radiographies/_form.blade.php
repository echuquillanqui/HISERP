<div class="mb-3">
    <label class="form-label">Descripción</label>
    <input type="text" name="description" class="form-control" value="{{ old('description', $radiography->description) }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Tipo de Contraste</label>
    <select name="contrast_type" class="form-select" required>
        <option value="CON_CONTRASTE" @selected(old('contrast_type', $radiography->contrast_type) === 'CON_CONTRASTE')>CON CONTRASTE</option>
        <option value="SIN_CONTRASTE" @selected(old('contrast_type', $radiography->contrast_type ?? 'SIN_CONTRASTE') === 'SIN_CONTRASTE')>SIN CONTRASTE</option>
    </select>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Precio Particular</label>
        <input type="number" name="private_price" class="form-control" step="0.01" min="0" value="{{ old('private_price', $radiography->private_price) }}" placeholder="0.00">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Uso de Placas</label>
        <input type="number" name="plate_usage" class="form-control" min="0" value="{{ old('plate_usage', $radiography->plate_usage ?? 0) }}">
    </div>
</div>

<div class="border rounded p-3 bg-light-subtle">
    <h6 class="fw-bold mb-3">Precios por Convenio (Opcional)</h6>

    @php
        $oldPrices = old('agreement_prices');
        $priceByAgreement = $oldPrices
            ? collect($oldPrices)->keyBy('agreement_id')->map(fn ($item) => $item['price'] ?? null)
            : $radiography->agreementPrices->pluck('price', 'agreement_id');
    @endphp

    <div class="row g-3">
        @forelse($agreements as $index => $agreement)
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text">{{ $agreement->description }}</span>
                    <input type="hidden" name="agreement_prices[{{ $index }}][agreement_id]" value="{{ $agreement->id }}">
                    <input type="number"
                           class="form-control"
                           step="0.01"
                           min="0"
                           name="agreement_prices[{{ $index }}][price]"
                           value="{{ $priceByAgreement[$agreement->id] ?? '' }}"
                           placeholder="Sin precio">
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-muted mb-0">No hay convenios activos. Registra convenios para definir precios diferenciados.</p>
            </div>
        @endforelse
    </div>
</div>
