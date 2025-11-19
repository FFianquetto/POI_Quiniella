@extends('layouts.app')

@section('template_title')
    {{ __('Mis Chats') }}
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header de la página -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="world-cup-header chat-header">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h1 class="mb-1">
                            <i class="fas fa-comments me-3"></i>
                            Mis Chats
                        </h1>
                        <p class="mb-0">
                            Mantén contacto con otros usuarios y participa en grupos. Inicia conversaciones y comparte contenido con la comunidad.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="card mundial-card">
                <div class="card-header bg-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-white">
                            <i class="fas fa-comments me-2"></i>
                            Mis Chats
                        </span>
                        <div>
                            <a href="{{ route('chat.buscar') }}" class="btn btn-light btn-sm me-2">
                                <i class="fas fa-plus me-1"></i> Nuevo Chat
                            </a>
                            <a href="{{ route('chat.grupo.create') }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-users me-1"></i> Crear Grupo
                            </a>
                        </div>
                    </div>
                </div>
                @if ($message = Session::get('success'))
                    <div class="alert alert-success">
                        <p>{{ $message }}</p>
                    </div>
                @endif

                <div class="card-body">
                    @if($chats->count() > 0)
                        <div class="list-group">
                            @foreach ($chats as $chat)
                                @php
                                    $otroUsuario = $chat->otroUsuario($usuario->id);
                                    $ultimoMensaje = $chat->ultimoMensaje;
                                @endphp
                                <a href="{{ $chat->esGrupal() ? route('chat.grupo.show', $chat->id) : route('chat.show', $chat->id) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between flex-wrap gap-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <h6 class="mb-0">
                                                @if($chat->esGrupal())
                                                    <i class="fa fa-users text-primary me-1"></i>
                                                    {{ $chat->nombre }}
                                                @elseif($otroUsuario)
                                                    {{ $otroUsuario->nombre }}
                                                @else
                                                    Chat Individual
                                                @endif
                                            </h6>
                                        </div>
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
                            <a href="{{ route('chat.grupo.create') }}" class="btn btn-success ms-2">
                                <i class="fa fa-users"></i> Crear Grupo
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
