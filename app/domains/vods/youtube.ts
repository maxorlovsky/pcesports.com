// The YouTube Data API v3, through the four list endpoints that cost 1 quota unit per call: channels, channelSections,
// playlistItems and videos (https://developers.google.com/youtube/v3/determine_quota_cost). search.list is never
// called: it has its own bucket of 100 calls a day, far too few to poll with.
export const YOUTUBE_API = 'https://www.googleapis.com/youtube/v3';
const PAGE = 50;
/** A runaway guard for one uploads playlist read: 1,000 uploads. */
const MAX_PAGES = 20;

/** One upload as the job keeps it locally (never committed): what the matcher reads, as YouTube returns it. */
export interface Upload {
  id: string;
  channelId: string;
  title: string;
  publishedAt: string;
  /** contentDetails.duration as returned, an ISO 8601 duration ("PT1H32M10S"). */
  duration: string;
  /** snippet.liveBroadcastContent: "live" or "upcoming" while a stream is not over, else "none". */
  liveBroadcastContent: string;
}

export interface YouTubeOptions {
  key: string;
  fetch?: typeof fetch;
  api?: string;
}

export interface YouTubeClient {
  /** Units spent so far, and calls per endpoint. */
  readonly quota: { units: number; calls: Record<string, number> };
  /** The channel ids a channel lists in its own featured-channels sections. */
  featuredChannels(channelId: string): Promise<string[]>;
  /** The uploads playlist of each channel that exists, by channel id. */
  uploadPlaylists(channelIds: string[]): Promise<Map<string, string>>;
  /** Ids of a playlist's items that went public at or after `since`, newest first. */
  uploadsSince(playlistId: string, since: Date): Promise<string[]>;
  /** The uploads YouTube still returns for these ids; a deleted or private video is missing. */
  videos(ids: string[]): Promise<Upload[]>;
}

export function youtubeClient({ key, fetch = globalThis.fetch, api = YOUTUBE_API }: YouTubeOptions): YouTubeClient {
  const quota = { units: 0, calls: {} as Record<string, number> };

  async function get(endpoint: string, params: Record<string, string>): Promise<Record<string, unknown>> {
    quota.units += 1;
    quota.calls[endpoint] = (quota.calls[endpoint] ?? 0) + 1;
    const query = new URLSearchParams({ ...params, key });
    let response: Response;
    try {
      response = await fetch(`${api}/${endpoint}?${query}`, { headers: { Accept: 'application/json' } });
    } catch (error) {
      // The request URL holds the key, so the cause, which may repeat it, stays off: the message is all that's kept.
      throw new Error(`YouTube ${endpoint}: ${String((error as Error).message).replaceAll(key, '<key>')}`); // eslint-disable-line preserve-caught-error
    }
    const body = (await response.json().catch(() => null)) as { error?: { message?: string } } | null;
    if (!response.ok || !body) throw new Error(`YouTube ${endpoint} HTTP ${response.status}: ${String(body?.error?.message ?? '').replaceAll(key, '<key>')}`);

    return body as Record<string, unknown>;
  }

  return {
    quota,

    async featuredChannels(channelId) {
      const body = await get('channelSections', { part: 'contentDetails', channelId });
      const sections = (body.items ?? []) as { contentDetails?: { channels?: string[] } }[];
      return sections.flatMap((section) => section.contentDetails?.channels ?? []);
    },

    async uploadPlaylists(channelIds) {
      const out = new Map<string, string>();
      for (let i = 0; i < channelIds.length; i += PAGE) {
        const body = await get('channels', { part: 'contentDetails', id: channelIds.slice(i, i + PAGE).join(','), maxResults: String(PAGE) });
        for (const item of (body.items ?? []) as { id: string; contentDetails: { relatedPlaylists: { uploads: string } } }[]) {
          out.set(item.id, item.contentDetails.relatedPlaylists.uploads);
        }
      }
      return out;
    },

    // The uploads playlist is ordered by each item's snippet.publishedAt, when it went public, so the first older item
    // ends the read. For a stream that time moves (the live start while it runs, a later time once the recording is
    // up), which is why the job re-reads every upload it first saw live.
    async uploadsSince(playlistId, since) {
      const ids: string[] = [];
      let pageToken = '';
      for (let page = 0; page < MAX_PAGES; page++) {
        const params: Record<string, string> = { part: 'snippet,contentDetails', playlistId, maxResults: String(PAGE) };
        if (pageToken) params.pageToken = pageToken;
        const body = await get('playlistItems', params);
        for (const item of (body.items ?? []) as { snippet: { publishedAt: string }; contentDetails: { videoId: string } }[]) {
          if (Date.parse(item.snippet.publishedAt) < since.getTime()) return ids;
          ids.push(item.contentDetails.videoId);
        }
        pageToken = typeof body.nextPageToken === 'string' ? body.nextPageToken : '';
        if (!pageToken) return ids;
      }
      return ids;
    },

    async videos(ids) {
      const out: Upload[] = [];
      for (let i = 0; i < ids.length; i += PAGE) {
        const body = await get('videos', { part: 'snippet,contentDetails', id: ids.slice(i, i + PAGE).join(','), maxResults: String(PAGE) });
        type Item = { id: string; snippet: { channelId: string; title: string; publishedAt: string; liveBroadcastContent?: string }; contentDetails: { duration?: string } };
        for (const item of (body.items ?? []) as Item[]) {
          out.push({
            id: item.id,
            channelId: item.snippet.channelId,
            title: item.snippet.title,
            publishedAt: item.snippet.publishedAt,
            duration: item.contentDetails.duration ?? '',
            liveBroadcastContent: item.snippet.liveBroadcastContent ?? 'none',
          });
        }
      }
      return out;
    },
  };
}
