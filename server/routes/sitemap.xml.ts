// Prerendered by `nuxt generate` (nuxt.config.ts) into a static sitemap.xml: nothing runs on the server.
import { sitemapXml } from '~~/app/domains/site';

export default defineEventHandler((event) => {
  setResponseHeader(event, 'content-type', 'application/xml; charset=utf-8');

  return sitemapXml();
});
