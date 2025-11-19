@extends('layouts.app')

@section('content')
<div class="tournament-container">
    <div class="container-fluid">
        <div id="activeTournamentData"
             data-tournament='@json($serializedTournament)'
             class="d-none"></div>
        <!-- Header del Torneo -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="tournament-header world-cup-header">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <h1 class="mb-1">
                                <i class="fas fa-earth-americas me-3"></i>
                                Copa Mundial de la FIFA 2026
                            </h1>
                            <p class="mb-0">
                                Elige tu selección favorita, define el cuadro oficial de 32 países clasificados y simularemos la ruta hacia el título mundial.
                            </p>
                        </div>
                        <div class="world-cup-badge text-end">
                            <span class="badge bg-warning text-dark fs-6">
                                <i class="fas fa-star me-2"></i>Sedes: Estados Unidos · México · Canadá
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Panel de Selección de Equipos (Se muestra inicialmente) -->
    <div id="teamSelectionPanel" class="row">
        <div class="col-12 mb-4">
            <div class="team-selection-panel">
                <div class="team-selection-header">
                    <h4 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Selecciones participantes
                    </h4>
                    <small class="text-white opacity-75">
                        <i class="fas fa-info-circle me-1"></i>
                        Consulta las 32 selecciones clasificadas y define tu favorita para el sistema de recompensas.
                    </small>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-center justify-content-center">
                        <div class="col-xl-4 col-lg-5 col-md-6">
                            <label for="favoriteTeam" class="form-label text-white fw-semibold">
                                <i class="fas fa-gem me-2"></i>
                                Selecciona tu equipo favorito
                            </label>
                            <select id="favoriteTeam" class="form-select tournament-select">
                                <option value="">-- Elige una selección --</option>
                                @foreach($worldCupTeams as $team)
                                    <option value="{{ $team->code }}">{{ $team->name }} ({{ $team->code }})</option>
                                @endforeach
                            </select>
                            <div id="favoriteTeamFeedback" class="form-text text-tournament-orange mt-2 d-none">
                                Selecciona una selección favorita antes de generar el torneo.
                            </div>
                            <small class="text-white-50">
                                Tu favorito determinará las medallas y puntos que obtengas en cada simulación.
                            </small>
                        </div>
                        <div class="col-xl-4 col-lg-5 col-md-6">
                            <div class="selection-summary d-flex flex-wrap justify-content-center gap-3">
                                <div class="selected-team-display" id="selectedTeamDisplay">
                                    <span class="label text-white-50 d-block mb-1">Favorito seleccionado</span>
                                    <span class="selected-team-pill text-white-75" data-default="Selecciona un favorito">
                                        Selecciona un favorito
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-5 col-md-6 mt-3 mt-md-0">
                            <div class="tournament-actions d-flex flex-column flex-lg-row align-items-center justify-content-center justify-content-lg-end gap-2">
                                <button type="button" class="btn tournament-btn btn-generate btn-wide flex-fill" id="generateTournament">
                                    <i class="fas fa-play me-2"></i>
                                    Generar Mundial
                                </button>
                                <button type="button"
                                        class="btn btn-view-simulator flex-fill d-none"
                                        id="openTournamentSimulator">
                                    <i class="fas fa-eye me-2"></i>
                                    Ver simulación
                                </button>
                            </div>
                            <small id="tournamentLockMessage" class="tournament-lock-message text-tournament-orange d-none text-center text-lg-end mt-2">
                                Ya hay un torneo en curso. Finalízalo antes de generar uno nuevo.
                            </small>
                        </div>
                    </div>

                    <div class="world-cup-teams-wrapper mt-4">
                        <div id="worldCupTeamsData"
                             data-teams='{{ $worldCupTeamsDataset }}'></div>
                        <div class="row g-3">
                            @foreach($worldCupTeams as $team)
                            <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-6">
                                <div class="world-cup-team-card">
                                    <div class="world-cup-team-flag">
                                        @if($team->flag_url)
                                            <img src="{{ $team->flag_url }}" alt="Bandera de {{ $team->name }}">
                                        @else
                                            <span class="placeholder-flag">{{ $team->code }}</span>
                                        @endif
                                    </div>
                                    <div class="world-cup-team-body">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h5 class="mb-0">{{ $team->name }}</h5>
                                            <span class="badge bg-light text-dark fw-semibold">{{ $team->code }}</span>
                                        </div>
                                        <p class="mb-1">
                                            <span class="label">Ranking FIFA:</span>
                                            <span class="value">{{ $team->fifa_ranking }}</span>
                                        </p>
                                        <p class="mb-0">
                                            <span class="label">Confederación:</span>
                                            <span class="value">{{ $team->continent }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel del Bracket del Torneo (Se oculta inicialmente) -->
    <div id="tournamentBracketPanel" class="row" style="display: none;">
        <div class="col-12 mb-4">
            <div class="bracket-panel">
                <div class="bracket-header">
                    <h4 class="mb-0">
                        <i class="fas fa-sitemap me-2"></i>
                        Bracket del Torneo
                        <span id="currentPhaseBadge" class="badge bg-success ms-2" style="display: none;">
                            <span id="currentPhaseText">Fase 1</span>
                        </span>
                    </h4>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-success" id="simulateCurrentRound">
                            <i class="fas fa-forward-step me-1"></i>
                            Simular Fase Actual
                        </button>
                        @if(isset($isTournamentCompleted) && $isTournamentCompleted)
                        <button type="button" class="btn btn-sm btn-outline-light" id="backToSelection">
                            <i class="fas fa-arrow-left me-1"></i>
                            Volver a Selección
                        </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div id="tournamentBracket" class="tournament-bracket-horizontal">
                        <div class="tournament-empty-state">
                            <i class="fas fa-futbol"></i>
                            <h5>Configura la Copa Mundial 2026</h5>
                            <p>Ingresa las 32 selecciones para iniciar en dieciseisavos rumbo a la Gran Final</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de Información del Torneo -->
    <div class="row" id="tournamentInfo" style="display: none;">
        <div class="col-12">
            <div class="tournament-info-panel">
                <div class="tournament-info-header">
                    <h4 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Información del Torneo
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-item info-teams">
                                <h5>Selecciones</h5>
                                <h3 id="totalTeams">0</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item info-rounds">
                                <h5>Fases Oficiales</h5>
                                <h3 id="totalRounds">6</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item info-current">
                                <h5>Fase Actual</h5>
                                <h3 id="currentRoundName">Pendiente</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item info-matches">
                                <h5>Partidos Totales</h5>
                                <h3 id="totalMatches">32</h3>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="info-item info-status">
                                <h5>Estado del Mundial</h5>
                                <h3 id="tournamentStatus">Listo</h3>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="info-item info-favorite text-md-start text-center">
                                <h5>Selección Favorita</h5>
                                <h3 id="favoriteTeamLabel" class="text-uppercase">Sin definir</h3>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3" id="finalizeTournamentRow" style="display: none;">
                        <div class="col-12">
                            <div class="d-flex justify-content-center">
                                <form action="{{ route('torneo.finalize') }}" method="POST" id="finalizeTournamentForm">
                                    @csrf
                                    <input type="hidden" name="tournament_uuid" id="finalizeTournamentUuid" value="">
                                    <button type="submit" class="btn btn-lg btn-warning" id="finalizeTournamentButton">
                                        <i class="fas fa-check-circle me-2"></i>
                                        Finalizar Torneo y Crear Nuevo
                                    </button>
                                </form>
                            </div>
                            <p class="text-center text-white-50 mt-2 mb-0">
                                <small>Al finalizar este torneo podrás generar uno nuevo desde cero.</small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4" id="rewardPanel" style="display: none;">
        <div class="col-12">
            <div class="tournament-info-panel">
                <div class="tournament-info-header">
                    <h4 class="mb-0">
                        <i class="fas fa-medal me-2"></i>
                        Recompensas del Torneo
                    </h4>
                </div>
                <div class="card-body">
                    <p id="rewardMessage" class="text-white-50 mb-3">
                        Aún no se han definido las recompensas. Completa el torneo para conocer tus medallas.
                    </p>
                    <ul class="list-group list-group-flush reward-list" id="rewardList"></ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4" id="achievementPanel">
        <div class="col-12">
            <div class="achievement-panel">
                <div class="achievement-header">
                    <div>
                        <h4 class="mb-1">
                            <i class="fas fa-medal me-2"></i>
                            Salón de la Fama Mundialista
                        </h4>
                        <p class="mb-0 text-white-50">
                            Acumula puntos dirigiendo a tu selección favorita y presume tus logros ante la comunidad.
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-light" id="resetAchievements">
                            <i class="fas fa-rotate-left me-1"></i>
                            Reiniciar Progreso
                        </button>
                        <button class="btn btn-sm btn-gold" id="shareAchievements">
                            <i class="fas fa-share-alt me-1"></i>
                            Compartir Logros
                        </button>
                    </div>
                </div>
                <div class="achievement-body">
                    <div class="row g-4">
                        <div class="col-lg-4">
                            <div class="achievement-summary-card">
                                <h6>Total de Puntos Mundialistas</h6>
                                <h2 id="userMedalPoints">0</h2>
                                <span id="achievementTierLabel" class="badge rounded-pill bg-light text-dark fw-semibold">Novato</span>
                                <div class="progress mt-3">
                                    <div class="progress-bar" id="medalProgressBar" role="progressbar" style="width: 0%;"></div>
                                </div>
                                <small id="nextBadgeMessage" class="text-white-50 d-block mt-2"></small>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="badge-gallery">
                                <h6 class="text-uppercase text-white-50">Insignias Mundialistas</h6>
                                <div class="row g-3" id="badgeGrid"></div>
                            </div>
                        </div>
                    </div>
                    <div class="achievement-history mt-4">
                        <h6 class="text-uppercase text-white-50 mb-2">
                            <i class="fas fa-trophy me-2"></i>
                            Últimas hazañas
                        </h6>
                        <ul class="list-group list-group-flush" id="achievementTimeline">
                            <li class="list-group-item text-white-50 bg-transparent">
                                Aún no hay torneos registrados. ¡Simula tu primer Mundial y acumula puntos!
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/tournament/constants.js') }}"></script>
<script src="{{ asset('js/tournament/state.js') }}"></script>
<script src="{{ asset('js/tournament/storage.js') }}"></script>
<script src="{{ asset('js/tournament/notifications.js') }}"></script>
<script src="{{ asset('js/tournament/achievements.js') }}"></script>
<script src="{{ asset('js/tournament/api.js') }}"></script>
<script src="{{ asset('js/tournament/controller.js') }}"></script>
<script src="{{ asset('js/tournament-simulator.js') }}"></script>
@endsection



