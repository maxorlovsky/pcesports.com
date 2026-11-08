// The matches and vods domains' rules: the tier rule and the merge, the matcher and its rematch guard, YouTube's
// 30-day rule, and that no client error repeats a secret.
import assert from 'node:assert/strict';
import { test } from 'node:test';
import { KEEP_DAYS, fetchPandaScoreMatches, fetchWindow, mergeMatches, toMatch } from '../app/domains/matches/index.ts';
import { MAX_AGE_DAYS, REFRESH_DAYS, applyRefresh, dueForRefresh, durationSeconds, linkVods, missingVods, pruneUploads, unmatchedUploads, youtubeClient } from '../app/domains/vods/index.ts';
import { ESL_ARCHIVES, PGL_VODS, rawMatch, upload } from './fixtures/cs2.mjs';

const DAY = 24 * 3600e3;
const at = (iso, hours = 0) => new Date(Date.parse(iso) + hours * 3600e3).toISOString();
const ALL_CHANNELS = new Set([ESL_ARCHIVES, PGL_VODS]);

test('the tier rule keeps PandaScore tier s and a from a known league only', () => {
  const start = '2026-11-02T02:00:00Z';
  assert.equal(toMatch(rawMatch({ id: 1, start }))?.organizer, 'ESL');
  assert.equal(toMatch(rawMatch({ id: 2, start, league: 'PGL Masters', tier: 'a' }))?.organizer, 'PGL');
  assert.equal(toMatch(rawMatch({ id: 3, start, tier: 'b' })), null);
  assert.equal(toMatch(rawMatch({ id: 4, start, league: 'CCT Europe', tier: 's' })), null);
  assert.equal(toMatch(rawMatch({ id: 5, start, league: 'toString' })), null);
});

test('a match record holds its teams, result and times, in team order', () => {
  const match = toMatch(rawMatch({ id: 7, start: '2026-11-02T02:00:00Z', teams: ['mouz', 'navi'] }));
  assert.deepEqual(match.teams.map((team) => team.name), ['MOUZ', 'Natus Vincere']);
  assert.deepEqual(match.score, [2, 1]);
  assert.equal(match.winnerId, 3240);
  assert.equal(match.beginAt, '2026-11-02T02:00:00Z');
});

test('a fetch replaces its window and keeps the stored matches outside it until they are 30 days old', () => {
  const now = new Date('2026-11-20T12:00:00Z');
  const window = fetchWindow(now);
  const old = toMatch(rawMatch({ id: 1, start: at(now.toISOString(), -(KEEP_DAYS + 1) * 24) }));
  const kept = toMatch(rawMatch({ id: 2, start: at(now.toISOString(), -10 * 24) }));
  const vanished = toMatch(rawMatch({ id: 3, start: at(now.toISOString(), -24) }));
  const updated = toMatch(rawMatch({ id: 4, start: at(now.toISOString(), 24), status: 'not_started' }));
  const fresh = { ...updated, status: 'running' };
  const merged = mergeMatches([old, kept, vanished, updated], [fresh], window, now);
  assert.deepEqual(merged.map((match) => [match.id, match.status]), [[2, 'finished'], [4, 'running']]);
});

test('PandaScore pages until a short page, with the token in the header only', async () => {
  const matches = Array.from({ length: 150 }, (_, i) => rawMatch({ id: i + 1, start: '2026-11-02T02:00:00Z' }));
  const seen = [];
  const fetch = async (url, init) => {
    seen.push({ url, auth: init.headers.Authorization });
    const page = Number(new URL(url).searchParams.get('page'));
    return new Response(JSON.stringify(matches.slice((page - 1) * 100, page * 100)));
  };
  const result = await fetchPandaScoreMatches({ token: 'secret-token', from: new Date('2026-11-01'), to: new Date('2026-11-10'), fetch });
  assert.equal(result.matches.length, 150);
  assert.equal(result.requests, 2);
  assert.ok(seen.every(({ url, auth }) => !url.includes('secret-token') && auth === 'Bearer secret-token'));
  const failing = async () => new Response(JSON.stringify({ error: 'Token is invalid' }), { status: 401 });
  await assert.rejects(fetchPandaScoreMatches({ token: 'secret-token', from: new Date(), to: new Date(), fetch: failing }), (error) => !error.message.includes('secret-token') && /Token is invalid/.test(error.message));
});

test('a YouTube error never repeats the key, though the request URL holds it', async () => {
  const thrower = async (url) => {
    throw new Error(`connect failed for ${url}`);
  };
  await assert.rejects(youtubeClient({ key: 'yt-secret', fetch: thrower }).videos(['a']), (error) => !error.message.includes('yt-secret') && /<key>/.test(error.message));
  const denied = async () => new Response(JSON.stringify({ error: { message: 'API key yt-secret not valid' } }), { status: 400 });
  await assert.rejects(youtubeClient({ key: 'yt-secret', fetch: denied }).videos(['a']), (error) => !error.message.includes('yt-secret'));
});

