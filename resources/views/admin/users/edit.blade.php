@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold" style="color: var(--azul-clinico)">
                        <i class="bi bi-pencil-square me-2"></i>Editar Usuario: {{ $user->name }}
                    </h3>
                    <p class="text-muted">Modifique los campos necesarios. Los campos de contraseña pueden quedar vacíos si no desea cambiarlos.</p>
                </div>
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Volver al listado
                </a>
            </div>

            <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data" x-data="editUserHandler('{{ $user->firma ? asset('storage/'.$user->firma) : '' }}')">
                @csrf
                @method('PUT')
                
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 fw-bold text-primary small text-uppercase">
                                    <i class="bi bi-person-badge me-2"></i>Información Personal y Acceso
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label small fw-bold">Nombre Completo</label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label small fw-bold">DNI</label>
                                        <input type="text" name="dni" class="form-control @error('dni') is-invalid @enderror" value="{{ old('dni', $user->dni) }}" maxlength="8">
                                        @error('dni') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold">Correo Electrónico</label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold">Nombre de Usuario</label>
                                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}" required>
                                        @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold text-danger">Nueva Contraseña (Opcional)</label>
                                        <input type="password" name="password" class="form-control" placeholder="Dejar vacío para no cambiar">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold">Rol en el Sistema</label>
                                        <select name="role" class="form-select" required>
                                            <option value="administracion" {{ $user->role == 'administracion' ? 'selected' : '' }}>Administración</option>
                                            <option value="medicina" {{ $user->role == 'medicina' ? 'selected' : '' }}>Medicina / Especialista</option>
                                            <option value="laboratorio" {{ $user->role == 'laboratorio' ? 'selected' : '' }}>Laboratorio</option>
                                            <option value="superadmin" {{ $user->role == 'superadmin' ? 'selected' : '' }}>Super Administrador</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 fw-bold text-info small text-uppercase">
                                    <i class="bi bi-hospital me-2"></i>Especialidad y Colegiatura
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold">Especialidad</label>
                                        <select name="specialty_id" class="form-select">
                                            <option value="">Sin especialidad asociada</option>
                                            @foreach($specialties as $specialty)
                                                <option value="{{ $specialty->id }}" {{ (old('specialty_id', $user->specialty_id) == $specialty->id) ? 'selected' : '' }}>
                                                    {{ $specialty->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label small fw-bold">N° Colegiatura</label>
                                        <input type="text" name="colegiatura" class="form-control" value="{{ old('colegiatura', $user->colegiatura) }}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label small fw-bold">RNE</label>
                                        <input type="text" name="rne" class="form-control" value="{{ old('rne', $user->rne) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 fw-bold text-dark small text-uppercase">
                                    <i class="bi bi-pen me-2"></i>Firma Digital
                                </h5>
                            </div>
                            <div class="card-body text-center p-4">
                                <div class="firma-container border rounded mb-3 d-flex align-items-center justify-content-center bg-light" style="height: 180px; position: relative;">
                                    <template x-if="imageUrl">
                                        <img :src="imageUrl" class="img-fluid p-2" style="max-height: 100%; object-fit: contain;">
                                    </template>
                                    <template x-if="!imageUrl">
                                        <span class="text-muted small">Sin firma registrada</span>
                                    </template>
                                </div>
                                
                                <input type="file" name="firma" id="editFirma" class="d-none" accept="image/*" @change="fileChosen">
                                <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="document.getElementById('editFirma').click()">
                                    <i class="bi bi-upload me-1"></i> Reemplazar Firma
                                </button>
                                <p class="text-muted mt-2" style="font-size: 0.75rem;">Se recomienda fondo blanco o transparente.</p>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label small fw-bold">Estado del Usuario</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="1" {{ old('status', $user->status) == 1 ? 'selected' : '' }}>🟢 Activo</option>
                                <option value="0" {{ old('status', $user->status) == 0 ? 'selected' : '' }}>🔴 Bloqueado</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-4 mb-5 d-flex justify-content-end gap-3">
                    <button type="submit" class="btn btn-primary-custom px-5 py-2 shadow">
                        <i class="bi bi-save me-2"></i>Actualizar Información
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editUserHandler(initialImage) {
    return {
        imageUrl: initialImage || null,
        userStatus: {{ $user->status ? 'true' : 'false' }},
        fileChosen(event) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = (e) => {
                this.imageUrl = e.target.result;
            };
        }
    }
}

// Escuchar cambios en el switch para cambiar el texto en tiempo real
document.getElementById('statusSwitch').addEventListener('change', function() {
    const alpineData = document.querySelector('[x-data]').__x.$data;
    alpineData.userStatus = this.checked;
});
</script>

<style>
    .custom-switch .form-check-input {
        width: 3rem;
        height: 1.5rem;
        cursor: pointer;
    }
    .custom-switch .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
    }
</style>
@endsection