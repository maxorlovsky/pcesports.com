#!/usr/bin/env node
// The next release version, from the commits since the last version tag (README.md, "Releasing"; the CI workflow's
// release job runs it on every push to master):
//   node tools/release/next-version.mjs [--ref <rev>] [--repo <owner/name>] [--write]
// Run from the root of the repository. Nothing in here is specific to one project.
//
// - The last release is the highest X.Y.Z tag (no "v") merged into the ref (default HEAD). The commits counted are
//   <tag>..<ref>; with no tag yet, what the ref brought in: a merge's own range (<ref>^1..<ref>^2), or the commit.
// - Level: a `type!:` subject or a `BREAKING CHANGE:` footer is major, otherwise any `feat` is minor, otherwise patch.
//   Commits whose subject starts with "Merge " (git's and GitHub's own merge messages) are left out; a merge given a
//   conventional subject (`feat!: …`) counts.
// - --repo: a release:major, release:minor or release:patch label on the pull request that the ref merged wins over
//   the commits (looked up with `gh api`, so GH_TOKEN must be set in Actions).
// - The version bumped is the higher of the last tag's and the one in the ref's package.json.
// - --write sets the version in the working tree's package.json and package-lock.json (both of its version fields).
//
// It prints the version and the reason. In GitHub Actions it also sets the step outputs `version` and
// `commit_message`, and writes both to the job summary.
import { execFileSync } from 'node:child_process';
import { appendFileSync, existsSync, readFileSync, writeFileSync } from 'node:fs';
import { join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

/** The release job skips a push whose head commit starts with this, its own release commit (a test checks they match). */
export const releaseCommitPrefix = 'chore(release): ';

const LEVELS = ['patch', 'minor', 'major'];
const VERSION = /^(\d+)\.(\d+)\.(\d+)$/;
const CONVENTIONAL = /^(\w+)(\([^)]*\))?(!)?:\s/;
const BREAKING_FOOTER = /^BREAKING[ -]CHANGE:\s/m;
const MERGE = /^Merge /;

function parseVersion(text, source) {
  const match = VERSION.exec(String(text));
  if (!match) throw new Error(`${source} has version "${text}", expected X.Y.Z (digits only, no "v")`);
  return match.slice(1).map(Number);
}

function compareVersions(first, second) {
  const [a, b] = [parseVersion(first, 'version'), parseVersion(second, 'version')];
  return a[0] - b[0] || a[1] - b[1] || a[2] - b[2];
}

function bumpVersion(version, level) {
  const [major, minor, patch] = parseVersion(version, 'version');
  if (level === 'major') return `${major + 1}.0.0`;
  if (level === 'minor') return `${major}.${minor + 1}.0`;
  return `${major}.${minor}.${patch + 1}`;
}

/** The highest X.Y.Z tag in a list of tag names, or null. */
export function lastVersionTag(tags) {
  return tags.filter((tag) => VERSION.test(tag)).sort(compareVersions).pop() || null;
}

const subjectOf = (message) => message.split('\n')[0];
const plural = (count, word) => `${count} ${word}${count === 1 ? '' : 's'}`;

/** The level the commit messages (full messages: subject, body and footers) ask for, and why. */
function levelFromCommits(messages) {
  const counted = messages.filter((message) => !MERGE.test(message));
  const breaking = counted.filter((message) => CONVENTIONAL.exec(subjectOf(message))?.[3] === '!'
    || BREAKING_FOOTER.test(message.split('\n').slice(1).join('\n')));
  const features = counted.filter((message) => CONVENTIONAL.exec(subjectOf(message))?.[1].toLowerCase() === 'feat');
  if (breaking.length) return { level: 'major', reason: `${plural(breaking.length, 'breaking change')}, e.g. "${subjectOf(breaking[0])}"` };
  if (features.length) return { level: 'minor', reason: `${plural(features.length, 'feat commit')}, e.g. "${subjectOf(features[0])}"` };
  return { level: 'patch', reason: `${plural(counted.length, 'commit')}, no feat or breaking change` };
}

/** The level a release:* label asks for, or null; two different ones fail, since nobody can tell which one was meant. */
export function levelFromLabels(labels) {
  const asked = [...new Set(labels)].filter((label) => LEVELS.some((level) => label === `release:${level}`));
  if (asked.length > 1) throw new Error(`The pull request has ${asked.join(' and ')}: keep one, then re-run the workflow`);
  return asked.length ? asked[0].slice('release:'.length) : null;
}

/** Of the pull requests GitHub associates with a commit, the one that commit merged (a direct push has none). */
export function pullRequestMergedAs(pullRequests, sha) {
  return pullRequests.find((pullRequest) => pullRequest.merge_commit_sha === sha) || null;
}

