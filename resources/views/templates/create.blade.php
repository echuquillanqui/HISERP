@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4" x-data="templateVisualBuilder('', @js(old('fields_schema') ? json_decode(old('fields_schema'), true) : []))" x-init="init()">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-primary mb-1"><i class="bi bi-file-earmark-plus"></i> Crear plantilla</h3>
            <p class="text-muted mb-0">Define los campos que necesitas y redacta el formato final de forma simple.</p>
        </div>
        <a href="{{ route('templates.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <form action="{{ route('templates.store') }}" method="POST" @submit="syncHtml()">
        @csrf
        <input type="hidden" name="html_content" x-ref="htmlContent">
        <input type="hidden" name="fields_schema" x-ref="fieldsSchema">

        @include('templates.partials.editor-layout', ['services' => $services, 'template' => null])
    </form>
</div>

@include('templates.partials.visual-builder')
@endsection
