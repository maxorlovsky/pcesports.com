# PCesports

<https://www.pcesports.com>: esports for people who don't have time to follow everything. A static site built with
[Nuxt](https://nuxt.com) (Vue, TypeScript): `nuxt generate` renders every page to plain HTML, nginx serves the files,
and nothing runs on the server but nginx.

## Run it

Node.js 24 (`package.json`'s `engines`).

```sh
npm ci              # install
npm run dev         # dev server with hot reload, http://localhost:3000
npm run generate    # the static site, into .output/public (`dist` links to it)
npm run preview     # serve that build as nginx would, http://localhost:3000 (tools/preview.mjs)
```

## Checks

Run them all before a pull request: `npm run check`, then `npm run nginx:test`. CI runs the same on every pull
request into dev or master (`.github/workflows/ci.yml`), except the nginx one.

| Command | What it checks |
|---|---|
| `npm run lint` | ESLint (TypeScript, Vue) and Stylelint (CSS, Vue styles) |
| `npm run typecheck` | `vue-tsc` through `nuxt typecheck` |
| `npm test` | `tests/`, with Node's test runner: the architecture rules (`docs/architecture.md`), AGENTS.md's size, the build check, the deploy script, the `site` domain's consent rules, the `matches`, `vods` and `watch` rules, the guides (their format, length, typography, the rating guide's facts, and no spoiler the build check reads) and the data job (against fake APIs and a scratch origin) |
| `npm run generate` then `npm run check:build` | each page's content is in its HTML (readable with JavaScript off; the guides' pages are read from `app/domains/guides/content/`), its title, description, canonical URL and Open Graph tags, the sitemap and robots.txt, nothing of the old site, no Google analytics tag in a page's HTML, and no result of a listed match (a result field, a winner's id or name, a map length, a score or map count in a page or a script's strings) (`tools/check-build.mjs`) |
| `npm run nginx:test` | `tools/nginx/nginx.prod.conf` in a throwaway nginx: its TLS rules (see "On max-cloud"), `nginx -t`, then the answers of the pages, the redirects to www, the old URLs, Let's Encrypt's challenge and the three headers of every HTTPS 2xx and 3xx answer (`tools/nginx/test.sh`). Needs nginx, so it runs on the factory machine, not in CI |

## Where things are

- `app/domains/`: the domains, each owning its rules, data and UI (`docs/architecture.md`); each one's `README.md`
  lists what it owns.
  - `site`: the site's address, the sitemap's pages, the footer's data credits and the contact address (`site.ts`),
    the layout, header and footer, `usePageSeo` (every page's title, description, canonical URL and Open Graph tags),
    the analytics consent banner and Google Analytics, which loads only after "Accept" (its `README.md`), and the
    privacy and 404 pages' content.
  - `matches` and `vods`: the CS2 matches from PandaScore and their official VODs, which the data job keeps current
    (see "Data job").
  - `watch`: the after-match rating and the "What should I watch?" list the data job writes, and the home page that
    shows it (its `README.md` has the formula and the no-spoiler rules).
  - `guides`: the guides, one Markdown file each in `app/domains/guides/content/`, at `/guides` and `/guides/<slug>`
    (its `README.md`: adding a guide, the format, the rules every guide keeps). `docs/guides-sources.md` lists the
    primary source of every fact in them that can change.
- `app/app.vue`, `app/pages/`, `app/layouts/`, `app/error.vue`, `server/routes/`: the thin shell Nuxt needs in fixed
  places; each file renders or serves one domain's view or function.
- `app/assets/css/tokens.css`: the design tokens (colours, type, spacing, radii) as CSS variables, which components
  take their colours, fonts and spacing from. The brand colours are `--color-main` `#5587e4` and `--color-secondary` `#e5dc2d`, on black.
- `server/routes/sitemap.xml.ts`: prerendered into `sitemap.xml` at build time.
- `public/`: copied as is: the logo, the favicon, `robots.txt` and `og.png`, the Open Graph image
  (`tools/og-image.mjs` renders it).
- `tools/nginx/`: production's vhost and the old URLs' redirects (`docs/old-urls.md`).
- `tools/deploy/`: the deploy and its SSH forced command. `tools/data-job/`: the data job, and `pull.mjs`, which puts
  the data branch's files in place for a build.
- `docs/`: the architecture, the feasibility report, the guides' sources and the old URLs. `experiments/`: the data experiments (not part
  of the site).
