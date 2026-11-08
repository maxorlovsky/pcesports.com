// Step 3: link each match to a full-match VOD from the whitelisted channels, check every link
// against Liquipedia's own per-map VOD links, and measure what each VOD gives away.
// Reads out/matches.json + out/videos.json, writes out/results.json + out/results.md.
import { readFile, writeFile } from 'node:fs/promises';
import { join } from 'node:path';
import { ROOT, readJson, writeJson } from './lib/cache.js';

const HOUR = 3600 * 1000;
// A VOD is published after the match starts; organisers upload within hours, so 3 days is generous.
const PUBLISH_WINDOW_MS = 72 * HOUR;
// Below this a video is a clip or a Short; above it a whole broadcast day, not one match.
const MIN_VOD_SEC = 20 * 60;
const MAX_VOD_SEC = 6 * 3600;

export const norm = (s) => s.normalize('NFKD').replace(/[̀-ͯ]/g, '').toLowerCase()
    .replace(/&/g, ' and ').replace(/[^a-z0-9]+/g, ' ').trim();

const GENERIC = /^(team|esports|gaming|clan|cs|club)$/;

/** Every spelling a title may use for a team: Liquipedia name, short and bracket names, the
 * template id, each without generic words and without spaces, plus manual aliases. */
export function aliases(team, manual = []) {
    const out = new Set();
    for (const raw of [team.name, team.short, team.bracket, team.id, ...manual]) {
        if (!raw) continue;
        const n = norm(raw);
        const core = n.split(' ').filter((w) => !GENERIC.test(w)).join(' ');
        for (const v of [n, core]) if (v) { out.add(v); out.add(v.replace(/ /g, '')); }
    }
    return out;
}

/** "[A] Team A vs. Team B - Event - Stage" -> { left, right, rest } or null. */
export function splitTitle(title) {
    const t = title.replace(/^\s*\[[^\]]*\]\s*/, '');
    const m = /^(.+?)\s+vs\.?\s+(.+?)\s+[-|–]\s+(.*)$/i.exec(t);
    return m ? { left: norm(m[1]), right: norm(m[2]), rest: norm(m[3]) } : null;
}

const sideIs = (side, set) => {
    const core = side.split(' ').filter((w) => !GENERIC.test(w)).join(' ');
    return [side, core].some((v) => v && (set.has(v) || set.has(v.replace(/ /g, ''))));
};

/** Significant words of the event name, e.g. "Intel Extreme Masters Beijing 2026" -> beijing, ... */
function eventWords(name) {
    return norm(name).split(' ').filter((w) => w.length >= 4 && !/^\d+$/.test(w) && !['intel', 'extreme'].includes(w));
}

export function kind(video) {
    if (video.durationSec < MIN_VOD_SEC) return 'clip';
    if (video.durationSec > MAX_VOD_SEC || /full show/i.test(video.title)) return 'broadcast';
    return 'vod';
}

function candidatesFor(match, videos, teams, manualAliases) {
    const a1 = aliases(teams[match.team1], manualAliases[match.team1]);
    const a2 = aliases(teams[match.team2], manualAliases[match.team2]);
    const start = new Date(match.start).getTime();
    const words = eventWords(match.event);
    const out = [];
    for (const v of videos) {
        if (!v.organizers.includes(match.organizer)) continue;
        const t = new Date(v.publishedAt).getTime();
        if (t < start || t > start + PUBLISH_WINDOW_MS) continue;
        const parts = splitTitle(v.title);
        if (!parts) continue;
        const straight = sideIs(parts.left, a1) && sideIs(parts.right, a2);
        const swapped = sideIs(parts.left, a2) && sideIs(parts.right, a1);
        if (!straight && !swapped) continue;
        if (!words.some((w) => parts.rest.includes(w))) continue;
        out.push({ video: v, kind: kind(v), titleFirst: straight ? 1 : 2, delayH: (t - start) / HOUR });
    }
    return out;
}

