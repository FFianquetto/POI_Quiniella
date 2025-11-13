@extends('layouts.app')

@section('template_title')
    {{ __('Quinielas') }}
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header de la página -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="world-cup-header quiniela-header">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h1 class="mb-1">
                            <i class="fas fa-trophy me-3"></i>
                            Quinielas Disponibles
                        </h1>
                        <p class="mb-0">
                            Participa en las mejores quinielas de fútbol y demuestra tus predicciones mundialistas.
                        </p>
                    </div>
                    <div class="world-cup-badge text-end">
                        <span class="badge bg-warning text-dark fs-6">
                            <i class="fas fa-users me-2"></i>
                            {{ $quinielas->count() }} Quinielas Activas
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($worldCupMatches->isNotEmpty())
    <div class="row mt-4">
        <div class="col-sm-12">
            <div class="card mundial-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span id="worldCupCardTitle">
                            <i class="fas fa-globe-americas me-2"></i>
                            Quinielas del Mundial 2026
                        </span>
                        <span class="badge bg-warning text-dark">
                            No hay empates: elige ganador directo
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3 text-end">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="randomFillButton">
                            <i class="fas fa-dice me-2"></i>
                            Autocompletar aleatorio
                        </button>
                    </div>
                    <form action="{{ route('quinielas.mundial.apostar') }}"
                          method="POST"
                          id="worldCupBetsForm"
                          data-matches="{{ $worldCupMatches->count() }}">
                        @csrf
                        <div class="table-responsive">
                            <table class="table align-middle mundial-table">
                                <thead>
                                    <tr>
                                        <th>Llave</th>
                                        <th class="confed-col">Confederaciones</th>
                                        <th class="text-center ganador-col">¿Quién gana?</th>
                                        <th class="text-center marcador-col">Marcador estimado</th>
                                        <th class="text-center seleccion-col">Tu selección</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($worldCupMatches as $match)
                                        @php
                                            $bet = $usuarioBets->get($match['match_key']);
                                        @endphp
                                        <tr data-match-key="{{ $match['match_key'] }}"
                                            data-match-label="{{ $match['team_a']['code'] }} vs {{ $match['team_b']['code'] }}">
                                            <td class="llave-cell">
                                                <div class="match-teams">
                                                    <div class="team-line">
                                                        <span class="team-code badge bg-primary">{{ $match['team_a']['code'] }}</span>
                                                        <span class="team-name">{{ $match['team_a']['name'] }}</span>
                                                    </div>
                                                    <div class="vs-badge mini">vs</div>
                                                    <div class="team-line">
                                                        <span class="team-code badge bg-secondary">{{ $match['team_b']['code'] }}</span>
                                                        <span class="team-name">{{ $match['team_b']['name'] }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="confed-col">
                                                <div class="text-muted small">
                                                    {{ $match['team_a']['continent'] }} / {{ $match['team_b']['continent'] }}
                                                </div>
                                            </td>
                                            <td class="text-center ganador-col">
                                                <div class="winner-options">
                                                    <label class="winner-option">
                                                        <input type="radio"
                                                               name="bets[{{ $match['match_key'] }}][team]"
                                                               value="{{ $match['team_a']['code'] }}"
                                                               class="form-check-input winner-radio"
                                                               {{ optional($bet)->selected_code === $match['team_a']['code'] ? 'checked' : '' }}>
                                                        <span class="winner-btn">{{ $match['team_a']['code'] }}</span>
                                                    </label>
                                                    <label class="winner-option">
                                                        <input type="radio"
                                                               name="bets[{{ $match['match_key'] }}][team]"
                                                               value="{{ $match['team_b']['code'] }}"
                                                               class="form-check-input winner-radio"
                                                               {{ optional($bet)->selected_code === $match['team_b']['code'] ? 'checked' : '' }}>
                                                        <span class="winner-btn">{{ $match['team_b']['code'] }}</span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td class="text-center marcador-col">
                                                <div class="marcador-inputs d-flex justify-content-center align-items-center gap-2">
                                                    <input type="number"
                                                           name="bets[{{ $match['match_key'] }}][score_a]"
                                                           min="0"
                                                           max="20"
                                                           class="form-control form-control-sm marcador"
                                                           placeholder="0"
                                                           value="{{ optional($bet)->score_a }}">
                                                    <span class="fw-bold">-</span>
                                                    <input type="number"
                                                           name="bets[{{ $match['match_key'] }}][score_b]"
                                                           min="0"
                                                           max="20"
                                                           class="form-control form-control-sm marcador"
                                                           placeholder="0"
                                                           value="{{ optional($bet)->score_b }}">
                                                </div>
                                            </td>
                                            <td class="text-center seleccion-col">
                                                <span class="selection-badge badge {{ $bet ? 'bg-success text-white fw-semibold' : 'text-muted' }}">
                                                    @if($bet)
                                                        {{ $bet->selected_code }}
                                                        @if(!is_null($bet->score_a) && !is_null($bet->score_b))
                                                            {{ $bet->score_a }}-{{ $bet->score_b }}
                                                        @endif
                                                    @else
                                                        Sin selección
                                                    @endif
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="bets-validation-summary mt-4">
                            <div id="betsValidationMessage" class="alert alert-warning d-none" role="alert"></div>
                            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                                <p class="mb-0 text-muted" id="betsProgressText">
                                    Completa tus predicciones para registrar la Quiniela de esta fase.
                                </p>
                                <div class="d-flex flex-column flex-md-row gap-2 w-100 w-lg-auto">
                                    <button type="submit" class="btn btn-success w-100" id="submitBetsButton" disabled>
                                        <i class="fas fa-save me-2"></i>
                                        Guardar Quiniela
                                    </button>
                                </div>
                            </div>
                        </div>

    @endif
</div>

<style>
.team-vs {
    display: flex;
    align-items: center;
    justify-content: center;
}

.team {
    min-width: 80px;
}

.team-name {
    font-size: 0.9rem;
}

.vs-badge {
    font-weight: bold;
}

.league-info {
    margin-top: 8px;
}

.date-time, .deadline-info {
    line-height: 1.2;
}

.date, .deadline-date {
    font-size: 0.9rem;
}

.time, .deadline-time {
    font-size: 0.8rem;
}

.participants-count .badge {
    min-width: 60px;
}

.hinche-placeholder .hinche-card {
    max-width: 520px;
    background: rgba(255, 255, 255, 0.85);
    border-radius: 18px;
    padding: 28px;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08);
    backdrop-filter: blur(4px);
}

