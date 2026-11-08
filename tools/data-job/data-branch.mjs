// The `data` branch: where the data job publishes the site's data, apart from the branches the code lives on.
// It holds data files only, never code, and always a single commit: each publish replaces it (force-push to `data`
// only), so YouTube's ids don't pile up in history (YouTube's 30-day rule, docs/feasibility/cs2-data-and-vods.md).
// The deploy (tools/deploy/deploy.mjs, through pull.mjs) builds master's code with these files.
import { spawnSync } from 'node:child_process';
import { mkdtempSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

export const DATA_BRANCH = 'data';
const REMOTE_REF = `refs/remotes/origin/${DATA_BRANCH}`;
/** The only paths the branch may hold: a domain's data folder, JSON files. Anything else stops a pull. */
export const DATA_PATH = /^app\/domains\/[a-z][a-z0-9-]*\/data\/[a-z0-9-]+\.json$/;
/** Who the data commits are by: the job, not a person. */
const IDENTITY = { name: 'PCesports data job', email: 'data-job@pcesports.invalid' };
const GAP_SLACK_MS = 2 * 60 * 1000;

export function git(cwd, args, { env = {}, input } = {}) {
  const result = spawnSync('git', args, { cwd, encoding: 'utf8', input, env: { ...process.env, ...env } });
  if (result.error || result.status !== 0) throw new Error(`git ${args.join(' ')}: ${(result.stderr || result.error?.message || '').trim()}`);
  return result.stdout.trim();
}

/** Whether origin has a `data` branch; an unreachable origin is an error, never "no". */
export function remoteHasData(cwd) {
  // The full ref: a bare `data` would also match `fix/data`.
  const result = spawnSync('git', ['ls-remote', '--exit-code', 'origin', `refs/heads/${DATA_BRANCH}`], { cwd, encoding: 'utf8' });
  if (result.status === 0) return true;
  if (result.status === 2) return false;
  throw new Error(`git ls-remote origin: ${(result.stderr || result.error?.message || '').trim()}`);
}

/** Fetches origin's `data` branch and returns its commit, or null when there is none yet. */
export function fetchData(cwd) {
  if (!remoteHasData(cwd)) {
    spawnSync('git', ['update-ref', '-d', REMOTE_REF], { cwd });
    return null;
  }
  git(cwd, ['fetch', '--quiet', '--no-tags', 'origin', `+refs/heads/${DATA_BRANCH}:${REMOTE_REF}`]);
  return git(cwd, ['rev-parse', REMOTE_REF]);
}

/** The files of a data commit, path -> text. A path outside `DATA_PATH` or a file that isn't JSON is an error. */
export function readData(cwd, commit) {
  const files = new Map();
  for (const path of git(cwd, ['ls-tree', '-r', '--name-only', '-z', commit]).split('\0').filter(Boolean)) {
    if (!DATA_PATH.test(path)) throw new Error(`the data branch holds ${path}, which is not a data file (app/domains/<domain>/data/<name>.json)`);
    const text = git(cwd, ['cat-file', 'blob', `${commit}:${path}`]);
    try {
      JSON.parse(text);
    } catch (error) {
      throw new Error(`the data branch's ${path} is not JSON: ${error.message}`, { cause: error });
    }
    files.set(path, `${text}\n`);
  }
  return files;
}

/** Writes `files` (path -> text) as a tree in the repository's object store, without touching the checkout or its index. */
function writeTree(cwd, files) {
  const dir = mkdtempSync(join(tmpdir(), 'pce-data-index-'));
  const env = { GIT_INDEX_FILE: join(dir, 'index') };
  try {
    for (const [path, text] of [...files].sort(([a], [b]) => a.localeCompare(b))) {
      if (!DATA_PATH.test(path)) throw new Error(`${path} is not a data file (app/domains/<domain>/data/<name>.json)`);
      const blob = git(cwd, ['hash-object', '-w', '--stdin'], { input: text });
      git(cwd, ['update-index', '--add', '--cacheinfo', `100644,${blob},${path}`], { env });
    }
    return git(cwd, ['write-tree'], { env });
  } finally {
    rmSync(dir, { recursive: true, force: true });
  }
}

/**
 * Publishes `files` as the `data` branch's one commit, given `base`, the branch's commit as just fetched (null when
 * there is none). Nothing happens when the files are what `base` holds (`unchanged`), or when `base` is younger than
 * `minGapMs` (`waiting`: the next run after the gap publishes whatever the data is by then). The push replaces the
 * branch only if it is still `base`, so two jobs can never both publish.
 */
export function publishData(cwd, files, { base, now, minGapMs }) {
  const tree = writeTree(cwd, files);
  if (base && git(cwd, ['rev-parse', `${base}^{tree}`]) === tree) return { status: 'unchanged', commit: base };
  if (base) {
    const last = Number(git(cwd, ['log', '-1', '--format=%ct', base])) * 1000;
    // Runs start every 15 minutes but take a few seconds each, so the gap allows for that: 30 minutes means the second
    // run after a publish, not the third.
    if (now.getTime() - last < minGapMs - GAP_SLACK_MS) return { status: 'waiting', commit: base, lastAt: new Date(last) };
  }
  const env = {
    GIT_AUTHOR_NAME: IDENTITY.name, GIT_AUTHOR_EMAIL: IDENTITY.email, GIT_AUTHOR_DATE: now.toISOString(),
    GIT_COMMITTER_NAME: IDENTITY.name, GIT_COMMITTER_EMAIL: IDENTITY.email, GIT_COMMITTER_DATE: now.toISOString(),
  };
  const commit = git(cwd, ['commit-tree', tree, '-m', `data: ${now.toISOString()}`], { env });
  git(cwd, ['push', '--quiet', `--force-with-lease=refs/heads/${DATA_BRANCH}:${base ?? ''}`, 'origin', `${commit}:refs/heads/${DATA_BRANCH}`]);
  git(cwd, ['update-ref', REMOTE_REF, commit]);
  return { status: 'pushed', commit };
}
