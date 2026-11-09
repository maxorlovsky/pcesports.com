// The after-match rating: stakes (known before the match) plus closeness (after it), as one of three labels. Every
// number here was measured on past matches for how much a label gives away; README.md has the measurement.
import type { Match } from '../matches/index.ts';
import { winChance } from './elo.ts';
import type { BeforeMatch } from './elo.ts';
import type { Label } from './page.ts';
/** A rating at or above it is "Must watch"; at or above `WORTH_A_WATCH`, "Worth a watch"; below, "For fans". */
export const MUST_WATCH = 3.5;
export const WORTH_A_WATCH = 1.75;

/** The round in PandaScore's match name: "Semifinal 1" in "Semifinal 1: MGLZ vs NAVI", or "". */
export function roundOf(match: Pick<Match, 'name'>): string {
  const colon = match.name.indexOf(': ');
  return colon === -1 ? '' : match.name.slice(0, colon);
}

/**
 * What the match decides, from PandaScore's stage and round: a grand final 3; a semi-final or a playoff upper or lower
 * final 2; another playoff match 1, a third-place one 0.5; a group match that eliminates or advances a team 1 (by its
 * round's name, or a Swiss team on two wins or two losses); any other group match 0.
 */
export function stagePoints(match: Pick<Match, 'name' | 'stage' | 'teams'>, records?: BeforeMatch['records']): number {
  const round = roundOf(match).toLowerCase();
  if (/grand final/.test(round)) return 3;
  if (/playoff/i.test(match.stage)) {
    if (/semifinal|upper bracket final|lower bracket final/.test(round)) return 2;
    if (/3rd|third/.test(round)) return 0.5;
    return 1;
  }
  if (/elimination|decider|lower bracket|upper bracket final|winners'? match/.test(round)) return 1;
  if (/^round \d/.test(round) && records) {
    if (match.teams.some((team) => (records.get(team.id) ?? [0, 0]).includes(2))) return 1;
  }
  return 0;
}

/** The event: a Major 1, PandaScore tier s 0.5, tier a 0. */
export function eventPoints(match: Pick<Match, 'league' | 'serie' | 'tier'>): number {
  if (/major/i.test(`${match.league} ${match.serie}`)) return 1;
  return match.tier === 's' ? 0.5 : 0;
}

/** Where ratings sit among recent tier s and a matches: the median and the 90th percentile of both teams' average. */
export interface StrengthScale {
  median: number;
  top: number;
}

/** The two teams' strength: their average rating, -0.5 for the weakest, 0 at the median, 1 at the 90th percentile and above. */
export function strengthPoints(average: number, scale: StrengthScale): number {
  const spread = scale.top - scale.median;
  if (spread <= 0) return 0;
  return Math.max(-0.5, Math.min(1, (average - scale.median) / spread));
}

/**
 * What closeness reads. Only PandaScore's free plan for now: each map's length and the winner's chance before the match.
 * A per-map round score (a paid source) would come in here as one more field of `maps`.
 */
export interface ClosenessInput {
  bestOf: number | null;
  /** The played maps, map 1 first. */
  maps: { seconds: number | null }[];
  /** The winner's chance by rating before the match, or null when unrated. */
  winnerChance: number | null;
}

/** A map this long went to overtime in every case measured; one this long was mostly close. */
export const OVERTIME_MINUTES = 60;
export const CLOSE_MINUTES = 50;
/** A winner given less chance than this before the match is an upset. */
export const UPSET_CHANCE = 0.35;

/**
 * How close the match was, 0 to 1: the strongest of an upset (1), a map of `OVERTIME_MINUTES` or more (1) or of
 * `CLOSE_MINUTES` or more (0.5). It reads only the maps every series of its length plays (maps 1-2 of a best-of-three,
 * 1-3 of a best-of-five), and never whether the series went the distance: a label must not tell the map count.
 */
export function closeness({ bestOf, maps, winnerChance }: ClosenessInput): number {
  const always = maps.slice(0, Math.ceil((bestOf ?? 1) / 2));
  const longest = Math.max(0, ...always.map((map) => map.seconds ?? 0)) / 60;
  const upset = winnerChance !== null && winnerChance < UPSET_CHANCE ? 1 : 0;
  const length = longest >= OVERTIME_MINUTES ? 1 : longest >= CLOSE_MINUTES ? 0.5 : 0;
  return Math.max(upset, length);
}

export function labelOf(rating: number): Label {
  return rating >= MUST_WATCH ? 'Must watch' : rating >= WORTH_A_WATCH ? 'Worth a watch' : 'For fans';
}

export interface Rated {
  label: Label;
  /** Only the part known before the match: what orders matches that share a label. */
  stakes: number;
}

/**
 * A finished, played two-team match's label, or null for any other. `before` is what the replay knew just before it
 * (none: it counts as unrated, so strength 0 and no upset).
 */
export function rate(match: Match, before: BeforeMatch | undefined, scale: StrengthScale): Rated | null {
  if (match.status !== 'finished' || match.forfeit || match.teams.length !== 2 || match.winnerId === null) return null;
  const ratings = match.teams.map((team) => before?.ratings.get(team.id));
  const rated = ratings.every((rating) => rating !== undefined);
  const strength = rated ? strengthPoints(((ratings[0] as number) + (ratings[1] as number)) / 2, scale) : 0;
  const stakes = stagePoints(match, before?.records) + eventPoints(match) + strength;
  const winner = match.teams.findIndex((team) => team.id === match.winnerId);
  const winnerChance = rated && winner !== -1 ? winChance(ratings[winner] as number, ratings[1 - winner] as number) : null;
  const close = closeness({ bestOf: match.bestOf, maps: (match.mapSeconds ?? []).map((seconds) => ({ seconds })), winnerChance });

  return { label: labelOf(stakes + close), stakes };
}
