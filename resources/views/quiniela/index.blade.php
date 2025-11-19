@extends('layouts.app')

@section('template_title')
    {{ __('Quinielas') }}
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header de la página -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="world-cup-header quiniela-header">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h1 class="mb-1">
                            <i class="fas fa-trophy me-3"></i>
                            Quinielas Disponibles
                        </h1>
                        <p class="mb-0">
                            Participa en las mejores quinielas de fútbol y demuestra tus predicciones mundialistas.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($puntosPorFase) && count($puntosPorFase) > 0 && !$isTournamentClosed)
    <div class="row mt-4">
        <div class="col-sm-12">
            <div class="card mundial-card">
                <div class="card-header bg-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-white">
                            <i class="fas fa-trophy me-2"></i>
                            Mis Puntos por Fase
                        </span>
                        <span class="badge bg-light text-dark fs-6">
                            <i class="fas fa-star me-1"></i>
                            Total: {{ $puntosTotales }} puntos
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($puntosPorFase as $fase)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-muted mb-2">
                                        <i class="fas fa-flag me-2"></i>
                                        Fase {{ $fase['fase'] }}
                                    </h6>
                                    <h5 class="card-subtitle mb-2 text-primary">
                                        {{ $fase['nombre'] }}
                                    </h5>
                                    <div class="display-4 fw-bold text-success mb-0">
                                        {{ $fase['puntos'] }}
                                    </div>
                                    <small class="text-muted">puntos</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($isTournamentClosed)
    <div class="row mt-4" id="tournamentResultsCard">
        <div class="col-sm-12">
            <div class="card mundial-card">
                <div class="card-header bg-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <span id="worldCupCardTitle" class="text-white">
                            <i class="fas fa-trophy me-2"></i>
                            Resultados del Torneo - Mundial Finalizado
                        </span>
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-check-circle me-1"></i>
                            Torneo Completado
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h3 class="text-success mb-3">
                            <i class="fas fa-star me-2"></i>
                            ¡Tus Resultados!
                        </h3>
                        
                        @if(isset($puntosTotales) && $puntosTotales > 0)
                        <div class="row justify-content-center mb-4">
                            <div class="col-md-8">
                                <div class="p-4 bg-light rounded border">
                                    <h5 class="text-muted mb-3">Puntos Totales Obtenidos</h5>
                                    <div class="display-3 fw-bold text-success mb-2">
                                        {{ $puntosTotales }}
                                    </div>
                                    <p class="text-muted mb-0">puntos en este torneo</p>
                                    
                                    @if(isset($favoritaGano) && $favoritaGano && isset($puntosBonusFavorita) && $puntosBonusFavorita > 0)
                                    <div class="mt-3 p-3 bg-warning rounded">
                                        <p class="mb-1 fw-bold text-dark">
                                            <i class="fas fa-gift me-2"></i>
                                            ¡Bono por Selección Favorita Ganadora!
                                        </p>
                                        <p class="mb-0 text-dark">
                                            Tu selección favorita ganó el torneo. 
                                            <strong>+{{ $puntosBonusFavorita }} puntos adicionales</strong>
                                        </p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        @if(isset($puntosPorFase) && count($puntosPorFase) > 0)
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-muted mb-3">Desglose por Fase</h5>
                                <div class="row g-3">
                                    @foreach($puntosPorFase as $fase)
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card h-100 border-0 shadow-sm">
                                            <div class="card-body text-center">
                                                <h6 class="card-title text-muted mb-2">
                                                    <i class="fas fa-flag me-2"></i>
                                                    Fase {{ $fase['fase'] }}
                                                </h6>
                                                <h5 class="card-subtitle mb-2 text-primary">
                                                    {{ $fase['nombre'] }}
                                                </h5>
                                                <div class="display-5 fw-bold text-success mb-0">
                                                    {{ $fase['puntos'] }}
                                                </div>
                                                <small class="text-muted">puntos</small>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    
                                    @if(isset($puntosBonusFavorita) && $puntosBonusFavorita > 0)
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card h-100 border-0 shadow-sm" style="border: 2px solid #ffc107 !important;">
                                            <div class="card-body text-center">
                                                <h6 class="card-title text-muted mb-2">
                                                    <i class="fas fa-gift me-2"></i>
                                                    Bonus
                                                </h6>
                                                <h5 class="card-subtitle mb-2 text-warning">
                                                    Favorita Ganadora
                                                </h5>
                                                <div class="display-5 fw-bold text-warning mb-0">
                                                    +{{ $puntosBonusFavorita }}
                                                </div>
                                                <small class="text-muted">puntos</small>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                        @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No participaste en este torneo o no obtuviste puntos.
                        </div>
                        @endif
                        
                        <div class="mt-4">
                            <button type="button" class="btn btn-success btn-lg" id="acceptTournamentResults">
                                <i class="fas fa-check me-2"></i>
                                Aceptar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @elseif($tournamentHasNoMatches)
    <div class="row mt-4">
        <div class="col-sm-12">
            <div class="card mundial-card">
                <div class="card-header bg-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <span id="worldCupCardTitle" class="text-white">
                            <i class="fas fa-clock me-2"></i>
                            Quinielas del Mundial 2026 - En Preparación
                        </span>
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-hourglass-half me-1"></i>
                            Torneo en Curso
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @elseif($worldCupMatches->isNotEmpty())
    <div class="row mt-4">
        <div class="col-sm-12">
            <div class="card mundial-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span id="worldCupCardTitle">
                            <i class="fas fa-globe-americas me-2"></i>
                            Quinielas del Mundial 2026
                            @if(isset($activeRoundNumber) && $activeRoundNumber)
                                <span class="badge bg-success ms-2">
                                    Fase {{ $activeRoundNumber }}
                                    @if(isset($activeRoundName))
                                        - {{ $activeRoundName }}
                                    @endif
                                </span>
                            @endif
                        </span>
                        <span class="badge bg-warning text-dark">
                            No hay empates: elige ganador directo
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('quinielas.mundial.apostar') }}"
                          method="POST"
                          id="worldCupBetsForm"
                          data-matches="{{ $worldCupMatches->count() }}">
                        @csrf
                        
                        @if(!((isset($hasAllBetsForActiveRound) && $hasAllBetsForActiveRound) || (isset($isTournamentClosed) && $isTournamentClosed)) || (isset($isFirstPhase) && $isFirstPhase && isset($tournamentTeams) && $tournamentTeams->isNotEmpty()))
                        <div class="mb-3 d-flex justify-content-between align-items-end gap-3 flex-wrap">
                            @if(isset($isFirstPhase) && $isFirstPhase && isset($tournamentTeams) && $tournamentTeams->isNotEmpty())
                            <div class="p-3 bg-light rounded border" style="max-width: 500px; flex: 1; min-width: 300px;">
                                <label for="favorite_team_code" class="form-label fw-bold mb-2">
                                    <i class="fas fa-star text-warning me-2"></i>
                                    Escoge tu selección favorita para ganar el torneo
                                </label>
                                <select name="favorite_team_code" 
                                        id="favorite_team_code" 
                                        class="form-select"
                                        @if((isset($hasAllBetsForActiveRound) && $hasAllBetsForActiveRound) || (isset($isTournamentClosed) && $isTournamentClosed))
                                            disabled
                                        @endif>
                                    <option value="">-- Selecciona tu favorita --</option>
                                    @foreach($tournamentTeams as $team)
                                        <option value="{{ $team['code'] }}" 
                                                {{ (isset($userFavoriteTeam) && $userFavoriteTeam === $team['code']) ? 'selected' : '' }}>
                                            {{ $team['name'] }} ({{ $team['code'] }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            
                            @if(!((isset($hasAllBetsForActiveRound) && $hasAllBetsForActiveRound) || (isset($isTournamentClosed) && $isTournamentClosed)))
                            <div class="text-end">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="randomFillButton">
                                    <i class="fas fa-dice me-2"></i>
                                    Autocompletar aleatorio
                                </button>
                            </div>
                            @endif
                        </div>
                        @endif
                        <div class="table-responsive">
                            <table class="table align-middle mundial-table">
                                <thead>
                                    <tr>
                                        <th>Llave</th>
                                        <th class="confed-col">Confederaciones</th>
                                        <th class="text-center ganador-col">¿Quién gana?</th>
                                        <th class="text-center marcador-col">Marcador estimado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($worldCupMatches as $match)
                                        @php
                                            $bet = $usuarioBets->get($match['match_key']);
                                        @endphp
                                        <tr data-match-key="{{ $match['match_key'] }}"
                                            data-match-label="{{ $match['team_a']['code'] }} vs {{ $match['team_b']['code'] }}">
                                            <td class="llave-cell">
                                                <div class="match-teams">
                                                    <div class="team-line">
                                                        <span class="team-code badge bg-primary">{{ $match['team_a']['code'] }}</span>
                                                        <span class="team-name">{{ $match['team_a']['name'] }}</span>
                                                    </div>
                                                    <div class="vs-badge mini">vs</div>
                                                    <div class="team-line">
                                                        <span class="team-code badge bg-secondary">{{ $match['team_b']['code'] }}</span>
                                                        <span class="team-name">{{ $match['team_b']['name'] }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="confed-col">
                                                <div class="text-muted">
                                                    {{ $match['team_a']['continent'] }} / {{ $match['team_b']['continent'] }}
                                                </div>
                                            </td>
                                            <td class="text-center ganador-col">
                                                <div class="winner-options">
                                                    <label class="winner-option {{ (isset($hasAllBetsForActiveRound) && $hasAllBetsForActiveRound) ? 'disabled' : '' }}">
                                                        <input type="radio"
                                                               name="bets[{{ $match['match_key'] }}][team]"
                                                               value="{{ $match['team_a']['code'] }}"
                                                               class="form-check-input winner-radio"
                                                               {{ optional($bet)->selected_code === $match['team_a']['code'] ? 'checked' : '' }}
                                                               @if(isset($hasAllBetsForActiveRound) && $hasAllBetsForActiveRound)
                                                                   disabled
                                                               @endif>
                                                        <span class="winner-btn">{{ $match['team_a']['code'] }}</span>
                                                    </label>
                                                    <label class="winner-option {{ (isset($hasAllBetsForActiveRound) && $hasAllBetsForActiveRound) ? 'disabled' : '' }}">
                                                        <input type="radio"
                                                               name="bets[{{ $match['match_key'] }}][team]"
                                                               value="{{ $match['team_b']['code'] }}"
                                                               class="form-check-input winner-radio"
                                                               {{ optional($bet)->selected_code === $match['team_b']['code'] ? 'checked' : '' }}
                                                               @if(isset($hasAllBetsForActiveRound) && $hasAllBetsForActiveRound)
                                                                   disabled
                                                               @endif>
                                                        <span class="winner-btn">{{ $match['team_b']['code'] }}</span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td class="text-center marcador-col">
                                                <div class="marcador-inputs d-flex justify-content-center align-items-center gap-2">
                                                    <input type="number"
                                                           name="bets[{{ $match['match_key'] }}][score_a]"
                                                           min="0"
                                                           max="20"
                                                           class="form-control form-control-sm marcador"
                                                           placeholder="0"
                                                           value="{{ optional($bet)->score_a ?? 0 }}"
                                                           @if(isset($hasAllBetsForActiveRound) && $hasAllBetsForActiveRound)
                                                               disabled readonly
                                                           @endif>
                                                    <span class="fw-bold">-</span>
                                                    <input type="number"
                                                           name="bets[{{ $match['match_key'] }}][score_b]"
                                                           min="0"
                                                           max="20"
                                                           class="form-control form-control-sm marcador"
                                                           placeholder="0"
                                                           value="{{ optional($bet)->score_b ?? 0 }}"
                                                           @if(isset($hasAllBetsForActiveRound) && $hasAllBetsForActiveRound)
                                                               disabled readonly
                                                           @endif>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="bets-validation-summary mt-4">
                            <div id="betsValidationMessage" class="alert alert-warning d-none" role="alert"></div>
                            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                                <p class="mb-0 text-muted" id="betsProgressText">
                                    Completa tus predicciones para registrar la Quiniela de esta fase.
                                </p>
                                <div class="d-flex flex-column flex-md-row gap-2 w-100 w-lg-auto">
                                    <button type="submit" 
                                            class="btn btn-success w-100" 
                                            id="submitBetsButton" 
                                            @if((isset($hasAllBetsForActiveRound) && $hasAllBetsForActiveRound) || (isset($isTournamentClosed) && $isTournamentClosed))
                                                disabled
                                            @endif
                                            data-has-all-bets="{{ isset($hasAllBetsForActiveRound) && $hasAllBetsForActiveRound ? 'true' : 'false' }}">
                                        <i class="fas fa-save me-2"></i>
                                        @if(isset($hasAllBetsForActiveRound) && $hasAllBetsForActiveRound)
                                            Quiniela Ya Guardada
                                        @elseif(isset($isTournamentClosed) && $isTournamentClosed)
                                            Torneo Cerrado
                                        @else
                                            Guardar Quiniela
                                        @endif
                                    </button>
                                </div>
                            </div>
                        </div>

    @endif
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

.hinche-placeholder .hinche-card {
    max-width: 520px;
    background: rgba(255, 255, 255, 0.85);
    border-radius: 18px;
    padding: 28px;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08);
    backdrop-filter: blur(4px);
}

@media (max-width: 768px) {
    .hinche-placeholder .hinche-card {
        padding: 22px;
    }
}

.mundial-card {
    border-radius: 22px;
    box-shadow: 0 18px 32px rgba(46, 125, 50, 0.2);
    margin-bottom: 3rem;
}

.mundial-table thead {
    background: linear-gradient(120deg, rgba(39, 174, 96, 0.95), rgba(27, 132, 68, 0.95));
    color: var(--white);
    text-transform: uppercase;
    font-size: 0.78rem;
    letter-spacing: 0.05em;
}

.llave-cell {
    min-width: 220px;
}

.match-teams {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.team-line {
    display: flex;
    align-items: center;
    gap: 8px;
}

.team-line .team-code {
    min-width: 44px;
}

.winner-options {
    display: flex;
    justify-content: center;
    gap: 16px;
}

.winner-option {
    position: relative;
    display: inline-flex;
}

.winner-option .winner-radio {
    position: absolute;
    opacity: 0;
    pointer-events: none;
    left: -9999px;
    margin: 0;
    appearance: none;
    outline: none;
    box-shadow: none;
}

.winner-option .winner-btn {
    display: inline-block;
    min-width: 64px;
    padding: 8px 16px;
    border-radius: 12px;
    background: rgba(46, 125, 50, 0.12);
    color: var(--primary-green);
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.2s ease;
    cursor: pointer;
}

.winner-option .winner-radio:checked + .winner-btn {
    background: linear-gradient(120deg, #3de074, #1bb154);
    color: var(--white);
    box-shadow: 0 8px 16px rgba(27, 177, 84, 0.35);
}

.winner-option .winner-btn:hover {
    transform: translateY(-1px);
}

.winner-option.disabled .winner-btn {
    opacity: 0.6;
    cursor: not-allowed;
    pointer-events: none;
}

.winner-option.disabled .winner-radio:disabled + .winner-btn {
    opacity: 0.7;
    cursor: not-allowed;
}

.marcador:disabled,
.marcador[readonly] {
    background-color: #e9ecef;
    opacity: 0.7;
    cursor: not-allowed;
}

.confed-col {
    min-width: 160px;
    font-size: 1.1rem;
}

.confed-col div {
    font-size: 1.1rem;
    font-weight: 500;
}

.ganador-col {
    min-width: 170px;
}

.marcador-col {
    min-width: 180px;
}

.marcador-inputs .marcador {
    width: 60px;
    text-align: center;
}

.vs-badge.mini {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--primary-green);
    text-transform: uppercase;
    margin: 2px 0;
}

.bets-validation-summary {
    background: rgba(248, 252, 248, 0.9);
    border: 1px solid rgba(46, 125, 50, 0.18);
    border-radius: 18px;
    padding: 18px 22px;
}

.bets-validation-summary .btn-success {
    font-weight: 700;
    box-shadow: 0 10px 20px rgba(46, 125, 50, 0.22);
}

.bets-validation-summary .btn-outline-secondary {
    font-weight: 600;
    border-radius: 999px;
}

.bets-validation-summary .alert {
    border-radius: 12px;
    margin-bottom: 16px;
    border: none;
}

@media (max-width: 768px) {
    .match-teams {
        align-items: center;
    }

    .winner-options {
        gap: 8px;
    }

    .marcador-inputs .marcador {
        width: 50px;
    }
}
</style>
@push('scripts')
<script src="{{ asset('js/quiniela-autocomplete.js') }}"></script>
<script>
// Recargar la página automáticamente 1 vez al cargar para asegurar que todas las variables estén disponibles
(function() {
    var hasReloaded = sessionStorage.getItem('quinielaHasReloaded');
    
    if (!hasReloaded) {
        sessionStorage.setItem('quinielaHasReloaded', 'true');
        // Recargar la página después de un breve delay
        setTimeout(function() {
            window.location.reload(true);
        }, 100);
        return; // Salir para evitar ejecutar el resto del código
    } else {
        // Limpiar la bandera después de la recarga
        sessionStorage.removeItem('quinielaHasReloaded');
    }
})();

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('worldCupBetsForm');
    
    // Variables globales para el formulario
    let totalMatches = 0;
    let submitButton = null;
    let messageBox = null;
    let progressText = null;

    // Inicializar variables solo si el formulario existe
    if (form) {
        totalMatches = parseInt(form.dataset.matches || '0', 10);
        submitButton = document.getElementById('submitBetsButton');
        messageBox = document.getElementById('betsValidationMessage');
        progressText = document.getElementById('betsProgressText');
        
        // Verificar si ya tiene todas las apuestas guardadas
        const hasAllBets = submitButton && submitButton.dataset.hasAllBets === 'true';
        if (hasAllBets && submitButton) {
            submitButton.disabled = true;
            submitButton.classList.add('disabled');
            if (progressText) {
                progressText.textContent = 'Ya has guardado tus predicciones para esta fase.';
                progressText.classList.remove('text-warning', 'text-danger');
                progressText.classList.add('text-success', 'fw-semibold');
            }
        }
    }

    const getMatchRows = () => {
        if (!form) return [];
        return Array.from(form.querySelectorAll('tbody tr[data-match-key]'));
    };

    function showMessage(text, type = 'warning') {
        if (!messageBox) {
            return;
        }
        messageBox.textContent = text;
        messageBox.className = `alert alert-${type}`;
        messageBox.classList.remove('d-none');
    }

    function hideMessage() {
        if (!messageBox) {
            return;
        }
        messageBox.classList.add('d-none');
        messageBox.textContent = '';
    }

    function evaluateSelections() {
        if (!form || !submitButton) {
            return { allCompleted: false, pendingLabels: [] };
        }

        const rows = getMatchRows();
        let completed = 0;
        const pendingLabels = [];

        rows.forEach((row) => {
            // Buscar los radios de ganador
            const radios = row.querySelectorAll('input.winner-radio[type="radio"]');
            const matchLabel = row.dataset.matchLabel || row.dataset.matchKey;

            // Verificar que haya un ganador seleccionado (los marcadores no son obligatorios)
            // Verificar SOLO la propiedad checked (la que JavaScript controla)
            let checkedCount = 0;
            radios.forEach((radio) => {
                // Usar la propiedad checked directamente - es más confiable que el atributo
                if (radio.checked === true) {
                    checkedCount++;
                }
            });
            
            const winnerSelected = checkedCount === 1;

            if (winnerSelected) {
                completed += 1;
            } else {
                pendingLabels.push(matchLabel);
            }
        });

        const allCompleted = completed === totalMatches && totalMatches > 0;

        // Verificar si ya tiene todas las apuestas guardadas antes de habilitar
        const hasAllBets = submitButton && submitButton.dataset.hasAllBets === 'true';
        
        if (allCompleted && !hasAllBets) {
            if (submitButton) {
                submitButton.removeAttribute('disabled');
                submitButton.disabled = false;
                submitButton.classList.remove('disabled');
            }
            if (progressText) {
                progressText.classList.remove('text-warning', 'text-danger');
                progressText.classList.add('text-success', 'fw-semibold');
                progressText.textContent = '¡Listo! Todos los partidos de esta fase tienen un ganador seleccionado.';
            }
            hideMessage();
        } else {
            if (submitButton) {
                submitButton.setAttribute('disabled', 'disabled');
                submitButton.disabled = true;
                submitButton.classList.add('disabled');
            }
            const remaining = Math.max(totalMatches - completed, 0);
            if (progressText) {
                progressText.classList.remove('text-success', 'fw-semibold');
                progressText.classList.add('text-warning');
                progressText.textContent = `Te faltan ${remaining} partido${remaining === 1 ? '' : 's'} por completar. Selecciona el ganador en cada partido.`;
            }
            if (pendingLabels.length) {
                showMessage(`Debes seleccionar el ganador para: ${pendingLabels.join(', ')}`, 'warning');
            }
        }

        return { allCompleted, pendingLabels };
    }

    // Hacer la función evaluateSelections disponible globalmente para el script de autocompletar
    window.evaluateSelections = evaluateSelections;

    // Botón para aceptar y ocultar los resultados del torneo finalizado
    const acceptResultsBtn = document.getElementById('acceptTournamentResults');
    if (acceptResultsBtn) {
        acceptResultsBtn.addEventListener('click', function() {
            const resultsCard = document.getElementById('tournamentResultsCard');
            if (resultsCard) {
                resultsCard.style.display = 'none';
                // Guardar en sessionStorage que el usuario ya vio los resultados
                sessionStorage.setItem('tournamentResultsAccepted', 'true');
            }
        });
    }
    
    // Si el usuario ya aceptó los resultados anteriormente, ocultar la tarjeta
    // Pero solo si el torneo sigue cerrado (si hay un nuevo torneo, mostrar los resultados de nuevo)
    const tournamentResultsCard = document.getElementById('tournamentResultsCard');
    if (tournamentResultsCard) {
        // Verificar si hay un torneo cerrado actualmente
        const isTournamentClosed = tournamentResultsCard.style.display !== 'none';
        if (sessionStorage.getItem('tournamentResultsAccepted') === 'true' && isTournamentClosed) {
            // Solo ocultar si el usuario ya aceptó Y el torneo sigue cerrado
            // Si se genera un nuevo torneo, el sessionStorage se limpiará al recargar
            tournamentResultsCard.style.display = 'none';
        }
    }
    
    // Limpiar el sessionStorage cuando se detecta que hay un nuevo torneo activo
    @if(!$isTournamentClosed)
    sessionStorage.removeItem('tournamentResultsAccepted');
    @endif

    // Código del formulario (solo si existe)
    if (!form) {
        return;
    }

    // Verificar si ya tiene todas las apuestas guardadas al cargar
    const hasAllBetsOnLoad = submitButton && submitButton.dataset.hasAllBets === 'true';
    
    // Si ya tiene todas las apuestas, deshabilitar todos los campos
    if (hasAllBetsOnLoad) {
        const allRadios = form.querySelectorAll('.winner-radio');
        const allMarcadores = form.querySelectorAll('.marcador');
        
        allRadios.forEach(function(radio) {
            radio.disabled = true;
        });
        
        allMarcadores.forEach(function(input) {
            input.disabled = true;
            input.readOnly = true;
        });
    }

    // Escuchar cambios en los radios de ganador (evento 'change')
    form.addEventListener('change', (event) => {
        if (event.target.matches('.winner-radio')) {
            // No permitir cambios si ya está guardado
            const hasAllBets = submitButton && submitButton.dataset.hasAllBets === 'true';
            if (hasAllBets) {
                event.preventDefault();
                return;
            }
            evaluateSelections();
        }
    });

    // Escuchar cambios en los inputs de marcador (eventos 'input' y 'change')
    form.addEventListener('input', (event) => {
        if (event.target.matches('.marcador')) {
            // No permitir cambios si ya está guardado
            const hasAllBets = submitButton && submitButton.dataset.hasAllBets === 'true';
            if (hasAllBets) {
                event.preventDefault();
                return;
            }
            evaluateSelections();
        }
    });

    form.addEventListener('change', (event) => {
        if (event.target.matches('.marcador')) {
            // No permitir cambios si ya está guardado
            const hasAllBets = submitButton && submitButton.dataset.hasAllBets === 'true';
            if (hasAllBets) {
                event.preventDefault();
                return;
            }
            evaluateSelections();
        }
    });

    form.addEventListener('submit', (event) => {
        const { allCompleted } = evaluateSelections();
        const hasAllBets = submitButton && submitButton.dataset.hasAllBets === 'true';
        
        if (!allCompleted) {
            event.preventDefault();
            showMessage('Debes seleccionar el ganador en todos los partidos antes de guardar la Quiniela.', 'danger');
            return;
        }
        
        if (hasAllBets) {
            event.preventDefault();
            showMessage('Ya has guardado tus predicciones para esta fase.', 'info');
            return;
        }
        
        // Deshabilitar el botón después de enviar exitosamente
        // Se habilitará nuevamente si el servidor redirige con error
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.classList.add('disabled');
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Guardando...';
        }
    });
    
    // Verificar mensajes de éxito/error del servidor
    @if(session('success'))
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.classList.add('disabled');
            submitButton.dataset.hasAllBets = 'true';
            submitButton.innerHTML = '<i class="fas fa-check me-2"></i> Quiniela Ya Guardada';
            if (progressText) {
                progressText.textContent = '{{ session('success') }}';
                progressText.classList.remove('text-warning', 'text-danger');
                progressText.classList.add('text-success', 'fw-semibold');
            }
        }
    @endif
    
    @if(session('error'))
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.classList.remove('disabled');
            submitButton.innerHTML = '<i class="fas fa-save me-2"></i> Guardar Quiniela';
        }
    @endif

    // Ejecutar evaluación inicial al cargar la página
    setTimeout(function() {
        evaluateSelections();
    }, 100);
});
</script>
@endpush
@endsection
