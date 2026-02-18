<div class="modal fade" id="modalForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light border-0">
                <h5 class="fw-bold mb-0" x-text="modalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form @submit.prevent="submitForm">
                    <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>
                    <input type="hidden" name="area_id" :value="formData.area_id">

                    <template x-if="activeType === 'area'">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Nombre del Área</label>
                            <input type="text" name="name" class="form-control" x-model="formData.name" required>
                        </div>
                    </template>

                    <template x-if="activeType === 'catalog'">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase">Nombre del Examen</label>
                                <input type="text" name="name" class="form-control" x-model="formData.name" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-uppercase">Unidad</label>
                                <input type="text" name="unit" class="form-control" x-model="formData.unit">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-uppercase">Precio (S/.)</label>
                                <input type="number" step="0.01" name="price" class="form-control" x-model="formData.price" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase">Valores de Referencia</label>
                                <textarea name="reference_range" class="form-control" rows="2" x-model="formData.reference_range"></textarea>
                            </div>
                        </div>
                    </template>

                    <template x-if="activeType === 'profile'">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase">Nombre del Perfil</label>
                                <input type="text" name="name" class="form-control" x-model="formData.name" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase">Precio de Paquete</label>
                                <input type="number" step="0.01" name="price" class="form-control" x-model="formData.price" required>
                            </div>
                        </div>
                    </template>

                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary-custom px-4 rounded-pill shadow-sm">
                            <i class="bi bi-save me-2"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>