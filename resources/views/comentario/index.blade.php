@extends('layouts.app')

@section('template_title')
    Mis Mensajes
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span id="card_title">
                                {{ __('Mis Mensajes') }}
                            </span>

                            <div class="float-right">
                                <a href="{{ route('comentarios.create') }}" class="btn btn-primary btn-sm float-right" data-placement="left">
                                  {{ __('Nuevo Mensaje') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    @if(session('success'))
                        <div class="alert alert-success m-4">
                            <i class="fa fa-check-circle"></i> {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger m-4">
                            <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
                        </div>
                    @endif
                    
                    <div class="card-body bg-white">
                        <div class="row">
                            <!-- Mensajes Recibidos -->
                            <div class="col-md-6">
                                <h5 class="text-primary mb-3">
                                    <i class="fa fa-inbox"></i> Mensajes Recibidos ({{ $mensajesRecibidos->count() }})
                                </h5>
                                
                                @if($mensajesRecibidos->count() > 0)
                                    <div class="list-group">
                                        @foreach($mensajesRecibidos as $mensaje)
                                            <div class="list-group-item">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1">
                                                        <i class="fa fa-user"></i> 
                                                        {{ $mensaje->emisor->nombre }}
                                                    </h6>
                                                    <small class="text-muted">
                                                        {{ $mensaje->created_at->format('d/m/Y H:i') }}
                                                    </small>
                                                </div>
                                                <p class="mb-1">{{ $mensaje->mensaje }}</p>
                                                @if($mensaje->link)
                                                    <small><a href="{{ $mensaje->link }}" target="_blank">Link adjunto</a></small>
                                                @endif
                                                <div class="mt-2">
                                                    <a href="{{ route('comentarios.create', ['emisor_id' => session('registro_id'), 'receptor_id' => $mensaje->emisor->id]) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fa fa-reply"></i> Contestar
                                                    </a>
                                                    <a href="{{ route('comentarios.conversacion', [session('registro_id'), $mensaje->emisor->id]) }}" class="btn btn-sm btn-outline-success">
                                                        <i class="fa fa-comments"></i> Ver Chat
                                                    </a>
                                                    <a href="{{ route('comentarios.show', $mensaje->id) }}" class="btn btn-sm btn-outline-info">
                                                        <i class="fa fa-eye"></i> Ver
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i> No tienes mensajes recibidos.
                                    </div>
                                @endif
                            </div>

                            <!-- Mensajes Enviados -->
                            <div class="col-md-6">
                                <h5 class="text-success mb-3">
                                    <i class="fa fa-paper-plane"></i> Mensajes Enviados ({{ $mensajesEnviados->count() }})
                                </h5>
                                
                                @if($mensajesEnviados->count() > 0)
                                    <div class="list-group">
                                        @foreach($mensajesEnviados as $mensaje)
                                            <div class="list-group-item">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1">
                                                        <i class="fa fa-user"></i> 
                                                        Para: {{ $mensaje->receptor->nombre }}
                                                    </h6>
                                                    <small class="text-muted">
                                                        {{ $mensaje->created_at->format('d/m/Y H:i') }}
                                                    </small>
                                                </div>
                                                <p class="mb-1">{{ $mensaje->mensaje }}</p>
                                                @if($mensaje->link)
                                                    <small><a href="{{ $mensaje->link }}" target="_blank">Link adjunto</a></small>
                                                @endif
                                                <div class="mt-2">
                                                    <a href="{{ route('comentarios.conversacion', [session('registro_id'), $mensaje->receptor->id]) }}" class="btn btn-sm btn-outline-success">
                                                        <i class="fa fa-comments"></i> Ver Chat
                                                    </a>
                                                    <a href="{{ route('comentarios.show', $mensaje->id) }}" class="btn btn-sm btn-outline-info">
                                                        <i class="fa fa-eye"></i> Ver
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i> No has enviado mensajes.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
