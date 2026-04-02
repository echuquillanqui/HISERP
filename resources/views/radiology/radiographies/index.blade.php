@extends('layouts.app')

@section('content')
<div class="container" x-data="radiographyManager()">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h3 class="fw-bold" style="color: var(--azul-clinico)">
                <i class="bi bi-image-fill me-2"></i>Gestión de Radiografías
            </h3>
            <p class="text-muted mb-0">Administra descripciones, precios particulares y uso de placas.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="{{ route('radiographies.create') }}" class="btn btn-primary-custom shadow-sm px-4">
                <i class="bi bi-plus-circle me-2"></i>Nueva Radiografía
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="p-4 border-bottom bg-light-subtle">
                <div class="input-group input-group-lg shadow-sm">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search" style="color: var(--cian-clinico)"></i>
                    </span>
                    <input type="text"
                           class="form-control border-start-0 ps-0"
                           placeholder="Buscar por descripción..."
                           x-model="search"
                           @input.debounce.300ms="fetchRadiographies(1)">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small fw-bold text-uppercase">
                        <tr>
                            <th class="ps-4 py-3">Descripción</th>
                            <th>Precio Particular</th>
                            <th>Uso de Placas</th>
                            <th>Convenios con Precio</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="item in radiographies" :key="item.id">
                            <tr>
                                <td class="ps-4 fw-semibold" x-text="item.description"></td>
                                <td x-text="item.private_price ? `S/ ${Number(item.private_price).toFixed(2)}` : '-' "></td>
                                <td x-text="item.plate_usage"></td>
                                <td x-text="item.agreement_prices_count"></td>
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-sm">
                                        <a :href="`/radiographies/${item.id}/edit`" class="btn btn-sm btn-white border" title="Editar">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </a>
                                        <button @click="confirmDelete(item)" class="btn btn-sm btn-white border text-danger" title="Eliminar">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <tr x-show="radiographies.length === 0">
                            <td colspan="5" class="text-center py-5">
                                <h5 class="text-secondary fw-bold mb-1">No hay radiografías para mostrar</h5>
                                <p class="text-muted mb-0">Intenta con otro término de búsqueda.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top" x-show="total > 0">
                <small class="text-muted mb-2 mb-md-0" x-text="`Mostrando ${from}-${to} de ${total} registros`"></small>
                <div class="btn-group">
                    <button class="btn btn-outline-secondary btn-sm" :disabled="currentPage === 1" @click="fetchRadiographies(currentPage - 1)">Anterior</button>
                    <template x-for="page in pages" :key="page">
                        <button class="btn btn-sm" :class="page === currentPage ? 'btn-primary' : 'btn-outline-secondary'" @click="fetchRadiographies(page)" x-text="page"></button>
                    </template>
                    <button class="btn btn-outline-secondary btn-sm" :disabled="currentPage === lastPage" @click="fetchRadiographies(currentPage + 1)">Siguiente</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function radiographyManager() {
    return {
        radiographies: [],
        search: '',
        currentPage: 1,
        lastPage: 1,
        total: 0,
        from: 0,
        to: 0,

        get pages() {
            const pages = [];
            const start = Math.max(1, this.currentPage - 2);
            const end = Math.min(this.lastPage, this.currentPage + 2);
            for (let page = start; page <= end; page++) {
                pages.push(page);
            }
            return pages;
        },

        init() {
            this.fetchRadiographies(1);
        },

        fetchRadiographies(page = 1) {
            const params = new URLSearchParams({
                search: this.search,
                page: page,
                per_page: 10,
            });

            fetch(`{{ route('radiographies.index') }}?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => {
                if (!res.ok) throw new Error('Error al cargar radiografías');
                return res.json();
            })
            .then(data => {
                this.radiographies = data.data ?? [];
                this.currentPage = data.current_page ?? 1;
                this.lastPage = data.last_page ?? 1;
                this.total = data.total ?? 0;
                this.from = data.from ?? 0;
                this.to = data.to ?? 0;
            })
            .catch(() => {
                this.radiographies = [];
            });
        },

        confirmDelete(item) {
            Swal.fire({
                title: '¿Eliminar radiografía?',
                text: `Se eliminará: ${item.description}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#2d406b',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/radiographies/${item.id}`;
                    form.innerHTML = `
                        @csrf
                        @method('DELETE')
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    }
}
</script>
@endsection
