// tools/deploy/deploy.mjs and ssh.sh, the forced command, in a scratch live clone of a scratch origin. Only what runs
// no build: the refusals, the lock and the dry run (a real deploy runs npm ci and nuxt generate), and the pruning of
// old releases on its own (tools/deploy/prune.mjs).
import assert from 'node:assert/strict';
import { spawn, spawnSync } from 'node:child_process';
import { chmodSync, cpSync, existsSync, mkdirSync, mkdtempSync, readdirSync, rmSync, symlinkSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, resolve } from 'node:path';
import { after, before, test } from 'node:test';
import { pruneReleases } from '../tools/deploy/prune.mjs';

const REPO = resolve(import.meta.dirname, '..');
let dir;
let clone;

function sh(command, args, cwd, env = {}) {
  return spawnSync(command, args, { cwd, encoding: 'utf8', env: { ...process.env, ...env } });
}

function git(cwd, ...args) {
  const result = sh('git', ['-c', 'user.name=Test', '-c', 'user.email=test@example.com', ...args], cwd);
  assert.equal(result.status, 0, result.stderr);
  return result.stdout.trim();
}

before(() => {
  dir = mkdtempSync(join(tmpdir(), 'pce-deploy-'));
  const work = join(dir, 'work');
  mkdirSync(join(work, 'tools'), { recursive: true });
  cpSync(join(REPO, 'tools', 'deploy'), join(work, 'tools', 'deploy'), { recursive: true });
  cpSync(join(REPO, 'tools', 'data-job'), join(work, 'tools', 'data-job'), { recursive: true });
  cpSync(join(REPO, 'package.json'), join(work, 'package.json'));
  cpSync(join(REPO, '.gitignore'), join(work, '.gitignore'));
  git(dir, 'init', '-q', '-b', 'master', work);
  git(work, 'add', '-A');
  git(work, 'commit', '-q', '-m', 'test: scratch site');
  git(work, 'tag', '-a', '6.0.0', '-m', '6.0.0');
  git(dir, 'clone', '-q', '--bare', work, join(dir, 'origin.git'));
  clone = join(dir, 'live');
  git(dir, 'clone', '-q', join(dir, 'origin.git'), clone);
});

after(() => rmSync(dir, { recursive: true, force: true }));

test('the dry run checks, prints every step and runs none', () => {
  const result = sh('node', ['tools/deploy/deploy.mjs', '--dry-run'], clone);
  assert.equal(result.status, 0, result.stderr);
  for (const step of ['git fetch origin master', 'git merge --ff-only origin/master', 'node tools/data-job/pull.mjs', 'npm ci', 'npm run generate', 'node tools/check-build.mjs .output/public']) {
    assert.match(result.stdout, new RegExp(`> ${step.replace(/[.]/g, '\\.')}`));
  }
  assert.match(result.stdout, /Dry run: nothing ran\./);
  for (const left of ['site', '.releases', 'node_modules', '.deploy.lock']) assert.equal(existsSync(join(clone, left)), false, left);
});

test('npm run deploy --dry-run without -- is a dry run too', () => {
  const result = sh('node', ['tools/deploy/deploy.mjs'], clone, { npm_config_dry_run: 'true' });
  assert.equal(result.status, 0, result.stderr);
  assert.match(result.stdout, /Dry run: nothing ran\./);
});

test('the forced command runs the dry run for `dry-run`, after reaching origin', () => {
  // Started from another folder, as an SSH session starts in the home folder: it runs in the clone it lives in
  const inClone = sh('sh', [join(clone, 'tools', 'deploy', 'ssh.sh')], dir, { SSH_ORIGINAL_COMMAND: 'dry-run' });
  assert.equal(inClone.status, 0, inClone.stderr);
  assert.match(inClone.stdout, /Dry run: nothing ran\./);
});

test('the forced command stops when origin cannot be read', () => {
  const broken = join(dir, 'no-origin');
  git(dir, 'clone', '-q', join(dir, 'origin.git'), broken);
  git(broken, 'remote', 'set-url', 'origin', join(dir, 'missing.git'));
  const result = sh('sh', [join(broken, 'tools', 'deploy', 'ssh.sh')], dir, { SSH_ORIGINAL_COMMAND: 'dry-run' });
  assert.equal(result.status, 1);
  assert.match(result.stderr, /git cannot read origin from this shell/);
});

