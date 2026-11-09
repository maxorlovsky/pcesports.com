# watch

"What should I watch?": the after-match rating of each finished tier-1 CS2 match, the list the data job writes, and the
home page that shows it. Depends on `site` (the page's SEO), `matches` (the matches and their results) and `vods`
(each match's official VOD).

- `index.ts` (plain TypeScript, for the data job and the tests):
  - `toResult`, `mergeResults`, `replay`, `winChance`, `RESULTS_DAYS`: team strength, an Elo rating replayed over the
    last year of PandaScore's tier s and a results of every league (K 32, x1.25 for a best-of-five, x0.75 for a
    best-of-one; each team starts at 1500).
  - `rate`, `stagePoints`, `eventPoints`, `strengthPoints`, `closeness`, `labelOf`, `roundOf` and the thresholds: the
    rating.
  - `watchEntries`, `strengthScale`, `recentEntries`, `inList`, `SCALE_DAYS`: the list, in order; `LIST_DAYS` and
    `LABELS` (`page.ts`, which the page imports too).
  - `shownEntries`, `WATCH_DATA`: what the home page reads at build time (`data.ts`).
  - the types `Result`, `BeforeMatch`, `ClosenessInput`, `Rated`, `StrengthScale`, `WatchEntry`,
    `WatchData`, `ShownEntry`, `Label`.
- `views/HomePage.vue` (`app/pages/index.vue`): the hero and the list of the last `LIST_DAYS` (7) days, grouped by
  label, each match as the site's own card: event, stage, date and label, its teams behind a "Show teams" tap
  (`<details>`, no script), and a "Watch the VOD" link to the official video. It says so plainly when the list is
  empty.
- `data/watch.json`: the last `LIST_DAYS`' entries only, so a match ageing out is a change the job publishes and the
  site is rebuilt without it. Not in the code branches. The data job publishes it on the `data` branch, and
  `tools/data-job/pull.mjs` puts it here for a build (git ignores the folder). Shape:
  `{ source, entries: WatchEntry[] }`.
- The results the ratings are replayed from stay in the data job's state folder (`state.json`, `results`), never in
  the data branch.

## The rating

**rating = stakes + closeness**, measured on 621 tier-1 matches (2026-03-01 to 2026-11-08) before it was built.

- **Stakes** (known before the match), added up:
  - the stage (`stagePoints`): a grand final 3; a semi-final or a playoff upper or lower final 2; another playoff match
    1, a third-place one 0.5; a group match that eliminates or advances a team 1; any other group match 0;
  - the event (`eventPoints`): a Major 1, PandaScore tier s 0.5, tier a 0;
  - the teams' strength (`strengthPoints`): both teams' average rating before the match, scaled from -0.5 to 1
    between the median and the 90th percentile of the last `SCALE_DAYS` (250) of results.
- **Closeness** (after the match, `closeness`), the strongest of: an upset (the winner had under 35% chance before
  the match) 1; a map of 60 minutes or more 1 (every such measured map went to overtime); a map of 50 to 60 minutes
  0.5. It reads only the maps every series of its length plays (maps 1-2 of a best-of-three, 1-3 of a best-of-five).
- **Labels:** Must watch at 3.5 or more, Worth a watch at 1.75 or more, For fans below.
- **Order:** by label, then by stakes, then newest first.

Invariants:

- **No result before the click.** A label must not let a visitor read off the winner, the score or the map count, so
  closeness never reads whether a series went the distance or came back from map 1 (in a best-of-three both are the
  map count), only the maps every series plays, and the strongest single signal, so a label never says "it was an
  upset". Matches that share a label are ordered by stakes, known before the match, so a position says nothing more.
  A change to the rating measures the leak again: the share of best-of-three that went to three maps per label, against
  the base rate (40% when measured; per label 52%, 43% and 39%), and the same for the favourite winning.
- **Nothing in the build reveals a result.** `data/watch.json` holds what the page shows and nothing else (no score,
  winner, map count, rating or stakes). The page reads it on the server at build time (`useAsyncData` with
  `import.meta.server`), so its HTML and payload get only the shown entries. `npm run check:build` fails if a listed
  match's result fields or its winner's id or map lengths are in the build's data (JSON, inline scripts, value
  attributes), or its score or map count is written out in the usual ways anywhere a reader or a script gets
  (`tools/check-build.mjs` names what it can't read).
- **Teams on tap.** Later rounds name who won earlier ones, so every entry hides its teams until the visitor taps.
- **Closeness has one entry point.** Everything it reads comes in through `ClosenessInput`: a paid source's per-map
  round scores would be one more field of its `maps`, and no other code changes.
- The results come from PandaScore only, and no YouTube data goes into the rating ("Source: PandaScore", the footer's
  `DATA_CREDITS`).
