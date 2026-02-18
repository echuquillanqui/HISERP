@extends('layouts.app')
<style>
    /* Estilo para la pestaña ACTIVA */
    .nav-pills .nav-link.active {
        background-color: var(--cian-clinico) !important;
        color: #ffffff !important; /* Blanco puro */
        border: none;
    }

    /* Asegura que los iconos dentro de la pestaña activa sean blancos */
    .nav-pills .nav-link.active i, 
    .nav-pills .nav-link.active span {
        color: #ffffff !important;
        opacity: 1 !important;
    }

    /* Estilo para la pestaña INACTIVA */
    .nav-pills .nav-link:not(.active) {
        color: #6c757d !important;
        background: transparent;
    }

    /* Efecto Hover para que se vea profesional */
    .nav-pills .nav-link:hover:not(.active) {
        color: var(--cian-clinico) !important;
        background-color: rgba(0, 172, 193, 0.05);
    }
</style>

@section('content')
<div class="container-fluid" x-data="labManager()">
    <div id="toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1060;"></div>

    <div class="row" style="min-height: 85vh;">
        <div class="col-md-3 border-end bg-light py-4 shadow-sm">
            <div class="px-3 mb-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-uppercase text-muted medium mb-0">Áreas de Laboratorio</h6>
                <button class="btn btn-sm btn-primary-custom rounded-circle" @click="openModal('area')">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </div>
            
            <div class="list-group list-group-flush px-2">
                @foreach($areas as $area)
                <button type="button" 
                    class="list-group-item list-group-item-action rounded-3 mb-1 border-0 d-flex justify-content-between align-items-center py-3"
                    :class="selectedArea.id == {{ $area->id }} ? 'active-area shadow-sm' : ''"
                    @click="loadArea({{ $area->id }}, '{{ $area->name }}')">
                    <span class="fw-bold">{{ $area->name }}</span>
                    <i class="bi bi-chevron-right small" x-show="selectedArea.id == {{ $area->id }}"></i>
                </button>
                @endforeach
            </div>
        </div>

        <div class="col-md-9 py-4 px-5 bg-white">
            <template x-if="selectedArea.id">
                <div>
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h2 class="fw-bold text-dark mb-1" x-text="selectedArea.name"></h2>
                            <p class="text-muted">Gestión de exámenes y paquetes.</p>
                        </div>
                        <button class="btn btn-outline-warning border-0 fw-bold" @click="openModal('area', selectedArea)">
                            <i class="bi bi-pencil-square me-1"></i> Editar Área
                        </button>
                    </div>

                    <ul class="nav nav-pills mb-4 bg-light p-1 rounded-pill d-inline-flex shadow-sm">
                        <li class="nav-item">
                            <button class="nav-link rounded-pill px-4 py-2 d-flex align-items-center" 
                                :class="activeTab === 'catalog' ? 'active shadow-sm text-white' : 'text-muted'" 
                                @click="activeTab = 'catalog'">
                                <i class="bi bi-flask me-2" :class="activeTab === 'catalog' ? 'text-white' : ''"></i>
                                <span :class="activeTab === 'catalog' ? 'text-white' : ''">Catálogo</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link rounded-pill px-4 py-2 d-flex align-items-center" 
                                :class="activeTab === 'profiles' ? 'active shadow-sm text-white' : 'text-muted'" 
                                @click="activeTab = 'profiles'">
                                <i class="bi bi-layers-fill me-2" :class="activeTab === 'profiles' ? 'text-white' : ''"></i>
                                <span :class="activeTab === 'profiles' ? 'text-white' : ''">Perfiles</span>
                            </button>
                        </li>
                    </ul>

                    <div x-show="activeTab === 'catalog'" x-transition>
                        <div class="card border-0 shadow-sm border-top border-primary border-4">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">Exámenes Individuales</h6>
                                <button class="btn btn-sm btn-primary-custom" @click="openModal('catalog', {area_id: selectedArea.id})">
                                    <i class="bi bi-plus-lg me-1"></i> Agregar Examen
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light small text-uppercase">
                                        <tr>
                                            <th>Examen</th><th>Unidad</th><th>Referencia</th><th>Precio</th><th class="text-end">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="item in catalogs" :key="item.id">
                                            <tr>
                                                <td class="fw-bold text-primary" x-text="item.name"></td>
                                                <td x-text="item.unit || '-'"></td>
                                                <td class="text-muted small" x-text="item.reference_range || '-'"></td>
                                                <td class="fw-bold" x-text="'S/. ' + item.price"></td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-light border" @click="openModal('catalog', item)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div x-show="activeTab === 'profiles'" x-transition>
                        <div class="row g-3">
                            <div class="col-12 text-end mb-2">
                                <button class="btn btn-sm btn-info text-white fw-bold shadow-sm" @click="openModal('profile', {area_id: selectedArea.id})">
                                    <i class="bi bi-plus-circle me-1"></i> Nuevo Perfil
                                </button>
                            </div>
                            <template x-for="profile in profiles" :key="profile.id">
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                            <h6 class="fw-bold text-info mb-0" x-text="profile.name"></h6>
                                            <span class="badge bg-info-subtle text-info rounded-pill" x-text="'S/. ' + profile.price"></span>
                                        </div>
                                        <div class="card-body pt-0">
                                            <label class="small fw-bold text-muted mb-2 text-uppercase" style="font-size: 0.7rem;">Incluir Exámenes:</label>
                                            <div class="bg-light rounded p-2 border overflow-auto" style="max-height: 180px;">
                                                <template x-for="exam in catalogs" :key="exam.id">
                                                    <div class="form-check small mb-1">
                                                        <input class="form-check-input" type="checkbox" 
                                                            :id="'p'+profile.id+'e'+exam.id"
                                                            :checked="profile.catalogs.some(c => c.id === exam.id)"
                                                            @change="toggleExam(profile.id, exam.id)">
                                                        <label class="form-check-label" :for="'p'+profile.id+'e'+exam.id" x-text="exam.name"></label>
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="mt-2 text-end">
                                                <button class="btn btn-link btn-sm text-warning p-0" @click="openModal('profile', profile)">
                                                    <i class="bi bi-pencil-square"></i> Editar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="!selectedArea.id">
                <div class="h-100 d-flex flex-column align-items-center justify-content-center text-center py-5">
                    <i class="bi bi-layout-sidebar-inset text-light" style="font-size: 5rem;"></i>
                    <h4 class="mt-4 text-muted fw-bold">Panel de Configuración</h4>
                    <p class="text-muted">Seleccione un área médica para comenzar.</p>
                </div>
            </template>
        </div>
    </div>

    @include('labs.areas.partials.modal_form')