test('it refuses another branch', () => {
  git(clone, 'switch', '-q', '-c', 'feature');
  const result = sh('node', ['tools/deploy/deploy.mjs', '--dry-run'], clone);
  git(clone, 'switch', '-q', 'master');
  assert.equal(result.status, 1);
  assert.match(result.stderr, /deploy from master only, and this checkout is on feature/);
});

test('it refuses a checkout with changes', () => {
  writeFileSync(join(clone, 'stray.txt'), 'not reviewed');
  const result = sh('node', ['tools/deploy/deploy.mjs', '--dry-run'], clone);
  rmSync(join(clone, 'stray.txt'));
  assert.equal(result.status, 1);
  assert.match(result.stderr, /the checkout has changes/);
});

test('it refuses a master head that is no release: no X.Y.Z tag on origin', () => {
  // A merge pushed to master whose release commit hasn't landed, or whose release failed
  const work = join(dir, 'unreleased-work');
  git(dir, 'clone', '-q', join(dir, 'origin.git'), work);
  git(work, 'commit', '-q', '--allow-empty', '-m', 'Merge pull request #9 from me/dev');
  git(work, 'tag', 'v6.0.1');
  git(dir, 'clone', '-q', '--bare', work, join(dir, 'unreleased-origin.git'));
  const live = join(dir, 'unreleased-live');
  git(dir, 'clone', '-q', join(dir, 'unreleased-origin.git'), live);
  const unreleased = sh('node', ['tools/deploy/deploy.mjs', '--dry-run'], live);
  assert.equal(unreleased.status, 1);
  assert.match(unreleased.stderr, /master at \w{7} is no release \(no X\.Y\.Z tag on it\)/);
  assert.doesNotMatch(unreleased.stdout, /npm ci/);
  // Tagged on origin only (the release job's push), it ships
  git(work, 'tag', '6.0.1');
  git(work, 'push', '-q', join(dir, 'unreleased-origin.git'), '6.0.1');
  assert.equal(sh('node', ['tools/deploy/deploy.mjs', '--dry-run'], live).status, 0);
});

test('it refuses a site folder that is not a link to a release', () => {
  mkdirSync(join(clone, 'site'));
  const result = sh('node', ['tools/deploy/deploy.mjs', '--dry-run'], clone);
  rmSync(join(clone, 'site'), { recursive: true });
  assert.equal(result.status, 1);
  assert.match(result.stderr, /is not a link to a release/);
});

test('it refuses a second deploy while one runs', async () => {
  // flock holding the lock stands in for the running deploy
  // (its own process group, so the kill below ends sleep too, which holds the lock as well)
  const holder = spawn('flock', [join(clone, '.deploy.lock'), 'sleep', '30'], { stdio: 'ignore', detached: true });
  while (sh('flock', ['--nonblock', join(clone, '.deploy.lock'), 'true'], clone).status === 0) await new Promise((done) => setTimeout(done, 20));
  const result = sh('node', ['tools/deploy/deploy.mjs'], clone);
  process.kill(-holder.pid);
  assert.equal(result.status, 1);
  assert.match(result.stderr, /another deploy is running/);
  assert.equal(existsSync(join(clone, 'node_modules')), false);
});

test('a lock file left by a stopped deploy does not stop the next', () => {
  writeFileSync(join(clone, '.deploy.lock'), '12345');
  // The scratch site has no package-lock.json, so this deploy gets past the lock, the pull and the data (origin has
  // no data branch), then stops at npm ci
  const result = sh('node', ['tools/deploy/deploy.mjs'], clone);
  rmSync(join(clone, '.deploy.lock'), { force: true });
  assert.equal(result.status, 1);
  assert.doesNotMatch(result.stderr, /another deploy is running/);
  assert.match(result.stderr, /npm ci failed/);
  assert.match(result.stdout, /> git merge --ff-only origin\/master/);
  assert.match(result.stdout, /No data branch on origin yet: building without data\./);
});

