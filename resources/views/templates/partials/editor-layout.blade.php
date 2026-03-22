<div class="row g-3">
    <div class="col-xl-4">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="fw-bold mb-1">Servicio asignado</label>
                        <select name="service_id" class="form-select" required>
                            <option value="">Seleccione...</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" @selected(old('service_id', $template->service_id ?? null) == $service->id)>{{ $service->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
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
                                <tr><td colspan="5" class="text-center py-3 text-muted">Agrega los campos que deseas llenar en el formulario.</td></tr>
                            </template>
                            <template x-for="(field, index) in fields" :key="index">
                                <tr>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" x-model="field.label" @input="syncFieldIdentity(field)">
                                        <small class="text-muted">Marcador: <code x-text="buildFieldToken(field.key)"></code></small>
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
    </div>

    <div class="col-xl-8">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-primary text-white fw-bold">Formato del documento</div>
            <div class="card-body">
                <p class="text-muted small mb-2">Escribe aquí el contenido final tal como debe imprimirse. Puedes usar los marcadores rápidos para insertar campos.</p>

                <div class="border rounded p-2 bg-light mb-3">
                    <div class="small fw-semibold mb-2">Marcadores rápidos del sistema</div>
                    <div class="d-flex flex-wrap gap-2">
                        <template x-for="placeholder in systemPlaceholders" :key="placeholder.value">
                            <button type="button" class="btn btn-outline-secondary btn-sm" x-text="placeholder.label" @click="insertTextAtCursor(placeholder.value)"></button>
                        </template>
                    </div>
                </div>

                <div class="border rounded p-2 bg-light mb-3" x-show="fields.length">
                    <div class="small fw-semibold mb-2">Marcadores de campos creados</div>
                    <div class="d-flex flex-wrap gap-2">
                        <template x-for="field in fields" :key="field.key">
                            <button type="button" class="btn btn-outline-primary btn-sm" @click="insertFieldToken(field)" x-text="field.label"></button>
                        </template>
                    </div>
                </div>

                <label class="fw-bold mb-1">Contenido / formato</label>
                <textarea class="form-control font-monospace" rows="16" x-model="documentTemplate" x-ref="editor" @input="syncHtml()" placeholder="Ejemplo:\n\nINFORME MÉDICO\nPaciente: @{{nombre_paciente}}\nFecha: @{{fecha_actual}}\n\nDiagnóstico: @{{campo:diagnostico}}\nIndicaciones: @{{campo:indicaciones}}"></textarea>
            </div>
            <div class="card-footer bg-white border-0">
                <button type="submit" class="btn btn-success w-100">
                    <i class="bi bi-save"></i> {{ $template ? 'Guardar cambios' : 'Crear plantilla' }}
                </button>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold">Vista previa A4</div>
            <div class="card-body"><div class="template-preview-sheet" x-ref="preview"></div></div>
        </div>
    </div>
</div>
