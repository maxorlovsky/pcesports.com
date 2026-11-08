// tools/deploy/deploy.mjs and ssh.sh, the forced command, in a scratch live clone of a scratch origin. Only what runs
// no build: the refusals, the lock and the dry run (a real deploy runs npm ci and nuxt generate).
import assert from 'node:assert/strict';
import { spawn, spawnSync } from 'node:child_process';
import { cpSync, existsSync, mkdirSync, mkdtempSync, rmSync, symlinkSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, resolve } from 'node:path';
import { after, before, test } from 'node:test';

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
  const status = git(clone, 'status', '--porcelain');
  rmSync(join(clone, '.releases'), { recursive: true });
  rmSync(join(clone, 'site'));
  rmSync(join(clone, '.deploy.lock'));
  rmSync(join(clone, '.output'), { recursive: true });
  assert.equal(status, '');
});
