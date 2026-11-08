import { cached } from './cache.js';

// YouTube Data API v3. Only channels.list, channelSections.list, playlistItems.list and videos.list
// are used, 1 quota unit per call each (https://developers.google.com/youtube/v3/determine_quota_cost).
// search.list is deliberately absent: it has its own bucket of only 100 calls per day.
const API = 'https://www.googleapis.com/youtube/v3';
export const quota = { units: 0, calls: {}, cacheHits: 0 };

function key() {
    const k = process.env.YOUTUBE_API_KEY;
    if (!k) throw new Error('YOUTUBE_API_KEY is not set (see README: node --env-file=../../.env.local ...)');
    return k;
}

async function get(endpoint, params, { cache = true } = {}) {
    const query = new URLSearchParams(params).toString();
    const produce = async () => {
        quota.units += 1;
        quota.calls[endpoint] = (quota.calls[endpoint] || 0) + 1;
        const res = await fetch(`${API}/${endpoint}?${query}&key=${encodeURIComponent(key())}`);
        const json = await res.json();
        if (!res.ok) throw new Error(`YouTube ${endpoint} ${res.status}: ${json.error?.message}`);
        return json;
    };
    if (!cache) return produce();
    let miss = false;
    const value = await cached('youtube', `${endpoint}?${query}`, () => { miss = true; return produce(); });
    if (!miss) quota.cacheHits += 1;
    return value;
}

export async function channel(id) {
    const json = await get('channels', { part: 'snippet,contentDetails,statistics', id });
    const item = json.items?.[0];
    if (!item) throw new Error(`YouTube channel not found: ${id}`);
    return {
        id: item.id,
        title: item.snippet.title,
        handle: item.snippet.customUrl,
        subscribers: Number(item.statistics.subscriberCount),
        uploads: item.contentDetails.relatedPlaylists.uploads,
    };
}

/**
 * Uploads newer than `since`, newest first. The uploads playlist is ordered by the item's
 * snippet.publishedAt (when it went public; for a stream, its start time), so the first older item
 * ends the scan. contentDetails.videoPublishedAt is not used: for a scheduled stream it is the day
 * the stream was created, days earlier (seen on @eslcs), and would put it out of order. Pages are
 * cached per page token, which makes a rerun reproducible; delete cache/youtube for fresh uploads.
 */
export async function uploadsSince(playlistId, since) {
    const out = [];
    let pageToken;
    for (;;) {
        const params = { part: 'snippet,contentDetails', playlistId, maxResults: '50' };
        if (pageToken) params.pageToken = pageToken;
        const json = await get('playlistItems', params);
        for (const item of json.items) {
            const { publishedAt } = item.snippet;
            if (new Date(publishedAt) < since) return out;
            out.push({ id: item.contentDetails.videoId, publishedAt });
        }
        pageToken = json.nextPageToken;
        if (!pageToken) return out;
    }
}

const isoDuration = (d) => {
    const m = /^P(?:(\d+)D)?T?(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?$/.exec(d || '');
    if (!m) return null;
    const [, days, h, min, s] = m.map((x) => Number(x || 0));
    return days * 86400 + h * 3600 + min * 60 + s;
};

/** Channel ids a channel lists in its own "featured channels" sections (1 unit, not cached on
 * disk, so every run re-checks the whitelist; asked once per channel per run). */
const featured = new Map();
export function featuredChannels(id) {
    if (!featured.has(id)) {
        featured.set(id, get('channelSections', { part: 'contentDetails', channelId: id }, { cache: false })
            .then((json) => json.items.flatMap((s) => s.contentDetails?.channels || [])));
    }
    return featured.get(id);
}

/** Video details in batches of 50 ids (1 unit per batch). */
export async function videos(ids) {
    const out = [];
    for (let i = 0; i < ids.length; i += 50) {
        const json = await get('videos', { part: 'snippet,contentDetails,liveStreamingDetails', id: ids.slice(i, i + 50).join(',') });
        for (const v of json.items) {
            out.push({
                id: v.id,
                channelId: v.snippet.channelId,
                channelTitle: v.snippet.channelTitle,
                title: v.snippet.title,
                description: v.snippet.description,
                publishedAt: v.snippet.publishedAt,
                durationSec: isoDuration(v.contentDetails.duration),
                wasLive: Boolean(v.liveStreamingDetails),
                thumbnail: v.snippet.thumbnails?.high?.url || v.snippet.thumbnails?.default?.url,
            });
        }
    }
    return out;
}
