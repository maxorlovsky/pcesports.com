// Step 1, alternative source: the window's matches from PandaScore's free plan, in the same shape
// as out/matches.json, so match.js can link VODs from them (node match.js --source pandascore).
// Writes out/matches-pandascore.json.
import { readFile } from 'node:fs/promises';
import { join } from 'node:path';
import { window as runWindow } from './lib/window.js';
import { pastMatches, stats } from './lib/pandascore.js';
import { ROOT, writeJson } from './lib/cache.js';

// PandaScore assigns its own tiers (S to D); PGL Masters Bucharest 2026 was "a" there and
// S-Tier on Liquipedia, so the top two are kept by default.
const DEFAULT_TIERS = ['s', 'a'];

export async function fetchPandaScore({ from, to }, tiers = DEFAULT_TIERS) {
    const leagues = JSON.parse(await readFile(join(ROOT, 'leagues.json'), 'utf8')).leagues;
    const all = await pastMatches(from, to);
    const teams = {};
    const matches = [];
    const unknownLeagues = new Set();
    for (const x of all) {
        if (!tiers.includes(x.tournament?.tier) || x.status !== 'finished' || x.opponents.length !== 2) continue;
        const [o1, o2] = x.opponents.map((o) => o.opponent);
        for (const o of [o1, o2]) teams[o.slug] = { id: o.slug, name: o.name, short: o.acronym, bracket: null };
        const organizer = leagues[x.league.name] || null;
        if (!organizer) unknownLeagues.add(x.league.name);
        const winnerSide = (id) => (id === o1.id ? 1 : id === o2.id ? 2 : null);
        const maps = x.games.map((g) => ({ n: g.position, map: null, played: g.status === 'finished', winner: winnerSide(g.winner?.id) }));
        const score = (id) => x.results.find((r) => r.team_id === id)?.score ?? 0;
        matches.push({
            id: `pandascore:${x.id}`,
            event: `${x.league.name} ${x.serie.full_name}`,
            organizer,
            tier: x.tournament.tier,
            stage: x.tournament.name,
            team1: o1.slug,
            team2: o2.slug,
            start: x.begin_at,
            scheduledAt: x.original_scheduled_at || x.scheduled_at,
            bestOf: x.number_of_games,
            maps,
            seriesScore: [score(o1.id), score(o2.id)],
            winner: winnerSide(x.winner_id),
            endAt: x.end_at,
            modifiedAt: x.modified_at,
        });
    }
    return {
        source: 'pandascore',
        window: { from: from.toISOString(), to: to.toISOString() },
        tiers,
        unknownLeagues: [...unknownLeagues],
        counts: { all: all.length, kept: matches.length, byTier: countBy(all, (x) => x.tournament?.tier || 'none') },
        teams,
        matches,
    };
}

function countBy(xs, f) {
    const out = {};
    for (const x of xs) out[f(x)] = (out[f(x)] || 0) + 1;
    return out;
}

if (import.meta.url === `file://${process.argv[1]}`) {
    const data = await fetchPandaScore(runWindow(process.argv.slice(2)));
    await writeJson('matches-pandascore.json', data);
    console.log(`PandaScore: ${JSON.stringify(data.counts)}; unknown leagues: ${data.unknownLeagues.join(', ') || 'none'}; ${JSON.stringify(stats)}`);
}
