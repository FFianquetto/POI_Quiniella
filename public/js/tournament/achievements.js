(function (global) {
    const namespace = global.TournamentSim = global.TournamentSim || {};
    const stateModule = namespace.state;
    const constants = namespace.constants;

    if (!stateModule || !constants) {
        throw new Error('TournamentSim.state y TournamentSim.constants deben cargarse antes de achievements.js.');
    }

    function getCurrentTier(points) {
        let tier = constants.ACHIEVEMENT_TIERS[0];
        constants.ACHIEVEMENT_TIERS.forEach((candidate) => {
            if (points >= candidate.threshold) {
                tier = candidate;
            }
        });
        return tier;
    }

    function createAchievementManager({
        elements = {},
        showNotification
    } = {}) {
        const {
            pointsElement,
            tierLabel,
            progressBar,
            nextBadgeMessage,
            badgeGrid,
            timeline
        } = elements;

        function render() {
            if (!pointsElement || !tierLabel || !progressBar || !badgeGrid || !timeline) {
                return;
            }

            const achievementState = stateModule.getAchievementState();
            const points = achievementState.points;
            const currentTier = getCurrentTier(points);
            const nextTier = constants.ACHIEVEMENT_TIERS.find((tier) => tier.threshold > points);

            pointsElement.textContent = points;
            tierLabel.textContent = currentTier.name;

            if (nextTier) {
                const tierRange = nextTier.threshold - currentTier.threshold;
                const progress = Math.min(100, Math.round(((points - currentTier.threshold) / tierRange) * 100));
                progressBar.style.width = `${progress}%`;
                progressBar.setAttribute('aria-valuenow', progress);
                if (nextBadgeMessage) {
                    nextBadgeMessage.textContent = `Necesitas ${nextTier.threshold - points} puntos para desbloquear la insignia "${nextTier.name}".`;
                }
            } else {
                progressBar.style.width = '100%';
                progressBar.setAttribute('aria-valuenow', 100);
                if (nextBadgeMessage) {
                    nextBadgeMessage.textContent = '¡Has alcanzado la Gloria Eterna! Comparte tus logros con el mundo.';
                }
            }

            badgeGrid.innerHTML = constants.ACHIEVEMENT_TIERS.map((tier) => {
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

        function processTournamentResults(results, favoriteTeam) {
            if (!favoriteTeam?.code) {
                return;
            }

            const favoriteCode = favoriteTeam.code;
            const favoriteName = favoriteTeam.name || favoriteCode;
            let pointsEarned = 0;
            let placementLabel = null;

            if (results.champion && results.champion.code === favoriteCode) {
                pointsEarned = constants.FAVORITE_REWARD_POINTS.champion;
                placementLabel = 'Campeón del Mundo';
            } else if (results.runnerUp && results.runnerUp.code === favoriteCode) {
                pointsEarned = constants.FAVORITE_REWARD_POINTS.runnerUp;
                placementLabel = 'Subcampeón Mundial';
            } else if (results.thirdPlace && results.thirdPlace.code === favoriteCode) {
                pointsEarned = constants.FAVORITE_REWARD_POINTS.thirdPlace;
                placementLabel = 'Tercer Lugar Mundialista';
            } else if (results.fourthPlace && results.fourthPlace.code === favoriteCode) {
                pointsEarned = constants.FAVORITE_REWARD_POINTS.fourthPlace;
                placementLabel = 'Cuarto Lugar Mundialista';
            }

            if (!pointsEarned || !placementLabel) {
                return;
            }

            const previousState = stateModule.getAchievementState();
            const historyEntry = {
                placement: placementLabel,
                points: pointsEarned,
                team: favoriteName,
                code: favoriteCode,
                timestamp: new Date().toISOString()
            };

            const updatedHistory = [historyEntry, ...previousState.history].slice(0, constants.MAX_HISTORY_ITEMS);
            const totalPoints = previousState.points + pointsEarned;
            const badgesUnlocked = constants.ACHIEVEMENT_TIERS
                .filter((tier) => totalPoints >= tier.threshold)
                .map((tier) => tier.name);
            const previouslyUnlocked = new Set(previousState.badgesUnlocked);
            const newBadges = badgesUnlocked.filter((badge) => !previouslyUnlocked.has(badge) && badge !== 'Novato');

            stateModule.updateAchievementState(() => ({
                points: totalPoints,
                favoriteTitles: previousState.favoriteTitles + (placementLabel === 'Campeón del Mundo' ? 1 : 0),
                favoritePodiums: previousState.favoritePodiums + 1,
                history: updatedHistory,
                badgesUnlocked
            }));

            render();

            showNotification?.(`¡${placementLabel}! Tu selección sumó ${pointsEarned} puntos mundialistas.`, 'success');
            if (newBadges.length) {
                showNotification?.(`Nueva insignia desbloqueada: ${newBadges.join(', ')}`, 'warning');
            }
        }

        function shareAchievements() {
            const achievementState = stateModule.getAchievementState();
            const tier = getCurrentTier(achievementState.points);
            const message = `¡Estoy arrasando en la Copa Mundial 2026! ${achievementState.points} pts mundialistas · Rango: ${tier.name}. ¿Te atreves a superar mis logros?`;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(message)
                    .then(() => showNotification?.('Texto copiado. ¡Comparte tus logros con tus amigos!', 'success'))
                    .catch(() => fallbackShare(message));
            } else {
                fallbackShare(message);
            }
        }

        function fallbackShare(message) {
            prompt('Copia y comparte tus logros:', message);
        }

        function resetAchievements() {
            stateModule.resetAchievementState();
            render();
            showNotification?.('Progreso mundialista reiniciado correctamente.', 'info');
        }

        return {
            render,
            processTournamentResults,
            shareAchievements,
            resetAchievements
        };
    }

    namespace.createAchievementManager = createAchievementManager;
})(window);

