@extends('layouts.app')

@section('template_title')
    {{ __('Ranking Global') }} - Top 10 Mejores Participantes
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header de la página -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="world-cup-header dashboard-header">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h1 class="mb-1">
                            <i class="fas fa-trophy me-3"></i>
                            Ranking Global - Top 10
                        </h1>
                        <p class="mb-0">
                            Los mejores participantes con las puntuaciones más altas en el sistema de quinielas.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mundial-card">
                <div class="card-header bg-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-white">
                            <i class="fas fa-medal me-2"></i>
                            Top 10 Mejores Participantes
                        </span>
                        @if($usuarioId && $posicionUsuario)
                        <span class="badge bg-light text-dark fs-6">
                            <i class="fas fa-user me-1"></i>
                            Tu posición: #{{ $posicionUsuario }}
                        </span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($top10->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;" class="text-center">Posición</th>
                                        <th>Participante</th>
                                        <th class="text-center" style="width: 150px;">Puntos Totales</th>
                                        <th class="text-center" style="width: 150px;">Partidos Acertados</th>
                                        <th class="text-center" style="width: 150px;">Rango</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($top10 as $index => $participante)
                                        @php
                                            $posicion = $index + 1;
                                            $rango = \App\Models\UserTotalPoint::calcularRango($participante->puntos_totales);
                                            $esUsuarioActual = $usuarioId && $participante->registro_id == $usuarioId;
                                        @endphp
                                        <tr class="{{ $esUsuarioActual ? 'table-warning' : '' }}" style="{{ $esUsuarioActual ? 'font-weight: 600;' : '' }}">
                                            <td class="text-center">
                                                @if($posicion == 1)
                                                    <div class="d-flex flex-column align-items-center">
                                                        <i class="fas fa-trophy fa-2x" style="color: #FFD700;"></i>
                                                        <span class="badge bg-warning text-dark mt-1">1°</span>
                                                    </div>
                                                @elseif($posicion == 2)
                                                    <div class="d-flex flex-column align-items-center">
                                                        <i class="fas fa-trophy fa-2x" style="color: #C0C0C0;"></i>
                                                        <span class="badge bg-secondary mt-1">2°</span>
                                                    </div>
                                                @elseif($posicion == 3)
                                                    <div class="d-flex flex-column align-items-center">
                                                        <i class="fas fa-trophy fa-2x" style="color: #CD7F32;"></i>
                                                        <span class="badge bg-danger mt-1">3°</span>
                                                    </div>
                                                @else
                                                    <span class="badge bg-light text-dark fs-6">#{{ $posicion }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <i class="fas fa-user-circle fa-2x" style="color: {{ $rango['color'] }};"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">
                                                            {{ $participante->usuario ? $participante->usuario->nombre : 'Usuario #' . $participante->registro_id }}
                                                            @if($esUsuarioActual)
                                                                <span class="badge bg-warning text-dark ms-2">Tú</span>
                                                            @endif
                                                        </h6>
                                                        @if($participante->usuario)
                                                            <small class="text-muted">{{ $participante->usuario->correo }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success fs-6 px-3 py-2">
                                                    <i class="fas fa-star me-1"></i>
                                                    {{ number_format($participante->puntos_totales, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info fs-6 px-3 py-2">
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    {{ $participante->partidos_acertados ?? 0 }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="fas {{ $rango['icono'] }} fa-lg mb-1" style="color: {{ $rango['color'] }};"></i>
                                                    <small class="fw-bold" style="color: {{ $rango['color'] }};">
                                                        {{ $rango['nombre'] }}
                                                    </small>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($usuarioId && $posicionUsuario && $posicionUsuario > 10)
                        <div class="alert alert-info mt-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle fa-2x me-3"></i>
                                <div>
                                    <h6 class="mb-1">Tu posición en el ranking</h6>
                                    <p class="mb-0">
                                        Estás en la posición <strong>#{{ $posicionUsuario }}</strong> con 
                                        <strong>{{ number_format($puntosUsuario, 0, ',', '.') }} puntos</strong>.
                                        ¡Sigue participando para subir en el ranking!
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-trophy fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">Aún no hay participantes en el ranking</h5>
                            <p class="text-muted">Sé el primero en participar en las quinielas y aparecer en el top 10.</p>
                            <a href="{{ route('quinielas.index') }}" class="btn btn-primary mt-3">
                                <i class="fas fa-trophy me-2"></i> Ver Quinielas Disponibles
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($top10->count() > 0)
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card mundial-card">
                <div class="card-header bg-primary">
                    <span class="text-white">
                        <i class="fas fa-info-circle me-2"></i>
                        Información del Ranking
                    </span>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <i class="fas fa-star text-warning me-2"></i>
                        <strong>Puntos Totales:</strong> Suma de todos los puntos obtenidos en quinielas y torneos.
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Partidos Acertados:</strong> Total de predicciones correctas realizadas.
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-medal text-primary me-2"></i>
                        <strong>Rango:</strong> Nivel alcanzado según los puntos totales acumulados.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mundial-card">
                <div class="card-header bg-warning text-dark">
                    <span>
                        <i class="fas fa-trophy me-2"></i>
                        Rangos Disponibles
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <small class="d-block"><i class="fas fa-medal me-1" style="color: #cd7f32;"></i> Bronce (0-19)</small>
                            <small class="d-block"><i class="fas fa-medal me-1" style="color: #95a5a6;"></i> Plata (20-39)</small>
                            <small class="d-block"><i class="fas fa-medal me-1" style="color: #f39c12;"></i> Oro (40-59)</small>
                        </div>
                        <div class="col-6">
                            <small class="d-block"><i class="fas fa-medal me-1" style="color: #95a5a6;"></i> Platino (60-79)</small>
                            <small class="d-block"><i class="fas fa-gem me-1" style="color: #3498db;"></i> Diamante (80-99)</small>
                            <small class="d-block"><i class="fas fa-crown me-1" style="color: #9b59b6;"></i> Leyenda (100+)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
.ranking-table tbody tr {
    transition: background-color 0.2s ease;
}

.ranking-table tbody tr:hover {
    background-color: rgba(46, 125, 50, 0.05) !important;
}

.table-warning {
    background-color: rgba(255, 193, 7, 0.15) !important;
    border-left: 4px solid #ffc107;
}
</style>
@endsection

