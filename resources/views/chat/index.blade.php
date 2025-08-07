@extends('layouts.app')

@section('template_title')
    {{ __('Mis Chats') }}
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span id="card_title">
                            {{ __('Mis Chats') }}
                        </span>
                        <div>
                            <a href="{{ route('chats.buscar') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Nuevo Chat
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
    </div>
</div>
@endsection
