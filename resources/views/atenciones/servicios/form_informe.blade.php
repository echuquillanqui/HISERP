@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark">Redacción de Informe</h2>
            <p class="text-muted mb-0">
                Paciente:
                <span class="fw-bold">{{ $detail->order->patient->first_name }} {{ $detail->order->patient->last_name }}</span>
                | Servicio: {{ $detail->name }}
            </p>
        </div>
        <div>
            <a href="{{ route('serviceresults.index') }}" class="btn btn-outline-secondary shadow-sm">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
            <button type="button" onclick="document.getElementById('formInforme').submit()" class="btn btn-primary shadow-sm">
                <i class="bi bi-save"></i> Guardar Informe Final
            </button>
        </div>
    </div>

    

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form id="formInforme" action="{{ route('services.guardar', $detail->id) }}" method="POST">
                @csrf
                <input type="hidden" name="template_id" value="{{ $template->id }}">
                
                <textarea id="reportEditor" name="html_final">{!! $htmlContent !!}</textarea>
            </form>
        </div>
    </div>
</div>

@include('templates.partials.ckeditor-config')
@endsection
