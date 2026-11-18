import { lstatSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

// A scratch copy whose node_modules is a symlink to another checkout's (how reviewers check a branch). Once the copy has
// a .nuxt (`nuxt prepare` or `nuxt typecheck` made one), Nuxt builds into node_modules/.cache/nuxt/.nuxt: the other
// checkout's folder, where the build also fails (`#internal/nuxt/paths`). Such a copy builds in its own .nuxt instead,
// and skips the Nuxt CLI's locks: the output lock's file goes into the linked node_modules/.cache/nuxt. Any other
// checkout keeps Nuxt's defaults.
const nodeModules = join(dirname(fileURLToPath(import.meta.url)), 'node_modules');
const nodeModulesLinked = lstatSync(nodeModules, { throwIfNoEntry: false })?.isSymbolicLink() ?? false;
if (nodeModulesLinked) process.env.NUXT_IGNORE_LOCK ??= '1';

// A static site: `nuxt generate` prerenders every page into .output/public, and nginx serves those files
// (tools/nginx/nginx.prod.conf). No server runtime runs in production.
export default defineNuxtConfig({
  compatibilityDate: '2026-11-01',
  devtools: { enabled: false },
  buildDir: nodeModulesLinked ? '.nuxt' : undefined,
  // Project code is never auto-imported, so every import between files is written out and the domain boundaries stay
  // visible (docs/architecture.md, "Auto-imports"). Vue's and Nuxt's own APIs (computed, useHead, NuxtLink) still are.
  components: { dirs: [] },
  imports: { scan: false },
  css: ['@fontsource-variable/inter/wght.css', '~/assets/css/tokens.css', '~/assets/css/base.css'],
  app: {
    head: {
      htmlAttrs: { lang: 'en' },
      meta: [
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'theme-color', content: '#000000' },
        { name: 'color-scheme', content: 'dark' },
      ],
      link: [{ rel: 'icon', href: '/favicon.ico', sizes: '16x16' }],
    },
  },
  // Each page's component styles go into its HTML, so it renders without waiting for a stylesheet request
  features: { inlineStyles: true },
  nitro: {
    prerender: {
      routes: ['/', '/privacy', '/guides', '/sitemap.xml'],
      crawlLinks: true,
      failOnError: true,
      // `privacy.html`, not `privacy/index.html`: nginx serves `/privacy` without a trailing-slash redirect.
      autoSubfolderIndex: false,
    },
  },
  // 404.html is the error page rendered at build time, readable without JavaScript, not an empty app shell.
  experimental: { prerenderErrorPages: true },
  // A component used in a template without an import fails `npm run typecheck`, since none is auto-registered.
  typescript: { strict: true, tsConfig: { vueCompilerOptions: { checkUnknownComponents: true } } },
});
