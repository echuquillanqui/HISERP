@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<div class="container-fluid">
    {{-- BLOQUE DE DIAGNÓSTICO DE ERRORES --}}
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm border-start border-danger border-4">
            <h5 class="fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Error de Validación:</h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    @if (session('error'))
        <div class="alert alert-warning border-start border-warning border-4">
            {{ session('error') }}
        </div>
    @endif
</div>

<style>
    /* SOLUCIÓN A TABS INVISIBLES */
    .nav-tabs {
        border-bottom: 2px solid #dee2e6;
        background: #f1f1f1; /* Fondo para el área detrás de los tabs */
        border-radius: 8px 8px 0 0;
    }
    .nav-tabs .nav-link {
        color: #555 !important;
        background-color: #e9ecef; /* Color gris claro para tabs inactivos */
        border: 1px solid #dee2e6 !important;
        margin-right: 4px;
        transition: all 0.2s;
        font-weight: 500;
    }
    .nav-tabs .nav-link:hover {
        background-color: #dee2e6;
    }
    .nav-tabs .nav-link.active {
        color: #fff !important;
        background-color: #0d6efd !important; /* Azul para el activo */
        border-color: #0d6efd !important;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    /* Espaciado para el contenido del tab */
    .tab-content {
        border: 1px solid #dee2e6;
        border-top: none;
        border-radius: 0 0 8px 8px;
        background: #fff;
    }
</style>

<div class="container-fluid py-4" 
     id="clinical-app"
     x-data="clinicalWorkstation()" 
     data-diagnostics="{{ $history->diagnostics->map(fn($d) => ['cie10_id' => $d->cie10_id, 'codigo' => $d->cie10->codigo ?? 'S/C', 'descripcion' => $d->diagnostico, 'tratamiento' => $d->tratamiento])->toJson() }}"
     data-prescription="{{ $history->prescription ? $history->prescription->items->map(fn($i) => [
         'product_id' => $i->product_id, // DEBE SER product_id
         'name' => $i->product->name ?? 'N/A', 
         'concentration' => $i->product->concentration ?? '',
        'presentation'  => $i->product->presentation ?? '',
         'qty' => $i->cantidad, 
         'notes' => $i->indicaciones
     ])->toJson() : '[]' }}"
     data-labs="{{ $history->labItems->pluck('name')->toJson() }}">

    <form action="{{ route('histories.update', $history->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white p-0">
                        <ul class="nav nav-tabs nav-fill border-0" id="myTab" role="tablist">
                            <li class="nav-item"><a class="nav-link active py-3" data-bs-toggle="tab" href="#tab-anamnesis">1. ANAMNESIS / ANTECEDENTES</a></li>
                            <li class="nav-item"><a class="nav-link py-3" data-bs-toggle="tab" href="#tab-dx">2. DIAGNÓSTICOS</a></li>
                            <li class="nav-item"><a class="nav-link py-3" data-bs-toggle="tab" href="#tab-rx">3. RECETA</a></li>
                            <li class="nav-item"><a class="nav-link py-3" data-bs-toggle="tab" href="#tab-lab">4. LABORATORIO</a></li>
                        </ul>
                    </div>
                    
                    <div class="card-body p-4 tab-content">
                        <div class="tab-pane fade show active" id="tab-anamnesis">
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label class="fw-bold mb-2">Relato de la consulta (Anamnesis)</label>
                                    <textarea name="anamnesis" class="form-control" rows="6" required>{{ $history->anamnesis }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="fw-bold mb-2">Antecedentes Familiares</label>
                                    <textarea name="antecedentes_familiares" class="form-control" rows="3">{{ $history->antecedentes_familiares }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-bold mb-2">Otros Antecedentes / Especificar</label>
                                    <textarea name="antecedentes_otros" class="form-control" rows="3">{{ $history->antecedentes_otros }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label class="fw-bold mb-2">Alergias</label>
                                    <input type="text" name="alergias" class="form-control" value="{{ $history->alergias }}">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <label class="fw-bold mb-2">Examen Físico Detallado</label>
                                    <textarea name="examen_fisico_detalle" class="form-control" rows="3">{{ $history->examen_fisico_detalle }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-dx">
                            <div class="mb-4">
                                <label class="fw-bold text-primary">Buscar en CIE10</label>
                                <select id="cie10_select" class="form-control"></select>
                            </div>
                            <table class="table align-middle">
                                <thead><tr><th>Cód.</th><th>Descripción</th><th>Tratamiento</th><th></th></tr></thead>
                                <tbody>
                                    <template x-for="(dx, index) in diagnostics" :key="index">
                                        <tr>
                                            <td x-text="dx.codigo" class="fw-bold"></td>
                                            <td x-text="dx.descripcion" class="small"></td>
                                            <td>
                                                <input type="text" :name="'diagnostics['+index+'][tratamiento]'" x-model="dx.tratamiento" class="form-control form-control-sm">
                                                <input type="hidden" :name="'diagnostics['+index+'][cie10_id]'" :value="dx.cie10_id">
                                                <input type="hidden" :name="'diagnostics['+index+'][descripcion]'" :value="dx.descripcion">
                                            </td>
                                            <td><button type="button" @click="removeDx(index)" class="btn btn-sm text-danger">×</button></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <div class="tab-pane fade" id="tab-rx">
                            <div class="mb-4">
                                <label class="fw-bold text-success">Buscar Medicamento</label>
                                <select id="product_select" class="form-control"></select>
                            </div>
                            <table class="table align-middle">
                            <thead>
        <tr class="table-light">
            <th>Medicamento / Detalles</th>
            <th width="120px">Cant.</th>
            <th>Indicaciones (Dosis, Frecuencia, etc.)</th>
            <th width="50px"></th>
        </tr>
    </thead>
    <tbody>
        <template x-for="(item, index) in prescription" :key="index">
            <tr>
                <td>
                    <div class="fw-bold text-primary" x-text="item.name"></div>
                    <div class="small text-muted">
                        <span x-show="item.concentration" x-text="item.concentration"></span>
                        <span x-show="item.concentration && item.presentation"> - </span>
                        <span x-show="item.presentation" x-text="item.presentation"></span>
                        <span x-show="!item.concentration && !item.presentation" class="fst-italic">Sin especificar</span>
                    </div>
                    <input type="hidden" :name="'prescription['+index+'][product_id]'" :value="item.product_id">
                </td>

                <td>
                    <input type="text" 
                           :name="'prescription['+index+'][qty]'" 
                           x-model="item.qty" 
                           placeholder="Ej: 10"
                           class="form-control form-control-sm border-primary-subtle">
                </td>

                <td>
                    <input type="text" 
                           :name="'prescription['+index+'][notes]'" 
                           x-model="item.notes" 
                           placeholder="Ej: 1 tableta cada 8 horas por 3 días"
                           class="form-control form-control-sm border-primary-subtle">
                </td>

                <td class="text-end">
                    <button type="button" @click="removeRx(index)" class="btn btn-sm btn-light text-danger shadow-sm">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </td>
            </tr>
        </template>
        
        <template x-if="prescription.length === 0">
            <tr>
                <td colspan="4" class="text-center py-4 text-muted">
                    <i class="bi bi-capsule me-2"></i> No hay medicamentos añadidos a la receta.
                </td>
            </tr>
        </template>
    </tbody>
                        </table>

                            <div class="mt-4 p-3 border-top bg-light rounded">
                                <div class="row align-items-center">
                                    <div class="col-md-5">
                                        <label class="fw-bold text-dark small">
                                            <i class="bi bi-calendar-check text-success me-1"></i>PRÓXIMA CITA:
                                        </label>
                                        <div class="input-group input-group-sm mt-2">
                                            <span class="input-group-text bg-white"><i class="bi bi-calendar-event"></i></span>
                                            <input type="date" name="fecha_sig_cita" 
                                                class="form-control" 
                                                value="{{ old('fecha_sig_cita', $history->prescription->fecha_sig_cita ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <p class="text-muted mb-0 small" style="margin-top: 20px;">
                                            * Esta fecha aparecerá al final de la receta impresa.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-lab">
                            <div class="bg-light p-4 rounded border">
                                <label class="fw-bold text-info mb-2">Solicitar Exámenes / Perfiles</label>
                                <select id="lab_select" name="lab_exams[]" multiple></select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-dark text-white text-center fw-bold py-2 small">SIGNOS VITALES</div>
                    <div class="card-body bg-light">
                        <div class="row g-2 small">
                            <div class="col-4"><label>P.A. (mmHg)</label><input type="text" name="pa" class="form-control form-control-sm" value="{{ $history->pa }}"></div>
                            <div class="col-4"><label>F.C. (LPM)</label><input type="text" name="fc" class="form-control form-control-sm" value="{{ $history->fc }}"></div>
                            <div class="col-4"><label>T° (°C)</label><input type="text" name="temp" class="form-control form-control-sm" value="{{ $history->temp }}"></div>
                            <div class="col-6"><label>F.R. (RPM)</label><input type="text" name="fr" class="form-control form-control-sm" value="{{ $history->fr }}"></div>
                            <div class="col-6"><label>SO2 (%)</label><input type="text" name="so2" class="form-control form-control-sm" value="{{ $history->so2 }}"></div>
                            <div class="col-6">
                                <label>Peso (Kg)</label>
                                <input type="number" step="0.1" name="peso" x-model="peso" class="form-control form-control-sm" placeholder="Kg">
                            </div>
                            <div class="col-6">
                                <label>Talla (cm)</label>
                                <input type="number" name="talla" x-model="talla" class="form-control form-control-sm" placeholder="cm">
                            </div>
                            <div class="col-12 mt-2">
                                <input type="hidden" name="imc" :value="imc">
                                
                                <div class="p-2 rounded text-center fw-bold" :class="imcClass" style="border: 1px solid rgba(0,0,0,0.1)">
                                    IMC: <span x-text="imc"></span> <br>
                                    <small x-text="imcStatus"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-secondary text-white text-center fw-bold py-1 small">HÁBITOS</div>
                    <div class="card-body py-2">
                        <div class="form-check small">
                            <input class="form-check-input" type="checkbox" name="habito_tabaco" id="tabaco" value="1" {{ $history->habito_tabaco ? 'checked' : '' }}>
                            <label class="form-check-label" for="tabaco">Tabaco</label>
                        </div>
                        <div class="form-check small">
                            <input class="form-check-input" type="checkbox" name="habito_alcohol" id="alcohol" value="1" {{ $history->habito_alcohol ? 'checked' : '' }}>
                            <label class="form-check-label" for="alcohol">Alcohol</label>
                        </div>
                        <div class="form-check small">
                            <input class="form-check-input" type="checkbox" name="habito_coca" id="coca" value="1" {{ $history->habito_coca ? 'checked' : '' }}>
                            <label class="form-check-label" for="coca">Coca</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 shadow mb-3">GUARDAR TODO</button>
            </div>
        </div>
    </form>
</div>



<script>
function clinicalWorkstation() {
    const el = document.getElementById('clinical-app');
    return {
        diagnostics: JSON.parse(el.getAttribute('data-diagnostics') || '[]'),
        prescription: el.getAttribute('data-prescription') ? JSON.parse(el.getAttribute('data-prescription')) : [],

        // --- NUEVAS VARIABLES PARA IMC ---
        peso: "{{ $history->peso }}",
        talla: "{{ $history->talla }}",

        get imc() {
            if (!this.peso || !this.talla || this.talla == 0) return '0.00';
            let tallaMetros = this.talla / 100;
            return (this.peso / (tallaMetros * tallaMetros)).toFixed(2);
        },

        get imcStatus() {
            let val = parseFloat(this.imc);
            if (val === 0) return 'Ingrese datos';
            if (val < 18.5) return 'Bajo Peso';
            if (val < 25) return 'Normal';
            if (val < 30) return 'Sobrepeso';
                return 'Obesidad';
        },

        get imcClass() {
            let val = parseFloat(this.imc);
            if (val === 0) return 'bg-white text-muted';
            if (val < 18.5) return 'bg-info text-white';
            if (val < 25) return 'bg-success text-white';
            if (val < 30) return 'bg-warning text-dark';
                return 'bg-danger text-white';
        },
        
        init() {
            // Configuración común para que NO cargue todo de golpe
            const remoteSettings = {
                valueField: 'id',
                labelField: 'name',
                searchField: 'name',
                loadThrottle: 300, // Retraso de 300ms para no saturar el servidor
                preload: false,    // ¡IMPORTANTE! No carga nada al abrir el select
                shouldLoad: (query) => query.length >= 3, // Solo busca si hay 3+ letras
            };

            // 1. Buscador CIE10
            new TomSelect('#cie10_select', {
                valueField: 'id',
                labelField: 'descripcion',
                searchField: ['codigo', 'descripcion'],
                preload: false, // ¡ESTO EVITA QUE CARGUE TODO AL INICIO!
                loadThrottle: 400,
                shouldLoad: (query) => query.length >= 2, // Espera a 2 caracteres
                load: (q, cb) => {
                    fetch(`/api/search/cie10?q=${q}`)
                        .then(r => r.json())
                        .then(j => cb(j))
                        .catch(() => cb());
                },
                // Esto mejora visualmente cómo se ve el resultado en la lista
                render: {
                    option: function(item, escape) {
                        return `<div>
                            <span class="fw-bold text-primary">${escape(item.codigo)}</span> - 
                            <span class="small">${escape(item.descripcion)}</span>
                        </div>`;
                    },
                    item: function(item, escape) {
                        return `<div>${escape(item.codigo)} - ${escape(item.descripcion)}</div>`;
                    }
                },
                onChange: (v) => {
                    if(!v) return;
                    // Obtenemos el item seleccionado desde las opciones de TomSelect
                    let ts = document.getElementById('cie10_select').tomselect;
                    let item = ts.options[v];
                    
                    if(!this.diagnostics.find(d => d.cie10_id == v)) {
                        this.diagnostics.push({
                            cie10_id: v, 
                            codigo: item.codigo, 
                            descripcion: item.descripcion, 
                            tratamiento: ''
                        });
                    }
                    ts.clear();
                }
            });

            // 2. Buscador Productos
            const tsProduct = new TomSelect('#product_select', {
                valueField: 'id',
                labelField: 'name',
                searchField: ['name', 'concentration'],
                options: [],
                render: {
                    option: function(data, escape) {
                    // Creamos una cadena con los detalles solo si existen
                    let detalles = [data.concentration, data.presentation]
                        .filter(info => info && info.trim() !== '') // Quitamos nulos o vacíos
                        .join(' - '); // Los unimos con un guion

                    return `<div>
                        <div class="fw-bold">${escape(data.name)}</div>
                        ${detalles ? `<small class="text-muted">${escape(detalles)}</small>` : '<small class="text-muted text-italic">Sin detalles</small>'}
                    </div>`;
                },
                item: function(data, escape) {
                    let extra = data.concentration ? ` (${escape(data.concentration)})` : '';
                    return `<div>${escape(data.name)}${extra}</div>`;
                }
                },
                load: (q, cb) => {
                    fetch(`/api/search/products?q=${q}`)
                        .then(r => r.json())
                        .then(j => cb(j))
                        .catch(() => cb());
                },
                onChange: (id) => {
                    if(!id) return;
                    const item = tsProduct.options[id];
                    // Agregamos a la receta con los campos de la migración
                    this.prescription.push({
                        product_id: item.id,
                        name: item.name,
                        concentration: item.concentration,
                        presentation: item.presentation,
                        dose: '',
                        frequency: '',
                        duration: ''
                    });
                    tsProduct.clear();
                }
            });

            // 3. Buscador Laboratorio (Múltiple)
            const tsLab = new TomSelect('#lab_select', {
                valueField: 'name', 
                labelField: 'name',
                searchField: 'name',
                plugins: ['remove_button'],
                persist: false, // Evita que se queden cosas raras en memoria
                create: true,   // Permite al médico escribir un examen que no esté en la lista
                load: (q, cb) => {
                    fetch(`/api/search/lab?q=${q}`).then(r => r.json()).then(j => cb(j)).catch(() => cb());
                }
            });

            // Precarga de laboratorios ya guardados (limpia y segura)
            const savedLabs = JSON.parse(el.getAttribute('data-labs') || '[]');

            if(savedLabs.length > 0) {
                // 1. Limpiamos cualquier opción previa para evitar duplicados o basura
                tsLab.clearOptions(); 
                
                savedLabs.forEach(examName => {
                    // 2. Agregamos la opción usando 'name' como llave (coincidiendo con valueField)
                    tsLab.addOption({ name: examName });
                    // 3. Marcamos el ítem como seleccionado
                    tsLab.addItem(examName);
                });
            }
        },
        removeDx(i) { this.diagnostics.splice(i, 1); },
        removeRx(i) { this.prescription.splice(i, 1); }
    }
}
</script>
@endsection