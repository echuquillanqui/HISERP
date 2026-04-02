@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold">Editar Convenio</div>
        <div class="card-body">
            <form action="{{ route('convenios.update', $agreement) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <input type="text" name="description" class="form-control" value="{{ old('description', $agreement->description) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select" required>
                        <option value="ACTIVE" @selected(old('status', $agreement->status) === 'ACTIVE')>ACTIVO</option>
                        <option value="INACTIVE" @selected(old('status', $agreement->status) === 'INACTIVE')>NO ACTIVO</option>
                    </select>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('convenios.index') }}" class="btn btn-light">Cancelar</a>
                    <button type="submit" class="btn btn-primary-custom">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
