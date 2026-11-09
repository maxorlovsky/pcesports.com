// Checks a static build (`npm run generate` writes .output/public) before it ships: run by `npm run check:build`,
// by the deploy before the site's link moves (tools/deploy/deploy.mjs), and in CI.
//   node tools/check-build.mjs <dir>
// Each page must carry its content in the HTML itself (readable with JavaScript off) and its SEO tags; the sitemap
// must list exactly the indexable pages, at the canonical host; nothing of the old site may come back; and no result
// of a match the home page lists (score, winner, map count) may be anywhere in it (AGENTS.md, "No spoilers").
import { existsSync, readdirSync, readFileSync, statSync } from 'node:fs';
import { join, relative, resolve } from 'node:path';
import { pathToFileURL } from 'node:url';
import { MATCHES_FILE, WATCH_FILE } from './data-job/job.mjs';

const ROOT = resolve(import.meta.dirname, '..');

const SITE_URL = 'https://www.pcesports.com';
/** Each page the build must have: its file, its path, and a sentence its HTML must hold without JavaScript. */
export const PAGES = [
  { file: 'index.html', path: '/', text: 'What should I watch?' },
  { file: 'privacy.html', path: '/privacy', text: 'What we collect' },
  { file: '404.html', path: null, text: 'This page is not here' },
];
/** What the old site had and the new one must not: the deleted Twitter account, the LoL background, the icon font. */
const BANNED = [/(twitter|x)\.com\/pcesports/i, /icomoon/i, /bg\.jpg/i, /fonts\.googleapis\.com/i];

function files(dir) {
  return readdirSync(dir).flatMap((name) => {
    const path = join(dir, name);
    return statSync(path).isDirectory() ? files(path) : [path];
  });
}

/**
 * A built file as the spoiler check reads it: its data (JSON, a page's inline JSON such as Nuxt's payload), its code
 * (a script bundle, a page's inline scripts), its text-bearing attributes' values, and all the text a reader, a screen
 * reader or a script gets (the data, the page's text and those attributes).
 */
