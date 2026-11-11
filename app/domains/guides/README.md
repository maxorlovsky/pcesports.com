# guides

Short, evergreen guides for people who don't follow every match: `/guides` lists them, and each one is a page at
`/guides/<slug>`. Depends on `site` (each page's SEO), `vods` (the whitelist's VOD channels, for the VOD guide) and
`watch` (the rating's numbers and labels, for the rating guide), so it is the top domain: `site` <- `matches` <- `vods`
<- `watch` <- `guides`.

- `index.ts` (plain TypeScript, for the sitemap, the build check and the tests): `loadGuides`, `guidePaths` and
  `GUIDES_DIR` (`load.ts`, which reads `content/` from the repository root a build runs in); `parseGuide`, `wordsOf`,
  `WORDS_PER_MINUTE` (`guides.ts`); the Markdown subset, `parseMarkdown`, `parseInline`, `textOf` (`markdown.ts`);
  `GUIDE_FACTS`, `RATING_FACTS`, `vodChannelList` (`facts.ts`); the types `Guide`, `GuideSummary`, `Block`, `Inline`.
  The views import `load.ts`'s `findGuide` and `guideSummaries` themselves.
- `views/GuidesIndexPage.vue` (`app/pages/guides/index.vue`) and `views/GuidePage.vue` (`app/pages/guides/[slug].vue`):
  the list and a guide. Both read the guides at build time, on the server only (`useAsyncData` with
  `import.meta.server`, as the home page reads its list), so the files and `load.ts` never reach the browser, and the
  list's payload holds no guide's body.
- `ui/GuideBody.vue` and `ui/GuideText.vue`: a guide's blocks as HTML, through Vue's templates (no `v-html`).
- `content/<slug>.md`: one file per guide. The site's other pages link to `/guides`: the footer (`site`) and the home
  page (`watch`), below the list.

## Adding a guide

Add one file, `content/<slug>.md`. The file name is the slug and the address (`/guides/<slug>`): lowercase letters,
digits and single hyphens. The list, the page, the sitemap (`server/routes/sitemap.xml.ts`), the prerender (Nuxt
crawls the list's links) and the build check (`tools/check-build.mjs`, `guidePages`) all pick it up from the folder.

```md
---
title: The page's h1 and title
description: The meta description and the list's line, 50 to 160 characters.
order: 5
---
**The short answer:** lead with it.

## A heading a reader can skim

A paragraph. Lines join until a blank line.

- a list item
  that goes on, indented two spaces
- **bold** and [a link](/guides/other-guide) or [one outside](https://example.com)

1. an ordered list
```

The format is a Markdown subset: `##` and `###` headings, paragraphs, `- ` and `1. ` lists, `**bold**` and links
(a path on the site, or `https://`). An underscore inside a word is text (`GAM3RS_X`). Anything else (an `#` heading,
emphasis, code, HTML, images, tables, quotes) fails the build with its file and line, so a guide never renders
differently from how it reads. The header holds `title`, `description` and `order` (the list's order, lowest first),
each as `name: value`.

## Facts from the code

A guide may write `{{name}}` for a fact the site's code holds, filled at build time from `GUIDE_FACTS` (`facts.ts`); an
unknown name fails the build. The rating guide takes every number and label from there: the thresholds, the labels,
the stage, event, strength and closeness points (read from the `watch` domain's own functions, so a change to the
rating reaches the guide), the days the list and the team ratings cover. `tests/guides.test.mjs` fails if the rating
guide (its title and description included) types a number or a label by hand, or if any fact disagrees with what the
`watch` domain's code does: the test works each one out again from `watch`'s exports, and a new fact needs its row
there. A third test reads each line of the rating guide that states a number and holds it to the facts its words call
for (a "grand final" line states `{{grandFinal}}` and nothing else), so a placeholder on the wrong line fails too; a
rewrite keeps the words a rule reads, or updates the rule.

The VOD guide (`where-to-watch-official-cs2-vods.md`) writes `{{vodChannels}}` on a line of its own: a list built from
the whitelist (`app/domains/vods/channels.json`) by `vodChannelList`, one item per VOD channel, with its organisers and
its name linked by its channel id, and "its main channel" where the organiser has no other channel there. So a channel
added to or removed from the whitelist changes the guide, and the guide shows nothing the YouTube API returns.
`tests/guides.test.mjs` holds the list to the whitelist's VOD channels, names and links, and shows that a channel added
to the whitelist appears in the guide. The rest of the guide names no channel, and says which organisers post full
matches only through the list; what it says of particular organisers (their titles and clips, the ones with no VODs)
is dated and sourced in `docs/guides-sources.md`.

## The rules every guide keeps

The owner's rules (the site is for busy people), each held by `tests/guides.test.mjs` where a test can hold it:

- **Short:** about 3 to 5 minutes at `WORDS_PER_MINUTE` (238, adults' silent reading of non-fiction in Brysbaert's
  2019 meta-analysis); the page and the list show the minutes. Lead with the answer, then the detail, under headings a
  reader can skim.
- **No spoilers:** no winner, score, map count or result of any match or event, past ones included. Explain how
  things work, never who won. `npm run check:build` reads every page against the listed matches' results, and the
  test runs the same check over every guide, as a page and as its payload, against every score and map count a
  match can have. So a guide never writes a score-like pair of digits or a digit next to "map" ("map 1", "3 maps"),
  which go in words, nor a result's field name before a colon ("score:", "maps:", "winner:"), which the payload would
  carry as a result's key.
- **Facts from primary sources:** every fact that can change (formats, dates, qualification rules, YouTube's
  interface) is checked against its owner's own page and listed, with the URL and the date read, in
  `docs/guides-sources.md`. A claim says no more than its source covers: what was seen at two organisers is said of
  those two, a rule the code applies by round names is described by those names, a weight in Valve's code is said of
  the measures it weighs, and where events differ the guide says so. What the site itself shows or checks is read
  from its code (`watchEntries` lists only matches with a VOD), and an event's dates from its start, not its arena
  days. "As of <month year>" goes in the body, at the section it dates: the page doesn't show the description. Never
  copy Liquipedia's or HLTV's text; a fact that can't be confirmed stays out.
- **Typography:** straight quotes and plain hyphens only, no curly quotes, long dashes or ellipsis characters.
- **One claim per sentence:** preserve its subject and qualifiers when rewriting.

## Shared terms

Use these terms consistently in guide prose. Keep quoted interface labels and PandaScore's round names as they appear
on screen (for example, "Lower Bracket Final" and "Semifinal 1").

- **Match** or **series**: the whole contest between two teams; a **map** is one game within it; a **round** is one
  round within a map. A tournament's round is named in its stage context.
- **Best-of-one**, **best-of-three**, **best-of-five**: spell out series lengths. Do not mix these with BO1, BO3
  or best-of-3.
- **Swiss stage**, **group**, **playoffs**, **single elimination**, **double elimination**: the stage or format.
  Use **upper bracket**, **lower bracket**, **quarter-finals**, **semi-finals** and **grand final** for their parts.
- **VOD**: a full-match recording posted by the organiser; expand it as **video on demand** when defining it.
  Use **full-match VOD** when the distinction from highlights or a full broadcast day matters.
- **Valve Regional Standings (VRS)**: introduce the full name before the abbreviation. Use **global list** and
  **regional list** for its lists, **roster** for the players who hold a ranking or invitation, and **club** for
  their organisation. The site's own **team rating** is separate from the VRS.
- **Stakes**, **closeness**, **rating**, **label**: the rating's two inputs, their total, and the label that total
  determines. Keep the generated labels and numbers as placeholders.
- Use **organiser**, **armour**, **licence** (noun) and **Autoplay** consistently; retain proper names such as ESL Pro League.