test('YouTube durations parse as returned, and only full-match lengths count', () => {
  assert.equal(durationSeconds('PT1H32M10S'), 5530);
  assert.equal(durationSeconds('P0D'), 0);
  assert.equal(durationSeconds('PT'), null);
  assert.equal(durationSeconds(''), null);
});

const START = '2026-11-02T02:00:00Z';
const match = (id, overrides = {}) => toMatch(rawMatch({ id, start: START, ...overrides }));

test('the matcher links the organiser VOD channel upload naming both teams and the event, and nothing else', () => {
  const uploads = [
    upload({ id: 'broadcast', title: 'Natus Vincere vs Team Falcons - IEM Beijing 2026 - Group A', publishedAt: at(START, 1), duration: 'PT9H' }),
    upload({ id: 'clip', title: 'NAVI vs Falcons - IEM Beijing 2026 - Highlights', publishedAt: at(START, 1), duration: 'PT5M' }),
    upload({ id: 'other-event', title: 'NAVI vs Falcons - IEM Cologne 2026 - Group A', publishedAt: at(START, 2) }),
    upload({ id: 'pgl', channelId: PGL_VODS, title: 'NAVI vs Falcons - IEM Beijing 2026 - Group A', publishedAt: at(START, 2) }),
    upload({ id: 'live', title: 'NAVI vs Falcons - IEM Beijing 2026 - Group A', publishedAt: at(START, 2), duration: 'P0D', live: 'live' }),
    upload({ id: 'before', title: 'NAVI vs Falcons - IEM Beijing 2026 - Group A', publishedAt: at(START, -1) }),
    upload({ id: 'vod', title: 'Falcons vs. NAVI - IEM Beijing 2026 - Group A', publishedAt: at(START, 3) }),
  ];
  const { links } = linkVods([match(1)], uploads, [], ALL_CHANNELS);
  assert.deepEqual(links, [{ matchId: 1, videoId: 'vod', channelId: ESL_ARCHIVES }]);
});

test('the matcher reads aliases.json for spellings PandaScore lacks', () => {
  const uploads = [upload({ id: 'pv', title: 'PVISION vs. MOUZ - IEM Beijing 2026 - Group B', publishedAt: at(START, 2) })];
  assert.equal(linkVods([match(1, { teams: ['mouz', 'parivision'] })], uploads, [], ALL_CHANNELS).links.length, 1);
});

test('rematch guard: with the first meeting VOD missing, the second meeting VOD is never linked to the first', () => {
  const first = match(1);
  const second = match(2, { start: at(START, 30) });
  const secondVod = upload({ id: 'second', title: 'NAVI vs. Falcons - IEM Beijing 2026 - Group A', publishedAt: at(START, 33) });
  const { links } = linkVods([first, second], [secondVod], [], ALL_CHANNELS);
  assert.deepEqual(links.map((link) => [link.matchId, link.videoId]), [[2, 'second']]);
  const firstVod = upload({ id: 'first', title: 'Falcons vs. NAVI - IEM Beijing 2026 - Group A', publishedAt: at(START, 3) });
  assert.deepEqual(linkVods([first, second], [secondVod, firstVod], [], ALL_CHANNELS).links.map((link) => [link.matchId, link.videoId]), [[1, 'first'], [2, 'second']]);
});

test('a canceled or forfeited rematch neither takes the first meeting VOD nor cuts its window short', () => {
  const first = match(1);
  const vod = upload({ id: 'first', title: 'NAVI vs. Falcons - IEM Beijing 2026 - Group A', publishedAt: at(START, 4) });
  const forfeited = { ...match(2, { start: at(START, 3) }), forfeit: true };
  assert.deepEqual(linkVods([first, forfeited], [vod], [], ALL_CHANNELS).links.map((link) => [link.matchId, link.videoId]), [[1, 'first']]);
  // A forfeit has no VOD of its own, whatever upload names its teams
  const after = upload({ id: 'later', title: 'NAVI vs. Falcons - IEM Beijing 2026 - Group A', publishedAt: at(START, 6) });
  assert.deepEqual(linkVods([forfeited], [after], [], ALL_CHANNELS).links, []);
  const canceled = match(3, { start: at(START, 3), status: 'canceled' });
  assert.deepEqual(linkVods([first, canceled], [vod], [], ALL_CHANNELS).links.map((link) => [link.matchId, link.videoId]), [[1, 'first']]);
});