function parts(name, content) {
  if (name.endsWith('.json')) return { data: content, code: '', attributes: [], text: content };
  if (name.endsWith('.js')) return { data: '', code: content, attributes: [], text: '' };
  const scripts = [...content.matchAll(/<script([^>]*)>([\s\S]*?)<\/script>/g)];
  const data = scripts.filter((match) => /type=["']application\/(ld\+)?json["']/.test(match[1])).map((match) => match[2]).join('\n');
  const code = scripts.filter((match) => !/type=["']application\/(ld\+)?json["']/.test(match[1])).map((match) => match[2]).join('\n');
  const markup = content.replace(/<(script|style)[\s\S]*?<\/\1>/g, '');
  const attributes = [...markup.matchAll(TEXT_ATTRIBUTES)].map((match) => match[3]);
  return { data, code, attributes, text: [markup.replace(/<[^>]+>/g, ' '), ...attributes, data].join(' ') };
}

function attr(html, pattern) {
  return html.match(pattern)?.[1];
}

/**
 * A match's result fields, as an object key: the record must never ship. In data (JSON, a page's inline JSON) every
 * field; in code only the names no library uses (Vue's router has a `score`). A page's prose ("The result: …") is
 * neither.
 */
const RESULT_KEY = /["']?\b(winnerId|winner_id|winner|score|results?|mapSeconds|maps|games|forfeit)\b["']?\s*:/;
const RESULT_KEY_IN_CODE = /["']?\b(winnerId|winner_id|mapSeconds)\b["']?\s*:/;
/** The attributes that carry text or data a reader, a screen reader or a script gets; never SVG geometry or classes. */
const TEXT_ATTRIBUTES = /\s(aria-[\w-]+|data-[\w-]+|title|alt|content|value|placeholder|label|href)\s*=\s*(["'])([\s\S]*?)\2/g;
/** Between the two numbers of a score: "2-1", "2 – 1", "2:1", "2&ndash;1", "2 to 1". */
const SCORE_SEPARATOR = '\\s*(?:[-–:]|&ndash;|&#8211;|&#x2013;)\\s*|\\s+to\\s+';

/** The data a build was made from: the data job's matches and the watch list (none: a build without data). */
export function buildData(root = ROOT) {
  const read = (path, key) => {
    const file = join(root, path);
    return existsSync(file) ? JSON.parse(readFileSync(file, 'utf8'))[key] ?? [] : [];
  };
  return { matches: read(MATCHES_FILE, 'matches'), entries: read(WATCH_FILE, 'entries') };
}

/**
 * The results of the listed matches, as they could leak: each winner's team id and each map's length (as values in
 * data or attributes), and each series score or map count as text ("2-1", "1 to 2", "3 maps", "map 3" past the maps
 * every series plays). A listed match with no record is a problem of its own: the check can't know its result.
 *
 * It catches the record and its numbers however they are serialised, and the common ways to write a score; it can't
 * read every sentence ("the second team won"). The page's own guarantee is what it is given: only the shown fields.
 */
function spoilers({ matches, entries }) {
  const byId = new Map(matches.map((match) => [match.id, match]));
  const missing = entries.filter((entry) => !byId.has(entry.matchId)).map((entry) => entry.matchId);
  const leaks = entries.map((entry) => byId.get(entry.matchId)).filter(Boolean).map((match) => {
    const [a, b] = match.score ?? [];
    // From 1000: team ids start above it, a real map lasts longer, and a payload's own indexes stay below it.
    const numbers = [match.winnerId, ...(match.mapSeconds ?? [])].filter((value) => Number.isInteger(value) && value >= 1000);
    const scores = a === undefined ? [] : [`${a}(?:${SCORE_SEPARATOR})${b}`, `${b}(?:${SCORE_SEPARATOR})${a}`];
    const maps = match.mapSeconds?.length ?? 0;
    if (maps) scores.push(`${maps}\\s+maps?`);
    if (maps > Math.ceil((match.bestOf ?? 1) / 2)) scores.push(`maps?\\s+${maps}`);
    const list = numbers.join('|');
    return {
      match: `match ${match.id}`,
      // As a serialised value (`,3216,`, `[3216]`, `:"3216"}`): a page's text holds years and counts of its own.
      number: numbers.length ? new RegExp(`(?<=[:,[]\\s*"?)(${list})(?="?\\s*[,\\]}])`) : null,
      // An attribute's value, whole or as one of its words (`data-w="3240"`), but not inside a date or an id.
      inAttribute: numbers.length ? new RegExp(`(?<![\\w.:-])(${list})(?![\\w.:-])`) : null,
      score: scores.length ? new RegExp(`(?<![\\w.:–-])(${scores.join('|')})(?![\\w.:–-])`) : null,
    };
  });
  return { leaks, missing };
}

export function checkBuild(dir, { data = buildData() } = {}) {
  const problems = [];
  const problem = (message) => problems.push(message);
  if (!existsSync(dir)) return [`${dir} does not exist: run npm run generate first`];

  for (const { file, path, text } of PAGES) {
    const where = join(dir, file);
    if (!existsSync(where)) {
      problem(`${file} is missing`);
      continue;
    }
    const html = readFileSync(where, 'utf8');
    const title = attr(html, /<title>([^<]+)<\/title>/);
    if (!title) problem(`${file}: no <title>`);
    if (!attr(html, /<meta name="description" content="([^"]+)"/)) problem(`${file}: no meta description`);
    // The content is in the HTML, not built by a script: what a reader with JavaScript off and a crawler see
    const visible = html.replace(/<(script|style)[\s\S]*?<\/\1>/g, '').replace(/<[^>]+>/g, '');
    if (!visible.replace(/&#39;|&#x27;/g, '\'').includes(text)) problem(`${file}: "${text}" is not in its HTML`);
    for (const property of ['og:title', 'og:description', 'og:image', 'og:type']) {
      if (!attr(html, new RegExp(`<meta property="${property}" content="([^"]+)"`))) problem(`${file}: no ${property}`);
    }
    const canonical = attr(html, /<link rel="canonical" href="([^"]+)"/);
    if (path) {
      const expected = `${SITE_URL}${path}`;
      if (canonical !== expected) problem(`${file}: canonical is ${canonical ?? 'missing'}, expected ${expected}`);
      if (attr(html, /<meta property="og:url" content="([^"]+)"/) !== expected) problem(`${file}: og:url is not ${expected}`);
      if (/<meta name="robots" content="[^"]*noindex/.test(html)) problem(`${file}: noindex on an indexable page`);
    } else {
      if (canonical) problem(`${file}: the 404 page has a canonical URL`);
      if (!/<meta name="robots" content="noindex"/.test(html)) problem(`${file}: the 404 page is not noindex`);
    }
  }

  const sitemapFile = join(dir, 'sitemap.xml');
  if (!existsSync(sitemapFile)) problem('sitemap.xml is missing');
  else {
    const listed = [...readFileSync(sitemapFile, 'utf8').matchAll(/<loc>([^<]+)<\/loc>/g)].map((match) => match[1]).sort();
    const expected = PAGES.filter((page) => page.path).map((page) => `${SITE_URL}${page.path}`).sort();
    if (JSON.stringify(listed) !== JSON.stringify(expected)) problem(`sitemap.xml lists ${listed.join(', ') || 'nothing'}, expected ${expected.join(', ')}`);
  }
  const robotsFile = join(dir, 'robots.txt');
  if (!existsSync(robotsFile)) problem('robots.txt is missing');
  else if (!readFileSync(robotsFile, 'utf8').includes(`Sitemap: ${SITE_URL}/sitemap.xml`)) problem('robots.txt does not name the sitemap');
  if (!existsSync(join(dir, 'og.png'))) problem('og.png, the Open Graph image, is missing');

  const { leaks, missing } = spoilers(data);
  for (const id of missing) problem(`the watch list holds match ${id}, which the matches data lacks: its result can't be checked`);
  for (const path of files(dir)) {
    const name = relative(dir, path);
    if (name.endsWith('.map')) problem(`${name}: a source map in the build`);
    if (!/\.(html|js|css|json|xml|txt)$/.test(name)) continue;
    const content = readFileSync(path, 'utf8');
    for (const pattern of BANNED) if (pattern.test(content)) problem(`${name}: matches ${pattern}, which the new site dropped`);
    if (!/\.(html|js|json)$/.test(name)) continue;
    const { data: values, code, attributes, text } = parts(name, content);
    const key = values.match(RESULT_KEY) ?? code.match(RESULT_KEY_IN_CODE);
    if (key) problem(`${name}: holds a match result field (${key[0].trim()}), a spoiler`);
    // Numbers only in data and attributes: a script bundle is full of unrelated ones.
    for (const leak of leaks) {
      if (leak.number?.test(values) || attributes.some((value) => leak.inAttribute.test(value))) {
        problem(`${name}: holds a winner's team id or a map length of listed ${leak.match}, a spoiler`);
      }
      if (leak.score?.test(text)) problem(`${name}: holds the score or map count of listed ${leak.match}, a spoiler`);
    }
  }
  return problems;
}

if (import.meta.url === pathToFileURL(process.argv[1]).href) {
  const dir = process.argv[2];
  if (!dir) {
    console.error('usage: node tools/check-build.mjs <dir>');
    process.exit(2);
  }
  const problems = checkBuild(dir);
  if (problems.length) {
    console.error(`Build check failed for ${dir}:\n${problems.map((p) => `- ${p}`).join('\n')}`);
    process.exit(1);
  }
  console.log(`Build check passed: ${dir}`);
}
