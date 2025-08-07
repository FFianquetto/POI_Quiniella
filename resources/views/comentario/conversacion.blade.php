@extends('layouts.app')

@section('template_title')
    Conversación con {{ $otroUsuarioData->nombre }}
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span id="card_title">
                                <i class="fa fa-comments"></i> Conversación con {{ $otroUsuarioData->nombre }}
                            </span>

                            <div class="float-right">
                                <a href="{{ route('comentarios.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fa fa-arrow-left"></i> Volver
                                </a>
                                <a href="{{ route('comentarios.create', ['emisor_id' => $usuarioActual, 'receptor_id' => $otroUsuarioData->id]) }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-reply"></i> Nuevo Mensaje
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body bg-white">
                        @if($conversacion->count() > 0)
                            <div class="chat-container" style="max-height: 500px; overflow-y: auto;">
                                @foreach($conversacion as $mensaje)
                                    @php
                                        $esMio = $mensaje->registro_id_emisor == $usuarioActual;
                                    @endphp
                                    
                                    <div class="mb-3 {{ $esMio ? 'text-end' : 'text-start' }}">
                                        <div class="d-inline-block {{ $esMio ? 'bg-primary text-white' : 'bg-light' }} p-3 rounded" style="max-width: 70%;">
                                            <div class="small mb-1 {{ $esMio ? 'text-white-50' : 'text-muted' }}">
                                                <strong>{{ $mensaje->emisor->nombre }}</strong>
                                                <span class="ms-2">{{ $mensaje->created_at->format('d/m/Y H:i') }}</span>
                                            </div>
                                            <div class="mb-2">
                                                {{ $mensaje->mensaje }}
                                            </div>
                                            @if($mensaje->link)
                                                <div class="small">
                                                    <a href="{{ $mensaje->link }}" target="_blank" class="{{ $esMio ? 'text-white' : 'text-primary' }}">
                                                        <i class="fa fa-external-link"></i> Link adjunto
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info text-center">
                                <i class="fa fa-info-circle"></i> No hay mensajes en esta conversación.
                                <br>
                                <a href="{{ route('comentarios.create', ['emisor_id' => $usuarioActual, 'receptor_id' => $otroUsuarioData->id]) }}" class="btn btn-primary mt-2">
                                    <i class="fa fa-paper-plane"></i> Enviar primer mensaje
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 