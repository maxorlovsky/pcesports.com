// Reads the guides from ./content/ for a build, on the server only: the pages import it at build time, so neither the
// files nor this code reach the browser. Adding a guide is adding one file there.
import { readFileSync, readdirSync } from 'node:fs';
import { join } from 'node:path';
import { GUIDE_FACTS } from './facts.ts';
import { parseGuide, sortGuides, summaryOf } from './guides.ts';
import type { Guide, GuideSummary } from './guides.ts';

/** Where the guides sit, from the repository root a build runs in. */
export const GUIDES_DIR = 'app/domains/guides/content';

/** Every guide, in the list's order. One that breaks the format fails the build, naming its file. */
export function loadGuides(root = process.cwd()): Guide[] {
  const dir = join(root, GUIDES_DIR);
  const files = readdirSync(dir).filter((name) => name.endsWith('.md'));
  return sortGuides(files.map((name) => parseGuide(name.slice(0, -3), readFileSync(join(dir, name), 'utf8'), GUIDE_FACTS)));
}

/** What `/guides` lists. */
export function guideSummaries(root = process.cwd()): GuideSummary[] {
  return loadGuides(root).map(summaryOf);
}

/** The guide at `/guides/<slug>`, or null. */
export function findGuide(slug: string, root = process.cwd()): Guide | null {
  return loadGuides(root).find((guide) => guide.slug === slug) ?? null;
}

/** The pages the sitemap lists for the guides: the list, then each guide. */
export function guidePaths(root = process.cwd()): string[] {
  return ['/guides', ...loadGuides(root).map((guide) => `/guides/${guide.slug}`)];
}
