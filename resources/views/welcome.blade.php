<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $branch->razon_social ?? 'Clínica Médica' }} | Excelencia en Salud</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Poppins:wght@500;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #0d6efd;       /* Azul Médico Principal */
            --secondary: #00cba9;     /* Verde Salud */
            --dark: #1a252f;          /* Texto profundo */
            --light-bg: #f8faff;      /* Fondo suave */
            --transition: all 0.3s ease;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            color: var(--dark); 
            background-color: #ffffff;
            scroll-behavior: smooth;
        }

        h1, h2, h3, .navbar-brand { font-family: 'Poppins', sans-serif; }

        /* Navbar Mejorada */
        .navbar { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
        .nav-link { 
            color: var(--dark) !important; 
            font-weight: 500; 
            transition: var(--transition);
            border-bottom: 2px solid transparent;
        }
        .nav-link:hover { color: var(--primary) !important; border-bottom: 2px solid var(--primary); }

        /* Hero Section Dinámico */
        .hero-section { 
            padding: 120px 0 80px; 
            background: radial-gradient(circle at top right, rgba(13, 110, 253, 0.05), transparent),
                        radial-gradient(circle at bottom left, rgba(0, 203, 169, 0.05), transparent);
        }
        .hero-title { font-size: 3.8rem; font-weight: 700; line-height: 1.1; margin-bottom: 1.5rem; }
        .hero-title span { color: var(--primary); }
        .img-hero-container { position: relative; }
        .img-hero-container::after {
            content: ""; position: absolute; width: 100%; height: 100%;
            border: 2px solid var(--secondary); top: 20px; left: 20px;
            z-index: -1; border-radius: 1rem;
        }

        /* Estadísticas Rápidas */
        .stats-bar { margin-top: -50px; position: relative; z-index: 10; }
        .stat-item { 
            background: white; padding: 30px; border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); text-align: center;
        }

        /* Cards de Servicios */
        .service-card { 
            border: none; border-radius: 20px; padding: 40px;
            background: var(--light-bg); transition: var(--transition);
            height: 100%;
        }
        .service-card:hover { 
            background: var(--primary); transform: translateY(-10px);
        }
        .service-card:hover * { color: white !important; }
        .service-icon { 
            width: 70px; height: 70px; background: white; 
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%; font-size: 2rem; color: var(--primary);
            margin-bottom: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        /* Footer Moderno */
        footer { background: var(--dark); color: #ecf0f1; padding: 80px 0 30px; }
        .social-btn {
            width: 45px; height: 45px; display: inline-flex;
            align-items: center; justify-content: center;
            border-radius: 50%; background: rgba(255,255,255,0.1);
            color: white; transition: var(--transition); text-decoration: none;
        }
        .social-btn:hover { background: var(--primary); transform: scale(1.1); }

        /* Botones Custom */
        .btn-primary { 
            background-color: var(--primary); border: none; 
            padding: 14px 30px; border-radius: 50px; font-weight: 600;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                @if($branch && $branch->logo)
                    <img src="{{ asset('storage/' . $branch->logo) }}" alt="Logo" height="45" class="me-2">
                @else
                    <i class="bi bi-heart-pulse-fill text-primary me-2 fs-3"></i>
                @endif
                <span class="fw-bold tracking-tighter">{{ $branch->razon_social }}</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list fs-1"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link mx-2" href="#inicio">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link mx-2" href="#servicios">Especialidades</a></li>
                    <li class="nav-item"><a class="nav-link mx-2" href="#nosotros">Nosotros</a></li>
                    <li class="nav-item ms-lg-4">
                        <a class="btn btn-outline-primary rounded-pill px-4" href="{{ route('login') }}">
                            <i class="bi bi-lock-fill me-1"></i> Acceso Staff
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <header id="inicio" class="hero-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill mb-3 fw-bold text-uppercase">Tu salud es nuestra prioridad</span>
                    <h1 class="hero-title">Atención Médica de <span>Alta Precisión</span></h1>
                    <p class="lead text-muted mb-4 fs-5">En <strong>{{ $branch->razon_social }}</strong> combinamos calidez humana con tecnología avanzada para ofrecerte el mejor cuidado preventivo y curativo.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="https://wa.me/{{ $branch->telefono }}" class="btn btn-primary btn-lg shadow-lg">
                            <i class="bi bi-calendar-check me-2"></i> Agendar Cita Online
                        </a>
                        <a href="#contacto" class="btn btn-light btn-lg border px-4">
                            Ubicación
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="img-hero-container">
                        <img src="https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=800&q=80" alt="Médico" class="img-fluid rounded-4 shadow-2xl">
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="stats-bar container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="stat-item">
                    <h3 class="fw-bold text-primary mb-0">+10 Años</h3>
                    <p class="text-muted mb-0">De experiencia médica</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <h3 class="fw-bold text-primary mb-0">15+</h3>
                    <p class="text-muted mb-0">Especialistas Certificados</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <h3 class="fw-bold text-primary mb-0">24/7</h3>
                    <p class="text-muted mb-0">Atención de Emergencias</p>
                </div>
            </div>
        </div>
    </section>

    <section id="servicios" class="py-5 mt-5">
        <div class="container py-5">
            <div class="row mb-5 justify-content-center text-center">
                <div class="col-lg-7">
                    <h2 class="fw-bold display-5">Servicios Médicos</h2>
                    <p class="text-muted">Ofrecemos un enfoque integral para cada etapa de tu vida.</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="service-card shadow-sm">
                        <div class="service-icon"><i class="bi bi-heart-pulse"></i></div>
                        <h4 class="fw-bold mb-3">Cardiología</h4>
                        <p class="text-muted">Diagnósticos precisos para el cuidado del corazón con tecnología no invasiva.</p>
                        <a href="#" class="text-decoration-none fw-bold mt-auto d-inline-block">Saber más <i class="bi bi-arrow-right ms-1"></i></a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card shadow-sm">
                        <div class="service-icon"><i class="bi bi-capsule"></i></div>
                        <h4 class="fw-bold mb-3">Medicina Interna</h4>
                        <p class="text-muted">Prevención, diagnóstico y tratamiento de enfermedades del adulto.</p>
                        <a href="#" class="text-decoration-none fw-bold mt-auto d-inline-block">Saber más <i class="bi bi-arrow-right ms-1"></i></a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card shadow-sm">
                        <div class="service-icon"><i class="bi bi-activity"></i></div>
                        <h4 class="fw-bold mb-3">Chequeos Ejecutivos</h4>
                        <p class="text-muted">Evaluaciones completas en un solo día para monitorear tu salud general.</p>
                        <a href="#" class="text-decoration-none fw-bold mt-auto d-inline-block">Saber más <i class="bi bi-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer id="contacto">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4">
                    <h3 class="fw-bold mb-4 text-white">{{ $branch->razon_social }}</h3>
                    <p class="opacity-75 mb-4">Líderes en salud privada, comprometidos con la innovación médica y la recuperación pronta de nuestros pacientes.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="social-btn"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-btn"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-btn"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-4 text-white">Información de Contacto</h5>
                    <ul class="list-unstyled opacity-75">
                        <li class="mb-3 d-flex align-items-start">
                            <i class="bi bi-geo-alt-fill me-3 text-primary fs-5"></i>
                            {{ $branch->direccion }}
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                            <i class="bi bi-telephone-fill me-3 text-primary fs-5"></i>
                            {{ $branch->telefono }}
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                            <i class="bi bi-envelope-at-fill me-3 text-primary fs-5"></i>
                            contacto@clinica.com
                        </li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-4 text-white">Horario de Atención</h5>
                    <ul class="list-unstyled opacity-75">
                        <li class="d-flex justify-content-between mb-2">
                            <span>Lun - Vie:</span>
                            <span>08:00 AM - 08:00 PM</span>
                        </li>
                        <li class="d-flex justify-content-between mb-2">
                            <span>Sábados:</span>
                            <span>08:00 AM - 02:00 PM</span>
                        </li>
                        <li class="d-flex justify-content-between mb-2 text-warning">
                            <span>Emergencias:</span>
                            <span>24 Horas</span>
                        </li>
                    </ul>
                </div>
            </div>
            <hr class="my-5 opacity-10">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <small class="opacity-50">&copy; {{ date('Y') }} {{ $branch->razon_social }}. Todos los derechos reservados.</small>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <small class="opacity-50 text-uppercase">Desarrollado con Excelencia Médica</small>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>