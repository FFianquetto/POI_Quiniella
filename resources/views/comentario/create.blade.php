@extends('layouts.app')

@section('template_title')
    Enviar Mensaje
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">

                <div class="card card-default">
                    <div class="card-header">
                        <span class="card-title">Enviar Mensaje</span>
                    </div>
                    <div class="card-body bg-white">
                        @if($emisor && $receptor)
                            <div class="alert alert-info mb-3">
                                <strong>De:</strong> {{ $emisor->nombre }} ({{ $emisor->correo }})
                                <br><strong>Para:</strong> {{ $receptor->nombre }} ({{ $receptor->correo }})
                                @if($publicacion)
                                    <br><strong>Sobre el servicio:</strong> {{ $publicacion->titulo }}
                                @endif
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ route('comentarios.store') }}" role="form" enctype="multipart/form-data">
                            @csrf
                            
                            @if($emisor)
                                <input type="hidden" name="emisor_id" value="{{ $emisor->id }}">
                            @endif
                            
                            @if($receptor)
                                <input type="hidden" name="receptor_id" value="{{ $receptor->id }}">
                            @endif

                            @include('comentario.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
