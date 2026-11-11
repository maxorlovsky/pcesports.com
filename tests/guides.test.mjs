// The guides domain: its Markdown subset, the guide files' header, every guide as the owner's rules want it (short,
// straight typography, no spoiler the build check can read), and the rating guide's facts, which come from the code.
import assert from 'node:assert/strict';
import { mkdirSync, mkdtempSync, readFileSync, readdirSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { test } from 'node:test';
import { GUIDES_DIR, GUIDE_FACTS, RATING_FACTS, WORDS_PER_MINUTE, loadGuides, parseGuide, parseInline, parseMarkdown, textOf, vodChannelList, wordsOf } from '../app/domains/guides/index.ts';
import { CHANNELS_LIST, VOD_CHANNELS } from '../app/domains/vods/index.ts';
import { CLOSE_MINUTES, LABELS, LIST_DAYS, MUST_WATCH, OVERTIME_MINUTES, RESULTS_DAYS, UPSET_CHANCE, WORTH_A_WATCH, closeness, eventPoints, labelOf, stagePoints, strengthPoints } from '../app/domains/watch/index.ts';
import { checkBuild } from '../tools/check-build.mjs';

const ROOT = join(import.meta.dirname, '..');
const guides = loadGuides(ROOT);
const sources = Object.fromEntries(readdirSync(join(ROOT, GUIDES_DIR)).map((name) => [name.slice(0, -3), readFileSync(join(ROOT, GUIDES_DIR, name), 'utf8')]));
const RATING_GUIDE = 'how-the-watch-rating-works';
const VOD_GUIDE = 'where-to-watch-official-cs2-vods';
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
  assert.deepEqual(parseInline('GAM3RS_X runs it.'), [{ kind: 'text', text: 'GAM3RS_X runs it.' }]);
});

const unsupported = [
  ['an h1', '# Title'],
  ['a quote', '> said'],
  ['a table', '| a | b |'],
  ['a star list', '* item'],
  ['emphasis', 'some *word*'],
  ['underscore emphasis', 'some _word_'],
  ['an underscore before a word', 'some _word'],
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
    const minutes = wordsOf(guide.blocks) / WORDS_PER_MINUTE;
    assert.ok(minutes >= 2.75 && minutes <= 5.25, `${minutes.toFixed(2)} minutes at ${WORDS_PER_MINUTE} words a minute`);
    assert.equal(guide.minutes, Math.round(minutes));
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
  const body = sources[RATING_GUIDE].replace(/^order: .*$/m, '').replace(/\{\{\s*\w+\s*\}\}/g, '');
  const number = body.match(/(?<![\w.])-?\d+(?:\.\d+)?(?![\w.]?\d)/);
  assert.equal(number, null, `"${number?.[0]}" is typed by hand: write it as a {{fact}} (facts.ts)`);
  const words = body.toLowerCase().replace(/[^a-z]+/g, ' ');
  for (const label of LABELS) assert.ok(!words.includes(label.toLowerCase()), `"${label}" is typed by hand: write it as a {{fact}}`);
  const rating = guides.find((guide) => guide.slug === RATING_GUIDE);
  for (const label of LABELS) assert.ok(textOfGuide(rating).includes(label), `the guide doesn't name "${label}"`);
});

test('the rating guide\'s facts are what the watch domain\'s code does, every one of them', () => {
  // Worked out here from the watch domain's exports and its functions on other round and league names than facts.ts
  // uses, so a fact typed by hand, or read from the wrong place, fails.
  const playoff = (round) => stagePoints({ name: `${round}: X vs Y`, stage: 'Playoffs', teams: [] });
  const group = (round, stage) => stagePoints({ name: `${round}: X vs Y`, stage, teams: [] });
  const scale = { median: 1500, top: 1600 };
  const expected = {
    mustWatch: MUST_WATCH,
    worthAWatch: WORTH_A_WATCH,
    labelMustWatch: LABELS[0],
    labelWorthAWatch: LABELS[1],
    labelForFans: LABELS[2],
    listDays: LIST_DAYS,
    resultsDays: RESULTS_DAYS,
    grandFinal: playoff('Grand final'),
    semiFinal: playoff('Semifinal 2'),
    bracketFinal: playoff('Upper Bracket Final'),
    otherPlayoff: playoff('Quarterfinal 3'),
    thirdPlace: playoff('Third Place Match'),
    groupDecider: group('Elimination Match', 'Group B'),
    otherGroup: group('Round 2', 'Swiss Stage'),
    major: eventPoints({ league: 'PGL', serie: 'Major Copenhagen 2024', tier: 'a' }),
    tierS: eventPoints({ league: 'BLAST Premier', serie: 'World Final 2026', tier: 's' }),
    tierA: eventPoints({ league: 'BLAST Rivals', serie: 'Fall 2026', tier: 'a' }),
    strengthMin: strengthPoints(0, scale),
    strengthMax: strengthPoints(3000, scale),
    upsetPercent: UPSET_CHANCE * 100,
    upset: closeness({ bestOf: 3, maps: [{ seconds: 1800 }, { seconds: 1800 }], winnerChance: UPSET_CHANCE / 2 }),
    overtimeMinutes: OVERTIME_MINUTES,
    overtimeMap: closeness({ bestOf: 3, maps: [{ seconds: OVERTIME_MINUTES * 60 + 30 }, { seconds: 1800 }], winnerChance: 0.5 }),
    closeMinutes: CLOSE_MINUTES,
    closeMap: closeness({ bestOf: 3, maps: [{ seconds: 1800 }, { seconds: CLOSE_MINUTES * 60 + 30 }], winnerChance: 0.5 }),
  };
  assert.deepEqual(RATING_FACTS, Object.fromEntries(Object.entries(expected).map(([name, value]) => [name, String(value)])));
  // What the guide says around them: the thresholds are where the labels change; semi-finals of a bracket score as
  // semi-finals; a shorter map, or the favourite winning, adds nothing.
  assert.equal(labelOf(MUST_WATCH - 0.01), LABELS[1]);
  assert.equal(labelOf(WORTH_A_WATCH - 0.01), LABELS[2]);
  assert.equal(playoff('Lower Bracket Semifinal 1'), playoff('Semifinal 1'));
  // Outside the playoffs, a round scores by its name as the group lines list them, and a Swiss round by the records.
  for (const round of ['Elimination Match', 'Decider Match', 'Winners Match', 'Upper Bracket Final', 'Lower Bracket Semifinal 1', 'Lower Bracket Quarterfinal 2']) {
    assert.equal(group(round, 'Group A'), Number(RATING_FACTS.groupDecider), round);
  }
  for (const round of ['Upper Bracket Semifinal 1', 'Upper Bracket Quarterfinal 1', 'Third Place Match', 'Opening Match']) {
    assert.equal(group(round, 'Group A'), Number(RATING_FACTS.otherGroup), round);
  }
  const swiss = (records) => stagePoints({ name: 'Round 4: X vs Y', stage: 'Swiss Stage', teams: [{ id: 1 }, { id: 2 }] }, new Map(records));
  assert.equal(swiss([[1, [2, 1]], [2, [1, 1]]]), Number(RATING_FACTS.groupDecider));
  assert.equal(swiss([[1, [1, 2]]]), Number(RATING_FACTS.groupDecider));
  assert.equal(swiss([[1, [1, 1]], [2, [1, 0]]]), Number(RATING_FACTS.otherGroup));
  assert.equal(closeness({ bestOf: 1, maps: [{ seconds: CLOSE_MINUTES * 60 - 60 }], winnerChance: UPSET_CHANCE }), 0);
});

