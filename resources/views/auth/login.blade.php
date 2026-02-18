@extends('layouts.app')

@section('content')
<style>
    /* Eliminamos cualquier margen que pueda venir del layout para el login */
    .login-wrapper {
        height: 100vh;
        width: 100vw;
        display: flex;
        margin: 0;
        padding: 0;
        overflow: hidden;
    }

    .login-image {
        background-image: url('https://images.unsplash.com/photo-1579684385127-1ef15d508118?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80');
        background-size: cover;
        background-position: center;
        width: 50%;
        position: relative;
    }

    /* Superposición para dar el tono celeste profesional a la imagen */
    .login-image::after {
        content: "";
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(135deg, rgba(30, 58, 138, 0.7) 0%, rgba(59, 130, 246, 0.2) 100%);
    }

    .login-form-side {
        width: 50%;
        background-color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px;
    }

    .login-box {
        width: 100%;
        max-width: 400px;
    }

    @media (max-width: 768px) {
        .login-image { display: none; }
        .login-form-side { width: 100%; }
    }
</style>

<div class="login-wrapper" x-data="{ showPass: false }">
    <div class="login-image d-none d-md-block">
        <div class="position-absolute bottom-0 start-0 p-5 text-white" style="z-index: 2;">
            <h1 class="fw-bold">Software Médico</h1>
            <p class="lead">Gestión eficiente para el cuidado de sus pacientes.</p>
        </div>
    </div>

    <div class="login-form-side">
        <div class="login-box">
            <div class="text-center mb-5">
                <h2 class="fw-bold" style="color: #1e3a8a;">Bienvenido</h2>
                <p class="text-muted small text-uppercase fw-semibold">Introduce tus credenciales</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-4">
                    <label class="form-label small fw-bold text-secondary">USUARIO O CORREO</label>
                    <input type="text" name="login" 
                           class="form-control form-control-lg border-0 bg-light @error('login') is-invalid @enderror" 
                           style="border-radius: 10px;"
                           value="{{ old('login') }}" required autofocus>
                    @error('login') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-secondary">CONTRASEÑA</label>
                    <div class="input-group bg-light" style="border-radius: 10px;">
                        <input :type="showPass ? 'text' : 'password'" name="password" 
                               class="form-control form-control-lg border-0 bg-transparent @error('password') is-invalid @enderror" 
                               required>
                        <button class="btn border-0 text-muted" type="button" @click="showPass = !showPass">
                            <i class="bi" :class="showPass ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small text-muted" for="remember">Recordarme</label>
                    </div>
                    @if (Route::has('password.request'))
                        <a class="small text-decoration-none fw-bold" style="color: #3182ce;" href="{{ route('password.request') }}">
                            ¿Olvidó su clave?
                        </a>
                    @endif
                </div>

                <button type="submit" class="btn btn-lg w-100 text-white shadow-sm" 
                        style="background-color: #1e3a8a; border-radius: 10px; font-weight: 600;">
                    Acceder al Sistema
                </button>
            </form>
        </div>
    </div>
</div>
@endsection