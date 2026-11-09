// PandaScore's REST API on the free "Fixtures" plan: 1,000 requests per hour, the token on every request, server side
// only. `/csgo/matches` lists past, running and upcoming matches alike; `/csgo/tournaments` and `/csgo/matches/past`
// serve the year of results the watch domain's team strength is built from.
import type { PandaScoreMatch } from './matches.ts';

export const PANDASCORE_API = 'https://api.pandascore.co';
const PER_PAGE = 100;
/** A runaway guard: the window holds about 400 matches of every tier, 5 pages; a year of tier s and a, about 11. */
const MAX_PAGES = 30;
/** Tournament ids per `/csgo/matches/past` query, so the URL stays short. */
const IDS_PER_QUERY = 50;
const DAY = 24 * 3600 * 1000;
/** A tournament whose matches reach into a window began at most this long before it. */
const TOURNAMENT_LEAD_DAYS = 60;

export interface PandaScoreOptions {
  token: string;
  from: Date;
  to: Date;
  fetch?: typeof fetch;
  api?: string;
}

/** Every item of a list endpoint, page by page, and the number of requests that took. */
async function list<T>({ token, fetch = globalThis.fetch, api = PANDASCORE_API }: PandaScoreOptions, path: string, params: Record<string, string>): Promise<{ items: T[]; requests: number }> {
  const items: T[] = [];
  for (let page = 1; page <= MAX_PAGES; page++) {
    const query = new URLSearchParams({ ...params, per_page: String(PER_PAGE), page: String(page) });
    const response = await fetch(`${api}${path}?${query}`, { headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' } });
    const body = await response.json().catch(() => null);
    if (!response.ok || !Array.isArray(body)) {
      const message = body && typeof body === 'object' && 'error' in body ? String(body.error) : `HTTP ${response.status}`;
      throw new Error(`PandaScore ${path} page ${page}: ${message}`);
    }
    items.push(...(body as T[]));
    if (body.length < PER_PAGE) return { items, requests: page };
  }
  throw new Error(`PandaScore ${path}: more than ${MAX_PAGES} pages for one query`);
}

const range = (from: Date, to: Date) => `${from.toISOString()},${to.toISOString()}`;

/** Every CS match scheduled in [from, to], of any tier, and the number of requests that took. */
export async function fetchPandaScoreMatches(options: PandaScoreOptions): Promise<{ matches: PandaScoreMatch[]; requests: number }> {
  const { items, requests } = await list<PandaScoreMatch>(options, '/csgo/matches', { 'range[scheduled_at]': range(options.from, options.to), sort: 'scheduled_at,id' });
  return { matches: items, requests };
}

/**
 * Every past CS match that began in [from, to] in a tournament of one of `tiers`, of any league: the free plan can't
 * filter matches by tier, so it lists the tournaments first, then their matches.
 */
export async function fetchPandaScoreTierResults(options: PandaScoreOptions & { tiers: string[] }): Promise<{ matches: PandaScoreMatch[]; requests: number }> {
  const tournaments = await list<{ id: number }>(options, '/csgo/tournaments', {
    'filter[tier]': options.tiers.join(','),
    'range[begin_at]': range(new Date(options.from.getTime() - TOURNAMENT_LEAD_DAYS * DAY), options.to),
    sort: 'begin_at,id',
  });
  let requests = tournaments.requests;
  const matches: PandaScoreMatch[] = [];
  const ids = tournaments.items.map((tournament) => tournament.id);
  for (let i = 0; i < ids.length; i += IDS_PER_QUERY) {
    const page = await list<PandaScoreMatch>(options, '/csgo/matches/past', {
      'filter[tournament_id]': ids.slice(i, i + IDS_PER_QUERY).join(','),
      'range[begin_at]': range(options.from, options.to),
      sort: 'begin_at,id',
    });
    matches.push(...page.items);
    requests += page.requests;
  }
  return { matches, requests };
}
