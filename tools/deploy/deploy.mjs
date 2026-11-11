// The production deploy (README.md, "Deploy"), run in the live clone on master, whatever its folder:
//   node tools/deploy/deploy.mjs [--dry-run]   (npm run deploy, npm run deploy -- --dry-run)
// It refuses another branch, a checkout with changes and a second deploy while one runs. It pulls master
// (fast-forward only) and runs the deploy it pulled, which refuses a head that is no release (no X.Y.Z tag), fetches the data branch's files, installs with npm ci, builds the
// static site, checks the build, then points `site` (what nginx serves, tools/nginx/nginx.prod.conf) at it in one
// step, keeping the releases before it to go back to. --dry-run checks the same and prints the steps without running them.
// An older release it can't delete leaves the site switched but ends the run red (exit 1): a green run's log is never
// read, and the same release would stay stuck on disk deploy after deploy.
import { spawnSync } from 'node:child_process';
import { cpSync, existsSync, lstatSync, readFileSync, readlinkSync, renameSync, rmSync, symlinkSync } from 'node:fs';
import { basename, dirname, join, relative, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { pruneReleases } from './prune.mjs';

const ROOT = resolve(dirname(fileURLToPath(import.meta.url)), '../..');
const BRANCH = 'master';
/** What nginx serves: a link to the newest release. */
const SITE = join(ROOT, 'site');
/** Each deploy's build, a folder of its own, so the link moves to a finished build in one step. */
const RELEASES = join(ROOT, '.releases');
/** Releases kept besides the one the site shows, to go back to (README.md, "Going back"). */
const KEEP = 2;
/** The file flock(1) locks while a deploy runs, so a second deploy started meanwhile stops before it touches anything. */
const LOCK = join(ROOT, '.deploy.lock');
/** Set in the deploy that holds the lock. */
const LOCKED = 'PCESPORTS_DEPLOY_LOCKED';
/** flock's exit code when another deploy holds the lock. */
const BUSY = 75;
/** What `npm run generate` writes (nuxt.config.ts). */
const BUILD = join(ROOT, '.output', 'public');

const flags = process.argv.slice(2);
// `npm run deploy --dry-run`, without `--`, hands npm the flag, which only tells the script through its environment.
const dryRun = flags.includes('--dry-run') || process.env.npm_config_dry_run === 'true';
/** A release being built, which a failed step removes, so no broken build waits there to go back to. */
let unfinished = null;

function fail(message) {
  if (unfinished) rmSync(unfinished, { recursive: true, force: true });
  console.error(`\nDeploy stopped: ${message}`);
  process.exit(1);
}

function git(...args) {
  const result = spawnSync('git', args, { cwd: ROOT, encoding: 'utf8' });
  if (result.status !== 0) fail(`git ${args.join(' ')}: ${(result.stderr || result.error?.message || '').trim()}`);
  return result.stdout.trim();
}

/** Runs a step, or only prints it on a dry run. */
function run(command, args) {
  console.log(`\n> ${[command, ...args].join(' ')}`);
  if (dryRun) return;
  const result = spawnSync(command, args, { cwd: ROOT, stdio: 'inherit' });
  if (result.error || result.status !== 0) fail(`${command} ${args.join(' ')} failed${result.error ? `: ${result.error.message}` : ''}`);
}

/** Points `link` at `to` in one step: a new link renamed over the old one, so nginx never sees a half-built site. */
function point(link, to) {
  const next = `${link}.next`;
  rmSync(next, { force: true });
  symlinkSync(relative(dirname(link), to), next);
  renameSync(next, link);
}

// The live clone stays on master with nothing of its own, so what ships is what master holds.
const branch = git('rev-parse', '--abbrev-ref', 'HEAD');
if (branch !== BRANCH) fail(`deploy from ${BRANCH} only, and this checkout is on ${branch}`);
const changes = git('status', '--porcelain');
if (changes) fail(`the checkout has changes, which would ship unreviewed:\n${changes}`);

// One deploy at a time: two would share node_modules and .output. The deploy runs itself again under flock(1),
// whose lock the kernel holds until that deploy, and the one it runs after the pull, end, and drops even when one is
// killed: no lock is ever left behind. The lock file itself stays (git ignores it).
if (spawnSync('flock', ['--version'], { stdio: 'ignore' }).error) fail('flock (util-linux) is not on PATH');
if (!dryRun && !process.env[LOCKED]) {
  const locked = spawnSync('flock', ['--nonblock', '--conflict-exit-code', String(BUSY), LOCK, process.execPath, fileURLToPath(import.meta.url), ...flags], {
    cwd: ROOT,
    stdio: 'inherit',
    env: { ...process.env, [LOCKED]: '1' },
  });
  if (locked.status === BUSY) fail('another deploy is running: wait for it');
  process.exit(locked.status ?? 1);
}

// Pull master, then run the script again from what was pulled, so a change to the deploy itself applies at once.
if (!flags.includes('--pulled')) {
  run('git', ['fetch', 'origin', BRANCH]);
  const before = git('rev-parse', 'HEAD');
  run('git', ['merge', '--ff-only', `origin/${BRANCH}`]);
  if (!dryRun && git('rev-parse', 'HEAD') !== before) {
    const again = spawnSync(process.execPath, [fileURLToPath(import.meta.url), ...flags, '--pulled'], { cwd: ROOT, stdio: 'inherit' });
    process.exit(again.status ?? 1);
  }
}

// Only a release ships: master's head must carry its X.Y.Z tag (README.md, "Releasing"), so a release that failed, or
// the minute before its release commit lands, never deploys an unversioned build. Read from origin: a fetch of master
// alone may leave the tag behind.
const head = git('rev-parse', 'HEAD');
const released = git('ls-remote', '--tags', 'origin').split('\n').some((line) => {
  const [sha, ref] = line.split('\t');
  return sha === head && /^refs\/tags\/\d+\.\d+\.\d+(\^\{\})?$/.test(ref ?? '');
});
if (!released) {
  fail(`master at ${head.slice(0, 7)} is no release (no X.Y.Z tag on it): its release run deploys it once tagged, and a `
    + 'failed one needs finishing first (README.md, "Releasing")');
}

// What the steps need, before the first runs
const engines = JSON.parse(readFileSync(join(ROOT, 'package.json'), 'utf8')).engines?.node ?? '';
const needed = Number(engines.match(/\d+/)?.[0] ?? 0);
const major = Number(process.versions.node.split('.')[0]);
if (major < needed) fail(`Node.js ${process.versions.node} is too old: package.json needs ${engines}`);
if (spawnSync('npm', ['--version'], { stdio: 'ignore' }).error) fail('npm is not on PATH');
if (existsSync(SITE) && !lstatSync(SITE).isSymbolicLink()) fail(`${SITE} is not a link to a release: move it away first`);

const sha = git('rev-parse', '--short', 'HEAD');
const name = `${new Date().toISOString().replace(/[-:]/g, '').slice(0, 15)}-${sha}`;
const release = join(RELEASES, name);
// Copied under a name of its own until it is whole, so a deploy stopped halfway leaves no release that looks
// finished; the next deploy removes what it left.
const staging = join(RELEASES, `.building-${name}`);
if (existsSync(release)) fail(`${release} is there already`);

// No tests here: they ran on the change's pull request (.github/workflows/ci.yml), and this server serves other
// sites too. The build check stays: it is what stops a broken page from going live.
// The newest data first, from the data branch: whether a push to master or the data job started this deploy, it builds
// master's code with origin's data as it is now, never older data and never code from that branch.
run('node', ['tools/data-job/pull.mjs']);
run('npm', ['ci']);
run('npm', ['run', 'generate']);
run('node', ['tools/check-build.mjs', relative(ROOT, BUILD)]);
console.log(`\n> site -> ${relative(ROOT, release)}, keeping the ${KEEP} releases before it`);
if (!dryRun) {
  unfinished = staging;
  rmSync(staging, { recursive: true, force: true });
  cpSync(BUILD, staging, { recursive: true });
  renameSync(staging, release);
  unfinished = release;
  point(SITE, release);
  unfinished = null;
  const stuck = pruneReleases(RELEASES, basename(readlinkSync(SITE)), KEEP);
  if (stuck.length) {
    console.error(`\nDeployed ${BRANCH} at ${sha} into ${relative(ROOT, release)}, and the site shows it, but `
      + `${stuck.length === 1 ? 'an older release' : 'older releases'} could not be deleted:\n${stuck.map((why) => `- ${why}`).join('\n')}`);
    process.exit(1);
  }
}
console.log(`\n${dryRun ? 'Dry run: nothing ran.' : `Deployed ${BRANCH} at ${sha} into ${relative(ROOT, release)}.`}`);
