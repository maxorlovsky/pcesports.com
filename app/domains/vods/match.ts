// Links a match to its full-match VOD: the experiment's matcher (docs/feasibility/cs2-data-and-vods.md section 2),
// with the rematch guard the report proposes.
import { eventOf, startOf } from '../matches/index.ts';
import type { Match, Team } from '../matches/index.ts';
import ALIASES from './aliases.json' with { type: 'json' };
import CHANNELS from './channels.json' with { type: 'json' };
import type { Upload } from './youtube.ts';

const HOUR = 3600 * 1000;
/** A VOD goes up after its match starts; organisers upload within hours (2 to 6 in the report), so 3 days is generous. */
export const PUBLISH_WINDOW_HOURS = 72;
/** Shorter is a clip or a Short, longer a whole broadcast day: neither is one match. */
const MIN_VOD_SECONDS = 20 * 60;
const MAX_VOD_SECONDS = 6 * 3600;
/** A match without a VOD this long after its start is listed in the job's report. */
export const MISSING_AFTER_HOURS = 12;

export interface Channel {
  id: string;
  name: string;
  handle: string;
  organizers: string[];
  role: string;
  uploads: string;
  linkedFrom?: string;
  featuredBy?: string;
  verified: string;
}

/** The whitelist (channels.json). */
export const CHANNELS_LIST: Channel[] = CHANNELS.channels;
/** The channels holding full-match VODs: the only ones the job polls and links from. */
export const VOD_CHANNELS: Channel[] = CHANNELS_LIST.filter((channel) => channel.role === 'vods');
const MANUAL_ALIASES: Record<string, string[]> = ALIASES.aliases;

/** A match's VOD as the data job stores it (`app/domains/vods/data/vods.json`): YouTube's ids and nothing else. */
export interface VodLink {
  matchId: number;
  videoId: string;
  channelId: string;
}

export const norm = (text: string): string => text.normalize('NFKD').replace(/[̀-ͯ]/g, '').toLowerCase()
  .replace(/&/g, ' and ').replace(/[^a-z0-9]+/g, ' ').trim();

const GENERIC = /^(team|esports|gaming|clan|cs|go|club)$/;
const core = (name: string) => name.split(' ').filter((word) => !GENERIC.test(word)).join(' ');

/** Every spelling a title may use for a team: its name, acronym and slug, each without generic words and spaces, and its aliases. */
export function spellings(team: Team, aliases: Record<string, string[]> = MANUAL_ALIASES): Set<string> {
  const out = new Set<string>();
  for (const raw of [team.name, team.acronym, team.slug, ...(aliases[team.slug] ?? [])]) {
    if (!raw) continue;
    const name = norm(raw);
    for (const form of [name, core(name)]) {
      if (!form) continue;
      out.add(form);
      out.add(form.replace(/ /g, ''));
    }
  }
  return out;
}

/** "[A] Team A vs. Team B - Event - Stage" -> its two sides and the rest, or null. */
export function splitTitle(title: string): { left: string; right: string; rest: string } | null {
  const bare = title.replace(/^\s*\[[^\]]*\]\s*/, '');
  const parts = /^(.+?)\s+vs\.?\s+(.+?)\s+[-|–]\s+(.*)$/i.exec(bare);
  return parts ? { left: norm(parts[1] ?? ''), right: norm(parts[2] ?? ''), rest: norm(parts[3] ?? '') } : null;
}

const names = (side: string, set: Set<string>) => [side, core(side)].some((form) => form && (set.has(form) || set.has(form.replace(/ /g, ''))));

/** The words of an event name a title must repeat: "IEM Beijing 2026" -> beijing. */
function eventWords(event: string): string[] {
  return norm(event).split(' ').filter((word) => word.length >= 4 && !/^\d+$/.test(word) && !['intel', 'extreme'].includes(word));
}

