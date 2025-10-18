@extends('layouts.app')

@section('content')
<div class="tournament-container">
    <div class="container-fluid">
        <!-- Header del Torneo -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="tournament-header">
                    <h1>
                        <i class="fas fa-trophy me-3"></i>
                        Simulador de Torneo Fleg Football
                    </h1>
                    <p>Crea tu torneo de 16 equipos con eliminación directa</p>
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
                        Escoge los equipos
                    </h4>
                    <small class="text-white opacity-75">
                        <i class="fas fa-info-circle me-1"></i>
                        Equipos con nombres por defecto del ranking FIFA
                    </small>
                </div>
                <div class="card-body">
                    <form id="tournamentForm">
                        <!-- Equipos en formato horizontal -->
                        <div class="row g-3 mb-4">
                            @php
                                $defaultTeams = [
                                    'Brasil', 'Argentina', 'Francia', 'Inglaterra', 'España', 'Países Bajos',
                                    'Portugal', 'Italia', 'Alemania', 'Bélgica', 'Croacia', 'Uruguay',
                                    'Marruecos', 'Colombia', 'México', 'Japón'
                                ];
                            @endphp
                            @for($i = 1; $i <= 16; $i++)
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                <div class="team-input-group-horizontal">
                                    <input type="text" 
                                           class="team-input-horizontal" 
                                           id="team{{ $i }}" 
                                           name="teams[]" 
                                           placeholder="Equipo {{ $i }}"
                                           value="{{ $defaultTeams[$i-1] }}"
                                           maxlength="30"
                                           required>
                                    <div class="team-checkbox-container-horizontal">
                                        <input class="team-checkbox-horizontal" 
                                               type="checkbox" 
                                               id="check{{ $i }}" 
                                               value="{{ $i }}"
                                               checked>
                                    </div>
                                </div>
                            </div>
                            @endfor
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="button" class="btn tournament-btn btn-generate btn-lg" id="generateTournament">
                                <i class="fas fa-play me-2"></i>
                                Generar Torneo
                            </button>
                            <div class="row g-2">
                                <div class="col-6">
                                    <button type="button" class="btn tournament-btn btn-select-all w-100" id="selectAllTeams">
                                        <i class="fas fa-check-double me-2"></i>
                                        Seleccionar Todos
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn tournament-btn btn-clear w-100" id="clearTeams">
                                        <i class="fas fa-trash me-2"></i>
                                        Limpiar Todo
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
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
                    </h4>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-warning" id="generateRandomResults">
                            <i class="fas fa-dice me-1"></i>
                            Resultados Aleatorios
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light" id="backToSelection">
                            <i class="fas fa-arrow-left me-1"></i>
                            Volver a Selección
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="tournamentBracket" class="tournament-bracket-horizontal">
                        <div class="tournament-empty-state">
                            <i class="fas fa-trophy"></i>
                            <h5>Ingresa los equipos y genera el torneo</h5>
                            <p>El torneo comenzará en octavos de final con 16 equipos</p>
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
                                <h5>Total de Equipos</h5>
                                <h3 id="totalTeams">0</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item info-rounds">
                                <h5>Rondas</h5>
                                <h3 id="totalRounds">4</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item info-matches">
                                <h5>Partidos</h5>
                                <h3 id="totalMatches">15</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item info-status">
                                <h5>Estado</h5>
                                <h3 id="tournamentStatus">Listo</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar el resultado del partido -->
