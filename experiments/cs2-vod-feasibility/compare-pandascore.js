// Step 4 (optional): compare PandaScore's match list with Liquipedia's for the same window.
// Reads out/matches.json, out/results.json, out/matches-pandascore.json, out/results-pandascore.json;
// writes out/compare-pandascore.json.
import { readJson, writeJson } from './lib/cache.js';
import { aliases } from './match.js';

const HOUR = 3600 * 1000;
const PAIR_WINDOW_MS = 12 * HOUR;

const sameTeam = (a, b) => [...aliases(a)].some((x) => aliases(b).has(x));

const median = (xs) => {
    if (!xs.length) return null;
    const s = [...xs].sort((a, b) => a - b);
    return s[Math.floor(s.length / 2)];
};

export async function compare() {
    const lp = await readJson('matches.json');
    const ps = await readJson('matches-pandascore.json');
    const lpRes = Object.fromEntries((await readJson('results.json')).results.map((r) => [r.id, r]));
    const psRes = Object.fromEntries((await readJson('results-pandascore.json')).results.map((r) => [r.id, r]));

    const pairs = [];
    const usedPs = new Set();
    for (const a of lp.matches) {
        const t1 = lp.teams[a.team1];
        const t2 = lp.teams[a.team2];
        const b = ps.matches.find((x) => !usedPs.has(x.id)
            && Math.abs(new Date(x.start) - new Date(a.start)) < PAIR_WINDOW_MS
            && ((sameTeam(t1, ps.teams[x.team1]) && sameTeam(t2, ps.teams[x.team2]))
                || (sameTeam(t1, ps.teams[x.team2]) && sameTeam(t2, ps.teams[x.team1]))));
        if (!b) { pairs.push({ liquipedia: a.id, pandascore: null }); continue; }
        usedPs.add(b.id);
        const flipped = !sameTeam(t1, ps.teams[b.team1]);
        const psScore = flipped ? [...b.seriesScore].reverse() : b.seriesScore;
        const psWinner = b.winner && (flipped ? 3 - b.winner : b.winner);
        pairs.push({
            liquipedia: a.id,
            pandascore: b.id,
            match: `${t1.name} vs ${t2.name}`,
            event: a.event,
            psTier: b.tier,
            psStage: b.stage,
            lpStage: a.stage,
            // Liquipedia's time is the scheduled start; PandaScore's begin_at is when it actually began.
            beginMinusLiquipediaMin: Math.round((new Date(b.start) - new Date(a.start)) / 60000),
            scheduledMinusLiquipediaMin: b.scheduledAt ? Math.round((new Date(b.scheduledAt) - new Date(a.start)) / 60000) : null,
            winnerAgrees: psWinner === a.winner,
            scoreAgrees: psScore.join('-') === a.seriesScore.join('-'),
            mapsPlayedAgree: b.maps.filter((x) => x.played).length === a.maps.filter((x) => x.played).length,
            bestOfAgrees: b.bestOf === a.bestOf,
            // Upper bound on how late the final result landed: the record's last change after the end.
            lastEditAfterEndMin: b.endAt ? Math.round((new Date(b.modifiedAt) - new Date(b.endAt)) / 60000) : null,
            sameVod: (lpRes[a.id]?.vod?.id ?? null) === (psRes[b.id]?.vod?.id ?? null),
            lpVod: lpRes[a.id]?.vod?.id ?? null,
            psVod: psRes[b.id]?.vod?.id ?? null,
        });
    }
    const found = pairs.filter((p) => p.pandascore);
    const n = (f) => found.filter(f).length;
    const begin = found.map((p) => p.beginMinusLiquipediaMin);
    const summary = {
        liquipediaMatches: lp.matches.length,
        pandascoreMatches: ps.matches.length,
        paired: found.length,
        onlyLiquipedia: pairs.length - found.length,
        onlyPandaScore: ps.matches.length - found.length,
        winnerAgrees: n((p) => p.winnerAgrees),
        scoreAgrees: n((p) => p.scoreAgrees),
        mapsPlayedAgree: n((p) => p.mapsPlayedAgree),
        bestOfAgrees: n((p) => p.bestOfAgrees),
        sameVodLinked: n((p) => p.sameVod),
        pandascoreTiersByEvent: Object.fromEntries([...new Set(found.map((p) => p.event))].map((e) => [e, [...new Set(found.filter((p) => p.event === e).map((p) => p.psTier))]])),
        beginMinusLiquipediaMin: { median: median(begin), min: Math.min(...begin), max: Math.max(...begin) },
        lastEditAfterEndMin: (() => {
            const xs = found.map((p) => p.lastEditAfterEndMin).filter((x) => x !== null);
            return { median: median(xs), max: Math.max(...xs), within60: xs.filter((x) => x <= 60).length, of: xs.length };
        })(),
        pandascoreAllMatchesByTier: ps.counts.byTier,
    };
    return { summary, pairs };
}

if (import.meta.url === `file://${process.argv[1]}`) {
    const data = await compare();
    await writeJson('compare-pandascore.json', data);
    console.log(JSON.stringify(data.summary, null, 2));
}
