// The site domain's consent rules (app/domains/site/analytics.ts), which the browser code applies
// (app/domains/site/ui/useAnalyticsConsent.ts): what reaches Google, and when.
import assert from 'node:assert/strict';
import { test } from 'node:test';
import { analyticsController, analyticsCookieNames, cookieDomains, parseConsent } from '../app/domains/site/index.ts';

test('only a choice the site wrote counts: anything else asks again', () => {
  assert.equal(parseConsent('granted'), 'granted');
  assert.equal(parseConsent('denied'), 'denied');
  for (const value of [null, undefined, '', 'true', 'GRANTED', ' granted']) assert.equal(parseConsent(value), null);
});

test('a withdrawal deletes GA\'s cookies and no other', () => {
  assert.deepEqual(analyticsCookieNames('_ga=GA1.1.1.2; _ga_425HK770VC=GS2.1.s1; theme=dark; _gab=1; x_ga=2'), ['_ga', '_ga_425HK770VC']);
  assert.deepEqual(analyticsCookieNames(''), []);
});

test('the cookies are deleted on each domain GA may have set them on', () => {
  assert.deepEqual(cookieDomains('www.pcesports.com'), ['www.pcesports.com', 'pcesports.com']);
  assert.deepEqual(cookieDomains('pcesports.com'), ['pcesports.com']);
  for (const host of ['localhost', '127.0.0.1', '[::1]']) assert.deepEqual(cookieDomains(host), [], host);
});

const HOME = { path: '/', url: 'https://www.pcesports.com/' };
const PRIVACY = { path: '/privacy', url: 'https://www.pcesports.com/privacy' };

/** A controller over effects that only record what they were asked to do. */
function started(stored, page = HOME) {
  const calls = [];
  const controller = analyticsController({
    load: () => calls.push('load'),
    setDisabled: (disabled) => calls.push(disabled ? 'disable' : 'enable'),
    deleteCookies: () => calls.push('delete cookies'),
    pageView: (referrer) => calls.push(referrer ? `view from ${referrer}` : 'view'),
  }, stored, page);
  return { controller, calls };
}

test('before a choice, and after "Decline", nothing loads and nothing is sent', () => {
  for (const stored of [null, 'denied']) {
    const { controller, calls } = started(stored);
    controller.navigated(PRIVACY);
    if (stored === null) controller.choose('denied');
    controller.navigated(HOME);
    assert.ok(!calls.some((call) => call === 'load' || call.startsWith('view')), `${stored}: ${calls}`);
  }
});

test('"Accept" loads the tag once and counts the page it was given on, then each other page once', () => {
  const { controller, calls } = started(null);
  controller.choose('granted');
  controller.navigated({ path: '/', url: 'https://www.pcesports.com/#main' }); // a hash: the same page
  controller.navigated(PRIVACY);
  controller.choose('granted'); // the footer's banner, "Accept" again
  controller.navigated(HOME);
  assert.deepEqual(calls, ['load', 'enable', 'view', `view from ${HOME.url}`, `view from ${PRIVACY.url}`]);
});

test('a stored "Accept" counts the page the app starts on', () => {
  assert.deepEqual(started('granted', PRIVACY).calls, ['load', 'enable', 'view']);
});

test('a withdrawal stops the tag and deletes its cookies; "Accept" again counts only a page not yet counted', () => {
  const { controller, calls } = started('granted');
  controller.choose('denied');
  controller.navigated(PRIVACY);
  assert.deepEqual(calls, ['load', 'enable', 'view', 'disable', 'delete cookies']);
  controller.choose('granted'); // on /privacy, which was not counted
  controller.choose('denied');
  controller.choose('granted'); // on /privacy again: counted already
  assert.deepEqual(calls.slice(5), ['enable', 'view', 'disable', 'delete cookies', 'enable']);
});
