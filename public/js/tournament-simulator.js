document.addEventListener('DOMContentLoaded', () => {
    const namespace = window.TournamentSim;

    if (!namespace?.TournamentController) {
        console.error('TournamentSim.TournamentController no está disponible. Verifica el orden de carga de los scripts.');
        return;
    }

    const controller = new namespace.TournamentController(document);
    controller.init();
});

