import type { NuxtApp } from '#app';
import { ANALYTICS_ID, ANALYTICS_SCRIPT_URL, CONSENT_KEY, analyticsController, analyticsCookieNames, cookieDomains, parseConsent } from '../analytics.ts';
import type { AnalyticsEffects, Consent } from '../analytics.ts';

declare global {
  interface Window {
    dataLayer?: unknown[];
    gtag?: (...args: unknown[]) => void;
  }
}

// One state for the whole app, written only in the browser (from `startAnalytics`, after mount): a prerendered page
// holds no choice, no banner and no tag, whatever the server saw.
const consent = shallowRef<Consent | null>(null);
/** The stored choice has been read: until then nothing renders, so a returning visitor never sees the banner flash. */
const ready = shallowRef(false);
/** Opened again from the footer, to change a choice already made. */
const reopened = shallowRef(false);

let controller: ReturnType<typeof analyticsController> | null = null;

function storedConsent(): Consent | null {
  try {
    return parseConsent(localStorage.getItem(CONSENT_KEY));
  } catch {
    return null; // Storage blocked: the banner asks on every page load
  }
}

function storeConsent(value: Consent) {
  try {
    localStorage.setItem(CONSENT_KEY, value);
  } catch {
    // Storage blocked: the choice holds for this page load only
  }
}

/** The browser side of the consent rules (`analyticsController` decides when each runs). */
const effects: AnalyticsEffects = {
  // Google's snippet. No automatic page view: the controller sends each one, with the page it is for, even before the
  // script arrives.
  load() {
    window.dataLayer = window.dataLayer ?? [];
    // gtag.js reads each call's `arguments` object from the data layer, not an array.
    window.gtag = function gtag() {
      // eslint-disable-next-line prefer-rest-params
      window.dataLayer?.push(arguments);
    };
    window.gtag('js', new Date());
    window.gtag('config', ANALYTICS_ID, { send_page_view: false });
    const script = document.createElement('script');
    script.async = true;
    script.src = ANALYTICS_SCRIPT_URL;
    document.head.appendChild(script);
  },
  setDisabled(disabled) {
    (window as unknown as Record<string, boolean>)[`ga-disable-${ANALYTICS_ID}`] = disabled;
  },
  deleteCookies() {
    const domains = ['', ...cookieDomains(location.hostname)];
    for (const name of analyticsCookieNames(document.cookie)) {
      for (const domain of domains) {
        document.cookie = `${name}=; Max-Age=0; Path=/${domain ? `; Domain=${domain}` : ''}`;
      }
    }
  },
  pageView(url, referrer) {
    window.gtag?.('event', 'page_view', {
      page_location: url,
      page_title: document.title,
      ...(referrer ? { page_referrer: referrer } : {}),
    });
  },
};

const shownPage = () => ({ path: location.pathname, url: location.href });

function choose(value: Consent) {
  reopened.value = false;
  storeConsent(value);
  controller?.choose(value);
  consent.value = value;
}

/**
 * Reads the stored choice, loads the tag if it is "Accept", and reports each page a client-side navigation shows once
 * its title is in. Runs once, whichever layout calls it.
 */
export function startAnalytics(nuxtApp: NuxtApp) {
  if (controller) return;
  controller = analyticsController(effects, storedConsent(), shownPage());
  consent.value = controller.consent;
  ready.value = true;

  // A choice made in another tab of the site applies here at once: a withdrawal there stops this tab's tag too.
  window.addEventListener('storage', (event) => {
    if (event.key !== CONSENT_KEY && event.key !== null) return;
    const value = parseConsent(event.key === null ? null : event.newValue);
    controller?.choose(value);
    consent.value = value;
    reopened.value = false;
  });

  nuxtApp.hook('page:finish', () => {
    // Nuxt's head writes the new title in a `setTimeout(0)` it schedules on this same hook, before this one runs:
    // a timer set now fires after it, so the page view carries the new page's title.
    setTimeout(() => controller?.navigated(shownPage()), 0);
  });
}

/** The visitor's choice and what the banner and the footer show. */
export function useAnalyticsConsent() {
  return {
    consent: readonly(consent),
    ready: readonly(ready),
    reopened: readonly(reopened),
    bannerOpen: computed(() => ready.value && (consent.value === null || reopened.value)),
    accept: () => choose('granted'),
    decline: () => choose('denied'),
    reopen: () => {
      reopened.value = true;
    },
  };
}
