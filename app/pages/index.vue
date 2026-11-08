<script setup lang="ts">
usePageSeo({
  title: 'esports for people who don\'t have time to follow everything',
  description: 'Which CS2 matches are worth your evening, with links to the official full-match VODs and no spoilers before you click.',
  path: '/',
});

const promises = [
  {
    title: 'Only the matches worth it',
    text: 'A short list of the CS2 matches that were worth watching this week, not every game of every event.',
  },
  {
    title: 'No spoilers before you click',
    text: 'No scores, no winners, no video lengths or thumbnails that give the result away.',
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
      <div class="watch-head">
        <h2 id="watch-title">What should I watch?</h2>
        <span class="badge">Coming soon</span>
      </div>
      <p class="watch-intro">
        The list of this week's CS2 matches is being built. Here is what it will give you:
      </p>
      <ol class="promises">
        <li v-for="(promise, index) in promises" :key="promise.title" class="promise">
          <span class="promise-number" aria-hidden="true">{{ index + 1 }}</span>
          <h3>{{ promise.title }}</h3>
          <p>{{ promise.text }}</p>
        </li>
      </ol>
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
  border-radius: 999px;
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

.watch-head {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: var(--space-3);
  margin-bottom: var(--space-3);
}

.watch-head h2 {
  margin: 0;
}

.badge {
  padding: var(--space-1) var(--space-3);
  border-radius: 999px;
  background: var(--color-secondary);
  color: var(--color-bg);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-bold);
}

.watch-intro {
  color: var(--color-text-muted);
}

.promises {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(16rem, 1fr));
  gap: var(--space-4);
  margin: var(--space-6) 0 0;
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
</style>
