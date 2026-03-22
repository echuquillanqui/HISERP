@extends('layouts.app')

@section('content')
<style>
.template-preview-wrapper{background:#f1f3f5;padding:16px;border-radius:.5rem;}
.template-preview-sheet{background:#fff;width:210mm;min-height:297mm;margin:0 auto;padding:15mm;box-sizing:border-box;border:1px solid #d8dee4;box-shadow:0 4px 16px rgba(0,0,0,.08);font-family:'Times New Roman',serif;line-height:1.3;font-size:12pt;}
@media print{.template-preview-wrapper{background:#fff;padding:0}.template-preview-sheet{width:auto;min-height:auto;margin:0;border:0;box-shadow:none;padding:0}.btn{display:none!important}}
</style>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1">Vista previa: {{ $template->nombre_plantilla }}</h3>
            <p class="text-muted mb-0">Previsualización de estructura final para validar antes de usarla.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('templates.render', $template) }}" class="btn btn-success"><i class="bi bi-file-earmark-text"></i> Rellenar e imprimir</a>
            <button type="button" class="btn btn-outline-dark" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir</button>
        </div>
    </div>

    <div class="template-preview-wrapper">
        <div class="template-preview-sheet">{!! $htmlPrevisualizado !!}</div>
    </div>
</div>
@endsection
