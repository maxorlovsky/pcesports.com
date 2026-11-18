// Where `nuxt generate` builds (nuxt.config.ts). A reviewer's scratch copy, with node_modules symlinked to a checkout's,
// must build in its own .nuxt and take no Nuxt CLI lock, never in node_modules/.cache/nuxt, which is that checkout's.
// Any other checkout keeps Nuxt's default. Each case loads the real config in a child process, as the Nuxt CLI does.
import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import { copyFileSync, mkdirSync, mkdtempSync, rmSync, symlinkSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { afterEach, test } from 'node:test';

const ROOT = join(import.meta.dirname, '..');
const LOAD = `const { loadNuxtConfig } = await import(${JSON.stringify(import.meta.resolve('@nuxt/kit'))});
const { buildDir } = await loadNuxtConfig({ cwd: process.cwd() });
console.log(JSON.stringify({ buildDir, lock: process.env.NUXT_IGNORE_LOCK ?? null }));`;
let dir;

afterEach(() => rmSync(dir, { recursive: true, force: true }));

/** The config's choice in a copy that already has a .nuxt (`nuxt prepare` or `typecheck` ran), with node_modules made by `addNodeModules`. */
function load(addNodeModules) {
  dir = mkdtempSync(join(tmpdir(), 'pce-build-folder-'));
  for (const file of ['nuxt.config.ts', 'package.json']) copyFileSync(join(ROOT, file), join(dir, file));
  mkdirSync(join(dir, '.nuxt'));
  addNodeModules(join(dir, 'node_modules'));
  const env = { ...process.env };
  delete env.NUXT_IGNORE_LOCK;
  return JSON.parse(execFileSync(process.execPath, ['--input-type=module', '-e', LOAD], { cwd: dir, env, encoding: 'utf8' }));
}

test('a copy with a symlinked node_modules builds in its own .nuxt and takes no lock', () => {
  const result = load((path) => symlinkSync(join(ROOT, 'node_modules'), path));
  assert.deepEqual(result, { buildDir: join(dir, '.nuxt'), lock: '1' });
});

test("a checkout with its own node_modules keeps Nuxt's default build folder and its lock", () => {
  const result = load((path) => mkdirSync(path));
  assert.deepEqual(result, { buildDir: join(dir, 'node_modules/.cache/nuxt/.nuxt'), lock: null });
});
