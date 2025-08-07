@extends('layouts.app')

@section('template_title')
    Ver Servicio
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-default">
                    <div class="card-header">
                        <span class="card-title">Detalles del Servicio</span>
                        <div class="float-right">
                            <a class="btn btn-primary" href="{{ route('publicaciones.index') }}"> Volver</a>
                        </div>
                    </div>
                    <div class="card-body bg-white">
                        
                        <div class="form-group mb-2 mb20">
                            <strong>Servicio:</strong>
                            {{ $publicacione->titulo }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Descripción:</strong>
                            {{ $publicacione->contenido }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Publicado por:</strong>
                            {{ $publicacione->autor->nombre ?? 'Sin autor' }}
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Contactar al proveedor:</strong>
                            @if($publicacione->autor)
                                @php
                                    $usuarioActual = session('registro_id');
                                    $esPropiaPublicacion = $usuarioActual && $publicacione->registro_id == $usuarioActual;
                                @endphp
                                
                                @if(!$esPropiaPublicacion)
                                    <a href="{{ route('comentarios.create', ['emisor_id' => session('registro_id'), 'receptor_id' => $publicacione->autor->id, 'publicacion_id' => $publicacione->id]) }}" class="btn btn-info">
                                        <i class="fa fa-envelope"></i> Contactar a {{ $publicacione->autor->nombre }}
                                    </a>
                                @else
                                    <span class="text-muted">Este es tu servicio</span>
                                @endif
                            @else
                                <span class="text-muted">Proveedor no disponible</span>
                            @endif
                        </div>
                        <div class="form-group mb-2 mb20">
                            <strong>Fecha de publicación:</strong>
                            {{ $publicacione->created_at->format('d/m/Y H:i') }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
