interface PageSeo {
  title: string;
  description: string;
  /** The page's path, e.g. `/privacy`; its canonical URL and og:url. */
  path: string;
  /** Pages that must stay out of search results (the 404 page). */
  noindex?: boolean;
}

/** Title, description, canonical URL and Open Graph tags, all rendered into the static HTML. */
export function usePageSeo({ title, description, path, noindex = false }: PageSeo) {
  const url = absoluteUrl(path);
  const fullTitle = path === '/' ? `${SITE_NAME}: ${title}` : `${title} | ${SITE_NAME}`;

  useHead({
    link: noindex ? [] : [{ rel: 'canonical', href: url }],
  });
  useSeoMeta({
    title: fullTitle,
    description,
    robots: noindex ? 'noindex' : undefined,
    ogType: 'website',
    ogSiteName: SITE_NAME,
    ogTitle: fullTitle,
    ogDescription: description,
    ogUrl: noindex ? undefined : url,
    ogImage: `${SITE_URL}/og.png`,
    ogImageWidth: 1200,
    ogImageHeight: 630,
    ogImageAlt: `${SITE_NAME} logo`,
    twitterCard: 'summary_large_image',
  });
}
