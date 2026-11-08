// The production deploy (README.md, "Deploy"), run in the live clone on master, whatever its folder:
//   node tools/deploy/deploy.mjs [--dry-run]   (npm run deploy, npm run deploy -- --dry-run)
// It refuses another branch, a checkout with changes and a second deploy while one runs. It pulls master
// (fast-forward only) and runs the deploy it pulled, installs with npm ci, builds the static site, checks the build,
// then points `site` (what nginx serves, tools/nginx/nginx.prod.conf) at it in one step, keeping the releases before
// it to go back to. --dry-run checks the same and prints the steps without running them.
import { spawnSync } from 'node:child_process';
import { cpSync, existsSync, lstatSync, readdirSync, readFileSync, readlinkSync, renameSync, rmSync, symlinkSync, writeFileSync } from 'node:fs';
import { basename, dirname, join, relative, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = resolve(dirname(fileURLToPath(import.meta.url)), '../..');
const BRANCH = 'master';
/** What nginx serves: a link to the newest release. */
const SITE = join(ROOT, 'site');
/** Each deploy's build, a folder of its own, so the link moves to a finished build in one step. */
const RELEASES = join(ROOT, '.releases');
/** Releases kept besides the one the site shows, to go back to (README.md, "Going back"). */
const KEEP = 2;
/** Holds the running deploy's process id, so a second deploy started meanwhile stops before it touches anything. */
const LOCK = join(ROOT, '.deploy.lock');
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

function running(pid) {
  try {
    process.kill(pid, 0);
    return true;
  } catch (error) {
    return error.code === 'EPERM';
  }
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

// One deploy at a time: two would share node_modules and .output. A lock whose process is gone (a deploy stopped
// with Ctrl-C) is taken over. The deploy run again after the pull holds its caller's.
if (!dryRun && !flags.includes('--pulled')) {
  const holder = existsSync(LOCK) ? Number(readFileSync(LOCK, 'utf8')) : 0;
  if (holder && running(holder)) fail(`another deploy is running (process ${holder}): wait for it`);
  writeFileSync(LOCK, String(process.pid));
  process.on('exit', () => rmSync(LOCK, { force: true }));
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
  const shown = basename(readlinkSync(SITE));
  const all = readdirSync(RELEASES).sort();
  const kept = all.filter((entry) => !entry.startsWith('.') && entry !== shown).slice(-KEEP);
  for (const entry of all) {
    if (entry !== shown && !kept.includes(entry)) rmSync(join(RELEASES, entry), { recursive: true, force: true });
  }
}
console.log(`\n${dryRun ? 'Dry run: nothing ran.' : `Deployed ${BRANCH} at ${sha} into ${relative(ROOT, release)}.`}`);
