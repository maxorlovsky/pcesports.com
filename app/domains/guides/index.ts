// The guides domain's public entry, plain TypeScript: the sitemap, the build check and the tests read the guides with
// it. The pages read them through ./load.ts at build time only (views/).
export { GUIDE_FACTS } from './facts.ts';
export { WORDS_PER_MINUTE, fillFacts, parseGuide, sortGuides, summaryOf, wordsOf } from './guides.ts';
export type { Guide, GuideSummary } from './guides.ts';
export { GUIDES_DIR, findGuide, guidePaths, guideSummaries, loadGuides } from './load.ts';
export { parseInline, parseMarkdown, textOf } from './markdown.ts';
export type { Block, Inline } from './markdown.ts';
