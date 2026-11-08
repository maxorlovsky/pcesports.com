// AGENTS.md is read by every agent session in full, so it holds only the critical rules and stays small. Detail goes
// into README.md, docs/ or a test.
import assert from 'node:assert/strict';
import { readFileSync, statSync } from 'node:fs';
import { join } from 'node:path';
import { test } from 'node:test';

const ROOT = join(import.meta.dirname, '..');
const CAP = 8 * 1024;

test(`AGENTS.md stays under ${CAP} bytes`, () => {
  const size = statSync(join(ROOT, 'AGENTS.md')).size;
  assert.ok(size <= CAP, `AGENTS.md is ${size} bytes: move detail to README.md, docs/ or a test`);
});

test('CLAUDE.md only imports AGENTS.md', () => {
  assert.equal(readFileSync(join(ROOT, 'CLAUDE.md'), 'utf8'), '@AGENTS.md\n');
});
