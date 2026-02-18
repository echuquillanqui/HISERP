@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold" style="color: var(--azul-clinico)">
                        <i class="bi bi-pencil-square me-2"></i>Editar Paciente: {{ $patient->first_name }} {{ $patient->last_name }}
                    </h3>
                    <p class="text-muted">Actualice la información del paciente según sea necesario.</p>
                </div>
                <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Volver al listado
                </a>
            </div>

            <form action="{{ route('patients.update', $patient) }}" method="POST" x-data="patientEditForm('{{ $patient->birth_date }}')">
                @csrf
                @method('PUT')
                
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 fw-bold text-primary small text-uppercase">
                                    <i class="bi bi-person-badge me-2"></i>Información Personal
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label small fw-bold">DNI / Pasaporte</label>
                                        <input type="text" name="dni" class="form-control @error('dni') is-invalid @enderror" value="{{ old('dni', $patient->dni) }}" required>
                                        @error('dni') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label small fw-bold">Nombres</label>
                                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $patient->first_name) }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label small fw-bold">Apellidos</label>
                                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $patient->last_name) }}" required>
                                    </div>

                                    <div class="col-md-5 mb-3">
                                        <label class="form-label small fw-bold">Fecha de Nacimiento</label>
                                        <input type="date" name="birth_date" x-model="birthDate" class="form-control" @change="updateAge()">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label small fw-bold">Edad Actual</label>
                                        <input type="text" class="form-control bg-light" x-model="age" readonly>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label small fw-bold">Género</label>
                                        <select name="gender" class="form-select">
                                            <option value="M" {{ $patient->gender == 'M' ? 'selected' : '' }}>Masculino</option>
                                            <option value="F" {{ $patient->gender == 'F' ? 'selected' : '' }}>Femenino</option>
                                            <option value="Otro" {{ $patient->gender == 'Otro' ? 'selected' : '' }}>Otro</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 fw-bold text-info small text-uppercase">
                                    <i class="bi bi-telephone me-2"></i>Datos de Contacto
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Teléfono</label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $patient->phone) }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $patient->email) }}">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small fw-bold">Dirección</label>
                                    <textarea name="address" class="form-control" rows="3">{{ old('address', $patient->address) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 mb-5 text-end">
                    <button type="submit" class="btn btn-primary-custom px-5 py-2 shadow">
                        <i class="bi bi-save me-2"></i>Actualizar Información
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function patientEditForm(initialBirthDate) {
    return {
        birthDate: initialBirthDate,
        age: '',
        init() { this.updateAge(); },
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