// The matches domain's public entry, plain TypeScript: the data job (tools/data-job/) and later the app import it.
export { FETCH_AHEAD_DAYS, FETCH_PAST_DAYS, KEEP_DAYS, TIER_ONE, eventOf, fetchWindow, isTierOne, mergeMatches, organizerOf, startOf, toMatch } from './matches.ts';
export type { FetchWindow, Match, PandaScoreMatch, Team } from './matches.ts';
export { PANDASCORE_API, fetchPandaScoreMatches } from './pandascore.ts';