- `AGENTS.md` (`CLAUDE.md` imports it): the rules coding agents work by.

## Branches

Work goes into `dev` through pull requests. The owner merges `dev` into `master`, and a push to `master` deploys. The
`data` branch is the data job's (see "Data job"): data files only, one commit, never merged.

## Data job

The site's CS2 data (PandaScore's tier-1 matches and the organisers' official VODs) comes from the data job,
`tools/data-job/run.mjs` (`npm run data-job`). Cron runs it every 15 minutes on the factory machine, veilweaver2, in a
clone of its own on master, `~/pcesports-data`, never the factory's checkout. The rules it applies are the `matches` and
`vods` domains' (their `README.md`); the sources, terms and quota are in `docs/feasibility/cs2-data-and-vods.md`.

The job's clone is a plain one (`-b master`: the repository's default branch is `dev`):

```
git clone -b master https://github.com/maxorlovsky/pcesports.com.git ~/pcesports-data
```

The factory user's crontab holds its line (`node`, `git`, `gh` and `flock` are in `/usr/bin`, on the crontab's PATH):

```
*/15 * * * * cd "$HOME/pcesports-data" && { { test -f tools/data-job/run.mjs || git pull -q --ff-only --no-tags origin master; } && test -f tools/data-job/run.mjs && node tools/data-job/run.mjs; } >> "$HOME/pcesports-data-job.log" 2>&1
```

While the clone has no job, the line pulls master by name and runs nothing, so it could go in before the job reached
master. Once the job is there, the line only starts it, and every git step runs inside the job's lock (step 2 pulls).
A bare `git pull` would fetch the `data` branch too, outside the lock and with a reflog.

`~/pcesports-data-job.log` stays empty while nothing goes wrong: the job prints only problems. Check it, and the last
lines of `~/.local/state/pcesports-data-job/job.log` (one per run that reaches the job, with the day's YouTube units).

Each run:

1. takes a `flock` lock in its state folder: a second run sharing that folder (a cron run and one by hand) logs that it
   skipped and stops;
2. pulls master (fast-forward only) and runs the job it pulled, as the deploy does;
3. reads PandaScore's matches scheduled from 3 days ago to 14 days ahead, keeps the tier-1 ones (tier `s` or `a` from a
   league in `app/domains/matches/leagues.json`), and merges them into the stored list (30 days back);
4. reads the new uploads of each whitelisted VOD channel through its uploads playlist, re-reads the uploads it first saw
   live, re-checks the whitelist once a day, refreshes every linked video weekly, and links each match to its VOD
   (`app/domains/vods/README.md`, "The matcher"; a run whose PandaScore read failed links nothing new);
5. rates the finished matches of the last 7 days that have a VOD, for the home page's list
   (`app/domains/watch/README.md`): the team ratings are replayed from a year of PandaScore's tier s and a results,
   which the state folder keeps. The first run reads that year (about a dozen requests), and a run after the job was
   off longer than the 3 days each run reads catches up on the stretch it missed; until such a read succeeds the list
   keeps its entries. A stored match from before the record kept map lengths takes them from that year's read;
6. publishes the data branch if the data changed and the last publish is 30 minutes old or more, then starts the deploy
   (`gh workflow run ci.yml --ref master`);
7. writes `report.md`, `state.json` and a line in `job.log` in its state folder, `~/.local/state/pcesports-data-job/`.
   With nothing wrong it prints nothing; a problem goes to stderr and the exit code is 1.

**Batching:** at most one publish per 30 minutes (2 minutes' slack for how long a run takes, so it is the second run
after a publish, never the third), so at most 48 deploys a day, about 20 on an event day. A change waits for the first
run after the gap, so a new VOD is live within about 45 minutes of its upload (15 for the poll, 30 for the gap, plus the
deploy). The organisers upload their VODs hours after a match, so this costs little.

