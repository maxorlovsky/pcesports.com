// The guides domain's public entry, plain TypeScript: the sitemap, the build check and the tests read the guides with
// it. The pages read them through ./load.ts at build time only (views/).
export { GUIDE_FACTS, RATING_FACTS, vodChannelList } from './facts.ts';
export { WORDS_PER_MINUTE, parseGuide, wordsOf } from './guides.ts';
export type { Guide, GuideSummary } from './guides.ts';
export { GUIDES_DIR, guidePaths, loadGuides } from './load.ts';
export { parseInline, parseMarkdown, textOf } from './markdown.ts';
export type { Block, Inline } from './markdown.ts';
