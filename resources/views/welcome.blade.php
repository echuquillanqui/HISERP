<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $branch->razon_social ?? 'Clínica Médica' }} | Salud y Confianza</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --medical-blue: #3498db; /* Azul del logo */
            --medical-dark: #2c3e50; /* Azul oscuro del logo */
        }

        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #444; }

        /* Navbar personalizada */
        .navbar-brand img { max-height: 50px; }
        .btn-medical { background-color: var(--medical-blue); color: white; border-radius: 25px; padding: 8px 25px; font-weight: 600; }
        .btn-medical:hover { background-color: #2980b9; color: white; }

        /* Hero Section */
        .hero-section { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 80px 0; }
        .hero-title { color: var(--medical-dark); font-weight: 800; font-size: 3.5rem; }
        .hero-title span { color: var(--medical-blue); }

        /* Cards de Servicios */
        .service-card { 
            border: none; border-top: 5px solid var(--medical-blue); 
            transition: transform 0.3s shadow 0.3s; border-radius: 12px;
        }
        .service-card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        .service-icon { font-size: 2.5rem; color: var(--medical-blue); margin-bottom: 1rem; }

        /* Footer */
        footer { background-color: var(--medical-dark); color: white; padding: 50px 0 20px; }
        .footer-link { color: #bdc3c7; text-decoration: none; }
        .footer-link:hover { color: var(--medical-blue); }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                @if($branch && $branch->logo)
                    <img src="{{ asset('storage/' . $branch->logo) }}" alt="Logo" class="me-2">
                @endif
                <span class="fw-bold text-uppercase" style="color: var(--medical-dark)">{{ $branch->razon_social }}</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link px-3 fw-semibold" href="#inicio">INICIO</a></li>
                    <li class="nav-item"><a class="nav-link px-3 fw-semibold" href="#servicios">SERVICIOS</a></li>
                    <li class="nav-item"><a class="nav-link px-3 fw-semibold me-3" href="#contacto">CONTACTO</a></li>
                    <li class="nav-item">
                        <a class="btn btn-medical shadow-sm" href="{{ route('login') }}">
                            <i class="bi bi-person-lock me-1"></i> INTRANET
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <header id="inicio" class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="hero-title mb-4">Cuidamos de ti <span>y de los tuyos</span></h1>
                    <p class="lead mb-5 text-secondary">Atención médica integral con el respaldo de los mejores profesionales y tecnología de vanguardia.</p>
                    <div class="d-flex gap-3">
                        <a href="https://wa.me/{{ $branch->telefono }}" class="btn btn-success btn-lg rounded-pill px-4 py-3 shadow">
                            <i class="bi bi-whatsapp me-2"></i> Agendar Cita
                        </a>
                        <a href="#servicios" class="btn btn-outline-primary btn-lg rounded-pill px-4 py-3">Ver Servicios</a>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <img src="https://img.freepik.com/foto-gratis/doctora-sonriente-su-oficina_23-2148222513.jpg" alt="Doctor" class="img-fluid rounded-4 shadow-lg border-start border-5 border-primary">
                </div>
            </div>
        </div>
    </header>

    <section id="servicios" class="py-5">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold text-uppercase" style="color: var(--medical-dark)">Nuestras Especialidades</h2>
                <div style="height: 4px; width: 80px; background-color: var(--medical-blue); margin: 15px auto;"></div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 service-card p-4 shadow-sm text-center">
                        <div class="service-icon"><i class="bi bi-microscope"></i></div>
                        <h4 class="fw-bold">Laboratorio Clínico</h4>
                        <p class="text-muted">Análisis clínicos de alta complejidad con resultados precisos y rápidos.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 service-card p-4 shadow-sm text-center">
                        <div class="service-icon"><i class="bi bi-heart-pulse"></i></div>
                        <h4 class="fw-bold">Cardiología</h4>
                        <p class="text-muted">Especialistas en la prevención y tratamiento de enfermedades cardiovasculares.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 service-card p-4 shadow-sm text-center">
                        <div class="service-icon"><i class="bi bi-shield-check"></i></div>
                        <h4 class="fw-bold">Chequeo Integral</h4>
                        <p class="text-muted">Programas de prevención diseñados para monitorear tu salud anualmente.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer id="contacto">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4">
                    <h4 class="fw-bold mb-4">{{ $branch->razon_social }}</h4>
                    <p class="text-secondary">Comprometidos con la excelencia médica y el trato humano a nuestros pacientes desde hace más de 10 años.</p>
                </div>
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-4">Contáctanos</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-geo-alt-fill me-2 text-primary"></i> {{ $branch->direccion }}</li>
                        <li class="mb-2"><i class="bi bi-telephone-fill me-2 text-primary"></i> {{ $branch->telefono }}</li>
                        <li class="mb-2"><i class="bi bi-envelope-fill me-2 text-primary"></i> contacto@clinica.com</li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-4">Siguenos</h5>
                    <div class="d-flex gap-3">
                        <a href="#" class="btn btn-outline-light rounded-circle"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="btn btn-outline-light rounded-circle"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4 opacity-25">
            <div class="text-center">
                <small class="opacity-50">&copy; {{ date('Y') }} {{ $branch->razon_social }}. Todos los derechos reservados.</small>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>