# Old URLs

The URLs search engines may still know from older versions of pcesports.com, and what the new site answers for each.
nginx does it, in `tools/nginx/old-urls.conf`, which `tools/nginx/nginx.prod.conf` includes.

## Sources

- **The Wayback Machine's CDX API**, read on 2026-11-08: every URL it captured on pcesports.com and www.pcesports.com
  (`http://web.archive.org/cdx/search/cdx?url=pcesports.com/*&output=json&fl=original,statuscode,timestamp,mimetype&collapse=urlkey`).
  That gave 2,107 distinct paths, with the query strings dropped. They are all in [`old-urls.csv`](old-urls.csv), with
  the date each was first captured.
- **Google Search Console** was not used: no export was added to the card. Its list of URLs that still get clicks or
  links would show which old pages still bring visitors. Add those here, and to `old-urls.conf` if they need a line.

The site has had four lives: the 2014-2016 tournament platform (`/en/...`, `/ru/...`, `/web/static/...`), the
2017-2019 site (`/dist/...`, `/article/...`, `/blog/...`), the 2019-2024 event catalogue (`/events/<game>/<event>`,
`/user/<name>`), and the 2024 React page (`/assets/...`). Until this change nginx answered every unknown URL with the
React page and a 200, so none of them ever told a search engine it was gone.

## The rule

- A URL whose topic lives on in a page of the new site gets a **301** there. Today that is the home page, the future
  "What should I watch?": the event listings and the old homes, tournament lists and live-stream lists.
- A URL whose content is gone for good gets a **410 Gone**, so search engines drop it instead of retrying it as they
  do a 404. The 404 page is its body, so a visitor still sees a page with a link home. There is no blanket redirect
  to the home page: a single 2018 Hearthstone cup or a user profile redirected home would be a soft 404 to Google.
- Anything else gets the normal **404**. That covers the files bots probe for (feeds, `ads.txt`, `manifest.json`,
  `/.well-known/...`), which never held content here.

| Old URLs | Paths | Era | Answer |
|---|---|---|---|
| `/events`, `/events/` | 1 | 2019-2024 | 301 to `/` |
| `/events/<game>` for the 8 games listed (counter-strike, dota, hearthstone, heroes-of-the-storm, league-of-legends, overwatch, rocket-league, smite) | 8 | 2019-2024 | 301 to `/` |
| `/en`, `/ru`, `/en/tournaments`, `/en/streams/...` | 11 | 2014-2016 | 301 to `/` |
| `/events/<game>/<event>`, `/events/<id>` | 1,378 | 2019-2024 | 410 |
| `/user/<name>` | 302 | 2019-2024 | 410 |
| `/article/...`, `/blog/...` | 51 | 2017-2023 | 410 |
| every other `/en/...` and `/ru/...` (members, teams, boards, blogs, news, game pages) | 125 | 2014-2016 | 410 |
| `/web/...`, `/dist/...`, `/assets/...` (old sites' files) | 205 | 2014-2024 | 410 |
| `/api/...`, `/wp-api/...`, `/images/...` (the old backend at api.pcesports.com, which nginx proxied to) | 1 | 2021 | 410 |
| `/`, `/favicon.ico`, `/robots.txt`, `/sitemap.xml` | 4 | | 200, the new files |
| bot probes and feeds: `/.well-known/...`, `/ads.txt`, `/atom.xml`, `/feed/`, `/manifest.json`, `/cdn-cgi/...`... | 21 | | 404 (`/feed/` and a `/cdn-cgi/` path first drop their trailing slash) |

The `status` and `location` columns of `old-urls.csv` are what the nginx config itself answered for every path,
not what this table says. They were written by `OLD_URLS=docs/old-urls.csv npm run nginx:test` (it writes
`docs/old-urls.csv.answers.csv`), which runs the config in a throwaway nginx on a build. `npm run nginx:test` on its
own checks one URL of each group.

## Changing it

A later page that reuses one of these paths (an `/events` page, say) takes that path's line out of `old-urls.conf`
first: a location there wins over the site's own `location /`. Then run `npm run nginx:test`.
