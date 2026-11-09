// tools/check-build.mjs on a small hand-made build: what passes, and each kind of build it must stop.
import assert from 'node:assert/strict';
import { mkdirSync, mkdtempSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { afterEach, test } from 'node:test';
import { checkBuild } from '../tools/check-build.mjs';

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
  matches: [{ id: 7, teams: [{ id: 3240, name: 'MOUZ' }, { id: 3216, name: 'Natus Vincere' }], winnerId: 3240, score: [2, 1], mapSeconds: [3125, 2710, 3962] }],
  entries: [{ matchId: 7, start: '2026-11-02T02:00:00Z', event: 'IEM Beijing 2026', stage: 'Playoffs · Semifinal 1', teams: ['MOUZ', 'Natus Vincere'], label: 'Must watch', videoId: 'v7' }],
};
const listedPage = (body) => page({ path: '/', body: `<h2>What should I watch?</h2><p>IEM Beijing 2026, Playoffs · Semifinal 1, Mon 2 Nov</p><details><summary>Show teams</summary>MOUZ vs Natus Vincere</details>${body}` });

test('a build that lists a match without its result passes', () => {
  assert.deepEqual(build({ 'index.html': listedPage(''), '_payload.json': '[{"teams":1,"label":2},["MOUZ","Natus Vincere"],"Must watch"]' }, LISTED), []);
});

const spoiled = [
  ['the score on the page', { 'index.html': listedPage('<p>MOUZ 2-1 Natus Vincere</p>') }, /shows the score or map count of listed match 7/],
  ['the score the other way round', { 'index.html': listedPage('<p>1 : 2</p>') }, /shows the score or map count of listed match 7/],
  ['the map count on the page', { 'index.html': listedPage('<p>went to 3 maps</p>') }, /shows the score or map count of listed match 7/],
  ['the winner in the payload', { '_payload.json': '[{"teams":1,"w":3},["MOUZ","Natus Vincere"],3240]' }, /_payload\.json: holds a winner's team id or a map length of listed match 7/],
  ['a map length in the HTML', { 'index.html': listedPage('<span data-length="3962"></span>') }, /index\.html: holds a winner's team id or a map length of listed match 7/],
  ['the match record in a script', { '_nuxt/app.js': 'const m={winnerId:3240,score:[2,1]}' }, /_nuxt\/app\.js: holds a match result field \(winnerId:\)/],
  ['the score field in the payload', { '_payload.json': '[{"score":1},[2,1]]' }, /_payload\.json: holds a match result field \("score":\)/],
  ['the map lengths field in the HTML', { 'index.html': listedPage('<script>window.x={"mapSeconds":[]}</script>') }, /holds a match result field \("mapSeconds":\)/],
];

for (const [what, change, expected] of spoiled) {
  test(`a build with ${what} fails`, () => {
    const problems = build(change, LISTED);
    assert.ok(problems.some((problem) => expected.test(problem)), problems.join('\n') || 'no problem found');
  });
}
