// YouTube's storage rule (Developer Policies III.E.4): API data kept without a user's authorisation is deleted or
// refreshed within 30 calendar days. The committed links (YouTube's ids) are re-read with videos.list every
// `REFRESH_DAYS`; a video YouTube no longer returns is dropped. Titles and the rest stay on the job's machine only,
// and go after `KEEP_UPLOADS_DAYS`.
import type { VodLink } from './match.ts';
import type { Upload } from './youtube.ts';

const DAY = 24 * 3600 * 1000;
/** A stored link is re-read this often, well inside the 30 days, so a failed day or two still keeps the rule. */
export const REFRESH_DAYS = 7;
/** YouTube's limit: a link not refreshed for this long is dropped, whatever else happens. */
export const MAX_AGE_DAYS = 30;
/** How long the job keeps an upload's title and details after it last read them: enough to match and to fix an alias. */
export const KEEP_UPLOADS_DAYS = 14;

/** When each stored video was last read from YouTube (the job's local state). */
export type RefreshTimes = Record<string, string>;

/** The links to re-read now: never read in this state, or read `REFRESH_DAYS` ago or more. */
export function dueForRefresh(links: VodLink[], refreshed: RefreshTimes, now: Date): string[] {
  return links
    .filter((link) => {
      const at = refreshed[link.videoId];
      return !at || Date.parse(at) <= now.getTime() - REFRESH_DAYS * DAY;
    })
    .map((link) => link.videoId);
}

export interface RefreshResult {
  links: VodLink[];
  refreshed: RefreshTimes;
  /** Links dropped, with why. */
  dropped: { link: VodLink; reason: 'deleted' | 'moved' | 'expired' }[];
}

/**
 * The links after a refresh. `checked` are the ids asked for (empty when the refresh call failed) and `found` what
 * YouTube returned: a checked video it no longer returns (deleted, made private) or that changed channel is dropped,
 * and so is any link last read `MAX_AGE_DAYS` ago or more. Refresh times of videos no link holds are forgotten.
 */
export function applyRefresh(links: VodLink[], refreshed: RefreshTimes, checked: string[], found: Upload[], now: Date): RefreshResult {
  const asked = new Set(checked);
  const returned = new Map(found.map((upload) => [upload.id, upload]));
  const times: RefreshTimes = {};
  const kept: VodLink[] = [];
  const dropped: RefreshResult['dropped'] = [];
  for (const link of links) {
    if (asked.has(link.videoId)) {
      const video = returned.get(link.videoId);
      if (!video) {
        dropped.push({ link, reason: 'deleted' });
        continue;
      }
      if (video.channelId !== link.channelId) {
        dropped.push({ link, reason: 'moved' });
        continue;
      }
      times[link.videoId] = now.toISOString();
      kept.push(link);
      continue;
    }
    const at = refreshed[link.videoId];
    if (at && Date.parse(at) <= now.getTime() - MAX_AGE_DAYS * DAY) {
      dropped.push({ link, reason: 'expired' });
      continue;
    }
    if (at) times[link.videoId] = at;
    kept.push(link);
  }
  return { links: kept, refreshed: times, dropped };
}

/** The locally kept uploads still inside `KEEP_UPLOADS_DAYS` of when the job last read them (`readAt`). */
export function pruneUploads<T extends { readAt: string }>(uploads: Record<string, T>, now: Date): Record<string, T> {
  const oldest = now.getTime() - KEEP_UPLOADS_DAYS * DAY;
  return Object.fromEntries(Object.entries(uploads).filter(([, upload]) => Date.parse(upload.readAt) > oldest));
}
