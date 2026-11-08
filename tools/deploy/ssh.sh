#!/bin/sh
# The forced command of the deploy key in the live clone's ~/.ssh/authorized_keys (README.md, "Deploy"): the CI
# workflow's deploy job SSHes in with that key, and this runs npm run deploy in the clone it lives in. The command the
# SSH client asked for arrives in SSH_ORIGINAL_COMMAND: `dry-run` checks that this shell reaches GitHub, then runs
# npm run deploy -- --dry-run, which checks what a deploy needs and runs no step; anything else deploys, since the key
# is for nothing else.
set -eu
cd "$(dirname "$0")/../.."
# A forced command runs in a shell that read no login profile, which is where Debian puts ~/.local/bin on PATH.
PATH="$HOME/.local/bin:$PATH"
export PATH
if [ "${SSH_ORIGINAL_COMMAND:-}" = dry-run ]; then
  # What the deploy's first step pulls with, in this shell: a GitHub key held by an agent the owner's login forwards
  # is missing here.
  git ls-remote --exit-code --heads origin master > /dev/null || { echo 'git cannot read origin from this shell' >&2; exit 1; }
  exec npm run deploy -- --dry-run
fi
exec npm run deploy
