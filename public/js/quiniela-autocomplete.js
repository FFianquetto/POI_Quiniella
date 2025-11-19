/**
 * Funcionalidad de autocompletar aleatorio para quinielas
 * Este script solo selecciona opciones visualmente, NO guarda nada.
 * El usuario debe hacer click en "Guardar Quiniela" para confirmar.
 */
(function() {
    'use strict';

    // Esperar a que el DOM esté completamente cargado
    document.addEventListener('DOMContentLoaded', function() {
        const randomFillButton = document.getElementById('randomFillButton');
        
        // Si no existe el botón, no hacer nada
        if (!randomFillButton) {
            return;
        }

        // Buscar elementos del formulario
        const betsForm = document.getElementById('worldCupBetsForm');
        const messageBox = document.getElementById('betsValidationMessage');

        // Event listener para el botón de autocompletar
        randomFillButton.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            
            // Verificar si ya tiene todas las apuestas guardadas
            const submitButton = document.getElementById('submitBetsButton');
            const hasAllBets = submitButton && submitButton.dataset.hasAllBets === 'true';
            if (hasAllBets) {
                return; // No permitir autocompletar si ya está guardado
            }
            
            // Verificar que el formulario exista
            if (!betsForm) {
                console.error('No se encontró el formulario de apuestas');
                return;
            }
            
            // Obtener todas las filas de partidos
            const matchRows = betsForm.querySelectorAll('tbody tr[data-match-key]');
            
            if (!matchRows || matchRows.length === 0) {
                console.warn('No hay partidos disponibles para autocompletar');
                return;
            }

            // Iterar sobre cada partido y completar aleatoriamente
            matchRows.forEach(function(row) {
                // Obtener los radios de ganador
                const winnerRadios = Array.from(row.querySelectorAll('input.winner-radio[type="radio"]'));
                if (winnerRadios.length !== 2) {
                    return;
                }

                // Obtener los inputs de marcador
                const scoreInputs = Array.from(row.querySelectorAll('input.marcador[type="number"]'));
                if (scoreInputs.length !== 2) {
                    return;
                }

                // Seleccionar aleatoriamente el ganador (50% de probabilidad)
                const selectedWinnerIndex = Math.random() < 0.5 ? 0 : 1;
                
                // Guardar el radio actualmente marcado para comparar
                const previouslyChecked = winnerRadios.find(function(radio) {
                    return radio.checked;
                });
                
                // Desmarcar todos los radios primero
                winnerRadios.forEach(function(radio) {
                    if (radio.checked) {
                        radio.checked = false;
                    }
                });
                
                // Marcar el ganador seleccionado - PRIMERO establecer checked
                const selectedRadio = winnerRadios[selectedWinnerIndex];
                selectedRadio.checked = true;
                
                // Luego hacer click en el label para actualizar la UI visual
                const labelElement = selectedRadio.closest('label.winner-option');
                if (labelElement) {
                    // Hacer click en el label para activar visualmente el radio
                    labelElement.click();
                } else {
                    // Si no hay label, hacer click directamente en el radio
                    selectedRadio.click();
                }
                
                // CRÍTICO: Disparar evento change explícitamente para que el formulario lo capture
                // Usar setTimeout para asegurar que se ejecute después del click
                setTimeout(function() {
                    const changeEvent = new Event('change', { bubbles: true, cancelable: true });
                    selectedRadio.dispatchEvent(changeEvent);
                }, 10);

                // Generar marcadores aleatorios (ganador siempre tiene más goles)
                const winnerScore = Math.floor(Math.random() * 5) + 1; // Entre 1 y 5
                let loserScore = Math.floor(Math.random() * winnerScore); // Menor que ganador
                
                // Asegurar que el perdedor tenga menos goles
                if (loserScore >= winnerScore) {
                    loserScore = Math.max(0, winnerScore - 1);
                }
                
                // Si ganador tiene 1 gol, perdedor tiene 0
                if (winnerScore === 1) {
                    loserScore = 0;
                }

                // Asignar marcadores según quién ganó (SOLO VISUAL - NO SE GUARDA)
                // Forzar el cambio incluso si el valor es el mismo
                const oldValueA = scoreInputs[0].value;
                const oldValueB = scoreInputs[1].value;
                
                if (selectedWinnerIndex === 0) {
                    // Primer equipo (team_a) gana
                    scoreInputs[0].value = winnerScore;
                    scoreInputs[1].value = loserScore;
                } else {
                    // Segundo equipo (team_b) gana
                    scoreInputs[0].value = loserScore;
                    scoreInputs[1].value = winnerScore;
                }

                // Disparar eventos en los inputs de marcador para actualizar la UI
                // Importante: disparar eventos incluso si el valor no cambió
                scoreInputs.forEach(function(input) {
                    // Forzar el evento input primero
                    const inputEvent = new Event('input', { bubbles: true, cancelable: true });
                    input.dispatchEvent(inputEvent);
                    
                    // Disparar evento change
                    const changeEvent = new Event('change', { bubbles: true, cancelable: true });
                    input.dispatchEvent(changeEvent);
                    
                    // También disparar evento blur para simular interacción completa
                    const blurEvent = new Event('blur', { bubbles: true, cancelable: true });
                    input.dispatchEvent(blurEvent);
                });
            });

            // Forzar evaluación después de un delay para asegurar que todos los eventos se procesen
            setTimeout(function() {
                // Verificar que los radios realmente están marcados
                const allChecked = Array.from(matchRows).every(function(row) {
                    const radios = Array.from(row.querySelectorAll('input.winner-radio[type="radio"]'));
                    const checkedCount = radios.filter(function(radio) {
                        return radio.checked === true;
                    }).length;
                    return checkedCount === 1;
                });
                
                // Llamar a evaluateSelections si existe en el scope global para actualizar el botón de guardar
                if (typeof window.evaluateSelections === 'function') {
                    const result = window.evaluateSelections();
                    
                    if (messageBox) {
                        if (result && result.allCompleted) {
                            messageBox.textContent = '¡Quiniela autocompletada! Se generaron selecciones aleatorias para todos los partidos. Revisa y haz click en "Guardar Quiniela" para confirmar.';
                            messageBox.className = 'alert alert-success';
                        } else {
                            messageBox.textContent = 'Se generaron selecciones aleatorias. Revisa los partidos y haz click en "Guardar Quiniela" para confirmar.';
                            messageBox.className = 'alert alert-info';
                        }
                        messageBox.classList.remove('d-none');
                    }
                } else {
                    console.warn('evaluateSelections no está disponible globalmente');
                    if (messageBox) {
                        messageBox.textContent = 'Se generaron selecciones aleatorias. Revisa los partidos y haz click en "Guardar Quiniela" para confirmar.';
                        messageBox.className = 'alert alert-info';
                        messageBox.classList.remove('d-none');
                    }
                }
            }, 600);
        });
    });
})();

