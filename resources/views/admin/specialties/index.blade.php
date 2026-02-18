@extends('layouts.app')

@section('content')
<div class="container" x-data="specialtyManager()">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold" style="color: var(--azul-clinico)">Gestión de Especialidades</h5>
            <button class="btn btn-primary-custom" @click="openModal('create')">
                <i class="bi bi-plus-lg"></i> Nueva Especialidad
            </button>
        </div>
        
        <div class="card-body">
            <div class="mb-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control bg-light border-start-0" 
                           placeholder="Buscar especialidad..." 
                           x-model="search" @input.debounce.300ms="fetchSpecialties()">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>N°</th>
                            <th>Nombre de la Especialidad</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="item in specialties" :key="item.id">
                            <tr>
                                <td x-text="item.id"></td>
                                <td class="fw-bold text-secondary" x-text="item.name"></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-info me-2" @click="openModal('edit', item)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" @click="deleteSpecialty(item.id)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="specialtyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" x-text="editMode ? 'Editar Especialidad' : 'Nueva Especialidad'"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nombre</label>
                        <input type="text" class="form-control" x-model="formData.name">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary-custom" @click="saveSpecialty()">
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function specialtyManager() {
    return {
        specialties: [],
        search: '',
        editMode: false,
        formData: { id: null, name: '' },
        modal: null,

        init() {
            const modalElement = document.getElementById('specialtyModal');
            this.modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            this.fetchSpecialties();
        },

        fetchSpecialties() {
            fetch(`{{ route('specialties.index') }}?search=${this.search}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => this.specialties = data);
        },

        openModal(mode, item = null) {
            this.editMode = mode === 'edit';
            if (this.editMode) {
                this.formData = { ...item };
            } else {
                this.formData = { id: null, name: '' };
            }
            this.modal.show();
        },

        async saveSpecialty() {
            let url = this.editMode ? `/specialties/${this.formData.id}` : '/specialties';
            let method = this.editMode ? 'PUT' : 'POST';

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(this.formData)
            });

            if (response.ok) {
                this.modal.hide();
                this.fetchSpecialties();
                // Aquí se dispara tu SweetAlert con sonido que configuramos en el layout
                Swal.fire({ icon: 'success', title: '¡Éxito!', showConfirmButton: false, timer: 1500 });
            }
        },

        deleteSpecialty(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    await fetch(`/specialties/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    });
                    this.fetchSpecialties();
                }
            });
        }
    }
}
</script>
@endsection