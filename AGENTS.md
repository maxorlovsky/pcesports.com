# pcesports.com: agent instructions

PCesports (<https://www.pcesports.com>): esports for people who don't have time to follow everything. A "What should
I watch?" page for CS2 matches with links to the official full-match VODs, and later guides. A static Nuxt site:
`nuxt generate` renders every page to HTML, nginx on max-cloud serves the files, nothing else runs in production.

Where things are: `README.md` (run, checks, deploy, the server), `docs/architecture.md` (the domains and their rules),
`docs/feasibility/cs2-data-and-vods.md` (the data and VOD report: sources, terms, the owner's decisions),
`docs/old-urls.md` (the old site's redirects). `experiments/` holds the report's data experiments: not part of the
site, left as they are.

## Branches

Work goes into `dev` through a pull request. The owner merges `dev` into `master`, and every push to `master` deploys
(`.github/workflows/ci.yml`). Never push to `dev` or `master`, never deploy, and add no version bump or release
automation unless a card asks for it.

## Domains

The app is domain-modular: `app/domains/<domain>/`, today `site` only; `matches`, `vods`, `watch` and `guides` are
planned (`docs/architecture.md` has their order, where the data job goes, and every rule).

- Work in the domain that owns the change; read its `README.md` and `domain.json` first. A new domain comes with its
  first code, never empty, and adds itself to the table in `docs/architecture.md`.
- Pages, layouts, `error.vue` and server routes stay thin: they import a domain and hold no logic.

`npm test` (`tests/architecture.test.mjs`) enforces the rest:

- From outside a domain, import only its `index.ts` (plain TypeScript), its `ui/index.ts`, or, from a page, layout or
  `error.vue`, one of its `views/*.vue`. A domain imports another only if its `domain.json` allows it, and only a lower
  one, and it imports no project file outside `app/domains/`.
- A domain's plain part (everything but `ui/` and `views/`) never touches Vue or Nuxt, and imports by relative path
  with the `.ts` or `.json` extension: Node runs it as it is, for the data job.
- Project code is never auto-imported (`nuxt.config.ts`): import every component and function you use, written out.
  Vue's and Nuxt's own APIs (`computed`, `useHead`, `NuxtLink`) stay auto-imported.

## Data rules that must never break

From the report and the owner's decisions; a change that bends one needs the owner's ruling on the card first.

- **Credits:** every page that shows PandaScore data shows "Source: PandaScore" (the footer's `DATA_CREDITS` in the
  `site` domain). Any other data source comes with its own credit, as its terms say.
- **No derived YouTube metrics:** compute no metrics or derived data from YouTube API data (views, likes, lengths,
  comments); show it as YouTube returns it, or not at all. Whether matching a VOD to its match by title counts is the
  report's open owner decision 5, to settle before launch.
- **30-day refresh:** YouTube API data the site stores is refreshed or deleted within 30 days, and deleted videos are
  dropped.
- **No spoilers before the click:** before a visitor clicks through to a VOD, the site shows no score, winner, map
  count, video length, YouTube thumbnail or chapters. A VOD is the site's own card with a deep link.
- API keys stay on the machine that runs the data job, never in the built site or the repository.

## Validators

Before a pull request: `npm run check` (lint, typecheck, tests, `nuxt generate`, then `check:build` on the build) and
`npm run nginx:test` (on the factory machine; it needs its nginx). Both must pass and leave `git status` unchanged. CI
runs the same, except nginx, on every pull request into `dev` or `master`.

Build and look: `npm run generate` writes `.output/public`; `npm run preview` serves it as nginx would
(`PORT=<port>`; pick a free one and stop it afterwards). `npm run dev` is the hot-reload server.

## Never

- Move, rename or delete `tools/nginx/nginx.prod.conf`: production's nginx links to it in the live clone (`README.md`,
  "On max-cloud"), and a missing file fails every nginx reload on that server.
- Touch production, max-cloud or the deploy secrets, or commit `.env` files.
