// The deploy's last step (tools/deploy/deploy.mjs): deleting the releases nobody goes back to.
import { lstatSync, readdirSync, readFileSync, rmSync } from 'node:fs';
import { basename, join } from 'node:path';

/** Files a failure names at most. */
const NAMED = 5;

/** A user's name from /etc/passwd, or `uid <n>`. */
function userName(uid) {
  try {
    const line = readFileSync('/etc/passwd', 'utf8').split('\n').find((entry) => entry.split(':')[2] === String(uid));
    if (line) return line.split(':')[0];
  } catch {
    // no /etc/passwd to read: the number says enough
  }
  return `uid ${uid}`;
}

/**
 * Who owns what in `dir` (itself included): the files `uid` doesn't own (a folder another user owns is named, not
 * entered), and the ones it can't read, whose files stay unknown.
 */
function scan(dir, uid, ownerOf, found = { foreign: [], unread: [] }) {
  if (found.foreign.length + found.unread.length >= NAMED) return found;
  let owner;
  try {
    owner = ownerOf(dir);
  } catch {
    found.unread.push(dir);
    return found;
  }
  if (owner !== uid) {
    found.foreign.push({ path: dir, owner });
    return found;
  }
  let entries = [];
  try {
    if (lstatSync(dir).isDirectory()) entries = readdirSync(dir);
  } catch {
    found.unread.push(dir);
  }
  for (const entry of entries) scan(join(dir, entry), uid, ownerOf, found);
  return found;
}

const CERTBOT = 'A root-owned .well-known is certbot\'s: its webroot belongs in the acme folder, outside the releases '
  + '(README.md, "On max-cloud")';

/** Why `path` could not be deleted, for the person who reads the deploy's log. */
function explain(path, error, uid, ownerOf) {
  const { foreign, unread } = scan(path, uid, ownerOf);
  const user = userName(uid);
  const cantRead = unread.length ? `${user} can't read ${unread.join(', ')}, so whose files are in it is unknown` : '';
  if (!foreign.length) {
    if (!cantRead) return `${path}: ${error.message}. No file in it belongs to another user: check the permissions of its folders`;
    return `${path}: ${error.message}. ${cantRead}: a file there may be another user's (\`sudo ls -laR ${path}\` shows). ${CERTBOT}`;
  }
  const list = foreign.map((file) => `${file.path} (owned by ${userName(file.owner)})`).join(', ');
  const certbot = foreign.some((file) => file.owner === 0 && basename(file.path) === '.well-known') ? `. ${CERTBOT}` : '';
  return `${path}: ${error.code ?? error.message}. ${list} ${foreign.length === 1 ? 'is' : 'are'} not the deploy user's `
    + `(${user}), who can't delete it: delete the release as root, \`sudo rm -rf ${path}\`${cantRead ? `. ${cantRead}` : ''}${certbot}`;
}

/**
 * Deletes the folders in `releases` but `shown` and the `keep` newest releases besides it (a `.`-folder is a deploy
 * stopped halfway, never kept). It tries every one and returns why each it couldn't delete failed, so one stuck
 * release never keeps the others on disk. `ownerOf` (a path's owner's uid) is there for the tests.
 */
export function pruneReleases(releases, shown, keep, { uid = process.getuid(), ownerOf = (path) => lstatSync(path).uid } = {}) {
  const all = readdirSync(releases).sort();
  const kept = all.filter((entry) => !entry.startsWith('.') && entry !== shown).slice(-keep);
  const failures = [];
  for (const entry of all) {
    if (entry === shown || kept.includes(entry)) continue;
    const path = join(releases, entry);
    try {
      rmSync(path, { recursive: true, force: true });
    } catch (error) {
      failures.push(explain(path, error, uid, ownerOf));
    }
  }
  return failures;
}
