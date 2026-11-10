<script setup lang="ts">
import { usePageSeo } from '../../site/ui/index.ts';
import type { ShownEntry } from '../list.ts';
import { LABELS, LIST_DAYS } from '../page.ts';
import type { Label } from '../page.ts';

usePageSeo({
  title: 'esports for people who don\'t have time to follow everything',
  description: 'Which CS2 matches are worth your evening, with links to the official full-match VODs and no spoilers before you click.',
  path: '/',
});

// The list is read at build time, on the server: the page's HTML and payload get the entries it shows and nothing
// else, and the data file never reaches the browser.
const { data: entries } = await useAsyncData('watch-list', async (): Promise<ShownEntry[]> => {
  if (!import.meta.server) return [];
  const { shownEntries } = await import('../data.ts');
  return shownEntries(new Date());
}, { default: () => [] });

const NOTES: Record<Label, string> = {
  'Must watch': 'The week\'s best. Start here.',
  'Worth a watch': 'Good matches, if you have the evening.',
  'For fans': 'For those who follow the event closely.',
};

const groups = computed(() => LABELS
  .map((label) => ({ label, note: NOTES[label], entries: (entries.value ?? []).filter((entry) => entry.label === label) }))
  .filter((group) => group.entries.length));

const dateFormat = new Intl.DateTimeFormat('en-GB', { weekday: 'short', day: 'numeric', month: 'short', timeZone: 'UTC' });
const day = (date: string) => dateFormat.format(new Date(`${date}T00:00:00Z`));
const vodUrl = (videoId: string) => `https://www.youtube.com/watch?v=${encodeURIComponent(videoId)}`;
const labelClass = (label: Label) => `label-${LABELS.indexOf(label)}`;

const promises = [
  {
    title: 'Only the matches worth it',
    text: 'Each finished tier-1 CS2 match gets a label that weighs what was at stake with how close it got. Never the score.',
  },
  {
    title: 'No spoilers before you click',
    text: 'No scores, no winners, no map counts, no video lengths or thumbnails. Even the teams wait for your tap, because a final\'s line-up gives the semi-finals away.',
  },
  {
    title: 'The official full VOD',
    text: 'Every match links to the organiser\'s own full-match video, so you watch it the way it was broadcast.',
  },
];
</script>

<template>
  <div>
    <section class="hero">
      <div class="container hero-inner">
        <p class="eyebrow">CS2 first, more games later</p>
        <h1>Esports for people who don't have time to <span class="accent">follow everything</span></h1>
        <p class="lead">
          Missed the week's matches? PCesports tells you which ones are worth watching and takes you straight to the
          official full-match VOD, without spoiling the result.
        </p>
      </div>
    </section>

    <section class="container watch" aria-labelledby="watch-title">
      <h2 id="watch-title">What should I watch?</h2>
      <p class="watch-intro">
        The finished tier-1 CS2 matches of the last {{ LIST_DAYS }} days that have an official full-match VOD, best
        first. Teams stay hidden until you tap: later rounds would give earlier winners away.
      </p>

      <p v-if="!groups.length" class="empty">
        No finished tier-1 CS2 match with an official VOD in the last {{ LIST_DAYS }} days. The list fills up again with
        the next event.
      </p>

      <section v-for="group in groups" :key="group.label" class="group" :aria-labelledby="`group-${labelClass(group.label)}`">
        <div class="group-head">
          <h3 :id="`group-${labelClass(group.label)}`">
            <span class="label" :class="labelClass(group.label)">{{ group.label }}</span>
          </h3>
          <p class="group-note">{{ group.note }}</p>
        </div>
        <ol class="entries">
          <li v-for="entry in group.entries" :key="entry.videoId" class="entry" :class="labelClass(entry.label)">
            <p class="entry-meta">
              <span class="entry-label">{{ entry.label }}</span>
              <time :datetime="entry.date">{{ day(entry.date) }}</time>
            </p>
            <p class="entry-event">{{ entry.event }}</p>
            <p class="entry-stage">{{ entry.stage }}</p>
            <details class="teams">
              <summary>
                <span class="teams-show">Show teams</span>
                <span class="teams-hide">Hide teams</span>
              </summary>
              <p class="teams-names">
                {{ entry.teams[0] }} <span class="versus">vs</span> {{ entry.teams[1] }}
              </p>
            </details>
            <a
              class="vod"
              :href="vodUrl(entry.videoId)"
              target="_blank"
              rel="noopener"
              :aria-label="`Watch the VOD: ${entry.event}, ${entry.stage}, on YouTube`"
            >Watch the VOD</a>
          </li>
        </ol>
      </section>
    </section>

    <section class="container how" aria-labelledby="how-title">
      <h2 id="how-title">How it works</h2>
      <ol class="promises">
        <li v-for="(promise, index) in promises" :key="promise.title" class="promise">
          <span class="promise-number" aria-hidden="true">{{ index + 1 }}</span>
          <h3>{{ promise.title }}</h3>
          <p>{{ promise.text }}</p>
        </li>
      </ol>
      <p class="how-more">
        How the labels are worked out, how the Majors and formats work, and how to stay spoiler-free:
        <NuxtLink to="/guides">read the guides</NuxtLink>.
      </p>
    </section>
  </div>
</template>

<style scoped>
.hero {
  position: relative;
  overflow: hidden;
  border-bottom: 1px solid var(--color-border);
  background:
    radial-gradient(60rem 30rem at 85% -10%, var(--color-main-soft), transparent 70%),
    radial-gradient(40rem 20rem at -10% 110%, var(--color-secondary-soft), transparent 70%),
    var(--color-bg);
}

.hero-inner {
  padding-block: clamp(3rem, 8vw, 6.5rem);
}

