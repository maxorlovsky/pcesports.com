// The release version (tools/release/next-version.mjs), on scratch git histories with a fake gh, and the CI workflow's
// release, merge-back and deploy jobs (README.md, "Releasing"), read as text: the repository has no YAML parser.
import assert from 'node:assert/strict';
import { spawnSync } from 'node:child_process';
import { copyFileSync, mkdirSync, mkdtempSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, resolve } from 'node:path';
import { afterEach, describe, test } from 'node:test';
import {
  lastVersionTag, levelFromLabels, nextRelease, pullRequestMergedAs, releaseCommitPrefix, writeVersion,
} from '../tools/release/next-version.mjs';

const REPO = resolve(import.meta.dirname, '..');
const SCRIPT = join(REPO, 'tools', 'release', 'next-version.mjs');
let dir = '';

afterEach(() => {
  if (dir) rmSync(dir, { recursive: true, force: true });
  dir = '';
});

const release = (messages, { packageVersion = '6.0.0', lastTag = '6.0.0', labelLevel = null } = {}) => nextRelease({
  packageVersion, lastTag, messages, labelLevel, labelSource: 'PR #40',
});

describe('the release level from the commits', () => {
  test('is minor for any feat', () => {
    const next = release(['fix(watch): one', 'feat(guides): two', 'chore: three']);
    assert.deepEqual([next.version, next.level, next.reason], ['6.1.0', 'minor', '1 feat commit, e.g. "feat(guides): two"']);
  });

  test('is patch for fix and chore commits only, and for no commits at all', () => {
    assert.equal(release(['fix(watch): one', 'docs(learnings): two', 'not conventional at all']).version, '6.0.1');
    assert.equal(release([]).version, '6.0.1');
  });

  test('is major for a type! subject', () => {
    const next = release(['feat(matches)!: drop a field', 'feat: two']);
    assert.deepEqual([next.version, next.level, next.reason], ['7.0.0', 'major', '1 breaking change, e.g. "feat(matches)!: drop a field"']);
    assert.equal(release(['fix!: one']).level, 'major');
  });

  test('leaves out "Merge …" commits, whatever their body says', () => {
    const next = release([
      'Merge pull request #37 from maxorlovsky/dev\n\nfeat!: a pull request title',
      'Merge branch \'master\' into dev\n\nBREAKING CHANGE: not a footer of a real commit',
      'fix: one',
    ]);
    assert.deepEqual([next.level, next.reason], ['patch', '1 commit, no feat or breaking change']);
  });

  test('is major for a BREAKING CHANGE footer, but not for those words in the subject', () => {
    assert.equal(release(['fix(vods): rename a field\n\nBREAKING CHANGE: the data says videoId']).level, 'major');
    assert.equal(release(['chore: two\n\nBREAKING-CHANGE: also a footer']).level, 'major');
    assert.equal(release(['BREAKING CHANGE: in a subject line']).level, 'patch');
  });

  test('bumps from the higher of package.json and the last tag: the first release continues from 6.0.0', () => {
    assert.equal(release(['fix: one'], { packageVersion: '6.0.0', lastTag: null }).version, '6.0.1');
    assert.equal(release(['feat: one'], { packageVersion: '6.0.0', lastTag: null }).version, '6.1.0');
    assert.equal(release(['fix: one'], { packageVersion: '5.9.4', lastTag: '6.0.2' }).version, '6.0.3');
    assert.equal(release(['fix: one'], { packageVersion: '6.1.0', lastTag: '6.0.2' }).version, '6.1.1');
  });

  test('fails loudly on a malformed version in package.json', () => {
    for (const packageVersion of ['v6.0.0', '6.0', '6.0.0-beta.1', 'latest']) {
      assert.throws(() => release(['fix: one'], { packageVersion }), { message: `package.json has version "${packageVersion}", expected X.Y.Z (digits only, no "v")` });
    }
  });
});

