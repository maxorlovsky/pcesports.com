// tools/data-job/: one run end to end against fake PandaScore and YouTube APIs and a scratch origin. What it
// publishes (ids only, one commit, only when changed, the 30-minute gap), the refresh, the lock, the env file rules,
// and pull.mjs, which the deploy runs.
import assert from 'node:assert/strict';
import { spawn, spawnSync } from 'node:child_process';
import { chmodSync, cpSync, existsSync, mkdirSync, mkdtempSync, readFileSync, rmSync, utimesSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, resolve } from 'node:path';
import { after, before, test } from 'node:test';
import { fetchData, pruneData, publishData } from '../tools/data-job/data-branch.mjs';
import { MATCHES_FILE, VODS_FILE, runJob } from '../tools/data-job/job.mjs';
import { BLAST_VODS, ESL_ARCHIVES, PGL_VODS, fakeApis, rawMatch, upload } from './fixtures/cs2.mjs';

const REPO = resolve(import.meta.dirname, '..');
const START = '2026-11-02T02:00:00Z';
const later = (hours) => new Date(Date.parse(START) + hours * 3600e3);
const SECRETS = { pandascore: 'ps-secret', youtube: 'yt-secret' };
let dir;

function git(cwd, ...args) {
  const result = spawnSync('git', ['-c', 'user.name=Test', '-c', 'user.email=test@example.com', ...args], { cwd, encoding: 'utf8' });
  assert.equal(result.status, 0, result.stderr);
  return result.stdout.trim();
}

/** A scratch origin with a master commit, and a clone of it: where a run publishes. */
function scratch(name) {
  const root = join(dir, name);
  const work = join(root, 'work');
  mkdirSync(work, { recursive: true });
  git(root, 'init', '-q', '-b', 'master', work);
  writeFileSync(join(work, 'README.md'), 'scratch\n');
  git(work, 'add', '-A');
  git(work, 'commit', '-q', '-m', 'test: scratch');
  git(root, 'clone', '-q', '--bare', work, join(root, 'origin.git'));
  git(root, 'clone', '-q', join(root, 'origin.git'), join(root, 'clone'));
  return { clone: join(root, 'clone'), origin: join(root, 'origin.git'), state: join(root, 'state') };
}

function world() {
  return {
    matches: [rawMatch({ id: 1, start: START }), rawMatch({ id: 2, start: later(5).toISOString(), teams: ['mouz', 'vitality'], status: 'running' })],
    uploads: {
      [ESL_ARCHIVES]: [upload({ id: 'vod1', title: 'NAVI vs. Falcons - IEM Beijing 2026 - Group A', publishedAt: later(3).toISOString() })],
      [PGL_VODS]: [],
      [BLAST_VODS]: [],
    },
  };
}

const dataFiles = (origin) => git(origin, 'ls-tree', '-r', '--name-only', 'data').split('\n');
const dataFile = (origin, path) => JSON.parse(git(origin, 'show', `data:${path}`));

before(() => {
  dir = mkdtempSync(join(tmpdir(), 'pce-data-job-'));
});

after(() => rmSync(dir, { recursive: true, force: true }));

test('a run publishes the matches and YouTube ids only, as the data branch one commit, then starts the deploy', async () => {
  const { clone, origin, state } = scratch('first');
  const apis = fakeApis(world());
  let deploys = 0;
  const run = await runJob({ cwd: clone, stateDir: state, now: later(6), secrets: SECRETS, fetch: apis.fetch, push: true, deploy: async () => deploys++ });
  assert.deepEqual(run.errors, []);
  assert.equal(run.published.status, 'pushed');
  assert.equal(deploys, 1);
  assert.deepEqual(dataFiles(origin), [MATCHES_FILE, VODS_FILE]);
  assert.equal(git(origin, 'rev-list', '--count', 'data'), '1');
  assert.deepEqual(dataFile(origin, VODS_FILE), { vods: [{ matchId: 1, videoId: 'vod1', channelId: ESL_ARCHIVES }] });
  assert.deepEqual(dataFile(origin, MATCHES_FILE).matches.map((match) => match.id), [1, 2]);
  // The title, YouTube's publish time and length stay on the machine
  const published = git(origin, 'show', `data:${VODS_FILE}`) + git(origin, 'show', `data:${MATCHES_FILE}`);
  for (const local of ['IEM Beijing 2026 - Group A', 'PT1H45M', later(3).toISOString()]) assert.ok(!published.includes(local), local);
  assert.match(readFileSync(join(state, 'state.json'), 'utf8'), /IEM Beijing 2026 - Group A/);
  // No search.list, ever
  assert.ok(apis.calls.every((call) => call.endpoint !== 'search'));
});