test('git ignores what a deploy writes, so the next deploy sees a clean checkout', () => {
  mkdirSync(join(clone, '.releases', 'r1'), { recursive: true });
  symlinkSync('.releases/r1', join(clone, 'site'));
  writeFileSync(join(clone, '.deploy.lock'), '1');
  mkdirSync(join(clone, '.output', 'public'), { recursive: true });
  // A challenge token certbot left in its webroot (tools/nginx/nginx.prod.conf)
  mkdirSync(join(clone, 'acme', '.well-known', 'acme-challenge'), { recursive: true });
  writeFileSync(join(clone, 'acme', '.well-known', 'acme-challenge', 'token'), 'x');
  const status = git(clone, 'status', '--porcelain');
  rmSync(join(clone, 'acme'), { recursive: true });
  rmSync(join(clone, '.releases'), { recursive: true });
  rmSync(join(clone, 'site'));
  rmSync(join(clone, '.deploy.lock'));
  rmSync(join(clone, '.output'), { recursive: true });
  assert.equal(status, '');
});

/** A scratch .releases folder holding `names`, each with an index.html. */
function releases(name, names) {
  const folder = join(dir, name);
  for (const release of names) {
    mkdirSync(join(folder, release), { recursive: true });
    writeFileSync(join(folder, release, 'index.html'), release);
  }
  return folder;
}

test('the prune keeps the release shown and the two newest besides it, and deletes the rest, a stopped build included', () => {
  const folder = releases('prune-plain', ['r1', 'r2', 'r3', 'r4', 'r5', '.building-r6']);
  assert.deepEqual(pruneReleases(folder, 'r5', 2), []);
  assert.deepEqual(readdirSync(folder).sort(), ['r3', 'r4', 'r5']);
});

// A folder the deploy user can't write into stands in for certbot's root-owned .well-known: deleting what is in it
// fails with EACCES, as on max-cloud. Root can delete it anyway, so the tests that use it skip as root.
function stuckReleases(name) {
  const folder = releases(name, ['r1', 'r2', 'r3', 'r4', 'r5']);
  const stuck = join(folder, 'r1', '.well-known');
  mkdirSync(join(stuck, 'acme-challenge'), { recursive: true });
  chmodSync(stuck, 0o555);
  return { folder, stuck };
}

test('a release another user\'s file keeps on disk is named with that file and its owner; the other old ones go', (t) => {
  if (process.getuid() === 0) return t.skip('root can delete anything');
  const { folder, stuck } = stuckReleases('prune-foreign');
  try {
    const failures = pruneReleases(folder, 'r5', 2, { ownerOf: (path) => (path === stuck ? 0 : process.getuid()) });
    assert.equal(failures.length, 1);
    assert.match(failures[0], new RegExp(`^${join(folder, 'r1')}: EACCES\\. ${stuck} \\(owned by root\\) is not the deploy user's`));
    assert.match(failures[0], new RegExp(`sudo rm -rf ${join(folder, 'r1')}\``));
    assert.match(failures[0], /certbot/);
    assert.deepEqual(readdirSync(folder).sort(), ['r1', 'r3', 'r4', 'r5']);
  } finally {
    chmodSync(stuck, 0o755);
  }
});

test('a release the deploy user owns but can\'t delete is named, and the message says no other user is involved', (t) => {
  if (process.getuid() === 0) return t.skip('root can delete anything');
  const { folder, stuck } = stuckReleases('prune-own');
  try {
    const failures = pruneReleases(folder, 'r5', 2);
    assert.equal(failures.length, 1);
    assert.match(failures[0], new RegExp(`^${join(folder, 'r1')}: EACCES.*No file in it belongs to another user`));
  } finally {
    chmodSync(stuck, 0o755);
  }
});

test('a release with a folder the deploy user can\'t read never claims that no other user owns a file in it', (t) => {
  if (process.getuid() === 0) return t.skip('root can read anything');
  const folder = releases('prune-unread', ['r1', 'r2', 'r3', 'r4']);
  const unread = join(folder, 'r1', '.well-known');
  mkdirSync(join(unread, 'acme-challenge'), { recursive: true });
  writeFileSync(join(unread, 'acme-challenge', 'token'), 'x');
  chmodSync(unread, 0o300);
  try {
    const failures = pruneReleases(folder, 'r4', 2);
    assert.equal(failures.length, 1);
    assert.match(failures[0], new RegExp(`can't read ${unread}, so whose files are in it is unknown: a file there may be another user's`));
    assert.doesNotMatch(failures[0], /No file in it belongs to another user/);
  } finally {
    chmodSync(unread, 0o755);
  }
});