test('the rating guide puts each fact where it belongs: a wrong placeholder on a line fails', () => {
  // Each line (a list item or a paragraph) that states a number is matched to the first rule its words fit, and may
  // hold only that rule's facts, all of them, written as the rule says. A rewrite keeps the words a rule reads, or
  // updates the rule with them.
  const rules = [
    [/\{\{labelMustWatch\}\}/, ['mustWatch'], '{{mustWatch}} or more'],
    [/\{\{labelWorthAWatch\}\}/, ['worthAWatch'], '{{worthAWatch}} or more'],
    [/\{\{labelForFans\}\}/, ['worthAWatch'], 'below {{worthAWatch}}'],
    [/^\*\*the teams:\*\*/i, ['strengthMin', 'strengthMax', 'resultsDays'], /from \{\{strengthMin\}\}.* to \{\{strengthMax\}\}.*\{\{resultsDays\}\} days/],
    [/in a group,|in a swiss stage/i, ['groupDecider']],
    [/grand final/i, ['grandFinal']],
    [/semi-final/i, ['semiFinal']],
    [/bracket final/i, ['bracketFinal']],
    [/third-place/i, ['thirdPlace']],
    [/other playoff/i, ['otherPlayoff']],
    [/other group/i, ['otherGroup']],
    [/\ba major\b/i, ['major']],
    [/\btier s\b/i, ['tierS']],
    [/\btier a\b/i, ['tierA']],
    [/upset/i, ['upset', 'upsetPercent'], '{{upsetPercent}}% chance'],
    [/very long map/i, ['overtimeMap', 'overtimeMinutes'], '{{overtimeMinutes}} minutes'],
    [/long map/i, ['closeMap', 'closeMinutes'], '{{closeMinutes}} minutes'],
    [/\{\{listDays\}\} days/, ['listDays']],
  ];
  const labels = new Set(['labelMustWatch', 'labelWorthAWatch', 'labelForFans']);
  const stated = new Set();
  for (const line of sources[RATING_GUIDE].split('\n')) {
    const facts = [...line.matchAll(/\{\{\s*(\w+)\s*\}\}/g)].map((match) => match[1]).filter((name) => !labels.has(name));
    if (!facts.length) continue;
    const rule = rules.find(([words]) => words.test(line));
    assert.ok(rule, `no rule says which facts "${line}" may state: add one`);
    const [, allowed, written] = rule;
    assert.deepEqual([...new Set(facts)].sort(), [...allowed].sort(), `"${line}" states ${facts.join(', ')}, expected ${allowed.join(', ')}`);
    if (written) assert.ok(typeof written === 'string' ? line.includes(written) : written.test(line), `"${line}" doesn't say ${written}`);
    facts.forEach((name) => stated.add(name));
  }
  const numbers = Object.keys(RATING_FACTS).filter((name) => !labels.has(name));
  assert.deepEqual(numbers.filter((name) => !stated.has(name)), [], 'every fact the rating guide can state, it states');
});

test('the guides\' facts are the rating\'s and the VOD channel list, nothing else', () => {
  assert.deepEqual(GUIDE_FACTS, { ...RATING_FACTS, vodChannels: vodChannelList() });
});

test('the VOD guide lists every VOD channel of the whitelist, and a channel added there appears in it', () => {
  const links = (guide) => guide.blocks.flatMap((block) => ('items' in block ? block.items.flat() : block.inlines)).filter((inline) => inline.kind === 'link');
  const channelLinks = (guide) => links(guide).filter((link) => link.href.startsWith('https://www.youtube.com/'));
  const guide = guides.find((each) => each.slug === VOD_GUIDE);
  assert.deepEqual(channelLinks(guide), VOD_CHANNELS.map((channel) => ({ kind: 'link', text: channel.name, href: `https://www.youtube.com/channel/${channel.id}` })));
  for (const channel of VOD_CHANNELS) assert.ok(textOfGuide(guide).includes(`${channel.organizers.join(' and ')}: ${channel.name}`), channel.name);
  // The source types no channel: every one comes from the whitelist (a main channel may share its organiser's name).
  for (const channel of CHANNELS_LIST) assert.ok(!sources[VOD_GUIDE].includes(channel.id), `${channel.id} is typed by hand`);
  for (const channel of VOD_CHANNELS) assert.ok(!sources[VOD_GUIDE].includes(channel.name), `${channel.name} is typed by hand`);

  // A VOD channel added to the whitelist, for a new organiser with no other channel, and for one with a main channel.
  const added = { id: 'UCnewnewnewnewnewnewnewn', name: 'New Org VODs', handle: '@neworgvods', organizers: ['New Org'], role: 'vods', uploads: 'UUnewnewnewnewnewnewnewn', verified: '2026-11-11' };
  const second = { ...added, id: 'UCeslsecondsecondsecondse', name: 'ESL Second Archive', organizers: ['ESL'] };
  const facts = { ...GUIDE_FACTS, vodChannels: vodChannelList([...VOD_CHANNELS, added, second], [...CHANNELS_LIST, added, second]) };
  const grown = parseGuide(VOD_GUIDE, sources[VOD_GUIDE], facts);
  assert.equal(channelLinks(grown).length, VOD_CHANNELS.length + 2);
  assert.ok(textOfGuide(grown).includes('New Org: New Org VODs, its main channel'));
  assert.match(textOfGuide(grown), /^ESL: ESL Second Archive$/m);
  assert.ok(channelLinks(grown).some((link) => link.href === `https://www.youtube.com/channel/${added.id}`));
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
