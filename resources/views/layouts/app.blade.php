<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Fleg Football') }}</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS desde CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    @vite(['resources/sass/app.scss', 'resources/css/app.css', 'resources/css/futbol.css', 'resources/js/app.js'])
    
    <!-- CSS de formularios con prioridad máxima -->
    <link rel="stylesheet" href="{{ asset('css/forms.css') }}">
    
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ELIMINAR EFECTOS DE HOVER PROBLEMÁTICOS
            // Deshabilitar cualquier efecto de hover en el navbar
            const navbar = document.querySelector('.navbar');
            if (navbar) {
                // Eliminar cualquier evento de hover que pueda estar causando problemas
                navbar.removeEventListener('mouseenter', function() {});
                navbar.removeEventListener('mouseleave', function() {});
                
                // Asegurar que no haya transformaciones
                navbar.style.transform = 'none';
                navbar.style.transition = 'none';
            }
            
            // Deshabilitar efectos de hover en todos los enlaces del navbar
            document.querySelectorAll('.navbar-nav .nav-link').forEach(function(link) {
                link.addEventListener('mouseenter', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    // No hacer nada en hover
                });
                
                link.addEventListener('mouseleave', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    // No hacer nada al salir del hover
                });
            });
        });
    </script>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <i class="fas fa-futbol me-2"></i>
                    Fleg Football
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarSupportedContent"
                        aria-controls="navbarSupportedContent"
                        aria-expanded="false"
                        aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto">
                    @if(session('usuario_logueado'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('quinielas.*') ? 'nav-link-active' : '' }}" href="{{ route('quinielas.index') }}">
                                <i class="fas fa-trophy me-1"></i> Quinielas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('torneo.*') ? 'nav-link-active' : '' }}" href="{{ route('torneo.index') }}">
                                <i class="fas fa-sitemap me-1"></i> Torneo
                            </a>
                        </li>
                    @endif
                </ul>

                <ul class="navbar-nav ms-auto">
                    @if(session('usuario_logueado'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('chat.*') ? 'nav-link-active' : '' }}" href="{{ route('chat.index') }}">
                                <i class="fas fa-comments me-1"></i> Chats
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('auth.dashboard') }}">
                                <i class="fas fa-user-circle me-2"></i> 
                                <span class="fw-semibold">{{ session('usuario_registrado') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <form action="{{ route('auth.logout') }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="nav-link btn btn-link p-0">
                                    <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" >
                                ¡Bienvenido a la quiniela más grande de fútbol!
                            </a>
                        </li>
                    @endif
                </ul>
                </div>
            </div>
        </nav>

        <!-- Contenido Principal -->
        <main class="py-4 container">
            @yield('content')
        </main>


        <footer class="bg-dark text-white text-center py-3">
            <div class="container">
                <p class="mb-0">&copy; {{ date('Y') }} Fleg Football. Todos los derechos reservados.</p>
            </div>
        </footer>
    </div>

</body>
</html>
