<script setup lang="ts">
import { ANALYTICS_ID } from '../analytics.ts';
import { CONTACT_EMAIL } from '../site.ts';
import { usePageSeo } from '../ui/usePageSeo.ts';

usePageSeo({
  title: 'Privacy',
  description: 'What PCesports collects about you: no accounts, no ads and no access logs; Google Analytics only if you accept it.',
  path: '/privacy',
});

/** GA4's cookie for its session state: `_ga_` and the measurement ID without its `G-`. */
const sessionCookie = `_ga_${ANALYTICS_ID.replace(/^G-/, '')}`;
</script>

<template>
  <div class="container page">
    <article class="prose">
      <h1>Privacy</h1>
      <p class="updated">Last updated: 10 November 2026</p>

      <p>
        PCesports.com is a small hobby site. This page says what happens to your data when you visit it. In short:
        nothing is collected about you, unless you accept Google Analytics, which counts visits.
      </p>

      <h2>What we collect</h2>
      <ul>
        <li>No accounts, no sign-up and no forms.</li>
        <li>No ads.</li>
        <li>
          Visits are counted with Google Analytics only if you choose "Accept" in the banner the site shows on your
          first visit. Until you choose, and after "Decline", nothing is loaded from Google and no cookie is set.
        </li>
        <li>
          Your choice is kept in your browser (its local storage, not a cookie), so the banner does not come back on
          every page. It is not sent anywhere.
        </li>
        <li>
          Our server keeps no access logs, so your visits are not recorded with your address or browser. Like any web
          server, its error log can note an address when a request fails.
        </li>
        <li>The site's fonts and images come from this site itself, not from another company's servers.</li>
      </ul>

      <h2>Google Analytics</h2>
      <p>
        If you accept, each page loads Google's analytics tag from Google's servers, and your browser sends Google:
      </p>
      <ul>
        <li>each page you open on this site, its title and the page you came from;</li>
        <li>how long each page stays open in front of you;</li>
        <li>your browser, operating system, type of device, screen size and language;</li>
        <li>
          your IP address, from which Google works out an approximate location (city, region and country). Google
          Analytics does not log or store IP addresses
          (<a href="https://support.google.com/analytics/answer/2763052" rel="noopener">Google's note</a>);
        </li>
        <li>
          two cookies it sets on this site, <code>_ga</code> and <code>{{ sessionCookie }}</code>, which hold a random
          ID that tells a returning browser and its visits apart. They last up to 2 years.
        </li>
      </ul>
      <p>
        That is all: the site sends Google page views only, and nothing about what you click, type or scroll.
      </p>
      <p>
        We use it to see how many people visit and which pages they read. Google deletes what is tied to that random
        ID at most 14 months after your last visit, under our account's retention setting; the totals in its reports
        (such as visits per page or per country) stay. Google processes the data for us, as our processor,
        under its Google Analytics terms; how Google uses it is in its
        <a href="https://policies.google.com/privacy" rel="noopener">privacy policy</a> and on
        <a href="https://policies.google.com/technologies/partner-sites" rel="noopener">How Google uses information
          from sites that use its services</a>.
      </p>

      <h2>Changing your choice</h2>
      <p>
        "Cookie settings", at the bottom of every page, shows the banner again: choose "Decline" to withdraw your
        consent. Google Analytics then stops at once and its cookies are deleted. Clearing this site's data in your
        browser also forgets your choice, and the banner asks again.
      </p>

      <h2>Cloudflare</h2>
      <p>
        The site is delivered through Cloudflare, which carries every request to our server and protects it from
        attacks. Cloudflare processes technical data such as your IP address to do that, under its own
        <a href="https://www.cloudflare.com/privacypolicy/" rel="noopener">privacy policy</a>.
      </p>

      <h2>Links to other sites</h2>
      <p>
        Links take you to other sites, such as YouTube for match videos. Once you follow one, that site's own privacy
        policy applies.
      </p>

      <h2>Changes</h2>
      <p>
        If PCesports ever adds ads, or anything else that collects data, this page will say so, and what it collects,
        before it goes live.
      </p>

      <h2>Contact</h2>
      <p>
        Questions about your data: <a :href="`mailto:${CONTACT_EMAIL}`">{{ CONTACT_EMAIL }}</a>.
      </p>
    </article>
  </div>
</template>

<style scoped>
.page {
  padding-top: var(--space-7);
}

.updated {
  color: var(--color-text-faint);
  font-size: var(--font-size-sm);
}
</style>
