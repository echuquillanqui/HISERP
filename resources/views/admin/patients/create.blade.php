@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold" style="color: var(--azul-clinico)">
                        <i class="bi bi-person-plus-fill me-2"></i>Registrar Nuevo Paciente
                    </h3>
                    <p class="text-muted">Complete la ficha técnica del paciente para su historia clínica.</p>
                </div>
                <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Volver al listado
                </a>
            </div>

            <form action="{{ route('patients.store') }}" method="POST" x-data="patientForm()">
                @csrf
                <div class="row g-4">
                    <div class="col-lg-9">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 fw-bold text-primary small text-uppercase">
                                    <i class="bi bi-person-lines-fill me-2"></i>Información Personal
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label small fw-bold">DNI / Pasaporte *</label>
                                        <input type="text" name="dni" class="form-control @error('dni') is-invalid @enderror" value="{{ old('dni') }}" placeholder="Documento de identidad" required>
                                        @error('dni') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label small fw-bold">Nombres *</label>
                                        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
                                        @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label small fw-bold">Apellidos *</label>
                                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" required>
                                        @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-5 mb-3">
                                        <label class="form-label small fw-bold">Fecha de Nacimiento</label>
                                        <input type="date" name="birth_date" x-model="birthDate" class="form-control" @change="updateAge()">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label small fw-bold">Edad Calculada</label>
                                        <input type="text" class="form-control bg-light" x-model="age" readonly placeholder="---">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label small fw-bold">Género</label>
                                        <select name="gender" class="form-select">
                                            <option value="">Seleccione...</option>
                                            <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Masculino</option>
                                            <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Femenino</option>
                                            <option value="Otro" {{ old('gender') == 'Otro' ? 'selected' : '' }}>Otro</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 fw-bold text-info small text-uppercase">
                                    <i class="bi bi-telephone-fill me-2"></i>Contacto
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Teléfono / Celular</label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="999 999 999">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Correo Electrónico</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="ejemplo@correo.com">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small fw-bold">Dirección</label>
                                    <textarea name="address" class="form-control" rows="3" placeholder="Dirección domiciliaria">{{ old('address') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-2 mb-5 d-flex justify-content-end gap-3">
                    <button type="submit" class="btn btn-primary-custom px-5 py-2 shadow">
                        <i class="bi bi-check-circle me-2"></i>Finalizar Registro
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function patientForm() {
    return {
        birthDate: '',
        age: '',
        updateAge() {
            if (!this.birthDate) return;
            const birth = new Date(this.birthDate);
            const now = new Date();
            let calculatedAge = now.getFullYear() - birth.getFullYear();
            const m = now.getMonth() - birth.getMonth();
            if (m < 0 || (m === 0 && now.getDate() < birth.getDate())) {
                calculatedAge--;
            }
            this.age = calculatedAge + ' años';
        }
    }
}
</script>
@endsection