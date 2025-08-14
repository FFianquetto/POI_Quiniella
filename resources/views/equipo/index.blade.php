@extends('layouts.app')

@section('template_title')
    {{ __('Equipos') }}
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header de la página -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1 class="display-6 fw-bold mb-2">
                        <i class="fas fa-users me-3"></i>
                        Equipos de Fútbol
                    </h1>
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Conoce todos los equipos disponibles para las quinielas
                    </p>
                </div>
                <div class="text-end">
                    <div class="badge bg-primary fs-6 p-3">
                        <i class="fas fa-futbol me-2"></i>
                        {{ $equipos->count() }} Equipos Registrados
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
                            {{ __('Catálogo de Equipos') }}
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
                                        <i class="fas fa-shield-alt me-1"></i> Equipo
                                    </th>
                                    <th class="text-center">
                                        <i class="fas fa-tag me-1"></i> Abreviación
                                    </th>
                                    <th>
                                        <i class="fas fa-map-marker-alt me-1"></i> Ciudad
                                    </th>
                                    <th>
                                        <i class="fas fa-trophy me-1"></i> Liga
                                    </th>
                                    <th class="text-center">
                                        <i class="fas fa-cogs me-1"></i> Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($equipos as $index => $equipo)
                                    <tr class="align-middle">
                                        <td class="text-center">
                                            <span class="badge bg-primary fs-6">{{ $index + 1 }}</span>
                                        </td>
                                        <td>
                                            <div class="team-info d-flex align-items-center">
                                                <div class="team-avatar me-3">
                                                    <div class="team-logo">
                                                        <i class="fas fa-shield-alt fa-2x text-primary"></i>
                                                    </div>
                                                </div>
                                                <div class="team-details">
                                                    <div class="team-name fw-bold fs-6">{{ $equipo->nombre }}</div>
                                                    <div class="team-meta text-muted small">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        Equipo registrado
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info fs-6 fw-bold">
                                                {{ $equipo->abreviacion }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="city-info">
                                                <div class="city-name fw-bold">
                                                    <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                                    {{ $equipo->ciudad }}
                                                </div>
                                                <div class="city-country text-muted small">
                                                    <i class="fas fa-flag me-1"></i>
                                                    México
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="league-info">
                                                <span class="badge bg-success">
                                                    <i class="fas fa-trophy me-1"></i>
                                                    {{ $equipo->liga }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <a class="btn btn-primary btn-sm" href="{{ route('equipos.show',$equipo->id) }}">
                                                <i class="fas fa-eye me-1"></i> Ver Detalles
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
                {!! $equipos->links() !!}
            </div>
        </div>
    </div>
</div>

<style>
.team-info {
    min-width: 200px;
}

.team-avatar {
    width: 50px;
    height: 50px;
}

.team-logo {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #dee2e6;
    transition: all 0.3s ease;
}

.team-logo:hover {
    transform: scale(1.1);
    border-color: var(--primary-green);
    box-shadow: var(--shadow-green);
}

.team-details {
    flex: 1;
}

.team-name {
    color: var(--primary-green);
    margin-bottom: 2px;
}

.team-meta {
    font-size: 0.75rem;
}

.city-info {
    line-height: 1.2;
}

.city-name {
    font-size: 0.9rem;
    margin-bottom: 2px;
}

.city-country {
    font-size: 0.75rem;
}

.league-info .badge {
    font-size: 0.8rem;
    padding: 8px 12px;
}

@media (max-width: 768px) {
    .team-info {
        min-width: 150px;
    }
    
    .team-avatar {
        width: 40px;
        height: 40px;
    }
    
    .team-logo i {
        font-size: 1.5rem !important;
    }
    
    .table-responsive {
        font-size: 0.85rem;
    }
}
</style>
@endsection
