document.addEventListener('DOMContentLoaded', function () {
    const ROUND_NAMES = [
        'Dieciseisavos de Final',
        'Octavos de Final',
        'Cuartos de Final',
        'Semifinales',
        'Final'
    ];
    const GOAL_DIVISOR = 30;
    const MAX_GOALS = 6;
    const ACHIEVEMENT_STORAGE_KEY = 'worldCupAchievementState';
    const MAX_HISTORY_ITEMS = 6;
    const ACHIEVEMENT_TIERS = [
        { name: 'Novato', threshold: 0, icon: 'fa-seedling', description: 'Has iniciado tu camino mundialista.' },
        { name: 'Contendiente', threshold: 150, icon: 'fa-medal', description: 'Tu selección ya siente el protagonismo.' },
        { name: 'Leyenda Regional', threshold: 350, icon: 'fa-flag', description: 'Dominas tu confederación con autoridad.' },
        { name: 'Figura Internacional', threshold: 600, icon: 'fa-trophy', description: 'Eres referente en las grandes noches.' },
        { name: 'Gloria Eterna', threshold: 900, icon: 'fa-crown', description: 'Te codeas con los campeones del mundo.' }
    ];
    const FAVORITE_REWARD_POINTS = {
        champion: 150,
        runnerUp: 100,
        thirdPlace: 70,
        fourthPlace: 40
    };

    let tournamentData = {
        teams: [],
        rounds: [],
        currentRound: 0,
        currentMatch: 0,
        favoriteTeamCode: null,
        favoriteTeamName: null,
        thirdPlaceMatch: null,
        rewards: createEmptyRewards(),
        uuid: null,
        status: 'idle'
    };
    let achievementState = loadAchievementState();

    const generateBtn = document.getElementById('generateTournament');
    const tournamentLockMessage = document.getElementById('tournamentLockMessage');
    const activeTournamentElement = document.getElementById('activeTournamentData');
    const openSimulatorBtn = document.getElementById('openTournamentSimulator');
    let hasHydratedInitialTournament = false;
    const clearBtn = document.getElementById('clearTeams');
    const bracketContainer = document.getElementById('tournamentBracket');
    const tournamentInfo = document.getElementById('tournamentInfo');
    const favoriteTeamSelect = document.getElementById('favoriteTeam');
    const favoriteTeamFeedback = document.getElementById('favoriteTeamFeedback');
    const rewardPanel = document.getElementById('rewardPanel');
    const rewardList = document.getElementById('rewardList');
    const rewardMessage = document.getElementById('rewardMessage');
    const selectAllBtn = document.getElementById('selectAllTeams');
    const teamSelectionPanel = document.getElementById('teamSelectionPanel');
    const tournamentBracketPanel = document.getElementById('tournamentBracketPanel');
    const backToSelectionBtn = document.getElementById('backToSelection');
    const generateRandomResultsBtn = document.getElementById('generateRandomResults');
    const simulateCurrentRoundBtn = document.getElementById('simulateCurrentRound');
    const favoriteTeamLabel = document.getElementById('favoriteTeamLabel');
    const currentRoundLabel = document.getElementById('currentRoundName');
    const shareAchievementsBtn = document.getElementById('shareAchievements');
    const resetAchievementsBtn = document.getElementById('resetAchievements');
    const teamsDataElement = document.getElementById('worldCupTeamsData');
    const worldCupTeamsDataset = teamsDataElement ? JSON.parse(teamsDataElement.dataset.teams || '[]') : [];
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null;

    if (!generateBtn) {
        return;
    }

    generateBtn.addEventListener('click', generateTournament);
    if (clearBtn) {
        clearBtn.addEventListener('click', clearAllTeams);
    }
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', selectAllTeams);
    }
    backToSelectionBtn.addEventListener('click', backToSelection);
    generateRandomResultsBtn.addEventListener('click', generateRandomResults);
    if (simulateCurrentRoundBtn) {
        simulateCurrentRoundBtn.addEventListener('click', simulateCurrentRound);
    }
    if (openSimulatorBtn) {
        openSimulatorBtn.addEventListener('click', () => openCurrentTournament(true));
    }
    if (shareAchievementsBtn) {
        shareAchievementsBtn.addEventListener('click', shareAchievementProgress);
    }
    if (resetAchievementsBtn) {
        resetAchievementsBtn.addEventListener('click', resetAchievementProgress);
    }

    document.querySelectorAll('.team-checkbox-horizontal').forEach((checkbox) => {
        checkbox.addEventListener('change', function () {
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

    document.querySelectorAll('.team-input-horizontal').forEach((input) => {
        input.addEventListener('input', syncFavoriteOptions);
    });

    applyInitialStyles();
    syncFavoriteOptions();
    updateFavoriteTeamDisplay();
    renderAchievementPanel();
    hydrateInitialTournament();

    function createEmptyRewards() {
        return {
            champion: null,
            runnerUp: null,
            thirdPlace: null,
            fourthPlace: null
        };
    }

    function serializeTeamForStorage(team) {
        if (!team) {
            return null;
        }

        return {
            id: team.id ?? null,
            name: team.name ?? null,
            code: team.code ?? null,
            ranking: team.ranking ?? null,
            continent: team.continent ?? null,
            flag_url: team.flag_url ?? null,
        };
    }

    function serializeRoundsForStorage() {
        if (!Array.isArray(tournamentData.rounds)) {
            return [];
        }

        return tournamentData.rounds.map((round, roundIndex) => ({
            name: round.name ?? null,
            matches: (round.matches || []).map((match, matchIndex) => {
                const matchKey = match.match_key || match.id || `round-${roundIndex}-match-${matchIndex}`;

                return {
                    id: match.id ?? matchKey,
                    match_key: matchKey,
                    team1: serializeTeamForStorage(match.team1),
                    team2: serializeTeamForStorage(match.team2),
                    score1: match.score1 ?? null,
                    score2: match.score2 ?? null,
                    winner: serializeTeamForStorage(match.winner),
                    played: match.played ?? false,
                    stage: round.name ?? null,
                };
            })
        }));
    }

    function serializeThirdPlaceMatch() {
        if (!tournamentData.thirdPlaceMatch) {
            return null;
        }

        const match = tournamentData.thirdPlaceMatch;
        return {
            team1: serializeTeamForStorage(match.team1),
            team2: serializeTeamForStorage(match.team2),
            score1: match.score1 ?? null,
            score2: match.score2 ?? null,
            winner: serializeTeamForStorage(match.winner),
            played: match.played ?? false,
        };
    }

    async function syncTournamentState({ initial = false } = {}) {
        if (!csrfToken) {
            return;
        }

        const sanitizedRewards = tournamentData.rewards ? {
            champion: serializeTeamForStorage(tournamentData.rewards.champion),
            runnerUp: serializeTeamForStorage(tournamentData.rewards.runnerUp),
            thirdPlace: serializeTeamForStorage(tournamentData.rewards.thirdPlace),
            fourthPlace: serializeTeamForStorage(tournamentData.rewards.fourthPlace),
        } : null;
        const serializedThirdPlace = serializeThirdPlaceMatch();

        const resultsPayload = {};
        if (sanitizedRewards && Object.values(sanitizedRewards).some((value) => value)) {
            resultsPayload.rewards = sanitizedRewards;
        }
        if (serializedThirdPlace) {
            resultsPayload.third_place = serializedThirdPlace;
        }

        const payload = {
            favorite_team: tournamentData.favoriteTeamCode,
            rounds: serializeRoundsForStorage(),
        };

        if (Object.keys(resultsPayload).length) {
            payload.results = resultsPayload;
        }

        if (initial) {
            const baseTeams = Array.isArray(tournamentData.teams) && tournamentData.teams.length
                ? tournamentData.teams
                : worldCupTeamsDataset;
            payload.teams = baseTeams.map((team) => serializeTeamForStorage(team));
        }

        const url = initial
            ? '/torneo/store'
            : (tournamentData.uuid ? `/torneo/${tournamentData.uuid}` : null);

        if (!url) {
            return;
        }

        try {
            const response = await fetch(url, {
                method: initial ? 'POST' : 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(payload),
            });

            const responseText = await response.text();
            let data = null;

            if (responseText) {
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.warn('No se pudo interpretar la respuesta del torneo', parseError);
                }
            }

            if (!response.ok) {
                if (initial && response.status === 409) {
                    applyServerStatusFromResponse(data, { initial: true, conflict: true });
                    if (!hasHydratedInitialTournament) {
                        hydrateInitialTournament();
                    }
                    return;
                }

                if (initial) {
                    unlockGenerateButton('No se pudo crear el torneo. Intenta nuevamente.');
                }

                throw new Error(`Sync error (${response.status})`);
            }

            applyServerStatusFromResponse(data, { initial });

            return data;
        } catch (error) {
            if (initial) {
                unlockGenerateButton('No se pudo crear el torneo. Intenta nuevamente.');
            }
            console.warn('No se pudo sincronizar el torneo:', error);
        }
    }

    function applyInitialStyles() {
        document.querySelectorAll('.team-checkbox-horizontal:checked').forEach((checkbox) => {
            const teamInput = document.getElementById('team' + checkbox.value);
            if (teamInput) {
                teamInput.style.backgroundColor = '#E8F5E8';
                teamInput.style.borderColor = 'var(--primary-green)';
            }
        });
    }

    function syncFavoriteOptions() {
        if (!favoriteTeamSelect) {
            return;
        }

        const currentValue = favoriteTeamSelect.value;
        favoriteTeamSelect.innerHTML = `
            <option value="">-- Elige una selección --</option>
            ${worldCupTeamsDataset
                .map((team) => `<option value="${team.code}">${team.name} (${team.code})</option>`)
                .join('')}
        `;

        if (currentValue && worldCupTeamsDataset.some((team) => team.code === currentValue)) {
            favoriteTeamSelect.value = currentValue;
        }
    }

    function updateFavoriteTeamDisplay() {
        if (favoriteTeamLabel) {
            if (tournamentData.favoriteTeamCode && tournamentData.favoriteTeamName) {
                favoriteTeamLabel.textContent = `${tournamentData.favoriteTeamName} (${tournamentData.favoriteTeamCode})`;
            } else if (tournamentData.favoriteTeamCode) {
                favoriteTeamLabel.textContent = tournamentData.favoriteTeamCode;
            } else {
                favoriteTeamLabel.textContent = 'Sin definir';
            }
        }

        const selectedTeamDisplay = document.getElementById('selectedTeamDisplay');
        if (selectedTeamDisplay) {
            const pill = selectedTeamDisplay.querySelector('.selected-team-pill');
            if (pill) {
                if (tournamentData.favoriteTeamCode && tournamentData.favoriteTeamName) {
                    pill.textContent = `${tournamentData.favoriteTeamName} (${tournamentData.favoriteTeamCode})`;
                    pill.classList.add('bg-success', 'text-white');
                } else if (tournamentData.favoriteTeamCode) {
                    pill.textContent = tournamentData.favoriteTeamCode;
                    pill.classList.add('bg-success', 'text-white');
                } else {
                    pill.textContent = 'Selecciona un favorito';
                    pill.classList.remove('bg-success', 'text-white');
                }
            }
        }
    }

    function updateGenerateButtonState({ disabled, message, tone = 'warning' } = {}) {
        if (!generateBtn) {
            return;
        }

        let effectiveDisabled = generateBtn.disabled;

        if (typeof disabled === 'boolean') {
            generateBtn.disabled = disabled;
            generateBtn.setAttribute('aria-disabled', disabled ? 'true' : 'false');
            effectiveDisabled = disabled;
        }

        if (tournamentLockMessage) {
            if (message) {
                tournamentLockMessage.textContent = message;
                tournamentLockMessage.classList.remove('d-none');

                const toneClass = tone === 'error'
                    ? 'text-danger'
                    : tone === 'success'
                        ? 'text-success'
                        : 'text-tournament-orange';

                tournamentLockMessage.classList.remove('text-danger', 'text-success', 'text-tournament-orange');
                tournamentLockMessage.classList.add(toneClass);
            } else {
                tournamentLockMessage.classList.add('d-none');
            }
        }

        if (openSimulatorBtn) {
            if (effectiveDisabled) {
                openSimulatorBtn.classList.remove('d-none');
            } else {
                openSimulatorBtn.classList.add('d-none');
            }
        }
    }

    function lockGenerateButton(message) {
        updateGenerateButtonState({
            disabled: true,
            message: message || 'Ya hay un torneo en curso. Finalízalo antes de generar uno nuevo.',
            tone: 'warning',
        });
    }

    function unlockGenerateButton(message) {
        updateGenerateButtonState({
            disabled: false,
            message: message || '',
            tone: 'success',
        });
    }

    function applyServerStatusFromResponse(data, { initial = false, conflict = false } = {}) {
        if (!data || typeof data !== 'object') {
            return;
        }

        if ((initial || !tournamentData.uuid) && data.uuid) {
            tournamentData.uuid = data.uuid;
        }

        if (data.status) {
            tournamentData.status = data.status;

            if (data.status === 'in_progress') {
                const message = data.message || 'Tienes un Mundial en curso. Completa los partidos pendientes antes de generar otro.';
                lockGenerateButton(message);
            } else if (data.status === 'completed') {
                const message = data.message || 'La última Copa finalizó. Puedes generar una nueva simulación cuando gustes.';
                unlockGenerateButton(message);
            }
        }

        if (conflict && data.message) {
            showNotification(data.message, 'warning');
        }
    }

    function hydrateInitialTournament() {
        if (hasHydratedInitialTournament) {
            return;
        }

        hasHydratedInitialTournament = true;

        if (!activeTournamentElement) {
            unlockGenerateButton();
            return;
        }

        const raw = activeTournamentElement.dataset.tournament;

        if (!raw || raw === 'null' || raw === 'undefined') {
            unlockGenerateButton();
            return;
        }

        let payload = null;

        try {
            payload = JSON.parse(raw);
        } catch (error) {
            console.warn('No se pudo interpretar el torneo activo', error);
            unlockGenerateButton();
            return;
        }

        if (!payload || typeof payload !== 'object') {
            unlockGenerateButton();
            return;
        }

        tournamentData.uuid = payload.uuid || null;
        tournamentData.favoriteTeamCode = payload.favorite_team || null;
        tournamentData.status = payload.status || 'in_progress';

        tournamentData.teams = Array.isArray(payload.teams) && payload.teams.length
            ? payload.teams.map((team, index) => ({
                ...team,
                id: team.id ?? index + 1,
            }))
            : worldCupTeamsDataset.map((team) => ({ ...team }));

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
                            played: Boolean(match.played),
                        };
                    })
                    : [];

                return {
                    name: round.name || ROUND_NAMES[roundIndex] || `Ronda ${roundIndex + 1}`,
                    matches,
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
                played: Boolean(storedThirdPlace.played),
            }
            : null;

        const storedRewards = payload.results?.rewards ?? null;
        tournamentData.rewards = storedRewards
            ? {
                champion: storedRewards.champion || null,
                runnerUp: storedRewards.runnerUp || storedRewards.runner_up || null,
                thirdPlace: storedRewards.thirdPlace || storedRewards.third_place || null,
                fourthPlace: storedRewards.fourthPlace || storedRewards.fourth_place || null,
            }
            : createEmptyRewards();

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

        if (!foundPending && tournamentData.rounds.length) {
            currentRoundIndex = tournamentData.rounds.length - 1;
            currentMatchIndex = 0;
        }

        if (tournamentData.thirdPlaceMatch && !tournamentData.thirdPlaceMatch.played) {
            currentRoundIndex = ROUND_NAMES.length;
            currentMatchIndex = 0;
        }

        tournamentData.currentRound = currentRoundIndex;
        tournamentData.currentMatch = currentMatchIndex;

        if (favoriteTeamSelect && tournamentData.favoriteTeamCode) {
            favoriteTeamSelect.value = tournamentData.favoriteTeamCode;
        }

        updateFavoriteTeamDisplay();

        if (tournamentData.rounds.length) {
            displayBracket();
            showTournamentInfo(determineTournamentStatus());
        }

        if (tournamentData.rewards && tournamentData.rewards.champion) {
            rewardPanel.style.display = 'block';
            updateRewardsUI();
        } else {
            resetRewardsPanel();
        }

        openCurrentTournament(false);

        if (tournamentData.status === 'completed') {
            unlockGenerateButton('La última Copa finalizó. Puedes generar una nueva simulación.');
        } else {
            lockGenerateButton('Tienes un Mundial en curso. Completa los partidos pendientes antes de generar otro.');
        }
    }

    function generateTournament() {
        if (tournamentData.status === 'in_progress') {
            lockGenerateButton('Ya tienes un Mundial en curso. Finaliza la simulación antes de iniciar otro.');
            showNotification('Ya existe un torneo activo. Continúa los partidos pendientes o finalízalo.', 'warning');
            switchToBracketView();
            return;
        }

        if (!worldCupTeamsDataset.length) {
            showNotification('No hay selecciones registradas para generar el torneo.', 'danger');
            return;
        }

        if (worldCupTeamsDataset.length !== 32) {
            showNotification('Se requieren las 32 selecciones mundialistas para iniciar la simulación.', 'warning');
            return;
        }

        const favoriteCode = favoriteTeamSelect.value;
        if (!favoriteCode) {
            showNotification('Selecciona tu equipo favorito para activar el sistema de recompensas.', 'warning');
            favoriteTeamSelect.focus();
            favoriteTeamSelect.classList.add('is-invalid');
            if (favoriteTeamFeedback) {
                favoriteTeamFeedback.classList.remove('d-none');
            }

            const clearInvalidState = () => {
                favoriteTeamSelect.classList.remove('is-invalid');
                if (favoriteTeamFeedback) {
                    favoriteTeamFeedback.classList.add('d-none');
                }
                favoriteTeamSelect.removeEventListener('change', clearInvalidState);
            };

            favoriteTeamSelect.addEventListener('change', clearInvalidState);
            return;
        }

        const teams = worldCupTeamsDataset.map(team => ({ ...team }));
        const favoriteTeam = teams.find((team) => team.code === favoriteCode) || null;

        tournamentData = {
            teams: [],
            rounds: [],
            currentRound: 0,
            currentMatch: 0,
            favoriteTeamCode: null,
            favoriteTeamName: null,
            thirdPlaceMatch: null,
            rewards: createEmptyRewards(),
            uuid: null,
            status: 'in_progress'
        };

        generateBracket(teams, { shuffle: true });
        tournamentData.favoriteTeamCode = favoriteCode;
        tournamentData.favoriteTeamName = favoriteTeam ? favoriteTeam.name : null;
        tournamentData.status = 'in_progress';
        favoriteTeamSelect.classList.remove('is-invalid');
        if (favoriteTeamFeedback) {
            favoriteTeamFeedback.classList.add('d-none');
        }
        lockGenerateButton('Torneo en curso. Completa todos los partidos para generar uno nuevo.');
        showTournamentInfo('En Marcha');
        updateFavoriteTeamDisplay();
        resetRewardsPanel();
        switchToBracketView();
        syncTournamentState({ initial: true });

        showNotification('¡La Copa Mundial 2026 está configurada! Comenzamos en dieciseisavos de final.', 'success');
    }

    function generateBracket(teams, options = {}) {
        const { shuffle = false } = options;
        const workingTeams = [...teams];

        if (shuffle) {
            shuffleArray(workingTeams);
        }

        tournamentData.teams = workingTeams;
        tournamentData.rounds = ROUND_NAMES.map((name) => ({ name, matches: [] }));
        tournamentData.currentRound = 0;
        tournamentData.currentMatch = 0;
        tournamentData.thirdPlaceMatch = null;
        tournamentData.rewards = createEmptyRewards();

        for (let i = 0; i < workingTeams.length; i += 2) {
            tournamentData.rounds[0].matches.push(
                createMatchObject(workingTeams[i], workingTeams[i + 1], 0, i / 2)
            );
        }

        displayBracket();
    }

    function createMatchObject(team1, team2, roundIndex, matchIndex) {
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

    function shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
    }

    function displayBracket() {
        let html = '<div class="tennis-bracket-container">';

        tournamentData.rounds.forEach((round, roundIndex) => {
            const roundName = ROUND_NAMES[roundIndex];
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
                            ${formatTeamName(match.team1)}
                        </div>
                        <div class="match-score-tennis ${match.played ? 'has-score' : 'vs-text'}">
                            ${formatMatchScore(match)}
                        </div>
                        <div class="team-name-tennis ${match.winner && match.team2 && match.winner.id === match.team2.id ? 'winner' : ''}">
                            ${formatTeamName(match.team2)}
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
                                ${formatTeamName(match.team1)}
                            </div>
                            <div class="match-score-tennis ${match.played ? 'has-score' : 'vs-text'}">
                                ${formatMatchScore(match)}
                            </div>
                            <div class="team-name-tennis ${match.winner && match.team2 && match.winner.id === match.team2.id ? 'winner' : ''}">
                                ${formatTeamName(match.team2)}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        bracketContainer.innerHTML = html;
    }

    function formatTeamName(team) {
        return team ? `${team.name} (${team.code})` : 'Por definir';
    }

    function formatMatchScore(match) {
        if (!match.played) {
            return 'VS';
        }

        let baseScore = `${match.score1} - ${match.score2}`;
        if (match.decidedByPenalties && match.penaltyScore) {
            baseScore += ` (pen ${match.penaltyScore})`;
        }

        return baseScore;
    }

    function playMatch(roundIndex, matchIndex) {
        const round = tournamentData.rounds[roundIndex];
        if (!round) {
            return;
        }

        const match = round.matches[matchIndex];
        if (!match || match.played || !match.team1 || !match.team2) {
            return;
        }

        runMatch(roundIndex, matchIndex);
    }

    function runMatch(roundIndex, matchIndex, options = {}) {
        const match = tournamentData.rounds[roundIndex].matches[matchIndex];
        if (!match || match.played) {
            return;
        }

        const result = resolveMatch(match.team1, match.team2);
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

        advanceTournament(roundIndex);
        displayBracket();
        showTournamentInfo(determineTournamentStatus());
        syncTournamentState();
    }

    function resolveMatch(team1, team2) {
        const random1 = Math.random() * (team1.ranking + 1);
        const random2 = Math.random() * (team2.ranking + 1);

        let goals1 = Math.min(MAX_GOALS, Math.floor(random1 / GOAL_DIVISOR));
        let goals2 = Math.min(MAX_GOALS, Math.floor(random2 / GOAL_DIVISOR));

        if (goals1 !== goals2) {
            return {
                score1: goals1,
                score2: goals2,
                winner: goals1 > goals2 ? team1 : team2,
                decidedByPenalties: false,
                penaltyScore: null
            };
        }

        const penaltyResult = resolvePenalties(team1, team2);
        return {
            score1: goals1,
            score2: goals2,
            winner: penaltyResult.winner,
            decidedByPenalties: true,
            penaltyScore: penaltyResult.score
        };
    }

    function resolvePenalties(team1, team2) {
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

    function advanceTournament(roundIndex) {
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

        if (roundIndex < ROUND_NAMES.length - 1) {
            populateNextRound(roundIndex + 1);
            tournamentData.currentRound = roundIndex + 1;
            tournamentData.currentMatch = 0;
        }

        if (roundIndex === 3) {
            prepareThirdPlaceMatch();
        }

        if (roundIndex === ROUND_NAMES.length - 1) {
            finalizeTournament();
        }
    }

    function populateNextRound(nextRoundIndex) {
        const previousRound = tournamentData.rounds[nextRoundIndex - 1];
        const winners = previousRound.matches.map((match) => match.winner);
        const nextRound = tournamentData.rounds[nextRoundIndex];
        nextRound.matches = [];

        for (let i = 0; i < winners.length; i += 2) {
            nextRound.matches.push(createMatchObject(winners[i], winners[i + 1], nextRoundIndex, i / 2));
        }
    }

    function prepareThirdPlaceMatch() {
        const semifinalRound = tournamentData.rounds[3];
        if (!semifinalRound || !semifinalRound.matches.every((match) => match.played)) {
            return;
        }

        const losers = semifinalRound.matches.map((match) => {
            return match.winner.id === match.team1.id ? match.team2 : match.team1;
        });

        tournamentData.thirdPlaceMatch = createMatchObject(losers[0], losers[1], 'third', 0);
        tournamentData.currentRound = 4;
        tournamentData.currentMatch = 0;
        syncTournamentState();
    }

    function playThirdPlace() {
        if (!tournamentData.thirdPlaceMatch || tournamentData.thirdPlaceMatch.played) {
            return;
        }

        simulateThirdPlaceMatch();
    }

    function simulateThirdPlaceMatch(options = {}) {
        if (!tournamentData.thirdPlaceMatch || tournamentData.thirdPlaceMatch.played) {
            return;
        }

        const result = resolveMatch(tournamentData.thirdPlaceMatch.team1, tournamentData.thirdPlaceMatch.team2);
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

        displayBracket();
        finalizeTournament();
        showTournamentInfo(determineTournamentStatus());
        syncTournamentState();
    }

    function finalizeTournament() {
        const finalRound = tournamentData.rounds[ROUND_NAMES.length - 1];
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

        updateRewardsUI();
        showTournamentInfo('Copa Finalizada');
        syncTournamentState();
    }

    function updateRewardsUI() {
        const rewards = tournamentData.rewards;

        if (!rewards.champion) {
            rewardPanel.style.display = 'none';
            return;
        }

        const favoriteCode = tournamentData.favoriteTeamCode;
        rewardPanel.style.display = 'block';

        const rewardItems = [
            { label: 'Medalla de Oro', position: 'Campeón', team: rewards.champion },
            { label: 'Medalla de Plata', position: 'Subcampeón', team: rewards.runnerUp },
            { label: 'Medalla de Bronce', position: 'Tercer Lugar', team: rewards.thirdPlace },
            { label: 'Medalla de Cobre', position: 'Cuarto Lugar', team: rewards.fourthPlace }
        ];

        rewardList.innerHTML = rewardItems
            .map((item) => {
                const isFavorite = item.team && item.team.code === favoriteCode;
                return `
                <li class="list-group-item reward-list-item d-flex justify-content-between align-items-center">
                    <span>${item.position}: ${item.team ? `${item.team.name} (${item.team.code})` : 'Por definir'}</span>
                    <span class="badge bg-warning text-dark">${item.label}${isFavorite ? ' ⭐' : ''}</span>
                </li>
            `;
            })
            .join('');

        const favoriteReward = rewardItems.find((item) => item.team && item.team.code === favoriteCode);
        if (favoriteReward) {
            rewardMessage.textContent = `¡Tu selección obtuvo ${favoriteReward.position}! Recompensa desbloqueada: ${favoriteReward.label}.`;
        } else {
            rewardMessage.textContent = 'Tu selección favorita no alcanzó el podio esta vez. ¡Inténtalo de nuevo!';
        }
    }

    function showTournamentInfo(status) {
        document.getElementById('totalTeams').textContent = tournamentData.teams.length;
        document.getElementById('totalRounds').textContent = ROUND_NAMES.length + 1;
        document.getElementById('totalMatches').textContent = 32;
        document.getElementById('tournamentStatus').textContent = status || 'En Marcha';

        if (currentRoundLabel) {
            currentRoundLabel.textContent = getCurrentRoundLabel();
        }

        updateFavoriteTeamDisplay();
        tournamentInfo.style.display = 'block';
    }

    function determineTournamentStatus() {
        const finalRound = tournamentData.rounds[ROUND_NAMES.length - 1];
        if (finalRound && finalRound.matches[0] && finalRound.matches[0].played) {
            if (!tournamentData.thirdPlaceMatch || tournamentData.thirdPlaceMatch.played) {
                return 'Copa Finalizada';
            }
        }
        return 'En Marcha';
    }

    function getCurrentRoundLabel() {
        if (!tournamentData.rounds.length) {
            return 'Pendiente';
        }

        if (determineTournamentStatus() === 'Copa Finalizada') {
            return 'Campeón Definido';
        }

        if (tournamentData.thirdPlaceMatch && !tournamentData.thirdPlaceMatch.played && tournamentData.currentRound >= ROUND_NAMES.length) {
            return 'Partido por el Tercer Lugar';
        }

        const index = Math.min(tournamentData.currentRound, ROUND_NAMES.length - 1);
        return ROUND_NAMES[index];
    }

    function selectAllTeams() {
        document.querySelectorAll('.team-checkbox-horizontal').forEach((checkbox) => {
            checkbox.checked = true;
            const teamInput = document.getElementById('team' + checkbox.value);
            teamInput.style.backgroundColor = '#E8F5E8';
            teamInput.style.borderColor = 'var(--primary-green)';
        });

        showNotification('¡Todas las selecciones han sido activadas!', 'success');
    }

    function openCurrentTournament(shouldScroll = true) {
        switchToBracketView();

        if (!shouldScroll || !tournamentBracketPanel) {
            return;
        }

        requestAnimationFrame(() => {
            tournamentBracketPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    function switchToBracketView() {
        teamSelectionPanel.style.display = 'none';
        tournamentBracketPanel.style.display = 'block';
        tournamentInfo.style.display = 'block';
    }

    function backToSelection() {
        tournamentBracketPanel.style.display = 'none';
        tournamentInfo.style.display = 'none';
        rewardPanel.style.display = 'none';
        teamSelectionPanel.style.display = 'block';
        showNotification('Volviendo a la selección de equipos.', 'info');
    }

    function generateRandomResults() {
        if (!tournamentData.rounds.length || !tournamentData.rounds[0].matches.length) {
            showNotification('Primero debes generar un torneo.', 'warning');
            return;
        }

        generateBracket(tournamentData.teams, { shuffle: false });
        simulateAllMatches();
        showNotification('¡Toda la Copa fue simulada automáticamente!', 'success');
    }

    function simulateCurrentRound() {
        if (!tournamentData.rounds.length || !tournamentData.rounds[0].matches.length) {
            showNotification('Primero debes generar un torneo.', 'warning');
            return;
        }

        const roundIndex = Math.min(tournamentData.currentRound, ROUND_NAMES.length - 1);
        const round = tournamentData.rounds[roundIndex];

        if (!round || !round.matches.length) {
            if (tournamentData.thirdPlaceMatch && !tournamentData.thirdPlaceMatch.played) {
                simulateThirdPlaceMatch({ auto: true });
                showNotification('¡Partido por el tercer lugar simulado automáticamente!', 'success');
            } else {
                showNotification('No hay partidos pendientes en esta fase.', 'info');
            }
            return;
        }

        const pendingMatches = round.matches.filter((match) => !match.played);

        if (!pendingMatches.length) {
            if (roundIndex === ROUND_NAMES.length - 1 && tournamentData.thirdPlaceMatch && !tournamentData.thirdPlaceMatch.played) {
                simulateThirdPlaceMatch({ auto: true });
                showNotification('¡Partido por el tercer lugar simulado automáticamente!', 'success');
            } else {
                showNotification('Esta fase ya fue simulada.', 'info');
            }
            return;
        }

        round.matches.forEach((match, idx) => {
            if (!match.played) {
                runMatch(roundIndex, idx, { auto: true });
            }
        });

        showNotification(`¡${ROUND_NAMES[roundIndex]} de la Copa simulada automáticamente!`, 'success');

        if (roundIndex === ROUND_NAMES.length - 1 && tournamentData.thirdPlaceMatch && !tournamentData.thirdPlaceMatch.played) {
            simulateThirdPlaceMatch({ auto: true });
        }
    }

    function simulateAllMatches() {
        tournamentData.currentRound = 0;
        tournamentData.currentMatch = 0;

        for (let roundIndex = 0; roundIndex < ROUND_NAMES.length; roundIndex++) {
            const round = tournamentData.rounds[roundIndex];
            round.matches.forEach((match, matchIndex) => {
                runMatch(roundIndex, matchIndex, { auto: true });
            });
        }

        if (tournamentData.thirdPlaceMatch && !tournamentData.thirdPlaceMatch.played) {
            simulateThirdPlaceMatch({ auto: true });
        }

        finalizeTournament();
        displayBracket();
        showTournamentInfo('Copa Finalizada');
        syncTournamentState();
    }

    function clearAllTeams() {
        if (favoriteTeamSelect) {
            favoriteTeamSelect.value = '';
        }
        syncFavoriteOptions();
        updateFavoriteTeamDisplay();

        bracketContainer.innerHTML = `
            <div class="tournament-empty-state">
                <i class="fas fa-futbol"></i>
                <h5>Configura la Copa Mundial 2026</h5>
                <p>Ingresa las 32 selecciones rumbo a dieciseisavos de final</p>
            </div>
        `;

        tournamentInfo.style.display = 'none';
        resetRewardsPanel();

        tournamentData = {
            teams: [],
            rounds: [],
            currentRound: 0,
            currentMatch: 0,
            favoriteTeamCode: null,
            favoriteTeamName: null,
            thirdPlaceMatch: null,
            rewards: createEmptyRewards(),
            uuid: null,
            status: 'idle'
        };

        showNotification('Las selecciones permanecen fijas; se restableció la vista del simulador.', 'info');
    }

    function resetRewardsPanel() {
        rewardPanel.style.display = 'none';
        rewardList.innerHTML = '';
        rewardMessage.textContent = 'Aún no se han definido las recompensas. Completa el torneo para conocer tus medallas.';
    }

    function loadAchievementState() {
        try {
            const raw = window.localStorage.getItem(ACHIEVEMENT_STORAGE_KEY);
            if (!raw) {
                return getDefaultAchievementState();
            }
            const parsed = JSON.parse(raw);
            return {
                ...getDefaultAchievementState(),
                ...parsed,
                history: Array.isArray(parsed.history) ? parsed.history : []
            };
        } catch (error) {
            console.warn('No se pudo cargar el progreso de logros', error);
            return getDefaultAchievementState();
        }
    }

    function saveAchievementState(state) {
        try {
            window.localStorage.setItem(ACHIEVEMENT_STORAGE_KEY, JSON.stringify(state));
        } catch (error) {
            console.warn('No se pudo guardar el progreso de logros', error);
        }
    }

    function getDefaultAchievementState() {
        return {
            points: 0,
            favoriteTitles: 0,
            favoritePodiums: 0,
            badgesUnlocked: ['Novato'],
            history: []
        };
    }

    function getCurrentTier(points) {
        let tier = ACHIEVEMENT_TIERS[0];
        ACHIEVEMENT_TIERS.forEach((candidate) => {
            if (points >= candidate.threshold) {
                tier = candidate;
            }
        });
        return tier;
    }

    function renderAchievementPanel() {
        const pointsElement = document.getElementById('userMedalPoints');
        const tierLabel = document.getElementById('achievementTierLabel');
        const progressBar = document.getElementById('medalProgressBar');
        const nextBadgeMessage = document.getElementById('nextBadgeMessage');
        const badgeGrid = document.getElementById('badgeGrid');
        const timeline = document.getElementById('achievementTimeline');

        if (!pointsElement || !tierLabel || !progressBar || !badgeGrid || !timeline) {
            return;
        }

        const points = achievementState.points;
        const currentTier = getCurrentTier(points);
        const nextTier = ACHIEVEMENT_TIERS.find((tier) => tier.threshold > points);

        pointsElement.textContent = points;
        tierLabel.textContent = currentTier.name;

        if (nextTier) {
            const tierRange = nextTier.threshold - currentTier.threshold;
            const progress = Math.min(100, Math.round(((points - currentTier.threshold) / tierRange) * 100));
            progressBar.style.width = `${progress}%`;
            progressBar.setAttribute('aria-valuenow', progress);
            nextBadgeMessage.textContent = `Necesitas ${nextTier.threshold - points} puntos para desbloquear la insignia "${nextTier.name}".`;
        } else {
            progressBar.style.width = '100%';
            progressBar.setAttribute('aria-valuenow', 100);
            nextBadgeMessage.textContent = '¡Has alcanzado la Gloria Eterna! Comparte tus logros con el mundo.';
        }

        badgeGrid.innerHTML = ACHIEVEMENT_TIERS.map((tier) => {
            const unlocked = points >= tier.threshold;
            const stateClass = unlocked ? 'unlocked' : 'locked';
            return `
                <div class="col-sm-6 col-md-4">
                    <div class="badge-card ${stateClass}">
                        <div class="badge-icon">
                            <i class="fas ${tier.icon}"></i>
                        </div>
                        <h5>${tier.name}</h5>
                        <p>${tier.description}</p>
                    </div>
                </div>
            `;
        }).join('');

        if (!achievementState.history.length) {
            timeline.innerHTML = `
                <li class="list-group-item text-white-50 bg-transparent">
                    Aún no hay torneos registrados. ¡Simula tu primer Mundial y acumula puntos!
                </li>
            `;
        } else {
            timeline.innerHTML = achievementState.history.map((entry) => {
                const date = new Date(entry.timestamp);
                const formatter = new Intl.DateTimeFormat('es-MX', {
                    day: '2-digit',
                    month: 'short',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                return `
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <strong>${entry.placement}</strong>
                            <span class="text-white-50"> | ${entry.team}</span>
                            <div class="text-white-50 small">${formatter.format(date)}</div>
                        </div>
                        <span class="badge bg-warning text-dark">+${entry.points} pts</span>
                    </li>
                `;
            }).join('');
        }
    }

    function processAchievements(results) {
        if (!tournamentData.favoriteTeamCode) {
            return;
        }

        const favoriteCode = tournamentData.favoriteTeamCode;
        const favoriteName = tournamentData.favoriteTeamName || favoriteCode;
        let pointsEarned = 0;
        let placementLabel = null;

        if (results.champion && results.champion.code === favoriteCode) {
            pointsEarned = FAVORITE_REWARD_POINTS.champion;
            placementLabel = 'Campeón del Mundo';
            achievementState.favoriteTitles += 1;
        } else if (results.runnerUp && results.runnerUp.code === favoriteCode) {
            pointsEarned = FAVORITE_REWARD_POINTS.runnerUp;
            placementLabel = 'Subcampeón Mundial';
        } else if (results.thirdPlace && results.thirdPlace.code === favoriteCode) {
            pointsEarned = FAVORITE_REWARD_POINTS.thirdPlace;
            placementLabel = 'Tercer Lugar Mundialista';
        } else if (results.fourthPlace && results.fourthPlace.code === favoriteCode) {
            pointsEarned = FAVORITE_REWARD_POINTS.fourthPlace;
            placementLabel = 'Cuarto Lugar Mundialista';
        }

        if (!pointsEarned || !placementLabel) {
            return;
        }

        achievementState.points += pointsEarned;
        achievementState.favoritePodiums += 1;

        achievementState.history.unshift({
            placement: placementLabel,
            points: pointsEarned,
            team: favoriteName,
            code: favoriteCode,
            timestamp: new Date().toISOString()
        });
        achievementState.history = achievementState.history.slice(0, MAX_HISTORY_ITEMS);

        const previouslyUnlocked = new Set(achievementState.badgesUnlocked);
        achievementState.badgesUnlocked = ACHIEVEMENT_TIERS
            .filter((tier) => achievementState.points >= tier.threshold)
            .map((tier) => tier.name);
        const newBadges = achievementState.badgesUnlocked.filter((badge) => !previouslyUnlocked.has(badge) && badge !== 'Novato');

        saveAchievementState(achievementState);
        renderAchievementPanel();

        showNotification(`¡${placementLabel}! Tu selección sumó ${pointsEarned} puntos mundialistas.`, 'success');
        if (newBadges.length) {
            showNotification(`Nueva insignia desbloqueada: ${newBadges.join(', ')}`, 'warning');
        }
    }

    function shareAchievementProgress() {
        const tier = getCurrentTier(achievementState.points);
        const message = `¡Estoy arrasando en la Copa Mundial 2026! ${achievementState.points} pts mundialistas · Rango: ${tier.name}. ¿Te atreves a superar mis logros?`;

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(message)
                .then(() => showNotification('Texto copiado. ¡Comparte tus logros con tus amigos!', 'success'))
                .catch(() => fallbackShare(message));
        } else {
            fallbackShare(message);
        }
    }

    function fallbackShare(message) {
        prompt('Copia y comparte tus logros:', message);
    }

    function resetAchievementProgress() {
        const wantsReset = confirm('Esto eliminará todos tus puntos e historial mundialista. ¿Deseas continuar?');
        if (!wantsReset) {
            return;
        }

        achievementState = getDefaultAchievementState();
        saveAchievementState(achievementState);
        renderAchievementPanel();
        showNotification('Progreso mundialista reiniciado correctamente.', 'info');
    }

    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 320px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 4000);
    }

    window.playMatch = playMatch;
    window.playThirdPlace = playThirdPlace;
});


