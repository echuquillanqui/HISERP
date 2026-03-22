@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary"><i class="bi bi-pencil-square"></i> Editar Plantilla</h3>
        <a href="{{ route('templates.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al listado
        </a>
    </div>

    <form action="{{ route('templates.update', $template->id) }}" method="POST">
        @csrf
        @method('PUT') 
        
        <div class="row">
            <div class="col-md-9">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="fw-bold mb-1">Servicio Asignado</label>
                                <select name="service_id" class="form-select" required>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}" {{ $template->service_id == $service->id ? 'selected' : '' }}>
                                            {{ $service->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold mb-1">Nombre de la Plantilla</label>
                                <input type="text" name="nombre_plantilla" class="form-control" value="{{ $template->nombre_plantilla }}" required>
                            </div>
                        </div>
                        
                        <label class="fw-bold mb-2">Contenido de la Plantilla</label>
                        <textarea id="reportEditor" name="html_content">{!! $template->html_content !!}</textarea>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                    <div class="card-header bg-primary text-white fw-bold border-0">Variables</div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-dark btn-sm text-start" onclick="insertVar('@{{nombre_paciente}}')">@{{nombre_paciente}}</button>
                            <button type="button" class="btn btn-outline-dark btn-sm text-start" onclick="insertVar('@{{dni_paciente}}')">@{{dni_paciente}}</button>
                            <button type="button" class="btn btn-outline-dark btn-sm text-start" onclick="insertVar('@{{edad_paciente}}')">@{{edad_paciente}}</button>
                            <button type="button" class="btn btn-outline-dark btn-sm text-start" onclick="insertVar('@{{sexo_paciente}}')">@{{sexo_paciente}}</button>
                            <button type="button" class="btn btn-outline-dark btn-sm text-start" onclick="insertVar('@{{fecha_actual}}')">@{{fecha_actual}}</button>
                            <button type="button" class="btn btn-outline-dark btn-sm text-start" onclick="insertVar('@{{codigo_orden}}')">@{{codigo_orden}}</button>
                            <button type="button" class="btn btn-outline-dark btn-sm text-start" onclick="insertVar('@{{regimen_aseguramiento}}')">@{{regimen_aseguramiento}}</button>
                            <button type="button" class="btn btn-outline-dark btn-sm text-start" onclick="insertVar('@{{codigo_afiliacion}}')">@{{codigo_afiliacion}}</button>
                            <button type="button" class="btn btn-outline-dark btn-sm text-start" 
                                    onclick="insertVar('@{{firma_medico}}')">
                                @{{firma_medico}}
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm text-start" onclick="insertVar('@{{#if_hombre}}')">@{{#if_hombre}}</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm text-start" onclick="insertVar('@{{/if_hombre}}')">@{{/if_hombre}}</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm text-start" onclick="insertVar('@{{#if_mujer}}')">@{{#if_mujer}}</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm text-start" onclick="insertVar('@{{/if_mujer}}')">@{{/if_mujer}}</button>
                        </div>
                        <hr>
                        <p class="small mb-0 text-muted">
                            Usa bloques condicionales por sexo. Ejemplo:
                            <code>@{{#if_hombre}}Sección próstata@{{/if_hombre}}</code>.
                        </p>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <button type="submit" class="btn btn-warning w-100 py-2">
                            <i class="bi bi-save"></i> Actualizar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@include('templates.partials.ckeditor-config')
@endsection
