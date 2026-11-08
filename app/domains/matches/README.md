# matches

Tier-1 CS2 matches from PandaScore's free plan: schedule and results. The data job (`tools/data-job/`) fetches and
stores them; the `watch` domain will rank and show them. Depends on nothing yet (`site` is allowed, for the credit).

- `index.ts` (plain TypeScript, for the data job and later the app):
  - `fetchPandaScoreMatches`: `/csgo/matches`, every match scheduled in a window, any tier, 100 per page.
  - `toMatch`: the stored record of a tier-1 match, or null. `isTierOne`, `organizerOf`, `TIER_ONE`.
  - `fetchWindow`, `mergeMatches`, `KEEP_DAYS`: what one run reads and how it merges into the stored list.
  - `startOf`, `eventOf`; the types `Match`, `Team`, `PandaScoreMatch`.
- `leagues.json`: PandaScore league -> organiser. It is half of the tier rule and gives the matcher the organiser.
- `data/matches.json`: not in the code branches. The data job publishes it on the `data` branch, and
  `tools/data-job/pull.mjs` puts it here for a build (git ignores the folder). Shape: `{ source, matches: Match[] }`.

Invariants:

- **The tier rule:** PandaScore tier `s` or `a`, and a league listed in `leagues.json`. PandaScore's tiers are its own
  (PGL Masters Bucharest was `a`), so the league list is what keeps unknown events out. The job's report lists the tier
  `s`/`a` leagues it left out.
- A match stays in the data until `KEEP_DAYS` (30) after its start. Each run reads the matches scheduled from 3 days
  ago to 14 days ahead and replaces that part of the list; a stored match in that window that PandaScore no longer
  returns is dropped.
- Every page that shows this data shows "Source: PandaScore": the page that first does adds it to the `site` domain's
  `DATA_CREDITS` (no page shows it yet).
- The token is sent in a header, server side only, and never appears in an error.
