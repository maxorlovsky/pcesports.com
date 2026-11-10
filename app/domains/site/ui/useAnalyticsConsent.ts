import type { NuxtApp } from '#app';
import { ANALYTICS_ID, ANALYTICS_SCRIPT_URL, CONSENT_KEY, analyticsCookieNames, cookieDomains, parseConsent } from '../analytics.ts';
import type { Consent } from '../analytics.ts';

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

let started = false;
let tagLoaded = false;
/** The page the last page view counted (or would have, without consent): a route change to another path counts once. */
let shownPath = '';
let shownUrl = '';

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

/** GA's opt-out flag: while it is set, the loaded tag sends nothing. */
function setTagDisabled(disabled: boolean) {
  (window as unknown as Record<string, boolean>)[`ga-disable-${ANALYTICS_ID}`] = disabled;
}

/** Google's snippet, run only after "Accept"; it counts the current page itself, explicitly, as every later one. */
function loadTag() {
  if (tagLoaded) {
    setTagDisabled(false);
    return;
  }
  tagLoaded = true;
  setTagDisabled(false);
  window.dataLayer = window.dataLayer ?? [];
  // gtag.js reads each call's `arguments` object from the data layer, not an array.
  window.gtag = function gtag() {
    // eslint-disable-next-line prefer-rest-params
    window.dataLayer?.push(arguments);
  };
  window.gtag('js', new Date());
  // No automatic page view: `sendPageView` sends each one with the page it was for, even before the script arrives.
  window.gtag('config', ANALYTICS_ID, { send_page_view: false });
  sendPageView();
  const script = document.createElement('script');
  script.async = true;
  script.src = ANALYTICS_SCRIPT_URL;
  document.head.appendChild(script);
}

function sendPageView(referrer?: string) {
  window.gtag?.('event', 'page_view', {
    page_location: location.href,
    page_title: document.title,
    ...(referrer ? { page_referrer: referrer } : {}),
  });
}

/** After "Decline" on a page where the tag already runs: it stops sending, and its cookies go. */
function stopTag() {
  if (tagLoaded) setTagDisabled(true);
  const domains = ['', ...cookieDomains(location.hostname)];
  for (const name of analyticsCookieNames(document.cookie)) {
    for (const domain of domains) {
      document.cookie = `${name}=; Max-Age=0; Path=/${domain ? `; Domain=${domain}` : ''}`;
    }
  }
}

function choose(value: Consent) {
  consent.value = value;
  reopened.value = false;
  storeConsent(value);
  if (value === 'granted') loadTag();
  else stopTag();
}

/**
 * Reads the stored choice, loads the tag if it is "Accept", and counts each later page a client-side navigation shows,
 * once its title is in (`page:finish`, when Nuxt writes the new page's head). Runs once, whichever layout calls it.
 */
export function startAnalytics(nuxtApp: NuxtApp) {
  if (started) return;
  started = true;
  shownPath = location.pathname;
  shownUrl = location.href;
  consent.value = storedConsent();
  ready.value = true;
  if (consent.value === 'granted') loadTag();

  nuxtApp.hook('page:finish', () => {
    if (location.pathname === shownPath) return;
    const referrer = shownUrl;
    shownPath = location.pathname;
    shownUrl = location.href;
    if (consent.value === 'granted' && tagLoaded) sendPageView(referrer);
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
