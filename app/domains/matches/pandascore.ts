// PandaScore's REST API on the free "Fixtures" plan: 1,000 requests per hour, the token on every request, server side
// only. One endpoint, `/csgo/matches`, which lists past, running and upcoming matches alike.
import type { PandaScoreMatch } from './matches.ts';

export const PANDASCORE_API = 'https://api.pandascore.co';
const PER_PAGE = 100;
/** A runaway guard: the window holds about 400 matches of every tier, 5 pages. */
const MAX_PAGES = 30;

export interface PandaScoreOptions {
  token: string;
  from: Date;
  to: Date;
  fetch?: typeof fetch;
  api?: string;
}

/** Every CS match scheduled in [from, to], of any tier, and the number of requests that took. */
export async function fetchPandaScoreMatches({ token, from, to, fetch = globalThis.fetch, api = PANDASCORE_API }: PandaScoreOptions): Promise<{ matches: PandaScoreMatch[]; requests: number }> {
  const matches: PandaScoreMatch[] = [];
  for (let page = 1; page <= MAX_PAGES; page++) {
    const query = new URLSearchParams({
      'range[scheduled_at]': `${from.toISOString()},${to.toISOString()}`,
      sort: 'scheduled_at,id',
      per_page: String(PER_PAGE),
      page: String(page),
    });
    const response = await fetch(`${api}/csgo/matches?${query}`, { headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' } });
    const body = await response.json().catch(() => null);
    if (!response.ok || !Array.isArray(body)) {
      const message = body && typeof body === 'object' && 'error' in body ? String(body.error) : `HTTP ${response.status}`;
      throw new Error(`PandaScore /csgo/matches page ${page}: ${message}`);
    }
    matches.push(...(body as PandaScoreMatch[]));
    if (body.length < PER_PAGE) return { matches, requests: page };
  }
  throw new Error(`PandaScore /csgo/matches: more than ${MAX_PAGES} pages for one window`);
}