/** The next release: the version, its level and the reason for the level. */
export function nextRelease({ packageVersion, lastTag, messages, labelLevel, labelSource }) {
  parseVersion(packageVersion, 'package.json');
  const baseVersion = lastTag && compareVersions(lastTag, packageVersion) > 0 ? lastTag : packageVersion;
  const fromCommits = levelFromCommits(messages);
  const level = labelLevel || fromCommits.level;
  const reason = labelLevel
    ? `label release:${labelLevel} on ${labelSource} (the commits alone: ${fromCommits.level}, ${fromCommits.reason})`
    : fromCommits.reason;
  return { version: bumpVersion(baseVersion, level), baseVersion, level, reason };
}

/** package.json and package-lock.json with the new version, keeping their indentation; a lock file is optional. */
export function writeVersion(directory, version) {
  for (const file of ['package.json', 'package-lock.json']) {
    const path = join(directory, file);
    if (!existsSync(path)) continue;
    const text = readFileSync(path, 'utf8');
    const data = JSON.parse(text);
    const indent = (/^[ \t]+/m.exec(text) || ['  '])[0];
    data.version = version;
    if (file === 'package-lock.json' && data.packages?.['']) data.packages[''].version = version;
    writeFileSync(path, `${JSON.stringify(data, null, indent)}\n`);
  }
}

const git = (...args) => execFileSync('git', args, { encoding: 'utf8' });

function optionValue(args, name) {
  const index = args.indexOf(name);
  if (index === -1) return null;
  if (!args[index + 1] || args[index + 1].startsWith('--')) throw new Error(`${name} needs a value`);
  return args[index + 1];
}

function main(args) {
  if (args.includes('--write') && args.includes('--ref')) throw new Error('--write writes the working tree, so it takes no --ref');
  const ref = optionValue(args, '--ref') || 'HEAD';
  const repo = optionValue(args, '--repo');
  const sha = git('rev-parse', '--verify', `${ref}^{commit}`).trim();
  const packageVersion = JSON.parse(git('show', `${sha}:package.json`)).version;
  const lastTag = lastVersionTag(git('tag', '--merged', sha).split('\n'));
  const hasParent = git('rev-list', '--parents', '-n', '1', sha).trim().includes(' ');
  // With no tag yet: what the commit brought in. For a merge that's its own range (<ref>^1..<ref>^2, plus the merge
  // commit, left out by its "Merge " subject), for any other commit the commit itself
  let range = hasParent ? [`${sha}^1..${sha}`] : [sha];
  if (lastTag) range = [`${lastTag}..${sha}`];

  // Full messages, NUL-separated (-z)
  const messages = git('log', '-z', '--format=%B', ...range).split('\0').map((message) => message.trim()).filter(Boolean);
  let labelLevel = null;
  let labelSource = null;
  let pullRequestNote = 'not looked up (no --repo)';
  if (repo) {
    const pullRequests = JSON.parse(execFileSync('gh', ['api', `repos/${repo}/commits/${sha}/pulls`], { encoding: 'utf8' }));
    const pullRequest = pullRequestMergedAs(pullRequests, sha);
    pullRequestNote = 'none merged as this commit (a direct push)';
    if (pullRequest) {
      labelLevel = levelFromLabels(pullRequest.labels.map((label) => label.name));
      labelSource = `PR #${pullRequest.number}`;
      pullRequestNote = `#${pullRequest.number}, ${labelLevel ? `label release:${labelLevel}` : 'no release:* label'}`;
    }
  }

  const release = nextRelease({ packageVersion, lastTag, messages, labelLevel, labelSource });
  if (git('tag', '--list', release.version).trim()) {
    throw new Error(`Tag ${release.version} already exists: this commit was released already (a re-run?), or that tag is on a commit this one doesn't contain`);
  }
  const commitMessage = `${releaseCommitPrefix}${release.version}`;
  const lines = [
    `Version: ${release.version} (${release.level})`,
    `Reason: ${release.reason}`,
    `Bumped from: ${release.baseVersion} (package.json ${packageVersion}, last tag ${lastTag || 'none'})`,
    `Commits counted: ${range.join(' ')}`,
    `Pull request: ${pullRequestNote}`,
  ];
  console.info(lines.join('\n'));
  if (args.includes('--write')) writeVersion('.', release.version);
  if (process.env.GITHUB_OUTPUT) appendFileSync(process.env.GITHUB_OUTPUT, `version=${release.version}\ncommit_message=${commitMessage}\n`);
  if (process.env.GITHUB_STEP_SUMMARY) {
    appendFileSync(process.env.GITHUB_STEP_SUMMARY, `### Next version: ${release.version}\n\n${lines.map((line) => `- ${line}`).join('\n')}\n`);
  }
}

if (process.argv[1] && resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
  try {
    main(process.argv.slice(2));
  } catch (error) {
    console.error(`next-version: ${error.message}`);
    process.exit(1);
  }
}
