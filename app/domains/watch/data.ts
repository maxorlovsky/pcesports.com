// Reads the data job's list for a build, on the server only: the home page imports it at build time, so neither the
// file nor this code reaches the browser, and a build with no data (a pull request's) lists nothing.
import { existsSync, readFileSync } from 'node:fs';
import { join } from 'node:path';
import { recentEntries } from './list.ts';
import type { ShownEntry, WatchData } from './list.ts';

/** Where the data job's file sits, from the repository root a build runs in. */
export const WATCH_DATA = 'app/domains/watch/data/watch.json';

/** The entries the home page shows: those of the last days before `now`, or none without the data file. */
export function shownEntries(now: Date, root = process.cwd()): ShownEntry[] {
  const file = join(root, WATCH_DATA);
  if (!existsSync(file)) return [];
  const data = JSON.parse(readFileSync(file, 'utf8')) as WatchData;
  return recentEntries(data.entries, now);
}
