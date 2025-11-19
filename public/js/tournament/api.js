(function (global) {
    const namespace = global.TournamentSim = global.TournamentSim || {};
    const stateModule = namespace.state;
    const storageModule = namespace.storage;

    if (!stateModule || !storageModule) {
        throw new Error('TournamentSim.state y TournamentSim.storage deben cargarse antes de api.js.');
    }

    function createSyncService({
        csrfToken,
        worldCupTeamsDataset = [],
        lockGenerateButton,
        unlockGenerateButton,
        showNotification,
        onConflictHydrate
    } = {}) {
        if (!csrfToken) {
            console.warn('createSyncService: No se proporcionó token CSRF. La sincronización remota se omitirá.');
        }

        function applyServerStatusFromResponse(data, { initial = false, conflict = false } = {}) {
            if (!data || typeof data !== 'object') {
                return;
            }

            const currentState = stateModule.getTournamentData();
            if ((initial || !currentState.uuid) && data.uuid) {
                stateModule.mergeTournamentData({ uuid: data.uuid });
            }

            if (data.status) {
                stateModule.mergeTournamentData({ status: data.status });

                if (data.status === 'in_progress') {
                    const message = data.message || 'Tienes un Mundial en curso. Completa los partidos pendientes antes de generar otro.';
                    lockGenerateButton?.(message);
                } else if (data.status === 'completed') {
                    const message = data.message || 'La última Copa finalizó. Puedes generar una nueva simulación cuando gustes.';
                    unlockGenerateButton?.(message);
                }
            }

            if (conflict && data.message) {
                showNotification?.(data.message, 'warning');
            }
        }

        async function syncTournamentState({ initial = false } = {}) {
            if (!csrfToken) {
                return null;
            }

            const tournamentData = stateModule.getTournamentData();
            const sanitizedRewards = tournamentData.rewards ? {
                champion: storageModule.serializeTeamForStorage(tournamentData.rewards.champion),
                runnerUp: storageModule.serializeTeamForStorage(tournamentData.rewards.runnerUp),
                thirdPlace: storageModule.serializeTeamForStorage(tournamentData.rewards.thirdPlace),
                fourthPlace: storageModule.serializeTeamForStorage(tournamentData.rewards.fourthPlace)
            } : null;
            const serializedThirdPlace = storageModule.serializeThirdPlaceMatch(tournamentData.thirdPlaceMatch);

            const resultsPayload = {};
            if (sanitizedRewards && Object.values(sanitizedRewards).some((value) => value)) {
                resultsPayload.rewards = sanitizedRewards;
            }
            if (serializedThirdPlace) {
                resultsPayload.third_place = serializedThirdPlace;
            }

            const payload = {
                favorite_team: tournamentData.favoriteTeamCode,
            };

            // Siempre incluir rounds (pueden estar vacíos al inicio)
            const serializedRounds = storageModule.serializeRoundsForStorage(tournamentData);
            if (serializedRounds && serializedRounds.length > 0) {
                payload.rounds = serializedRounds;
            } else {
                // Si no hay rounds, enviar array vacío para que se guarde el torneo
                payload.rounds = [];
            }

            if (Object.keys(resultsPayload).length) {
                payload.results = resultsPayload;
            }

            if (initial) {
                const baseTeams = Array.isArray(tournamentData.teams) && tournamentData.teams.length
                    ? tournamentData.teams
                    : worldCupTeamsDataset;
                payload.teams = baseTeams.map((team) => storageModule.serializeTeamForStorage(team));
            }

            const url = initial
                ? '/torneo/store'
                : (tournamentData.uuid ? `/torneo/${tournamentData.uuid}` : null);

            if (!url) {
                return null;
            }

            try {
                console.log('Sincronizando torneo:', { initial, url, payload: { ...payload, teams: payload.teams?.length, rounds: payload.rounds?.length } });
                
                const response = await fetch(url, {
                    method: initial ? 'POST' : 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                const responseText = await response.text();
                let data = null;

                if (responseText) {
                    try {
                        data = JSON.parse(responseText);
                    } catch (parseError) {
                        console.warn('No se pudo interpretar la respuesta del torneo', parseError, responseText);
                    }
                }

                console.log('Respuesta del servidor:', { status: response.status, ok: response.ok, data });

                if (!response.ok) {
                    if (initial && response.status === 409) {
                        applyServerStatusFromResponse(data, { initial: true, conflict: true });
                        onConflictHydrate?.();
                        return null;
                    }

                    const errorMessage = data?.message || `Error del servidor (${response.status})`;
                    console.error('Error al sincronizar torneo:', errorMessage, data);
                    
                    if (initial) {
                        unlockGenerateButton?.('No se pudo crear el torneo. Intenta nuevamente.');
                        showNotification?.(errorMessage, 'danger');
                    }

                    throw new Error(errorMessage);
                }

                applyServerStatusFromResponse(data, { initial });
                
                // Si es la creación inicial y hay datos del torneo, actualizar el UUID
                if (initial && data && data.uuid) {
                    stateModule.mergeTournamentData({ uuid: data.uuid });
                    if (data.status) {
                        stateModule.mergeTournamentData({ status: data.status });
                    }
                }
                
                return data;
            } catch (error) {
                console.error('Error al sincronizar torneo:', error);
                
                if (initial) {
                    unlockGenerateButton?.('No se pudo crear el torneo. Intenta nuevamente.');
                    showNotification?.(error.message || 'Error al crear el torneo. Revisa la consola para más detalles.', 'danger');
                }

                return null;
            }
        }

        return {
            syncTournamentState,
            applyServerStatusFromResponse
        };
    }

    namespace.createSyncService = createSyncService;
})(window);