.eyebrow {
  display: inline-block;
  margin-bottom: var(--space-5);
  padding: var(--space-1) var(--space-3);
  border: 1px solid var(--color-border-strong);
  border-radius: var(--radius-pill);
  color: var(--color-text-muted);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-medium);
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.hero h1 {
  max-width: 18ch;
}

.accent {
  color: var(--color-secondary);
}

.lead {
  max-width: var(--width-text);
  margin-bottom: 0;
  color: var(--color-text-muted);
  font-size: var(--font-size-lg);
}

.watch {
  padding-top: var(--space-8);
}

.watch h2 {
  margin-bottom: var(--space-3);
}

.watch-intro {
  max-width: var(--width-text);
  color: var(--color-text-muted);
}

.empty {
  max-width: var(--width-text);
  padding: var(--space-5);
  border: 1px dashed var(--color-border-strong);
  border-radius: var(--radius-lg);
  color: var(--color-text-muted);
}

.group {
  margin-top: var(--space-6);
}

.group-head {
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  gap: var(--space-2) var(--space-3);
  margin-bottom: var(--space-4);
}

.group-head h3 {
  margin: 0;
  font-size: var(--font-size-md);
}

.group-note {
  margin: 0;
  color: var(--color-text-muted);
  font-size: var(--font-size-sm);
}

.label {
  display: inline-block;
  padding: var(--space-1) var(--space-3);
  border-radius: var(--radius-pill);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-bold);
}

.label.label-0 {
  background: var(--color-secondary);
  color: var(--color-bg);
}

.label.label-1 {
  background: var(--color-main);
  color: var(--color-bg);
}

.label.label-2 {
  border: 1px solid var(--color-border-strong);
  color: var(--color-text-muted);
}

.entries {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(17rem, 1fr));
  gap: var(--space-4);
  margin: 0;
  padding: 0;
  list-style: none;
}

.entry {
  display: flex;
  flex-direction: column;
  padding: var(--space-4) var(--space-5) var(--space-5);
  border: 1px solid var(--color-border);
  border-top: 3px solid var(--color-border-strong);
  border-radius: var(--radius-lg);
  background: var(--color-surface);
}

.entry.label-0 {
  border-top-color: var(--color-secondary);
}

.entry.label-1 {
  border-top-color: var(--color-main);
}

.entry p {
  margin: 0;
}

.entry-meta {
  display: flex;
  justify-content: space-between;
  gap: var(--space-3);
  color: var(--color-text-faint);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-medium);
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.entry .entry-event {
  margin-top: var(--space-2);
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-bold);
  line-height: var(--line-height-tight);
}

.entry .entry-stage {
  margin-top: var(--space-1);
  color: var(--color-text-muted);
  font-size: var(--font-size-sm);
}

/* The teams: a native tap target, no script, at least 48px high */
.teams {
  margin-top: var(--space-4);
  border: 1px solid var(--color-border-strong);
  border-radius: var(--radius-md);
  background: var(--color-surface-raised);
}

.teams summary {
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 3rem;
  padding: var(--space-2) var(--space-4);
  border-radius: var(--radius-md);
  color: var(--color-text);
  font-weight: var(--font-weight-medium);
  cursor: pointer;
  list-style: none;
  touch-action: manipulation;
  user-select: none;
  -webkit-tap-highlight-color: transparent;
}

.teams summary::-webkit-details-marker {
  display: none;
}

.teams summary::after {
  content: '';
  width: 0.55rem;
  height: 0.55rem;
  margin-right: var(--space-1);
  border-right: 2px solid currentcolor;
  border-bottom: 2px solid currentcolor;
  transform: rotate(45deg) translateY(-25%);
}

.teams[open] summary::after {
  transform: rotate(-135deg) translateY(-25%);
}

.teams summary:hover,
.teams summary:active {
  background: var(--color-main-soft);
}

.teams-hide,
.teams[open] .teams-show {
  display: none;
}

.teams[open] .teams-hide {
  display: inline;
}

.teams-names {
  padding: 0 var(--space-4) var(--space-3);
  font-size: var(--font-size-md);
  font-weight: var(--font-weight-bold);
}

.versus {
  color: var(--color-text-faint);
  font-weight: var(--font-weight-regular);
}

.vod {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 3rem;
  margin-top: var(--space-3);
  padding: var(--space-2) var(--space-4);
  border-radius: var(--radius-md);
  background: var(--color-main);
  color: var(--color-bg);
  font-weight: var(--font-weight-bold);
  text-decoration: none;
  touch-action: manipulation;
}

.vod::before {
  content: '';
  margin-right: var(--space-2);
  border-top: 0.4rem solid transparent;
  border-bottom: 0.4rem solid transparent;
  border-left: 0.65rem solid currentcolor;
}

.vod:hover {
  background: var(--color-main-strong);
  color: var(--color-bg);
}

.how {
  padding-top: var(--space-8);
}

.promises {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(16rem, 1fr));
  gap: var(--space-4);
  margin: var(--space-5) 0 0;
  padding: 0;
  list-style: none;
}

.promise {
  padding: var(--space-5);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  background: var(--color-surface);
}

.promise-number {
  display: inline-grid;
  place-items: center;
  width: 2rem;
  height: 2rem;
  margin-bottom: var(--space-4);
  border-radius: var(--radius-md);
  background: var(--color-main-soft);
  color: var(--color-main-strong);
  font-weight: var(--font-weight-bold);
}

.promise h3 {
  margin-bottom: var(--space-2);
}

.promise p {
  margin: 0;
  color: var(--color-text-muted);
  font-size: var(--font-size-sm);
}

.how-more {
  max-width: var(--width-text);
  margin: var(--space-5) 0 0;
  color: var(--color-text-muted);
}
</style>
