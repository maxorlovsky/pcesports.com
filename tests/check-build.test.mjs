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
  const url = path === '/' ? `${SITE}/` : `${SITE}${path}`;
  return `<!DOCTYPE html><html><head><title>${title}</title><meta name="description" content="About it">`
    + (path ? `<link rel="canonical" href="${url}"><meta property="og:url" content="${url}">` : '')
    + (noindex ? '<meta name="robots" content="noindex">' : '')
    + `<meta property="og:title" content="${title}"><meta property="og:description" content="About it">`
    + `<meta property="og:image" content="${SITE}/og.png"><meta property="og:type" content="website">`
    + `</head><body><main>${body}</main></body></html>`;
}

function build(change = {}) {
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
  return checkBuild(dir);
}

afterEach(() => rmSync(dir, { recursive: true, force: true }));

test('a whole build passes', () => {
  assert.deepEqual(build(), []);
});

const broken = [
  ['a missing page', { 'privacy.html': null }, /privacy\.html is missing/],
  ['content built by a script only', { 'index.html': page({ path: '/', body: '<div id="app"></div>' }) }, /"What should I watch\?" is not in its HTML/],
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
