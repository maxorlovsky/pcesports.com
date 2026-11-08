// Tier-1 CS2 matches from PandaScore's free plan: the site's tier rule, the record the data job commits, and how a
// new fetch merges into the stored list.
import LEAGUES from './leagues.json' with { type: 'json' };

const HOUR = 3600 * 1000;
const DAY = 24 * HOUR;

/** PandaScore's own tiers kept: its "s" and "a" held exactly Liquipedia's S-Tier in the report's window. */
export const TIER_ONE = ['s', 'a'];

/** Matches stay in the data this long after they start. */
export const KEEP_DAYS = 30;

/** Each run reads the matches scheduled in this window around now, and keeps the stored ones outside it. */
export const FETCH_PAST_DAYS = 3;
export const FETCH_AHEAD_DAYS = 14;

/** A match as PandaScore's `/csgo/matches` returns it: only the fields read here. */
export interface PandaScoreMatch {
  id: number;
  name: string;
  status: string;
  scheduled_at: string | null;
  begin_at: string | null;
  end_at: string | null;
  number_of_games: number | null;
  winner_id: number | null;
  forfeit: boolean | null;
  modified_at: string;
  league: { name: string };
  serie: { full_name: string };
  tournament: { name: string; tier: string | null };
  opponents: { opponent: { id: number; slug: string; name: string; acronym: string | null } }[];
  results: { team_id: number; score: number }[];
}

export interface Team {
  id: number;
  slug: string;
  name: string;
  acronym: string | null;
}

/** One match as the data job stores it (`app/domains/matches/data/matches.json` on the `data` branch). */
export interface Match {
  /** PandaScore's match id. */
  id: number;
  /** PandaScore's match name, e.g. "Semifinal 1: MGLZ vs NAVI". */
  name: string;
  league: string;
  serie: string;
  /** PandaScore's tournament, which is the stage: "Playoffs", "Group Stage". */
  stage: string;
  organizer: string;
  tier: string;
  /** PandaScore's status: not_started, running, finished, canceled or postponed. */
  status: string;
  scheduledAt: string | null;
  beginAt: string | null;
  endAt: string | null;
  bestOf: number | null;
  /** Zero to two teams: an upcoming match can wait for its teams. */
  teams: Team[];
  winnerId: number | null;
  /** Maps won, in the order of `teams`. */
  score: number[];
  forfeit: boolean;
  modifiedAt: string;
}

const ORGANIZERS: Record<string, string> = LEAGUES.leagues;

/** The organiser of a PandaScore league, or undefined when the league is not one the site lists. */
export function organizerOf(league: string): string | undefined {
  return Object.hasOwn(ORGANIZERS, league) ? ORGANIZERS[league] : undefined;
}

/** The site's tier rule: PandaScore tier "s" or "a", from a league whose organiser the site knows. */
export function isTierOne(raw: PandaScoreMatch): boolean {
  return TIER_ONE.includes(raw.tournament.tier ?? '') && organizerOf(raw.league.name) !== undefined;
}

/** The stored record of a tier-1 match, or null for any other match. */
export function toMatch(raw: PandaScoreMatch): Match | null {
  const organizer = organizerOf(raw.league.name);
  if (!organizer || !TIER_ONE.includes(raw.tournament.tier ?? '')) return null;
  const teams = raw.opponents.map(({ opponent }) => ({ id: opponent.id, slug: opponent.slug, name: opponent.name, acronym: opponent.acronym }));

  return {
    id: raw.id,
    name: raw.name,
    league: raw.league.name,
    serie: raw.serie.full_name,
    stage: raw.tournament.name,
    organizer,
    tier: raw.tournament.tier as string,
    status: raw.status,
    scheduledAt: raw.scheduled_at,
    beginAt: raw.begin_at,
    endAt: raw.end_at,
    bestOf: raw.number_of_games,
    teams,
    winnerId: raw.winner_id,
    score: teams.map((team) => raw.results.find((result) => result.team_id === team.id)?.score ?? 0),
    forfeit: Boolean(raw.forfeit),
    modifiedAt: raw.modified_at,
  };
}

/** When the match started, or is scheduled to: PandaScore's actual start once there is one. */
export function startOf(match: Match): string | null {
  return match.beginAt ?? match.scheduledAt;
}

/** The event a match belongs to, as organisers name it in titles: "IEM Beijing 2026". */
export function eventOf(match: Match): string {
  return `${match.league} ${match.serie}`;
}

export interface FetchWindow {
  from: Date;
  to: Date;
}

/** The window one run reads: from `FETCH_PAST_DAYS` before now (or `since`, when earlier) to `FETCH_AHEAD_DAYS` ahead. */
export function fetchWindow(now: Date, since?: Date): FetchWindow {
  const from = new Date(now.getTime() - FETCH_PAST_DAYS * DAY);

  return { from: since && since < from ? since : from, to: new Date(now.getTime() + FETCH_AHEAD_DAYS * DAY) };
}

/**
 * The stored list after a fetch: every fetched match, plus the stored ones the fetch window did not cover, minus those
 * that started more than `KEEP_DAYS` ago. A stored match inside the window that the fetch no longer returns is dropped:
 * PandaScore removed it, moved it out of the window or changed its tier.
 */
export function mergeMatches(stored: Match[], fetched: Match[], window: FetchWindow, now: Date): Match[] {
  const byId = new Map(fetched.map((match) => [match.id, match]));
  const inWindow = (match: Match) => {
    const at = match.scheduledAt ? Date.parse(match.scheduledAt) : NaN;
    return at >= window.from.getTime() && at <= window.to.getTime();
  };
  for (const match of stored) if (!byId.has(match.id) && !inWindow(match)) byId.set(match.id, match);
  const oldest = now.getTime() - KEEP_DAYS * DAY;

  return [...byId.values()]
    .filter((match) => {
      const start = startOf(match);
      return start !== null && Date.parse(start) >= oldest;
    })
    .sort((a, b) => (startOf(a) ?? '').localeCompare(startOf(b) ?? '') || a.id - b.id);
}
