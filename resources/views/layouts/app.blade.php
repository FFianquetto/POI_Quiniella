<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar dropdowns de Bootstrap 5
            var dropdownTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
            var dropdownList = dropdownTriggerList.map(function (dropdownTriggerEl) {
                return new bootstrap.Dropdown(dropdownTriggerEl);
            });
            
            // Asegurar que el dropdown funcione manualmente si es necesario
            document.querySelectorAll('.dropdown-toggle').forEach(function(element) {
                element.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Toggle manual del dropdown
                    var dropdownMenu = this.nextElementSibling;
                    if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                        dropdownMenu.classList.toggle('show');
                        this.setAttribute('aria-expanded', dropdownMenu.classList.contains('show'));
                    }
                });
            });
            
            // Cerrar dropdown al hacer click fuera
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown')) {
                    document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                        menu.classList.remove('show');
                    });
                    document.querySelectorAll('.dropdown-toggle[aria-expanded="true"]').forEach(function(toggle) {
                        toggle.setAttribute('aria-expanded', 'false');
                    });
                }
            });
        });
    </script>
    
    <style>
        .navbar-nav .dropdown-menu {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            margin-top: 5px;
            min-width: 200px;
        }
        
        .navbar-nav .dropdown-item {
            padding: 8px 20px;
            transition: all 0.2s ease;
        }
        
        .navbar-nav .dropdown-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }
        
        .navbar-nav .dropdown-item.text-danger:hover {
            background-color: #fee;
            color: #dc3545 !important;
        }
        
        .navbar-nav .nav-link.dropdown-toggle {
            padding: 8px 15px;
            border-radius: 25px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .navbar-nav .nav-link.dropdown-toggle:hover {
            background-color: #f8f9fa;
        }
        
        .navbar-nav .nav-link.dropdown-toggle.show {
            background-color: #e9ecef;
        }
        
        .dropdown-menu.show {
            display: block !important;
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm border-bottom">
            <div class="container">
                <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                    Fleg - Quiniela
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
                            <a class="nav-link" href="{{ route('quinielas.index') }}">Quinielas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('equipos.index') }}">Equipos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('chats.index') }}">Chats</a>
                        </li>
                    @endif
                </ul>

                <ul class="navbar-nav ms-auto">
                    @if(session('usuario_logueado'))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-user-circle me-2"></i> 
                                <span class="fw-semibold">{{ session('usuario_registrado') }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li>
                                    <a class="dropdown-item" href="{{ route('auth.dashboard') }}">
                                        <i class="fa fa-tachometer-alt me-2"></i> Mi Panel
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('auth.dashboard') }}">
                                        <i class="fa fa-user me-2"></i> Ver Perfil
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('auth.logout') }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger fw-bold">
                                            <i class="fa fa-sign-out-alt me-2"></i> Cerrar Sesión
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('auth.login') }}">
                                <i class="fa fa-sign-in-alt me-2"></i> ¡Bienvenido a la quiniela más grande de fútbol!
                            </a>
                        </li>
                    @endif
                </ul>
                </div>
            </div>
        </nav>

        <main class="py-4 container">
            @yield('content')
        </main>
    </div>
</body>
</html>
