@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold" style="color: var(--azul-clinico)">
                        <i class="bi bi-person-plus-fill me-2"></i>Registrar Nuevo Colaborador
                    </h3>
                    <p class="text-muted">Complete todos los campos para dar de alta al personal en el sistema.</p>
                </div>
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Volver al listado
                </a>
            </div>

            <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" x-data="firmaPreview()">
                @csrf
                
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 fw-bold text-primary small text-uppercase">
                                    <i class="bi bi-person-lines-fill me-2"></i>Información Personal y de Acceso
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label small fw-bold">Nombre Completo</label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Ej. Juan Pérez García" required>
                                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label small fw-bold">DNI (8 dígitos)</label>
                                        <input type="text" name="dni" class="form-control @error('dni') is-invalid @enderror" value="{{ old('dni') }}" maxlength="8" placeholder="70000000">
                                        @error('dni') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold">Correo Electrónico</label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="usuario@clinica.com" required>
                                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold">Nombre de Usuario (Username)</label>
                                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" placeholder="jperez" required>
                                        @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6 mb-0">
                                        <label class="form-label small fw-bold">Contraseña de Acceso</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required placeholder="Mínimo 8 caracteres">
                                        </div>
                                        @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 mb-0">
                                        <label class="form-label small fw-bold">Rol en el Sistema</label>
                                        <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                            <option value="administracion" selected>Administración</option>
                                            <option value="medicina">Medicina / Especialista</option>
                                            <option value="laboratorio">Personal de Laboratorio</option>
                                            <option value="superadmin">Super Administrador</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 fw-bold text-info small text-uppercase">
                                    <i class="bi bi-clipboard2-pulse me-2"></i>Datos Médicos y Colegiatura
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold">Especialidad</label>
                                        <select name="specialty_id" class="form-select shadow-sm">
                                            <option value="">Seleccione una especialidad...</option>
                                            @foreach($specialties as $specialty)
                                                <option value="{{ $specialty->id }}" {{ old('specialty_id') == $specialty->id ? 'selected' : '' }}>
                                                    {{ $specialty->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label small fw-bold">N° Colegiatura</label>
                                        <input type="text" name="colegiatura" class="form-control" value="{{ old('colegiatura') }}" placeholder="CMP-XXXXX">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label small fw-bold">RNE</label>
                                        <input type="text" name="rne" class="form-control" value="{{ old('rne') }}" placeholder="RNE-XXXXX">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 fw-bold text-dark small text-uppercase">
                                    <i class="bi bi-pen me-2"></i>Firma Digital
                                </h5>
                            </div>
                            <div class="card-body p-4 text-center">
                                <p class="text-muted small mb-4">Esta firma aparecerá en los informes y resultados emitidos por el usuario.</p>
                                
                                <div class="firma-preview-container border rounded mb-3 d-flex align-items-center justify-content-center bg-light" style="height: 180px; position: relative; overflow: hidden;">
                                    <template x-if="imageUrl">
                                        <img :src="imageUrl" class="img-fluid" style="max-height: 100%; object-fit: contain;">
                                    </template>
                                    <template x-if="!imageUrl">
                                        <div class="text-muted">
                                            <i class="bi bi-cloud-arrow-up display-4"></i>
                                            <p class="small">Vista previa de firma</p>
                                        </div>
                                    </template>
                                </div>

                                <div class="mb-3">
                                    <input type="file" name="firma" id="firmaInput" class="d-none" accept="image/*" @change="fileChosen">
                                    <button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="document.getElementById('firmaInput').click()">
                                        <i class="bi bi-image me-1"></i> Seleccionar Imagen
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-2">Formatos: PNG, JPG (fondo blanco).<br>Máx: 1MB</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 mb-5 d-flex justify-content-end gap-3">
                    <button type="reset" class="btn btn-light px-4 fw-bold text-secondary">Limpiar Campos</button>
                    <button type="submit" class="btn btn-primary-custom px-5 py-2 shadow">
                        <i class="bi bi-check-circle me-2"></i>Finalizar Registro
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function firmaPreview() {
    return {
        imageUrl: null,
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
</script>

<style>
    /* Estilos específicos para armonizar con el logo */
    .form-control:focus, .form-select:focus {
        border-color: var(--cian-clinico);
        box-shadow: 0 0 0 0.25rem rgba(0, 172, 193, 0.15);
    }
    .btn-primary-custom {
        background-color: var(--azul-clinico);
        border: none;
        color: white;
        transition: all 0.3s;
    }
    .btn-primary-custom:hover {
        background-color: #1e2b4a;
        transform: translateY(-1px);
    }
</style>
@endsection