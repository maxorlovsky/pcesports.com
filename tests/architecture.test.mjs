// docs/architecture.md's rules, checked against the real imports (tests/architecture/check.mjs). The fixtures below
// each break one rule in a small clean tree, so a rule that stops biting fails here.
import assert from 'node:assert/strict';
import { existsSync, readdirSync, readFileSync } from 'node:fs';
import { join, posix } from 'node:path';
import { describe, test } from 'node:test';
import { pathToFileURL } from 'node:url';
import { GENERATED, GENERATED_TSCONFIG, SOURCE, checkArchitecture } from './architecture/check.mjs';

const ROOT = join(import.meta.dirname, '..');

/** Not the project's code: dependencies, build output, the old apps (git-ignored) and the report's experiments. */
const SKIP = new Set(['.git', 'node_modules', '.nuxt', '.output', '.data', 'dist', 'site', 'site.next', '.releases', 'experiments', 'frontend-react', 'old-vuejs']);

/** Links the walk can't follow without leaving the tree: each is a test failure, since Nuxt follows them. */
const links = [];

function walk(dir) {
  return readdirSync(join(ROOT, dir), { withFileTypes: true }).flatMap((entry) => {
    const path = dir ? posix.join(dir, entry.name) : entry.name;
    if (SKIP.has(path)) return [];
    if (entry.isSymbolicLink()) links.push(path);
    if (entry.isDirectory()) return walk(path);
    return entry.isFile() ? [path] : [];
  });
}

const repoFiles = new Map(
  walk('')
    .filter((path) => SOURCE.test(path) || /\.(css|json|html)$/.test(path) || /^app\/domains\/[^/]+\/README\.md$/.test(path))
    .filter((path) => path !== 'package-lock.json')
    .map((path) => [path, readFileSync(join(ROOT, path), 'utf8')]),
);
const repoGenerated = new Map([...GENERATED, GENERATED_TSCONFIG].filter((path) => existsSync(join(ROOT, path)))
  .map((path) => [path, readFileSync(join(ROOT, path), 'utf8')]));
