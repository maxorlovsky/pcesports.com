// The vods domain's public entry, plain TypeScript: the data job (tools/data-job/) and the watch domain import it.
export { CHANNELS_LIST, MISSING_AFTER_HOURS, PUBLISH_WINDOW_HOURS, VOD_CHANNELS, candidates, durationSeconds, isVodLength, linkVods, missingVods, norm, spellings, splitTitle, unmatchedUploads } from './match.ts';
export type { Channel, LinkResult, VodLink } from './match.ts';
export { KEEP_UPLOADS_DAYS, MAX_AGE_DAYS, REFRESH_DAYS, applyRefresh, dueForRefresh, pruneUploads } from './refresh.ts';
export type { RefreshResult, RefreshTimes } from './refresh.ts';
export { YOUTUBE_API, youtubeClient } from './youtube.ts';
export type { Upload, YouTubeClient, YouTubeOptions } from './youtube.ts';
