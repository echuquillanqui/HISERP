@php
    $currentItems = collect(old('package_items', $package->items->map(function($item){
        return [
            'itemable_type' => class_basename($item->itemable_type),
            'itemable_id' => $item->itemable_id,
            'quantity' => $item->quantity,
            'unit_price' => (float)$item->unit_price,
        ];
    })->toArray() ?? []));

    $hasItem = fn(string $type, int $id) => $currentItems->first(fn($i) => strtolower($i['itemable_type']) === strtolower($type) && (int)$i['itemable_id'] === $id);
@endphp

<div class="card border-0 shadow-sm">
    <div class="card-body row g-3">
        <div class="col-md-6">
            <label class="form-label">Nombre</label>
            <input name="name" class="form-control" value="{{ old('name', $package->name ?? '') }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Código</label>
            <input name="code" class="form-control" value="{{ old('code', $package->code ?? '') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Precio de venta</label>
            <input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ old('price', $package->price ?? 0) }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Costo</label>
            <input type="number" step="0.01" min="0" name="cost" class="form-control" value="{{ old('cost', $package->cost ?? '') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Desde</label>
            <input type="date" name="starts_at" class="form-control" value="{{ old('starts_at', optional($package->starts_at ?? null)->format('Y-m-d')) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Hasta</label>
            <input type="date" name="ends_at" class="form-control" value="{{ old('ends_at', optional($package->ends_at ?? null)->format('Y-m-d')) }}">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $package->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Activo</label>
            </div>
        </div>
        <div class="col-12">
            <label class="form-label">Descripción</label>
            <textarea name="description" rows="3" class="form-control">{{ old('description', $package->description ?? '') }}</textarea>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-white fw-semibold">Ítems del paquete</div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-end mb-3">
            <div class="input-group input-group-sm" style="max-width: 320px;">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" id="package-items-search" class="form-control" placeholder="Buscar ítem...">
            </div>
        </div>

        @php
            $itemGroups = ['catalog' => $catalogs, 'profile' => $profiles, 'service' => $services, 'product' => $products];
            $typeLabels = ['catalog' => 'Catálogo', 'profile' => 'Perfil', 'service' => 'Servicio', 'product' => 'Producto'];
        @endphp

        <ul class="nav nav-tabs mb-3" id="package-item-tabs" role="tablist">
            @foreach($itemGroups as $type => $list)
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link {{ $loop->first ? 'active' : '' }}"
                        id="tab-{{ $type }}"
                        data-bs-toggle="tab"
                        data-bs-target="#pane-{{ $type }}"
                        type="button"
                        role="tab"
                        aria-controls="pane-{{ $type }}"
                        aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                    >
                        {{ $typeLabels[$type] ?? ucfirst($type) }}
                        <span class="badge bg-secondary-subtle text-dark ms-1">{{ $list->count() }}</span>
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="tab-content">
            @php $idx = 0; @endphp
            @foreach($itemGroups as $type => $list)
                @php $typeLabel = $typeLabels[$type] ?? ucfirst($type); @endphp
                <div
                    class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                    id="pane-{{ $type }}"
                    role="tabpanel"
                    aria-labelledby="tab-{{ $type }}"
                    tabindex="0"
                    data-type="{{ $type }}"
                >
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 40px"></th>
                                    <th>Nombre</th>
                                    <th style="width: 130px">Cantidad</th>
                                    <th style="width: 150px">Precio unit.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($list as $entry)
                                    @php
                                        $name = $type === 'service' ? $entry->nombre : $entry->name;
                                        $defaultPrice = $type === 'service' ? $entry->precio : ($type === 'product' ? $entry->selling_price : $entry->price);
                                        $selected = $hasItem($type, $entry->id);
                                    @endphp
                                    <tr class="package-item-row" data-name="{{ Illuminate\Support\Str::lower($name) }}" data-type-label="{{ Illuminate\Support\Str::lower($typeLabel) }}">
                                        <td>
                                            <input class="form-check-input package-item-toggle" type="checkbox" {{ $selected ? 'checked' : '' }} data-target="pkg-{{ $type }}-{{ $entry->id }}">
                                        </td>
                                        <td>{{ $name }}</td>
                                        <td>
                                            <input id="pkg-{{ $type }}-{{ $entry->id }}-qty" type="number" min="1" class="form-control form-control-sm" name="package_items[{{ $idx }}][quantity]" value="{{ $selected['quantity'] ?? 1 }}" {{ $selected ? '' : 'disabled' }}>
                                        </td>
                                        <td>
                                            <input id="pkg-{{ $type }}-{{ $entry->id }}-price" type="number" min="0" step="0.01" class="form-control form-control-sm" name="package_items[{{ $idx }}][unit_price]" value="{{ $selected['unit_price'] ?? $defaultPrice ?? 0 }}" {{ $selected ? '' : 'disabled' }}>
                                            <input id="pkg-{{ $type }}-{{ $entry->id }}-type" type="hidden" name="package_items[{{ $idx }}][itemable_type]" value="{{ $type }}" {{ $selected ? '' : 'disabled' }}>
                                            <input id="pkg-{{ $type }}-{{ $entry->id }}-id" type="hidden" name="package_items[{{ $idx }}][itemable_id]" value="{{ $entry->id }}" {{ $selected ? '' : 'disabled' }}>
                                        </td>
                                    </tr>
                                    @php $idx++; @endphp
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-muted text-center py-3">No hay registros de {{ Illuminate\Support\Str::lower($typeLabel) }}.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="mt-3 d-flex gap-2">
    <button class="btn btn-primary">Guardar</button>
    <a href="{{ route('packages.index') }}" class="btn btn-light">Cancelar</a>
</div>

<script>
    document.querySelectorAll('.package-item-toggle').forEach((checkbox) => {
        checkbox.addEventListener('change', (event) => {
            const rowPrefix = event.target.dataset.target;
            ['-qty', '-price', '-type', '-id'].forEach((suffix) => {
                const element = document.getElementById(`${rowPrefix}${suffix}`);
                if (element) {
                    element.disabled = !event.target.checked;
                }
            });
        });
    });

    const searchInput = document.getElementById('package-items-search');

    const applyPackageFilter = () => {
        const term = (searchInput?.value || '').trim().toLowerCase();

        const tabVisibility = {};
        document.querySelectorAll('.package-item-row').forEach((row) => {
            const rowName = row.dataset.name || '';
            const rowType = row.dataset.typeLabel || '';
            const visible = !term || rowName.includes(term) || rowType.includes(term);
            row.classList.toggle('d-none', !visible);

            const pane = row.closest('.tab-pane');
            if (pane) {
                tabVisibility[pane.id] = (tabVisibility[pane.id] || false) || visible;
            }
        });

        document.querySelectorAll('#package-item-tabs .nav-link').forEach((tabButton) => {
            const paneSelector = tabButton.dataset.bsTarget;
            const paneId = paneSelector?.replace('#', '');
            const visible = !term || (paneId && tabVisibility[paneId]);

            tabButton.classList.toggle('d-none', !visible);

            if (!visible && tabButton.classList.contains('active')) {
                tabButton.classList.remove('active');
            }
        });

        const activeTab = document.querySelector('#package-item-tabs .nav-link.active:not(.d-none)');
        if (!activeTab) {
            const firstVisibleTab = document.querySelector('#package-item-tabs .nav-link:not(.d-none)');
            if (firstVisibleTab && window.bootstrap?.Tab) {
                window.bootstrap.Tab.getOrCreateInstance(firstVisibleTab).show();
            }
        }
    };

    searchInput?.addEventListener('input', applyPackageFilter);
</script>
