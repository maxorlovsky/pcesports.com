// Runs the steps in order: node --env-file=../../.env.local run.js [--from YYYY-MM-DD] [--to YYYY-MM-DD]
// [--source pandascore]. With --source pandascore the match list comes from PandaScore, and if a
// Liquipedia run's out/ files exist the two are compared.
import { window as runWindow } from './lib/window.js';
import { writeJson } from './lib/cache.js';
import { fetchMatches } from './fetch-matches.js';
import { fetchPandaScore } from './fetch-pandascore.js';
import { stats as pandaStats } from './lib/pandascore.js';
import { compare } from './compare-pandascore.js';
import { fetchVods } from './fetch-vods.js';
import { stats } from './lib/liquipedia.js';
import { quota } from './lib/youtube.js';
import { matchAll, writeResults } from './match.js';

const w = runWindow(process.argv.slice(2));
console.log(`Window ${w.from.toISOString().slice(0, 10)} .. ${w.to.toISOString().slice(0, 10)} (exclusive)`);
const source = process.argv.includes('--source') ? process.argv[process.argv.indexOf('--source') + 1] : 'liquipedia';
if (source === 'pandascore') {
    const ps = await fetchPandaScore(w);
    await writeJson('matches-pandascore.json', ps);
    console.log(`PandaScore: ${JSON.stringify(ps.counts)} (${JSON.stringify(pandaStats)})`);
} else {
    const matches = await fetchMatches(w);
    await writeJson('matches.json', matches);
    console.log(`Liquipedia: ${matches.events.length} S-Tier events, ${matches.matches.length} played matches (${JSON.stringify(stats)})`);
}
const vods = await fetchVods(w);
await writeJson('videos.json', vods);
console.log(`YouTube: ${vods.videos.length} uploads from ${vods.channels.length} channels, ${quota.units} quota units (${JSON.stringify(quota.calls)}, cache hits ${quota.cacheHits})`);
const data = await matchAll(source);
await writeResults(data);
console.log(JSON.stringify(data.summary, null, 2));
if (source === 'pandascore') {
    const cmp = await compare().catch((e) => { console.log(`No comparison: ${e.message}`); return null; });
    if (cmp) {
        await writeJson('compare-pandascore.json', cmp);
        console.log(JSON.stringify(cmp.summary, null, 2));
    }
}
