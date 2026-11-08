// The data job (README.md, "Data job"), run every 15 minutes by cron in its own clone on the factory machine:
//   node tools/data-job/run.mjs [--no-push] [--since <date>] [--env-file <path>]
// Under a lock, so two runs never overlap: the second one logs that it skipped and stops. With nothing changed it
// prints nothing; problems go to stderr, and every run adds a line to job.log in the state folder.
//
// Publishing (the default) needs the clone on master with no changes. It pulls master first (fast-forward only) and
// runs the job it pulled, then publishes the data branch and starts the deploy: `gh workflow run ci.yml --ref master`.
// --no-push runs anywhere and writes the data files to the state folder's out/ instead; --since reads uploads and
// matches back to that date, for a backfill or a check.
//
// The secrets come from the env file, never from the repository: PANDASCORE_TOKEN, YOUTUBE_API_KEY and GH_TOKEN (for
// the push and the deploy; without it, git and gh use the machine's own GitHub login).
import { spawnSync } from 'node:child_process';
import { existsSync, mkdirSync, statSync } from 'node:fs';
import { homedir } from 'node:os';
import { dirname, join, relative, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { appendLog, runJob } from './job.mjs';

const ROOT = resolve(dirname(fileURLToPath(import.meta.url)), '../..');
const LOCK = join(ROOT, '.data-job.lock');
const LOCKED = 'PCESPORTS_DATA_JOB_LOCKED';
const BUSY = 75;
const SECRETS = ['PANDASCORE_TOKEN', 'YOUTUBE_API_KEY', 'GH_TOKEN'];
const DEFAULT_ENV_FILE = join(homedir(), '.config', 'pcesports', 'data-job.env');
const STATE_DIR = process.env.PCESPORTS_DATA_JOB_STATE || join(homedir(), '.local', 'state', 'pcesports-data-job');

const args = process.argv.slice(2);
const option = (name) => {
  const at = args.indexOf(name);
  return at >= 0 ? args[at + 1] : undefined;
};
const push = !args.includes('--no-push');

/** Any output, with every secret's value taken out. */
function redact(text) {
  let out = String(text);
  for (const name of SECRETS) if (process.env[name]) out = out.replaceAll(process.env[name], `<${name}>`);
  return out;
}

function fail(message) {
  console.error(redact(`Data job stopped: ${message}`));
  process.exit(1);
}

function git(...gitArgs) {
  const result = spawnSync('git', gitArgs, { cwd: ROOT, encoding: 'utf8' });
  if (result.error || result.status !== 0) fail(`git ${gitArgs.join(' ')}: ${(result.stderr || result.error?.message || '').trim()}`);
  return result.stdout.trim();
}

// One run at a time, as the deploy does it: the job runs itself again under flock(1), whose lock the kernel drops
// when that run ends, even when it is killed.
if (!process.env[LOCKED]) {
  if (spawnSync('flock', ['--version'], { stdio: 'ignore' }).error) fail('flock (util-linux) is not on PATH');
  const locked = spawnSync('flock', ['--nonblock', '--conflict-exit-code', String(BUSY), LOCK, process.execPath, fileURLToPath(import.meta.url), ...args], {
    stdio: 'inherit',
    env: { ...process.env, [LOCKED]: '1' },
  });
  if (locked.status === BUSY) {
    mkdirSync(STATE_DIR, { recursive: true });
    appendLog(STATE_DIR, `${new Date().toISOString()} skipped: another run holds the lock`);
    process.exit(0);
  }
  process.exit(locked.status ?? 1);
}

if (push && !args.includes('--pulled')) {
  const branch = git('rev-parse', '--abbrev-ref', 'HEAD');
  if (branch !== 'master') fail(`publishing runs on master only, and this checkout is on ${branch} (--no-push runs anywhere)`);
  if (git('status', '--porcelain')) fail('the checkout has changes: the job runs master as it is');
  // Pull master, then run the job it pulled, so a change to the job applies at once.
  const before = git('rev-parse', 'HEAD');
  git('fetch', '--quiet', '--no-tags', 'origin', 'master');
  git('merge', '--quiet', '--ff-only', 'origin/master');
  if (git('rev-parse', 'HEAD') !== before) {
    const again = spawnSync(process.execPath, [fileURLToPath(import.meta.url), ...args, '--pulled'], { stdio: 'inherit', env: process.env });
    process.exit(again.status ?? 1);
  }
}

// The env file: outside the repository, readable by its owner only.
const envFile = resolve(option('--env-file') || process.env.PCESPORTS_DATA_JOB_ENV || DEFAULT_ENV_FILE);
if (!relative(ROOT, envFile).startsWith('..')) fail(`the env file ${envFile} is inside the repository: keep it outside`);
if (existsSync(envFile)) {
  if (statSync(envFile).mode & 0o077) fail(`${envFile} is readable by others: chmod 600 it`);
  process.loadEnvFile(envFile);
}
for (const name of ['PANDASCORE_TOKEN', 'YOUTUBE_API_KEY']) if (!process.env[name]) fail(`${name} is not set (${envFile})`);

let since;
if (option('--since')) {
  since = new Date(option('--since'));
  if (Number.isNaN(since.getTime())) fail(`--since ${option('--since')} is not a date`);
}

function deploy() {
  const result = spawnSync('gh', ['workflow', 'run', 'ci.yml', '--ref', 'master'], { cwd: ROOT, encoding: 'utf8' });
  if (result.error || result.status !== 0) throw new Error(`gh workflow run ci.yml --ref master: ${(result.stderr || result.error?.message || '').trim()}`);
}

try {
  const { errors } = await runJob({
    cwd: ROOT,
    stateDir: STATE_DIR,
    secrets: { pandascore: process.env.PANDASCORE_TOKEN, youtube: process.env.YOUTUBE_API_KEY },
    since,
    push,
    deploy,
  });
  if (errors.length) {
    console.error(redact(`Data job finished with ${errors.length} problem${errors.length === 1 ? '' : 's'} (${join(STATE_DIR, 'report.md')}):\n${errors.map((error) => `- ${error}`).join('\n')}`));
    process.exit(1);
  }
} catch (error) {
  try {
    appendLog(STATE_DIR, redact(`${new Date().toISOString()} failed: ${error.message}`));
  } catch {
    // The state folder itself may be what failed
  }
  fail(error.message);
}
