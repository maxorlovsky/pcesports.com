// One run of the data job: PandaScore's tier-1 CS2 matches and the whitelisted VOD channels' uploads, linked, written
// as the data branch's files. run.mjs is the command around it (lock, update, env file); the rules are the domains'.
// What it keeps on the machine (never committed) is in the state folder: state.json (uploads with their titles, kept
// KEEP_UPLOADS_DAYS; when each linked video was last refreshed; when each channel was last read), report.md and job.log.
import { existsSync, mkdirSync, readFileSync, renameSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import { eventOf, fetchPandaScoreMatches, fetchPandaScoreTierResults, fetchWindow, mergeMatches, startOf, toMatch, TIER_ONE } from '../../app/domains/matches/index.ts';
import {
  CHANNELS_LIST, MISSING_AFTER_HOURS, PUBLISH_WINDOW_HOURS, REFRESH_DAYS, VOD_CHANNELS, applyRefresh, dueForRefresh, durationSeconds, linkVods,
  missingVods, pruneUploads, unmatchedUploads, youtubeClient,
} from '../../app/domains/vods/index.ts';
import { LABELS, RESULTS_DAYS, mergeResults, toResult, watchEntries } from '../../app/domains/watch/index.ts';
import { fetchData, forgetData, publishData, pruneData, readData } from './data-branch.mjs';

const HOUR = 3600 * 1000;
const DAY = 24 * HOUR;
/** At most one data commit per this many minutes, so a busy event day makes at most 48 deploys. */
export const MIN_GAP_MINUTES = 30;
/** How often the job's clone drops the data commits it replaced (pruneData). */
const PRUNE_HOURS = 24;
/** The whitelist's featured-channels links and uploads playlists are re-checked this often. */
export const WHITELIST_CHECK_HOURS = 24;
/** Each read of an uploads playlist starts this long before the last one, so an upload published meanwhile isn't missed. */
const POLL_OVERLAP_HOURS = 1;
/** How far back the first read of a channel goes: the matcher's publish window and a day. */
const FIRST_POLL_HOURS = PUBLISH_WINDOW_HOURS + 24;
const LOG_LINES = 5000;

export const MATCHES_FILE = 'app/domains/matches/data/matches.json';
export const VODS_FILE = 'app/domains/vods/data/vods.json';
export const WATCH_FILE = 'app/domains/watch/data/watch.json';

export function emptyState() {
  return {
    channels: {}, uploads: {}, refreshed: {}, whitelist: { checkedAt: null, failed: {} }, quota: { day: null, units: 0 }, deployPending: null, knownData: null, prunedAt: null,
    // The tier s and a results the team ratings are replayed from (watch domain), and when PandaScore was last read
    // for them without a gap: until then the list keeps what it was.
    results: { readTo: null, list: [] },
  };
}

export function loadState(dir) {
  const file = join(dir, 'state.json');
  if (!existsSync(file)) return emptyState();
  return { ...emptyState(), ...JSON.parse(readFileSync(file, 'utf8')) };
}

function writeAtomic(file, text) {
  const temp = `${file}.${process.pid}.tmp`;
  writeFileSync(temp, text, { mode: 0o600 });
  renameSync(temp, file);
}

const json = (value) => `${JSON.stringify(value, null, 2)}\n`;

/** The previous data, from the data branch's commit `base` (none: empty lists). */
function storedData(cwd, base) {
  const files = base ? readData(cwd, base) : new Map();
  const read = (path, key) => (files.has(path) ? JSON.parse(files.get(path))[key] ?? [] : []);
  return { matches: read(MATCHES_FILE, 'matches'), vods: read(VODS_FILE, 'vods'), watch: read(WATCH_FILE, 'entries') };
}

/** The daily whitelist check: each VOD channel's uploads playlist is the listed one, and its parent still features it. */
async function checkWhitelist(youtube, state, now, errors) {
  const last = state.whitelist.checkedAt ? Date.parse(state.whitelist.checkedAt) : 0;
  if (now.getTime() - last < WHITELIST_CHECK_HOURS * HOUR) return;
  const failed = {};
  try {
    const playlists = await youtube.uploadPlaylists(VOD_CHANNELS.map((channel) => channel.id));
    for (const channel of VOD_CHANNELS) {
      if (!playlists.has(channel.id)) failed[channel.id] = 'YouTube no longer returns the channel';
      else if (playlists.get(channel.id) !== channel.uploads) failed[channel.id] = `its uploads playlist is now ${playlists.get(channel.id)}`;
    }
    for (const parent of [...new Set(VOD_CHANNELS.map((channel) => channel.featuredBy).filter(Boolean))]) {
      const featured = new Set(await youtube.featuredChannels(parent));
      const parentName = CHANNELS_LIST.find((channel) => channel.id === parent)?.name ?? parent;
      for (const channel of VOD_CHANNELS.filter((vod) => vod.featuredBy === parent)) {
        if (!featured.has(channel.id)) failed[channel.id] ??= `${parentName} no longer features it`;
      }
    }
  } catch (error) {
    errors.push(`whitelist check: ${error.message}`);
    return;
  }
  state.whitelist = { checkedAt: now.toISOString(), failed };
  for (const [id, reason] of Object.entries(failed)) {
    errors.push(`whitelist: ${VOD_CHANNELS.find((channel) => channel.id === id).name} (${id}) is no longer read: ${reason} (app/domains/vods/channels.json)`);
  }
}

/**
 * Reads each active VOD channel's new uploads, and re-reads every upload first seen live. A channel's read time moves
 * on only once the details of what it listed are stored, so a failed videos.list loses nothing: the next run reads
 * the same uploads again.
 */
async function pollUploads(youtube, state, channels, now, since, errors) {
  const toRead = new Set();
  const listed = [];
  for (const channel of channels) {
    const polled = state.channels[channel.id]?.polledAt;
    let from = polled ? Date.parse(polled) - POLL_OVERLAP_HOURS * HOUR : now.getTime() - FIRST_POLL_HOURS * HOUR;
    if (since && since.getTime() < from) from = since.getTime();
    try {
      for (const id of await youtube.uploadsSince(channel.uploads, new Date(from))) if (!state.uploads[id]) toRead.add(id);
      listed.push(channel.id);
    } catch (error) {
      errors.push(`${channel.name}: ${error.message}`);
    }
  }
  for (const [id, upload] of Object.entries(state.uploads)) {
    if (upload.liveBroadcastContent !== 'none' || !durationSeconds(upload.duration)) toRead.add(id);
  }
  let read = [];
  if (toRead.size) {
    try {
      read = await youtube.videos([...toRead]);
    } catch (error) {
      errors.push(`videos.list: ${error.message}`);
      return 0;
    }
    const returned = new Set(read.map((upload) => upload.id));
    for (const upload of read) state.uploads[upload.id] = { ...upload, readAt: now.toISOString() };
    for (const id of toRead) if (!returned.has(id)) delete state.uploads[id];
  }
  for (const id of listed) state.channels[id] = { polledAt: now.toISOString() };
  return read.length;
}

/**
 * Runs the job once. `push` publishes the data branch and `deploy` starts the deploy: after a push, or for a data
 * commit on origin this state never saw, and again on later runs until it succeeds; without `push` the files go to the state folder's `out/` only. Returns what happened;
 * `errors` are problems the run carried on through.
 */
export async function runJob({ cwd, stateDir, now = new Date(), secrets, fetch = globalThis.fetch, since, push = false, deploy, redact = (text) => text }) {
  mkdirSync(stateDir, { recursive: true });
  const state = loadState(stateDir);
  const errors = [];

  const base = fetchData(cwd);
  let stored;
  try {
    stored = storedData(cwd, base);
  } finally {
    // A run by hand leaves no ref to the data branch behind, so that clone's own gc drops what it fetched.
    if (!push) forgetData(cwd);
  }

  const window = fetchWindow(now, since);
  let pandascore = { matches: [], requests: 0 };
  let matchesWindow = window;
  let matchesFresh = true;
  try {
    pandascore = await fetchPandaScoreMatches({ token: secrets.pandascore, from: window.from, to: window.to, fetch });
  } catch (error) {
    // The stored matches stay as they are, and the VOD side still runs, but links no new VOD: a rematch the stored
    // matches don't know of yet could lose its VOD to the first meeting.
    errors.push(error.message);
    matchesWindow = { from: now, to: now };
    matchesFresh = false;
  }
  const fetched = pandascore.matches.map(toMatch).filter(Boolean);
  const unknownLeagues = new Map();
  for (const raw of pandascore.matches) {
    if (TIER_ONE.includes(raw.tournament.tier ?? '') && !fetched.some((match) => match.id === raw.id)) {
      unknownLeagues.set(raw.league.name, (unknownLeagues.get(raw.league.name) ?? 0) + 1);
    }
  }
  const matches = mergeMatches(stored.matches, fetched, matchesWindow, now);
  if (matchesFresh) pandascore.requests += await updateResults(state, pandascore.matches, window, now, { token: secrets.pandascore, fetch }, errors);

  const youtube = youtubeClient({ key: secrets.youtube, fetch });
  await checkWhitelist(youtube, state, now, errors);
  const active = VOD_CHANNELS.filter((channel) => !state.whitelist.failed[channel.id]);
  // The uploads past KEEP_UPLOADS_DAYS go first, so none of them is matched; one its channel still lists is read again.
  state.uploads = pruneUploads(state.uploads, now);
  const read = await pollUploads(youtube, state, active, now, since, errors);

  const due = dueForRefresh(stored.vods, state.refreshed, now).filter((id) => !state.uploads[id] || state.uploads[id].readAt !== now.toISOString());
  let found = [];
  let checked = due;
  if (due.length) {
    try {
      found = await youtube.videos(due);
    } catch (error) {
      errors.push(`refresh: ${error.message}`);
      checked = [];
    }
  }
  // A stored video the poll just read counts as refreshed: it is in state.uploads with this run's time.
  const justRead = stored.vods.filter((link) => state.uploads[link.videoId]?.readAt === now.toISOString()).map((link) => link.videoId);
  const refresh = applyRefresh(stored.vods, state.refreshed, [...checked, ...justRead], [...found, ...justRead.map((id) => state.uploads[id])], now);
  for (const { link } of refresh.dropped) delete state.uploads[link.videoId];

  // A new link is never older YouTube data than a stored one: an upload read more than REFRESH_DAYS ago (one that
  // waited out a PandaScore outage, say) is read again before it is linked, and a video deleted since is dropped. One
  // that can't be read this run is left out of it.
  const channelIds = new Set(active.map((channel) => channel.id));
  const kept = new Set(refresh.links.map((link) => link.videoId));
  const skipped = new Set();
  let uploads;
  let links;
  let ambiguous;
  for (;;) {
    uploads = Object.values(state.uploads).filter((upload) => !skipped.has(upload.id));
    ({ links, ambiguous } = linkVods(matches, uploads, refresh.links, channelIds, { assign: matchesFresh }));
    const stale = links.map((link) => link.videoId)
      .filter((id) => !kept.has(id) && now.getTime() - Date.parse(state.uploads[id].readAt) >= REFRESH_DAYS * DAY);
    if (!stale.length) break;
    try {
      const back = new Map((await youtube.videos(stale)).map((upload) => [upload.id, upload]));
      for (const id of stale) {
        if (back.has(id)) state.uploads[id] = { ...back.get(id), readAt: now.toISOString() };
        else delete state.uploads[id];
      }
    } catch (error) {
      errors.push(`videos.list (re-read before linking): ${error.message}`);
      for (const id of stale) skipped.add(id);
    }
  }
  // A kept link keeps its last refresh (none when it was never read in this state and the refresh failed: the next run
  // asks again); a new link was read from YouTube when its upload was.
  const refreshed = {};
  for (const link of links) {
    const at = kept.has(link.videoId) ? refresh.refreshed[link.videoId] : state.uploads[link.videoId]?.readAt;
    if (at) refreshed[link.videoId] = at;
  }
  state.refreshed = refreshed;

  // Until the results are read without a gap, the ratings would be missing teams' strength: the list keeps its
  // entries (those whose VOD link still stands) rather than relabel them.
  const linked = new Set(links.map((link) => link.videoId));
  const entries = state.results.readTo ? watchEntries(matches, links, state.results.list, now) : stored.watch.filter((entry) => linked.has(entry.videoId));
  const files = new Map([
    [MATCHES_FILE, json({ source: 'PandaScore', matches })],
    [VODS_FILE, json({ vods: links })],
    [WATCH_FILE, json({ source: 'PandaScore', entries })],
  ]);

  let published = { status: 'not pushed' };
  if (push) {
    // A failed push (an expired token, say) is one more problem: the run still keeps its state, report and log, and
    // the next run publishes what the data is by then.
    try {
      published = publishData(cwd, files, { base, now, minGapMs: MIN_GAP_MINUTES * 60 * 1000 });
    } catch (error) {
      errors.push(`publish: ${error.message}`);
      published = { status: 'push failed' };
    }
    if (published.status === 'pushed') state.deployPending = published.commit;
    // A data commit this state never saw (a push that landed before its error, or another clone's) owes a deploy too;
    // a refused push leaves the branch where it was, and owes none. A new state folder owes one for what it finds,
    // which also covers a deploy its lost predecessor still owed.
    else if (base && base !== state.knownData) state.deployPending ??= base;
    state.knownData = published.status === 'pushed' ? published.commit : base;
    if (state.deployPending) {
      try {
        await deploy();
        state.deployPending = null;
      } catch (error) {
        errors.push(`deploy: ${error.message} (the next run tries again)`);
      }
    }
    // The job's own clone drops the data commits it replaced, once a day per state folder. Only there: the prune is
    // a `git gc` with its own expiry, which is no business of a developer's clone.
    if (!state.prunedAt || now.getTime() - Date.parse(state.prunedAt) >= PRUNE_HOURS * HOUR) {
      try {
        pruneData(cwd);
        state.prunedAt = now.toISOString();
      } catch (error) {
        errors.push(`prune: ${error.message}`);
      }
    }
  } else {
    mkdirSync(join(stateDir, 'out'), { recursive: true });
    for (const [path, text] of files) writeAtomic(join(stateDir, 'out', path.split('/').pop()), text);
  }

  // What goes to the files below may quote a git or gh error: no secret goes with it.
  errors.splice(0, errors.length, ...errors.map(redact));
  const day = now.toISOString().slice(0, 10);
  state.quota = { day, units: (state.quota.day === day ? state.quota.units : 0) + youtube.quota.units };
  writeAtomic(join(stateDir, 'state.json'), json(state));

  const summary = {
    matches: matches.length,
    vods: links.length,
    listed: entries.length,
    newUploads: read,
    dropped: refresh.dropped.length,
    pandascoreRequests: pandascore.requests,
    units: youtube.quota.units,
    unitsToday: state.quota.units,
    calls: youtube.quota.calls,
    data: published.status,
  };
  const missing = missingVods(matches, links, now);
  const unmatched = unmatchedUploads(uploads.filter((upload) => state.uploads[upload.id]), links);
  writeAtomic(join(stateDir, 'report.md'), report({ now, summary, errors, matches, links, missing, unmatched, ambiguous, unknownLeagues, dropped: refresh.dropped, state, published, entries }));
  appendLog(stateDir, `${now.toISOString()} ${errors.length ? 'errors' : 'ok'} matches=${summary.matches} vods=${summary.vods} listed=${summary.listed} new-uploads=${read} dropped=${summary.dropped} pandascore=${summary.pandascoreRequests} units=${summary.units} today=${summary.unitsToday} data=${published.status}${published.commit ? ` ${published.commit.slice(0, 7)}` : ''}${errors.length ? ` errors=${errors.length}` : ''}`);

  return { summary, errors, published, files };
}

/**
 * Merges the window's tier s and a results into the stored ones. A first run, or one after a gap the window doesn't
 * cover (the job off for days), first reads the missing stretch, back to `RESULTS_DAYS`: about a dozen requests for a
 * year. A failed read leaves `readTo` where it was, so the next run tries again. Returns the requests it made.
 */
async function updateResults(state, fetchedRaw, window, now, pandascore, errors) {
  const readTo = state.results.readTo ? Date.parse(state.results.readTo) : null;
  let backfill = [];
  let requests = 0;
  if (readTo === null || readTo < window.from.getTime()) {
    const from = new Date(Math.max(readTo === null ? 0 : readTo - DAY, now.getTime() - RESULTS_DAYS * DAY));
    try {
      ({ matches: backfill, requests } = await fetchPandaScoreTierResults({ ...pandascore, from, to: now, tiers: TIER_ONE }));
    } catch (error) {
      errors.push(`results for the team ratings: ${error.message}`);
      state.results.list = mergeResults(state.results.list, fetchedRaw.map(toResult).filter(Boolean), now);
      return requests;
    }
  }
  state.results.list = mergeResults(state.results.list, [...backfill, ...fetchedRaw].map(toResult).filter(Boolean), now);
  state.results.readTo = now.toISOString();
  return requests;
}

export function appendLog(stateDir, line) {
  const file = join(stateDir, 'job.log');
  const lines = existsSync(file) ? readFileSync(file, 'utf8').split('\n').filter(Boolean) : [];
  lines.push(line.replace(/\s*\n\s*/g, ' '));
  writeAtomic(file, `${lines.slice(-LOG_LINES).join('\n')}\n`);
}

const teams = (match) => (match.teams.length ? match.teams.map((team) => team.name).join(' vs ') : 'teams to be decided');
const when = (match) => (startOf(match) ?? '').slice(0, 16).replace('T', ' ');
const cell = (text) => String(text).replace(/\|/g, '\\|');

/** The local report: what to look at in a few minutes a week. It holds YouTube titles, so it is never committed. */
function report({ now, summary, errors, matches, links, missing, unmatched, ambiguous, unknownLeagues, dropped, state, published, entries }) {
  const byId = new Map(matches.map((match) => [match.id, match]));
  const uncovered = new Map();
  const covered = new Set(VOD_CHANNELS.flatMap((channel) => channel.organizers));
  for (const match of matches) if (!covered.has(match.organizer)) uncovered.set(match.organizer, (uncovered.get(match.organizer) ?? 0) + 1);
  const lines = [
    `# Data job report, ${now.toISOString()}`,
    '',
    `Data: ${published.status}${published.commit ? ` (${published.commit.slice(0, 7)})` : ''}${published.lastAt ? `, last push ${published.lastAt.toISOString()}` : ''}. ${summary.matches} matches, ${summary.vods} with a VOD. YouTube units: ${summary.units} this run, ${summary.unitsToday} today (UTC) ${JSON.stringify(summary.calls)}. PandaScore requests: ${summary.pandascoreRequests}.`,
    '',
    `## Errors (${errors.length})`,
    '',
    ...(errors.length ? errors.map((error) => `- ${error}`) : ['None.']),
    '',
    `## Finished matches with no VOD ${MISSING_AFTER_HOURS} h after their start (${missing.length})`,
    '',
    'An organiser spelling a team in a way the matcher misses goes into `app/domains/vods/aliases.json`.',
    '',
    ...(missing.length ? missing.map((match) => `- ${when(match)} ${eventOf(match)}: ${teams(match)} (PandaScore ${match.id}, ${match.organizer})`) : ['None.']),
    '',
    `## Full-match-length uploads no match links (${unmatched.length})`,
    '',
    ...(unmatched.length ? unmatched.map((upload) => `- ${upload.publishedAt.slice(0, 16).replace('T', ' ')} "${upload.title}" https://www.youtube.com/watch?v=${upload.id}`) : ['None.']),
    '',
    `## Matches with more than one candidate: the earliest was linked (${ambiguous.length})`,
    '',
    ...(ambiguous.length ? ambiguous.map(({ matchId, videoIds }) => `- ${teams(byId.get(matchId))} (${matchId}): ${videoIds.join(', ')}`) : ['None.']),
    '',
    `## Links dropped this run (${dropped.length})`,
    '',
    ...(dropped.length ? dropped.map(({ link, reason }) => `- ${link.videoId} (match ${link.matchId}): ${reason}`) : ['None.']),
    '',
    `## The watch list (${entries.length}): ${LABELS.map((label) => `${label} ${entries.filter((entry) => entry.label === label).length}`).join(', ')}`,
    '',
    `Team ratings from ${state.results.list.length} tier s/a results, read up to ${state.results.readTo ?? 'never (the list keeps its entries until then)'}.`,
    '',
    '## Tier s/a leagues not in app/domains/matches/leagues.json (left out)',
    '',
    ...(unknownLeagues.size ? [...unknownLeagues].map(([league, count]) => `- ${league}: ${count} match${count === 1 ? '' : 'es'}`) : ['None.']),
    '',
    '## Organisers with no whitelisted VOD channel (their matches get no VOD)',
    '',
    ...(uncovered.size ? [...uncovered].map(([organizer, count]) => `- ${organizer}: ${count} match${count === 1 ? '' : 'es'}`) : ['None.']),
    '',
    `## Channels the whitelist check stopped (${Object.keys(state.whitelist.failed).length})`,
    '',
    ...(Object.keys(state.whitelist.failed).length ? Object.entries(state.whitelist.failed).map(([id, reason]) => `- ${id}: ${reason}`) : ['None.']),
    '',
    `## Linked matches (${links.length}), for hand checks`,
    '',
    '| Start (UTC) | Event | Match | VOD | Title |',
    '|---|---|---|---|---|',
    ...links.map((link) => {
      const match = byId.get(link.matchId);
      const title = state.uploads[link.videoId]?.title ?? '(no longer kept locally)';
      return `| ${when(match)} | ${cell(eventOf(match))} | ${cell(teams(match))} | https://www.youtube.com/watch?v=${link.videoId} | ${cell(title)} |`;
    }),
    '',
  ];
  return lines.join('\n');
}