test('unchanged data commits nothing; a change waits for 30 minutes after the last push, then replaces the one commit', async () => {
  const { clone, origin, state } = scratch('batching');
  const data = world();
  const apis = fakeApis(data);
  const deploys = [];
  const options = { cwd: clone, stateDir: state, secrets: SECRETS, fetch: apis.fetch, push: true, deploy: async () => deploys.push(1) };
  await runJob({ ...options, now: later(6) });
  const first = git(origin, 'rev-parse', 'data');
  assert.equal((await runJob({ ...options, now: later(6.25) })).published.status, 'unchanged');
  data.matches[1] = rawMatch({ id: 2, start: later(5).toISOString(), teams: ['mouz', 'vitality'] });
  assert.equal((await runJob({ ...options, now: later(6.4) })).published.status, 'waiting');
  assert.equal(git(origin, 'rev-parse', 'data'), first);
  assert.equal(deploys.length, 1);
  // 29.5 minutes: a run starts a few seconds earlier in its slot than the one that published, and still publishes
  const run = await runJob({ ...options, now: later(6 + 29.5 / 60) });
  assert.equal(run.published.status, 'pushed');
  assert.notEqual(git(origin, 'rev-parse', 'data'), first);
  assert.equal(git(origin, 'rev-list', '--count', 'data'), '1');
  assert.equal(dataFile(origin, MATCHES_FILE).matches[1].status, 'finished');
  assert.equal(deploys.length, 2);
});

test('two clones that read the same data branch can never both replace it', async () => {
  const { clone, origin, state } = scratch('lease');
  await runJob({ cwd: clone, stateDir: state, now: later(6), secrets: SECRETS, fetch: fakeApis(world()).fetch, push: true, deploy: async () => {} });
  const other = join(dir, 'lease', 'other');
  git(join(dir, 'lease'), 'clone', '-q', origin, other);
  const base = fetchData(clone);
  assert.equal(fetchData(other), base);
  const files = (text) => new Map([[VODS_FILE, text]]);
  assert.equal(publishData(clone, files('{"vods":[]}\n'), { base, now: later(7), minGapMs: 0 }).status, 'pushed');
  const won = git(origin, 'rev-parse', 'data');
  assert.throws(() => publishData(other, files('{"vods":[1]}\n'), { base, now: later(7), minGapMs: 0 }), /stale info|rejected/);
  assert.equal(git(origin, 'rev-parse', 'data'), won);
});

const has = (cwd, object) => spawnSync('git', ['cat-file', '-e', object], { cwd }).status === 0;
const reflog = (clone) => join(clone, '.git', 'logs', 'refs', 'remotes', 'origin', 'data');

test('the data commits a clone replaced leave no trace in it, packed or loose: no reflog, and the prune deletes them', async () => {
  const { clone, origin, state } = scratch('prune');
  const data = world();
  const options = { cwd: clone, stateDir: state, secrets: SECRETS, fetch: fakeApis(data).fetch, push: true, deploy: async () => {} };
  await runJob({ ...options, now: later(6) });
  const old = git(clone, 'rev-parse', 'refs/remotes/origin/data');
  // A clone over file:// has the first data commit in a pack, with the reflog a plain clone writes
  const packed = join(dir, 'prune', 'packed');
  git(join(dir, 'prune'), 'clone', '-q', `file://${origin}`, packed);
  for (const to of ['HEAD', old]) git(packed, 'update-ref', '--create-reflog', 'refs/remotes/origin/data', to);
  assert.ok(existsSync(reflog(packed)) && has(packed, old));
  data.uploads[ESL_ARCHIVES] = [];
  data.matches.pop();
  await runJob({ ...options, now: later(7) });
  assert.notEqual(git(clone, 'rev-parse', 'refs/remotes/origin/data'), old);
  assert.equal(existsSync(reflog(clone)), false);
  pruneData(clone, 'now');
  assert.equal(has(clone, old), false, 'the replaced commit is gone from the job clone');
  // The job prunes its own clone daily (its first run in a state folder included): in the packed clone, with its
  // reflog, a run leaves the old commit nowhere
  process.env.PCESPORTS_DATA_PRUNE_EXPIRE = 'now';
  try {
    await runJob({ ...options, cwd: packed, stateDir: join(dir, 'prune', 'packed-state'), now: later(8) });
  } finally {
    delete process.env.PCESPORTS_DATA_PRUNE_EXPIRE;
  }
  assert.equal(existsSync(reflog(packed)), false);
  assert.equal(has(packed, old), false, 'the replaced commit is gone from the packed clone');
});

