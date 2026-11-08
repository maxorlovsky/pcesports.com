// Step 2: every upload of the whitelisted organiser channels since the window start, read through
// each channel's uploads playlist. Writes out/videos.json and prints the quota units spent.
import { readFile } from 'node:fs/promises';
import { join } from 'node:path';
import { window as runWindow } from './lib/window.js';
import { channel, featuredChannels, quota, uploadsSince, videos } from './lib/youtube.js';
import { ROOT, writeJson } from './lib/cache.js';

export async function loadWhitelist() {
    const json = JSON.parse(await readFile(join(ROOT, 'channels.json'), 'utf8'));
    return json.channels;
}

export async function fetchVods({ from }) {
    const list = await loadWhitelist();
    const out = { since: from.toISOString(), channels: [], videos: [] };
    for (const c of list) {
        if (c.featuredBy && !(await featuredChannels(c.featuredBy)).includes(c.id)) {
            throw new Error(`Whitelist check failed: ${c.name} (${c.id}) is no longer featured by ${c.featuredBy}`);
        }
        const info = await channel(c.id);
        const uploads = await uploadsSince(info.uploads, from);
        const details = await videos(uploads);
        out.channels.push({ ...c, ...info, uploadsInWindow: uploads.length });
        out.videos.push(...details.map((v) => ({ ...v, organizers: c.organizers })));
    }
    out.quota = { ...quota };
    return out;
}

if (import.meta.url === `file://${process.argv[1]}`) {
    const w = runWindow(process.argv.slice(2));
    const data = await fetchVods(w);
    await writeJson('videos.json', data);
    for (const c of data.channels) console.log(`${c.name} (${c.id}): ${c.uploadsInWindow} uploads since ${data.since.slice(0, 10)}`);
    console.log(`Quota units spent this run: ${quota.units} ${JSON.stringify(quota.calls)} (cache hits: ${quota.cacheHits})`);
}
