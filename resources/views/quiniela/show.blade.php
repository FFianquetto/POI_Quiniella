@extends('layouts.app')

@section('template_title')
    {{ __('Quiniela') }} - {{ $quiniela->partido->equipoLocal->nombre }} vs {{ $quiniela->partido->equipoVisitante->nombre }}
@endsection

@section('content')
<section class="content container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="float-left">
                        <span class="card-title">{{ __('Quiniela del Partido') }}</span>
                    </div>
                    <div class="float-right">
                        <a class="btn btn-primary" href="{{ route('quinielas.index') }}"> {{ __('Volver') }}</a>
                    </div>
                </div>
                <div class="card-body">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Información del Partido</h5>
                                </div>
                                <div class="card-body">
                                    <div class="text-center">
                                        <h3>{{ $quiniela->partido->equipoLocal->nombre }} vs {{ $quiniela->partido->equipoVisitante->nombre }}</h3>
                                        <p class="text-muted">{{ $quiniela->partido->liga }}</p>
                                        <p><strong>Fecha:</strong> {{ $quiniela->partido->fecha_hora->format('d/m/Y H:i') }}</p>
                                        @if($quiniela->partido->estadio)
                                            <p><strong>Estadio:</strong> {{ $quiniela->partido->estadio }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Información de la Quiniela</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Participantes:</strong> {{ $quiniela->participantes->count() }}</p>
                                    <p><strong>Fecha Límite:</strong> {{ $quiniela->fecha_limite->format('d/m/Y H:i') }}</p>
                                    <p><strong>Puntos por Ganar:</strong> {{ $quiniela->puntos_ganador }}</p>
                                    <p><strong>Estado:</strong> 
                                        @if($quiniela->estado === 'activa')
                                            <span class="badge bg-success">Activa</span>
                                        @elseif($quiniela->estado === 'cerrada')
                                            <span class="badge bg-warning">Cerrada</span>
                                        @else
                                            <span class="badge bg-secondary">Finalizada</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($quiniela->estado === 'finalizada' && $quiniela->resultado_final)
                        <div class="alert alert-info mt-3">
                            <h5>Resultado Final</h5>
                            <p>
                                @if($quiniela->resultado_final === 'local')
                                    <strong>Ganador: {{ $quiniela->partido->equipoLocal->nombre }}</strong>
                                @elseif($quiniela->resultado_final === 'visitante')
                                    <strong>Ganador: {{ $quiniela->partido->equipoVisitante->nombre }}</strong>
                                @else
                                    <strong>Resultado: Empate</strong>
                                @endif
                            </p>
                        </div>
                    @endif

                    @if(!$participante && $quiniela->isActiva())
                        <div class="text-center mt-3">
                            <form action="{{ route('quinielas.participar', $quiniela->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fa fa-plus"></i> Participar en esta Quiniela
                                </button>
                            </form>
                        </div>
                    @endif

                    @if($participante)
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5>Mi Apuesta</h5>
                            </div>
                            <div class="card-body">
                                @if($miPrediccion)
                                    <div class="alert alert-info">
                                        <strong>Tu predicción actual:</strong> 
                                        @if($miPrediccion->prediccion === 'local')
                                            {{ $quiniela->partido->equipoLocal->nombre }}
                                        @elseif($miPrediccion->prediccion === 'visitante')
                                            {{ $quiniela->partido->equipoVisitante->nombre }}
                                        @else
                                            Empate
                                        @endif
                                    </div>
                                @endif

                                @if($quiniela->isActiva())
                                    <form action="{{ route('quinielas.prediccion', $quiniela->id) }}" method="POST">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="card">
                                                    <div class="card-body text-center">
                                                        <h6>{{ $quiniela->partido->equipoLocal->nombre }}</h6>
                                                        <input type="radio" name="prediccion" value="local" id="local" 
                                                               {{ $miPrediccion && $miPrediccion->prediccion === 'local' ? 'checked' : '' }}>
                                                        <label for="local" class="btn btn-outline-primary w-100">Gana Local</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card">
                                                    <div class="card-body text-center">
                                                        <h6>Empate</h6>
                                                        <input type="radio" name="prediccion" value="empate" id="empate"
                                                               {{ $miPrediccion && $miPrediccion->prediccion === 'empate' ? 'checked' : '' }}>
                                                        <label for="empate" class="btn btn-outline-warning w-100">Empate</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card">
                                                    <div class="card-body text-center">
                                                        <h6>{{ $quiniela->partido->equipoVisitante->nombre }}</h6>
                                                        <input type="radio" name="prediccion" value="visitante" id="visitante"
                                                               {{ $miPrediccion && $miPrediccion->prediccion === 'visitante' ? 'checked' : '' }}>
                                                        <label for="visitante" class="btn btn-outline-primary w-100">Gana Visitante</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center mt-3">
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                {{ $miPrediccion ? 'Actualizar Predicción' : 'Hacer Predicción' }}
                                            </button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($estadisticas['total'] > 0)
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5>Estadísticas de Apuestas</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 text-center">
                                        <h4>{{ $estadisticas['local'] }}</h4>
                                        <p>{{ $quiniela->partido->equipoLocal->nombre }}</p>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: {{ $estadisticas['porcentaje_local'] }}%">
                                                {{ $estadisticas['porcentaje_local'] }}%
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <h4>{{ $estadisticas['empate'] }}</h4>
                                        <p>Empate</p>
                                        <div class="progress">
                                            <div class="progress-bar bg-warning" style="width: {{ $estadisticas['porcentaje_empate'] }}%">
                                                {{ $estadisticas['porcentaje_empate'] }}%
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <h4>{{ $estadisticas['visitante'] }}</h4>
                                        <p>{{ $quiniela->partido->equipoVisitante->nombre }}</p>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" style="width: {{ $estadisticas['porcentaje_visitante'] }}%">
                                                {{ $estadisticas['porcentaje_visitante'] }}%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</section>
@endsection