</div>

<style>
    :root { --azul-clinico: #2d406b; --cian-clinico: #00acc1; }
    .active-area { background-color: var(--azul-clinico) !important; color: white !important; }
    .nav-pills .nav-link.active { background-color: var(--cian-clinico) !important; }
    .btn-primary-custom { background-color: var(--azul-clinico); color: white; }
    .toast { min-width: 250px; }
</style>

<script>
function labManager() {
    return {
        selectedArea: { id: null, name: '' },
        activeTab: 'catalog', activeType: 'area', editMode: false,
        modalTitle: '', formUrl: '', formData: {},
        catalogs: [], profiles: [],

        async loadArea(id, name) {
            this.selectedArea = { id, name };
            const res = await fetch(`/api/areas/${id}/details`);
            const data = await res.json();
            this.catalogs = data.catalogs;
            this.profiles = data.profiles;
        },

        async toggleExam(profileId, catalogId) {
            try {
                const res = await fetch(`/profiles/${profileId}/sync`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ catalog_id: catalogId })
                });
                if (res.ok) this.notify('Asociación actualizada', 'info');
            } catch (e) { this.notify('Error al sincronizar', 'danger'); }
        },

        openModal(type, data = null) {
            this.activeType = type;
            this.editMode = !!(data && data.id);
            this.formData = data ? { ...data } : { area_id: this.selectedArea.id };
            const titles = { area: 'Área', catalog: 'Examen', profile: 'Perfil' };
            const routes = { area: '/areas', catalog: '/catalogs', profile: '/profiles' };
            this.modalTitle = (this.editMode ? 'Editar ' : 'Nuevo ') + titles[type];
            this.formUrl = routes[type] + (this.editMode ? '/' + data.id : '');
            new bootstrap.Modal(document.getElementById('modalForm')).show();
        },

        async submitForm(e) {
            const form = e.target;
            const data = new FormData(form);
            try {
                const res = await fetch(this.formUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
                    body: data
                });
                if (res.ok) {
                    bootstrap.Modal.getInstance(document.getElementById('modalForm')).hide();
                    this.notify('Guardado exitosamente', 'success');
                    if (this.activeType === 'area') location.reload(); // Recarga sidebar
                    else this.loadArea(this.selectedArea.id, this.selectedArea.name);
                }
            } catch (e) { this.notify('Error al procesar', 'danger'); }
        },

        notify(msg, type) {
            const id = Date.now();
            const toast = `<div id="${id}" class="toast show align-items-center text-white bg-${type} border-0 mb-2">
                <div class="d-flex"><div class="toast-body small">${msg}</div></div></div>`;
            document.getElementById('toast-container').insertAdjacentHTML('beforeend', toast);
            setTimeout(() => document.getElementById(id)?.remove(), 2500);
        }
    }
}
</script>
@endsection