@media (max-width: 768px) {
    .hinche-placeholder .hinche-card {
        padding: 22px;
    }
}

.mundial-card {
    border-radius: 22px;
    box-shadow: 0 18px 32px rgba(46, 125, 50, 0.2);
    margin-bottom: 3rem;
}

.mundial-table thead {
    background: linear-gradient(120deg, rgba(39, 174, 96, 0.95), rgba(27, 132, 68, 0.95));
    color: var(--white);
    text-transform: uppercase;
    font-size: 0.78rem;
    letter-spacing: 0.05em;
}

.llave-cell {
    min-width: 220px;
}

.match-teams {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.team-line {
    display: flex;
    align-items: center;
    gap: 8px;
}

.team-line .team-code {
    min-width: 44px;
}

.winner-options {
    display: flex;
    justify-content: center;
    gap: 16px;
}

.winner-option {
    position: relative;
    display: inline-flex;
}

.winner-option .winner-radio {
    position: absolute;
    opacity: 0;
    pointer-events: none;
    left: -9999px;
    margin: 0;
    appearance: none;
    outline: none;
    box-shadow: none;
}

.winner-option .winner-btn {
    display: inline-block;
    min-width: 64px;
    padding: 6px 14px;
    border-radius: 12px;
    background: rgba(46, 125, 50, 0.12);
    color: var(--primary-green);
    font-weight: 600;
    transition: all 0.2s ease;
    cursor: pointer;
}

.winner-option .winner-radio:checked + .winner-btn {
    background: linear-gradient(120deg, #3de074, #1bb154);
    color: var(--white);
    box-shadow: 0 8px 16px rgba(27, 177, 84, 0.35);
}

.winner-option .winner-btn:hover {
    transform: translateY(-1px);
}

.confed-col {
    min-width: 160px;
}

.ganador-col {
    min-width: 170px;
}

.marcador-col {
    min-width: 180px;
}

.seleccion-col {
    min-width: 160px;
}

.marcador-inputs .marcador {
    width: 60px;
    text-align: center;
}

.vs-badge.mini {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--primary-green);
    text-transform: uppercase;
    margin: 2px 0;
}

.bets-validation-summary {
    background: rgba(248, 252, 248, 0.9);
    border: 1px solid rgba(46, 125, 50, 0.18);
    border-radius: 18px;
    padding: 18px 22px;
}

.bets-validation-summary .btn-success {
    font-weight: 700;
    box-shadow: 0 10px 20px rgba(46, 125, 50, 0.22);
}

.bets-validation-summary .btn-outline-secondary {
    font-weight: 600;
    border-radius: 999px;
}

.bets-validation-summary .alert {
    border-radius: 12px;
    margin-bottom: 16px;
    border: none;
}

@media (max-width: 768px) {
    .match-teams {
        align-items: center;
    }

    .winner-options {
        gap: 8px;
    }

    .marcador-inputs .marcador {
        width: 50px;
    }
}
</style>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('worldCupBetsForm');
    if (!form) {
        return;
    }

    const totalMatches = parseInt(form.dataset.matches || '0', 10);
    const randomFillButton = document.getElementById('randomFillButton');
    const submitButton = document.getElementById('submitBetsButton');
    const messageBox = document.getElementById('betsValidationMessage');
    const progressText = document.getElementById('betsProgressText');

    const getMatchRows = () => Array.from(form.querySelectorAll('tr[data-match-key]'));

    function showMessage(text, type = 'warning') {
        if (!messageBox) {
            return;
        }
        messageBox.textContent = text;
        messageBox.className = `alert alert-${type}`;
        messageBox.classList.remove('d-none');
    }

    function hideMessage() {
        if (!messageBox) {
            return;
        }
        messageBox.classList.add('d-none');
        messageBox.textContent = '';
    }

    function evaluateSelections() {
        const rows = getMatchRows();
        let completed = 0;
        const pendingLabels = [];

        rows.forEach((row) => {
            const radios = row.querySelectorAll('.winner-options input[type="radio"]');
            const scoreInputs = row.querySelectorAll('.marcador');
            const matchLabel = row.dataset.matchLabel || row.dataset.matchKey;
            const selectionBadge = row.querySelector('.seleccion-col .selection-badge');

            const checkedRadio = Array.from(radios).find((radio) => radio.checked);
            const winnerSelected = Boolean(checkedRadio);
            const scoresFilled = Array.from(scoreInputs).every((input) => input.value !== '' && input.value !== null);

            if (selectionBadge) {
                if (winnerSelected && scoresFilled) {
                    const winnerCode = checkedRadio.value;
                    selectionBadge.textContent = `${winnerCode} ${scoreInputs[0].value}-${scoreInputs[1].value}`;
                    selectionBadge.classList.remove('bg-secondary', 'text-muted');
                    selectionBadge.classList.add('bg-success', 'text-white', 'fw-semibold');
                } else {
                    selectionBadge.textContent = 'Sin selección';
                    selectionBadge.classList.remove('bg-success', 'text-white', 'fw-semibold');
                    selectionBadge.classList.add('text-muted');
                }
            }

            if (winnerSelected && scoresFilled) {
                completed += 1;
            } else {
                pendingLabels.push(matchLabel);
            }
        });

        const allCompleted = completed === totalMatches && totalMatches > 0;

        if (allCompleted) {
            submitButton?.removeAttribute('disabled');
            submitButton?.classList.remove('disabled');
            progressText.classList.remove('text-warning', 'text-danger');
            progressText.classList.add('text-success', 'fw-semibold');
            progressText.textContent = '¡Listo! Todos los partidos de esta fase tienen pronóstico.';
            hideMessage();
        } else {
            submitButton?.setAttribute('disabled', 'disabled');
            submitButton?.classList.add('disabled');
            const remaining = Math.max(totalMatches - completed, 0);
            progressText.classList.remove('text-success', 'fw-semibold');
            progressText.classList.add('text-warning');
            progressText.textContent = `Te faltan ${remaining} pronóstico${remaining === 1 ? '' : 's'} por completar en esta fase.`;
            if (pendingLabels.length) {
                showMessage(`Debes seleccionar ganador y marcador para: ${pendingLabels.join(', ')}`, 'warning');
            }
        }

        return { allCompleted, pendingLabels };
    }

    randomFillButton?.addEventListener('click', (event) => {
        event.preventDefault();
        const rows = getMatchRows();

        rows.forEach((row) => {
            const radios = Array.from(row.querySelectorAll('.winner-options input[type="radio"]'));
            const scoreInputs = row.querySelectorAll('.marcador');

            if (radios.length !== 2 || scoreInputs.length !== 2) {
                return;
            }

            const selectedIndex = Math.random() < 0.5 ? 0 : 1;
            radios.forEach((radio, idx) => {
                radio.checked = idx === selectedIndex;
            });

            const winnerScore = Math.floor(Math.random() * 5) + 1;
            let loserScore = Math.floor(Math.random() * winnerScore);
            if (loserScore === winnerScore) {
                loserScore = Math.max(0, winnerScore - 1);
            }

            if (selectedIndex === 0) {
                scoreInputs[0].value = winnerScore;
                scoreInputs[1].value = loserScore;
            } else {
                scoreInputs[0].value = loserScore;
                scoreInputs[1].value = winnerScore;
            }

            radios[selectedIndex].dispatchEvent(new Event('change', { bubbles: true }));
            scoreInputs.forEach((input) => {
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });

        const { allCompleted } = evaluateSelections();
        if (allCompleted) {
            showMessage('Generamos una quiniela aleatoria para esta fase. ¡Revísala antes de guardar!', 'info');
        } else {
            showMessage('Se generaron selecciones aleatorias. Revisa los partidos pendientes antes de guardar.', 'warning');
        }
    });

    form.addEventListener('input', (event) => {
        if (event.target.matches('.winner-radio, .marcador')) {
            evaluateSelections();
        }
    });

    form.addEventListener('submit', (event) => {
        const { allCompleted } = evaluateSelections();
        if (!allCompleted) {
            event.preventDefault();
            showMessage('Completa todas tus predicciones antes de guardar la Quiniela.', 'danger');
        }
    });

    evaluateSelections();
});
</script>
@endpush
@endsection