const domains = [...new Set([...repoFiles.keys()].map((path) => path.match(/^app\/domains\/([^/]+)\//)?.[1]).filter(Boolean))];

describe('the repository', () => {
  test('keeps every architecture rule', () => {
    assert.deepEqual(checkArchitecture(repoFiles, repoGenerated), []);
  });

  test('holds no symbolic link the check would not read', () => {
    assert.deepEqual(links, []);
  });

  // The data job runs in Node, outside Nuxt: each domain's plain entry must load there as it is.
  for (const domain of domains) {
    test(`Node loads app/domains/${domain}/index.ts as it is`, async () => {
      const entry = await import(pathToFileURL(join(ROOT, 'app/domains', domain, 'index.ts')).href);
      assert.ok(Object.keys(entry).length > 0);
    });
  }
});

const manifest = (name, deps) => JSON.stringify({ name, owns: [`${name} things`], allowedDomainDependencies: deps });

/** Two domains, `high` on top of `low`, with the shell, the server, a tool and a test using them: no rule broken. */
function cleanTree() {
  return new Map(Object.entries({
    'package.json': JSON.stringify({ dependencies: { vue: '3', nuxt: '4', '@fontsource/inter': '5' }, devDependencies: { typescript: '6' } }),
    'nuxt.config.ts': 'export default defineNuxtConfig({});',
    'app/domains/low/domain.json': manifest('low', []),
    'app/domains/low/README.md': '# low',
    'app/domains/low/index.ts': "export { rule } from './rule.ts';",
    'app/domains/low/rule.ts': "import { readFileSync } from 'node:fs';\nimport levels from './data/levels.json' with { type: 'json' };\nconst computed = levels.length;\nexport function rule(): number { return computed + readFileSync.length; }",
    'app/domains/low/data/levels.json': '[1, 2]',
    'app/domains/low/ui/card.css': '.card { color: red; }',
    'app/domains/low/ui/index.ts': "export { useLow } from './useLow.ts';",
    'app/domains/low/ui/useLow.ts': "import { rule } from '../rule.ts';\nexport const useLow = () => computed(() => rule());",
    'app/domains/low/ui/LowBadge.vue': '<template><b>low</b></template>\n<style scoped src="./card.css"></style>',
    'app/domains/low/views/LowPage.vue': '<script setup lang="ts">\nimport LowBadge from \'../ui/LowBadge.vue\';\nimport { useLow } from \'../ui/useLow.ts\';\nuseLow();\n</script>\n<template><LowBadge /></template>',
    'app/domains/high/domain.json': manifest('high', ['low']),
    'app/domains/high/README.md': '# high',
    'app/domains/high/index.ts': "export { use } from './use.ts';",
    'app/domains/high/use.ts': "import { rule } from '../low/index.ts';\nexport const use = () => rule() + 1;",
    'app/domains/high/views/HighPage.vue': '<script setup lang="ts">\nimport { useLow } from \'~/domains/low/ui\';\nimport { use } from \'../use.ts\';\nuseLow();\nuse();\n</script>',
    'app/app.vue': '<template><NuxtLayout><NuxtPage /></NuxtLayout></template>',
    'app/error.vue': '<script setup lang="ts">\nimport LowPage from \'~/domains/low/views/LowPage.vue\';\n</script>',
    'app/pages/index.vue': '<script setup lang="ts">\nimport HighPage from \'~/domains/high/views/HighPage.vue\';\n</script>\n<template><HighPage /></template>',
    'app/layouts/default.vue': '<template><slot /></template>',
    'app/assets/css/base.css': "@import '@fontsource/inter/index.css';\nbody { margin: 0; }",
    'server/routes/low.txt.ts': "import { rule } from '~~/app/domains/low';\nexport default defineEventHandler(() => String(rule()));",
    'tools/job.mjs': "import { use } from '../app/domains/high/index.ts';\nuse();",
    'tests/low.test.mjs': "import assert from 'node:assert/strict';\nimport { rule } from '../app/domains/low/index.ts';\nassert.ok(rule());",
    'eslint.config.js': "import ts from 'typescript';\nexport default [ts];",
  }));
}

function cleanGenerated() {
  const generated = new Map(GENERATED.map((path) => [path, [
    "export { computed, ref } from 'vue';",
    "  const computed: typeof import('vue').computed",
    "  const ref: typeof import('vue').ref",
    "  const useHead: typeof import('../../node_modules/nuxt/dist/head/runtime/composables').useHead",
    "  const defineEventHandler: typeof import('../../node_modules/h3').defineEventHandler",
  ].join('\n')]));
  generated.set(GENERATED_TSCONFIG, JSON.stringify({ vueCompilerOptions: { checkUnknownComponents: true } }));
  return generated;
}

/** The rules the tree breaks once `changes` are applied to the clean one (a null text deletes the file). */
function rulesBroken(changes, generated = cleanGenerated()) {
  const files = cleanTree();
  for (const [path, text] of Object.entries(changes)) {
    if (text === null) files.delete(path);
    else files.set(path, text);
  }
  return [...new Set(checkArchitecture(files, generated).map((v) => v.rule))].sort();
}

describe('the architecture check', () => {
  test('passes the clean fixture', () => {
    assert.deepEqual(checkArchitecture(cleanTree(), cleanGenerated()), []);
  });

  const cases = [
    ['a script outside tools/ deep-imports a domain', { 'scripts/job.mjs': "import { rule } from '../app/domains/low/rule.ts';\nrule();" }, ['public-entry']],
    ["a domain reads another domain's data file", { 'app/domains/high/count.ts': "import levels from '../low/data/levels.json' with { type: 'json' };\nexport const n = levels.length;" }, ['public-entry']],
    ["a component takes another domain's stylesheet", { 'app/domains/high/views/Styled.vue': '<template><b /></template>\n<style src="../../low/ui/card.css"></style>' }, ['public-entry']],
    ["a stylesheet imports another domain's", { 'app/assets/css/base.css': "@import '~/domains/low/ui/card.css';" }, ['public-entry']],
    ["a component's style imports another domain's", { 'app/domains/high/views/Imp.vue': '<template><b /></template>\n<style>\n@import url("../../low/ui/card.css");\n</style>' }, ['public-entry']],
    ['a script requires a domain file', { 'tools/old.cjs': "const { rule } = require('../app/domains/low/rule.ts');\nrule();" }, ['public-entry']],
    ['a domain deep-imports another domain', { 'app/domains/high/ui/deep.ts': "import { rule } from '~/domains/low/rule.ts';\nexport const d = rule;" }, ['public-entry']],
    ['a deep import goes through a relative path', { 'app/domains/high/deep.ts': "import { rule } from '../low/rule.ts';\nexport const d = rule;" }, ['public-entry']],
    ["a domain deep-imports another domain's UI", { 'app/domains/high/views/Deep.vue': '<script setup lang="ts">\nimport LowBadge from \'~/domains/low/ui/LowBadge.vue\';\n</script>' }, ['public-entry']],
    ['a page deep-imports a domain', { 'app/pages/deep.vue': '<script setup lang="ts">\nimport { rule } from \'~/domains/low/rule.ts\';\n</script>' }, ['public-entry']],
    ['a tool deep-imports a domain', { 'tools/deep.mjs': "import { rule } from '../app/domains/low/rule.ts';\nrule();" }, ['public-entry']],
    ['a component loads another domain\'s internals as its script', { 'app/domains/high/views/Src.vue': '<script setup lang="ts" src="../../low/rule.ts"></script>' }, ['public-entry']],
    ["a domain renders another domain's view", { 'app/domains/high/views/Both.vue': '<script setup lang="ts">\nimport LowPage from \'~/domains/low/views/LowPage.vue\';\n</script>' }, ['public-entry']],
    ['a domain imports one its domain.json does not allow', { 'app/domains/low/up.ts': "import { use } from '../high/index.ts';\nexport const u = use;" }, ['domain-dependency']],
    ["a domain's view imports one its domain.json does not allow", { 'app/domains/low/views/Up.vue': '<script setup lang="ts">\nimport { use } from \'~/domains/high\';\n</script>' }, ['domain-dependency']],
    ['a domain reaches up through a folder path', { 'app/domains/low/ui/up.ts': "import { use } from '~/domains/high/';\nexport const u = use;" }, ['domain-dependency']],
    ["a domain's plain part imports Vue", { 'app/domains/low/state.ts': "import { ref } from 'vue';\nexport const n = ref(0);" }, ['plain']],
    ["a domain's plain part imports Nuxt", { 'app/domains/low/app.ts': "import { useNuxtApp } from '#app';\nexport const a = useNuxtApp;" }, ['plain']],
    ["a domain's plain part uses an auto-imported Nuxt API", { 'app/domains/low/head.ts': 'export const setTitle = (title: string) => useHead({ title });' }, ['plain']],
    ["a domain's plain part imports a Nuxt module", { 'app/domains/low/head.ts': "import { useHead } from '#unhead/composables';\nexport const h = useHead;" }, ['plain', 'resolve']],
    ["a domain's plain part imports a package it has not been cleared for", { 'app/domains/low/parse.ts': "import ts from 'typescript';\nexport const p = ts;" }, ['plain']],
    ["a domain's plain part imports without the .ts extension", { 'app/domains/low/again.ts': "export { rule } from './rule';" }, ['plain']],
    ["a domain's plain part imports another domain by an alias", { 'app/domains/high/up.ts': "import { rule } from '~/domains/low';\nexport const u = rule;" }, ['plain']],
    ["a domain's plain part uses an auto-imported name an interface also has", { 'app/domains/low/r.ts': 'interface R { ref: string }\nexport const r = (x: R) => ref(x.ref);' }, ['plain']],
    ["a domain's plain part uses an auto-imported name an object key also has", { 'app/domains/low/h.ts': 'const keys = { useHead: 1 };\nexport const h = () => useHead({ title: String(keys.useHead) });' }, ['plain']],
    ["a domain's plain part uses an auto-imported name another function declares", { 'app/domains/low/s.ts': 'function local() { const ref = 1; return ref; }\nexport const s = () => ref(local());' }, ['plain']],
    ["a domain's plain part calls Nuxt's $fetch", { 'app/domains/low/client.ts': 'export const getJson = (url: string) => $fetch(url);' }, ['plain']],
    ["a domain's plain part reads Nuxt's import.meta.client", { 'app/domains/low/where.ts': 'export const inBrowser = () => import.meta.client;' }, ['plain']],
    ["a domain's plain part reads Vite's import.meta.env", { 'app/domains/low/env.ts': 'export const key = () => import.meta.env.KEY;' }, ['plain']],
    ['a tool loads a domain file through createRequire', { 'tools/req.mjs': "import { createRequire } from 'node:module';\nconst load = createRequire(import.meta.url);\nload('../app/domains/low/rule.ts');" }, ['resolve']],
    ["a component's template is another domain's file", { 'app/domains/high/views/Tpl.vue': '<template src="../../low/ui/card.html"></template>', 'app/domains/low/ui/card.html': '<b />' }, ['public-entry']],
    ['a component takes an asset of another domain by URL', { 'app/domains/high/views/Url.vue': '<script setup lang="ts">\nconst bg = new URL(\'../../low/ui/bg.png\', import.meta.url).href;\n</script>' }, ['public-entry']],
    ['a script passes require around', { 'tools/alias.cjs': "const load = require;\nload('../app/domains/low/rule.ts');" }, ['resolve']],
    ['a script calls module.require', { 'tools/mod.cjs': "module.require('../app/domains/low/rule.ts');" }, ['public-entry']],
    ["a domain's plain part destructures import.meta", { 'app/domains/low/meta.ts': 'const { env } = import.meta;\nexport const e = () => env;' }, ['plain']],
    ["a domain's plain part calls globalThis.$fetch", { 'app/domains/low/g.ts': 'export const get = (u: string) => globalThis.$fetch(u);' }, ['plain']],
    ['a style takes an image from another domain', { 'app/domains/high/views/Bg.vue': '<template><b /></template>\n<style>\nb { background: url("~/domains/low/ui/bg.png"); }\n</style>' }, ['public-entry']],
    ["a domain's plain part imports its own UI", { 'app/domains/low/ui-back.ts': "import { useLow } from './ui/useLow.ts';\nexport const u = useLow;" }, ['plain']],
    ["a domain's plain entry exports a component", { 'app/domains/low/index.ts': "export { rule } from './rule.ts';\nexport { default as LowPage } from './views/LowPage.vue';" }, ['plain']],
    ["the server imports a domain's UI entry", { 'server/routes/ui.ts': "import { useLow } from '~~/app/domains/low/ui';\nexport default defineEventHandler(() => useLow());" }, ['plain']],
    ["a tool imports a domain's UI entry", { 'tools/ui.mjs': "import { useLow } from '../app/domains/low/ui/index.ts';\nuseLow();" }, ['plain']],
    ['a domain imports its own entry', { 'app/domains/low/self.ts': "import { rule } from './index.ts';\nexport const s = rule;" }, ['own-entry']],
    ["a domain's UI imports its own UI entry through '../'", { 'app/domains/low/ui/Self.vue': '<script setup lang="ts">\nimport { useLow } from \'../ui\';\n</script>' }, ['own-entry']],
    ['an import is an absolute path', { 'app/pages/abs.vue': '<script setup lang="ts">\nimport { rule } from \'/app/domains/low/rule.ts\';\n</script>' }, ['resolve']],
    ['an import names an alias no package is', { 'app/pages/alias.vue': '<script setup lang="ts">\nimport { rule } from \'@low/rule.ts\';\n</script>' }, ['resolve']],
    ['a script imports a Nuxt virtual module', { 'tools/nuxt.mjs': "import { useNuxtApp } from '#app';\nuseNuxtApp();" }, ['resolve']],
    ['a tool computes an import path', { 'tools/lazy.mjs': "const path = '../app/domains/low/rule.ts';\nawait import(path);" }, ['resolve']],
    ['a tool requires a computed path', { 'tools/lazy.cjs': "const path = '../app/domains/low/rule.ts';\nrequire(path);" }, ['resolve']],
    ['an import names no file', { 'app/domains/low/lost.ts': "import { gone } from './missing.ts';\nexport const g = gone;" }, ['resolve']],
    ['an import computes its path', { 'app/pages/any.vue': '<script setup lang="ts">\nconst name = \'low\';\nconst mod = import(`~/domains/${name}`);\n</script>' }, ['resolve']],
    ['code imports by pattern', { 'app/pages/glob.vue': '<script setup lang="ts">\nconst all = import.meta.glob(\'~/domains/*/index.ts\');\n</script>' }, ['resolve']],
    ['a composable sits in a folder Nuxt auto-imports', { 'app/composables/useThing.ts': 'export const useThing = () => 1;' }, ['layout']],
    ['a component sits in a folder Nuxt auto-registers', { 'app/components/Thing.vue': '<template><b /></template>' }, ['layout']],
    ['code sits in shared/', { 'shared/utils/thing.ts': 'export const thing = 1;' }, ['layout']],
    ['code sits in server/utils', { 'server/utils/thing.ts': 'export const thing = 1;' }, ['layout']],
    ['code sits in a local Nuxt module', { 'modules/m.ts': 'export default {};' }, ['layout']],
    ['a script sits in app/assets', { 'app/assets/js/x.ts': 'export const x = 1;' }, ['layout']],
    ['code sits in a Nuxt layer', { 'layers/extra/app/pages/x.vue': '<template><b /></template>' }, ['layout']],
    ["a component sits in a domain's plain part", { 'app/domains/low/Widget.vue': '<template><b /></template>' }, ['layout']],
    ['a view sits in a subfolder', { 'app/domains/low/views/parts/Part.vue': '<template><b /></template>' }, ['layout']],
    ["a domain's plain part is JavaScript", { 'app/domains/low/old.js': 'export const old = 1;' }, ['layout']],
    ['a domain has no README', { 'app/domains/low/README.md': null }, ['manifest']],
    ['a domain has no domain.json', { 'app/domains/low/domain.json': null }, ['manifest']],
    ['a domain has no public entry', { 'app/domains/extra/domain.json': manifest('extra', []), 'app/domains/extra/README.md': '# extra', 'app/domains/extra/thing.ts': 'export const t = 1;' }, ['manifest']],
    ['a manifest names another folder', { 'app/domains/low/domain.json': manifest('lower', []) }, ['manifest']],
    ['a manifest allows an unknown domain', { 'app/domains/high/domain.json': manifest('high', ['low', 'ghost']) }, ['manifest']],
    ['domains allow each other in a circle', { 'app/domains/low/domain.json': manifest('low', ['high']) }, ['manifest']],
  ];
  for (const [name, changes, rules] of cases) {
    test(`fails when ${name}`, () => {
      assert.deepEqual(rulesBroken(changes), rules);
    });
  }

  test('fails when Nuxt auto-imports project code', () => {
    const generated = cleanGenerated();
    generated.set('.nuxt/types/nitro-imports.d.ts', "  const thing: typeof import('../../server/utils/thing').thing");
    assert.deepEqual(rulesBroken({}, generated), ['auto-import']);
  });

  test('fails when typecheck stops failing on a component used without an import', () => {
    const generated = cleanGenerated();
    generated.set(GENERATED_TSCONFIG, JSON.stringify({ vueCompilerOptions: {} }));
    assert.deepEqual(rulesBroken({}, generated), ['auto-import']);
  });

  test("fails when Nuxt's generated lists are missing", () => {
    const generated = cleanGenerated();
    generated.delete('.nuxt/imports.d.ts');
    assert.deepEqual(rulesBroken({}, generated), ['auto-import']);
  });
});
