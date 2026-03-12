@extends('layouts.app')

@section('content')
<div class="container" x-data="serviceManager()">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold" style="color: var(--azul-clinico)">Gestión de Servicios</h5>
            <button class="btn btn-primary-custom" @click="openModal('create')">
                <i class="bi bi-plus-lg"></i> Nuevo Servicio
            </button>
        </div>
        
        <div class="card-body">
            <input type="text" class="form-control mb-4" placeholder="Buscar..." x-model="search" @input.debounce.300ms="fetchServices()">
            <table class="table table-hover">
                <thead><tr><th>Nombre</th><th>Precio</th><th class="text-end">Acciones</th></tr></thead>
                <tbody>
                    <template x-for="item in services" :key="item.id">
                        <tr>
                            <td x-text="item.nombre"></td>
                            <td x-text="'S/ ' + parseFloat(item.precio).toFixed(2)"></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-info" @click="openModal('edit', item)">Editar</button>
                                <button class="btn btn-sm btn-outline-danger" @click="deleteService(item.id)">Eliminar</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="serviceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" x-text="editMode ? 'Editar Servicio' : 'Nuevo Servicio'"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nombre</label>
                        <input type="text" class="form-control" x-model="formData.nombre" placeholder="Ej: Consulta Médica" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Precio</label>
                        <input type="number" step="0.01" class="form-control" x-model="formData.precio" placeholder="0.00" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary-custom" @click="saveService()">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function serviceManager() {
    return {
        services: [], search: '', editMode: false, formData: {nombre: '', precio: ''}, modal: null,
        init() { this.modal = new bootstrap.Modal(document.getElementById('serviceModal')); this.fetchServices(); },
        async fetchServices() { 
            let res = await fetch(`{{ route('services.index') }}?search=${this.search}`, { headers: {'X-Requested-With': 'XMLHttpRequest'} });
            this.services = await res.json();
        },
        openModal(mode, item = null) {
            this.editMode = mode === 'edit';
            this.formData = this.editMode ? {...item} : {nombre: '', precio: ''};
            this.modal.show();
        },
        async saveService() {
            // Validación básica en front
            if (!this.formData.nombre || !this.formData.precio) {
                return Swal.fire({icon: 'warning', title: 'Campos requeridos', text: 'Por favor, rellene todos los campos.'});
            }
            
            let url = this.editMode ? `/services/${this.formData.id}` : '/services';
            let res = await fetch(url, {
                method: this.editMode ? 'PUT' : 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify(this.formData)
            });

            if (res.ok) {
                this.modal.hide();
                this.fetchServices();
                Swal.fire({icon: 'success', title: 'Guardado', timer: 1500});
            } else {
                Swal.fire({icon: 'error', title: 'Error', text: 'Error al procesar los datos.'});
            }
        },
        async deleteService(id) {
            let confirm = await Swal.fire({title: '¿Eliminar?', icon: 'warning', showCancelButton: true});
            if (confirm.isConfirmed) {
                let res = await fetch(`/services/${id}`, { method: 'DELETE', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'} });
                let data = await res.json();
                if (res.ok) { this.fetchServices(); Swal.fire({icon: 'success', title: 'Eliminado', timer: 1500}); }
                else { Swal.fire({icon: 'error', title: 'Error', text: data.message}); }
            }
        }
    }
}
</script>
@endsection