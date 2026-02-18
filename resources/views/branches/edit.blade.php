@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold" style="color: var(--azul-clinico)">
                        <i class="bi bi-pencil-square me-2"></i>Editar Sucursal: {{ $branch->razon_social }}
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('branches.update', $branch) }}" method="POST" enctype="multipart/form-data" x-data="logoPreview('{{ $branch->logo ? asset('storage/'.$branch->logo) : null }}')">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold">RUC</label>
                                <input type="text" name="ruc" class="form-control" value="{{ $branch->ruc }}" readonly>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label small fw-bold">Razón Social</label>
                                <input type="text" name="razon_social" class="form-control" value="{{ $branch->razon_social }}" required>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label small fw-bold">Dirección</label>
                                <input type="text" name="direccion" class="form-control" value="{{ $branch->direccion }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Correo</label>
                                <input type="email" name="correo" class="form-control" value="{{ $branch->correo }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label small fw-bold">Teléfono</label>
                                <input type="text" name="telefono" class="form-control" value="{{ $branch->telefono }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label small fw-bold">Estado</label>
                                <select name="estado" class="form-select">
                                    <option value="1" {{ $branch->estado == 1 ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ $branch->estado == 0 ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="form-label small fw-bold">Logo Actual / Nuevo</label>
                                <div class="d-flex align-items-center gap-3 p-3 border rounded bg-light">
                                    <div class="bg-white border d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; overflow: hidden; border-radius: 10px;">
                                        <template x-if="imageUrl">
                                            <img :src="imageUrl" class="img-fluid object-fit-cover">
                                        </template>
                                        <template x-if="!imageUrl">
                                            <i class="bi bi-image text-muted fs-3"></i>
                                        </template>
                                    </div>
                                    <input type="file" name="logo" class="form-control" @change="fileChosen" accept="image/*">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="{{ route('branches.index') }}" class="btn btn-light px-4">Volver</a>
                            <button type="submit" class="btn btn-primary-custom px-5 shadow-sm">Actualizar Sucursal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function logoPreview(initialUrl) {
        return {
            imageUrl: initialUrl || null,
            fileChosen(event) {
                const file = event.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = (e) => this.imageUrl = e.target.result;
            }
        }
    }
</script>
@endsection