test('a stored link stays while its match does; a channel the whitelist check stopped links nothing new', () => {
  const stored = [{ matchId: 1, videoId: 'kept', channelId: ESL_ARCHIVES }, { matchId: 9, videoId: 'gone', channelId: ESL_ARCHIVES }];
  const vod = upload({ id: 'vod', title: 'NAVI vs. Falcons - IEM Beijing 2026 - Group A', publishedAt: at(START, 3) });
  assert.deepEqual(linkVods([match(1)], [vod], stored, ALL_CHANNELS).links.map((link) => link.videoId), ['kept']);
  assert.deepEqual(linkVods([match(2)], [vod], [], new Set([PGL_VODS])).links, []);
  // A channel taken out of channels.json takes its links with it, and only the ids of a stored link are carried
  const extra = [{ matchId: 1, videoId: 'unlisted', channelId: 'UCnotWhitelisted' }];
  assert.deepEqual(linkVods([match(1)], [], extra, ALL_CHANNELS).links, []);
  const padded = [{ matchId: 1, videoId: 'kept', channelId: ESL_ARCHIVES, title: 'leaked' }];
  assert.deepEqual(linkVods([match(1)], [], padded, ALL_CHANNELS).links, [{ matchId: 1, videoId: 'kept', channelId: ESL_ARCHIVES }]);
});

test('the report lists finished matches with no VOD after 12 h and full-length uploads no match links', () => {
  const now = new Date(at(START, 13));
  const lonely = upload({ id: 'lonely', title: 'Spirit vs. G2 - IEM Beijing 2026 - Group A', publishedAt: at(START, 3) });
  assert.deepEqual(missingVods([match(1), match(2, { start: at(START, 2) })], [], now).map((m) => m.id), [1]);
  assert.deepEqual(unmatchedUploads([lonely], []).map((u) => u.id), ['lonely']);
});

test('stored links are re-read every 7 days, and a video YouTube no longer returns is dropped', () => {
  const now = new Date('2026-12-01T00:00:00Z');
  const links = [
    { matchId: 1, videoId: 'fresh', channelId: ESL_ARCHIVES },
    { matchId: 2, videoId: 'stale', channelId: ESL_ARCHIVES },
    { matchId: 3, videoId: 'deleted', channelId: ESL_ARCHIVES },
    { matchId: 4, videoId: 'moved', channelId: ESL_ARCHIVES },
    { matchId: 5, videoId: 'unknown', channelId: ESL_ARCHIVES },
  ];
  const ago = (days) => new Date(now.getTime() - days * DAY).toISOString();
  const refreshed = { fresh: ago(1), stale: ago(REFRESH_DAYS), deleted: ago(8), moved: ago(9) };
  const due = dueForRefresh(links, refreshed, now);
  assert.deepEqual(due, ['stale', 'deleted', 'moved', 'unknown']);
  const found = [upload({ id: 'stale', publishedAt: ago(20), title: 'x' }), upload({ id: 'moved', channelId: PGL_VODS, publishedAt: ago(20), title: 'x' }), upload({ id: 'unknown', publishedAt: ago(20), title: 'x' })];
  const result = applyRefresh(links, refreshed, due, found, now);
  assert.deepEqual(result.links.map((link) => link.videoId), ['fresh', 'stale', 'unknown']);
  assert.deepEqual(result.dropped.map(({ link, reason }) => [link.videoId, reason]), [['deleted', 'deleted'], ['moved', 'moved']]);
  assert.deepEqual(result.refreshed, { fresh: ago(1), stale: now.toISOString(), unknown: now.toISOString() });
});

test('when the refresh fails, a link last read 30 days ago or more is dropped anyway', () => {
  const now = new Date('2026-12-01T00:00:00Z');
  const links = [{ matchId: 1, videoId: 'old', channelId: ESL_ARCHIVES }, { matchId: 2, videoId: 'recent', channelId: ESL_ARCHIVES }];
  const refreshed = { old: new Date(now.getTime() - MAX_AGE_DAYS * DAY).toISOString(), recent: new Date(now.getTime() - 8 * DAY).toISOString() };
  const result = applyRefresh(links, refreshed, [], [], now);
  assert.deepEqual(result.links.map((link) => link.videoId), ['recent']);
  assert.deepEqual(result.dropped.map(({ reason }) => reason), ['expired']);
});

test('uploads kept on the machine go 14 days after they were last read', () => {
  const now = new Date('2026-12-01T00:00:00Z');
  const kept = pruneUploads({ a: { readAt: '2026-11-20T00:00:00Z' }, b: { readAt: '2026-11-17T00:00:00Z' } }, now);
  assert.deepEqual(Object.keys(kept), ['a']);
});