test('a whole deploy refuses an untagged head, pulled or not, and builds nothing', () => {
  // The live clone at the tagged release; origin then gets a merge whose release run hasn't tagged it yet
  const work = join(dir, 'pulled-work');
  git(dir, 'clone', '-q', join(dir, 'origin.git'), work);
  git(dir, 'clone', '-q', '--bare', work, join(dir, 'pulled-origin.git'));
  const live = join(dir, 'pulled-live');
  git(dir, 'clone', '-q', join(dir, 'pulled-origin.git'), live);
  git(work, 'commit', '-q', '--allow-empty', '-m', 'Merge pull request #9 from me/dev');
  git(work, 'push', '-q', join(dir, 'pulled-origin.git'), 'master');
  const bin = join(dir, 'pulled-bin');
  mkdirSync(bin);
  writeFileSync(join(bin, 'npm'), '#!/bin/sh\necho "npm $*" >> "$0.log"\n', { mode: 0o755 });
  // The dry run, which pulls nothing, refuses it too: it checks the head a deploy would pull, not the tagged one it is on
  const dry = sh('node', ['tools/deploy/deploy.mjs', '--dry-run'], live);
  assert.equal(dry.status, 1, dry.stdout);
  assert.match(dry.stderr, /is no release/);
  const result = sh('node', ['tools/deploy/deploy.mjs'], live, { PATH: `${bin}:${process.env.PATH}` });
  assert.equal(result.status, 1, result.stderr);
  assert.match(result.stderr, /master at \w{7} is no release \(no X\.Y\.Z tag on it\)/);
  assert.equal(git(live, 'log', '-1', '--format=%s'), 'Merge pull request #9 from me/dev');
  assert.equal(existsSync(join(bin, 'npm.log')), false);
  // The next deploy (the data job's, while the release is still broken) pulls nothing new and refuses the same
  const again = sh('node', ['tools/deploy/deploy.mjs'], live, { PATH: `${bin}:${process.env.PATH}` });
  assert.equal(again.status, 1, again.stderr);
  assert.match(again.stderr, /is no release/);
  assert.equal(existsSync(join(bin, 'npm.log')), false);
  for (const left of ['site', '.releases']) assert.equal(existsSync(join(live, left)), false, left);
});

test('a whole deploy whose prune fails switches the site, then ends red and says so', (t) => {
  if (process.getuid() === 0) return t.skip('root can delete anything');
  // A site whose build is a stub: an npm that only writes .output/public, and a build check that passes
  const work = join(dir, 'full-work');
  git(dir, 'clone', '-q', join(dir, 'origin.git'), work);
  writeFileSync(join(work, 'tools', 'check-build.mjs'), '');
  git(work, 'add', '-A');
  git(work, 'commit', '-q', '-m', 'test: a build check that passes');
  git(work, 'tag', '6.0.1');
  git(dir, 'clone', '-q', '--bare', work, join(dir, 'full-origin.git'));
  const live = join(dir, 'full-live');
  git(dir, 'clone', '-q', join(dir, 'full-origin.git'), live);
  const bin = join(dir, 'full-bin');
  mkdirSync(bin);
  writeFileSync(join(bin, 'npm'), '#!/bin/sh\nif [ "$1" = run ] && [ "$2" = generate ]; then mkdir -p .output/public && echo built > .output/public/index.html; fi\n', { mode: 0o755 });
  const { stuck } = stuckReleases(join('full-live', '.releases'));
  symlinkSync('.releases/r5', join(live, 'site'));
  try {
    const result = sh('node', ['tools/deploy/deploy.mjs'], live, { PATH: `${bin}:${process.env.PATH}` });
    assert.equal(result.status, 1, result.stderr);
    assert.match(result.stderr, /Deployed master at \w+ into \.releases\/[^,]+, and the site shows it, but an older release could not be deleted:\n- .*\/r1: EACCES/);
    assert.doesNotMatch(result.stdout, /Deployed master/);
    const shown = sh('readlink', [join(live, 'site')], live).stdout.trim();
    assert.notEqual(shown, '.releases/r5');
    assert.deepEqual(readdirSync(join(live, '.releases')).sort(), ['r1', 'r4', 'r5', shown.replace('.releases/', '')].sort());
  } finally {
    chmodSync(stuck, 0o755);
  }
});
