@extends('layouts.app')

@section('content')
<div class="container" x-data="userManager()">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h3 class="fw-bold" style="color: var(--azul-clinico)">
                <i class="bi bi-people-fill me-2"></i>Gestión de Usuarios
            </h3>
            <p class="text-muted">Administra el personal médico, técnico y administrativo.</p>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="{{ route('users.create') }}" class="btn btn-primary-custom shadow-sm px-4">
                <i class="bi bi-person-plus-fill me-2"></i>Nuevo Usuario
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="p-4 border-bottom bg-light-subtle">
                <div class="input-group input-group-lg shadow-sm">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search" style="color: var(--cian-clinico)"></i>
                    </span>
                    <input type="text" 
                           class="form-control border-start-0 ps-0" 
                           placeholder="Buscar por nombre, DNI, usuario o especialidad..." 
                           x-model="search" 
                           @input.debounce.300ms="fetchUsers()">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small fw-bold text-uppercase">
                        <tr>
                            <th class="ps-4 py-3">Personal</th>
                            <th>DNI / Usuario</th>
                            <th>Rol / Especialidad</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="user in users" :key="user.id">
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3" 
                                             :style="user.status ? 'background-color: var(--cian-clinico)' : 'background-color: #94a3b8'"
                                             x-text="user.name.charAt(0)">
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark" x-text="user.name"></div>
                                            <div class="small text-muted" x-text="user.email"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div x-text="user.dni || '---'"></div>
                                    <div class="small text-primary fw-bold" x-text="'@' + user.username"></div>
                                </td>
                                <td>
                                    <span class="badge rounded-pill mb-1" 
                                          :class="{
                                              'bg-primary-subtle text-primary': user.role === 'medicina',
                                              'bg-info-subtle text-info': user.role === 'laboratorio',
                                              'bg-secondary-subtle text-secondary': user.role === 'administracion',
                                              'bg-dark-subtle text-dark': user.role === 'superadmin'
                                          }" 
                                          x-text="user.role">
                                    </span>
                                    <div class="small text-muted" x-text="user.specialty ? user.specialty.name : 'General'"></div>
                                </td>
                                <td class="text-center">
                                    <template x-if="user.status">
                                        <span class="badge bg-success-subtle text-success border border-success px-3">
                                            <i class="bi bi-check-circle-fill me-1"></i> ACTIVO
                                        </span>
                                    </template>

                                    <template x-if="!user.status">
                                        <span class="badge bg-danger-subtle text-danger border border-danger px-3">
                                            <i class="bi bi-x-circle-fill me-1"></i> BLOQUEADO
                                        </span>
                                    </template>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-sm">
                                        <a :href="'/users/' + user.id + '/edit'" class="btn btn-sm btn-white border" title="Editar">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </a>
                                        <button @click="confirmDelete(user)" class="btn btn-sm btn-white border text-danger" title="Eliminar">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <tr x-show="users.length === 0">
                            <td colspan="5" class="text-center py-5">
                                <div class="py-4">
                                    <i class="bi bi-person-exclamation display-1 text-light"></i>
                                    <h4 class="mt-3 fw-bold text-secondary">NO HAY DATOS QUE MOSTRAR</h4>
                                    <p class="text-muted">Intenta con otro término de búsqueda o registra un nuevo usuario.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        text-transform: uppercase;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .btn-white {
        background-color: white;
    }
    .btn-white:hover {
        background-color: #f8f9fa;
    }
    .transition-all {
        transition: all 0.3s ease;
    }
    .transition-all:hover {
        transform: scale(1.2);
    }
</style>

<script>
function userManager() {
    return {
        users: [],
        search: '',
        
        init() {
            this.fetchUsers();
        },

        fetchUsers() {
            fetch(`{{ route('users.index') }}?search=${this.search}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => {
                if (!res.ok) throw new Error('Error en red');
                return res.json();
            })
            .then(data => {
                this.users = data;
            })
            .catch(err => {
                console.error("Error al cargar:", err);
                this.users = []; // Evita que quede undefined
            });
        },

        confirmDelete(user) {
            const alertSound = new Audio('https://assets.mixkit.co/active_storage/sfx/2359/2359-preview.mp3');
            alertSound.play();

            Swal.fire({
                title: '¿Eliminar Usuario?',
                text: `Esta acción eliminará permanentemente a ${user.name}.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#2d406b',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Aquí podrías enviar un fetch con método DELETE
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/users/${user.id}`;
                    form.innerHTML = `
                        @csrf
                        @method('DELETE')
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    }
}
</script>
@endsection