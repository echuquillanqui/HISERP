<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Portal Clínico - {{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        :root {
            --azul-clinico: #2d406b; /* Color azul de tu logo */
            --cian-clinico: #00acc1; /* Color cian de tu logo */
            --bg-light: #f4f7f9;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: #2d3748;
        }

        /* Navbar principal con los colores del logo */
        .navbar-custom {
            background-color: var(--azul-clinico) !important;
            border-bottom: 3px solid var(--cian-clinico);
            padding: 0.7rem 1rem;
        }

        .navbar-brand {
            font-weight: 700;
            color: #ffffff !important;
            display: flex;
            align-items: center;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 500;
            margin: 0 5px;
            transition: all 0.2s;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--cian-clinico) !important;
        }

        /* Estilo del dropdown del usuario */
        .dropdown-menu {
            border: none;
            border-radius: 10px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
        }

        .user-pill {
            background: rgba(255, 255, 255, 0.1);
            padding: 5px 15px;
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-primary-custom {
            background-color: var(--azul-clinico);
            border: none;
            color: white;
            border-radius: 8px;
            transition: 0.3s;
        }

        .btn-primary-custom:hover {
            background-color: var(--cian-clinico);
        }
    </style>
</head>
<body>
    <div id="app">
        {{-- Navbar solo para usuarios autenticados --}}
        @auth
        <nav class="navbar navbar-expand-md navbar-dark navbar-custom shadow-sm">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <span class="me-2" style="color: var(--cian-clinico)">✚</span> 
                    {{ config('app.name', 'GESTIÓN MÉDICA') }}
                </a>
                
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                        <li class="nav-item dropdown">
                            <a id="adminDropdown" class="nav-link dropdown-toggle {{ Request::is('usuarios*') || Request::is('sucursal*') || Request::is('pacientes*') ? 'active' : '' }}" 
                            href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-shield-lock me-1"></i> Administración
                            </a>
                            <ul class="dropdown-menu shadow-sm" aria-labelledby="adminDropdown">

                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('specialties.index') }}">
                                        <i class="bi bi-person-vcard me-2 text-primary"></i> Especialidades
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('users.index') }}">
                                        <i class="bi bi-person-badge me-2 text-primary"></i> Usuarios
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('branches.index') }}">
                                        <i class="bi bi-building-check me-2 text-primary"></i> Sucursal
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('patients.index') }}">
                                        <i class="bi bi-person-vcard me-2 text-primary"></i> Pacientes
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a id="cashboxDropdown" class="nav-link dropdown-toggle {{ Request::is('cashbox*') ? 'active' : '' }}" 
                            href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-funnel me-1"></i> Caja
                            </a>
                            <ul class="dropdown-menu shadow-sm" aria-labelledby="cashboxDropdown">
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('cashbox.index') }}">
                                        <i class="bi bi-layers me-2 text-info"></i> Cuadre de Caja
                                    </a>
                                </li>

                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a id="labDropdown" class="nav-link dropdown-toggle {{ Request::is('laboratorio*') ? 'active' : '' }}" 
                            href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-funnel me-1"></i> Laboratorio
                            </a>
                            <ul class="dropdown-menu shadow-sm" aria-labelledby="labDropdown">
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('areas.index') }}">
                                        <i class="bi bi-layers me-2 text-info"></i> Catalogo
                                    </a>
                                </li>

                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a id="labDropdown" class="nav-link dropdown-toggle {{ Request::is('laboratorio*') ? 'active' : '' }}" 
                            href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-postcard-heart me-1"></i> Atenciones
                            </a>
                            <ul class="dropdown-menu shadow-sm" aria-labelledby="labDropdown">
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('orders.index') }}">
                                        <i class="bi bi-layers me-2 text-info"></i> Ordenes
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('histories.index') }}">
                                        <i class="bi bi-person-lines-fill me-2 text-info"></i> Medicina
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('lab-results.index') }}">
                                         <i class="bi bi-list
                                        <i class="bi bi-list-check me-2 text-info"></i> Resultados
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                    </ul>

                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle user-pill d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-2" style="color: var(--cian-clinico)"></i>
                                <span>{{ Auth::user()->name }}</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end shadow border-0">
                                <a class="dropdown-item py-2" href="#"><i class="bi bi-person me-2"></i> Mi Perfil</a>
                                <a class="dropdown-item py-2" href="#"><i class="bi bi-gear me-2"></i> Configuración</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item py-2 text-danger fw-bold" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="bi bi-box-arrow-right me-2"></i> {{ __('Cerrar Sesión') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        @endauth

        {{-- El contenido ocupa toda la pantalla en login, y tiene padding en el resto del sitio --}}
        <main class="{{ Request::is('login') ? '' : 'container-fluid py-4' }}">
            @yield('content')
        </main>
    </div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const playSuccessSound = () => {
        const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2354/2354-preview.mp3'); 
        audio.play();
    }

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '¡Operación Exitosa!',
            text: "{{ session('success') }}",
            timer: 3000,
            showConfirmButton: false,
            didOpen: () => { playSuccessSound(); }
        });
    @endif
</script>    
</body>
</html>