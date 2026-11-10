// The site domain's public entry, plain TypeScript: the app, the server and Node scripts import it as is.
// Its UI entry (usePageSeo) is ./ui/index.ts, and what the shell renders is in ./views/.
export { ANALYTICS_ID, ANALYTICS_SCRIPT_URL, CONSENT_KEY, analyticsCookieNames, cookieDomains, parseConsent } from './analytics.ts';
export type { Consent } from './analytics.ts';
export { CONTACT_EMAIL, DATA_CREDITS, SITE_NAME, SITE_URL, SITEMAP_PATHS, absoluteUrl, sitemapXml } from './site.ts';
