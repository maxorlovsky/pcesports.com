import { cached } from './cache.js';

// Liquipedia API terms (https://liquipedia.net/api-terms-of-use): at most 1 request per 2 s,
// action=parse at most 1 per 30 s, a custom User-Agent with contact information, gzip support.
// The contact is the project site and repo on purpose: no email (owner ruling on the card).
const API = 'https://liquipedia.net/counterstrike/api.php';
const USER_AGENT = 'PCesportsFeasibility/0.1 (https://www.pcesports.com/; https://github.com/maxorlovsky/pcesports.com)';
const GAP_MS = 2500;
const PARSE_GAP_MS = 31000;

let lastRequest = 0;
let lastParse = 0;
export const stats = { requests: 0, parses: 0, cacheHits: 0 };

const wait = (ms) => new Promise((r) => setTimeout(r, ms));

async function call(params, { parse = false } = {}) {
    const now = Date.now();
    const due = Math.max(lastRequest + GAP_MS, parse ? lastParse + PARSE_GAP_MS : 0);
    if (due > now) await wait(due - now);
    lastRequest = Date.now();
    if (parse) lastParse = lastRequest;
    stats.requests += 1;
    if (parse) stats.parses += 1;

    // fetch() negotiates gzip itself (Accept-Encoding: gzip, deflate, br).
    const res = await fetch(API, {
        method: 'POST',
        headers: { 'User-Agent': USER_AGENT, 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ format: 'json', formatversion: '2', ...params }),
    });
    if (!res.ok) throw new Error(`Liquipedia ${res.status} for ${JSON.stringify(params).slice(0, 200)}`);
    const json = await res.json();
    if (json.error) throw new Error(`Liquipedia error ${json.error.code}: ${json.error.info}`);
    return json;
}

function memo(key, produce) {
    let miss = false;
    return cached('liquipedia', key, () => { miss = true; return produce(); })
        .finally(() => { if (!miss) stats.cacheHits += 1; });
}

/** Raw wikitext of a page (a plain query, not a parse). */
export function wikitext(title) {
    return memo(`wikitext:${title}`, async () => {
        const json = await call({ action: 'query', prop: 'revisions', rvprop: 'content|timestamp', rvslots: 'main', titles: title });
        const page = json.query.pages[0];
        if (page.missing) throw new Error(`Liquipedia page missing: ${title}`);
        const rev = page.revisions[0];
        return { title: page.title, revisionTime: rev.timestamp, content: rev.slots.main.content };
    });
}

/** Rendered HTML of a wikitext snippet (action=parse, the rate-limited call). */
export function render(text) {
    return memo(`parse:${text}`, async () => {
        const json = await call({ action: 'parse', prop: 'text', contentmodel: 'wikitext', disablelimitreport: '1', text }, { parse: true });
        return json.parse.text;
    });
}
