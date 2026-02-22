@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-primary">Registrar Nuevo Medicamento</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('products.store') }}" method="POST">
                        @csrf
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Código de Barras / SKU</label>
                                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" required>
                                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Nombre del Medicamento</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted small">Concentración (Ej: 500mg)</label>
                                <input type="text" name="concentration" class="form-control" value="{{ old('concentration') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted small">Presentación (Ej: Tabletas)</label>
                                <input type="text" name="presentation" class="form-control" value="{{ old('presentation') }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-success">Precio Venta</label>
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="number" step="0.01" name="selling_price" class="form-control" value="{{ old('selling_price') }}" required>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-danger">Precio Compra</label>
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="number" step="0.01" name="purchase_price" class="form-control" value="{{ old('purchase_price') }}">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Stock Inicial</label>
                                <input type="number" name="stock" class="form-control" value="{{ old('stock', 0) }}" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Vencimiento</label>
                                <input type="date" name="expiration_date" class="form-control" value="{{ old('expiration_date') }}">
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                            <a href="{{ route('products.index') }}" class="btn btn-light px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-4">Guardar Producto</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection