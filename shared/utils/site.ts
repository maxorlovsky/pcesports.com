/** The site's one address: canonical URLs, Open Graph and the sitemap are built on it, and nginx 301s the bare domain here. */
export const SITE_URL = 'https://www.pcesports.com';
export const SITE_NAME = 'PCesports';
export const SITE_TAGLINE = 'Esports for people who don\'t have time to follow everything';
export const CONTACT_EMAIL = 'pentaclickesports@gmail.com';

/** Every page the sitemap lists. The 404 page stays out. */
export const SITEMAP_PATHS = ['/', '/privacy'];

/** Credits the footer shows for the data the pages use, e.g. `{ label: 'Source: PandaScore', url: 'https://pandascore.co' }`. */
export const DATA_CREDITS: { label: string; url: string }[] = [];

/** `/privacy` -> `https://www.pcesports.com/privacy`. */
export function absoluteUrl(path: string): string {
  return path === '/' ? `${SITE_URL}/` : `${SITE_URL}${path}`;
}
