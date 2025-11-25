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
                                Define el cuadro oficial de 32 países clasificados y simularemos la ruta hacia el título mundial.
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
                        Selecciones Participantes
                    </h4>
                    <small class="text-white opacity-75">
                        <i class="fas fa-info-circle me-1"></i>
                        ¡Que gane el mejor!
                    </small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="text-center mt-3 mb-2">
                                <button type="button" class="btn tournament-btn btn-generate btn-wide" id="generateTournament">
                                    <i class="fas fa-play me-2"></i>
                                    Generar Mundial
                                </button>
                                <button type="button"
                                        class="btn btn-view-simulator d-none"
                                        id="openTournamentSimulator">
                                    <i class="fas fa-eye me-2"></i>
                                    Ver simulación
                                </button>
                            </div>
                            <small id="tournamentLockMessage" class="tournament-lock-message text-tournament-orange d-none text-center d-block mt-2">
                                Ya hay un torneo en curso. Finalízalo antes de generar uno nuevo.
                            </small>
                        </div>
                    </div>

                    <div class="world-cup-teams-wrapper mt-2">
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
                                            <span class="label">Nivel de Juego:</span>
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
                        <button type="button" class="btn btn-sm btn-outline-warning" id="simulateWithArgentinaWinner" title="Simula la fase forzando que Argentina gane todos sus partidos">
                            <i class="fas fa-trophy me-1"></i>
                            Simular (ARG gana)
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
                                <h5>Selección que Ganó</h5>
                                <h3 id="championTeamLabel" class="text-uppercase">Pendiente</h3>
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

</div>

{{-- Cargar scripts con rutas absolutas y manejo de errores mejorado --}}
@php
    $baseUrl = rtrim(config('app.url', url('/')), '/');
    $scripts = [
        'constants.js',
        'state.js',
        'storage.js',
        'notifications.js',
        'api.js',
        'controller.js'
    ];
@endphp

@foreach($scripts as $script)
<script src="{{ $baseUrl }}/js/tournament/{{ $script }}" 
        onerror="console.error('❌ Error al cargar {{ $script }}'); console.error('URL intentada: {{ $baseUrl }}/js/tournament/{{ $script }}');"></script>
@endforeach
<script src="{{ $baseUrl }}/js/tournament-simulator.js" 
        onerror="console.error('❌ Error al cargar tournament-simulator.js'); console.error('URL intentada: {{ $baseUrl }}/js/tournament-simulator.js');"></script>

{{-- Script de carga alternativa si los scripts principales fallan --}}
<script>
    (function() {
        // Esperar a que se carguen los scripts o intentar cargarlos manualmente
        function checkAndLoadScripts() {
            const baseUrl = '{{ rtrim(config("app.url", url("/")), "/") }}';
            const scriptsToLoad = [];
            
            // Verificar qué scripts no se cargaron
            if (!window.TournamentSim) {
                console.warn('⚠️ TournamentSim no está disponible. Intentando cargar scripts manualmente...');
                
                const scripts = [
                    { name: 'constants.js', path: baseUrl + '/js/tournament/constants.js' },
                    { name: 'state.js', path: baseUrl + '/js/tournament/state.js' },
                    { name: 'storage.js', path: baseUrl + '/js/tournament/storage.js' },
                    { name: 'notifications.js', path: baseUrl + '/js/tournament/notifications.js' },
                    { name: 'api.js', path: baseUrl + '/js/tournament/api.js' },
                    { name: 'controller.js', path: baseUrl + '/js/tournament/controller.js' },
                    { name: 'tournament-simulator.js', path: baseUrl + '/js/tournament-simulator.js' }
                ];
                
                // Intentar cargar cada script que falte
                scripts.forEach(function(script) {
                    const scriptTag = document.createElement('script');
                    scriptTag.src = script.path;
                    scriptTag.async = false;
                    scriptTag.onerror = function() {
                        console.error('❌ No se pudo cargar ' + script.name + ' desde ' + script.path);
                    };
                    scriptTag.onload = function() {
                        console.log('✅ ' + script.name + ' cargado manualmente');
                    };
                    document.head.appendChild(scriptTag);
                });
            }
        }
        
        // Verificar después de un tiempo si los scripts se cargaron
        setTimeout(function() {
            if (!window.TournamentSim) {
                checkAndLoadScripts();
                
                // Verificar nuevamente después de intentar cargar
                setTimeout(function() {
                    console.log('=== Diagnóstico del Torneo ===');
                    
                    // Verificar botón
                    const generateBtn = document.getElementById('generateTournament');
                    if (!generateBtn) {
                        console.error('❌ El botón generateTournament NO existe en el DOM');
                    } else {
                        console.log('✅ El botón generateTournament existe');
                    }
                    
                    // Verificar namespace
                    if (!window.TournamentSim) {
                        console.error('❌ TournamentSim namespace NO está disponible después de intentar cargar scripts');
                        console.error('Por favor, verifica que los archivos JavaScript existan en: {{ rtrim(config("app.url", url("/")), "/") }}/js/tournament/');
                    } else {
                        console.log('✅ TournamentSim namespace está disponible');
                        console.log('   Módulos disponibles:', Object.keys(window.TournamentSim));
                        
                        if (!window.TournamentSim.TournamentController) {
                            console.error('❌ TournamentController NO está disponible en el namespace');
                        } else {
                            console.log('✅ TournamentController está disponible');
                            
                            // Intentar inicializar si el botón existe
                            if (generateBtn) {
                                try {
                                    const controller = new window.TournamentSim.TournamentController(document);
                                    controller.init();
                                    console.log('✅ Controller inicializado correctamente');
                                } catch (error) {
                                    console.error('❌ Error al inicializar controller:', error);
                                }
                            }
                        }
                    }
                    
                    console.log('=== Fin del Diagnóstico ===');
                }, 2000);
            } else {
                console.log('✅ Todos los scripts se cargaron correctamente');
            }
        }, 500);
    })();
</script>
@endsection