test('a failed videos.list loses no upload: the next run reads the same ones again and links them', async () => {
  const { clone, state } = scratch('videos-down');
  const data = { ...world(), fail: ['videos'] };
  const apis = fakeApis(data);
  const options = { cwd: clone, stateDir: state, secrets: SECRETS, fetch: apis.fetch, push: false };
  const first = await runJob({ ...options, now: later(6) });
  assert.match(first.errors.join('\n'), /videos\.list/);
  data.fail = [];
  const second = await runJob({ ...options, now: later(8) });
  assert.deepEqual(second.errors, []);
  assert.deepEqual(JSON.parse(readFileSync(join(state, 'out', 'vods.json'), 'utf8')).vods.map((link) => link.videoId), ['vod1']);
});

test('an upload first seen live is read again until it is over, then linked', async () => {
  const { clone, state } = scratch('live');
  const data = world();
  const stream = upload({ id: 'vod1', title: 'NAVI vs. Falcons - IEM Beijing 2026 - Group A', publishedAt: later(3).toISOString(), duration: 'P0D', live: 'live' });
  data.uploads[ESL_ARCHIVES] = [stream];
  const options = { cwd: clone, stateDir: state, secrets: SECRETS, fetch: fakeApis(data).fetch, push: false };
  await runJob({ ...options, now: later(4) });
  const vods = () => JSON.parse(readFileSync(join(state, 'out', 'vods.json'), 'utf8')).vods;
  assert.deepEqual(vods(), []);
  // Over now: the playlist lists it again, but as known, so only the re-read of live uploads can pick up its new state
  Object.assign(stream, { duration: 'PT1H45M', liveBroadcastContent: 'none' });
  await runJob({ ...options, now: later(9) });
  assert.deepEqual(vods().map((link) => link.videoId), ['vod1']);
});

test('a rejected push keeps the run: state, report and log are written, and the next run starts warm', async () => {
  const { clone, origin, state } = scratch('push-denied');
  const hook = join(origin, 'hooks', 'pre-receive');
  writeFileSync(hook, '#!/bin/sh\necho denied >&2\nexit 1\n', { mode: 0o755 });
  const apis = fakeApis(world());
  let deploys = 0;
  const options = { cwd: clone, stateDir: state, secrets: SECRETS, fetch: apis.fetch, push: true, deploy: async () => deploys++ };
  const run = await runJob({ ...options, now: later(6) });
  assert.equal(run.published.status, 'push failed');
  assert.equal((await runJob({ ...options, now: later(6.25) })).published.status, 'push failed');
  assert.equal(deploys, 0, 'a refused push owes no deploy, run after run');
  assert.match(run.errors.join('\n'), /publish: .*denied/s);
  assert.ok(existsSync(join(state, 'state.json')) && existsSync(join(state, 'report.md')));
  const checks = () => apis.calls.filter((call) => call.endpoint === 'channelSections').length;
  const before = checks();
  rmSync(hook);
  const next = await runJob({ ...options, now: later(6.5) });
  assert.equal(next.published.status, 'pushed');
  assert.equal(deploys, 1);
  assert.equal(checks(), before, 'the whitelist check was not redone');
});

test('a run by hand leaves its clone as it was: no ref to the data branch, no gc', async () => {
  const { clone, origin, state } = scratch('by-hand');
  await runJob({ cwd: clone, stateDir: state, now: later(6), secrets: SECRETS, fetch: fakeApis(world()).fetch, push: true, deploy: async () => {} });
  const dev = join(dir, 'by-hand', 'dev');
  git(join(dir, 'by-hand'), 'clone', '-q', origin, dev);
  git(dev, 'update-ref', '-d', 'refs/remotes/origin/data');
  // A developer's unreachable object, three days old: their own gc expiry decides when it goes
  const dangling = git(dev, 'commit-tree', git(dev, 'write-tree'), '-m', 'dropped stash');
  const loose = join(dev, '.git', 'objects', dangling.slice(0, 2), dangling.slice(2));
  const old = new Date(Date.now() - 3 * 24 * 3600e3);
  utimesSync(loose, old, old);
  await runJob({ cwd: dev, stateDir: join(dir, 'by-hand', 'dev-state'), now: later(7), secrets: SECRETS, fetch: fakeApis(world()).fetch, push: false });
  assert.equal(spawnSync('git', ['rev-parse', '-q', '--verify', 'refs/remotes/origin/data'], { cwd: dev }).status, 1);
  assert.equal(has(dev, dangling), true);
});

