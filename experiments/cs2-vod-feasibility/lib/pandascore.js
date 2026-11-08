import { cached } from './cache.js';

// PandaScore REST API, free "Fixtures" plan: 1,000 requests per hour, token on every request.
// Only the list endpoint /csgo/matches/past is used.
const API = 'https://api.pandascore.co';
const PER_PAGE = 100;
export const stats = { requests: 0, cacheHits: 0 };

function token() {
    const t = process.env.PANDASCORE_TOKEN;
    if (!t) throw new Error('PANDASCORE_TOKEN is not set (see README: node --env-file=../../.env.local ...)');
    return t;
}

async function page(path, query, n) {
    const qs = new URLSearchParams({ ...query, per_page: String(PER_PAGE), page: String(n) }).toString();
    let miss = false;
    const value = await cached('pandascore', `${path}?${qs}`, async () => {
        miss = true;
        stats.requests += 1;
        const res = await fetch(`${API}${path}?${qs}`, { headers: { Authorization: `Bearer ${token()}` } });
        const json = await res.json();
        if (!res.ok) throw new Error(`PandaScore ${path} ${res.status}: ${JSON.stringify(json).slice(0, 200)}`);
        return json;
    });
    if (!miss) stats.cacheHits += 1;
    return value;
}

/** Every finished-or-not CS match that began in [from, to), oldest first. */
export async function pastMatches(from, to) {
    const query = { 'range[begin_at]': `${from.toISOString()},${new Date(to - 1000).toISOString()}`, sort: 'begin_at,id' };
    const out = [];
    for (let n = 1; ; n++) {
        const items = await page('/csgo/matches/past', query, n);
        out.push(...items);
        if (items.length < PER_PAGE) return out;
    }
}
