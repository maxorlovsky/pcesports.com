// Prerendered by `nuxt generate` (nuxt.config.ts) into a static sitemap.xml: nothing runs on the server.
export default defineEventHandler((event) => {
  const urls = SITEMAP_PATHS.map((path) => `  <url><loc>${absoluteUrl(path)}</loc></url>`).join('\n');

  setResponseHeader(event, 'content-type', 'application/xml; charset=utf-8');

  return `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${urls}\n</urlset>\n`;
});
