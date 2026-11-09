# site

What every page shares, and the pages that belong to no other domain. It is the lowest domain: every other one may
depend on it, and it depends on none.

- `index.ts` (plain TypeScript, for the app, the server and Node scripts): `SITE_URL` (the canonical
  `https://www.pcesports.com`), `SITE_NAME`, `CONTACT_EMAIL`, `absoluteUrl`, `SITEMAP_PATHS` and `sitemapXml` (the
  pages the sitemap lists; a new page adds its path), and `DATA_CREDITS`, the credits the footer shows (a domain
  whose pages show third-party data adds its credit here: `Source: PandaScore` today).
- `ui/index.ts` (the app only): `usePageSeo`, which every page's content calls once for its title, description,
  canonical URL and Open Graph tags (`noindex` for the error page).
- `views/`: what the shell renders, one file each: `SiteLayout` (`app/layouts/default.vue`), `PrivacyPage`
  (`app/pages/privacy.vue`) and `ErrorPage` (`app/error.vue`). The home page is the `watch` domain's.
- Internal: `site.ts`, `ui/SiteHeader.vue`, `ui/SiteFooter.vue`, `ui/usePageSeo.ts`.

Invariants:

- Every page's HTML holds its content and its tags without JavaScript (`npm run check:build`).
- The privacy page says what the site collects. A change that adds a cookie, analytics, ads or a third-party request
  updates it first.
