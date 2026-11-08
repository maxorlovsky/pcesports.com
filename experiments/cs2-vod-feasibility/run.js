// Runs the three steps in order: node --env-file=../../.env.local run.js [--from YYYY-MM-DD] [--to YYYY-MM-DD]
import { window as runWindow } from './lib/window.js';
import { writeJson } from './lib/cache.js';
import { fetchMatches } from './fetch-matches.js';
import { fetchVods } from './fetch-vods.js';
import { stats } from './lib/liquipedia.js';
import { quota } from './lib/youtube.js';
import { matchAll, writeResults } from './match.js';

const w = runWindow(process.argv.slice(2));
console.log(`Window ${w.from.toISOString().slice(0, 10)} .. ${w.to.toISOString().slice(0, 10)} (exclusive)`);
const matches = await fetchMatches(w);
await writeJson('matches.json', matches);
console.log(`Liquipedia: ${matches.events.length} S-Tier events, ${matches.matches.length} played matches (${JSON.stringify(stats)})`);
const vods = await fetchVods(w);
await writeJson('videos.json', vods);
console.log(`YouTube: ${vods.videos.length} uploads from ${vods.channels.length} channels, ${quota.units} quota units (${JSON.stringify(quota.calls)}, cache hits ${quota.cacheHits})`);
const data = await matchAll();
await writeResults(data);
console.log(JSON.stringify(data.summary, null, 2));
