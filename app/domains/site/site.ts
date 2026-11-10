/** The site's one address: canonical URLs, Open Graph and the sitemap are built on it, and nginx 301s the bare domain here. */
export const SITE_URL = 'https://www.pcesports.com';
export const SITE_NAME = 'PCesports';
export const CONTACT_EMAIL = 'pentaclickesports@gmail.com';

/**
 * The pages the sitemap lists but the guides (the `guides` domain adds those in server/routes/sitemap.xml.ts): the home
 * page and this domain's. The 404 page stays out.
 */
export const SITEMAP_PATHS = ['/', '/privacy'];

/** Credits the footer shows, on every page, for the data the pages use. PandaScore's terms ask for its credit. */
export const DATA_CREDITS: { label: string; url: string }[] = [{ label: 'Source: PandaScore', url: 'https://pandascore.co' }];

/** `/privacy` -> `https://www.pcesports.com/privacy`, `/` -> `https://www.pcesports.com/`. */
export function absoluteUrl(path: string): string {
  return `${SITE_URL}${path}`;
}

/** The sitemap's XML of `paths`, which `server/routes/sitemap.xml.ts` serves and `nuxt generate` prerenders. */
export function sitemapXml(paths: string[]): string {
  const urls = paths.map((path) => `  <url><loc>${absoluteUrl(path)}</loc></url>`).join('\n');

  return `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${urls}\n</urlset>\n`;
}
