// A guide is one Markdown file in ./content/: a header (title, description, order) and a body (markdown.ts). This file
// turns one into what the pages show; load.ts reads the folder.
import { parseMarkdown, textOf } from './markdown.ts';
import type { Block } from './markdown.ts';

export interface GuideSummary {
  /** The file's name without `.md`: the page is `/guides/<slug>`. */
  slug: string;
  /** The page's h1 and title. */
  title: string;
  /** The page's meta description, and the guide's line in the list. */
  description: string;
  /** Where the list puts it, lowest first. */
  order: number;
  /** How long it reads, at `WORDS_PER_MINUTE`. */
  minutes: number;
}

export interface Guide extends GuideSummary {
  blocks: Block[];
}

/** Silent reading of non-fiction by adults, Brysbaert's meta-analysis (2019): 238 words a minute. */
export const WORDS_PER_MINUTE = 238;

const SLUG = /^[a-z0-9]+(-[a-z0-9]+)*$/;
const FIELDS = ['title', 'description', 'order'];

/** Words a reader reads in a guide's body: its runs of letters or digits, placeholders filled. */
export function wordsOf(blocks: Block[]): number {
  const text = blocks.flatMap((block) => ('items' in block ? block.items : [block.inlines]).map(textOf)).join(' ');
  return text.split(/\s+/).filter((word) => /[\p{L}\p{N}]/u.test(word)).length;
}

/** `{{name}}` -> the fact's value; a name `facts` lacks fails. */
export function fillFacts(text: string, facts: Record<string, string>): string {
  return text.replace(/\{\{\s*([\w]+)\s*\}\}/g, (placeholder, name: string) => {
    const value = facts[name];
    if (value === undefined) throw new Error(`{{${name}}} is not a fact the guides know (facts.ts)`);
    return value;
  });
}

/** A guide file's text -> the guide; a header or a body that breaks the format fails, naming the file and the line. */
export function parseGuide(slug: string, source: string, facts: Record<string, string>): Guide {
  const where = `guides/content/${slug}.md`;
  try {
    if (!SLUG.test(slug)) throw new Error('the file name is the slug: lowercase letters, digits and single hyphens');
    const lines = source.replace(/\r\n/g, '\n').split('\n');
    if (lines[0] !== '---') throw new Error('line 1: the header starts with ---');
    const end = lines.indexOf('---', 1);
    if (end === -1) throw new Error('the header ends with ---');
    const header: Record<string, string> = {};
    lines.slice(1, end).forEach((line, index) => {
      const field = line.match(/^(\w+): (.+)$/);
      if (!field || !FIELDS.includes(field[1] as string)) throw new Error(`line ${index + 2}: the header holds ${FIELDS.join(', ')}, each as "name: value"`);
      header[field[1] as string] = (field[2] as string).trim();
    });
    for (const name of FIELDS) if (!header[name]) throw new Error(`the header has no ${name}`);
    const order = Number(header.order);
    if (!Number.isInteger(order)) throw new Error('order is a whole number');
    const blocks = parseMarkdown(fillFacts(lines.slice(end + 1).join('\n'), facts), end + 2);
    if (!blocks.length) throw new Error('the guide has no body');
    const title = fillFacts(header.title as string, facts);
    const description = fillFacts(header.description as string, facts);
    return { slug, title, description, order, minutes: Math.max(1, Math.round(wordsOf(blocks) / WORDS_PER_MINUTE)), blocks };
  } catch (error) {
    throw new Error(`${where}: ${(error as Error).message}`, { cause: error });
  }
}

/** The guides in the list's order: by `order`, then by title. Two guides can't share a slug (they are file names). */
export function sortGuides<T extends GuideSummary>(guides: T[]): T[] {
  return [...guides].sort((a, b) => a.order - b.order || a.title.localeCompare(b.title));
}

/** What the list shows of a guide: no body, so the list's page carries none. */
export function summaryOf({ slug, title, description, order, minutes }: GuideSummary): GuideSummary {
  return { slug, title, description, order, minutes };
}
