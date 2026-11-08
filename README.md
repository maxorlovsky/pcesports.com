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
| `npm test` | `tests/`, with Node's test runner: the architecture rules (`docs/architecture.md`), AGENTS.md's size, the build check and the deploy script |
| `npm run generate` then `npm run check:build` | each page's content is in its HTML (readable with JavaScript off), its title, description, canonical URL and Open Graph tags, the sitemap and robots.txt, and nothing of the old site (`tools/check-build.mjs`) |
| `npm run nginx:test` | `tools/nginx/nginx.prod.conf` in a throwaway nginx: `nginx -t`, then the answers of the pages, the redirects to www and the old URLs (`tools/nginx/test.sh`). Needs nginx and the `/etc/nginx/snippets` it includes (the factory machine's copy of `ssl-params.conf`), so it runs on the factory machine, not in CI |

## Where things are

- `app/domains/`: the domains, each owning its rules, data and UI (`docs/architecture.md`). Today only `site`: the
  site's address, the sitemap's pages, the footer's data credits and the contact address (`site.ts`), the layout,
  header and footer, `usePageSeo` (every page's title, description, canonical URL and Open Graph tags), and the home,
  privacy and 404 pages' content. Its `README.md` lists what it owns.
- `app/app.vue`, `app/pages/`, `app/layouts/`, `app/error.vue`, `server/routes/`: the thin shell Nuxt needs in fixed
  places; each file renders or serves one domain's view or function.
- `app/assets/css/tokens.css`: the design tokens (colours, type, spacing, radii) as CSS variables, which components
  take their colours, fonts and spacing from. The brand colours are `--color-main` `#5587e4` and `--color-secondary` `#e5dc2d`, on black.
- `server/routes/sitemap.xml.ts`: prerendered into `sitemap.xml` at build time.
- `public/`: copied as is: the logo, the favicon, `robots.txt` and `og.png`, the Open Graph image
  (`tools/og-image.mjs` renders it).
- `tools/nginx/`: production's vhost and the old URLs' redirects (`docs/old-urls.md`).
- `tools/deploy/`: the deploy and its SSH forced command.
- `docs/`: the architecture, the feasibility report and the old URLs. `experiments/`: the data experiments (not part
  of the site).
- `AGENTS.md` (`CLAUDE.md` imports it): the rules coding agents work by.

## Branches

Work goes into `dev` through pull requests. The owner merges `dev` into `master`, and a push to `master` deploys.

## Deploy

Every push to master deploys: the CI workflow runs the checks, then its deploy job SSHes into max-cloud with a deploy
key that can only run `tools/deploy/ssh.sh`. That runs `npm run deploy` in the live clone, `/var/www/pcesports.com`
(user `max`), which:

1. refuses any branch but master, a checkout with changes, and a second deploy while one runs;
2. pulls master (fast-forward only) and runs the deploy it pulled;
3. runs `npm ci`, `npm run generate` and the build check, on the server's own Node.js, which must be 24 or later;
4. copies the build into `.releases/<time>-<commit>` and points the `site` link at it in one step. nginx serves
   `site`, so a failed build leaves the site as it was. The two releases before it stay, to go back to.

Actions → CI → Run workflow, on master, deploys again without a new commit. `npm run deploy -- --dry-run` in the clone
checks the same and prints the steps without running them.

### On max-cloud

- `/etc/nginx/sites-enabled/pcesports.com` is a symlink to `tools/nginx/nginx.prod.conf` in the live clone. nginx
  reads it at every reload, so never move, rename or delete that file, and keep the clone on master. A deploy doesn't
  reload nginx: after one that changed either nginx file, run `sudo nginx -t && sudo systemctl reload nginx`.
- The vhost serves `https://www.pcesports.com`; plain HTTP and the bare domain redirect there. Cloudflare's proxy sits
  in front. No server block keeps an access log (the privacy page says so).
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
