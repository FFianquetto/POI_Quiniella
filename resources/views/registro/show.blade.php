@extends('layouts.app')

@section('template_title')
    {{ $registro->name ?? __('Show') . " " . __('Registro') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Registro</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('registros.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                                <div class="form-group mb-2 mb20">
                                    <strong>Nombre:</strong>
                                    {{ $registro->nombre }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Correo:</strong>
                                    {{ $registro->correo }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Contrasena:</strong>
                                    {{ $registro->contrasena }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Edad:</strong>
                                    {{ $registro->edad }}
                                </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