/** An ISO 8601 duration ("PT1H32M10S", "P0D") in seconds, or null when it isn't one. */
export function durationSeconds(duration: string): number | null {
  const parts = /^P(?:(\d+)D)?(?:T(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?)?$/.exec(duration);
  if (!parts || duration === 'P' || duration.endsWith('T')) return null;
  const part = (at: number) => Number(parts[at] ?? 0);
  return part(1) * 86400 + part(2) * 3600 + part(3) * 60 + part(4);
}

/** A finished upload of full-match length: not a stream still running, a clip or a broadcast day. */
export function isVodLength(upload: Upload): boolean {
  if (upload.liveBroadcastContent !== 'none') return false;
  const seconds = durationSeconds(upload.duration);
  return seconds !== null && seconds >= MIN_VOD_SECONDS && seconds <= MAX_VOD_SECONDS;
}

const pairKey = (match: Match) => match.teams.map((team) => team.id).sort((a, b) => a - b).join('-');

/**
 * Where a match's VOD may have been published: from its start to `PUBLISH_WINDOW_HOURS` later, and before the same two
 * teams meet again. Without that guard a rematch's VOD would be linked to the first meeting when the first VOD is missing.
 */
function publishWindow(match: Match, matches: Match[]): { from: number; to: number } | null {
  const start = startOf(match);
  if (!start) return null;
  const from = Date.parse(start);
  let to = from + PUBLISH_WINDOW_HOURS * HOUR;
  const pair = pairKey(match);
  for (const other of matches) {
    const otherStart = startOf(other);
    if (other.id === match.id || other.teams.length !== 2 || pairKey(other) !== pair || !otherStart) continue;
    const at = Date.parse(otherStart);
    if (at > from && at < to) to = at;
  }
  return { from, to };
}

/** The uploads that could be this match's VOD, earliest first. */
export function candidates(match: Match, matches: Match[], uploads: Upload[], channelIds: Set<string>): Upload[] {
  const [teamA, teamB] = match.teams;
  if (!teamA || !teamB || match.teams.length !== 2 || !['running', 'finished'].includes(match.status)) return [];
  const window = publishWindow(match, matches);
  if (!window) return [];
  const first = spellings(teamA);
  const second = spellings(teamB);
  const words = eventWords(eventOf(match));
  const organizerChannels = new Set(VOD_CHANNELS.filter((channel) => channel.organizers.includes(match.organizer) && channelIds.has(channel.id)).map((channel) => channel.id));

  return uploads
    .filter((upload) => {
      if (!organizerChannels.has(upload.channelId) || !isVodLength(upload)) return false;
      const at = Date.parse(upload.publishedAt);
      if (at < window.from || at >= window.to) return false;
      const title = splitTitle(upload.title);
      if (!title) return false;
      const teamsNamed = (names(title.left, first) && names(title.right, second)) || (names(title.left, second) && names(title.right, first));
      return teamsNamed && words.some((word) => title.rest.includes(word));
    })
    .sort((a, b) => a.publishedAt.localeCompare(b.publishedAt) || a.id.localeCompare(b.id));
}

export interface LinkResult {
  links: VodLink[];
  /** Matches that had more than one candidate: the earliest won, the rest are listed for a look. */
  ambiguous: { matchId: number; videoIds: string[] }[];
}

/**
 * Every match's VOD link. A stored link stays as long as its match does and its channel is whitelisted (the job keeps
 * titles only for a while, so an old link can't be re-derived); the refresh drops it when YouTube no longer returns the video. Each other match gets
 * the earliest candidate no other match holds. `channelIds` are the VOD channels the whitelist check let through.
 */
export function linkVods(matches: Match[], uploads: Upload[], stored: VodLink[], channelIds: Set<string>): LinkResult {
  const present = new Set(matches.map((match) => match.id));
  const whitelisted = new Set(VOD_CHANNELS.map((channel) => channel.id));
  // A link goes with its match, or with its channel leaving the whitelist; only its ids are carried over.
  const links = stored
    .filter((link) => present.has(link.matchId) && whitelisted.has(link.channelId))
    .map(({ matchId, videoId, channelId }) => ({ matchId, videoId, channelId }));
  const linkedMatches = new Set(links.map((link) => link.matchId));
  const used = new Set(links.map((link) => link.videoId));
  const ambiguous: LinkResult['ambiguous'] = [];
  const byStart = [...matches].sort((a, b) => (startOf(a) ?? '').localeCompare(startOf(b) ?? '') || a.id - b.id);
  for (const match of byStart) {
    if (linkedMatches.has(match.id)) continue;
    const found = candidates(match, matches, uploads, channelIds).filter((upload) => !used.has(upload.id));
    const pick = found[0];
    if (!pick) continue;
    if (found.length > 1) ambiguous.push({ matchId: match.id, videoIds: found.map((upload) => upload.id) });
    links.push({ matchId: match.id, videoId: pick.id, channelId: pick.channelId });
    used.add(pick.id);
  }
  links.sort((a, b) => a.matchId - b.matchId);

  return { links, ambiguous };
}

/** Finished matches with a VOD channel for their organiser but no VOD `MISSING_AFTER_HOURS` after their start. */
export function missingVods(matches: Match[], links: VodLink[], now: Date): Match[] {
  const linked = new Set(links.map((link) => link.matchId));
  const covered = new Set(VOD_CHANNELS.flatMap((channel) => channel.organizers));
  return matches.filter((match) => {
    const start = startOf(match);
    return match.status === 'finished' && !match.forfeit && !linked.has(match.id) && covered.has(match.organizer)
      && start !== null && Date.parse(start) <= now.getTime() - MISSING_AFTER_HOURS * HOUR;
  });
}

/** Full-match-length uploads of the VOD channels that no match links. */
export function unmatchedUploads(uploads: Upload[], links: VodLink[]): Upload[] {
  const linked = new Set(links.map((link) => link.videoId));
  const vodChannels = new Set(VOD_CHANNELS.map((channel) => channel.id));
  return uploads.filter((upload) => vodChannels.has(upload.channelId) && isVodLength(upload) && !linked.has(upload.id));
}