<div class="modal fade modal-tournament" id="matchResultModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-futbol me-2"></i>
                    Resultado del Partido
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <h4 id="matchTeams" class="mb-4" style="color: var(--tournament-primary); font-weight: 700;"></h4>
                    <div class="row">
                        <div class="col-6">
                            <input type="number" class="score-input" id="score1" min="0" max="20" placeholder="0">
                        </div>
                        <div class="col-6">
                            <input type="number" class="score-input" id="score2" min="0" max="20" placeholder="0">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmResult">Confirmar Resultado</button>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    let tournamentData = {
        teams: [],
        rounds: [],
        currentRound: 0,
        currentMatch: 0
    };

    // Elementos del DOM
    const tournamentForm = document.getElementById('tournamentForm');
    const generateBtn = document.getElementById('generateTournament');
    const clearBtn = document.getElementById('clearTeams');
    const bracketContainer = document.getElementById('tournamentBracket');
    const tournamentInfo = document.getElementById('tournamentInfo');
    const matchModal = new bootstrap.Modal(document.getElementById('matchResultModal'));

    // Elementos del DOM adicionales
    const selectAllBtn = document.getElementById('selectAllTeams');
    const teamSelectionPanel = document.getElementById('teamSelectionPanel');
    const tournamentBracketPanel = document.getElementById('tournamentBracketPanel');
    const backToSelectionBtn = document.getElementById('backToSelection');
    const generateRandomResultsBtn = document.getElementById('generateRandomResults');

    // Event Listeners
    generateBtn.addEventListener('click', generateTournament);
    clearBtn.addEventListener('click', clearAllTeams);
    selectAllBtn.addEventListener('click', selectAllTeams);
    backToSelectionBtn.addEventListener('click', backToSelection);
    generateRandomResultsBtn.addEventListener('click', generateRandomResults);
    
    // Checkbox events
    document.querySelectorAll('.team-checkbox-horizontal').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const teamInput = document.getElementById('team' + this.value);
            if (this.checked) {
                teamInput.style.backgroundColor = '#E8F5E8';
                teamInput.style.borderColor = 'var(--primary-green)';
            } else {
                teamInput.style.backgroundColor = '';
                teamInput.style.borderColor = '';
            }
        });
    });

    // Aplicar estilo inicial a equipos pre-seleccionados
    document.querySelectorAll('.team-checkbox-horizontal:checked').forEach(checkbox => {
        const teamInput = document.getElementById('team' + checkbox.value);
        teamInput.style.backgroundColor = '#E8F5E8';
        teamInput.style.borderColor = 'var(--primary-green)';
    });

    function generateTournament() {
        const teams = [];
        const checkboxes = document.querySelectorAll('.team-checkbox-horizontal:checked');
        
        if (checkboxes.length !== 16) {
            alert('Debes seleccionar exactamente 16 equipos para el torneo.');
            return;
        }

        checkboxes.forEach(checkbox => {
            const teamInput = document.getElementById('team' + checkbox.value);
            const teamName = teamInput.value.trim();
            if (teamName) {
                teams.push({
                    id: parseInt(checkbox.value),
                    name: teamName
                });
            }
        });

        if (teams.length !== 16) {
            alert('Todos los equipos seleccionados deben tener un nombre.');
            return;
        }

        // Mezclar equipos aleatoriamente
        teams.sort(() => Math.random() - 0.5);

        tournamentData.teams = teams;
        tournamentData.rounds = [];
        tournamentData.currentRound = 0;
        tournamentData.currentMatch = 0;

        // Generar bracket
        generateBracket(teams);
        showTournamentInfo();
        
        // Cambiar a la vista del bracket
        switchToBracketView();
        
        // Mostrar mensaje de éxito
        showNotification('¡Torneo generado exitosamente! Comienza en octavos de final', 'success');
    }

    function generateBracket(teams) {
        const rounds = [
            { name: 'Octavos de Final', teams: teams, matches: [] },
            { name: 'Cuartos de Final', teams: [], matches: [] },
            { name: 'Semifinales', teams: [], matches: [] },
            { name: 'Final', teams: [], matches: [] }
        ];

        // Generar octavos de final
        for (let i = 0; i < teams.length; i += 2) {
            rounds[0].matches.push({
                id: `match-0-${i/2}`,
                team1: teams[i],
                team2: teams[i + 1],
                score1: null,
                score2: null,
                winner: null,
                played: false
            });
        }

        tournamentData.rounds = rounds;
        displayBracket();
    }

    function displayBracket() {
        let html = '<div class="tennis-bracket-container">';
        
        tournamentData.rounds.forEach((round, roundIndex) => {
            html += `
                <div class="bracket-round-tennis">
                    <h5 class="round-title-tennis">${round.name}</h5>
                    <div class="matches-container-tennis">
            `;
            
            round.matches.forEach((match, matchIndex) => {
                const matchClass = match.played ? 'played' : 
                                 (roundIndex === tournamentData.currentRound && 
                                  matchIndex === tournamentData.currentMatch) ? 'current' : '';
                
                html += `
                    <div class="match-container-tennis ${matchClass}" 
                         data-round="${roundIndex}" 
                         data-match="${matchIndex}"
                         onclick="playMatch(${roundIndex}, ${matchIndex})">
                        <div class="team-name-tennis ${match.winner && match.winner.id === match.team1.id ? 'winner' : ''}">
                            ${match.team1.name}
                        </div>
                        <div class="match-score-tennis ${match.played ? 'has-score' : 'vs-text'}">
                            ${match.played ? `${match.score1} - ${match.score2}` : 'VS'}
                        </div>
                        <div class="team-name-tennis ${match.winner && match.winner.id === match.team2.id ? 'winner' : ''}">
                            ${match.team2.name}
                        </div>
                    </div>
                `;
            });
            
            html += `
                    </div>
                </div>
            `;
        });

        html += '</div>';
        bracketContainer.innerHTML = html;
    }

    function playMatch(roundIndex, matchIndex) {
        const match = tournamentData.rounds[roundIndex].matches[matchIndex];
        
        if (match.played) {
            return;
        }

        document.getElementById('matchTeams').textContent = 
            `${match.team1.name} vs ${match.team2.name}`;
        document.getElementById('score1').value = '';
        document.getElementById('score2').value = '';
        
        // Guardar datos del partido actual
        window.currentMatch = { roundIndex, matchIndex };
        
        matchModal.show();
    }

    // Confirmar resultado del partido
    document.getElementById('confirmResult').addEventListener('click', function() {
        const score1 = parseInt(document.getElementById('score1').value);
        const score2 = parseInt(document.getElementById('score2').value);
        
        if (isNaN(score1) || isNaN(score2) || score1 < 0 || score2 < 0) {
            alert('Ingresa puntuaciones válidas (números positivos).');
            return;
        }

        const { roundIndex, matchIndex } = window.currentMatch;
        const match = tournamentData.rounds[roundIndex].matches[matchIndex];
        
        match.score1 = score1;
        match.score2 = score2;
        match.winner = score1 > score2 ? match.team1 : 
                      score2 > score1 ? match.team2 : null;
        match.played = true;

        // Si hay empate, el ganador se decide por sorteo
        if (!match.winner) {
            match.winner = Math.random() > 0.5 ? match.team1 : match.team2;
        }

        // Avanzar al siguiente partido o ronda
        advanceTournament(roundIndex, matchIndex);
        
        matchModal.hide();
        displayBracket();
    });

    function advanceTournament(roundIndex, matchIndex) {
        // Si es la ronda actual, avanzar al siguiente partido
        if (roundIndex === tournamentData.currentRound) {
            const currentRound = tournamentData.rounds[roundIndex];
            
            // Verificar si todos los partidos de esta ronda están jugados
            const allPlayed = currentRound.matches.every(match => match.played);
            
            if (allPlayed && roundIndex < 3) {
                // Generar siguiente ronda
                generateNextRound(roundIndex + 1);
                tournamentData.currentRound++;
                tournamentData.currentMatch = 0;
            } else {
                tournamentData.currentMatch++;
            }
        }
    }

    function generateNextRound(nextRoundIndex) {
        const currentRound = tournamentData.rounds[nextRoundIndex - 1];
        const winners = currentRound.matches.map(match => match.winner);
        
        const nextRound = tournamentData.rounds[nextRoundIndex];
        nextRound.teams = winners;
        nextRound.matches = [];

        for (let i = 0; i < winners.length; i += 2) {
            nextRound.matches.push({
                id: `match-${nextRoundIndex}-${i/2}`,
                team1: winners[i],
                team2: winners[i + 1],
                score1: null,
                score2: null,
                winner: null,
                played: false
            });
        }
    }

    function selectAllTeams() {
        document.querySelectorAll('.team-checkbox-horizontal').forEach(checkbox => {
            checkbox.checked = true;
            const teamInput = document.getElementById('team' + checkbox.value);
            teamInput.style.backgroundColor = '#E8F5E8';
            teamInput.style.borderColor = 'var(--primary-green)';
        });
        
        // Mostrar mensaje de confirmación
        showNotification('¡Todos los equipos han sido seleccionados!', 'success');
    }

    function switchToBracketView() {
        teamSelectionPanel.style.display = 'none';
        tournamentBracketPanel.style.display = 'block';
        tournamentInfo.style.display = 'block';
    }

    function backToSelection() {
        tournamentBracketPanel.style.display = 'none';
        tournamentInfo.style.display = 'none';
        teamSelectionPanel.style.display = 'block';
        
        // Mostrar mensaje de confirmación
        showNotification('Volviendo a la selección de equipos', 'info');
    }

    function generateRandomResults() {
        if (!tournamentData.rounds || tournamentData.rounds.length === 0) {
            showNotification('Primero debes generar un torneo', 'warning');
            return;
        }

        // Resetear el torneo
        tournamentData.currentRound = 0;
        tournamentData.currentMatch = 0;
        tournamentData.rounds.forEach(round => {
            round.matches.forEach(match => {
                match.score1 = null;
                match.score2 = null;
                match.winner = null;
                match.played = false;
            });
        });

        // Simular todos los partidos
        simulateAllMatches();
        
        // Actualizar la vista
        displayBracket();
        
        // Mostrar mensaje de éxito
        showNotification('¡Todos los resultados han sido generados aleatoriamente!', 'success');
    }

    function simulateAllMatches() {
        // Simular partidos de octavos de final
        simulateRound(0);
        
        // Generar cuartos de final y simular
        if (tournamentData.rounds.length > 1) {
            generateNextRound(1);
            simulateRound(1);
            
            // Generar semifinales y simular
            if (tournamentData.rounds.length > 2) {
                generateNextRound(2);
                simulateRound(2);
                
                // Generar final y simular
                if (tournamentData.rounds.length > 3) {
                    generateNextRound(3);
                    simulateRound(3);
                }
            }
        }
    }

    function simulateRound(roundIndex) {
        const round = tournamentData.rounds[roundIndex];
        round.matches.forEach((match, matchIndex) => {
            // Generar puntuaciones aleatorias (0-5 goles)
            const score1 = Math.floor(Math.random() * 6);
            const score2 = Math.floor(Math.random() * 6);
            
            // Asegurar que no haya empate
            let finalScore1 = score1;
            let finalScore2 = score2;
            
            if (score1 === score2) {
                // Si hay empate, agregar un gol aleatorio
                if (Math.random() > 0.5) {
                    finalScore1++;
                } else {
                    finalScore2++;
                }
            }
            
            // Asignar resultados
            match.score1 = finalScore1;
            match.score2 = finalScore2;
            match.winner = finalScore1 > finalScore2 ? match.team1 : match.team2;
            match.played = true;
            
            // Pequeña pausa visual para el efecto
            setTimeout(() => {
                // Actualizar solo este partido en la vista
                updateMatchDisplay(roundIndex, matchIndex);
            }, matchIndex * 200); // 200ms entre cada partido
        });
    }

    function updateMatchDisplay(roundIndex, matchIndex) {
        const match = tournamentData.rounds[roundIndex].matches[matchIndex];
        const matchElement = document.querySelector(`[data-round="${roundIndex}"][data-match="${matchIndex}"]`);
        
        if (matchElement) {
            // Agregar clase de animación
            matchElement.classList.add('simulating');
            
            // Actualizar el contenido del partido
            const team1Element = matchElement.querySelector('.team-name-tennis:first-child');
            const team2Element = matchElement.querySelector('.team-name-tennis:last-child');
            const scoreElement = matchElement.querySelector('.match-score-tennis');
            
            if (team1Element && team2Element && scoreElement) {
                // Actualizar puntuación
                scoreElement.textContent = `${match.score1} - ${match.score2}`;
                
                // Actualizar clases de ganador
                if (match.winner.id === match.team1.id) {
                    team1Element.classList.add('winner');
                    team2Element.classList.remove('winner');
                } else {
                    team2Element.classList.add('winner');
                    team1Element.classList.remove('winner');
                }
                
                // Marcar como jugado
                matchElement.classList.remove('current');
                matchElement.classList.add('played');
            }
            
            // Remover clase de animación después de que termine
            setTimeout(() => {
                matchElement.classList.remove('simulating');
            }, 600);
        }
    }

    function clearAllTeams() {
        document.querySelectorAll('.team-input-horizontal').forEach(input => {
            input.value = '';
            input.style.backgroundColor = '';
            input.style.borderColor = '';
        });
        
        document.querySelectorAll('.team-checkbox-horizontal').forEach(checkbox => {
            checkbox.checked = false;
        });
        
        bracketContainer.innerHTML = `
            <div class="tournament-empty-state">
                <i class="fas fa-trophy"></i>
                <h5>Ingresa los equipos y genera el torneo</h5>
                <p>El torneo comenzará en octavos de final con 16 equipos</p>
            </div>
        `;
        
        tournamentInfo.style.display = 'none';
        tournamentData = {
            teams: [],
            rounds: [],
            currentRound: 0,
            currentMatch: 0
        };
        
        // Mostrar mensaje de confirmación
        showNotification('Todos los equipos han sido limpiados', 'info');
    }

    function showNotification(message, type = 'info') {
        // Crear elemento de notificación
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remover después de 3 segundos
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }

    function showTournamentInfo() {
        document.getElementById('totalTeams').textContent = tournamentData.teams.length;
        document.getElementById('totalRounds').textContent = tournamentData.rounds.length;
        document.getElementById('totalMatches').textContent = 
            tournamentData.rounds.reduce((total, round) => total + round.matches.length, 0);
        document.getElementById('tournamentStatus').textContent = 'En Progreso';
        
        tournamentInfo.style.display = 'block';
    }

    // Hacer función global para onclick
    window.playMatch = playMatch;
});
</script>
@endsection
