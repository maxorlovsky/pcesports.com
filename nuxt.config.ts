// A static site: `nuxt generate` prerenders every page into .output/public, and nginx serves those files
// (tools/nginx/nginx.prod.conf). No server runtime runs in production.
export default defineNuxtConfig({
  compatibilityDate: '2026-11-01',
  devtools: { enabled: false },
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
  // The pages' content is plain HTML and CSS; the Vue runtime only takes over links once loaded.
  features: { inlineStyles: true },
  nitro: {
    prerender: {
      routes: ['/', '/privacy', '/sitemap.xml'],
      crawlLinks: true,
      failOnError: true,
      // `privacy.html`, not `privacy/index.html`: nginx serves `/privacy` without a trailing-slash redirect.
      autoSubfolderIndex: false,
    },
  },
  // 404.html is the error page rendered at build time, readable without JavaScript, not an empty app shell.
  experimental: { prerenderErrorPages: true },
  typescript: { strict: true },
});
