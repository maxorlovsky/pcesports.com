// Step 1: tier-1 (Liquipedia S-Tier) CS2 matches played in the window, from Liquipedia's
// MediaWiki API. Writes out/matches.json.
import { window as runWindow } from './lib/window.js';
import { render, stats, wikitext } from './lib/liquipedia.js';
import { findTemplates, headingAt, params } from './lib/wikitext.js';
import { writeJson } from './lib/cache.js';

const MONTHS = { jan: 0, feb: 1, mar: 2, apr: 3, may: 4, jun: 5, jul: 6, aug: 7, sep: 8, oct: 9, nov: 10, dec: 11 };
const strip = (html) => html.replace(/<[^>]+>/g, ' ').replace(/&#160;|&nbsp;/g, ' ').replace(/&amp;/g, '&')
    .replace(/&#(\d+);/g, (_, n) => String.fromCharCode(Number(n))).replace(/\s+/g, ' ').trim();

/** "Nov 02–08, 2026" | "Nov 24 – Dec 13, 2026" | "Dec 28, 2026 – Jan 03, 2027" -> [start, endExclusive] UTC. */
function parseRange(text) {
    const t = text.replace(/[–—]/g, '-');
    let m = /^(\w{3}) (\d{1,2})\s*-\s*(\d{1,2}), (\d{4})$/.exec(t);
    if (m) return [Date.UTC(+m[4], MONTHS[m[1].toLowerCase()], +m[2]), Date.UTC(+m[4], MONTHS[m[1].toLowerCase()], +m[3] + 1)];
    m = /^(\w{3}) (\d{1,2})\s*-\s*(\w{3}) (\d{1,2}), (\d{4})$/.exec(t);
    if (m) return [Date.UTC(+m[5], MONTHS[m[1].toLowerCase()], +m[2]), Date.UTC(+m[5], MONTHS[m[3].toLowerCase()], +m[4] + 1)];
    m = /^(\w{3}) (\d{1,2}), (\d{4})\s*-\s*(\w{3}) (\d{1,2}), (\d{4})$/.exec(t);
    if (m) return [Date.UTC(+m[3], MONTHS[m[1].toLowerCase()], +m[2]), Date.UTC(+m[6], MONTHS[m[4].toLowerCase()], +m[5] + 1)];
    m = /^(\w{3}) (\d{1,2}), (\d{4})$/.exec(t);
    if (m) return [Date.UTC(+m[3], MONTHS[m[1].toLowerCase()], +m[2]), Date.UTC(+m[3], MONTHS[m[1].toLowerCase()], +m[2] + 1)];
    return null;
}

async function tier1Events(from, to) {
    const events = [];
    for (let year = from.getUTCFullYear(); year <= new Date(to - 1).getUTCFullYear(); year++) {
        const html = await render(`{{TournamentsList|tier=1|tiertype=none|year=${year}}}`);
        for (const row of html.split('table2&#95;&#95;row--body').slice(1)) {
            const link = /class="column&#95;&#95;tournament"[^>]*><a href="\/counterstrike\/([^"]+)"[^>]*>([^<]+)<\/a><\/td><td[^>]*>([^<]+)<\/td>/.exec(row);
            if (!link) continue;
            const [, page, name, dateText] = link;
            const range = parseRange(dateText.trim());
            const cancelled = /Cancelled/.test(strip(row));
            if (!range || cancelled) continue;
            if (range[0] < to.getTime() && range[1] > from.getTime()) events.push({ page: decodeURIComponent(page).replace(/_/g, ' '), name: strip(name), dateText });
        }
    }
    return events;
}

function infobox(content) {
    const box = findTemplates(content, 'Infobox league')[0];
    return box ? params(box.body) : {};
}

const num = (v) => (v === undefined || v === '' ? 0 : Number(v));

function mapScore(p) {
    if (p.score1 !== undefined || p.score2 !== undefined) return [num(p.score1), num(p.score2)];
    let s1 = num(p.t1t) + num(p.t1ct);
    let s2 = num(p.t2t) + num(p.t2ct);
    for (const [k, v] of Object.entries(p)) {
        if (/^o\d+t1(t|ct)$/.test(k)) s1 += num(v);
        if (/^o\d+t2(t|ct)$/.test(k)) s2 += num(v);
    }
    return [s1, s2];
}

const youtubeId = (url) => {
    const m = /(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|live\/))([\w-]{11})(?:.*?[?&]t=(\d+))?/.exec(url || '');
    return m ? { id: m[1], t: m[2] ? Number(m[2]) : 0 } : null;
};

function parseDate(value, zones) {
    // "October 24, 2026 - 10:00 {{Abbr/EEST}}"
    const m = /^(\w+) (\d{1,2}), (\d{4})\s*-\s*(\d{1,2}):(\d{2})\s*(?:\{\{Abbr\/(\w+)\}\})?/.exec(value || '');
    if (!m) return null;
    const month = MONTHS[m[1].slice(0, 3).toLowerCase()];
    const offset = m[6] ? zones[m[6]] : 0;
    if (offset === undefined) throw new Error(`Unknown timezone ${m[6]}`);
    return new Date(Date.UTC(+m[3], month, +m[2], +m[4], +m[5]) - offset * 60000);
}

