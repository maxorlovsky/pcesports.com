# site

What every page shares, and the pages that belong to no other domain. It is the lowest domain: every other one may
depend on it, and it depends on none.

- `index.ts` (plain TypeScript, for the app, the server and Node scripts): `SITE_URL` (the canonical
  `https://www.pcesports.com`), `SITE_NAME`, `CONTACT_EMAIL`, `absoluteUrl`, `SITEMAP_PATHS` (the home page and this
  domain's pages; a new page here adds its path) and `sitemapXml(paths)`, which `server/routes/sitemap.xml.ts` gives
  these and the guides' (`guides` domain, `guidePaths`), and `DATA_CREDITS`, the credits the footer shows (a domain
  whose pages show third-party data adds its credit here: `Source: PandaScore` today).
  It also exports the consent rules (`analytics.ts`): `analyticsController`, which decides when the tag loads, stops
  and counts a page, the stored choice's parser, and which cookies a withdrawal deletes (`tests/site.test.mjs`).
- `ui/index.ts` (the app only): `usePageSeo`, which every page's content calls once for its title, description,
  canonical URL and Open Graph tags (`noindex` for the error page).
- `views/`: what the shell renders, one file each: `SiteLayout` (`app/layouts/default.vue`), `PrivacyPage`
  (`app/pages/privacy.vue`) and `ErrorPage` (`app/error.vue`). The home page is the `watch` domain's.
- Internal: `site.ts`, `analytics.ts`, `ui/SiteHeader.vue`, `ui/SiteFooter.vue`, `ui/usePageSeo.ts`, and the
  analytics consent: `ui/ConsentBanner.vue` (in the layout, after the footer) and `ui/useAnalyticsConsent.ts`.

Analytics: Google Analytics (GA4) counts page views, and only after the visitor's "Accept" in the banner. The choice is
kept in `localStorage`; the banner and the footer's "Cookie settings" render only after mount, so no page's HTML holds
them or the tag. `analyticsController` (plain, tested) decides; `ui/useAnalyticsConsent.ts` does it in the browser:
"Accept" loads `gtag.js` and counts the current page, each client-side navigation to another path counts one more on
`page:finish`, once Nuxt has written the new title, and "Decline" after "Accept" sets GA's `ga-disable-<id>` flag and
deletes its cookies. A choice made in one tab reaches the site's other open tabs through the `storage` event.

The site's code sends every page view itself (`send_page_view: false`) and nothing else, so in the GA property enhanced
measurement and user-provided data collection stay off: history-based page views would add one of their own on each
navigation (Google's single-page-app guide), and scroll, click, download, form and typed data are what the privacy page
says the site does not send. GA's admin is where to check, under Admin, Data collection and modification: in Data
streams, the web stream shows "Enhanced measurement" off with "Measuring: Page views", and Data collection offers "Turn
on" for user-provided data collection. Don't go by the `gtag.js` Google serves for the ID: 40 minutes after both were
switched off it still listed every enhanced-measurement flag as `true` (`"vtp_enableScroll":true` and the rest); whether
it catches up later is unknown. After a deploy, GA's Realtime report shows what arrives. Accept on the home page, follow
the footer's "Privacy" link, scroll that page to the bottom, go back to the home page (the header's link or the
browser's back button), and last click the footer's outbound "Source: PandaScore" link. Read Realtime while it shows one
user in the last 30 minutes (yours, with no earlier run of this check in that time: it counts every visitor): it should
show 3 `page_view` events (besides GA's own `first_visit`, `session_start` and `user_engagement`), and no `scroll` or
`click`. More page views, or either of those events, means enhanced measurement still runs.

The banner stays this simple: ads will need a Google-certified consent tool, which replaces it.

Invariants:

- Every page's HTML holds its content and its tags without JavaScript (`npm run check:build`).
- The privacy page says what the site collects. A change that adds a cookie, analytics, ads or a third-party request
  updates it first.
- Nothing reaches Google before "Accept" or after "Decline": no request, no cookie. `tests/site.test.mjs` holds the
  controller to it, and `npm run check:build` stops a page whose HTML names the tag.