test('a data commit the run did not make (a push that landed before its error) still gets its deploy', async () => {
  const { clone, origin, state } = scratch('landed');
  const data = world();
  let deploys = 0;
  const options = { cwd: clone, stateDir: state, secrets: SECRETS, fetch: fakeApis(data).fetch, push: true, deploy: async () => deploys++ };
  await runJob({ ...options, now: later(6) });
  // The commit lands on origin, but this state never learns of it, as when the push's answer is lost
  const other = join(dir, 'landed', 'other');
  git(join(dir, 'landed'), 'clone', '-q', origin, other);
  publishData(other, new Map([[VODS_FILE, '{"vods":[]}\n']]), { base: fetchData(other), now: later(6.6), minGapMs: 0 });
  await runJob({ ...options, now: later(6.75) });
  assert.equal(deploys, 2);
  await runJob({ ...options, now: later(7) });
  assert.equal(deploys, 2, 'and only once');
});

test('the first push landing before its error still gets its deploy', async () => {
  const { clone, origin, state } = scratch('first-landed');
  let deploys = 0;
  const options = { cwd: clone, stateDir: state, secrets: SECRETS, fetch: fakeApis(world()).fetch, push: true, deploy: async () => deploys++ };
  // A stale lock makes the local ref update after the push fail: the commit is on origin, the run sees an error
  const lock = join(clone, '.git', 'refs', 'remotes', 'origin', 'data.lock');
  mkdirSync(join(lock, '..'), { recursive: true });
  writeFileSync(lock, '');
  const first = await runJob({ ...options, now: later(6) });
  assert.equal(first.published.status, 'push failed');
  assert.doesNotThrow(() => git(origin, 'rev-parse', 'data'));
  assert.equal(deploys, 0);
  rmSync(lock);
  await runJob({ ...options, now: later(6.25) });
  assert.equal(deploys, 1);
});

test('a failed deploy start is tried again on the next run, even with nothing new to publish', async () => {
  const { clone, state } = scratch('deploy-retry');
  const apis = fakeApis(world());
  let fails = true;
  let deploys = 0;
  const deploy = async () => {
    if (fails) throw new Error('HTTP 403');
    deploys++;
  };
  const options = { cwd: clone, stateDir: state, secrets: SECRETS, fetch: apis.fetch, push: true, deploy };
  const first = await runJob({ ...options, now: later(6) });
  assert.match(first.errors.join('\n'), /deploy: HTTP 403/);
  fails = false;
  const second = await runJob({ ...options, now: later(6.25) });
  assert.equal(second.published.status, 'unchanged');
  assert.deepEqual(second.errors, []);
  assert.equal(deploys, 1);
});

test('a linked video is re-read within 7 days, and dropped from the data once YouTube no longer returns it', async () => {
  const { clone, origin, state } = scratch('refresh');
  const data = world();
  const apis = fakeApis(data);
  const options = { cwd: clone, stateDir: state, secrets: SECRETS, fetch: apis.fetch, push: true, deploy: async () => {} };
  await runJob({ ...options, now: later(6) });
  const videoCalls = () => apis.calls.filter((call) => call.endpoint === 'videos').map((call) => call.params.id);
  const before = videoCalls().length;
  await runJob({ ...options, now: later(6 + 24 * 6) });
  assert.equal(videoCalls().length, before, 'no refresh before 7 days');
  data.uploads[ESL_ARCHIVES] = [];
  const run = await runJob({ ...options, now: later(6 + 24 * 7) });
  assert.ok(videoCalls().slice(before).some((ids) => ids.split(',').includes('vod1')));
  assert.deepEqual(run.errors, []);
  assert.deepEqual(dataFile(origin, VODS_FILE), { vods: [] });
  assert.ok(!readFileSync(join(state, 'state.json'), 'utf8').includes('vod1'));
});

