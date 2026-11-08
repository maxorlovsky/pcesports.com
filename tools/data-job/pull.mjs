// Puts origin's `data` branch into the checkout's domain data folders (app/domains/<domain>/data/, which git ignores),
// so a build is master's code with the newest data. The deploy runs it on every deploy, from a push to master or from
// the data job's dispatch, under the deploy's lock (tools/deploy/deploy.mjs); CI runs it before building master.
//   node tools/data-job/pull.mjs
// It clears the data folders first, so a failed fetch stops the build instead of shipping the last deploy's data, and
// it takes nothing from the branch but data files (data-branch.mjs, DATA_PATH): never code.
import { existsSync, mkdirSync, readdirSync, rmSync, writeFileSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { DATA_BRANCH, fetchData, readData } from './data-branch.mjs';

const ROOT = resolve(dirname(fileURLToPath(import.meta.url)), '../..');
const DOMAINS = join(ROOT, 'app', 'domains');

function fail(message) {
  console.error(`Data pull stopped: ${message}`);
  process.exit(1);
}

for (const domain of existsSync(DOMAINS) ? readdirSync(DOMAINS) : []) rmSync(join(DOMAINS, domain, 'data'), { recursive: true, force: true });

let commit;
let files;
try {
  commit = fetchData(ROOT);
  files = commit ? readData(ROOT, commit) : new Map();
} catch (error) {
  fail(error.message);
}
if (!commit) {
  console.log(`No ${DATA_BRANCH} branch on origin yet: building without data.`);
  process.exit(0);
}
for (const path of files.keys()) {
  const domain = path.split('/')[2];
  if (!existsSync(join(DOMAINS, domain, 'domain.json'))) fail(`the ${DATA_BRANCH} branch holds ${path}, for ${domain}, which is no domain in this checkout`);
}
for (const [path, text] of files) {
  mkdirSync(dirname(join(ROOT, path)), { recursive: true });
  writeFileSync(join(ROOT, path), text);
}
console.log(`Data from origin/${DATA_BRANCH} at ${commit.slice(0, 7)}: ${files.size} file${files.size === 1 ? '' : 's'}.`);
