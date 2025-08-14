@extends('layouts.app')

@section('template_title')
    {{ __('Quinielas') }}
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header de la página -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1 class="display-6 fw-bold mb-2">
                        <i class="fas fa-trophy me-3"></i>
                        Quinielas Disponibles
                    </h1>
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Participa en las mejores quinielas de fútbol
                    </p>
                </div>
                <div class="text-end">
                    <div class="badge bg-success fs-6 p-3">
                        <i class="fas fa-users me-2"></i>
                        {{ $quinielas->count() }} Quinielas Activas
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span id="card_title">
                            <i class="fas fa-list me-2"></i>
                            {{ __('Lista de Quinielas') }}
                        </span>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-filter me-1"></i> Filtrar
                            </button>
                            <button class="btn btn-outline-success btn-sm">
                                <i class="fas fa-sort me-1"></i> Ordenar
                            </button>
                        </div>
                    </div>
                </div>
                
                @if ($message = Session::get('success'))
                    <div class="alert alert-success m-3">
                        <i class="fas fa-check-circle me-2"></i>
                        <p class="mb-0">{{ $message }}</p>
                    </div>
                @endif

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead">
                                <tr>
                                    <th class="text-center">
                                        <i class="fas fa-hashtag me-1"></i> No
                                    </th>
                                    <th>
                                        <i class="fas fa-futbol me-1"></i> Partido
                                    </th>
                                    <th>
                                        <i class="fas fa-calendar me-1"></i> Fecha
                                    </th>
                                    <th class="text-center">
                                        <i class="fas fa-users me-1"></i> Participantes
                                    </th>
                                    <th>
                                        <i class="fas fa-clock me-1"></i> Fecha Límite
                                    </th>
                                    <th class="text-center">
                                        <i class="fas fa-info-circle me-1"></i> Estado
                                    </th>
                                    <th class="text-center">
                                        <i class="fas fa-cogs me-1"></i> Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($quinielas as $index => $quiniela)
                                    <tr class="align-middle">
                                        <td class="text-center">
                                            <span class="badge bg-primary fs-6">{{ $index + 1 }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="team-vs me-3">
                                                    <div class="team local text-center">
                                                        <div class="team-name fw-bold">{{ $quiniela->partido->equipoLocal->nombre }}</div>
                                                        <small class="text-muted">{{ $quiniela->partido->equipoLocal->abreviacion }}</small>
                                                    </div>
                                                    <div class="vs-badge mx-2">
                                                        <span class="badge bg-dark fs-6">VS</span>
                                                    </div>
                                                    <div class="team visitor text-center">
                                                        <div class="team-name fw-bold">{{ $quiniela->partido->equipoVisitante->nombre }}</div>
                                                        <small class="text-muted">{{ $quiniela->partido->equipoVisitante->abreviacion }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="league-info mt-2">
                                                <span class="badge bg-info">
                                                    <i class="fas fa-trophy me-1"></i>
                                                    {{ $quiniela->partido->liga }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="date-time">
                                                <div class="date fw-bold">
                                                    <i class="fas fa-calendar-day me-1"></i>
                                                    {{ $quiniela->partido->fecha_hora->format('d/m/Y') }}
                                                </div>
                                                <div class="time text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ $quiniela->partido->fecha_hora->format('H:i') }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="participants-count">
                                                <span class="badge bg-success fs-6">
                                                    <i class="fas fa-users me-1"></i>
                                                    {{ $quiniela->participantes->count() }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="deadline-info">
                                                <div class="deadline-date fw-bold">
                                                    <i class="fas fa-hourglass-end me-1"></i>
                                                    {{ $quiniela->fecha_limite->format('d/m/Y') }}
                                                </div>
                                                <div class="deadline-time text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ $quiniela->fecha_limite->format('H:i') }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($quiniela->estado === 'activa')
                                                <span class="badge bg-success fs-6">
                                                    <i class="fas fa-play-circle me-1"></i> Activa
                                                </span>
                                            @elseif($quiniela->estado === 'cerrada')
                                                <span class="badge bg-warning fs-6">
                                                    <i class="fas fa-pause-circle me-1"></i> Cerrada
                                                </span>
                                            @else
                                                <span class="badge bg-secondary fs-6">
                                                    <i class="fas fa-stop-circle me-1"></i> Finalizada
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a class="btn btn-primary btn-sm" href="{{ route('quinielas.show',$quiniela->id) }}">
                                                <i class="fas fa-eye me-1"></i> Ver Quiniela
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Paginación mejorada -->
            <div class="d-flex justify-content-center mt-4">
                {!! $quinielas->links() !!}
            </div>
        </div>
    </div>
</div>

<style>
.team-vs {
    display: flex;
    align-items: center;
    justify-content: center;
}

.team {
    min-width: 80px;
}

.team-name {
    font-size: 0.9rem;
}

.vs-badge {
    font-weight: bold;
}

.league-info {
    margin-top: 8px;
}

.date-time, .deadline-info {
    line-height: 1.2;
}

.date, .deadline-date {
    font-size: 0.9rem;
}

.time, .deadline-time {
    font-size: 0.8rem;
}

.participants-count .badge {
    min-width: 60px;
}

@media (max-width: 768px) {
    .team-vs {
        flex-direction: column;
        gap: 10px;
    }
    
    .vs-badge {
        transform: rotate(90deg);
    }
    
    .table-responsive {
        font-size: 0.85rem;
    }
}
</style>
@endsection
