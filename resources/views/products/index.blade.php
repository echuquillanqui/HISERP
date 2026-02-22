@extends('layouts.app')

@section('content')
<div class="container py-4" x-data="productSearch()">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-primary mb-0">
                    <i class="bi bi-capsule me-2"></i>Catálogo de Medicamentos
                </h3>
                <p class="text-muted mb-0">Gestión de inventario y precios de farmacia.</p>
            </div>
            <a href="{{ route('products.create') }}" class="btn btn-primary px-4 shadow-sm">
                <i class="bi bi-plus-circle me-2"></i>Nuevo Producto
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-8">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               x-model="query" 
                               @input.debounce.300ms="fetchProducts(1)"
                               class="form-control border-start-0 ps-0 shadow-none" 
                               placeholder="Buscar por nombre, código o concentración...">
                        <template x-if="loading">
                            <span class="input-group-text bg-white border-start-0">
                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            </span>
                        </template>
                    </div>
                </div>

                <div class="col-md-4">
                    <select x-model="status" @change="fetchProducts(1)" class="form-select form-select-lg shadow-none">
                        <option value="">Todos los estados</option>
                        <option value="1">Solo Activos</option>
                        <option value="0">Solo Inactivos</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Código</th>
                        <th>Medicamento</th>
                        <th>Concentración / Presentación</th>
                        <th class="text-center">Stock</th>
                        <th>Precio Venta</th>
                        <th>Estado</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="products.length === 0 && !loading">
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-chat-left-dots fs-1 text-light d-block mb-3"></i>
                                <span class="text-muted">No se encontraron productos que coincidan con los filtros.</span>
                            </td>
                        </tr>
                    </template>

                    <template x-for="product in products" :key="product.id">
                        <tr>
                            <td class="ps-4">
                                <code class="text-primary fw-bold" x-text="product.code"></code>
                            </td>
                            <td>
                                <div class="fw-bold text-dark" x-text="product.name"></div>
                            </td>
                            <td>
                                <span class="text-muted small" x-text="product.concentration || 'N/A'"></span>
                                <span class="text-muted small" x-show="product.presentation" x-text="' / ' + product.presentation"></span>
                            </td>
                            <td class="text-center">
                                <span :class="product.stock <= 5 ? 'badge bg-danger' : 'badge bg-success'" 
                                      x-text="product.stock"></span>
                            </td>
                            <td>
                                <span class="fw-bold text-dark" x-text="'S/ ' + parseFloat(product.selling_price).toFixed(2)"></span>
                            </td>
                            <td>
                                <template x-if="product.is_active == 1">
                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3">Activo</span>
                                </template>
                                <template x-if="product.is_active == 0">
                                    <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-3">Inactivo</span>
                                </template>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a :href="'/products/' + product.id + '/edit'" class="btn btn-sm btn-outline-primary border-0 mx-1" title="Editar">
                                        <i class="bi bi-pencil-square fs-5"></i>
                                    </a>

                                    <button type="button" @click="deleteProduct(product.id)" class="btn btn-sm btn-outline-danger border-0">
                                        <i class="bi bi-trash fs-5"></i>
                                    </button>   
                                </div>
                                <form :id="'delete-form-' + product.id" 
                                    :action="'/products/' + product.id" 
                                    method="POST" 
                                    style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 border-top" x-show="pagination.last_page > 1">
            <div class="text-muted small">
                Mostrando página <span class="fw-bold" x-text="pagination.current_page"></span> de <span class="fw-bold" x-text="pagination.last_page"></span>
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ 'disabled': pagination.current_page === 1 }">
                        <button class="page-link shadow-none" @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1">
                            Anterior
                        </button>
                    </li>
                    <li class="page-item" :class="{ 'disabled': pagination.current_page === pagination.last_page }">
                        <button class="page-link shadow-none" @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page">
                            Siguiente
                        </button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<script>
function productSearch() {
    return {
        query: '',
        status: '',
        products: [],
        loading: false,
        pagination: {
            current_page: 1,
            last_page: 1
        },
        init() {
            this.fetchProducts();
        },
        fetchProducts(page = 1) {
            this.loading = true;
            fetch(`/api/products/search?q=${encodeURIComponent(this.query)}&status=${this.status}&page=${page}`)
                .then(res => res.json())
                .then(data => {
                    this.products = data.data;
                    this.pagination.current_page = data.current_page;
                    this.pagination.last_page = data.last_page;
                    this.loading = false;
                })
                .catch(() => this.loading = false);
        },
        changePage(newPage) {
            if (newPage >= 1 && newPage <= this.pagination.last_page) {
                this.fetchProducts(newPage);
            }
        },
        // NUEVA FUNCIÓN PARA ELIMINAR
        deleteProduct(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este producto? Esta acción no se puede deshacer.')) {
                document.getElementById('delete-form-' + id).submit();
            }
        }
    }
}
</script>

<style>
    .bg-success-subtle { background-color: #e1f7e3 !important; }
    .bg-danger-subtle { background-color: #fce8e8 !important; }
    .btn-outline-primary:hover { color: #fff !important; }
</style>
@endsection