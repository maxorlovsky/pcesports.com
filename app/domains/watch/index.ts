// The watch domain's public entry, plain TypeScript: the data job rates matches and writes the list with it. The home
// page reads the list through ./data.ts at build time only (views/HomePage.vue).
export { RESULTS_DAYS, mergeResults, replay, toResult, winChance } from './elo.ts';
export type { BeforeMatch, Result } from './elo.ts';
export { LABELS, LIST_DAYS } from './page.ts';
export type { Label } from './page.ts';
export { CLOSE_MINUTES, MUST_WATCH, OVERTIME_MINUTES, UPSET_CHANCE, WORTH_A_WATCH, closeness, eventPoints, labelOf, rate, roundOf, stagePoints, strengthPoints } from './rating.ts';
export type { ClosenessInput, Rated, StrengthScale } from './rating.ts';
export { SCALE_DAYS, recentEntries, strengthScale, watchEntries } from './list.ts';
export type { ShownEntry, WatchData, WatchEntry } from './list.ts';
export { WATCH_DATA, shownEntries } from './data.ts';
