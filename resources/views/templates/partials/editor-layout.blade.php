<div class="row g-3">
    <div class="col-xl-8">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="fw-bold mb-1">Servicio asignado</label>
                        <select name="service_id" class="form-select" required>
                            <option value="">Seleccione...</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" @selected(old('service_id', $template->service_id ?? null) == $service->id)>{{ $service->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold mb-1">Nombre de la plantilla</label>
                        <input type="text" name="nombre_plantilla" class="form-control" value="{{ old('nombre_plantilla', $template->nombre_plantilla ?? '') }}" required>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="fw-bold mb-0">Campos a completar</label>
                    <button type="button" class="btn btn-sm btn-outline-primary" @click="addField()">+ Agregar campo</button>
                </div>

                <div class="table-responsive border rounded">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Etiqueta</th>
                                <th>Tipo</th>
                                <th class="text-center">Oblig.</th>
                                <th>Opciones</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="fields.length === 0">
                                <tr><td colspan="5" class="text-center py-3 text-muted">Agrega al menos un campo dinámico para autocompletado.</td></tr>
                            </template>
                            <template x-for="(field, index) in fields" :key="index">
                                <tr>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" x-model="field.label" @input="syncFieldIdentity(field)">
                                        <small class="text-muted">Token: <code x-text="buildFieldToken(field.key)"></code></small>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm" x-model="field.type" @change="syncFieldsSchema()">
                                            <template x-for="option in fieldTypeOptions" :key="option.value">
                                                <option :value="option.value" x-text="option.label"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="text-center"><input type="checkbox" class="form-check-input" x-model="field.required" @change="syncFieldsSchema()"></td>
                                    <td>
                                        <textarea x-show="field.type === 'select'" class="form-control form-control-sm" rows="2" x-model="field.optionsText" @input="syncFieldsSchema()" placeholder="Una opción por línea"></textarea>
                                        <small x-show="field.type !== 'select'" class="text-muted">No aplica</small>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-outline-secondary btn-sm mb-1" @click="insertFieldToken(field)">Insertar</button>
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
            <div class="card-header bg-white fw-bold">Vista previa A4</div>
            <div class="card-body"><div class="template-preview-sheet" x-ref="preview"></div></div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white fw-bold">Constructor visual</div>
            <div class="card-body">
                <div class="d-grid gap-2 mb-3">
                    <button type="button" class="btn btn-outline-primary btn-sm" @click="addBlock('heading')">+ Título</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" @click="addBlock('paragraph')">+ Párrafo</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" @click="addBlock('token')">+ Token</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" @click="addBlock('conditional')">+ Condicional</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" @click="addBlock('divider')">+ Línea</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" @click="addBlock('spacer')">+ Espacio</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="addBlock('html')">+ HTML libre</button>
                </div>

                <template x-for="(block, index) in blocks" :key="index">
                    <div class="border rounded p-2 mb-2 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong x-text="`Bloque ${index + 1}: ${block.type}`"></strong>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" @click="moveUp(index)">↑</button>
                                <button type="button" class="btn btn-outline-secondary" @click="moveDown(index)">↓</button>
                                <button type="button" class="btn btn-outline-danger" @click="removeBlock(index)">×</button>
                            </div>
                        </div>

                        <template x-if="block.type === 'heading'"><div><select class="form-select form-select-sm mb-2" x-model="block.level" @change="syncHtml()"><option value="h1">H1</option><option value="h2">H2</option><option value="h3">H3</option><option value="h4">H4</option></select><input class="form-control form-control-sm" x-model="block.text" @input="syncHtml()"></div></template>
                        <template x-if="block.type === 'paragraph'"><textarea class="form-control form-control-sm" rows="3" x-model="block.text" @input="syncHtml()"></textarea></template>
                        <template x-if="block.type === 'token'"><select class="form-select form-select-sm" x-model="block.token" @change="syncHtml()"><template x-for="token in allTokenOptions" :key="token"><option :value="token" x-text="token"></option></template></select></template>
                        <template x-if="block.type === 'conditional'"><div><select class="form-select form-select-sm mb-2" x-model="block.gender" @change="syncHtml()"><option value="hombre">Solo hombre</option><option value="mujer">Solo mujer</option></select><textarea class="form-control form-control-sm" rows="4" x-model="block.content" @input="syncHtml()"></textarea></div></template>
                        <template x-if="block.type === 'spacer'"><input type="number" class="form-control form-control-sm" min="0" max="300" x-model="block.height" @input="syncHtml()"></template>
                        <template x-if="block.type === 'html'"><textarea class="form-control form-control-sm font-monospace" rows="4" x-model="block.html" @input="syncHtml()"></textarea></template>
                    </div>
                </template>
            </div>
            <div class="card-footer bg-white border-0">
                <button type="submit" class="btn btn-success w-100">
                    <i class="bi bi-save"></i> {{ $template ? 'Guardar cambios' : 'Crear plantilla' }}
                </button>
            </div>
        </div>
    </div>
</div>
