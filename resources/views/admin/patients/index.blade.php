@extends('layouts.app')

@section('content')
<div class="container" x-data="patientManager()">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h3 class="fw-bold" style="color: var(--azul-clinico)">
                <i class="bi bi-people-fill me-2"></i>Gestión de Pacientes
            </h3>
            <p class="text-muted">Administra la base de datos de pacientes y su información de contacto.</p>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="{{ route('patients.create') }}" class="btn btn-primary-custom shadow-sm px-4">
                <i class="bi bi-person-plus-fill me-2"></i>Nuevo Paciente
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
                    <input type="text" class="form-control border-start-0 ps-0" 
                           placeholder="Buscar por nombre, DNI o apellido..." 
                           x-model="search" @input.debounce.300ms="fetchPatients()">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small fw-bold text-uppercase">
                        <tr>
                            <th class="ps-4 py-3">Paciente</th>
                            <th>DNI</th>
                            <th>Género / Edad</th>
                            <th>Contacto / Dirección</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="patient in patients" :key="patient.id">
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3" 
                                             :style="patient.gender === 'F' ? 'background-color: #ec4899' : 'background-color: var(--cian-clinico)'"
                                             x-text="patient.first_name.charAt(0)">
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark" x-text="patient.first_name + ' ' + patient.last_name"></div>
                                            <div class="small text-muted" x-text="patient.email || 'Sin correo'"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="fw-bold" x-text="patient.dni"></td>
                                <td>
                                    <span class="badge rounded-pill mb-1" 
                                          :class="patient.gender === 'M' ? 'bg-primary-subtle text-primary' : (patient.gender === 'F' ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary')"
                                          x-text="patient.gender === 'M' ? 'Masculino' : (patient.gender === 'F' ? 'Femenino' : 'Otro')">
                                    </span>
                                    <div class="small text-muted fw-bold" x-text="calculateAge(patient.birth_date) + ' años'"></div>
                                </td>
                                <td>
                                    <div class="small"><i class="bi bi-telephone me-1"></i> <span x-text="patient.phone || '---'"></span></div>
                                    <div class="small text-muted text-truncate" style="max-width: 150px" x-text="patient.address || '---'"></div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-sm">
                                        <a :href="'/patients/' + patient.id + '/edit'" class="btn btn-sm btn-white border">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </a>
                                        <button @click="confirmDelete(patient)" class="btn btn-sm btn-white border text-danger">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="patients.length === 0">
                            <td colspan="5" class="text-center py-5 text-muted">No se encontraron pacientes.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function patientManager() {
    return {
        patients: [],
        search: '',
        init() { this.fetchPatients(); },
        fetchPatients() {
            fetch(`{{ route('patients.index') }}?search=${this.search}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => this.patients = data);
        },
        calculateAge(birthday) {
            if (!birthday) return '---';
            const birthDate = new Date(birthday);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) { age--; }
            return age;
        },
        confirmDelete(patient) {
            Swal.fire({
                title: '¿Eliminar Paciente?',
                text: `Se borrará a ${patient.first_name} permanentemente.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/patients/${patient.id}`;
                    form.innerHTML = `@csrf @method('DELETE')`;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    }
}
</script>
@endsection