test('a whitelisted channel its parent no longer features is not read, and the run says so', async () => {
  const { clone, state } = scratch('whitelist');
  const data = { ...world(), featured: {} };
  const apis = fakeApis(data);
  const run = await runJob({ cwd: clone, stateDir: state, now: later(6), secrets: SECRETS, fetch: apis.fetch, push: false });
  assert.match(run.errors.join('\n'), /ESL Archives .* is no longer read: ESL Counter-Strike no longer features it/);
  const read = apis.calls.filter((call) => call.endpoint === 'playlistItems').map((call) => call.params.playlistId);
  assert.ok(!read.includes(`UU${ESL_ARCHIVES.slice(2)}`) && !read.includes(`UU${PGL_VODS.slice(2)}`));
  // StarLadder's VODs are on its main channel, which no other channel has to vouch for
  assert.ok(read.includes('UUx1kGL8Qox1jEiHRMsnn9Ag'));
  assert.deepEqual(JSON.parse(readFileSync(join(state, 'out', 'vods.json'), 'utf8')), { vods: [] });
});

test('PandaScore down: the stored matches stay, the VOD side still runs, and the run says so', async () => {
  const { clone, origin, state } = scratch('pandascore-down');
  const data = world();
  const apis = fakeApis(data);
  const options = { cwd: clone, stateDir: state, secrets: SECRETS, fetch: apis.fetch, push: true, deploy: async () => {} };
  await runJob({ ...options, now: later(6) });
  data.fail = ['matches'];
  const run = await runJob({ ...options, now: later(7) });
  assert.match(run.errors.join('\n'), /PandaScore/);
  assert.equal(run.published.status, 'unchanged');
  assert.equal(dataFile(origin, MATCHES_FILE).matches.length, 2);
  assert.ok(!run.errors.join('\n').includes('ps-secret'));
});

test('PandaScore down: no new VOD is linked, so a rematch it has not reported yet keeps its VOD', async () => {
  const { clone, origin, state } = scratch('pandascore-down-rematch');
  const data = world();
  data.uploads[ESL_ARCHIVES] = [];
  const apis = fakeApis(data);
  const options = { cwd: clone, stateDir: state, secrets: SECRETS, fetch: apis.fetch, push: true, deploy: async () => {} };
  await runJob({ ...options, now: later(6) });
  // The first meeting has no VOD; the rematch is played while PandaScore is down, and its VOD goes up
  data.matches.push(rawMatch({ id: 3, start: later(30).toISOString() }));
  data.uploads[ESL_ARCHIVES] = [upload({ id: 'rematch', title: 'NAVI vs. Falcons - IEM Beijing 2026 - Group A', publishedAt: later(33).toISOString() })];
  data.fail = ['matches'];
  const down = await runJob({ ...options, now: later(34) });
  assert.match(down.errors.join('\n'), /PandaScore/);
  assert.deepEqual(dataFile(origin, VODS_FILE).vods, []);
  data.fail = [];
  await runJob({ ...options, now: later(35) });
  assert.deepEqual(dataFile(origin, VODS_FILE).vods.map((link) => [link.matchId, link.videoId]), [[3, 'rematch']]);
});

/** The job's command in a scratch copy of the repository's tools, with its own state folder and env file. */
function command(name) {
  const root = join(dir, name);
  mkdirSync(join(root, 'repo', 'tools'), { recursive: true });
  cpSync(join(REPO, 'tools', 'data-job'), join(root, 'repo', 'tools', 'data-job'), { recursive: true });
  for (const domain of ['site', 'matches', 'vods']) cpSync(join(REPO, 'app', 'domains', domain), join(root, 'repo', 'app', 'domains', domain), { recursive: true });
  return { repo: join(root, 'repo'), state: join(root, 'state'), env: join(root, 'data-job.env') };
}

const runCommand = (repo, args, env) => spawnSync('node', ['tools/data-job/run.mjs', ...args], { cwd: repo, encoding: 'utf8', env: { ...process.env, ...env } });

test('two runs at once: the second one logs that it skipped, quietly, and publishes nothing', async () => {
  const { repo, state } = command('lock');
  mkdirSync(state, { recursive: true });
  const lock = join(state, 'run.lock');
  // flock holding the lock stands in for the running job (its own process group, so the kill below ends sleep too)
  const holder = spawn('flock', [lock, 'sleep', '30'], { stdio: 'ignore', detached: true });
  while (spawnSync('flock', ['--nonblock', lock, 'true']).status === 0) await new Promise((done) => setTimeout(done, 20));
  const result = runCommand(repo, [], { PCESPORTS_DATA_JOB_STATE: state });
  process.kill(-holder.pid);
  assert.equal(result.status, 0, result.stderr);
  assert.equal(result.stdout + result.stderr, '');
  assert.match(readFileSync(join(state, 'job.log'), 'utf8'), /skipped: another run holds the lock/);
});

