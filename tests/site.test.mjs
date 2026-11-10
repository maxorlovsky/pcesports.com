// The site domain's consent rules, which the browser code applies (app/domains/site/ui/useAnalyticsConsent.ts).
import assert from 'node:assert/strict';
import { test } from 'node:test';
import { analyticsCookieNames, cookieDomains, parseConsent } from '../app/domains/site/index.ts';

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
