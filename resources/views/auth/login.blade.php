@extends('layouts.app')

@section('template_title')
    {{ __('Iniciar Sesión') }}
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Iniciar Sesión') }}</div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('auth.login.post') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="correo" class="col-md-4 col-form-label text-md-end">{{ __('Correo Electrónico') }}</label>

                            <div class="col-md-6">
                                <input id="correo" type="email" class="form-control @error('correo') is-invalid @enderror" 
                                       name="correo" value="{{ old('correo') }}" required autocomplete="email" autofocus>

                                @error('correo')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="contrasena" class="col-md-4 col-form-label text-md-end">{{ __('Contraseña') }}</label>

                            <div class="col-md-6 position-relative">
                                <input id="contrasena" type="password" class="form-control @error('contrasena') is-invalid @enderror" 
                                       name="contrasena" required autocomplete="current-password" style="padding-right: 40px;">
                                <i class="fas fa-eye position-absolute" id="togglePasswordLogin" style="right: 45px; top: 50%; transform: translateY(-50%); cursor: pointer; z-index: 8;" id="toggleIconLogin"></i>

                                @error('contrasena')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Iniciar Sesión') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="row mt-3">
                        <div class="col-md-8 offset-md-4">
                            <p class="mb-0">
                                ¿No tienes cuenta? 
                                <a href="{{ route('registros.create') }}" class="text-decoration-none">
                                    Regístrate aquí
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.getElementById('togglePasswordLogin');
    const passwordInput = document.getElementById('contrasena');
    const toggleIcon = document.getElementById('toggleIconLogin');
    
    togglePassword.addEventListener('click', function() {
        // Cambiar el tipo de input
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    });
});
</script>
@endsection
