// Checks a static build (`npm run generate` writes .output/public) before it ships: run by `npm run check:build`,
// by the deploy before the site's link moves (tools/deploy/deploy.mjs), and in CI.
//   node tools/check-build.mjs <dir>
// Each page must carry its content in the HTML itself (readable with JavaScript off) and its SEO tags; the sitemap
// must list exactly the indexable pages, at the canonical host; nothing of the old site may come back; no page's HTML
// may load Google's analytics tag, which waits for the visitor's "Accept"; and no result of a match the home page lists
// (score, winner, map count) may be anywhere in it (AGENTS.md, "No spoilers").
import { existsSync, readdirSync, readFileSync, statSync } from 'node:fs';
import { join, relative, resolve } from 'node:path';
import { pathToFileURL } from 'node:url';
import { tokenizer } from 'acorn';
import { decodeHTML } from 'entities';
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
/**
 * Google's analytics tag, which only the visitor's "Accept" may load (the `site` domain's script): a page whose HTML
 * names it would load it, or reach Google, before any choice.
 */
const GOOGLE_TAG = /googletagmanager\.com|google-analytics\.com|\bgtag\(/i;

function files(dir) {
  return readdirSync(dir).flatMap((name) => {
    const path = join(dir, name);
    return statSync(path).isDirectory() ? files(path) : [path];
  });
}

/**
 * A built file as the spoiler check reads it:
 * - `data`: what may carry a match's record: a JSON file, and every script a page holds inline (Nuxt's payload, as
 *   JSON or as `window.__NUXT__ = {…}`);
 * - `code`: a script bundle, which holds unrelated keys and numbers of its own, read for result keys and a listed
 *   match's own `score: [2, 1]` or `maps: 3`;
 * - `values`: the attributes that carry values for a script (`data-*`, `value`), read for ids and lengths;
 * - `text`: all a reader, a screen reader or a script can put on a page (the page's text and comments, its text-bearing
 *   attributes, the data's strings and a bundle's, with any markup in them, the text a stylesheet's `content` shows),
 *   read for a score, a map count or the winner.
 */
function parts(name, content) {
  if (name.endsWith('.json')) return { data: content, code: '', values: [], text: decode(readData(content)) };
  if (name.endsWith('.js')) return { data: '', code: content, values: [], text: decode(withMarkupDropped(stringsOf(content))) };
  if (name.endsWith('.css')) return { data: '', code: '', values: [], text: decode(shownByStyles(content)) };
  const data = [...content.matchAll(/<script[^>]*>([\s\S]*?)<\/script>/g)].map((match) => match[1]).join('\n');
  // A style attribute's value reaches the CSS parser with its entities decoded (Vue writes a bound style's quotes so)
  const styles = [...content.matchAll(/<style[^>]*>([\s\S]*?)<\/style>|\sstyle\s*=\s*(["'])([\s\S]*?)\2/g)].map((match) => match[1] ?? decodeHTML(match[3]));
  const markup = content.replace(/<(script|style)[\s\S]*?<\/\1>/g, '');
  const attributes = [...markup.matchAll(TEXT_ATTRIBUTES)];
  const values = attributes.filter((match) => /^(data-|value$)/.test(match[1])).map((match) => match[3]);
  const comments = [...markup.matchAll(/<!--([\s\S]*?)-->/g)].map((match) => match[1]);
  const text = [markup.replace(/<!--[\s\S]*?-->/g, ' ').replace(/<[^>]+>/g, ' '), ...comments, ...attributes.map((match) => match[3]), readData(data), ...styles.map(shownByStyles)];
  return { data, code: '', values, text: decode(text.join(' ')) };
}

function attr(html, pattern) {
  return html.match(pattern)?.[1];
}

/**
 * A match's result fields, as an object key: the record must never ship. In data (JSON, a page's inline scripts) every
 * field; in a bundle only the names no library uses (Vue's router has a `score`). A page's prose ("The result: …") is
 * neither.
 */
const RESULT_KEY = /["']?\b(winnerId|winner_id|winner|score|results?|mapSeconds|maps|games|forfeit)\b["']?\s*:/;
const RESULT_KEY_IN_CODE = /["']?\b(winnerId|winner_id|winner|mapSeconds)\b["']?\s*:/;
/** The attributes that carry text or data a reader, a screen reader or a script gets; never SVG geometry or classes. */
const TEXT_ATTRIBUTES = /\s(aria-[\w-]+|data-[\w-]+|title|alt|content|value|placeholder|label|href)\s*=\s*(["'])([\s\S]*?)\2/g;
/** Between a score's two numbers, entities decoded: "2-1", "2 – 1", "2 — 1", "2 − 1", "2:1", "2 to 1". */
const SCORE_SEPARATOR = '\\s*[-‐‑–—−:]\\s*|\\s+to\\s+';
/**
 * Text as a browser shows it: character references written out (`&#45;`, `&#150;`, `&nbsp`, `&thinsp;`) and every run
 * of white space one space, as the tags each turned into one leave it ("<b>3</b> <span>maps</span>").
 */
const decode = (text) => decodeHTML(text).replace(/\s+/g, ' ');

/**
 * Data's strings as they would show: their escapes written out (JSON's `\n`, `\u002F`, a script's `\x20`, `\-`), as
 * they are and again with any markup in them dropped ("<b>3</b> maps"). Dropping markup from the whole blob would let
 * a "<3" in one string and a ">" in a later one hide all between them, so the text keeps both.
 */
function readData(data) {
  const text = data
    .replace(/\\u([\da-f]{4})|\\x([\da-f]{2})/gi, (escape, u, x) => String.fromCharCode(Number.parseInt(u ?? x, 16)))
    .replace(/\\[nrtfbv]/g, ' ')
    .replace(/\\([\s\S])/g, '$1');
  return withMarkupDropped(text);
}

/** Text as it is and again with any markup in it dropped ("<b>3</b> maps"). */
const withMarkupDropped = (text) => `${text} ${text.replace(/<[^>]+>/g, ' ')}`;

/**
 * A script bundle's strings and template literals, their escapes written out: the text it can put on a page (its code
 * holds digits of its own). A bundle that doesn't tokenize is read whole, which fails safe.
 */
function stringsOf(script) {
  try {
    return [...tokenizer(script, { ecmaVersion: 'latest', sourceType: 'module' })]
      .filter((token) => token.type.label === 'string' || token.type.label === 'template')
      .map((token) => token.value ?? '').join('\n');
  } catch {
    return script;
  }
}

/**
 * The text a stylesheet shows: its strings (`::after { content: "…" }`, custom properties'), with CSS's escapes
 * written out: a hex one (`\2d `), a backslash before a line break (the string goes on), one before any other character.
 */
function shownByStyles(css) {
  return [...css.matchAll(/(["'])((?:\\[\s\S]|(?!\1)[^\\])*)\1/g)].map((match) => match[2]
    .replace(/\\\r?\n/g, '')
    .replace(/\\([\da-f]{1,6})\s?|\\([\s\S])/gi, (escape, hex, other) => {
      if (other !== undefined) return other;
      const code = Number.parseInt(hex, 16);
      return code <= 0x10ffff ? String.fromCodePoint(code) : ' ';
    })).join(' ');
}

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
 * data or attributes), each series score or map count as text ("2-1", "1 to 2", "3 maps", "Maps: 3") or in a bundle's
 * code (`score: [2, 1]`, `maps: 3`), and the winner by name ("Winner: MOUZ", "MOUZ won"). A listed match with no record
 * is a problem of its own: the check can't know its result.
 *
 * It looks for the record's fields, ids and lengths only in data (JSON, inline scripts) and `data-*`/`value`
 * attributes, and for a score, a map count or the winner, in upper or lower case, in all the text a page, its data, its
 * styles and its scripts' strings put on it, decoded as a browser shows it. It can't read a sentence ("the second team
 * went through", "three maps", "Maps in this series: 3"), an unquoted attribute, an `iframe`'s `srcdoc`, or invisible
 * characters between the digits. The page's own guarantee is what it is given: only the shown fields.
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
    // The count however it is worded: its number within a few characters of "map", either side ("3 maps", "3—mapper",
    // "Maps: 3", "Map count = 3", "map 3"). Fails safe: "Best of 3 maps" on a match that went to 3 stops the build too.
    if (maps) scores.push(`${maps}\\W{0,3}maps?\\w*`, `maps?\\b\\D{0,15}${maps}`);
    const list = numbers.join('|');
    const winner = (match.teams ?? []).find((team) => team.id === match.winnerId);
    const names = [winner?.name, winner?.acronym].filter(Boolean).map((name) => name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
    const named = `(?:${names.join('|')})`;
    const values = [...(a === undefined ? [] : [`scores?["']?\\s*:\\s*\\[\\s*${a}\\s*,\\s*${b}\\s*\\]`]), ...(maps ? [`maps\\w*["']?\\s*:\\s*${maps}(?![\\d.])`] : [])];
    return {
      match: `match ${match.id}`,
      // As a serialised value (`,3216,`, `[3216]`, `:"3216"}`, `w=3216`, `f(3216)`).
      number: numbers.length ? new RegExp(`(?<=[:,[=(]\\s*"?)(${list})(?="?(?:\\s*[,\\]});]|\\s|$))`) : null,
      // A value attribute's value, whole or as one of its words (`data-w="3240"`), but not inside a date or an id.
      inAttribute: numbers.length ? new RegExp(`(?<![\\w.:-])(${list})(?![\\w.:-])`) : null,
      // Not inside a word, a number, a date or a time ("12-1", "2-10", "10:2-1", "1.2-1", "2-1:30"), but next to a
      // full stop or a colon ("won 2-1.", "Score:2-1").
      score: scores.length ? new RegExp(`(?<![\\w‐‑–—−-]|\\d[.:])(${scores.join('|')})(?![\\w‐‑–—−-]|[.:]\\d)`, 'i') : null,
      // The winner by name, next to a word that says so.
      winner: names.length ? new RegExp(`(?<!\\w)(?:winners?\\W{0,3}${named}|${named}\\W{0,3}(?:won|wins|beat|beats|defeated|advances?))(?!\\w)`, 'i') : null,
      // In a bundle's code, this match's own score or map count as values.
      code: values.length ? new RegExp(`\\b(?:${values.join('|')})`) : null,
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
    if (name.endsWith('.html') && GOOGLE_TAG.test(content)) problem(`${name}: names Google's analytics tag, which only loads after "Accept"`);
    if (!/\.(html|js|json|css)$/.test(name)) continue;
    const { data, code, values, text } = parts(name, content);
    const key = data.match(RESULT_KEY) ?? code.match(RESULT_KEY_IN_CODE);
    if (key) problem(`${name}: holds a match result field (${key[0].trim()}), a spoiler`);
    // Numbers only in data and value attributes: a bundle, and a page's text, hold years and sizes of their own.
    for (const leak of leaks) {
      if (leak.number?.test(data) || values.some((value) => leak.inAttribute?.test(value))) {
        problem(`${name}: holds a winner's team id or a map length of listed ${leak.match}, a spoiler`);
      }
      if (leak.score?.test(text) || leak.code?.test(code)) problem(`${name}: holds the score or map count of listed ${leak.match}, a spoiler`);
      if (leak.winner?.test(text)) problem(`${name}: holds the winner of listed ${leak.match}, a spoiler`);
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
