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

/** What the browser does for the controller: the DOM side, in `ui/useAnalyticsConsent.ts`. */
export interface AnalyticsEffects {
  /** Loads Google's tag: its data layer, `config` without a page view of its own, and the script. Called once. */
  load(): void;
  /** Sets or clears GA's `ga-disable-<id>` flag, which stops a loaded tag from sending. */
  setDisabled(disabled: boolean): void;
  /** Deletes GA's cookies. */
  deleteCookies(): void;
  /** Sends a page view for `url`, the page shown now, with the page before it as its referrer after a navigation. */
  pageView(url: string, referrer?: string): void;
}

/** A page as the controller tells them apart: by path (a hash or a query is the same page), with its full URL. */
export interface ShownPage {
  path: string;
  url: string;
}

/**
 * The consent rules: nothing reaches Google before "Accept" or after "Decline", and each page counts once. `stored` is
 * the choice the browser kept, `page` the page the app started on.
 */
export function analyticsController(effects: AnalyticsEffects, stored: Consent | null, page: ShownPage) {
  let consent = stored;
  let loaded = false;
  let shown = page;
  /** The shown page has had its page view: an "Accept" again on it (after a withdrawal) doesn't count it twice. */
  let counted = false;

  const count = (referrer?: string) => {
    counted = true;
    effects.pageView(shown.url, referrer);
  };
  const grant = () => {
    if (!loaded) {
      loaded = true;
      effects.load();
    }
    effects.setDisabled(false);
    if (!counted) count();
  };
  if (consent === 'granted') grant();

  return {
    get consent() {
      return consent;
    },
    choose(value: Consent) {
      if (value === consent) return;
      consent = value;
      if (value === 'granted') {
        grant();
        return;
      }
      if (loaded) effects.setDisabled(true);
      effects.deleteCookies();
    },
    /** A client-side navigation has shown `next`, its title already written. */
    navigated(next: ShownPage) {
      if (next.path === shown.path) return;
      const referrer = shown.url;
      shown = next;
      counted = false;
      if (consent === 'granted') count(referrer);
    },
  };
}
