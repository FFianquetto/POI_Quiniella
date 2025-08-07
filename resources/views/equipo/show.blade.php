@extends('layouts.app')

@section('template_title')
    {{ __('Equipo') }} - {{ $equipo->nombre }}
@endsection

@section('content')
<section class="content container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="float-left">
                        <span class="card-title">{{ __('Detalles del Equipo') }}</span>
                    </div>
                    <div class="float-right">
                        <a class="btn btn-primary" href="{{ route('equipos.index') }}"> {{ __('Volver') }}</a>
                    </div>
                </div>
                <div class="card-body">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Información del Equipo</h5>
                                </div>
                                <div class="card-body">
                                    <div class="text-center">
                                        @if($equipo->logo)
                                            <img src="{{ $equipo->logo }}" alt="{{ $equipo->nombre }}" class="img-fluid mb-3" style="max-height: 150px;">
                                        @endif
                                        <h3>{{ $equipo->nombre }}</h3>
                                        <p class="text-muted">{{ $equipo->liga }}</p>
                                        <p><strong>Ciudad:</strong> {{ $equipo->ciudad }}</p>
                                        <p><strong>Abreviación:</strong> <span class="badge bg-primary">{{ $equipo->abreviacion }}</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Estadísticas</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Partidos como Local:</strong> {{ $equipo->partidosLocal->count() }}</p>
                                    <p><strong>Partidos como Visitante:</strong> {{ $equipo->partidosVisitante->count() }}</p>
                                    <p><strong>Total de Partidos:</strong> {{ $equipo->partidosLocal->count() + $equipo->partidosVisitante->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection
