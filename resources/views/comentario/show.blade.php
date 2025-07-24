@extends('layouts.app')

@section('template_title')
    {{ $comentario->name ?? __('Show') . " " . __('Comentario') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Comentario</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('comentarios.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                                <div class="form-group mb-2 mb20">
                                    <strong>Registro Id Emisor:</strong>
                                    {{ $comentario->registro_id_emisor }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Registro Id Receptor:</strong>
                                    {{ $comentario->registro_id_receptor }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Mensaje:</strong>
                                    {{ $comentario->mensaje }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Link:</strong>
                                    {{ $comentario->link }}
                                </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
