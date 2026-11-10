// The guides domain: its Markdown subset, the guide files' header, every guide as the owner's rules want it (short,
// straight typography, no spoiler the build check can read), and the rating guide's facts, which come from the code.
import assert from 'node:assert/strict';
import { mkdirSync, mkdtempSync, readFileSync, readdirSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { test } from 'node:test';
import { GUIDES_DIR, GUIDE_FACTS, WORDS_PER_MINUTE, loadGuides, parseGuide, parseInline, parseMarkdown, textOf } from '../app/domains/guides/index.ts';
import { CLOSE_MINUTES, LABELS, OVERTIME_MINUTES, UPSET_CHANCE, closeness, labelOf } from '../app/domains/watch/index.ts';
import { checkBuild } from '../tools/check-build.mjs';

const ROOT = join(import.meta.dirname, '..');
const guides = loadGuides(ROOT);
const sources = Object.fromEntries(readdirSync(join(ROOT, GUIDES_DIR)).map((name) => [name.slice(0, -3), readFileSync(join(ROOT, GUIDES_DIR, name), 'utf8')]));
const RATING_GUIDE = 'how-the-watch-rating-works';
const textOfGuide = (guide) => [guide.title, guide.description, ...guide.blocks.flatMap((block) => ('items' in block ? block.items : [block.inlines]).map(textOf))].join('\n');

test('the Markdown subset parses headings, paragraphs, lists, bold and links', () => {
  assert.deepEqual(parseMarkdown('## A heading\n\nOne line\nand the next.\n\n### Small\n\n- an item\n  going on\n- **bold** and [a link](/guides/x)\n\n1. first\n2. [out](https://example.com)\n'), [
    { kind: 'h2', inlines: [{ kind: 'text', text: 'A heading' }] },
    { kind: 'p', inlines: [{ kind: 'text', text: 'One line and the next.' }] },
    { kind: 'h3', inlines: [{ kind: 'text', text: 'Small' }] },
    { kind: 'ul', items: [
      [{ kind: 'text', text: 'an item going on' }],
      [{ kind: 'strong', text: 'bold' }, { kind: 'text', text: ' and ' }, { kind: 'link', text: 'a link', href: '/guides/x' }],
    ] },
    { kind: 'ol', items: [[{ kind: 'text', text: 'first' }], [{ kind: 'link', text: 'out', href: 'https://example.com' }]] },
  ]);
  assert.deepEqual(parseInline('**The short answer:** do this.'), [{ kind: 'strong', text: 'The short answer:' }, { kind: 'text', text: ' do this.' }]);
});

const unsupported = [
  ['an h1', '# Title'],
  ['a quote', '> said'],
  ['a table', '| a | b |'],
  ['a star list', '* item'],
  ['emphasis', 'some *word*'],
  ['code', 'a `cmd`'],
  ['HTML', 'a <b>tag</b>'],
  ['an image', '![alt](/x.png)'],
  ['a plain-http link', '[x](http://example.com)'],
  ['a relative link', '[x](guides/x)'],
  ['a bare bracket', 'a [note]'],
  ['an indented line', '    code'],
  ['a heading glued to a paragraph', 'text\n## Heading'],
  ['a list glued to a paragraph', '- item\ntext'],
];
for (const [name, source] of unsupported) {
  test(`the Markdown subset refuses ${name}`, () => {
    assert.throws(() => parseMarkdown(source), /line \d+/);
  });
}

const header = (fields) => `---\n${fields}\n---\nA body.\n`;
const badGuides = [
  ['a slug with capitals', 'Bad-Slug', header('title: T\ndescription: D\norder: 1'), /the file name is the slug/],
  ['no header', 'x', 'A body.\n', /header starts with ---/],
  ['an unclosed header', 'x', '---\ntitle: T\n', /header ends with ---/],
  ['a missing field', 'x', header('title: T\norder: 1'), /no description/],
  ['an unknown field', 'x', header('title: T\ndescription: D\norder: 1\ndate: today'), /line 5: the header holds/],
  ['an order that is no number', 'x', header('title: T\ndescription: D\norder: first'), /order is a whole number/],
  ['an unknown fact', 'x', header('title: T\ndescription: D\norder: 1').replace('A body.', 'Labels from {{nope}}.'), /\{\{nope\}\} is not a fact/],
  ['no body', 'x', header('title: T\ndescription: D\norder: 1').replace('A body.\n', ''), /no body/],
];
for (const [name, slug, source, error] of badGuides) {
  test(`a guide file with ${name} fails, naming its file`, () => {
    assert.throws(() => parseGuide(slug, source, {}), (thrown) => thrown.message.startsWith(`guides/content/${slug}.md: `) && error.test(thrown.message));
  });
}

test('facts fill the title, the description and the body; the body\'s errors keep their file line', () => {
  const guide = parseGuide('x', '---\ntitle: About {{a}}\ndescription: D {{a}}\norder: 2\n---\n## {{a}} here\n', { a: 'B' });
  assert.equal(guide.title, 'About B');
  assert.equal(guide.description, 'D B');
  assert.deepEqual(guide.blocks, [{ kind: 'h2', inlines: [{ kind: 'text', text: 'B here' }] }]);
  assert.throws(() => parseGuide('x', '---\ntitle: T\ndescription: D\norder: 1\n---\nFine.\n\n> no\n', {}), /line 8:/);
});

test('every guide file loads, in the list\'s order', () => {
  assert.deepEqual(guides.map((guide) => guide.slug).sort(), Object.keys(sources).sort());
  assert.ok(guides.length >= 4);
  assert.deepEqual(guides.map((guide) => guide.order), guides.map((guide) => guide.order).toSorted((a, b) => a - b));
});

for (const guide of guides) {
  test(`${guide.slug}: reads in about 3 to 5 minutes`, () => {
    assert.ok(guide.minutes >= 3 && guide.minutes <= 5, `${guide.minutes} minutes at ${WORDS_PER_MINUTE} words a minute`);
  });

  test(`${guide.slug}: straight quotes and plain hyphens only`, () => {
    const curly = sources[guide.slug].match(/[\u2018-\u201f\u2010-\u2015\u2212\u2026\u00a0]/);
    assert.equal(curly, null, `holds "${curly?.[0]}"`);
  });

  test(`${guide.slug}: a description a search result shows whole, and links that go somewhere`, () => {
    assert.ok(guide.description.length >= 50 && guide.description.length <= 160, `${guide.description.length} characters`);
    const pages = new Set(['/', '/privacy', '/guides', ...guides.map((other) => `/guides/${other.slug}`)]);
    for (const block of guide.blocks) {
      for (const inline of ('items' in block ? block.items.flat() : block.inlines)) {
        if (inline.kind === 'link' && inline.href.startsWith('/')) assert.ok(pages.has(inline.href), `${inline.href} is no page of the site`);
      }
    }
  });
}

test('the rating guide types no number and no label: each comes from the watch domain\'s code', () => {
  const body = sources[RATING_GUIDE].replace(/^---[\s\S]*?\n---\n/, '').replace(/\{\{\s*\w+\s*\}\}/g, '');
  const number = body.match(/(?<![\w.])-?\d+(?:\.\d+)?(?![\w.]?\d)/);
  assert.equal(number, null, `"${number?.[0]}" is typed by hand: write it as a {{fact}} (facts.ts)`);
  for (const label of LABELS) assert.ok(!body.toLowerCase().includes(label.toLowerCase()), `"${label}" is typed by hand: write it as a {{fact}}`);
  const rating = guides.find((guide) => guide.slug === RATING_GUIDE);
  for (const label of LABELS) assert.ok(textOfGuide(rating).includes(label), `the guide doesn't name "${label}"`);
});

test('the rating guide\'s facts are what the watch domain\'s code does', () => {
  const facts = GUIDE_FACTS;
  const must = Number(facts.mustWatch);
  const worth = Number(facts.worthAWatch);
  assert.deepEqual([facts.labelMustWatch, facts.labelWorthAWatch, facts.labelForFans], [...LABELS]);
  assert.equal(labelOf(must), facts.labelMustWatch);
  assert.equal(labelOf(must - 0.01), facts.labelWorthAWatch);
  assert.equal(labelOf(worth), facts.labelWorthAWatch);
  assert.equal(labelOf(worth - 0.01), facts.labelForFans);
  const upset = Number(facts.upsetPercent) / 100;
  assert.equal(upset, UPSET_CHANCE);
  assert.equal(closeness({ bestOf: 1, maps: [], winnerChance: upset - 0.01 }), Number(facts.upset));
  assert.equal(closeness({ bestOf: 1, maps: [], winnerChance: upset }), 0);
  const map = (minutes) => closeness({ bestOf: 1, maps: [{ seconds: minutes * 60 }], winnerChance: null });
  assert.equal(Number(facts.overtimeMinutes), OVERTIME_MINUTES);
  assert.equal(Number(facts.closeMinutes), CLOSE_MINUTES);
  assert.equal(map(OVERTIME_MINUTES), Number(facts.overtimeMap));
  assert.equal(map(CLOSE_MINUTES), Number(facts.closeMap));
  assert.ok(map(CLOSE_MINUTES - 1) < Number(facts.closeMap));
});

test('no guide holds anything the build\'s spoiler check reads as a score, a map count or a winner, whatever the data', () => {
  // Every result a listed match can have (each series score of a best-of-one to five, each map count) and winners
  // named like real teams whose names are also words.
  const names = ['Spirit', 'Liquid', 'Legacy', 'Heroic', 'Imperial', 'Aurora', 'Falcons', 'Complexity', 'Monte', 'BIG', 'OG'];
  const matches = [];
  for (let a = 0; a <= 3; a++) {
    for (let b = 0; b <= 3; b++) {
      if (a === b && a > 0) continue;
      const id = matches.length;
      const winner = { id: 5000 + id, name: names[id % names.length], acronym: names[(id + 1) % names.length] };
      matches.push({ id, winnerId: winner.id, teams: [winner, { id: 9000 + id, name: 'Zz', acronym: 'ZZ' }], score: [a, b], mapSeconds: Array.from({ length: a + b }, (_, map) => 2400 + map) });
    }
  }
  const dir = mkdtempSync(join(tmpdir(), 'pce-guides-'));
  try {
    mkdirSync(join(dir, 'guides'));
    // As the page shows it, and as its payload carries it (where a word before a colon reads as a result's key).
    for (const guide of guides) {
      writeFileSync(join(dir, 'guides', `${guide.slug}.html`), `<html><body>${textOfGuide(guide).split('\n').map((line) => `<p>${line}</p>`).join('')}</body></html>`);
      writeFileSync(join(dir, 'guides', `${guide.slug}.json`), JSON.stringify(guide));
    }
    const spoilers = checkBuild(dir, { data: { matches, entries: matches.map((match) => ({ matchId: match.id })) } }).filter((problem) => /a spoiler$/.test(problem));
    assert.deepEqual(spoilers, []);
  } finally {
    rmSync(dir, { recursive: true, force: true });
  }
});
