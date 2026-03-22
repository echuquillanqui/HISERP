@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4" x-data="templateVisualBuilder(@js(old('html_content', $template->html_content)), @js(old('fields_schema') ? json_decode(old('fields_schema'), true) : ($template->fields_schema ?? [])))" x-init="init()">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary"><i class="bi bi-pencil-square"></i> Editar Plantilla</h3>
        <a href="{{ route('templates.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al listado
        </a>
    </div>

    <form action="{{ route('templates.update', $template->id) }}" method="POST" @submit="syncHtml()">
        @csrf
        @method('PUT')
        <input type="hidden" name="html_content" x-ref="htmlContent">
        <input type="hidden" name="fields_schema" x-ref="fieldsSchema">

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 mb-3">
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
                                <input type="text" name="nombre_plantilla" class="form-control" value="{{ old('nombre_plantilla', $template->nombre_plantilla) }}" required>
                            </div>
                        </div>

                        <div class="mb-2 d-flex justify-content-between align-items-center">
                            <label class="fw-bold mb-0">Campos del formulario (sin JSON)</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" @click="addField()">+ Agregar campo</button>
                        </div>
                        <div class="table-responsive border rounded">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nombre del campo</th>
                                        <th>Tipo</th>
                                        <th class="text-center">Oblig.</th>
                                        <th>Opciones (solo lista)</th>
                                        <th class="text-center">Token</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-if="fields.length === 0">
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-3">No hay campos aún. Agrega uno para que aparezca en el formulario de llenado.</td>
                                        </tr>
                                    </template>
                                    <template x-for="(field, index) in fields" :key="index">
                                        <tr>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" x-model="field.label" @input="syncFieldIdentity(field)" placeholder="Ej: Observaciones">
                                                <small class="text-muted">Clave: <code x-text="field.key"></code></small>
                                            </td>
                                            <td>
                                                <select class="form-select form-select-sm" x-model="field.type" @change="syncFieldsSchema()">
                                                    <template x-for="option in fieldTypeOptions" :key="option.value">
                                                        <option :value="option.value" x-text="option.label"></option>
                                                    </template>
                                                </select>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input" x-model="field.required" @change="syncFieldsSchema()">
                                            </td>
                                            <td>
                                                <textarea x-show="field.type === 'select'" class="form-control form-control-sm" rows="2" x-model="field.optionsText" @input="syncFieldsSchema()" placeholder="Una opción por línea"></textarea>
                                                <small x-show="field.type !== 'select'" class="text-muted">No aplica</small>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="insertFieldToken(field)">Insertar</button>
                                            </td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-outline-danger btn-sm" @click="removeField(index)">Quitar</button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold">Vista previa (HTML generado automáticamente)</div>
                    <div class="card-body">
                        <div class="template-preview-sheet" x-ref="preview"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-primary text-white fw-bold">Constructor visual</div>
                    <div class="card-body">
                        <div class="d-grid gap-2 mb-3">
                            <button type="button" class="btn btn-outline-primary btn-sm" @click="addBlock('heading')">+ Título</button>
                            <button type="button" class="btn btn-outline-primary btn-sm" @click="addBlock('paragraph')">+ Párrafo</button>
                            <button type="button" class="btn btn-outline-primary btn-sm" @click="addBlock('token')">+ Variable</button>
                            <button type="button" class="btn btn-outline-primary btn-sm" @click="addBlock('conditional')">+ Bloque condicional</button>
                            <button type="button" class="btn btn-outline-primary btn-sm" @click="addBlock('divider')">+ Separador</button>
                            <button type="button" class="btn btn-outline-primary btn-sm" @click="addBlock('spacer')">+ Espacio</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" @click="addBlock('html')">+ HTML libre</button>
                        </div>

                        <template x-for="(block, index) in blocks" :key="index">
                            <div class="border rounded p-2 mb-2 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong x-text="`Bloque #${index + 1} (${block.type})`"></strong>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary" @click="moveUp(index)">↑</button>
                                        <button type="button" class="btn btn-outline-secondary" @click="moveDown(index)">↓</button>
                                        <button type="button" class="btn btn-outline-danger" @click="removeBlock(index)">×</button>
                                    </div>
                                </div>

                                <template x-if="block.type === 'heading'">
                                    <div>
                                        <select class="form-select form-select-sm mb-2" x-model="block.level" @change="syncHtml()">
                                            <option value="h1">H1</option>
                                            <option value="h2">H2</option>
                                            <option value="h3">H3</option>
                                            <option value="h4">H4</option>
                                        </select>
                                        <input class="form-control form-control-sm" x-model="block.text" @input="syncHtml()">
                                    </div>
                                </template>

                                <template x-if="block.type === 'paragraph'">
                                    <textarea class="form-control form-control-sm" rows="3" x-model="block.text" @input="syncHtml()"></textarea>
                                </template>

                                <template x-if="block.type === 'token'">
                                    <select class="form-select form-select-sm" x-model="block.token" @change="syncHtml()">
                                        <template x-for="token in allTokenOptions" :key="token">
                                            <option :value="token" x-text="token"></option>
                                        </template>
                                    </select>
                                </template>

                                <template x-if="block.type === 'conditional'">
                                    <div>
                                        <select class="form-select form-select-sm mb-2" x-model="block.gender" @change="syncHtml()">
                                            <option value="hombre">Solo hombre</option>
                                            <option value="mujer">Solo mujer</option>
                                        </select>
                                        <textarea class="form-control form-control-sm" rows="4" x-model="block.content" @input="syncHtml()"></textarea>
                                    </div>
                                </template>

                                <template x-if="block.type === 'spacer'">
                                    <input type="number" class="form-control form-control-sm" min="0" max="300" x-model="block.height" @input="syncHtml()">
                                </template>

                                <template x-if="block.type === 'html'">
                                    <textarea class="form-control form-control-sm font-monospace" rows="4" x-model="block.html" @input="syncHtml()"></textarea>
                                </template>
                            </div>
                        </template>
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

@include('templates.partials.visual-builder')
@endsection
