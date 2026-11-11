// The Markdown the guides are written in: a small subset, parsed into blocks the page renders with Vue (no v-html).
// Anything outside the subset fails with its line, so a guide never renders differently from how it reads.

/** A run of text in a block: plain, bold, or a link (`/guides/...` inside the site, `https://` outside it). */
export type Inline =
  | { kind: 'text'; text: string }
  | { kind: 'strong'; text: string }
  | { kind: 'link'; text: string; href: string };

export type Block =
  | { kind: 'h2' | 'h3' | 'p'; inlines: Inline[] }
  | { kind: 'ul' | 'ol'; items: Inline[][] };

const INLINE = /\*\*([^*]+)\*\*|\[([^\]]+)\]\(([^)\s]+)\)/g;
/**
 * What the subset doesn't have: emphasis, code, HTML, images, footnotes, a bracket that isn't a link. An underscore
 * inside a word is a name's (GAM3RS_X), not emphasis.
 */
const UNSUPPORTED_INLINE = /[*`<[\]]|(?<![\p{L}\p{N}])_|_(?![\p{L}\p{N}])/u;
const UNSUPPORTED_LINE = /^(#|>|\||\* |\+ |`|<|---|===|\d+\)|\s)/;

function fail(line: number, message: string): never {
  throw new Error(`line ${line}: ${message}`);
}

/** `Lead with **the answer**, then [the detail](/guides/x).` -> its runs. */
export function parseInline(text: string, line = 0): Inline[] {
  const inlines: Inline[] = [];
  const plain = (part: string) => {
    if (UNSUPPORTED_INLINE.test(part)) fail(line, `"${part.trim()}" uses Markdown the guides don't support (only **bold** and [links](/path))`);
    if (part) inlines.push({ kind: 'text', text: part });
  };
  let at = 0;
  for (const match of text.matchAll(INLINE)) {
    plain(text.slice(at, match.index));
    if (match[1] !== undefined) {
      if (UNSUPPORTED_INLINE.test(match[1])) fail(line, `bold "${match[1]}" uses Markdown the guides don't support`);
      inlines.push({ kind: 'strong', text: match[1] });
    }
    else {
      const [, , label, href] = match as unknown as [string, undefined, string, string];
      if (text[match.index - 1] === '!') fail(line, `"![${label}](${href})": the guides have no images`);
      if (!/^(\/|https:\/\/)/.test(href)) fail(line, `link "${href}": a path on the site (/...) or an https:// address`);
      if (UNSUPPORTED_INLINE.test(label)) fail(line, `link text "${label}" uses Markdown the guides don't support`);
      inlines.push({ kind: 'link', text: label, href });
    }
    at = match.index + match[0].length;
  }
  plain(text.slice(at));
  return inlines;
}

/**
 * A guide's body: `## ` and `### ` headings (one line each), paragraphs (lines joined by blank lines), `- ` and `1. `
 * lists (an item goes on over lines indented by two spaces). `line` numbers errors from where the body starts.
 */
export function parseMarkdown(source: string, firstLine = 1): Block[] {
  const blocks: Block[] = [];
  const lines = source.split('\n');
  let index = 0;
  const lineOf = (i: number) => i + firstLine;
  while (index < lines.length) {
    const line = (lines[index] as string).trimEnd();
    if (!line) {
      index++;
      continue;
    }
    const heading = line.match(/^(##|###) (.+)$/);
    if (heading) {
      blocks.push({ kind: heading[1] === '##' ? 'h2' : 'h3', inlines: parseInline(heading[2] as string, lineOf(index)) });
      index++;
      continue;
    }
    const marker = line.match(/^(- |\d+\. )/);
    if (marker) {
      const ordered = marker[1] !== '- ';
      const items: Inline[][] = [];
      while (index < lines.length) {
        const item = (lines[index] as string).trimEnd();
        const start = item.match(ordered ? /^\d+\. (.+)$/ : /^- (.+)$/);
        if (!start) break;
        const at = index;
        const parts = [start[1] as string];
        index++;
        while (index < lines.length && /^ {2}\S/.test(lines[index] as string)) parts.push((lines[index++] as string).trim());
        items.push(parseInline(parts.join(' '), lineOf(at)));
      }
      const next = (lines[index] ?? '').trimEnd();
      if (next) fail(lineOf(index), 'a list ends with a blank line');
      blocks.push({ kind: ordered ? 'ol' : 'ul', items });
      continue;
    }
    if (UNSUPPORTED_LINE.test(line)) fail(lineOf(index), `"${line}" uses Markdown the guides don't support`);
    const at = index;
    const parts: string[] = [];
    while (index < lines.length && (lines[index] as string).trim()) {
      const next = (lines[index] as string).trimEnd();
      if (UNSUPPORTED_LINE.test(next) || /^(##|###) |^(- |\d+\. )/.test(next)) fail(lineOf(index), 'a heading or a list starts after a blank line');
      parts.push(next);
      index++;
    }
    blocks.push({ kind: 'p', inlines: parseInline(parts.join(' '), lineOf(at)) });
  }
  return blocks;
}

/** A block's runs as plain text. */
export function textOf(inlines: Inline[]): string {
  return inlines.map((inline) => inline.text).join('');
}
