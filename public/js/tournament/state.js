(function (global) {
    const namespace = global.TournamentSim = global.TournamentSim || {};
    const constants = namespace.constants;

    if (!constants) {
        throw new Error('TournamentSim.constants no está inicializado. Asegúrate de cargar constants.js primero.');
    }

    function createEmptyRewards() {
        return {
            champion: null,
            runnerUp: null,
            thirdPlace: null,
            fourthPlace: null
        };
    }

    function createInitialTournamentState(overrides = {}) {
        return {
            teams: [],
            rounds: [],
            currentRound: 0,
            currentMatch: 0,
            favoriteTeamCode: null,
            favoriteTeamName: null,
            thirdPlaceMatch: null,
            rewards: createEmptyRewards(),
            uuid: null,
            status: 'idle',
            ...overrides
        };
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

    function loadAchievementState() {
        try {
            const raw = global.localStorage.getItem(constants.ACHIEVEMENT_STORAGE_KEY);
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
            global.localStorage.setItem(constants.ACHIEVEMENT_STORAGE_KEY, JSON.stringify(state));
        } catch (error) {
            console.warn('No se pudo guardar el progreso de logros', error);
        }
    }

    let tournamentData = createInitialTournamentState();
    let achievementState = loadAchievementState();

    function getTournamentData() {
        return tournamentData;
    }

    function setTournamentData(nextState) {
        tournamentData = { ...createInitialTournamentState(), ...nextState };
        return tournamentData;
    }

    function mergeTournamentData(partialState) {
        tournamentData = { ...tournamentData, ...partialState };
        return tournamentData;
    }

    function resetTournamentData() {
        tournamentData = createInitialTournamentState();
        return tournamentData;
    }

    function getAchievementState() {
        return achievementState;
    }

    function updateAchievementState(updater) {
        const nextState = typeof updater === 'function' ? updater(achievementState) : updater;
        achievementState = { ...achievementState, ...nextState };
        saveAchievementState(achievementState);
        return achievementState;
    }

    function resetAchievementState() {
        achievementState = getDefaultAchievementState();
        saveAchievementState(achievementState);
        return achievementState;
    }

    namespace.state = {
        createEmptyRewards,
        createInitialTournamentState,
        getTournamentData,
        setTournamentData,
        mergeTournamentData,
        resetTournamentData,
        getAchievementState,
        updateAchievementState,
        resetAchievementState,
        loadAchievementState,
        saveAchievementState,
        getDefaultAchievementState
    };
})(window);

