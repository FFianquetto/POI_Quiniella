(function (global) {
    const namespace = global.TournamentSim = global.TournamentSim || {};
    const constants = namespace.constants;
    const stateModule = namespace.state;
    const createSyncService = namespace.createSyncService;
    const showNotification = namespace.showNotification;

    if (!constants || !stateModule || !createSyncService || !showNotification) {
        throw new Error('controller.js requiere que constants, state, api y notifications se carguen previamente.');
    }

    class TournamentController {
        constructor(doc = document) {
            this.doc = doc;
            this.hasHydratedInitialTournament = false;
            this.worldCupTeamsDataset = [];
            this.syncService = null;

            this.cacheDomReferences();
        }

        cacheDomReferences() {
            this.generateBtn = this.doc.getElementById('generateTournament');
            this.tournamentLockMessage = this.doc.getElementById('tournamentLockMessage');
            this.activeTournamentElement = this.doc.getElementById('activeTournamentData');
            this.openSimulatorBtn = this.doc.getElementById('openTournamentSimulator');
            this.clearBtn = this.doc.getElementById('clearTeams');
            this.bracketContainer = this.doc.getElementById('tournamentBracket');
            this.tournamentInfo = this.doc.getElementById('tournamentInfo');
            this.favoriteTeamSelect = this.doc.getElementById('favoriteTeam');
            this.favoriteTeamFeedback = this.doc.getElementById('favoriteTeamFeedback');
            this.selectAllBtn = this.doc.getElementById('selectAllTeams');
            this.teamSelectionPanel = this.doc.getElementById('teamSelectionPanel');
            this.tournamentBracketPanel = this.doc.getElementById('tournamentBracketPanel');
            this.backToSelectionBtn = this.doc.getElementById('backToSelection');
            this.simulateCurrentRoundBtn = this.doc.getElementById('simulateCurrentRound');
            this.simulateWithArgentinaWinnerBtn = this.doc.getElementById('simulateWithArgentinaWinner');
            this.championTeamLabel = this.doc.getElementById('championTeamLabel');
            this.currentRoundLabel = this.doc.getElementById('currentRoundName');
            this.teamsDataElement = this.doc.getElementById('worldCupTeamsData');
            this.totalTeamsElement = this.doc.getElementById('totalTeams');
            this.totalRoundsElement = this.doc.getElementById('totalRounds');
            this.totalMatchesElement = this.doc.getElementById('totalMatches');
            this.tournamentStatusElement = this.doc.getElementById('tournamentStatus');
            this.selectedTeamDisplay = this.doc.getElementById('selectedTeamDisplay');
        }

        init() {
            if (!this.generateBtn) {
                console.error('TournamentController.init: El botón generateTournament no se encontró en el DOM.');
                console.error('Intentando buscar el botón nuevamente...');
                this.generateBtn = this.doc.getElementById('generateTournament');
                if (!this.generateBtn) {
                    console.error('TournamentController.init: El botón aún no está disponible. Verifica que el HTML se haya cargado correctamente.');
                    return;
                }
            }

            try {
                this.setupDatasets();
                this.setupServices();
                this.bindEventListeners();
                this.applyInitialStyles();
                this.syncFavoriteOptions();
                this.updateChampionDisplay();
                this.hydrateInitialTournament();
                this.exposeGlobalActions();
            } catch (error) {
                console.error('Error en TournamentController.init:', error);
                console.error('Stack trace:', error.stack);
                throw error;
            }
        }

        setupDatasets() {
            if (this.teamsDataElement) {
                try {
                    this.worldCupTeamsDataset = JSON.parse(this.teamsDataElement.dataset.teams || '[]');
                } catch (error) {
                    console.warn('No se pudo interpretar los equipos mundialistas', error);
                    this.worldCupTeamsDataset = [];
                }
            }

            const csrfMeta = this.doc.querySelector('meta[name="csrf-token"]');
            this.csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : null;
        }

        setupServices() {
            this.syncService = createSyncService({
                csrfToken: this.csrfToken,
                worldCupTeamsDataset: this.worldCupTeamsDataset,
                lockGenerateButton: (message) => this.lockGenerateButton(message),
                unlockGenerateButton: (message) => this.unlockGenerateButton(message),
                showNotification,
                onConflictHydrate: () => {
                    if (!this.hasHydratedInitialTournament) {
                        this.hydrateInitialTournament();
                    }
                }
            });
        }

        bindEventListeners() {
            if (!this.generateBtn) {
                console.error('bindEventListeners: generateBtn no está disponible');
                return;
            }
            
            try {
                this.generateBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Botón Generar Mundial clickeado');
                    this.generateTournament();
                });
                console.log('Event listener del botón Generar Mundial registrado correctamente');
            } catch (error) {
                console.error('Error al registrar event listener del botón:', error);
            }
            if (this.clearBtn) {
                this.clearBtn.addEventListener('click', () => this.clearAllTeams());
            }
            if (this.selectAllBtn) {
                this.selectAllBtn.addEventListener('click', () => this.selectAllTeams());
            }
            if (this.backToSelectionBtn) {
                this.backToSelectionBtn.addEventListener('click', () => this.backToSelection());
            }
            if (this.simulateCurrentRoundBtn) {
                this.simulateCurrentRoundBtn.addEventListener('click', () => this.simulateCurrentRound());
            }
            if (this.simulateWithArgentinaWinnerBtn) {
                this.simulateWithArgentinaWinnerBtn.addEventListener('click', () => this.simulateCurrentRoundWithArgentinaWinner());
            }
            if (this.openSimulatorBtn) {
                this.openSimulatorBtn.addEventListener('click', () => this.openCurrentTournament(true));
            }
            // Sistema de achievements removido - ya no se requiere

            this.doc.querySelectorAll('.team-checkbox-horizontal').forEach((checkbox) => {
                checkbox.addEventListener('change', () => {
                    const teamInput = this.doc.getElementById('team' + checkbox.value);
                    if (!teamInput) {
                        return;
                    }
                    if (checkbox.checked) {
                        teamInput.style.backgroundColor = '#E8F5E8';
                        teamInput.style.borderColor = 'var(--primary-green)';
                    } else {
                        teamInput.style.backgroundColor = '';
                        teamInput.style.borderColor = '';
                    }
                });
            });

            this.doc.querySelectorAll('.team-input-horizontal').forEach((input) => {
                input.addEventListener('input', () => this.syncFavoriteOptions());
            });
        }

        exposeGlobalActions() {
            global.playMatch = (roundIndex, matchIndex) => this.playMatch(roundIndex, matchIndex);
            global.playThirdPlace = () => this.playThirdPlace();
        }

        applyInitialStyles() {
            this.doc.querySelectorAll('.team-checkbox-horizontal:checked').forEach((checkbox) => {
                const teamInput = this.doc.getElementById('team' + checkbox.value);
                if (teamInput) {
                    teamInput.style.backgroundColor = '#E8F5E8';
                    teamInput.style.borderColor = 'var(--primary-green)';
                }
            });
        }

        syncFavoriteOptions() {
            // País favorito removido - ya no se requiere
        }

        updateChampionDisplay() {
            const tournamentData = stateModule.getTournamentData();

            if (this.championTeamLabel) {
                if (tournamentData.rewards && tournamentData.rewards.champion) {
                    const champion = tournamentData.rewards.champion;
                    this.championTeamLabel.textContent = `${champion.name} (${champion.code})`;
                } else {
                    this.championTeamLabel.textContent = 'Pendiente';
                }
            }
        }

        updateGenerateButtonState({ disabled, message, tone = 'warning' } = {}) {
            if (!this.generateBtn) {
                return;
            }

            let effectiveDisabled = this.generateBtn.disabled;

            if (typeof disabled === 'boolean') {
                this.generateBtn.disabled = disabled;
                this.generateBtn.setAttribute('aria-disabled', disabled ? 'true' : 'false');
                effectiveDisabled = disabled;
            }

            if (this.tournamentLockMessage) {
                if (message) {
                    this.tournamentLockMessage.textContent = message;
                    this.tournamentLockMessage.classList.remove('d-none');

                    const toneClass = tone === 'error'
                        ? 'text-danger'
                        : tone === 'success'
                            ? 'text-success'
                            : 'text-tournament-orange';

                    this.tournamentLockMessage.classList.remove('text-danger', 'text-success', 'text-tournament-orange');
                    this.tournamentLockMessage.classList.add(toneClass);
                } else {
                    this.tournamentLockMessage.classList.add('d-none');
                }
            }

            if (this.openSimulatorBtn) {
                if (effectiveDisabled) {
                    this.openSimulatorBtn.classList.remove('d-none');
                } else {
                    this.openSimulatorBtn.classList.add('d-none');
                }
            }
        }

        lockGenerateButton(message) {
            this.updateGenerateButtonState({
                disabled: true,
                message: message || 'Ya hay un torneo en curso. Finalízalo antes de generar uno nuevo.',
                tone: 'warning'
            });
        }

        unlockGenerateButton(message) {
            this.updateGenerateButtonState({
                disabled: false,
                message: message || '',
                tone: 'success'
            });
        }

        hydrateInitialTournament() {
            if (this.hasHydratedInitialTournament) {
                return;
            }

            this.hasHydratedInitialTournament = true;

            if (!this.activeTournamentElement) {
                this.unlockGenerateButton();
                return;
            }

            const raw = this.activeTournamentElement.dataset.tournament;

            if (!raw || raw === 'null' || raw === 'undefined') {
                this.unlockGenerateButton();
                return;
            }

            let payload = null;

            try {
                payload = JSON.parse(raw);
            } catch (error) {
                console.warn('No se pudo interpretar el torneo activo', error);
                this.unlockGenerateButton();
                return;
            }

            if (!payload || typeof payload !== 'object') {
                this.unlockGenerateButton();
                return;
            }

            const teams = Array.isArray(payload.teams) && payload.teams.length
                ? payload.teams.map((team, index) => ({
                    ...team,
                    id: team.id ?? index + 1
                }))
                : this.worldCupTeamsDataset.map((team) => ({ ...team }));

            const baseState = stateModule.createInitialTournamentState({
                uuid: payload.uuid || null,
                favoriteTeamCode: payload.favorite_team || null,
                status: payload.status || 'in_progress',
                teams
            });

            stateModule.setTournamentData(baseState);
            const tournamentData = stateModule.getTournamentData();

            if (tournamentData.favoriteTeamCode) {
                const favorite = tournamentData.teams.find((team) => team.code === tournamentData.favoriteTeamCode);
                tournamentData.favoriteTeamName = favorite ? favorite.name : null;
            } else {
                tournamentData.favoriteTeamName = null;
            }

            tournamentData.rounds = Array.isArray(payload.rounds)
                ? payload.rounds.map((round, roundIndex) => {
                    const matches = Array.isArray(round.matches)
                        ? round.matches.map((match, matchIndex) => {
                            const matchKey = match.match_key || match.id || `round-${roundIndex}-match-${matchIndex}`;
                            return {
                                ...match,
                                id: match.id || matchKey,
                                match_key: matchKey,
                                team1: match.team1 || match.team_a || null,
                                team2: match.team2 || match.team_b || null,
                                winner: match.winner || null,
                                played: Boolean(match.played)
                            };
                        })
                        : [];

                    return {
                        name: round.name || constants.ROUND_NAMES[roundIndex] || `Ronda ${roundIndex + 1}`,
                        matches
                    };
                })
                : [];

            const storedThirdPlace = payload.results?.third_place ?? null;
            tournamentData.thirdPlaceMatch = storedThirdPlace
                ? {
                    ...storedThirdPlace,
                    match_key: storedThirdPlace.match_key || storedThirdPlace.id || 'third-place',
                    team1: storedThirdPlace.team1 || storedThirdPlace.team_a || null,
                    team2: storedThirdPlace.team2 || storedThirdPlace.team_b || null,
                    played: Boolean(storedThirdPlace.played)
                }
                : null;

            const storedRewards = payload.results?.rewards ?? null;
            tournamentData.rewards = storedRewards
                ? {
                    champion: storedRewards.champion || null,
                    runnerUp: storedRewards.runnerUp || storedRewards.runner_up || null,
                    thirdPlace: storedRewards.thirdPlace || storedRewards.third_place || null,
                    fourthPlace: storedRewards.fourthPlace || storedRewards.fourth_place || null
                }
                : stateModule.createEmptyRewards();

            const hasRounds = tournamentData.rounds.length > 0;
            if (hasRounds) {
                let currentRoundIndex = 0;
                let currentMatchIndex = 0;
                let foundPending = false;

                for (let index = 0; index < tournamentData.rounds.length; index++) {
                    const matches = tournamentData.rounds[index]?.matches ?? [];
                    const pendingIndex = matches.findIndex((match) => !match.played);

                    if (pendingIndex !== -1) {
                        currentRoundIndex = index;
                        currentMatchIndex = pendingIndex;
                        foundPending = true;
                        break;
                    }
                }

                if (!foundPending) {
                    currentRoundIndex = tournamentData.rounds.length - 1;
                    currentMatchIndex = 0;
                }

                if (tournamentData.thirdPlaceMatch && !tournamentData.thirdPlaceMatch.played) {
                    currentRoundIndex = constants.ROUND_NAMES.length;
                    currentMatchIndex = 0;
                }

                tournamentData.currentRound = currentRoundIndex;
                tournamentData.currentMatch = currentMatchIndex;
            }

            // País favorito removido - ya no se requiere

            if (hasRounds) {
                this.displayBracket();
                this.showTournamentInfo(this.determineTournamentStatus());
            }

            this.updateChampionDisplay();

            // Si hay un torneo con UUID, siempre bloquear el botón (incluso sin rounds)
            if (tournamentData.uuid) {
                if (tournamentData.status === 'completed' || tournamentData.status === 'archived') {
                    this.unlockGenerateButton('La última Copa finalizó. Puedes generar una nueva simulación.');
                    // Mostrar botón "Volver a Selección" cuando el torneo está completado
                    if (this.backToSelectionBtn) {
                        this.backToSelectionBtn.style.display = 'inline-block';
                    }
                } else {
                    this.lockGenerateButton('Tienes un Mundial en curso. Completa los partidos pendientes antes de generar otro.');
                    // Ocultar botón "Volver a Selección" durante el torneo activo
                    if (this.backToSelectionBtn) {
                        this.backToSelectionBtn.style.display = 'none';
                    }
                    // Si hay rounds, mostrar el bracket; si no, mostrar mensaje de que el torneo está en preparación
                    if (hasRounds) {
                        this.openCurrentTournament(false);
                    } else {
                        // Torneo existe pero no tiene rounds aún - mostrar información del torneo
                        this.switchToBracketView();
                        this.showTournamentInfo('En Preparación');
                        // Mostrar mensaje en el bracket de que el torneo está generado pero sin partidos
                        if (this.bracketContainer) {
                            this.bracketContainer.innerHTML = `
                                <div class="tournament-empty-state">
                                    <i class="fas fa-clock fa-3x mb-3 text-warning"></i>
                                    <h5>Torneo Generado</h5>
                                    <p>El torneo del Mundial 2026 ha sido generado. Los enfrentamientos se mostrarán aquí una vez que se definan los partidos de la primera ronda.</p>
                                    <p class="text-muted small">Equipo favorito: ${tournamentData.favoriteTeamName || 'No seleccionado'}</p>
                                </div>
                            `;
                        }
                    }
                }
            } else {
                this.unlockGenerateButton();
                // Si no hay torneo, ocultar el botón "Volver a Selección"
                if (this.backToSelectionBtn) {
                    this.backToSelectionBtn.style.display = 'none';
                }
            }
        }

        generateTournament() {
            console.log('generateTournament() llamado');
            
            try {
                const tournamentData = stateModule.getTournamentData();
                console.log('Estado del torneo:', tournamentData);

                if (tournamentData.status === 'in_progress') {
                    console.warn('Ya hay un torneo en progreso');
                    this.lockGenerateButton('Ya tienes un Mundial en curso. Finaliza la simulación antes de iniciar otro.');
                    showNotification('Ya existe un torneo activo. Continúa los partidos pendientes o finalízalo.', 'warning');
                    this.switchToBracketView();
                    return;
                }

                console.log('Equipos disponibles:', this.worldCupTeamsDataset.length);
                if (!this.worldCupTeamsDataset.length) {
                    console.warn('No hay equipos en el dataset');
                    showNotification('No hay selecciones registradas para generar el torneo.', 'danger');
                    return;
                }

                if (this.worldCupTeamsDataset.length !== 32) {
                    console.warn(`Solo hay ${this.worldCupTeamsDataset.length} equipos, se requieren 32`);
                    showNotification('Se requieren las 32 selecciones mundialistas para iniciar la simulación.', 'warning');
                    return;
                }

                console.log('Iniciando generación del torneo...');

                const teams = this.worldCupTeamsDataset.map((team) => ({ ...team }));

                const baseState = stateModule.createInitialTournamentState({
                    status: 'in_progress'
                });

                stateModule.setTournamentData(baseState);
                const currentState = stateModule.getTournamentData();

                console.log('Generando bracket...');
                this.generateBracket(teams, { shuffle: true });
                currentState.favoriteTeamCode = null;
                currentState.favoriteTeamName = null;
                currentState.status = 'in_progress';

                this.lockGenerateButton('Torneo en curso. Completa todos los partidos para generar uno nuevo.');
                this.showTournamentInfo('En Marcha');
                this.updateChampionDisplay();
                this.resetRewardsPanel();
                this.switchToBracketView();
                
                // Ocultar el panel de selección y asegurar que el bracket permanezca visible
                if (this.teamSelectionPanel) {
                    this.teamSelectionPanel.style.display = 'none';
                }
                
                // Ocultar botón "Volver a Selección" durante el torneo activo
                if (this.backToSelectionBtn) {
                    this.backToSelectionBtn.style.display = 'none';
                }
                
                console.log('Sincronizando estado del torneo con el servidor...');
                if (this.syncService) {
                    this.syncService.syncTournamentState({ initial: true });
                } else {
                    console.warn('syncService no está disponible');
                }
                
                showNotification('¡La Copa Mundial 2026 está configurada! Comenzamos en dieciseisavos de final.', 'success');
                console.log('✅ Torneo generado exitosamente');
            } catch (error) {
                console.error('❌ Error al generar el torneo:', error);
                console.error('Stack trace:', error.stack);
                showNotification('Error al generar el torneo. Por favor, recarga la página e intenta nuevamente.', 'danger');
            }

            showNotification('¡La Copa Mundial 2026 está configurada! Comenzamos en dieciseisavos de final.', 'success');
        }

        generateBracket(teams, options = {}) {
            const { shuffle = false } = options;
            const workingTeams = [...teams];

            if (shuffle) {
                this.shuffleArray(workingTeams);
            }

            const tournamentData = stateModule.getTournamentData();
            tournamentData.teams = workingTeams;
            tournamentData.rounds = constants.ROUND_NAMES.map((name) => ({ name, matches: [] }));
            tournamentData.currentRound = 0;
            tournamentData.currentMatch = 0;
            tournamentData.thirdPlaceMatch = null;
            tournamentData.rewards = stateModule.createEmptyRewards();

            for (let i = 0; i < workingTeams.length; i += 2) {
                tournamentData.rounds[0].matches.push(
                    this.createMatchObject(workingTeams[i], workingTeams[i + 1], 0, i / 2)
                );
            }

            this.displayBracket();
        }

        createMatchObject(team1, team2, roundIndex, matchIndex) {
            const matchKey = `round-${roundIndex}-match-${matchIndex}`;
            return {
                id: matchKey,
                match_key: matchKey,
                team1,
                team2,
                score1: null,
                score2: null,
                winner: null,
                played: false,
                decidedByPenalties: false,
                penaltyScore: null
            };
        }

        shuffleArray(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
        }

        displayBracket() {
            const tournamentData = stateModule.getTournamentData();
            let html = '<div class="tennis-bracket-container">';

            tournamentData.rounds.forEach((round, roundIndex) => {
                const roundName = constants.ROUND_NAMES[roundIndex];
                html += `
                    <div class="bracket-round-tennis">
                        <h5 class="round-title-tennis">${roundName}</h5>
                        <div class="matches-container-tennis">
                `;

                round.matches.forEach((match, matchIndex) => {
                    const matchClass = match.played
                        ? 'played'
                        : roundIndex === tournamentData.currentRound && matchIndex === tournamentData.currentMatch
                            ? 'current'
                            : '';

                    html += `
                        <div class="match-container-tennis ${matchClass}"
                             data-round="${roundIndex}"
                             data-match="${matchIndex}"
                             onclick="playMatch(${roundIndex}, ${matchIndex})">
                            <div class="team-name-tennis ${match.winner && match.team1 && match.winner.id === match.team1.id ? 'winner' : ''}">
                                ${this.formatTeamName(match.team1)}
                            </div>
                            <div class="match-score-tennis ${match.played ? 'has-score' : 'vs-text'}">
                                ${this.formatMatchScore(match)}
                            </div>
                            <div class="team-name-tennis ${match.winner && match.team2 && match.winner.id === match.team2.id ? 'winner' : ''}">
                                ${this.formatTeamName(match.team2)}
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

            if (tournamentData.thirdPlaceMatch) {
                const match = tournamentData.thirdPlaceMatch;
                const matchClass = match.played ? 'played' : '';
                html += `
                    <div class="bracket-round-tennis third-place-block">
                        <h5 class="round-title-tennis">Tercer Lugar</h5>
                        <div class="matches-container-tennis">
                            <div class="match-container-tennis ${matchClass}"
                                 data-third="true"
                                 onclick="playThirdPlace()">
                                <div class="team-name-tennis ${match.winner && match.team1 && match.winner.id === match.team1.id ? 'winner' : ''}">
                                    ${this.formatTeamName(match.team1)}
                                </div>
                                <div class="match-score-tennis ${match.played ? 'has-score' : 'vs-text'}">
                                    ${this.formatMatchScore(match)}
                                </div>
                                <div class="team-name-tennis ${match.winner && match.team2 && match.winner.id === match.team2.id ? 'winner' : ''}">
                                    ${this.formatTeamName(match.team2)}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }

            if (this.bracketContainer) {
                this.bracketContainer.innerHTML = html;
            }
        }

        formatTeamName(team) {
            return team ? `${team.name} (${team.code})` : 'Por definir';
        }

        formatMatchScore(match) {
            if (!match.played) {
                return 'VS';
            }

            let baseScore = `${match.score1} - ${match.score2}`;
            if (match.decidedByPenalties && match.penaltyScore) {
                baseScore += ` (pen ${match.penaltyScore})`;
            }

            return baseScore;
        }

        playMatch(roundIndex, matchIndex) {
            const tournamentData = stateModule.getTournamentData();
            const round = tournamentData.rounds[roundIndex];
            if (!round) {
                return;
            }

            const match = round.matches[matchIndex];
            if (!match || match.played || !match.team1 || !match.team2) {
                return;
            }

            this.runMatch(roundIndex, matchIndex);
        }

        runMatch(roundIndex, matchIndex, options = {}) {
            const tournamentData = stateModule.getTournamentData();
            const match = tournamentData.rounds[roundIndex].matches[matchIndex];
            if (!match || match.played) {
                return;
            }

            const result = this.resolveMatch(match.team1, match.team2, options.forceArgentinaWin);
            match.score1 = result.score1;
            match.score2 = result.score2;
            match.winner = result.winner;
            match.decidedByPenalties = result.decidedByPenalties;
            match.penaltyScore = result.penaltyScore;
            match.played = true;

            if (!options.auto) {
                showNotification(
                    `Resultado: ${match.team1.name} ${match.score1}-${match.score2} ${match.team2.name}${
                        result.decidedByPenalties ? ' (penales)' : ''
                    }`,
                    'success'
                );
            }

            this.advanceTournament(roundIndex);
            this.displayBracket();
            this.showTournamentInfo(this.determineTournamentStatus());
            this.syncService.syncTournamentState();
        }

        resolveMatch(team1, team2, forceArgentinaWin = false) {
            // Si se fuerza que Argentina gane y Argentina está en el partido, hacer que gane
            if (forceArgentinaWin) {
                const isTeam1Argentina = team1.code === 'ARG' || team1.name === 'Argentina';
                const isTeam2Argentina = team2.code === 'ARG' || team2.name === 'Argentina';
                
                if (isTeam1Argentina || isTeam2Argentina) {
                    const argentinaTeam = isTeam1Argentina ? team1 : team2;
                    const opponentTeam = isTeam1Argentina ? team2 : team1;
                    
                    // Generar un marcador donde Argentina gana
                    const argentinaGoals = 2 + Math.floor(Math.random() * 3); // 2-4 goles
                    const opponentGoals = Math.min(argentinaGoals - 1, Math.floor(Math.random() * 2)); // 0-1 goles, siempre menos que Argentina
                    
                    return {
                        score1: isTeam1Argentina ? argentinaGoals : opponentGoals,
                        score2: isTeam1Argentina ? opponentGoals : argentinaGoals,
                        winner: argentinaTeam,
                        decidedByPenalties: false,
                        penaltyScore: null
                    };
                }
            }

            const random1 = Math.random() * (team1.ranking + 1);
            const random2 = Math.random() * (team2.ranking + 1);

            let goals1 = Math.min(constants.MAX_GOALS, Math.floor(random1 / constants.GOAL_DIVISOR));
            let goals2 = Math.min(constants.MAX_GOALS, Math.floor(random2 / constants.GOAL_DIVISOR));

            if (goals1 !== goals2) {
                return {
                    score1: goals1,
                    score2: goals2,
                    winner: goals1 > goals2 ? team1 : team2,
                    decidedByPenalties: false,
                    penaltyScore: null
                };
            }

            const penaltyResult = this.resolvePenalties(team1, team2, forceArgentinaWin);
            return {
                score1: goals1,
                score2: goals2,
                winner: penaltyResult.winner,
                decidedByPenalties: true,
                penaltyScore: penaltyResult.score
            };
        }

        resolvePenalties(team1, team2, forceArgentinaWin = false) {
            // Si se fuerza que Argentina gane y Argentina está en el partido, hacer que gane en penales
            if (forceArgentinaWin) {
                const isTeam1Argentina = team1.code === 'ARG' || team1.name === 'Argentina';
                const isTeam2Argentina = team2.code === 'ARG' || team2.name === 'Argentina';
                
                if (isTeam1Argentina || isTeam2Argentina) {
                    const argentinaTeam = isTeam1Argentina ? team1 : team2;
                    const opponentTeam = isTeam1Argentina ? team2 : team1;
                    
                    const winnerScore = 5;
                    const loserScore = 3 + Math.floor(Math.random() * 2); // 3-4 goles
                    
                    return {
                        winner: argentinaTeam,
                        loser: opponentTeam,
                        score: `${winnerScore}-${loserScore}`
                    };
                }
            }

            const totalRanking = team1.ranking + team2.ranking;
            const probabilityTeam1 = team1.ranking / totalRanking;
            const winner = Math.random() < probabilityTeam1 ? team1 : team2;
            const loser = winner.id === team1.id ? team2 : team1;

            const baseScore = 4 + Math.floor(Math.random() * 2);
            const winnerScore = baseScore + 1;
            const loserScore = Math.max(3, winnerScore - 1);

            return {
                winner,
                loser,
                score: `${winnerScore}-${loserScore}`
            };
        }

        advanceTournament(roundIndex) {
            const tournamentData = stateModule.getTournamentData();
            const currentRound = tournamentData.rounds[roundIndex];
            if (!currentRound) {
                return;
            }

            const allPlayed = currentRound.matches.every((match) => match.played);

            if (!allPlayed) {
                const nextMatchIndex = currentRound.matches.findIndex((match) => !match.played);
                tournamentData.currentRound = roundIndex;
                tournamentData.currentMatch = nextMatchIndex === -1 ? 0 : nextMatchIndex;
                return;
            }

            if (roundIndex < constants.ROUND_NAMES.length - 1) {
                this.populateNextRound(roundIndex + 1);
                tournamentData.currentRound = roundIndex + 1;
                tournamentData.currentMatch = 0;
            }

            if (roundIndex === 3) {
                this.prepareThirdPlaceMatch();
            }

            if (roundIndex === constants.ROUND_NAMES.length - 1) {
                this.finalizeTournament();
            }
        }

        populateNextRound(nextRoundIndex) {
            const tournamentData = stateModule.getTournamentData();
            const previousRound = tournamentData.rounds[nextRoundIndex - 1];
            const winners = previousRound.matches.map((match) => match.winner);
            const nextRound = tournamentData.rounds[nextRoundIndex];
            nextRound.matches = [];

            for (let i = 0; i < winners.length; i += 2) {
                nextRound.matches.push(this.createMatchObject(winners[i], winners[i + 1], nextRoundIndex, i / 2));
            }
        }

        prepareThirdPlaceMatch() {
            const tournamentData = stateModule.getTournamentData();
            const semifinalRound = tournamentData.rounds[3];
            if (!semifinalRound || !semifinalRound.matches.every((match) => match.played)) {
                return;
            }

            const losers = semifinalRound.matches.map((match) => {
                return match.winner.id === match.team1.id ? match.team2 : match.team1;
            });

            tournamentData.thirdPlaceMatch = this.createMatchObject(losers[0], losers[1], 'third', 0);
            tournamentData.currentRound = 4;
            tournamentData.currentMatch = 0;
            this.syncService.syncTournamentState();
        }

        playThirdPlace() {
            const tournamentData = stateModule.getTournamentData();
            if (!tournamentData.thirdPlaceMatch || tournamentData.thirdPlaceMatch.played) {
                return;
            }

            this.simulateThirdPlaceMatch();
        }

        simulateThirdPlaceMatch(options = {}) {
            const tournamentData = stateModule.getTournamentData();
            if (!tournamentData.thirdPlaceMatch || tournamentData.thirdPlaceMatch.played) {
                return;
            }

            const result = this.resolveMatch(tournamentData.thirdPlaceMatch.team1, tournamentData.thirdPlaceMatch.team2, options.forceArgentinaWin);
            Object.assign(tournamentData.thirdPlaceMatch, {
                score1: result.score1,
                score2: result.score2,
                winner: result.winner,
                decidedByPenalties: result.decidedByPenalties,
                penaltyScore: result.penaltyScore,
                played: true
            });

            if (!options.auto) {
                showNotification(
                    `Partido por el tercer lugar: ${tournamentData.thirdPlaceMatch.team1.name} ${result.score1}-${result.score2} ${tournamentData.thirdPlaceMatch.team2.name}${
                        result.decidedByPenalties ? ' (penales)' : ''
                    }`,
                    'info'
                );
            }

            this.displayBracket();
            this.finalizeTournament();
            this.showTournamentInfo(this.determineTournamentStatus());
            this.syncService.syncTournamentState();
        }

        finalizeTournament() {
            const tournamentData = stateModule.getTournamentData();
            const finalRound = tournamentData.rounds[constants.ROUND_NAMES.length - 1];
            if (!finalRound || !finalRound.matches.length) {
                return;
            }

            const finalMatch = finalRound.matches[0];
            if (!finalMatch.played) {
                return;
            }

            if (tournamentData.thirdPlaceMatch && !tournamentData.thirdPlaceMatch.played) {
                return;
            }

            const champion = finalMatch.winner;
            const runnerUp = champion.id === finalMatch.team1.id ? finalMatch.team2 : finalMatch.team1;

            let thirdPlace = null;
            let fourthPlace = null;

            if (tournamentData.thirdPlaceMatch && tournamentData.thirdPlaceMatch.played) {
                thirdPlace = tournamentData.thirdPlaceMatch.winner;
                fourthPlace = thirdPlace.id === tournamentData.thirdPlaceMatch.team1.id
                    ? tournamentData.thirdPlaceMatch.team2
                    : tournamentData.thirdPlaceMatch.team1;
            } else {
                const semifinalRound = tournamentData.rounds[3];
                const semifinalLosers = semifinalRound.matches.map((match) =>
                    match.winner.id === match.team1.id ? match.team2 : match.team1
                );
                [thirdPlace, fourthPlace] = semifinalLosers;
            }

            tournamentData.rewards = {
                champion,
                runnerUp,
                thirdPlace,
                fourthPlace
            };
            tournamentData.status = 'completed';

            this.updateChampionDisplay();
            this.showTournamentInfo('Copa Finalizada');
            this.syncService.syncTournamentState();
            
            // Mostrar botón "Volver a Selección" cuando el torneo se finaliza
            if (this.backToSelectionBtn) {
                this.backToSelectionBtn.style.display = 'inline-block';
            }

            const favoriteTeam = tournamentData.favoriteTeamCode
                ? {
                    code: tournamentData.favoriteTeamCode,
                    name: tournamentData.favoriteTeamName || tournamentData.favoriteTeamCode
                }
                : null;

            // Sistema de achievements removido - ya no se requiere
        }

        // Tabla de recompensas removida - ya no se requiere

        showTournamentInfo(status) {
            const tournamentData = stateModule.getTournamentData();

            if (this.totalTeamsElement) {
                this.totalTeamsElement.textContent = tournamentData.teams.length;
            }
            if (this.totalRoundsElement) {
                this.totalRoundsElement.textContent = constants.ROUND_NAMES.length + 1;
            }
            if (this.totalMatchesElement) {
                this.totalMatchesElement.textContent = 32;
            }
            if (this.tournamentStatusElement) {
                this.tournamentStatusElement.textContent = status || 'En Marcha';
            }

            if (this.currentRoundLabel) {
                this.currentRoundLabel.textContent = this.getCurrentRoundLabel();
            }

            // Actualizar badge de fase en el header del bracket
            const currentPhaseBadge = this.doc.getElementById('currentPhaseBadge');
            const currentPhaseText = this.doc.getElementById('currentPhaseText');
            
            if (currentPhaseBadge && currentPhaseText && tournamentData.rounds && tournamentData.rounds.length > 0) {
                // Determinar la ronda actual (primera ronda con partidos no jugados)
                let currentRoundIndex = 0;
                for (let i = 0; i < tournamentData.rounds.length; i++) {
                    const round = tournamentData.rounds[i];
                    const matches = round.matches || [];
                    
                    if (matches.length === 0) {
                        continue;
                    }
                    
                    // Verificar si todos los partidos están jugados
                    const allPlayed = matches.every(match => match.played);
                    
                    if (!allPlayed) {
                        currentRoundIndex = i;
                        break;
                    }
                    
                    // Si todos están jugados, esta podría ser la última ronda
                    currentRoundIndex = i;
                }
                
                const phaseNumber = currentRoundIndex + 1;
                const roundName = tournamentData.rounds[currentRoundIndex]?.name || constants.ROUND_NAMES[currentRoundIndex] || `Ronda ${phaseNumber}`;
                
                currentPhaseText.textContent = `Fase ${phaseNumber} - ${roundName}`;
                currentPhaseBadge.style.display = 'inline-block';
            } else if (currentPhaseBadge) {
                currentPhaseBadge.style.display = 'none';
            }

            this.updateChampionDisplay();
            if (this.tournamentInfo) {
                this.tournamentInfo.style.display = 'block';
            }

            // Mostrar botón de finalizar si el torneo está completado
            const finalizeRow = this.doc.getElementById('finalizeTournamentRow');
            const finalizeUuidInput = this.doc.getElementById('finalizeTournamentUuid');
            const isCompleted = status === 'Copa Finalizada' || tournamentData.status === 'completed' || tournamentData.status === 'archived';
            
            if (finalizeRow && finalizeUuidInput) {
                if (isCompleted && tournamentData.uuid) {
                    finalizeRow.style.display = 'block';
                    finalizeUuidInput.value = tournamentData.uuid;
                } else {
                    finalizeRow.style.display = 'none';
                }
            }
            
            // Mostrar/ocultar botón "Volver a Selección" solo cuando el torneo esté completado
            if (this.backToSelectionBtn) {
                if (isCompleted) {
                    this.backToSelectionBtn.style.display = 'inline-block';
                } else {
                    this.backToSelectionBtn.style.display = 'none';
                }
            }
        }

        determineTournamentStatus() {
            const tournamentData = stateModule.getTournamentData();
            const finalRound = tournamentData.rounds[constants.ROUND_NAMES.length - 1];
            if (finalRound && finalRound.matches[0] && finalRound.matches[0].played) {
                if (!tournamentData.thirdPlaceMatch || tournamentData.thirdPlaceMatch.played) {
                    return 'Copa Finalizada';
                }
            }
            return 'En Marcha';
        }

        getCurrentRoundLabel() {
            const tournamentData = stateModule.getTournamentData();
            if (!tournamentData.rounds.length) {
                return 'Pendiente';
            }

            if (this.determineTournamentStatus() === 'Copa Finalizada') {
                return 'Campeón Definido';
            }

            if (tournamentData.thirdPlaceMatch && !tournamentData.thirdPlaceMatch.played && tournamentData.currentRound >= constants.ROUND_NAMES.length) {
                return 'Partido por el Tercer Lugar';
            }

            const index = Math.min(tournamentData.currentRound, constants.ROUND_NAMES.length - 1);
            return constants.ROUND_NAMES[index];
        }

        selectAllTeams() {
            this.doc.querySelectorAll('.team-checkbox-horizontal').forEach((checkbox) => {
                checkbox.checked = true;
                const teamInput = this.doc.getElementById('team' + checkbox.value);
                if (teamInput) {
                    teamInput.style.backgroundColor = '#E8F5E8';
                    teamInput.style.borderColor = 'var(--primary-green)';
                }
            });

            showNotification('¡Todas las selecciones han sido activadas!', 'success');
        }

        openCurrentTournament(shouldScroll = true) {
            this.switchToBracketView();

            if (!shouldScroll || !this.tournamentBracketPanel) {
                return;
            }

            requestAnimationFrame(() => {
                this.tournamentBracketPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        }

        switchToBracketView() {
            if (this.teamSelectionPanel) {
                this.teamSelectionPanel.style.display = 'none';
            }
            if (this.tournamentBracketPanel) {
                this.tournamentBracketPanel.style.display = 'block';
            }
            if (this.tournamentInfo) {
                this.tournamentInfo.style.display = 'block';
            }
        }

        backToSelection() {
            const tournamentData = stateModule.getTournamentData();
            
            // Si hay un torneo activo (con UUID), no permitir volver a selección
            // El bracket debe permanecer visible durante todo el torneo
            if (tournamentData.uuid && tournamentData.status !== 'completed' && tournamentData.status !== 'archived') {
                showNotification('No puedes volver a selección mientras hay un torneo en curso. Completa o finaliza el torneo primero.', 'warning');
                return;
            }
            
            // Solo permitir volver a selección si no hay torneo activo
            if (this.tournamentBracketPanel) {
                this.tournamentBracketPanel.style.display = 'none';
            }
            if (this.tournamentInfo) {
                this.tournamentInfo.style.display = 'none';
            }
            // Tabla de recompensas removida - ya no se requiere
            if (this.teamSelectionPanel) {
                this.teamSelectionPanel.style.display = 'block';
            }
            showNotification('Volviendo a la selección de equipos.', 'info');
        }

        generateRandomResults() {
            const tournamentData = stateModule.getTournamentData();
            if (!tournamentData.rounds.length || !tournamentData.rounds[0].matches.length) {
                showNotification('Primero debes generar un torneo.', 'warning');
                return;
            }

            this.generateBracket(tournamentData.teams, { shuffle: false });
            this.simulateAllMatches();
            showNotification('¡Toda la Copa fue simulada automáticamente!', 'success');
        }

        simulateCurrentRound() {
            const tournamentData = stateModule.getTournamentData();
            if (!tournamentData.rounds.length || !tournamentData.rounds[0].matches.length) {
                showNotification('Primero debes generar un torneo.', 'warning');
                return;
            }

            const roundIndex = Math.min(tournamentData.currentRound, constants.ROUND_NAMES.length - 1);
            const round = tournamentData.rounds[roundIndex];

            if (!round || !round.matches.length) {
                if (tournamentData.thirdPlaceMatch && !tournamentData.thirdPlaceMatch.played) {
                    this.simulateThirdPlaceMatch({ auto: true });
                    showNotification('¡Partido por el tercer lugar simulado automáticamente!', 'success');
                } else {
                    showNotification('No hay partidos pendientes en esta fase.', 'info');
                }
                return;
            }

            const pendingMatches = round.matches.filter((match) => !match.played);

            if (!pendingMatches.length) {
                if (roundIndex === constants.ROUND_NAMES.length - 1 && tournamentData.thirdPlaceMatch && !tournamentData.thirdPlaceMatch.played) {
                    this.simulateThirdPlaceMatch({ auto: true });
                    showNotification('¡Partido por el tercer lugar simulado automáticamente!', 'success');
                } else {
                    showNotification('Esta fase ya fue simulada.', 'info');
                }
                return;
            }

            round.matches.forEach((match, idx) => {
                if (!match.played) {
                    this.runMatch(roundIndex, idx, { auto: true });
                }
            });

            showNotification(`¡${constants.ROUND_NAMES[roundIndex]} de la Copa simulada automáticamente!`, 'success');

            if (roundIndex === constants.ROUND_NAMES.length - 1 && tournamentData.thirdPlaceMatch && !tournamentData.thirdPlaceMatch.played) {
                this.simulateThirdPlaceMatch({ auto: true });
            }
        }

        simulateCurrentRoundWithArgentinaWinner() {
            const tournamentData = stateModule.getTournamentData();
            if (!tournamentData.rounds.length || !tournamentData.rounds[0].matches.length) {
                showNotification('Primero debes generar un torneo.', 'warning');
                return;
            }

            const roundIndex = Math.min(tournamentData.currentRound, constants.ROUND_NAMES.length - 1);
            const round = tournamentData.rounds[roundIndex];

            if (!round || !round.matches.length) {
                if (tournamentData.thirdPlaceMatch && !tournamentData.thirdPlaceMatch.played) {
                    this.simulateThirdPlaceMatch({ auto: true, forceArgentinaWin: true });
                    showNotification('¡Partido por el tercer lugar simulado automáticamente!', 'success');
                } else {
                    showNotification('No hay partidos pendientes en esta fase.', 'info');
                }
                return;
            }

            const pendingMatches = round.matches.filter((match) => !match.played);

            if (!pendingMatches.length) {
                if (roundIndex === constants.ROUND_NAMES.length - 1 && tournamentData.thirdPlaceMatch && !tournamentData.thirdPlaceMatch.played) {
                    this.simulateThirdPlaceMatch({ auto: true, forceArgentinaWin: true });
                    showNotification('¡Partido por el tercer lugar simulado automáticamente!', 'success');
                } else {
                    showNotification('Esta fase ya fue simulada.', 'info');
                }
                return;
            }

            round.matches.forEach((match, idx) => {
                if (!match.played) {
                    this.runMatch(roundIndex, idx, { auto: true, forceArgentinaWin: true });
                }
            });

            showNotification(`¡${constants.ROUND_NAMES[roundIndex]} de la Copa simulada automáticamente! (Argentina gana si participa)`, 'success');

            if (roundIndex === constants.ROUND_NAMES.length - 1 && tournamentData.thirdPlaceMatch && !tournamentData.thirdPlaceMatch.played) {
                this.simulateThirdPlaceMatch({ auto: true, forceArgentinaWin: true });
            }
        }

        simulateAllMatches() {
            const tournamentData = stateModule.getTournamentData();
            tournamentData.currentRound = 0;
            tournamentData.currentMatch = 0;

            for (let roundIndex = 0; roundIndex < constants.ROUND_NAMES.length; roundIndex++) {
                const round = tournamentData.rounds[roundIndex];
                round.matches.forEach((match, matchIndex) => {
                    this.runMatch(roundIndex, matchIndex, { auto: true });
                });
            }

            if (tournamentData.thirdPlaceMatch && !tournamentData.thirdPlaceMatch.played) {
                this.simulateThirdPlaceMatch({ auto: true });
            }

            this.finalizeTournament();
            this.displayBracket();
            this.showTournamentInfo('Copa Finalizada');
            this.syncService.syncTournamentState();
        }

        clearAllTeams() {
            if (this.favoriteTeamSelect) {
                // País favorito removido - ya no se requiere
            }
            this.syncFavoriteOptions();
            this.updateChampionDisplay();

            if (this.bracketContainer) {
                this.bracketContainer.innerHTML = `
                    <div class="tournament-empty-state">
                        <i class="fas fa-futbol"></i>
                        <h5>Configura la Copa Mundial 2026</h5>
                        <p>Ingresa las 32 selecciones rumbo a dieciseisavos de final</p>
                    </div>
                `;
            }

            if (this.tournamentInfo) {
                this.tournamentInfo.style.display = 'none';
            }
            this.resetRewardsPanel();

            stateModule.resetTournamentData();

            showNotification('Las selecciones permanecen fijas; se restableció la vista del simulador.', 'info');
        }

        resetRewardsPanel() {
            // Tabla de recompensas removida - ya no se requiere
        }
    }

    namespace.TournamentController = TournamentController;
})(window);