test('the env file must live outside the repository and be readable by its owner only', () => {
  const { repo, state, env } = command('env');
  const noFile = runCommand(repo, ['--no-push', '--env', join(dir, 'env', 'missing.env')], { PCESPORTS_DATA_JOB_STATE: state, PANDASCORE_TOKEN: 'ps-secret', YOUTUBE_API_KEY: 'yt-secret' });
  assert.equal(noFile.status, 1);
  assert.match(noFile.stderr, /no env file at .*: the secrets come from there only/);
  const lookalike = join(repo, '..env');
  writeFileSync(lookalike, 'PANDASCORE_TOKEN=ps-secret\n', { mode: 0o600 });
  assert.match(runCommand(repo, ['--no-push', '--env', lookalike], { PCESPORTS_DATA_JOB_STATE: state }).stderr, /inside the repository/);
  const inside = join(repo, 'data-job.env');
  writeFileSync(inside, 'PANDASCORE_TOKEN=ps-secret\n', { mode: 0o600 });
  const refused = runCommand(repo, ['--no-push', '--env', inside], { PCESPORTS_DATA_JOB_STATE: state });
  assert.equal(refused.status, 1);
  assert.match(refused.stderr, /inside the repository/);
  writeFileSync(env, 'PANDASCORE_TOKEN=ps-secret\nYOUTUBE_API_KEY=yt-secret\n');
  chmodSync(env, 0o644);
  const open = runCommand(repo, ['--no-push', '--env', env], { PCESPORTS_DATA_JOB_STATE: state });
  assert.equal(open.status, 1);
  assert.match(open.stderr, /readable by others: chmod 600/);
  chmodSync(env, 0o600);
  writeFileSync(env, 'PANDASCORE_TOKEN=ps-secret\n');
  // A secret the file leaves out is not taken from the shell
  const missing = runCommand(repo, ['--no-push', '--env', env], { PCESPORTS_DATA_JOB_STATE: state, YOUTUBE_API_KEY: 'shell-yt-key' });
  assert.equal(missing.status, 1);
  assert.match(missing.stderr, /YOUTUBE_API_KEY is not set/);
  assert.ok(!(missing.stderr + missing.stdout).includes('ps-secret'));
  // Whatever the job prints is redacted, even its own messages
  writeFileSync(env, 'PANDASCORE_TOKEN=ps-secret\nYOUTUBE_API_KEY=yt-secret\n');
  const echoed = runCommand(repo, ['--no-push', '--env', env, '--since', 'ps-secret'], { PCESPORTS_DATA_JOB_STATE: state });
  assert.equal(echoed.status, 1);
  assert.match(echoed.stderr, /--since <PANDASCORE_TOKEN> is not a date/);
  assert.ok(!echoed.stderr.includes('ps-secret'));
});

test('publishing runs only on master with no changes', () => {
  const { repo, state } = command('branch');
  git(dir, 'init', '-q', '-b', 'feature', repo);
  git(repo, 'add', '-A');
  git(repo, 'commit', '-q', '-m', 'test: scratch');
  const result = runCommand(repo, [], { PCESPORTS_DATA_JOB_STATE: state });
  assert.equal(result.status, 1);
  assert.match(result.stderr, /publishing runs on master only, and this checkout is on feature/);
});

/** A clone with the domains' folders and pull.mjs, whose origin has (or lacks) a data branch. */
function pullScratch(name, files) {
  const { clone, origin } = scratch(name);
  mkdirSync(join(clone, 'tools'), { recursive: true });
  cpSync(join(REPO, 'tools', 'data-job'), join(clone, 'tools', 'data-job'), { recursive: true });
  for (const domain of ['matches', 'vods']) {
    mkdirSync(join(clone, 'app', 'domains', domain), { recursive: true });
    writeFileSync(join(clone, 'app', 'domains', domain, 'domain.json'), '{}');
  }
  if (files) {
    const work = join(dir, name, 'data-work');
    git(join(dir, name), 'clone', '-q', origin, work);
    git(work, 'switch', '-q', '--orphan', 'data');
    for (const [path, text] of Object.entries(files)) {
      mkdirSync(join(work, path, '..'), { recursive: true });
      writeFileSync(join(work, path), text);
    }
    git(work, 'add', '-A');
    git(work, 'commit', '-q', '-m', 'data');
    git(work, 'push', '-q', 'origin', 'data');
  }
  return clone;
}

