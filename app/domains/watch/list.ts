// The list the home page shows, as the data job writes it (data/watch.json): finished tier-1 matches with an official
// VOD, in order, each with what the page shows and nothing else. No score, winner, map count, rating or stakes goes
// in it: the build ships what it reads from here.
import { eventOf, startOf } from '../matches/index.ts';
import type { Match } from '../matches/index.ts';
import type { VodLink } from '../vods/index.ts';
import { replay } from './elo.ts';
import type { Result } from './elo.ts';
import { LABELS, LIST_DAYS } from './page.ts';
import type { Label } from './page.ts';
import { rate, roundOf } from './rating.ts';
import type { StrengthScale } from './rating.ts';

const DAY = 24 * 3600 * 1000;

/**
 * The strength scale is read from the tier s and a results of this many days before now: the window the rating was
 * measured on. Ratings drift up over a year as new teams join below, so a shorter or longer one moves the labels.
 */
export const SCALE_DAYS = 250;

export interface WatchEntry {
  matchId: number;
  /** When the match began. */
  start: string;
  /** "IEM Beijing 2026". */
  event: string;
  /** "Playoffs · Semifinal 1". */
  stage: string;
  teams: [string, string];
  label: Label;
  /** The official full-match VOD on YouTube. */
  videoId: string;
}

/** `data/watch.json`. */
export interface WatchData {
  source: string;
  entries: WatchEntry[];
}

/** The median and 90th percentile of both teams' average rating, over the results of the last `SCALE_DAYS`. */
export function strengthScale(results: Result[], before: ReturnType<typeof replay>, now: Date): StrengthScale {
  const from = now.getTime() - SCALE_DAYS * DAY;
  const averages = results
    .filter((result) => Date.parse(result.at) >= from)
    .map((result) => {
      const ratings = before.get(result.id)?.ratings;
      return ratings ? (ratings.get(result.teams[0])! + ratings.get(result.teams[1])!) / 2 : NaN;
    })
    .filter((average) => !Number.isNaN(average))
    .sort((a, b) => a - b);
  const at = (share: number) => averages[Math.floor(share * (averages.length - 1))] ?? 0;
  return { median: at(0.5), top: at(0.9) };
}

/**
 * Every finished, played match with a VOD, rated: by label, then by stakes (known before the match, so the order says
 * no more than the label does), then newest first.
 */
export function watchEntries(matches: Match[], links: VodLink[], results: Result[], now: Date): WatchEntry[] {
  const before = replay(results);
  const scale = strengthScale(results, before, now);
  const videos = new Map(links.map((link) => [link.matchId, link.videoId]));
  const rated = [];
  for (const match of matches) {
    const videoId = videos.get(match.id);
    const start = startOf(match);
    const rating = videoId && start ? rate(match, before.get(match.id), scale) : null;
    const [home, away] = match.teams;
    if (!rating || !videoId || !start || !home || !away) continue;
    const round = roundOf(match);
    rated.push({
      stakes: rating.stakes,
      entry: {
        matchId: match.id,
        start,
        event: eventOf(match),
        stage: round ? `${match.stage} · ${round}` : match.stage,
        teams: [home.name, away.name] as [string, string],
        label: rating.label,
        videoId,
      },
    });
  }
  return rated
    .sort((a, b) => LABELS.indexOf(a.entry.label) - LABELS.indexOf(b.entry.label) || b.stakes - a.stakes || b.entry.start.localeCompare(a.entry.start) || a.entry.matchId - b.entry.matchId)
    .map(({ entry }) => entry);
}

/** What one entry of the page shows: nothing more reaches the page's HTML or its payload. */
export interface ShownEntry {
  videoId: string;
  date: string;
  event: string;
  stage: string;
  teams: [string, string];
  label: Label;
}

/** The entries of the `LIST_DAYS` before `now`, in the data's order, cut to what the page shows. */
export function recentEntries(entries: WatchEntry[], now: Date): ShownEntry[] {
  const from = now.getTime() - LIST_DAYS * DAY;
  return entries
    .filter((entry) => Date.parse(entry.start) >= from && Date.parse(entry.start) <= now.getTime())
    .map(({ videoId, start, event, stage, teams, label }) => ({ videoId, date: start.slice(0, 10), event, stage, teams, label }));
}
