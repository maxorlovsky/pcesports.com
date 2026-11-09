// tools/check-build.mjs on a small hand-made build: what passes, and each kind of build it must stop.
import assert from 'node:assert/strict';
import { mkdirSync, mkdtempSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { afterEach, test } from 'node:test';
import { MATCHES_FILE, WATCH_FILE } from '../tools/data-job/job.mjs';
import { buildData, checkBuild } from '../tools/check-build.mjs';

const SITE = 'https://www.pcesports.com';
let dir;

function page({ title = 'A page', path, body, noindex = false }) {
  const url = `${SITE}${path}`;
  return `<!DOCTYPE html><html><head><title>${title}</title><meta name="description" content="About it">`
    + (path ? `<link rel="canonical" href="${url}"><meta property="og:url" content="${url}">` : '')
    + (noindex ? '<meta name="robots" content="noindex">' : '')
    + `<meta property="og:title" content="${title}"><meta property="og:description" content="About it">`
    + `<meta property="og:image" content="${SITE}/og.png"><meta property="og:type" content="website">`
    + `</head><body><main>${body}</main></body></html>`;
}

function build(change = {}, data = { matches: [], entries: [] }) {
  dir = mkdtempSync(join(tmpdir(), 'pce-build-'));
  const files = {
    'index.html': page({ path: '/', body: '<h2>What should I watch?</h2>' }),
    'privacy.html': page({ path: '/privacy', body: '<h2>What we collect</h2>' }),
    '404.html': page({ body: '<h1>This page is not here</h1>', noindex: true }),
    'sitemap.xml': `<urlset><url><loc>${SITE}/</loc></url><url><loc>${SITE}/privacy</loc></url></urlset>`,
    'robots.txt': `User-agent: *\nSitemap: ${SITE}/sitemap.xml\n`,
    'og.png': 'png',
    '_nuxt/app.js': 'console.log(1)',
    ...change,
  };
  for (const [name, content] of Object.entries(files)) {
    if (content === null) continue;
    mkdirSync(join(dir, name, '..'), { recursive: true });
    writeFileSync(join(dir, name), content);
  }
  return checkBuild(dir, { data });
}

afterEach(() => rmSync(dir, { recursive: true, force: true }));

test('a whole build passes', () => {
  assert.deepEqual(build(), []);
});

const broken = [
  ['a missing page', { 'privacy.html': null }, /privacy\.html is missing/],
  ['content built by a script only', { 'index.html': page({ path: '/', body: '<div id="app"></div>' }) }, /"What should I watch\?" is not in its HTML/],
  ['content only in an inline script', { 'index.html': page({ path: '/', body: '<script>window.data = "What should I watch?"</script>' }) }, /"What should I watch\?" is not in its HTML/],
  ['a wrong canonical', { 'privacy.html': page({ path: '/privacy/', body: 'What we collect' }) }, /canonical is .*\/privacy\/, expected/],
  ['a page without a title', { 'index.html': page({ path: '/', body: 'What should I watch?' }).replace(/<title>.*<\/title>/, '') }, /index\.html: no <title>/],
  ['an indexable 404 page', { '404.html': page({ body: 'This page is not here' }) }, /the 404 page is not noindex/],
  ['a noindex page', { 'privacy.html': page({ path: '/privacy', body: 'What we collect', noindex: true }) }, /noindex on an indexable page/],
  ['a sitemap missing a page', { 'sitemap.xml': `<urlset><url><loc>${SITE}/</loc></url></urlset>` }, /sitemap\.xml lists/],
  ['robots.txt without the sitemap', { 'robots.txt': 'User-agent: *\n' }, /robots\.txt does not name the sitemap/],
  ['the Twitter link', { '_nuxt/app.js': 'href="https://twitter.com/pcesports"' }, /twitter/],
  ['the icon font', { '_nuxt/app.css': '@font-face{font-family:icomoon}' }, /icomoon/],
  ['a source map', { '_nuxt/app.js.map': '{}' }, /a source map/],
];
for (const [name, change, expected] of broken) {
  test(`it stops ${name}`, () => {
    const problems = build(change);
    assert.ok(problems.some((problem) => expected.test(problem)), problems.join('\n') || 'no problem found');
  });
}

// One listed match, MOUZ 2-1 NAVI over three maps, as the data job stores it and lists it.
const LISTED = {
  matches: [{ id: 7, teams: [{ id: 3240, name: 'MOUZ' }, { id: 3216, name: 'Natus Vincere' }], winnerId: 3240, score: [2, 1], bestOf: 3, mapSeconds: [3125, 2710, 3962] }],
  entries: [{ matchId: 7, start: '2026-11-02T02:00:00Z', event: 'IEM Beijing 2026', stage: 'Playoffs · Semifinal 1', teams: ['MOUZ', 'Natus Vincere'], label: 'Must watch', videoId: 'v7' }],
};
const listedPage = (body) => page({
  path: '/',
  body: '<h2>What should I watch?</h2><p>IEM Beijing 2026</p><p>Playoffs · Semifinal 1</p><time datetime="2026-11-02">Mon 2 Nov</time>'
    + '<details><summary>Show teams</summary>MOUZ vs Natus Vincere</details>'
    + `<a aria-label="Watch the VOD: IEM Beijing 2026, Playoffs · Semifinal 1, on YouTube" href="https://www.youtube.com/watch?v=v7">Watch the VOD</a>${body}`,
}).replace('</head>', '<meta property="og:image:width" content="1200"><meta property="og:image:height" content="630"></head>');

test('a build that lists a match without its result passes, years, sizes and the router\'s own score field and all', () => {
  // A time and a decimal around the score's digits are neither a score nor a spoiler
  const index = listedPage('<p>Kick-off 10:2-1 slot, 1.2-1 odds, 2-1:30 runtime</p><p>© 2014-2026</p><p>3125 fans</p><p>The result: a list of matches.</p><svg><path d="M3 0-3 4 2-1z"/></svg>'
    + '<script>window.__NUXT__={};window.__NUXT__.config={public:{},app:{baseURL:"/"}}</script>');
  // Map lengths that are also the event's year and the Open Graph image's width
  const data = { ...LISTED, matches: [{ ...LISTED.matches[0], mapSeconds: [2026, 1200, 3962] }] };
  assert.deepEqual(build({ 'index.html': index, '_payload.json': '[{"teams":1,"label":2},["MOUZ","Natus Vincere"],"Must watch"]', '_nuxt/app.js': 'return{re:o,score:r}' }, data), []);
  // A listed match with no number to look for: nothing to find, and no crash
  const unnumbered = listedPage('<p data-x="1">MOUZ &#99999999;</p>');
  assert.deepEqual(build({ 'index.html': unnumbered }, { ...LISTED, matches: [{ ...LISTED.matches[0], winnerId: null, mapSeconds: [] }] }), []);
});

const spoiled = [
  ['the score on the page', { 'index.html': listedPage('<p>MOUZ 2-1 Natus Vincere</p>') }, /holds the score or map count of listed match 7/],
  ['the score the other way round', { 'index.html': listedPage('<p>1 : 2</p>') }, /holds the score or map count of listed match 7/],
  ['the map count on the page', { 'index.html': listedPage('<p>went to 3 maps</p>') }, /holds the score or map count of listed match 7/],
  ['the winner in the payload', { '_payload.json': '[{"teams":1,"w":3},["MOUZ","Natus Vincere"],3240]' }, /_payload\.json: holds a winner's team id or a map length of listed match 7/],
  ['a map length in the page\'s inline payload', { 'index.html': listedPage('<script type="application/json" id="__NUXT_DATA__">[{"m":1},3962]</script>') }, /index\.html: holds a winner's team id or a map length of listed match 7/],
  ['the match record in a script', { '_nuxt/app.js': 'const m={winnerId:3240,score:[2,1]}' }, /_nuxt\/app\.js: holds a match result field \(winnerId:\)/],
  ['the score field in the payload', { '_payload.json': '[{"score":1},[2,1]]' }, /_payload\.json: holds a match result field \("score":\)/],
  ['the score as text in the payload', { '_payload.json': '[{"teams":1,"s":3},["MOUZ","Natus Vincere"],"2-1"]' }, /_payload\.json: holds the score or map count of listed match 7/],
  ['the score in the page\'s inline payload', { 'index.html': listedPage('<script type="application/json" id="__NUXT_DATA__">[{"s":1},"1:2"]</script>') }, /index\.html: holds the score or map count of listed match 7/],
  ['the map count in the payload', { '_payload.json': '[{"teams":1,"n":2},["MOUZ","Natus Vincere"],"3 maps"]' }, /_payload\.json: holds the score or map count of listed match 7/],
  ['the score in an attribute', { 'index.html': listedPage('<a aria-label="MOUZ won 2-1" href="#">VOD</a>') }, /index\.html: holds the score or map count of listed match 7/],
  ['the map count in an attribute', { 'index.html': listedPage('<span title="3 maps"></span>') }, /index\.html: holds the score or map count of listed match 7/],
  ['the winner\'s id as a string', { '_payload.json': '[{"w":1},"3240"]' }, /_payload\.json: holds a winner's team id or a map length of listed match 7/],
  ['a result field', { '_payload.json': '[{"result":1},[2,1]]' }, /_payload\.json: holds a match result field \("result":\)/],
  ['the maps field', { '_payload.json': '[{"games":1},[]]' }, /_payload\.json: holds a match result field \("games":\)/],
  ['the winner\'s id in an attribute', { 'index.html': listedPage('<p data-w="3240">MOUZ</p>') }, /index\.html: holds a winner's team id or a map length of listed match 7/],
  ['the score as an HTML entity', { 'index.html': listedPage('<p>2&ndash;1</p>') }, /index\.html: holds the score or map count of listed match 7/],
  ['the score in words', { 'index.html': listedPage('<p>won it 2 to 1</p>') }, /index\.html: holds the score or map count of listed match 7/],
  ['the deciding map named', { 'index.html': listedPage('<p>decided on map 3</p>') }, /index\.html: holds the score or map count of listed match 7/],
  ['the map count in a single-quoted attribute', { 'index.html': listedPage('<span title=\'3 maps\'></span>') }, /index\.html: holds the score or map count of listed match 7/],
  ['the maps field in the inline payload', { 'index.html': listedPage('<script type="application/json" id="__NUXT_DATA__">[{"maps":1},3]</script>') }, /index\.html: holds a match result field \("maps":\)/],
  ['the record in an inline script', { 'index.html': listedPage('<script>window.__NUXT__={data:{x:{winner:3240,maps:3}}}</script>') }, /index\.html: holds a match result field \(winner:\)/],
  ['the winner\'s id in an inline script', { 'index.html': listedPage('<script>window.__W=3240;</script>') }, /index\.html: holds a winner's team id or a map length of listed match 7/],
  ['the score in an inline script', { 'index.html': listedPage('<script>window.__S="2-1"</script>') }, /index\.html: holds the score or map count of listed match 7/],
  ['the score in a comment', { 'index.html': listedPage('<!-- 1-2 -->') }, /index\.html: holds the score or map count of listed match 7/],
  ['the score with an em dash', { 'index.html': listedPage('<p>2 — 1</p>') }, /index\.html: holds the score or map count of listed match 7/],
  ['the score with a numeric hyphen', { 'index.html': listedPage('<p>2&#45;1</p>') }, /index\.html: holds the score or map count of listed match 7/],
  ['the deciding map capitalised', { 'index.html': listedPage('<h3>Map 3: Nuke</h3>') }, /index\.html: holds the score or map count of listed match 7/],
  ['the map count capitalised', { 'index.html': listedPage('<p>3 Maps</p>') }, /index\.html: holds the score or map count of listed match 7/],
  ['the score with an upper-case hex hyphen', { 'index.html': listedPage('<p>2&#x2D;1</p>') }, /index\.html: holds the score or map count of listed match 7/],
  ['the score with a minus sign', { 'index.html': listedPage('<p>2 − 1</p>') }, /index\.html: holds the score or map count of listed match 7/],
  ['the map count with a non-breaking space', { 'index.html': listedPage('<p>3&nbsp;maps</p>') }, /index\.html: holds the score or map count of listed match 7/],
  ['the winner\'s id ending an inline script', { 'index.html': listedPage('<script>window.__W = 3240\n</script>') }, /index\.html: holds a winner's team id or a map length of listed match 7/],
  ['the winner\'s id as an argument', { 'index.html': listedPage('<script>show(3240)</script>') }, /index\.html: holds a winner's team id or a map length of listed match 7/],
  ['the score ending a sentence', { 'index.html': listedPage('<p>MOUZ won 2-1.</p>') }, /index\.html: holds the score or map count of listed match 7/],
  ['the map count ending a sentence', { '_payload.json': '[{"t":1},"It went to 3 maps."]' }, /_payload\.json: holds the score or map count of listed match 7/],
  ['the score after a colon', { 'index.html': listedPage('<p>Score:2-1</p>') }, /index\.html: holds the score or map count of listed match 7/],
  ['the map count with a bare non-breaking space', { 'index.html': listedPage('<p>3&nbspmaps</p>') }, /index\.html: holds the score or map count of listed match 7/],
  ['the score with thin spaces and a dash entity', { 'index.html': listedPage('<p>2&thinsp;&dash;&thinsp;1</p>') }, /index\.html: holds the score or map count of listed match 7/],
  ['the map count as a label', { 'index.html': listedPage('<dt>Maps:</dt> <dd>3</dd>') }, /index\.html: holds the score or map count of listed match 7/],
  ['the map count as played', { '_payload.json': '[{"t":1},"Maps played: 3"]' }, /_payload\.json: holds the score or map count of listed match 7/],
  ['the map count as a compound', { 'index.html': listedPage('<p>a 3-map series</p>') }, /index\.html: holds the score or map count of listed match 7/],
  ['the map count as a 3-mapper', { 'index.html': listedPage('<p>a 3-mapper</p>') }, /index\.html: holds the score or map count of listed match 7/],
  ['the score in a Windows-1252 reference', { 'index.html': listedPage('<p>2&#150;1</p>') }, /index\.html: holds the score or map count of listed match 7/],
  ['the map count with a hair space', { 'index.html': listedPage('<p>3&numsp;maps</p>') }, /index\.html: holds the score or map count of listed match 7/],
  ['a listed match the data lacks', { 'index.html': listedPage('') }, /the watch list holds match 8, which the matches data lacks/, { ...LISTED, entries: [...LISTED.entries, { ...LISTED.entries[0], matchId: 8 }] }],
  ['the map lengths field in the HTML', { 'index.html': listedPage('<script>window.x={"mapSeconds":[]}</script>') }, /holds a match result field \("mapSeconds":\)/],
];

for (const [what, change, expected, data = LISTED] of spoiled) {
  test(`a build with ${what} fails`, () => {
    const problems = build(change, data);
    assert.ok(problems.some((problem) => expected.test(problem)), problems.join('\n') || 'no problem found');
  });
}

test('the check reads the data the build was made from, and nothing without it', () => {
  const root = mkdtempSync(join(tmpdir(), 'pce-build-data-'));
  try {
    assert.deepEqual(buildData(root), { matches: [], entries: [] });
    for (const [path, body] of [[MATCHES_FILE, { matches: LISTED.matches }], [WATCH_FILE, { entries: LISTED.entries }]]) {
      mkdirSync(join(root, path, '..'), { recursive: true });
      writeFileSync(join(root, path), JSON.stringify(body));
    }
    assert.deepEqual(buildData(root), LISTED);
  } finally {
    rmSync(root, { recursive: true, force: true });
  }
});