**The `data` branch** holds data files only (`app/domains/<domain>/data/*.json`) as a single commit: each publish
replaces it, with a push that only succeeds if the branch is still what the run read, so two clones can't both publish.
It is never merged, so code branches and history never collect data, and YouTube's ids don't pile up in history
(YouTube's 30-day rule). Every deploy, from a push to master or from the job, builds master's code with the branch's
current files: `tools/deploy/deploy.mjs` runs `tools/data-job/pull.mjs`, which empties the data folders, fetches the
branch and takes nothing but data files. The clones keep no replaced data commit: the job's clone and every deploy prune
them (no reflog holds them), so old YouTube ids don't linger in `.git`. Both deploys share the deploy's lock
(`.deploy.lock`) and CI's concurrency group, so they never overlap. CI's runs on master pull the data too; pull requests
build without it.

**What it stores.** Committed: the matches (PandaScore's data, credited "Source: PandaScore" wherever shown), per
match, YouTube's video and channel ids, and the home page's list (`watch.json`: what the page shows, with the video id
and no result). On the machine only, in `state.json`: each upload's title, publish time and length, kept 14 days after
they were last read, when each linked video was last refreshed, and a year of tier s and a results for the team
ratings. `report.md` gives the list's count per label, and lists the finished matches with no VOD 12 hours after their
start, the full-length uploads no match links, the dropped links, the tier-1 leagues left out, and every link, for
hand checks.

**YouTube quota:** measured per run with the 4 VOD channels: 4 units (one `playlistItems.list` each), plus 1 when there
are uploads to read (`videos.list`), plus 1 when linked videos fall due for their weekly refresh (per 50), plus 4 once a
day for the whitelist check. A cold first run cost 9 and a 15-day backfill 10. A day of 15-minute runs is 96 x 4 = 384
units when nothing is new, about 580 on a busy day: under 6% of the 10,000-unit daily quota (a failing whitelist check
retries every run, up to 4 units more each, still under 10%). `job.log` gives each run's units and the day's total
(`today=`). PandaScore: 5 requests per run, about 20 an hour of its 1,000, plus about 12 for a year of results on a
first run.

**The env file**, `~/.config/pcesports/data-job.env` (or `--env <path>`, or `PCESPORTS_DATA_JOB_ENV`), outside the
repository and `chmod 600` (the job refuses anything else):

```sh
PANDASCORE_TOKEN=...   # PandaScore, free plan
YOUTUBE_API_KEY=...    # YouTube Data API v3
GH_TOKEN=...           # fine-grained, this repository only: Contents and Actions, read and write
```

`GH_TOKEN` needs Contents for the push to `data` and Actions to start the deploy. The job stops without the file, and
takes the secrets from it only: one the file leaves out is unset, whatever the shell exports. git reads `GH_TOKEN`
through gh's credential helper (`gh auth setup-git`, set up on veilweaver2). Without `GH_TOKEN`, git and gh use the
machine's own GitHub login, which can push but can't start a workflow. The job refreshes every linked video on its first
run with a new state folder, since the refresh times live there.

**Other clones:** once the `data` branch exists, every `git fetch` in a working clone stores its commits too, and only
that clone's own `git gc` drops them. `git config --add remote.origin.fetch '^refs/heads/data' && git update-ref -d
refs/remotes/origin/data` keeps the branch out of that clone's fetches; the data job and the deploy fetch it by name
and prune their own clones. A run by hand (`--no-push`) deletes its ref to the branch afterwards and runs no gc.

**By hand:** `node tools/data-job/run.mjs --no-push` runs on any branch of a clone and writes the data to the state
folder's `out/` instead of publishing; `--since <date>` reads uploads and matches back to that date (a backfill, or the
data for a check). `PCESPORTS_DATA_JOB_STATE` points it at another state folder.

**Stopping it:** remove the cron line. The site keeps the data of its last deploy, and later deploys pull the data
branch as it was, so if the job stays off for good, within 30 days delete the branch (`git push origin --delete data`),
the state folder (`state.json` holds titles and ids) and the job's clone, then deploy again (Actions → CI → Run
workflow, on master) so the site drops the ids too: their YouTube data is no longer refreshed.

## Deploy

Every push to master deploys: the CI workflow runs the checks, then its deploy job SSHes into max-cloud with a deploy
key that can only run `tools/deploy/ssh.sh`. That runs `npm run deploy` in the live clone, `/var/www/pcesports.com`
(user `max`), which:

1. refuses any branch but master, a checkout with changes, and a second deploy while one runs;
2. pulls master (fast-forward only) and runs the deploy it pulled;
3. puts the data branch's files into the domains' data folders (`tools/data-job/pull.mjs`, see "Data job"); a fetch that
   fails stops the deploy, so a release never ships older data;
4. runs `npm ci`, `npm run generate` and the build check, on the server's own Node.js, which must be 24 or later;
5. copies the build into `.releases/<time>-<commit>` and points the `site` link at it in one step. nginx serves `site`,
   so a failed build leaves the site as it was. The two releases before it stay, to go back to, and older ones are
   deleted. One it can't delete leaves the site switched but ends the run red, naming the files in it that another user
   owns (a root-owned `.well-known` is certbot's: see "On max-cloud").

Actions → CI → Run workflow, on master, deploys again without a new commit; the data job starts the same run after it
publishes new data. `npm run deploy -- --dry-run` in the clone checks the same and prints the steps without running
them.

### On max-cloud

- `/etc/nginx/sites-enabled/pcesports.com` is a symlink to `tools/nginx/nginx.prod.conf` in the live clone. nginx
  reads it at every reload, so never move, rename or delete that file, and keep the clone on master. A deploy doesn't
  reload nginx: after one that changed either nginx file, run `sudo nginx -t && sudo systemctl reload nginx`.
- The vhost's TLS settings (protocols, ciphers, session cache, DH parameters) and its three headers
  (Strict-Transport-Security, X-Frame-Options, X-Content-Type-Options) are written out in each HTTPS server block of
  `nginx.prod.conf`, which includes no shared `/etc/nginx/snippets` file: a change to one would reach every site that
  includes it. Every HTTPS site on the server shares port 443, and one site's TLS settings can break another's
  handshakes, so they match the other sites' and change only together with them. `npm run nginx:test` holds
  `ssl_protocols` to the agreed line, once per HTTPS block, and fails on any include but `old-urls.conf`, on
  `default_server` and on a TLS setting in `old-urls.conf`. A change takes effect at nginx's next reload, whatever
  starts it (certbot's renewals too), so reload right after the deploy that brings it (above), then check that every
  site on the server answers, for each host:
  `curl -sk -o /dev/null -w '%{http_code}\n' --resolve <host>:443:127.0.0.1 https://<host>/`.
- The vhost serves `https://www.pcesports.com`; plain HTTP and the bare domain redirect there. Cloudflare's proxy sits
  in front. No server block keeps an access log (the privacy page says so).
- The certificate, `pcesports.com` for both names, is Let's Encrypt's, renewed by certbot with the webroot method. Its
  webroot for both names (`webroot_map` in `/etc/letsencrypt/renewal/pcesports.com.conf`) is
  `/var/www/pcesports.com/acme`, which the vhost serves at `http://<name>/.well-known/acme-challenge/`. That folder is
  in the clone, ignored by git, and outside `.releases` on purpose: certbot runs as root, and a root-owned `.well-known`
  inside a release would stop the deploy that prunes that release, which the deploy user can't delete.
  `sudo certbot renew --dry-run --cert-name pcesports.com` checks a renewal.
- The deploy key, in `~max/.ssh/authorized_keys`, can run nothing but the forced command:

  ```
  command="sh /var/www/pcesports.com/tools/deploy/ssh.sh",no-port-forwarding,no-pty,no-agent-forwarding,no-X11-forwarding ssh-ed25519 AAAA… pcesports-deploy@github
  ```

  Whatever command it is sent, it deploys, except `dry-run`, which checks that git reaches GitHub from that shell and
  then runs the dry run: `ssh -i <key> max@<host> dry-run` ends in `Dry run: nothing ran.` A forced command runs in a
  shell that read no login profile, so node, npm, git and flock must be on that shell's own PATH (the script adds
  `~/.local/bin`), and the clone's GitHub access must work without an SSH agent.
- The repository secrets: `PROD_DEPLOY_HOST`, `PROD_DEPLOY_SSH_USER` (`max`), `PROD_DEPLOY_SSH_KEY` (the deploy key's
  private half) and, if SSH isn't on port 22, `PROD_DEPLOY_SSH_PORT`.

### Going back

`cd /var/www/pcesports.com && ls .releases`, then
`test -f .releases/<earlier>/index.html && ln -sfn .releases/<earlier> site.next && mv -T site.next site` shows an
earlier release at once (`test` stops a mistyped name). The next deploy shows master again, so for anything lasting,
revert the change on master with a pull request.
