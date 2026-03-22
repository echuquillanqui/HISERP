@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4" x-data="templateVisualBuilder(@js(old('html_content', $template->html_content)))" x-init="init()">
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

                        <div class="mb-3">
                            <label class="fw-bold mb-1">Campos dinámicos (JSON opcional)</label>
                            <textarea name="fields_schema" rows="7" class="form-control font-monospace" placeholder='[{"key":"glucosa","label":"Glucosa","type":"number","required":true}]'>{{ old('fields_schema', json_encode($template->fields_schema ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
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
                                        <template x-for="token in tokenOptions" :key="token">
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