const pull = (clone) => spawnSync('node', ['tools/data-job/pull.mjs'], { cwd: clone, encoding: 'utf8' });

test('pull.mjs puts the data branch files into the domains, replacing what an earlier pull left', () => {
  const clone = pullScratch('pull', { [VODS_FILE]: '{"vods":[]}\n' });
  mkdirSync(join(clone, 'app', 'domains', 'matches', 'data'), { recursive: true });
  writeFileSync(join(clone, MATCHES_FILE), '{"stale":true}\n');
  const result = pull(clone);
  assert.equal(result.status, 0, result.stderr);
  assert.equal(readFileSync(join(clone, VODS_FILE), 'utf8'), '{"vods":[]}\n');
  assert.equal(existsSync(join(clone, MATCHES_FILE)), false);
});

test('pull.mjs takes no code from the data branch: anything but a domain data file stops it, and nothing is written', () => {
  for (const [name, files] of [
    ['pull-code', { [VODS_FILE]: '{}', 'tools/deploy/deploy.mjs': 'process.exit(0)' }],
    ['pull-notjson', { [VODS_FILE]: 'not json' }],
    ['pull-nodomain', { 'app/domains/evil/data/x.json': '{}' }],
  ]) {
    const clone = pullScratch(name, files);
    const result = pull(clone);
    assert.equal(result.status, 1, name);
    assert.match(result.stderr, /Data pull stopped/);
    assert.equal(existsSync(join(clone, VODS_FILE)), false, name);
  }
});

test('pull.mjs drops the data commits it fetched before, even packed in a fresh clone', () => {
  const clone = pullScratch('pull-prune', { [VODS_FILE]: '{"vods":[]}\n' });
  const origin = join(dir, 'pull-prune', 'origin.git');
  const live = join(dir, 'pull-prune', 'live');
  git(join(dir, 'pull-prune'), 'clone', '-q', `file://${origin}`, live);
  cpSync(join(clone, 'tools'), join(live, 'tools'), { recursive: true });
  cpSync(join(clone, 'app'), join(live, 'app'), { recursive: true });
  assert.equal(pull(live).status, 0);
  const old = git(live, 'rev-parse', 'refs/remotes/origin/data');
  // The data job publishes a new data commit, and the next deploy pulls it
  const files = new Map([[VODS_FILE, '{"vods":[{"matchId":1}]}\n']]);
  publishData(clone, files, { base: fetchData(clone), now: new Date(), minGapMs: 0 });
  for (const to of ['HEAD', old]) git(live, 'update-ref', '--create-reflog', 'refs/remotes/origin/data', to);
  const result = spawnSync('node', ['tools/data-job/pull.mjs'], { cwd: live, encoding: 'utf8', env: { ...process.env, PCESPORTS_DATA_PRUNE_EXPIRE: 'now' } });
  assert.equal(result.status, 0, result.stderr);
  assert.equal(readFileSync(join(live, VODS_FILE), 'utf8'), '{"vods":[{"matchId":1}]}\n');
  assert.equal(existsSync(reflog(live)), false);
  assert.equal(has(live, old), false, 'the deploy clone keeps no replaced data commit');
});

test('pull.mjs builds without data when origin has no data branch yet, and stops when origin cannot be read', () => {
  const clone = pullScratch('pull-none');
  // A branch whose name only ends in `data` is not the data branch
  git(clone, 'push', '-q', 'origin', 'HEAD:refs/heads/fix/data');
  const none = pull(clone);
  assert.equal(none.status, 0, none.stderr);
  assert.match(none.stdout, /No data branch on origin yet/);
  mkdirSync(join(clone, 'app', 'domains', 'vods', 'data'), { recursive: true });
  writeFileSync(join(clone, VODS_FILE), '{"old":true}\n');
  git(clone, 'remote', 'set-url', 'origin', join(dir, 'missing.git'));
  const broken = pull(clone);
  assert.equal(broken.status, 1);
  assert.equal(existsSync(join(clone, VODS_FILE)), false, 'the last data is gone, so no build ships it');
});