function spoilers(match, pick) {
    const v = pick.video;
    const played = match.maps.filter((m) => m.played).length;
    const chapters = [...(v.description || '').matchAll(/^\s*\d{1,2}:\d{2}(?::\d{2})?\s+(.+)$/gm)].map((m) => m[1]);
    return {
        // A score like 2-1 or 13:7 anywhere in the title or description.
        scoreInText: /\b\d{1,2}\s*[-:]\s*\d{1,2}\b/.test(v.title.replace(/\b20\d\d\b/g, '')) || /\b[0-3]\s*[-:]\s*[0-3]\b(?!\d)/.test((v.description || '').replace(/\d{1,2}:\d{2}:\d{2}/g, '')),
        // Does the title list the winner first (would give the result away if it always did)?
        titleWinnerFirst: pick.titleFirst === match.winner,
        // Map chapters in the description show how many maps were played.
        chapterMaps: chapters.length,
        chaptersRevealMapCount: chapters.length > 0 && chapters.length === played,
        durationMin: Math.round(v.durationSec / 60),
        mapsPlayed: played,
        bestOf: match.bestOf,
        // Stage words that reveal a team got that far (a semi-final title names both winners of the quarter-finals).
        stageInTitle: /(semi|quarter|final|3rd|third|playoff|elimination|decider)/i.test(v.title),
    };
}

export async function matchAll() {
    const m = await readJson('matches.json');
    const v = await readJson('videos.json');
    const manualAliases = JSON.parse(await readFile(join(ROOT, 'aliases.json'), 'utf8')).aliases;
    const useManual = !process.argv.includes('--no-manual-aliases');
    const results = [];
    for (const match of m.matches) {
        const cands = candidatesFor(match, v.videos, m.teams, useManual ? manualAliases : {});
        const vods = cands.filter((c) => c.kind === 'vod').sort((a, b) => a.delayH - b.delayH);
        const pick = vods[0] || null;
        const reference = new Set(match.maps.filter((x) => x.vod).map((x) => x.vod.id));
        if (match.matchVod) reference.add(match.matchVod.id);
        let check = 'no-reference';
        if (pick && reference.size) check = reference.has(pick.video.id) ? 'agrees' : 'DISAGREES';
        else if (!pick && reference.size) check = 'MISSED';
        results.push({
            id: match.id,
            event: match.event,
            stage: match.stage,
            start: match.start,
            team1: m.teams[match.team1].name,
            team2: m.teams[match.team2].name,
            score: match.seriesScore.join('-'),
            bestOf: match.bestOf,
            vod: pick && { id: pick.video.id, title: pick.video.title, channel: pick.video.channelTitle, publishedAt: pick.video.publishedAt, delayH: Number(pick.delayH.toFixed(1)) },
            otherVodCandidates: vods.slice(1).map((c) => c.video.id),
            // Broadcast-day videos titled after this match: what a naive title match would have linked.
            broadcastLookalikes: cands.filter((c) => c.kind === 'broadcast').map((c) => ({ id: c.video.id, title: c.video.title, durationMin: Math.round(c.video.durationSec / 60) })),
            liquipediaVods: [...reference],
            check,
            spoilers: pick && spoilers(match, pick),
        });
    }
    const used = new Set(results.flatMap((r) => (r.vod ? [r.vod.id] : [])));
    const unusedVodUploads = v.videos
        .filter((x) => kind(x) === 'vod' && !used.has(x.id) && new Date(x.publishedAt) < new Date(m.window.to))
        .map((x) => ({ id: x.id, channel: x.channelTitle, title: x.title, publishedAt: x.publishedAt, durationMin: Math.round(x.durationSec / 60) }));
    const n = (f) => results.filter(f).length;
    const summary = {
        matches: results.length,
        matched: n((r) => r.vod),
        unmatched: n((r) => !r.vod),
        agreesWithLiquipedia: n((r) => r.check === 'agrees'),
        disagreesWithLiquipedia: n((r) => r.check === 'DISAGREES'),
        missedButLiquipediaHasOne: n((r) => r.check === 'MISSED'),
        noLiquipediaReference: n((r) => r.check === 'no-reference'),
        ambiguous: n((r) => r.otherVodCandidates.length),
        broadcastLookalikes: n((r) => r.broadcastLookalikes.length),
        manualAliases: useManual,
        unusedVodUploads: unusedVodUploads.length,
        medianUploadDelayH: median(results.filter((r) => r.vod).map((r) => r.vod.delayH)),
        maxUploadDelayH: Math.max(...results.filter((r) => r.vod).map((r) => r.vod.delayH)),
        spoilers: spoilerSummary(results),
        byEvent: Object.fromEntries([...new Set(results.map((r) => r.event))].map((e) => [e, {
            matches: n((r) => r.event === e), matched: n((r) => r.event === e && r.vod),
        }])),
    };
    return { window: m.window, quota: v.quota, summary, results, unusedVodUploads };
}

