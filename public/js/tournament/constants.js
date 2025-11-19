(function (global) {
    const namespace = global.TournamentSim = global.TournamentSim || {};

    const constants = {
        ROUND_NAMES: [
            'Dieciseisavos de Final',
            'Octavos de Final',
            'Cuartos de Final',
            'Semifinales',
            'Final'
        ],
        GOAL_DIVISOR: 30,
        MAX_GOALS: 6,
        ACHIEVEMENT_STORAGE_KEY: 'worldCupAchievementState',
        MAX_HISTORY_ITEMS: 6,
        ACHIEVEMENT_TIERS: [
            { name: 'Novato', threshold: 0, icon: 'fa-seedling', description: 'Has iniciado tu camino mundialista.' },
            { name: 'Contendiente', threshold: 150, icon: 'fa-medal', description: 'Tu selección ya siente el protagonismo.' },
            { name: 'Leyenda Regional', threshold: 350, icon: 'fa-flag', description: 'Dominas tu confederación con autoridad.' },
            { name: 'Figura Internacional', threshold: 600, icon: 'fa-trophy', description: 'Eres referente en las grandes noches.' },
            { name: 'Gloria Eterna', threshold: 900, icon: 'fa-crown', description: 'Te codeas con los campeones del mundo.' }
        ],
        FAVORITE_REWARD_POINTS: {
            champion: 150,
            runnerUp: 100,
            thirdPlace: 70,
            fourthPlace: 40
        }
    };

    namespace.constants = Object.freeze(constants);
})(window);