function opponentId(value) {
    const t = findTemplates(value || '', 'TeamOpponent')[0];
    return t ? String(params(t.body)[1] || '').toLowerCase() : null;
}

function matchesOf(event, content, zones) {
    const out = [];
    for (const [i, tpl] of findTemplates(content, 'Match').entries()) {
        const p = params(tpl.body);
        const team1 = opponentId(p.opponent1);
        const team2 = opponentId(p.opponent2);
        if (!team1 || !team2 || team1 === 'tbd' || team2 === 'tbd') continue;
        const maps = [];
        for (let n = 1; p[`map${n}`] !== undefined; n++) {
            const mt = findTemplates(p[`map${n}`], 'Map')[0];
            if (!mt) continue;
            const mp = params(mt.body);
            if (mp.finished !== 'true') { maps.push({ n, map: mp.map || null, played: false }); continue; }
            const [score1, score2] = mapScore(mp);
            maps.push({ n, map: mp.map, played: true, score1, score2, winner: score1 > score2 ? 1 : 2, vod: youtubeId(mp.vod) });
        }
        const played = maps.filter((m) => m.played);
        const wins1 = played.filter((m) => m.winner === 1).length;
        const wins2 = played.length - wins1;
        out.push({
            id: `${event.page}#${i + 1}`,
            event: event.name,
            eventPage: event.page,
            organizer: event.organizer,
            stage: headingAt(content, tpl.start).filter((h) => h !== 'Results').join(' / '),
            team1,
            team2,
            start: parseDate(p.date, zones)?.toISOString() || null,
            finished: p.finished === 'true',
            bestOf: maps.length,
            maps,
            seriesScore: [wins1, wins2],
            winner: played.length ? (wins1 > wins2 ? 1 : 2) : null,
            matchVod: youtubeId(p.vod),
            streamRef: p.youtube || null,
            hltv: p.hltv || null,
        });
    }
    return out;
}

async function lookups(teamIds, zones) {
    // One action=parse call resolves every team template and timezone abbreviation at once.
    const lines = [...teamIds].map((id) => `<div data-team="${id}">{{TeamShort|${id}}};;{{TeamBracket|${id}}};;{{Team|${id}}}</div>`);
    lines.push(...[...zones].map((z) => `<div data-zone="${z}">{{Abbr/${z}}}</div>`));
    const html = await render(lines.join('\n'));
    const teams = {};
    for (const m of html.matchAll(/data-team="([^"]+)">([\s\S]*?)<\/div>\s*(?=<div data-|$)/g)) {
        const [short, bracket, full] = strip(m[2]).split(';;').map((s) => s.trim());
        teams[m[1]] = { id: m[1], name: full, short, bracket };
    }
    const offsets = {};
    for (const m of html.matchAll(/data-zone="([^"]+)">[\s\S]*?title="([^"]+)"/g)) {
        const o = /UTC([+-−])(\d{1,2})(?::(\d{2}))?/.exec(m[2]);
        offsets[m[1]] = o ? (o[1] === '+' ? 1 : -1) * (Number(o[2]) * 60 + Number(o[3] || 0)) : 0;
        offsets[`${m[1]}:title`] = m[2];
    }
    return { teams, offsets };
}

export async function fetchMatches({ from, to }) {
    const events = await tier1Events(from, to);
    const pages = [];
    for (const e of events) {
        const page = await wikitext(e.page);
        const box = infobox(page.content);
        e.organizer = box.organizer || null;
        e.tier = box.liquipediatier || null;
        e.youtube = box.youtube || null;
        e.web = box.web || null;
        e.revisionTime = page.revisionTime;
        pages.push({ e, content: page.content });
    }

    const zoneNames = new Set();
    const teamIds = new Set();
    for (const { content } of pages) {
        for (const m of content.matchAll(/\{\{Abbr\/(\w+)\}\}/g)) zoneNames.add(m[1]);
        for (const m of content.matchAll(/\{\{TeamOpponent\|([^|}]+)/g)) teamIds.add(m[1].trim().toLowerCase());
    }
    teamIds.delete('tbd');
    const { teams, offsets } = await lookups(teamIds, zoneNames);

    const all = pages.flatMap(({ e, content }) => matchesOf(e, content, offsets));
    const inWindow = all.filter((m) => m.start && new Date(m.start) >= from && new Date(m.start) < to);
    const played = inWindow.filter((m) => m.maps.some((x) => x.played));
    return {
        window: { from: from.toISOString(), to: to.toISOString() },
        events,
        timezones: Object.fromEntries(Object.entries(offsets).filter(([k]) => k.endsWith(':title'))),
        teams,
        counts: { parsed: all.length, inWindow: inWindow.length, played: played.length },
        matches: played,
    };
}

if (import.meta.url === `file://${process.argv[1]}`) {
    const w = runWindow(process.argv.slice(2));
    const data = await fetchMatches(w);
    await writeJson('matches.json', data);
    console.log(`Events: ${data.events.map((e) => `${e.name} [${e.organizer}, ${e.tier}]`).join('; ')}`);
    console.log(`Matches: ${JSON.stringify(data.counts)}  Liquipedia: ${JSON.stringify(stats)}`);
}