function spoilerSummary(results) {
    const sp = results.filter((r) => r.spoilers).map((r) => ({ ...r.spoilers, channel: r.vod.channel }));
    const bo3 = (maps) => sp.filter((x) => x.bestOf === 3 && x.mapsPlayed === maps).map((x) => x.durationMin);
    const range = (xs) => (xs.length ? [Math.min(...xs), Math.max(...xs)] : null);
    const two = bo3(2);
    const three = bo3(3);
    // One length cut-off that best separates 2-map from 3-map Bo3 VODs, and how often it is right.
    let best = { cutMin: null, correct: 0 };
    for (const cut of [...two, ...three]) {
        const correct = two.filter((d) => d <= cut).length + three.filter((d) => d > cut).length;
        if (correct > best.correct) best = { cutMin: cut, correct };
    }
    return {
        withVod: sp.length,
        scoreInTitleOrDescription: sp.filter((x) => x.scoreInText).length,
        titleListsWinnerFirst: sp.filter((x) => x.titleWinnerFirst).length,
        chaptersRevealMapCount: sp.filter((x) => x.chaptersRevealMapCount).length,
        stageWordInTitle: sp.filter((x) => x.stageInTitle).length,
        bo3TwoMapsMinutes: range(two),
        bo3ThreeMapsMinutes: range(three),
        lengthGuessesMapCount: `${best.correct}/${two.length + three.length} Bo3 VODs (cut-off ${best.cutMin} min)`,
    };
}

const median = (xs) => {
    if (!xs.length) return null;
    const s = [...xs].sort((a, b) => a - b);
    return s[Math.floor(s.length / 2)];
};

function markdown({ window: w, summary: s, results }) {
    const rows = results.map((r, i) => `| ${i + 1} | ${r.start.slice(0, 16).replace('T', ' ')} | ${r.event.replace(/Intel Extreme Masters/, 'IEM')} | ${r.stage} | ${r.team1} vs ${r.team2} | ${r.score} | ${r.vod ? `[${r.vod.id}](https://youtu.be/${r.vod.id}) ${r.vod.channel}, +${r.vod.delayH} h` : '-'} | ${r.check} |`);
    return [
        `Window ${w.from.slice(0, 10)} to ${w.to.slice(0, 10)} (exclusive), generated ${new Date().toISOString()}.`,
        '',
        '```json',
        JSON.stringify(s, null, 2),
        '```',
        '',
        '| # | Start (UTC) | Event | Stage | Match | Score | VOD | Liquipedia check |',
        '|---|---|---|---|---|---|---|---|',
        ...rows,
        '',
    ].join('\n');
}

export async function writeResults(data) {
    await writeJson('results.json', data);
    await writeFile(join(ROOT, 'out', 'results.md'), markdown(data));
}

if (import.meta.url === `file://${process.argv[1]}`) {
    const data = await matchAll();
    await writeResults(data);
    console.log(JSON.stringify(data.summary, null, 2));
}
