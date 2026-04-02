@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold">Nueva Radiografía</div>
        <div class="card-body">
            <form action="{{ route('radiographies.store') }}" method="POST">
                @csrf
                @php($radiography = new \App\Models\Radiography())
                @include('radiology.radiographies._form')
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('radiographies.index') }}" class="btn btn-light">Cancelar</a>
                    <button type="submit" class="btn btn-primary-custom">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