describe('the release level from a label', () => {
  test('beats the commits, both ways', () => {
    const next = release(['feat(matches)!: drop a field'], { labelLevel: 'patch' });
    assert.deepEqual([next.version, next.reason], ['6.0.1', 'label release:patch on PR #40 (the commits alone: major, 1 breaking change, e.g. "feat(matches)!: drop a field")']);
    assert.equal(release(['fix: one'], { labelLevel: 'major' }).version, '7.0.0');
  });

  test('is read from the release:* labels only, and two different ones fail', () => {
    assert.equal(levelFromLabels(['bug', 'release:minor', 'enhancement']), 'minor');
    assert.equal(levelFromLabels(['bug', 'release:notes']), null);
    assert.equal(levelFromLabels([]), null);
    assert.throws(() => levelFromLabels(['release:minor', 'release:major']), /release:minor and release:major/);
  });

  test('comes from the pull request the pushed commit merged, not one that only contains it', () => {
    const pullRequests = [{ number: 36, merge_commit_sha: 'f068a05' }, { number: 37, merge_commit_sha: '3d39878' }];
    assert.equal(pullRequestMergedAs(pullRequests, '3d39878')?.number, 37);
    assert.equal(pullRequestMergedAs(pullRequests, '1d100db'), null);
  });
});

test('the last release tag is the highest X.Y.Z tag, compared as numbers, without a "v"', () => {
  assert.equal(lastVersionTag(['6.0.0', '6.10.0', '6.9.3', 'v7.0.0', '7.0.0-rc.1', '']), '6.10.0');
  assert.equal(lastVersionTag(['v1.0.0', 'latest']), null);
});

test('writing the version changes only the version lines of package.json and package-lock.json', () => {
  dir = mkdtempSync(join(tmpdir(), 'pce-release-write-'));
  for (const file of ['package.json', 'package-lock.json']) copyFileSync(join(REPO, file), join(dir, file));
  writeVersion(dir, '9.8.7');
  const changedLines = (file) => {
    const before = readFileSync(join(REPO, file), 'utf8').split('\n');
    const after = readFileSync(join(dir, file), 'utf8').split('\n');
    assert.equal(after.length, before.length);
    return after.filter((line, index) => line !== before[index]);
  };
  assert.deepEqual(changedLines('package.json'), ['  "version": "9.8.7",']);
  assert.deepEqual(changedLines('package-lock.json'), ['  "version": "9.8.7",', '      "version": "9.8.7",']);
});

