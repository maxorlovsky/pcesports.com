// Team strength: an Elo rating replayed over a year of PandaScore's tier s and a results, of every league. The data
// job keeps the results in its state folder; the ratings are recomputed from them on every run and never stored.
import { TIER_ONE } from '../matches/index.ts';
import type { PandaScoreMatch } from '../matches/index.ts';

const DAY = 24 * 3600 * 1000;

/** How long a result counts towards the ratings. */
export const RESULTS_DAYS = 365;
const START_RATING = 1500;
const K = 32;

/** One finished series, as the ratings need it. */
export interface Result {
  /** PandaScore's match id. */
  id: number;
  /** When it began. */
  at: string;
  tournamentId: number;
  teams: [number, number];
  winnerId: number;
  bestOf: number | null;
}

/** What the replay knows just before a series: each team's rating and its wins and losses in the same tournament. */
export interface BeforeMatch {
  ratings: Map<number, number>;
  records: Map<number, [number, number]>;
}

/** The result of a finished, played tier s or a series between two teams, or null. */
export function toResult(raw: PandaScoreMatch): Result | null {
  if (raw.status !== 'finished' || raw.forfeit || raw.winner_id === null || !raw.begin_at) return null;
  const [a, b] = raw.opponents.map(({ opponent }) => opponent.id);
  if (!TIER_ONE.includes(raw.tournament.tier ?? '') || raw.opponents.length !== 2 || a === undefined || b === undefined) return null;

  return { id: raw.id, at: raw.begin_at, tournamentId: raw.tournament_id, teams: [a, b], winnerId: raw.winner_id, bestOf: raw.number_of_games };
}

/**
 * The stored results with what a read of PandaScore returned replacing theirs (a match read again that is no result
 * now, a forfeit or no longer tier s or a, leaves), minus those older than `RESULTS_DAYS`, oldest first.
 */
export function mergeResults(stored: Result[], read: PandaScoreMatch[], now: Date): Result[] {
  const byId = new Map(stored.map((result) => [result.id, result]));
  for (const raw of read) {
    const result = toResult(raw);
    if (result) byId.set(raw.id, result);
    else byId.delete(raw.id);
  }
  const oldest = now.getTime() - RESULTS_DAYS * DAY;

  return [...byId.values()].filter((result) => Date.parse(result.at) >= oldest).sort((a, b) => a.at.localeCompare(b.at) || a.id - b.id);
}

/** The chance that a team rated `a` beats one rated `b`. */
export function winChance(a: number, b: number): number {
  return 1 / (1 + 10 ** ((b - a) / 400));
}

/** Replays the results in order: what each series' two teams were rated, and their records, just before it. */
export function replay(results: Result[]): Map<number, BeforeMatch> {
  const ratings = new Map<number, number>();
  const records = new Map<string, [number, number]>();
  const before = new Map<number, BeforeMatch>();
  for (const result of results) {
    const [a, b] = result.teams;
    const ra = ratings.get(a) ?? START_RATING;
    const rb = ratings.get(b) ?? START_RATING;
    const record = (team: number) => records.get(`${result.tournamentId}:${team}`) ?? [0, 0];
    before.set(result.id, { ratings: new Map([[a, ra], [b, rb]]), records: new Map([[a, record(a)], [b, record(b)]]) });
    // A best-of-five says more than a best-of-three, a best-of-one less.
    const k = K * (result.bestOf !== null && result.bestOf >= 5 ? 1.25 : result.bestOf === 1 ? 0.75 : 1);
    const delta = k * ((result.winnerId === a ? 1 : 0) - winChance(ra, rb));
    ratings.set(a, ra + delta);
    ratings.set(b, rb - delta);
    for (const team of [a, b]) {
      const [wins, losses] = record(team);
      records.set(`${result.tournamentId}:${team}`, result.winnerId === team ? [wins + 1, losses] : [wins, losses + 1]);
    }
  }
  return before;
}
