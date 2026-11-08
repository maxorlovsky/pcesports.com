# CS2 match data + official VOD matching: feasibility experiment

Throwaway code behind `docs/feasibility/cs2-data-and-vods.md`. Plain Node 24 with no dependencies. It doesn't touch the site's `package.json`.

It runs in three steps:

1. **`fetch-matches.js`** reads Liquipedia's MediaWiki API. It finds the S-Tier events that overlap the window (`{{TournamentsList|tier=1}}`, one `action=parse` per year), reads each event page's wikitext, and pulls every `{{Match}}`: teams, start time (converted to UTC), maps, scores, and Liquipedia's own per-map VOD links. One more `action=parse` resolves team names and time zones.
2. **`fetch-vods.js`** reads every whitelisted channel in `channels.json` through its uploads playlist: `channels.list`, `playlistItems.list` and `videos.list`, 1 quota unit each. A channel's `featuredBy` link is re-checked with `channelSections.list`. `search.list` is never called.
3. **`match.js`** links each match to one full-match VOD. It needs:
   - the same organiser
   - published within 72 h after the start
   - a title `A vs B - <event words> - ...` naming both teams in either order (Liquipedia names, short names, and `aliases.json`)
   - a length between 20 min and 6 h, which rules out clips and whole broadcast days

   Each link is then compared with Liquipedia's per-map VOD links, and the script measures what each VOD gives away.

**PandaScore as the match source.** `fetch-pandascore.js` reads the same window from PandaScore's free plan (`/csgo/matches/past`, 100 per page, cached), and keeps the top two PandaScore tiers (`s`, `a`). PandaScore has no organiser field, so `leagues.json` maps its league names to the organisers in `channels.json`.
- `node match.js --source pandascore` links VODs from that list.
- `compare-pandascore.js` pairs it with Liquipedia's list and compares teams, start times, winners, scores, map counts and the linked VODs.

## Liquipedia's terms changed during this experiment

The Liquipedia data was fetched on 2026-11-08 under the API terms read on 2026-11-07, which offered the MediaWiki API "for use in your own projects". Read again on 2026-11-08, the terms say the MediaWiki API is for editing and "Use of the API for non-editing purposes is not supported". The report has the details (sections 1 and 6).

So the Liquipedia client now refuses any network call unless `LIQUIPEDIA_MEDIAWIKI_OK=1` is set, which should only happen once the owner has cleared it.

- Runs that the local `cache/` already covers, such as the report's run below on this machine, still work and make no Liquipedia call.
- A fresh window with the Liquipedia source needs that decision, or a switch to the paid LPDB API. `--source pandascore` fetches a fresh window without any Liquipedia call (the comparison with Liquipedia is then skipped).

## Run it

You need a YouTube Data API v3 key in `YOUTUBE_API_KEY`, and for the PandaScore steps a PandaScore token in `PANDASCORE_TOKEN`. The owner keeps both in the repo-root `.env.local`, which the root `.gitignore` already ignores (`*.local`). Never commit a key.

```bash
cd experiments/cs2-vod-feasibility
node --env-file=../../.env.local run.js --from 2026-10-24 --to 2026-11-08   # the report's run
node --env-file=../../.env.local run.js                                     # the 14 days before today (UTC)
node match.js --no-manual-aliases                                           # re-match without aliases.json (overwrites out/results.*)
node --env-file=../../.env.local run.js --from 2026-10-24 --to 2026-11-08 --source pandascore   # PandaScore list + comparison
```

- `--to` is exclusive. Without arguments the window is the 14 days before today, so matches played today (no VOD yet) stay out.
- Output goes to `out/` (`matches.json`, `videos.json`, `results.json`, `results.md`; with `--source pandascore` also `matches-pandascore.json`, `results-pandascore.*` and `compare-pandascore.json`).
- The comparison needs a Liquipedia run on the same window first; if `out/matches.json` covers another window it is skipped with a message.

## Cache

Every API response is cached in `cache/`, so a rerun gives the same result. The only calls a warm run makes are the 3 `channelSections.list` whitelist checks (3 units), which are never cached.

- **Fresh data:** delete `cache/youtube` before a run that should see new uploads. Don't delete `cache/liquipedia` without the owner's go-ahead (see above).
- PandaScore pages are cached in `cache/pandascore`; delete them for fresh results.
- **Delete `cache/` and `out/` when you're done:** both hold YouTube API data (titles, descriptions, publish times), and YouTube's Developer Policies allow it to be kept for at most 30 days. The same goes for the API data quoted in the committed report (video titles, lengths, chapter names): strip or refresh it there by 2026-12-08 if the report is kept.
- `cache/` and `out/` are gitignored.

## Costs of one run

- **Liquipedia** (only with `LIQUIPEDIA_MEDIAWIKI_OK=1`): about 4 requests for a 2-week window: 2 `action=parse` plus one wikitext query per event.
  - The client spaces requests 2.5 s apart (31 s between parses), so a cold run takes about 35 s.
  - It sends the User-Agent `PCesportsFeasibility/0.1 (https://www.pcesports.com/; https://github.com/maxorlovsky/pcesports.com)`, as Liquipedia's API terms require.
- **PandaScore:** 1 request per 100 matches of any tier (7 for the report's window, out of 1,000 per hour).
- **YouTube:** about 25 units for a cold 15-day backfill of 7 channels, and 3 for a warm rerun. The run prints the exact count.
