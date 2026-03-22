@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Módulo de Plantillas</h2>
            <p class="text-muted mb-0">Diseña, prueba, rellena e imprime plantillas HTML listas para atención médica.</p>
        </div>
        <a href="{{ route('templates.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nueva plantilla
        </a>
    </div>

    <div class="row g-3">
        @forelse($templates as $template)
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <span class="badge bg-info text-dark align-self-start mb-2">{{ $template->service->nombre ?? 'Sin servicio' }}</span>
                        <h5 class="fw-bold">{{ $template->nombre_plantilla }}</h5>
                        <p class="text-muted small mb-3">Campos dinámicos: {{ count($template->fields_schema ?? []) }}</p>

                        <div class="mt-auto d-grid gap-2">
                            <a href="{{ route('templates.preview', $template) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye"></i> Vista previa
                            </a>
                            <a href="{{ route('templates.render', $template) }}" class="btn btn-outline-success btn-sm">
                                <i class="bi bi-file-earmark-text"></i> Rellenar e imprimir
                            </a>
                            <a href="{{ route('templates.edit', $template) }}" class="btn btn-outline-warning btn-sm">
                                <i class="bi bi-pencil"></i> Editar diseño
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-light border text-center mb-0">No hay plantillas creadas todavía.</div>
            </div>
        @endforelse
    </div>
</div>
@endsection
