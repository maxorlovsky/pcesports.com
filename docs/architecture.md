# Architecture

PCesports is a small domain-modular Nuxt app: each part of the product is a **domain**, a folder under
`app/domains/<domain>/` that owns its rules, its data and its UI. The files Nuxt needs in fixed places (pages,
layouts, `error.vue`, server routes) are a thin **shell** that imports the domains. `tests/architecture.test.mjs`
enforces the rules below on every `npm test`, so in CI too.

## Domains

| Domain | Owns | May depend on | State |
|---|---|---|---|
| `site` | the site's address, name, contact and sitemap, the footer's data credits, the layout, `usePageSeo`, the home, privacy and error pages | - | exists |
| `matches` | CS2 fixtures and results from PandaScore (free plan): fetching, normalising, which matches the site lists | `site` | planned |
| `vods` | the organisers' official YouTube VOD channels: the whitelist, reading their uploads, matching a VOD to its match, the 30-day refresh | `site`, `matches` | planned |
| `watch` | the "What should I watch?" ranking and its page, with each VOD shown as the site's own card | `site`, `matches`, `vods` | planned |
| `guides` | the guides and their pages | `site` | planned |

So the order is `site` <- `matches` <- `vods` <- `watch`, with `guides` beside them on `site`. A domain depends only on
the ones below it, as its `domain.json` allows. When a lower domain needs something from a higher one, the higher one
passes it down, or the shell composes the two; the lower one never imports up. A planned domain gets its folder with
the card that gives it its first code: no empty domains.

`docs/feasibility/cs2-data-and-vods.md` is the source for the planned domains' data rules.

### The data job

The data job (fetch matches and VODs, match them, rank, commit the data, and let the deploy publish it) runs on the
factory machine, outside Nuxt. Its rules live in the domains that own them, in their plain parts: the PandaScore client
and normalising in `matches`, the whitelist, matcher and refresh in `vods`, the ranking in `watch`. The job itself is a
thin script under `tools/` (as `tools/deploy/` is) that calls those domains' `index.ts` and writes their data. Node runs
it directly: Node 24 strips TypeScript types on its own, but only erasable syntax (no `enum`, `namespace` or
constructor parameter properties), and resolves only paths written out in full. That is why a plain part imports by
relative path with the `.ts` or `.json` extension (`../site/index.ts`, `./data/matches.json` with
`with { type: 'json' }`), never reaches Vue or Nuxt, and why `npm test` loads every domain's `index.ts` in Node. The
data files it writes belong to the domain whose rules write them (for example `app/domains/matches/data/`), and the
app reads them through that domain's entry.

## Where code goes

