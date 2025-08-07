@extends('layouts.app')

@section('template_title')
    {{ __('Dashboard') }} - {{ $usuario->nombre }}
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span id="card_title">
                            {{ __('Dashboard de') }} {{ $usuario->nombre }}
                        </span>
                        <div class="float-right">
                            <form action="{{ route('auth.logout') }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fa fa-sign-out"></i> {{ __('Cerrar Sesión') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Chats Recientes -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="card-title">{{ __('Chats Recientes') }}</span>
                        <a href="{{ route('chats.index') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-comments"></i> Ver Todos los Chats
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
                                <a href="{{ route('chats.show', $chat->id) }}" class="list-group-item list-group-item-action">
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
                            <a href="{{ route('chats.buscar') }}" class="btn btn-primary">
                                <i class="fa fa-plus"></i> Buscar Usuarios
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Información del Usuario -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">{{ __('Mi Perfil') }}</span>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fa fa-user-circle fa-4x text-primary"></i>
                    </div>
                    <h5 class="text-center">{{ $usuario->nombre }}</h5>
                    <p class="text-center text-muted">{{ $usuario->correo }}</p>
                    <p class="text-center"><small class="text-muted">Edad: {{ $usuario->edad }} años</small></p>
                    
                    <hr>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <h6>{{ $chats->count() }}</h6>
                            <small class="text-muted">Chats</small>
                        </div>
                        <div class="col-6">
                            <h6>{{ $usuario->participaciones->count() }}</h6>
                            <small class="text-muted">Quinielas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">{{ __('Acciones Rápidas') }}</span>
                </div>
                <div class="card-body">
                    <a href="{{ route('quinielas.index') }}" class="btn btn-primary">
                        <i class="fa fa-trophy"></i> {{ __('Ver Quinielas') }}
                    </a>
                    <a href="{{ route('equipos.index') }}" class="btn btn-success">
                        <i class="fa fa-futbol-o"></i> {{ __('Ver Equipos') }}
                    </a>
                    <a href="{{ route('chats.buscar') }}" class="btn btn-info">
                        <i class="fa fa-user-plus"></i> {{ __('Buscar Usuarios') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 