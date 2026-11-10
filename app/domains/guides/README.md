# guides

Short, evergreen guides for people who don't follow every match: `/guides` lists them, and each one is a page at
`/guides/<slug>`. Depends on `site` (each page's SEO) and `watch` (the rating's numbers and labels, for the rating
guide), so it is the top domain: `site` <- `matches` <- `vods` <- `watch` <- `guides`.

- `index.ts` (plain TypeScript, for the sitemap, the build check and the tests): `loadGuides`, `findGuide`,
  `guideSummaries` and `guidePaths` (`load.ts`, which reads `content/` from the repository root a build runs in);
  `parseGuide`, `fillFacts`, `wordsOf`, `sortGuides`, `summaryOf`, `WORDS_PER_MINUTE` (`guides.ts`); the Markdown
  subset, `parseMarkdown`, `parseInline`, `textOf` (`markdown.ts`); `GUIDE_FACTS` (`facts.ts`); the types `Guide`,
  `GuideSummary`, `Block`, `Inline`.
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
(a path on the site, or `https://`). Anything else (an `#` heading, emphasis, code, HTML, images, tables, quotes) fails
the build with its file and line, so a guide never renders differently from how it reads. The header holds `title`,
`description` and `order` (the list's order, lowest first), each as `name: value`.

## Facts from the code

A guide may write `{{name}}` for a fact the site's code holds, filled at build time from `GUIDE_FACTS` (`facts.ts`); an
unknown name fails the build. The rating guide takes every number and label from there: the thresholds, the labels,
the stage, event, strength and closeness points (read from the `watch` domain's own functions, so a change to the
rating reaches the guide), the days the list and the team ratings cover. `tests/guides.test.mjs` fails if the rating
guide types a number or a label by hand, or if a fact disagrees with what the `watch` domain's code does.

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
- **Facts from primary sources:** every fact that can change (formats, dates, qualification rules) is checked against
  its owner's own page and listed, with the URL and the date read, in `docs/guides-sources.md`; where it can change,
  the guide says "as of <month year>". Never copy Liquipedia's or HLTV's text; a fact that can't be confirmed stays
  out.
- **Typography:** straight quotes and plain hyphens only, no curly quotes, long dashes or ellipsis characters.
- **One claim per sentence** where possible, so a rewrite can't silently change a fact.
