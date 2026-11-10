// Google Analytics (GA4), which runs only after the visitor accepts it. The privacy page says what it collects.

/** The site's GA4 measurement ID: public, it is in every request the tag sends. */
export const ANALYTICS_ID = 'G-425HK770VC';

/** Google's tag, which the browser loads only after "Accept". */
export const ANALYTICS_SCRIPT_URL = `https://www.googletagmanager.com/gtag/js?id=${ANALYTICS_ID}`;

/** Where the browser keeps the visitor's choice (localStorage, not a cookie). */
export const CONSENT_KEY = 'pcesports-analytics-consent';

export type Consent = 'granted' | 'denied';

/** The stored choice, or null when there is none (or it is something this code never wrote). */
export function parseConsent(value: string | null | undefined): Consent | null {
  return value === 'granted' || value === 'denied' ? value : null;
}

/** GA4's own cookies: `_ga` and `_ga_<container-id>`. The site sets no other cookie a script can see. */
export function analyticsCookieNames(cookies: string): string[] {
  return cookies.split(';')
    .map((cookie) => cookie.split('=')[0]?.trim() ?? '')
    .filter((name) => name === '_ga' || name.startsWith('_ga_'));
}

/**
 * The domains a cookie for this host can be set on, from the host itself up to its registrable domain: GA writes its
 * cookies on the highest one the browser accepts (`.pcesports.com` for `www.pcesports.com`), so a withdrawal deletes
 * them on each. `localhost` and an IP address take host-only cookies (no domain).
 */
export function cookieDomains(hostname: string): string[] {
  if (!hostname.includes('.') || /^[\d.]+$/.test(hostname) || hostname.includes(':')) return [];
  const labels = hostname.split('.');
  return labels.slice(0, -1).map((_label, index) => labels.slice(index).join('.'));
}