describe('next-version.mjs on a git history', () => {
  const run = (...args) => spawnSync('node', [SCRIPT, ...args], {
    cwd: dir,
    encoding: 'utf8',
    env: {
      ...process.env,
      // A fake gh (fakeGitHub), for --repo
      PATH: `${join(dir, '.fake-bin')}:${process.env.PATH}`,
      // Its own step outputs and job summary, also when the tests run in GitHub Actions
      GITHUB_OUTPUT: join(dir, '.github-output'),
      GITHUB_STEP_SUMMARY: join(dir, '.github-summary'),
    },
  });
  const git = (...args) => {
    const result = spawnSync('git', ['-c', 'user.name=Test', '-c', 'user.email=test@example.com', '-c', 'core.hooksPath=/dev/null', ...args], { cwd: dir, encoding: 'utf8' });
    assert.equal(result.status, 0, result.stderr);
    return result.stdout.trim();
  };
  const commit = (message) => git('commit', '--allow-empty', '-q', '-m', message);

  // master at <version> (tagged 6.0.0 unless told otherwise), then dev with the given commits, not merged yet
  const repository = (version, devCommits, tagged = true) => {
    if (dir) rmSync(dir, { recursive: true, force: true });
    dir = mkdtempSync(join(tmpdir(), 'pce-release-history-'));
    git('init', '-q', '-b', 'master');
    writeFileSync(join(dir, 'package.json'), `{\n  "name": "x",\n  "version": "${version}"\n}\n`);
    git('add', 'package.json');
    commit('chore: start');
    if (tagged) git('tag', '-a', '6.0.0', '-m', '6.0.0');
    git('switch', '-q', '-c', 'dev');
    devCommits.forEach(commit);
    git('switch', '-q', 'master');
  };
  const mergeDev = () => git('merge', '--no-ff', '-q', '-m', 'Merge pull request #5 from me/dev', 'dev');

  // gh api answers with these pull requests, and records the arguments it got
  const fakeGitHub = (pullRequests) => {
    const bin = join(dir, '.fake-bin');
    mkdirSync(bin);
    writeFileSync(join(bin, 'pulls.json'), JSON.stringify(pullRequests));
    writeFileSync(join(bin, 'gh'), '#!/bin/sh\necho "$@" > "$0.args"\ncat "$(dirname "$0")/pulls.json"\n', { mode: 0o755 });
    return () => readFileSync(join(bin, 'gh.args'), 'utf8').trim();
  };

  test('counts a dev -> master merge from the last tag, and writes the version', () => {
    repository('6.0.0', ['fix: one', 'feat(guides): two']);
    mergeDev();
    const result = run('--write');
    assert.equal(result.status, 0, result.stderr);
    assert.match(result.stdout, /^Version: 6\.1\.0 \(minor\)$/m);
    assert.match(result.stdout, /^Reason: 1 feat commit, e\.g\. "feat\(guides\): two"$/m);
    assert.match(result.stdout, /last tag 6\.0\.0/);
    assert.equal(JSON.parse(readFileSync(join(dir, 'package.json'), 'utf8')).version, '6.1.0');
    // The workflow commits and tags with these, and skips a push whose head commit starts with releaseCommitPrefix
    assert.equal(readFileSync(join(dir, '.github-output'), 'utf8'), `version=6.1.0\ncommit_message=${releaseCommitPrefix}6.1.0\n`);
    assert.match(readFileSync(join(dir, '.github-summary'), 'utf8'), /^### Next version: 6\.1\.0\n\n- Version: 6\.1\.0 \(minor\)\n- Reason: 1 feat commit/);
  });

  test('leaves out git\'s and GitHub\'s merge commits whatever their body says, but counts a merge with a conventional subject', () => {
    repository('6.0.0', ['fix: one']);
    commit('chore: on master');
    git('switch', '-q', 'dev');
    git('merge', '--no-ff', '-q', '-m', 'Merge branch \'master\' into dev\n\nBREAKING CHANGE: not a real footer', 'master');
    git('switch', '-q', 'master');
    git('merge', '--no-ff', '-q', '-m', 'Merge pull request #5 from me/dev\n\nfeat!: a pull request title', 'dev');
    const result = run();
    assert.equal(result.status, 0, result.stderr);
    assert.match(result.stdout, /^Version: 6\.0\.1 \(patch\)$/m);
    assert.match(result.stdout, /^Reason: 2 commits, no feat or breaking change$/m);

    git('switch', '-q', 'dev');
    commit('fix: two');
    git('switch', '-q', 'master');
    git('merge', '--no-ff', '-q', '-m', 'feat!: incompatible integration', 'dev');
    assert.match(run().stdout, /^Version: 7\.0\.0 \(major\)$/m);
  });

  test('lets a release label on the pull request the merge closed beat the commits', () => {
    repository('6.0.0', ['fix: one']);
    mergeDev();
    const sha = git('rev-parse', 'HEAD');
    const ghArguments = fakeGitHub([
      { number: 4, merge_commit_sha: 'another', labels: [{ name: 'release:minor' }] },
      { number: 5, merge_commit_sha: sha, labels: [{ name: 'bug' }, { name: 'release:major' }] },
    ]);
    const result = run('--repo', 'owner/name');
    assert.equal(result.status, 0, result.stderr);
    assert.equal(ghArguments(), `api repos/owner/name/commits/${sha}/pulls`);
    assert.match(result.stdout, /^Version: 7\.0\.0 \(major\)$/m);
    assert.match(result.stdout, /^Reason: label release:major on PR #5 \(the commits alone: patch, 1 commit, no feat or breaking change\)$/m);
    assert.match(result.stdout, /^Pull request: #5, label release:major$/m);
  });

  test('reads package.json from the ref, and writes only without --ref', () => {
    repository('6.0.0', ['fix: one']);
    mergeDev();
    // HEAD and the working tree say 7.0.0, the merge (HEAD^1) says 6.0.0
    writeFileSync(join(dir, 'package.json'), '{\n  "version": "7.0.0"\n}\n');
    git('commit', '-q', '-am', 'chore: a later commit');
    assert.match(run('--ref', 'HEAD^1').stdout, /^Version: 6\.0\.1 \(patch\)$/m);
    const result = run('--ref', 'HEAD', '--write');
    assert.deepEqual([result.status, result.stderr], [1, 'next-version: --write writes the working tree, so it takes no --ref\n']);
  });

  test('gives a direct push to master a version from its own commits, the same as a merge', () => {
    repository('6.0.0', []);
    commit('fix(site): hotfix one');
    commit('chore: hotfix two');
    const result = run();
    assert.equal(result.status, 0, result.stderr);
    assert.match(result.stdout, /^Version: 6\.0\.1 \(patch\)$/m);
    assert.match(result.stdout, /^Reason: 2 commits, no feat or breaking change$/m);
  });

  test('counts what the commit brought in when there is no tag yet (the first release): a merge\'s own range, or the commit', () => {
    repository('6.0.0', ['fix: one'], false);
    commit('feat!: on master before the merge, not counted');
    mergeDev();
    const result = run();
    assert.equal(result.status, 0, result.stderr);
    assert.match(result.stdout, /^Version: 6\.0\.1 \(patch\)$/m);
    assert.match(result.stdout, /^Reason: 1 commit, no feat or breaking change$/m);
    assert.match(result.stdout, /last tag none/);

    // A direct push on top, still with no tag: the commit itself
    commit('fix: a hotfix');
    assert.match(run().stdout, /^Version: 6\.0\.1 \(patch\)$/m);
  });

  test('fails loudly on a malformed version in package.json, and when the version is tagged already', () => {
    repository('6.0', ['fix: one']);
    mergeDev();
    const malformed = run();
    assert.deepEqual([malformed.status, malformed.stderr], [1, 'next-version: package.json has version "6.0", expected X.Y.Z (digits only, no "v")\n']);

    // A re-run after the release commit and its tag were pushed: the tag is on a child of the pushed commit
    repository('6.0.0', ['fix: one']);
    mergeDev();
    git('switch', '-q', '-c', 'released');
    commit(`${releaseCommitPrefix}6.0.1`);
    git('tag', '6.0.1');
    git('switch', '-q', 'master');
    const rerun = run();
    assert.equal(rerun.status, 1);
    assert.match(rerun.stderr, /Tag 6\.0\.1 already exists/);
  });
});

describe('the CI workflow', () => {
  const workflow = readFileSync(join(REPO, '.github', 'workflows', 'ci.yml'), 'utf8');
  // A job's lines, from its `  <name>:` line to the next job (or the end)
  const job = (name) => {
    const start = workflow.indexOf(`\n  ${name}:\n`);
    assert.notEqual(start, -1, `no job ${name}`);
    const end = workflow.slice(start + 1).search(/\n {2}[\w-]+:\n/);
    return end === -1 ? workflow.slice(start) : workflow.slice(start, start + 1 + end);
  };
  const isReleaseCommit = `startsWith(github.event.head_commit.message, '${releaseCommitPrefix}')`;

  test('runs on pull requests, on pushes to master only, and by hand (the data job\'s deploy)', () => {
    assert.match(workflow, /\non:\n {2}pull_request:\n {4}branches:\n {6}- dev\n {6}- master\n {2}push:\n {4}branches:\n {6}- master\n {2}workflow_dispatch:\n\n/);
    // tools/data-job/run.mjs starts that manual run after it publishes new data
    assert.match(readFileSync(join(REPO, 'tools', 'data-job', 'run.mjs'), 'utf8'), /spawnSync\('gh', \['workflow', 'run', 'ci\.yml', '--ref', 'master'\]/);
  });

  test('releases every push but its own release commit, the one the script names, and never a manual run', () => {
    const lines = job('release');
    assert.ok(lines.includes(`    if: "\${{ github.event_name == 'push' && !${isReleaseCommit} }}"\n`), lines);
    assert.ok(lines.includes('        run: node tools/release/next-version.mjs --write --repo "$GITHUB_REPOSITORY"\n'));
    assert.ok(lines.includes('          COMMIT_MESSAGE: ${{ steps.version.outputs.commit_message }}\n'));
    assert.ok(lines.includes('git push --atomic origin "HEAD:refs/heads/master" "refs/tags/$VERSION"\n'));
    assert.ok(lines.includes('run: gh release create "$VERSION" --verify-tag --title "$VERSION" --generate-notes\n'));
  });

  test('checks every run but the release commit\'s own', () => {
    assert.ok(job('check').includes(`    if: "\${{ github.event_name != 'push' || !${isReleaseCommit} }}"\n`));
  });

  test('merges the release back into dev in a job of its own, which the deploy doesn\'t wait for', () => {
    assert.match(job('merge-back'), /\n {4}needs: release\n/);
    assert.match(job('merge-back'), /git push origin HEAD:refs\/heads\/dev\n/);
    assert.doesNotMatch(job('release'), /merge/i);
  });

  test('deploys once per release, after it, or on a manual run on master, which releases nothing', () => {
    const lines = job('deploy');
    assert.match(lines, /\n {4}needs: \[release, check\]\n/);
    assert.ok(lines.includes(`    if: >-
      \${{ !cancelled() && needs.check.result == 'success' && github.ref == 'refs/heads/master'
      && (needs.release.result == 'success' || github.event_name == 'workflow_dispatch') }}
`), lines);
    assert.match(lines, /\n {4}concurrency:\n {6}group: deploy-production\n {6}cancel-in-progress: false\n/);
  });

  // A step's `run: |` block, as GitHub Actions runs it (bash -eo pipefail)
  const runBlock = (lines, stepName) => {
    const step = lines.slice(lines.indexOf(`- name: ${stepName}\n`));
    const block = /\n {8}run: \|\n((?: {10}.*\n)+)/.exec(step);
    assert.ok(block, `no run block in ${stepName}`);
    return block[1].replace(/^ {10}/gm, '');
  };

  test('its release and merge-back steps, on a scratch origin: an atomic master + annotated tag push, dev merged, data untouched', () => {
    dir = mkdtempSync(join(tmpdir(), 'pce-release-steps-'));
    const sh = (cwd, script, env = {}) => {
      const result = spawnSync('bash', ['--noprofile', '--norc', '-eo', 'pipefail', '-c', script], {
        cwd, encoding: 'utf8', env: { ...process.env, GIT_CONFIG_GLOBAL: '/dev/null', ...env },
      });
      assert.equal(result.status, 0, `${script}\n${result.stdout}${result.stderr}`);
      return result.stdout.trim();
    };
    const origin = join(dir, 'origin.git');
    const work = join(dir, 'work');
    const who = 'git -c user.name=Test -c user.email=test@example.com -c core.hooksPath=/dev/null';
    sh(dir, 'git init -q --bare -b master origin.git && git init -q -b master work');
    for (const file of ['package.json', 'package-lock.json']) copyFileSync(join(REPO, file), join(work, file));
    mkdirSync(join(work, 'tools', 'release'), { recursive: true });
    copyFileSync(SCRIPT, join(work, 'tools', 'release', 'next-version.mjs'));
    sh(work, `${who} add -A && ${who} commit -q -m 'chore: start' && git remote add origin ../origin.git
      ${who} switch -q -c data && ${who} commit -q --allow-empty -m 'data' && git switch -q master
      ${who} switch -q -c dev && ${who} commit -q --allow-empty -m 'feat(guides): a guide'
      git switch -q master && ${who} merge -q --no-ff -m 'Merge pull request #71 from me/dev' dev
      git push -q origin master dev data`);
    const data = sh(work, 'git rev-parse origin/data');

    // The runner: a clone at the pushed merge, then the release job's steps, as the workflow has them
    sh(dir, 'git clone -q origin.git runner');
    const runner = join(dir, 'runner');
    const release = job('release');
    const picked = sh(runner, 'node tools/release/next-version.mjs --write', { GITHUB_OUTPUT: join(dir, 'output') });
    assert.match(picked, /^Version: 6\.1\.0 \(minor\)$/m);
    const outputs = Object.fromEntries(readFileSync(join(dir, 'output'), 'utf8').trim().split('\n').map((line) => line.split('=')));
    sh(runner, runBlock(release, 'Commit, tag and push'), { VERSION: outputs.version, COMMIT_MESSAGE: outputs.commit_message });

    const inOrigin = (args) => sh(origin, `git ${args}`);
    assert.equal(inOrigin('log -1 --format=%s master'), 'chore(release): 6.1.0');
    assert.equal(inOrigin('cat-file -t 6.1.0'), 'tag');
    assert.equal(inOrigin('rev-parse 6.1.0^{commit}'), inOrigin('rev-parse master'));
    assert.equal(inOrigin('diff --name-only master^ master'), 'package-lock.json\npackage.json');
    assert.equal(JSON.parse(inOrigin('show master:package-lock.json')).packages[''].version, '6.1.0');

    // merge-back, from a fresh clone of master
    sh(dir, 'git clone -q -b master origin.git merger');
    sh(join(dir, 'merger'), runBlock(job('merge-back'), 'Merge master into dev'));
    assert.equal(inOrigin('merge-base --is-ancestor master dev && echo yes'), 'yes');
    assert.equal(inOrigin('rev-parse data'), data);

    // The next release counts from the tag, through the merge-back
    sh(runner, 'git fetch -q origin dev');
    assert.match(sh(runner, 'node tools/release/next-version.mjs --ref origin/dev'), /last tag 6\.1\.0/);
  });

  test('keeps pushes and manual runs in concurrency groups of their own', () => {
    assert.ok(workflow.includes(`concurrency:
  group: ci-\${{ github.event.pull_request.number || format('{0}-{1}', github.event_name, github.ref) }}
  cancel-in-progress: \${{ github.event_name == 'pull_request' }}
`));
  });
});
