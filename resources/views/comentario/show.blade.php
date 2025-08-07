@extends('layouts.app')

@section('template_title')
    Ver Mensaje
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-default">
                    <div class="card-header">
                        <span class="card-title">Detalles del Mensaje</span>
                        <div class="float-right">
                            <a class="btn btn-primary" href="{{ route('comentarios.index') }}"> Volver</a>
                        </div>
                    </div>
                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>De:</strong>
                            {{ $comentario->emisor->nombre ?? 'Sin emisor' }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Para:</strong>
                            {{ $comentario->receptor->nombre ?? 'Sin receptor' }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Mensaje:</strong>
                            <div class="alert alert-light border">
                                {{ $comentario->mensaje }}
                            </div>
                        </div>
                        @if($comentario->link)
                        <div class="form-group mb-2 mb20">
                            <strong>Link:</strong>
                            <a href="{{ $comentario->link }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-external-link"></i> {{ $comentario->link }}
                            </a>
                        </div>
                        @endif
                        <div class="form-group mb-2 mb20">
                            <strong>Fecha de envío:</strong>
                            {{ $comentario->created_at->format('d/m/Y H:i') }}
                        </div>

                        @php
                            $usuarioActual = session('registro_id');
                            $esReceptor = $usuarioActual == $comentario->registro_id_receptor;
                            $esEmisor = $usuarioActual == $comentario->registro_id_emisor;
                        @endphp

                        <div class="form-group mb-2 mb20">
                            @if($esReceptor)
                                <a href="{{ route('comentarios.create', ['emisor_id' => session('registro_id'), 'receptor_id' => $comentario->emisor->id]) }}" class="btn btn-primary">
                                    <i class="fa fa-reply"></i> Contestar a {{ $comentario->emisor->nombre }}
                                </a>
                            @elseif($esEmisor)
                                <span class="text-muted">Este es tu mensaje enviado</span>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
