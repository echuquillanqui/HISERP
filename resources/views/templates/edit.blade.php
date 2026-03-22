@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4" x-data="templateVisualBuilder(@js(old('html_content', $template->html_content)), @js(old('fields_schema') ? json_decode(old('fields_schema'), true) : ($template->fields_schema ?? [])))" x-init="init()">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-warning mb-1"><i class="bi bi-pencil-square"></i> Editar plantilla</h3>
            <p class="text-muted mb-0">Ajusta diseño, variables y estructura para un llenado intuitivo.</p>
        </div>
        <a href="{{ route('templates.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <form action="{{ route('templates.update', $template->id) }}" method="POST" @submit="syncHtml()">
        @csrf
        @method('PUT')
        <input type="hidden" name="html_content" x-ref="htmlContent">
        <input type="hidden" name="fields_schema" x-ref="fieldsSchema">

        @include('templates.partials.editor-layout', ['services' => $services, 'template' => $template])
    </form>
</div>

@include('templates.partials.visual-builder')
@endsection
