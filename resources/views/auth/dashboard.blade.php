@extends('layouts.app')

@section('template_title')
    {{ __('Dashboard') }} - {{ $usuario->nombre }}
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header de la página -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="world-cup-header dashboard-header">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h1 class="mb-1">
                            <i class="fas fa-user-circle me-3"></i>
                            Dashboard de {{ $usuario->nombre }}
                        </h1>
                        <p class="mb-0">
                            Bienvenido a tu panel de control. Gestiona tus quinielas, participa en torneos y mantén contacto con otros usuarios.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Chats Recientes -->
        <div class="col-md-8">
            <div class="card mundial-card">
                <div class="card-header bg-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-white">
                            <i class="fas fa-comments me-2"></i>
                            Chats Recientes
                        </span>
                        <a href="{{ route('chat.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-comments me-1"></i> Ver Todos los Chats
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($chats->count() > 0)
                        <div class="list-group">
                            @foreach($chats as $chat)
                                @php
                                    $otroUsuario = $chat->otroUsuario($usuario->id);
                                    $ultimoMensaje = $chat->ultimoMensaje;
                                @endphp
                                <a href="{{ route('chat.show', $chat->id) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">
                                            @if($otroUsuario)
                                                {{ $otroUsuario->nombre }}
                                            @else
                                                Chat Grupal
                                            @endif
                                        </h6>
                                        <small class="text-muted">
                                            @if($ultimoMensaje)
                                                {{ $ultimoMensaje->created_at->diffForHumans() }}
                                            @endif
                                        </small>
                                    </div>
                                    @if($ultimoMensaje)
                                        <p class="mb-1">
                                            <strong>{{ $ultimoMensaje->emisor->nombre }}:</strong>
                                            @if($ultimoMensaje->tipo === 'texto')
                                                {{ Str::limit($ultimoMensaje->contenido, 50) }}
                                            @else
                                                <i class="fa fa-file"></i> {{ ucfirst($ultimoMensaje->tipo) }}
                                            @endif
                                        </p>
                                    @else
                                        <p class="mb-1 text-muted">No hay mensajes aún</p>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fa fa-comments fa-3x text-muted mb-3"></i>
                            <h5>No tienes chats aún</h5>
                            <p class="text-muted">Comienza una conversación con otros usuarios</p>
                            <a href="{{ route('chat.buscar') }}" class="btn btn-primary">
                                <i class="fa fa-plus"></i> Buscar Usuarios
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Acciones Rápidas -->
            <div class="card mundial-card mt-3" style="max-width: 900px;">
                <div class="card-header bg-info">
                    <span class="text-white">
                        <i class="fas fa-bolt me-2"></i>
                        Acciones Rápidas
                    </span>
                </div>
                <div class="card-body">
                    <a href="{{ route('quinielas.index') }}" class="btn btn-primary">
                        <i class="fa fa-trophy"></i> {{ __('Ver Quinielas') }}
                    </a>
                    <a href="{{ route('torneo.index') }}" class="btn btn-success">
                        <i class="fa fa-globe"></i> {{ __('Ver Torneo') }}
                    </a>
                    <a href="{{ route('chat.buscar') }}" class="btn btn-info">
                        <i class="fa fa-user-plus"></i> {{ __('Buscar Usuarios') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Información del User -->
        <div class="col-md-4 mb-4">
            <div class="card mundial-card">
                <div class="card-header bg-primary">
                    <span class="text-white">
                        <i class="fas fa-user-circle me-2"></i>
                        Mi Perfil
                    </span>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fa fa-user-circle fa-4x text-primary"></i>
                    </div>
                    <h5 class="text-center">{{ $usuario->nombre }}</h5>
                    <p class="text-center text-muted">{{ $usuario->correo }}</p>
                    <p class="text-center"><small class="text-muted">Edad: {{ $usuario->edad }} años</small></p>
                    
                    <!-- Rango/Medalla del Usuario -->
                    <div class="mb-3 p-3 rounded border text-center" style="background: linear-gradient(135deg, {{ $rango['color'] }}15 0%, {{ $rango['color'] }}05 100%); border-color: {{ $rango['color'] }} !important;">
                        <div class="mb-2">
                            <i class="fas {{ $rango['icono'] }} fa-3x" style="color: {{ $rango['color'] }};"></i>
                        </div>
                        <h4 class="mb-1 fw-bold" style="color: {{ $rango['color'] }};">
                            {{ $rango['nombre'] }}
                        </h4>
                        <small class="text-muted d-block">{{ $rango['descripcion'] }}</small>
                        @if($rango['puntos_siguiente'])
                            @php
                                $puntosParaSiguiente = $rango['puntos_siguiente'] - $puntosAcumulados;
                                $siguienteRango = \App\Models\UserTotalPoint::calcularRango($rango['puntos_siguiente']);
                            @endphp
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-arrow-up me-1"></i>
                                {{ $puntosParaSiguiente }} puntos para {{ $siguienteRango['nombre'] }}
                            </small>
                        @else
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-star me-1"></i>
                                Rango máximo alcanzado
                            </small>
                        @endif
                    </div>
                    
                    <hr>
                    
                    <!-- Puntos de la Última Quiniela -->
                    @if($puntosUltimaQuiniela > 0)
                    <div class="mb-3 p-3 rounded border" style="background-color: #fff3e0; border-color: #ff9800 !important;">
                        <div class="text-center">
                            <h4 class="mb-1" style="color: #ff9800;">
                                <i class="fas fa-trophy me-2" style="color: #ff9800;"></i>
                                {{ $puntosUltimaQuiniela }}
                            </h4>
                            <small class="text-muted d-block fw-semibold">Última Quiniela</small>
                            @if($nombreUltimaQuiniela)
                                <small class="text-muted d-block">{{ $nombreUltimaQuiniela }}</small>
                            @endif
                            @if($fechaUltimaQuiniela)
                                <small class="text-muted">{{ \Carbon\Carbon::parse($fechaUltimaQuiniela)->format('d/m/Y') }}</small>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    <!-- Puntos Acumulados Globales -->
                    <div class="mb-3 p-3 bg-light rounded">
                        <div class="text-center">
                            <h3 class="text-success mb-1">
                                <i class="fas fa-trophy me-2"></i>
                                {{ $puntosAcumulados ?? 0 }}
                            </h3>
                            <small class="text-muted d-block fw-semibold">Puntos Acumulados Globales</small>
                            <small class="text-muted">({{ $partidosAcertados ?? 0 }} partidos acertados en total)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 