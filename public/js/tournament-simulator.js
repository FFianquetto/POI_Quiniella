document.addEventListener('DOMContentLoaded', () => {
    try {
        const namespace = window.TournamentSim;

        if (!namespace) {
            console.error('TournamentSim namespace no está disponible. Verifica que los scripts se hayan cargado correctamente.');
            console.error('Scripts requeridos: constants.js, state.js, storage.js, notifications.js, api.js, controller.js');
            return;
        }

        if (!namespace.TournamentController) {
            console.error('TournamentSim.TournamentController no está disponible. Verifica el orden de carga de los scripts.');
            console.error('Namespace disponible:', Object.keys(namespace));
            return;
        }

        const controller = new namespace.TournamentController(document);
        controller.init();
        
        // Verificar que el botón se haya inicializado correctamente
        const generateBtn = document.getElementById('generateTournament');
        if (!generateBtn) {
            console.warn('El botón generateTournament no se encontró en el DOM.');
        } else {
            console.log('TournamentController inicializado correctamente.');
        }
    } catch (error) {
        console.error('Error al inicializar TournamentController:', error);
        console.error('Stack trace:', error.stack);
    }
});

