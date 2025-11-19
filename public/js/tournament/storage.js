(function (global) {
    const namespace = global.TournamentSim = global.TournamentSim || {};
    const stateModule = namespace.state;

    if (!stateModule) {
        throw new Error('TournamentSim.state no está inicializado. Carga state.js antes de storage.js.');
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
            flag_url: team.flag_url ?? null
        };
    }

    function serializeRoundsForStorage(tournamentData = stateModule.getTournamentData()) {
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
                    stage: round.name ?? null
                };
            })
        }));
    }

    function serializeThirdPlaceMatch(thirdPlaceMatch) {
        if (!thirdPlaceMatch) {
            return null;
        }

        return {
            team1: serializeTeamForStorage(thirdPlaceMatch.team1),
            team2: serializeTeamForStorage(thirdPlaceMatch.team2),
            score1: thirdPlaceMatch.score1 ?? null,
            score2: thirdPlaceMatch.score2 ?? null,
            winner: serializeTeamForStorage(thirdPlaceMatch.winner),
            played: thirdPlaceMatch.played ?? false
        };
    }

    namespace.storage = {
        serializeTeamForStorage,
        serializeRoundsForStorage,
        serializeThirdPlaceMatch
    };
})(window);

