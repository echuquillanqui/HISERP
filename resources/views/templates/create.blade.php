@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary"><i class="bi bi-file-earmark-medical"></i> Crear Nueva Plantilla</h3>
        <a href="{{ route('templates.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al listado
        </a>
    </div>

    <form action="{{ route('templates.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-9">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="fw-bold mb-1">Servicio Asignado</label>
                                <select name="service_id" class="form-select" required>
                                    <option value="">Seleccione un servicio...</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}">{{ $service->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold mb-1">Nombre de la Plantilla</label>
                                <input type="text" name="nombre_plantilla" class="form-control" placeholder="Ej: Reporte de Consulta" required>
                            </div>
                        </div>
                        
                        <label class="fw-bold mb-2">Contenido de la Plantilla</label>
                        <textarea id="tinyEditor" name="html_content"></textarea>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                    <div class="card-header bg-primary text-white fw-bold border-0">
                        <i class="bi bi-code-square"></i> Variables Disponibles
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">Haz clic para insertar en el editor:</p>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-dark btn-sm text-start" onclick="insertVar('@{{nombre_paciente}}')">@{{nombre_paciente}}</button>
                            <button type="button" class="btn btn-outline-dark btn-sm text-start" onclick="insertVar('@{{dni_paciente}}')">@{{dni_paciente}}</button>
                            <button type="button" class="btn btn-outline-dark btn-sm text-start" onclick="insertVar('@{{fecha_actual}}')">@{{fecha_actual}}</button>
                            <button type="button" class="btn btn-outline-dark btn-sm text-start" onclick="insertVar('@{{codigo_orden}}')">@{{codigo_orden}}</button>
                            <button type="button" class="btn btn-outline-dark btn-sm text-start" 
                                    onclick="insertVar('@{{firma_medico}}')">
                                @{{firma_medico}}
                            </button>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <button type="submit" class="btn btn-success w-100 py-2">
                            <i class="bi bi-save"></i> Guardar Plantilla
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>

<script>
    // Inicialización del editor
    tinymce.init({
        selector: '#tinyEditor',
        license_key: 'gpl',
        height: 500,
        plugins: 'advlist autolink lists link charmap preview searchreplace visualblocks code fullscreen table',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist | table | code',
        menubar: false
    });

    // Función para insertar las variables
    function insertVar(variable) {
        if (tinymce.get('tinyEditor')) {
            tinymce.get('tinyEditor').insertContent(variable + ' ');
        }
    }
</script>
@endsection