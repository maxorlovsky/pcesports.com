// docs/architecture.md's rules, checked against the real imports (tests/architecture/check.mjs). The fixtures below
// each break one rule in a small clean tree, so a rule that stops biting fails here.
import assert from 'node:assert/strict';
import { existsSync, readdirSync, readFileSync } from 'node:fs';
import { join, posix } from 'node:path';
import { describe, test } from 'node:test';
import { pathToFileURL } from 'node:url';
import { GENERATED, SOURCE, checkArchitecture } from './architecture/check.mjs';

const ROOT = join(import.meta.dirname, '..');

function walk(dir) {
  if (!existsSync(join(ROOT, dir))) return [];
  return readdirSync(join(ROOT, dir), { withFileTypes: true }).flatMap((entry) => {
    const path = posix.join(dir, entry.name);
    return entry.isDirectory() ? walk(path) : [path];
  });
}

const repoFiles = new Map(
  [...['app', 'server', 'shared', 'layers', 'tests', 'tools'].flatMap(walk), 'nuxt.config.ts']
    .filter((path) => SOURCE.test(path) || /^app\/domains\/[^/]+\/(domain\.json|README\.md)$/.test(path))
    .map((path) => [path, readFileSync(join(ROOT, path), 'utf8')]),
);
const repoGenerated = new Map(GENERATED.filter((path) => existsSync(join(ROOT, path))).map((path) => [path, readFileSync(join(ROOT, path), 'utf8')]));
const domains = [...new Set([...repoFiles.keys()].map((path) => path.match(/^app\/domains\/([^/]+)\//)?.[1]).filter(Boolean))];

describe('the repository', () => {
  test('keeps every architecture rule', () => {
    assert.deepEqual(checkArchitecture(repoFiles, repoGenerated), []);
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
    'nuxt.config.ts': 'export default defineNuxtConfig({});',
    'app/domains/low/domain.json': manifest('low', []),
    'app/domains/low/README.md': '# low',
    'app/domains/low/index.ts': "export { rule } from './rule.ts';",
    'app/domains/low/rule.ts': 'export function rule(): number { return 1; }',
    'app/domains/low/ui/index.ts': "export { useLow } from './useLow.ts';",
    'app/domains/low/ui/useLow.ts': "import { rule } from '../rule.ts';\nexport const useLow = () => computed(() => rule());",
    'app/domains/low/ui/LowBadge.vue': '<template><b>low</b></template>',
    'app/domains/low/views/LowPage.vue': '<script setup lang="ts">\nimport LowBadge from \'../ui/LowBadge.vue\';\nimport { useLow } from \'../ui/useLow.ts\';\nuseLow();\n</script>\n<template><LowBadge /></template>',
    'app/domains/high/domain.json': manifest('high', ['low']),
    'app/domains/high/README.md': '# high',
    'app/domains/high/index.ts': "export { use } from './use.ts';",
    'app/domains/high/use.ts': "import { rule } from '~/domains/low';\nexport const use = () => rule() + 1;",
    'app/domains/high/views/HighPage.vue': '<script setup lang="ts">\nimport { useLow } from \'~/domains/low/ui\';\nimport { use } from \'../use.ts\';\nuseLow();\nuse();\n</script>',
    'app/app.vue': '<template><NuxtLayout><NuxtPage /></NuxtLayout></template>',
    'app/error.vue': '<script setup lang="ts">\nimport LowPage from \'~/domains/low/views/LowPage.vue\';\n</script>',
    'app/pages/index.vue': '<script setup lang="ts">\nimport HighPage from \'~/domains/high/views/HighPage.vue\';\n</script>\n<template><HighPage /></template>',
    'app/layouts/default.vue': '<template><slot /></template>',
    'server/routes/low.txt.ts': "import { rule } from '~~/app/domains/low';\nexport default defineEventHandler(() => String(rule()));",
    'tools/job.mjs': "import { use } from '../app/domains/high/index.ts';\nuse();",
    'tests/low.test.mjs': "import { rule } from '../app/domains/low/index.ts';\nrule();",
  }));
}

function cleanGenerated() {
  return new Map(GENERATED.map((path) => [path, [
    "export { computed, ref } from 'vue';",
    "  const computed: typeof import('vue').computed",
    "  const useHead: typeof import('../../node_modules/nuxt/dist/head/runtime/composables').useHead",
    "  const defineEventHandler: typeof import('../../node_modules/h3').defineEventHandler",
  ].join('\n')]));
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
    ['a domain deep-imports another domain', { 'app/domains/high/deep.ts': "import { rule } from '~/domains/low/rule.ts';\nexport const d = rule;" }, ['public-entry']],
    ['a deep import goes through a relative path', { 'app/domains/high/deep.ts': "import { rule } from '../low/rule.ts';\nexport const d = rule;" }, ['public-entry']],
    ["a domain deep-imports another domain's UI", { 'app/domains/high/views/Deep.vue': '<script setup lang="ts">\nimport LowBadge from \'~/domains/low/ui/LowBadge.vue\';\n</script>' }, ['public-entry']],
    ['a page deep-imports a domain', { 'app/pages/deep.vue': '<script setup lang="ts">\nimport { rule } from \'~/domains/low/rule.ts\';\n</script>' }, ['public-entry']],
    ['a tool deep-imports a domain', { 'tools/deep.mjs': "import { rule } from '../app/domains/low/rule.ts';\nrule();" }, ['public-entry']],
    ['a component loads another domain\'s internals as its script', { 'app/domains/high/views/Src.vue': '<script setup lang="ts" src="../../low/rule.ts"></script>' }, ['public-entry']],
    ["a domain renders another domain's view", { 'app/domains/high/views/Both.vue': '<script setup lang="ts">\nimport LowPage from \'~/domains/low/views/LowPage.vue\';\n</script>' }, ['public-entry']],
    ['a domain imports one its domain.json does not allow', { 'app/domains/low/up.ts': "import { use } from '~/domains/high';\nexport const u = use;" }, ['domain-dependency']],
    ["a domain's view imports one its domain.json does not allow", { 'app/domains/low/views/Up.vue': '<script setup lang="ts">\nimport { use } from \'~/domains/high\';\n</script>' }, ['domain-dependency']],
    ['a domain reaches up through a folder path', { 'app/domains/low/up.ts': "import { use } from '~/domains/high/';\nexport const u = use;" }, ['domain-dependency']],
    ["a domain's plain part imports Vue", { 'app/domains/low/state.ts': "import { ref } from 'vue';\nexport const n = ref(0);" }, ['plain']],
    ["a domain's plain part imports Nuxt", { 'app/domains/low/app.ts': "import { useNuxtApp } from '#app';\nexport const a = useNuxtApp;" }, ['plain']],
    ["a domain's plain part uses an auto-imported Nuxt API", { 'app/domains/low/head.ts': 'export const setTitle = (title: string) => useHead({ title });' }, ['plain']],
    ["a domain's plain part imports its own UI", { 'app/domains/low/ui-back.ts': "import { useLow } from './ui/useLow.ts';\nexport const u = useLow;" }, ['plain']],
    ["a domain's plain entry exports a component", { 'app/domains/low/index.ts': "export { rule } from './rule.ts';\nexport { default as LowPage } from './views/LowPage.vue';" }, ['plain']],
    ["the server imports a domain's UI entry", { 'server/routes/ui.ts': "import { useLow } from '~~/app/domains/low/ui';\nexport default defineEventHandler(() => useLow());" }, ['plain']],
    ["a tool imports a domain's UI entry", { 'tools/ui.mjs': "import { useLow } from '../app/domains/low/ui/index.ts';\nuseLow();" }, ['plain']],
    ['a domain imports its own entry', { 'app/domains/low/self.ts': "import { rule } from '~/domains/low';\nexport const s = rule;" }, ['own-entry']],
    ["a domain's UI imports its own UI entry through '../'", { 'app/domains/low/ui/Self.vue': '<script setup lang="ts">\nimport { useLow } from \'../ui\';\n</script>' }, ['own-entry']],
    ['an import names no file', { 'app/domains/low/lost.ts': "import { gone } from './missing.ts';\nexport const g = gone;" }, ['resolve']],
    ['an import computes its path', { 'app/pages/any.vue': '<script setup lang="ts">\nconst name = \'low\';\nconst mod = import(`~/domains/${name}`);\n</script>' }, ['resolve']],
    ['code imports by pattern', { 'app/pages/glob.vue': '<script setup lang="ts">\nconst all = import.meta.glob(\'~/domains/*/index.ts\');\n</script>' }, ['resolve']],
    ['a composable sits in a folder Nuxt auto-imports', { 'app/composables/useThing.ts': 'export const useThing = () => 1;' }, ['layout']],
    ['a component sits in a folder Nuxt auto-registers', { 'app/components/Thing.vue': '<template><b /></template>' }, ['layout']],
    ['code sits in shared/', { 'shared/utils/thing.ts': 'export const thing = 1;' }, ['layout']],
    ['code sits in server/utils', { 'server/utils/thing.ts': 'export const thing = 1;' }, ['layout']],
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

  test("fails when Nuxt's generated lists are missing", () => {
    const generated = cleanGenerated();
    generated.delete('.nuxt/imports.d.ts');
    assert.deepEqual(rulesBroken({}, generated), ['auto-import']);
  });
});