| Path | What | Imports |
|---|---|---|
| `app/domains/<domain>/index.ts` | the domain's **plain entry**, TypeScript with no Vue or Nuxt: the app, the server, tools and tests import it | its own plain files |
| `app/domains/<domain>/*.ts` (and subfolders other than `ui/` and `views/`) | the domain's **plain part**: rules, data, types | by relative `.ts`/`.json` path: its own files, other domains' plain entries; Node built-ins; packages in `PLAIN_PACKAGES` (`tests/architecture/check.mjs`; none yet) |
| `app/domains/<domain>/ui/index.ts` | the domain's **UI entry**: components and composables other domains' UI may use | its `ui/` files |
| `app/domains/<domain>/ui/` | its components and composables | Vue, Nuxt, its plain part, other domains' entries |
| `app/domains/<domain>/views/<Name>.vue` | what the shell renders: a page's content, a layout, the error page; one file each, each a public entry for the shell only | like `ui/` |
| `app/domains/<domain>/domain.json` | `name` (its folder), `owns`, `allowedDomainDependencies` | |
| `app/domains/<domain>/README.md` | what it owns, its entries and its invariants | |
| `app/pages/`, `app/layouts/`, `app/app.vue`, `app/error.vue` | the shell: Nuxt's routes and layouts, each importing one domain view and holding no logic | domain views and entries, `app/assets/` |
| `server/routes/` | prerendered files such as `sitemap.xml`, each a few lines over a domain's plain entry | domain plain entries |
| `app/assets/css/` | the global stylesheet (`nuxt.config.ts`'s `css`): the design tokens and base styles, which every domain's components read as CSS variables. It stays in the shell because no single domain owns it | other stylesheets in `app/assets/`, packages |
| `public/` | files copied as they are | |
| `tools/`, `tests/`, any other script | Node scripts and tests | domain plain entries |

A source file anywhere else under `app/`, `server/`, `shared/`, `layers/` or `modules/` (the folders Nuxt reads by
itself) fails the test. A new kind of place (a Nuxt plugin, middleware, a server API) is added to the test and to this
table by the card that needs it.

Views are one public file each, and not re-exported from `ui/index.ts`, because Nuxt splits the client code per route:
a barrel that re-exported every view put the views and the shared code in one more chunk that every page loads.

## Rules (`tests/architecture/check.mjs`)

- **public-entry:** from outside a domain, import only its `index.ts`, its `ui/index.ts`, or (the shell only) one of
  its `views/*.vue`. Never a file behind them (code, data, a stylesheet, an image), by any path (`~/`, `~~/`, `../`)
  or any form (`import`, `import()`, `require()`, `new URL(…, import.meta.url)`, `<script src>`, `<style src>`,
  `<template src>`, a template's asset URLs as Vue's compiler reads them (`<img src>`, `srcset`, `poster`), `@import`,
  `url()`). Every source, style, HTML and JSON file in the repository is read, but for
  dependencies, build output, the old apps and `experiments/`.
- **reach:** outside the domains, the import column above is the whole list: a domain imports no file outside
  `app/domains/` (so no tool, page or helper can carry it up the stack or bring it Vue), the app imports no Node
  script, and Node scripts import no app code but the domains' plain entries.
- **domain-dependency:** a domain imports another only if its `domain.json` lists it in `allowedDomainDependencies`,
  and those lists never form a cycle.
- **plain:** a domain's plain part imports only what the table allows, by relative path with its extension, and uses
  none of the names Nuxt auto-imports (`useHead`, `computed`, `defineEventHandler`) without declaring them, nor Nuxt's
  `$fetch`, nor `import.meta` but for the fields Node has (`url`, `dirname`, `filename`, `resolve`). The server, tools
  and tests import plain entries only.
- **own-entry:** a domain never imports its own `index.ts` or `ui/index.ts`; it imports the file itself.
- **resolve:** every import names a file, a Node built-in, a package in `package.json`, or (app code only) one of
  Nuxt's `#app`, `#imports`, `#components`. No absolute paths, no `import.meta.glob`, and no computed `import()` or
  `require()` but in the Node scripts `COMPUTED_IMPORTS` names, each with its reason.
- **layout:** source files sit only where the table above says; a domain's plain part is `.ts`.
- **manifest:** every domain has `domain.json` (its folder's name, a non-empty `owns`, known dependencies), `README.md`
  and `index.ts`.
- **auto-import:** Nuxt's generated auto-import lists (`.nuxt/*.d.ts`) name no project file, and its generated
  `tsconfig.app.json` keeps `checkUnknownComponents` on.

Each rule has a fixture in `tests/architecture.test.mjs` that breaks it, so a rule that stops biting fails there.

## Auto-imports

Nuxt auto-imports every component in `components/`, every export of `composables/`, `utils/`, `shared/utils/` and
`server/utils/`, and with layers, everything of every layer. That makes a reach into another domain invisible: no
import to read, review or test. So:

- **Project code is never auto-imported.** `nuxt.config.ts` sets `components: { dirs: [] }` and
  `imports: { scan: false }`, and every file imports what it uses from a domain's entry, written out.
- **Vue's and Nuxt's own APIs still are** (`computed`, `useHead`, `NuxtLink`, `defineEventHandler`): they cross no
  domain boundary.
- **Enforced** by the `auto-import` rule (Nuxt's generated lists hold only packages), the `layout` rule (nothing in the
  folders Nuxt scans), and `npm run typecheck`, which fails on a component used in a template without an import
  (`vueCompilerOptions.checkUnknownComponents`, which the `auto-import` rule keeps on).

Nuxt layers were the other way to split the app. They were turned down because they share one auto-import namespace
and one router, so nothing in a layer can be private to it.
