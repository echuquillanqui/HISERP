@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<div class="container">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold">Editar Orden de Tomografía</div>
        <div class="card-body">
            <form action="{{ route('order-tomografia.update', $orderTomography) }}" method="POST">
                @csrf
                @method('PUT')
                @include('radiology.order_tomographies._form')
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('order-tomografia.index') }}" class="btn btn-light">Cancelar</a>
                    <button type="submit" class="btn btn-primary-custom">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
