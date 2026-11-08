// The architecture rules of docs/architecture.md, checked against the real imports of every source and style file, the
// domain manifests (app/domains/<domain>/domain.json) and what Nuxt generates in .nuxt/.
// tests/architecture.test.mjs runs it on the repository, and on a small fixture per rule to prove the rule still bites.
//
// A rule fails closed: an import it can't resolve, a package package.json doesn't list, a file in a folder it doesn't
// know, or an import it can't read (a computed path, import.meta.glob) is a violation, never a pass.
import { builtinModules } from 'node:module';
import { posix } from 'node:path';
import ts from 'typescript';
import { compileTemplate, parse as parseSfc } from 'vue/compiler-sfc';

/** Source files: what the checks place and read. Styles are read for their imports; everything else is data. */
export const SOURCE = /\.(vue|ts|mts|cts|tsx|js|mjs|cjs|jsx)$/;
const STYLE = /\.css$/;

/** Folders Nuxt reads code from by itself; a source file under them outside a known place breaks `layout`. */
const PLACED_ROOTS = ['app/', 'server/', 'shared/', 'layers/', 'modules/'];

/** Nuxt's virtual modules that app code may import. */
const NUXT_VIRTUAL = /^(#app|#app\/.+|#imports|#components)$/;

/** Packages a domain's plain part may import: each must run in Node without Vue or Nuxt. None so far. */
const PLAIN_PACKAGES = new Set();

/** Globals Nuxt and Nitro declare outside the auto-import lists (.nuxt/fetch.d.ts). Node has none of them. */
const NUXT_GLOBALS = new Set(['$fetch']);

/** The `import.meta` fields Node itself provides; Vite's and Nuxt's (`env`, `client`, `server`, `dev`…) it lacks. */
const NODE_IMPORT_META = new Set(['url', 'dirname', 'filename', 'resolve']);

/** Node scripts that load a module by a computed path, each for a stated reason. */
const COMPUTED_IMPORTS = new Map([
  ['tools/og-image.mjs', "Playwright, from the factory machine's own install"],
  ['tests/architecture.test.mjs', "each domain's plain entry, to prove Node loads it"],
]);

/** Where a file sits. Paths are repository-relative with forward slashes. */
export function areaOf(path) {
  const domain = path.match(/^app\/domains\/([^/]+)\/(.+)$/);
  if (domain) {
    const [, name, rest] = domain;
    if (rest === 'index.ts') return { kind: 'domain', domain: name, part: 'entry' };
    if (rest === 'ui/index.ts') return { kind: 'domain', domain: name, part: 'ui-entry' };
    if (/^views\/[^/]+\.vue$/.test(rest)) return { kind: 'domain', domain: name, part: 'view' };
    if (rest.startsWith('views/')) return { kind: 'domain', domain: name, part: 'misplaced' };
    if (rest.startsWith('ui/')) return { kind: 'domain', domain: name, part: 'ui' };
    if (rest.endsWith('.vue')) return { kind: 'domain', domain: name, part: 'misplaced' };
    return { kind: 'domain', domain: name, part: 'plain' };
  }
  if (path === 'app/app.vue' || path === 'app/error.vue' || /^app\/(pages|layouts)\//.test(path)) return { kind: 'shell' };
  if (path.startsWith('app/assets/')) return { kind: 'styles' };
  if (path.startsWith('server/routes/')) return { kind: 'server' };
  if (path === 'nuxt.config.ts') return { kind: 'config' };
  if (PLACED_ROOTS.some((root) => path.startsWith(root))) return { kind: 'unplaced' };
  // Tests, tools, configs and any other script: Node runs them, outside the app.
  return { kind: 'node' };
}

/**
 * Which project files each area may import, outside the domains (whose own rules follow). A domain reaches nothing but
 * domains, so no file elsewhere can carry it up the stack or bring Vue into its plain part; the app reaches no Node
 * script; Node scripts reach no app code but the domains' plain entries.
 */
const REACH = {
  domain: ['domain'],
  shell: ['domain', 'styles'],
  server: ['domain'],
  styles: ['styles'],
  config: ['node'],
  node: ['node', 'domain'],
  unplaced: [],
};

const isUiPart = (area) => area.kind === 'domain' && ['ui-entry', 'ui', 'view'].includes(area.part);
const isPlainPart = (area) => area.kind === 'domain' && ['entry', 'plain'].includes(area.part);

/** A single-file component's blocks, as Vue's own parser reads them (an attribute holding `>`, a block in a comment). */
function sfcOf(path, text) {
  const { descriptor, errors } = parseSfc(text, { filename: path });
  return { descriptor, failed: errors.length ? `the component does not parse: ${errors[0].message ?? errors[0]}` : undefined };
}

/** The script a file holds: itself, or a component's script blocks. */
function scriptOf(path, text, sfc) {
  if (!sfc) return text;
  return [sfc.descriptor.script, sfc.descriptor.scriptSetup].filter(Boolean).map((block) => block.content).join('\n');
}

/** The style a file holds: a stylesheet, or a component's style blocks. */
function styleOf(path, text, sfc) {
  if (STYLE.test(path)) return text;
  return sfc ? sfc.descriptor.styles.map((block) => block.content).join('\n') : '';
}

const EXTENSIONS = ['', '.ts', '.vue', '.mjs', '.js', '/index.ts', '/index.mjs', '/index.js'];

/** The file an import names, `pkg:<name>` for a package or a virtual module, or the bare path when no file matches. */
export function resolve(from, spec, files) {
  let base;
  if (/^[~@]\//.test(spec)) base = `app/${spec.slice(2)}`;
  else if (/^(~~|@@)\//.test(spec)) base = spec.slice(3);
  else if (spec === '~' || spec === '@') base = 'app';
  else if (spec.startsWith('#shared/')) base = `shared/${spec.slice(8)}`;
  else if (spec.startsWith('.')) base = posix.normalize(posix.join(posix.dirname(from), spec));
  else if (spec.startsWith('/')) return `absolute:${spec}`;
  else return `pkg:${spec}`;
  // `~/domains/x/` and `../` name a folder as surely as `~/domains/x` does.
  base = base.replace(/\/+$/, '');
  for (const extension of EXTENSIONS) if (files.has(`${base}${extension}`)) return `${base}${extension}`;
  return base;
}

/** Images and fonts: imported by path, and not read here. Styles and JSON are, so they must exist. */
const ASSET = /\.(png|jpe?g|webp|avif|svg|gif|ico|woff2?|mp4|webm|mp3|ogg)$/;

/**
 * The asset URLs a component's template turns into imports (`<img src>`, `srcset`, `<video poster>`, `<source src>`,
 * `<use href>`…), read from Vue's own compiler so the list is Vue's. Vue drops the `~/` of `~/x`; `@/x` is the same
 * path in Nuxt, and it keeps it.
 */
function templateAssets(path, { descriptor }) {
  const template = descriptor.template;
  if (!template || template.src) return { specs: [] }; // a <template src> is opaque (importsOf)
  const source = template.content.replace(/(=\s*["'])~~\//g, '$1@@/').replace(/(=\s*["'])~\//g, '$1@/');
  const compiled = compileTemplate({ source, filename: path, id: 'check' });
  if (compiled.errors.length) return { failed: `the template does not compile: ${compiled.errors[0]}` };
  return { specs: [...compiled.code.matchAll(/^import _imports_\d+ from ['"]([^'"]+)['"]/gm)].map((m) => m[1]) };
}

/** `@scope/name/sub` -> `@scope/name`, `name/sub` -> `name`. */
const packageName = (spec) => spec.split('/').slice(0, spec.startsWith('@') ? 2 : 1).join('/');

/** Every import a file makes: ES imports and re-exports, `import()`, `require()` (and `module.require()`),
 *  `new URL('…', import.meta.url)`, `import('…')` types, a component's `<script src>`, `<style src>` and
 *  the asset URLs of its template, and `@import` and `url()` in its styles. A `<template src>` is opaque. A `require` or `createRequire` passed around is opaque. */
function importsOf(path, text, files) {
  const found = [];
  const add = (spec) => found.push({ spec, target: resolve(path, spec, files) });
  const opaque = (why) => found.push({ spec: why, target: 'opaque', opaque: why });
  const literalCall = (node, isCallee) => {
    if (!isCallee) return false;
    const [first] = node.arguments;
    if (first && ts.isStringLiteralLike(first)) add(first.text);
    else opaque(`a computed ${node.expression.getText()}() path`);
    return true;
  };
  const sfc = path.endsWith('.vue') ? sfcOf(path, text) : undefined;
  if (sfc?.failed) {
    opaque(sfc.failed);
    return found;
  }
  if (SOURCE.test(path)) {
    const source = ts.createSourceFile(path, scriptOf(path, text, sfc), ts.ScriptTarget.Latest, true, ts.ScriptKind.TS);
    const visit = (node) => {
      if ((ts.isImportDeclaration(node) || ts.isExportDeclaration(node)) && node.moduleSpecifier && ts.isStringLiteralLike(node.moduleSpecifier)) {
        add(node.moduleSpecifier.text);
      } else if (ts.isCallExpression(node) && literalCall(node, node.expression.kind === ts.SyntaxKind.ImportKeyword ||
          (ts.isIdentifier(node.expression) && node.expression.text === 'require') ||
          (ts.isPropertyAccessExpression(node.expression) && node.expression.name.text === 'require' &&
            ts.isIdentifier(node.expression.expression) && node.expression.expression.text === 'module'))) {
        // handled
      } else if (ts.isIdentifier(node) && (node.text === 'createRequire' ||
          (node.text === 'require' && !(ts.isCallExpression(node.parent) && node.parent.expression === node) &&
            !(ts.isPropertyAccessExpression(node.parent) && node.parent.name === node)))) {
        // A require() under another name loads what it is given; read it as a computed import.
        opaque(`${node.text} passed around`);
      } else if (ts.isNewExpression(node) && ts.isIdentifier(node.expression) && node.expression.text === 'URL' &&
          node.arguments?.length === 2 && ts.isStringLiteralLike(node.arguments[0]) && /import\.meta\.url/.test(node.arguments[1].getText())) {
        add(node.arguments[0].text);
      } else if (ts.isPropertyAccessExpression(node) && ts.isMetaProperty(node.expression) &&
          node.expression.keywordToken === ts.SyntaxKind.ImportKeyword && node.name.text.startsWith('glob')) {
        opaque('import.meta.glob');
      } else if (ts.isImportTypeNode(node) && ts.isLiteralTypeNode(node.argument) && ts.isStringLiteralLike(node.argument.literal)) {
        add(node.argument.literal.text);
      } else if (ts.isImportEqualsDeclaration(node) && ts.isExternalModuleReference(node.moduleReference) &&
          ts.isStringLiteralLike(node.moduleReference.expression)) {
        add(node.moduleReference.expression.text);
      }
      ts.forEachChild(node, visit);
    };
    visit(source);
  }
  if (sfc) {
    const { script, scriptSetup, styles, template } = sfc.descriptor;
    for (const block of [script, scriptSetup, ...styles]) if (block?.src) add(block.src);
    // An external template's own asset URLs would go unread: the template stays in the component.
    if (template?.src) opaque('a <template src>, whose asset URLs the check cannot read');
    const assets = templateAssets(path, sfc);
    if (assets.failed) opaque(assets.failed);
    else assets.specs.forEach(add);
  }
  const style = styleOf(path, text, sfc).replace(/\/\*[\s\S]*?\*\//g, '');
  for (const m of style.matchAll(/@import\s+["']([^"']+)["']/g)) add(m[1]);
  // url(): every file a style reaches, but public/'s own (`/logo.png`), data: and remote ones. A bare name is relative.
  for (const m of style.matchAll(/url\(\s*["']?([^"')\s]+)/g)) {
    const spec = m[1].replace(/[?#].*$/, '');
    if (spec && !/^(\/|data:|https?:)/.test(m[1]) && !m[1].startsWith('#')) add(/^[.~@]/.test(spec) ? spec : `./${spec}`);
  }
  return found;
}

/**
 * The names a file uses that resolve to nothing in it (no declaration or import in scope), by TypeScript's own scope
 * rules. In a domain's plain part, one of those that Nuxt auto-imports ties the code to Nuxt as surely as an import.
 */
function unresolvedNames(path, text) {
  const fileName = `/check/${path}`;
  const source = ts.createSourceFile(fileName, text, ts.ScriptTarget.Latest, true, ts.ScriptKind.TS);
  const host = ts.createCompilerHost({});
  host.getSourceFile = (name) => (name === fileName ? source : undefined);
  host.fileExists = (name) => name === fileName;
  host.readFile = (name) => (name === fileName ? text : undefined);
  const checker = ts.createProgram([fileName], { noLib: true, noResolve: true, types: [] }, host).getTypeChecker();
  const names = new Set();
  const visit = (node) => {
    // import.meta only as a dot access to a field Node has: `const { env } = import.meta` reaches Vite's too.
    if (ts.isMetaProperty(node) && node.keywordToken === ts.SyntaxKind.ImportKeyword &&
        !(ts.isPropertyAccessExpression(node.parent) && NODE_IMPORT_META.has(node.parent.name.text))) {
      names.add(ts.isPropertyAccessExpression(node.parent) ? `import.meta.${node.parent.name.text}` : 'import.meta');
    }
    // `globalThis.$fetch` is the same global.
    if (ts.isPropertyAccessExpression(node) && ts.isIdentifier(node.expression) && ['globalThis', 'window', 'self'].includes(node.expression.text) &&
        NUXT_GLOBALS.has(node.name.text)) {
      names.add(node.name.text);
    }
    if (ts.isIdentifier(node)) {
      const parent = node.parent;
      // Member names (`a.ref`, `{ ref: 1 }`, `interface { ref }`) name no variable.
      const isMember = (ts.isPropertyAccessExpression(parent) && parent.name === node) || (ts.isQualifiedName(parent) && parent.right === node) ||
        ((ts.isPropertyAssignment(parent) || ts.isPropertySignature(parent) || ts.isPropertyDeclaration(parent) ||
          ts.isMethodDeclaration(parent) || ts.isMethodSignature(parent) || ts.isGetAccessor(parent) || ts.isSetAccessor(parent) ||
          ts.isEnumMember(parent)) && parent.name === node) ||
        ((ts.isBindingElement(parent) || ts.isImportSpecifier(parent) || ts.isExportSpecifier(parent)) && parent.propertyName === node);
      const symbol = ts.isShorthandPropertyAssignment(parent) && parent.name === node
        ? checker.getShorthandAssignmentValueSymbol(parent)
        : checker.getSymbolAtLocation(node);
      if (!isMember && !symbol) names.add(node.text);
    }
    ts.forEachChild(node, visit);
  };
  visit(source);
  return names;
}

/** What Nuxt generates (`nuxt prepare`, which `npm ci` runs): its auto-import lists, and the app's tsconfig. */
export const GENERATED = [
  '.nuxt/components.d.ts',
  '.nuxt/imports.d.ts',
  '.nuxt/types/components.d.ts',
  '.nuxt/types/imports.d.ts',
  '.nuxt/types/nitro-imports.d.ts',
  '.nuxt/types/shared-imports.d.ts',
];
export const GENERATED_TSCONFIG = '.nuxt/tsconfig.app.json';

/** The names Nuxt auto-imports anywhere, read from its generated declarations: `const useHead: typeof import(…)`. */
function autoImportedNames(generated) {
  const names = new Set();
  for (const path of GENERATED) for (const m of (generated.get(path) ?? '').matchAll(/^\s*const (\w+): typeof import\(/gm)) names.add(m[1]);
  return names;
}

function checkGenerated(generated, violations) {
  for (const path of [...GENERATED, GENERATED_TSCONFIG]) {
    if (!generated.has(path)) violations.push({ rule: 'auto-import', file: path, detail: 'missing: run `npx nuxt prepare` (npm ci does)' });
  }
  // Project code is never auto-imported: Nuxt's generated lists name only packages and .nuxt itself.
  for (const path of GENERATED) {
    for (const m of (generated.get(path) ?? '').matchAll(/(?:import\(|from\s)\s*["']([^"']+)["']/g)) {
      const spec = m[1];
      const at = spec.startsWith('.') ? posix.normalize(posix.join(posix.dirname(path), spec)) : spec;
      if ((spec.startsWith('.') || spec.startsWith('/')) && !at.includes('node_modules/') && !at.startsWith('.nuxt/')) {
        violations.push({ rule: 'auto-import', file: path, detail: `auto-imports project code: ${spec}` });
      }
    }
  }
  // Without auto-registered components, a component used without an import must fail `npm run typecheck`.
  const tsconfig = generated.get(GENERATED_TSCONFIG);
  if (tsconfig !== undefined) {
    let options;
    try {
      options = JSON.parse(tsconfig).vueCompilerOptions;
    } catch {
      options = undefined;
    }
    if (options?.checkUnknownComponents !== true) {
      violations.push({ rule: 'auto-import', file: GENERATED_TSCONFIG, detail: "vueCompilerOptions.checkUnknownComponents is off (nuxt.config.ts's typescript.tsConfig)" });
    }
  }
}

function checkManifests(files, domains, violations) {
  const manifests = new Map();
  for (const domain of domains) {
    const at = `app/domains/${domain}`;
    for (const needed of ['domain.json', 'README.md', 'index.ts']) {
      if (!files.has(`${at}/${needed}`)) violations.push({ rule: 'manifest', file: at, detail: `no ${needed}` });
    }
    let manifest;
    try {
      manifest = JSON.parse(files.get(`${at}/domain.json`) ?? 'null');
    } catch (error) {
      violations.push({ rule: 'manifest', file: `${at}/domain.json`, detail: `not JSON: ${error.message}` });
    }
    if (!manifest) continue;
    const deps = manifest.allowedDomainDependencies;
    if (manifest.name !== domain) violations.push({ rule: 'manifest', file: `${at}/domain.json`, detail: `name "${manifest.name}" is not its folder's` });
    if (!Array.isArray(manifest.owns) || manifest.owns.length === 0) violations.push({ rule: 'manifest', file: `${at}/domain.json`, detail: 'no `owns` list' });
    if (!Array.isArray(deps)) {
      violations.push({ rule: 'manifest', file: `${at}/domain.json`, detail: 'no `allowedDomainDependencies` list' });
      continue;
    }
    for (const dep of deps) {
      if (dep === domain || !domains.includes(dep)) violations.push({ rule: 'manifest', file: `${at}/domain.json`, detail: `allows "${dep}", which is ${dep === domain ? 'itself' : 'no domain'}` });
    }
    manifests.set(domain, new Set(deps));
  }
  // Domains depend on each other in one direction only.
  const visiting = new Set();
  const done = new Set();
  const walk = (domain, trail) => {
    if (done.has(domain)) return;
    if (visiting.has(domain)) {
      violations.push({ rule: 'manifest', file: `app/domains/${domain}/domain.json`, detail: `dependency cycle: ${[...trail, domain].join(' -> ')}` });
      return;
    }
    visiting.add(domain);
    for (const dep of manifests.get(domain) ?? []) if (manifests.has(dep)) walk(dep, [...trail, domain]);
    visiting.delete(domain);
    done.add(domain);
  };
  for (const domain of manifests.keys()) walk(domain, []);
  return manifests;
}

/** A package import's problem, or undefined: it must be a Node built-in, a Nuxt virtual module or in package.json. */
function packageProblem(spec, packages, area) {
  if (spec.startsWith('node:') || builtinModules.includes(spec)) return undefined;
  if (spec.startsWith('#')) return NUXT_VIRTUAL.test(spec) && area.kind !== 'node' ? undefined : `${spec} is no Nuxt module the app may import`;
  return packages.has(packageName(spec)) ? undefined : `${spec} is in no package.json dependency`;
}

/** A plain-part import's problem, or undefined: Node runs this code as it is, so no Vue, Nuxt, aliases or bare paths. */
function plainProblem(spec, target) {
  if (spec.startsWith('node:') || builtinModules.includes(spec)) return undefined;
  if (target.startsWith('pkg:')) return PLAIN_PACKAGES.has(packageName(spec)) ? undefined : `${spec}: a plain part imports no package that is not in PLAIN_PACKAGES (Node runs it without Vue or Nuxt)`;
  if (!spec.startsWith('.')) return `${spec}: a plain part imports by relative path (Node knows no ~, @ or #)`;
  if (target.endsWith('.vue')) return `${spec}: a plain part imports no component`;
  if (!/\.(ts|json)$/.test(spec)) return `${spec}: a plain part names the file with its extension, .ts or .json (Node resolves nothing else)`;
  return undefined;
}

/**
 * Every rule broken in `files` (repository-relative path -> text: every source, style, HTML and JSON file of the
 * repository but package-lock.json, and each domain's README.md), given `generated` (GENERATED and
 * GENERATED_TSCONFIG -> their text).
 */
export function checkArchitecture(files, generated) {
  const violations = [];
  const paths = new Set(files.keys());
  const domains = [...new Set([...paths].map((p) => p.match(/^app\/domains\/([^/]+)\//)?.[1]).filter(Boolean))].sort();
  const manifests = checkManifests(files, domains, violations);
  checkGenerated(generated, violations);
  const autoNames = autoImportedNames(generated);
  let packages = new Set();
  try {
    const manifest = JSON.parse(files.get('package.json') ?? '');
    packages = new Set([...Object.keys(manifest.dependencies ?? {}), ...Object.keys(manifest.devDependencies ?? {})]);
  } catch {
    violations.push({ rule: 'resolve', file: 'package.json', detail: 'missing or not JSON: no package import can be checked' });
  }

  for (const [path, text] of files) {
    if (!SOURCE.test(path) && !STYLE.test(path)) continue;
    const area = areaOf(path);
    if (SOURCE.test(path) && (area.kind === 'unplaced' || area.kind === 'styles' || area.part === 'misplaced')) {
      violations.push({ rule: 'layout', file: path, detail: 'a source file outside the known places (docs/architecture.md, "Where code goes")' });
    }
    if (area.kind === 'domain' && area.part === 'plain' && SOURCE.test(path) && !/\.(ts|mts)$/.test(path)) {
      violations.push({ rule: 'layout', file: path, detail: "a domain's plain part is TypeScript (.ts)" });
    }
    if (isPlainPart(area) && SOURCE.test(path)) {
      for (const name of unresolvedNames(path, text)) {
        if (autoNames.has(name) || NUXT_GLOBALS.has(name) || name.startsWith('import.meta')) {
          violations.push({ rule: 'plain', file: path, detail: `uses ${name}, which only Nuxt or Vite provide` });
        }
      }
    }

    for (const imp of importsOf(path, text, paths)) {
      const { spec, target } = imp;
      if (imp.opaque) {
        if (!COMPUTED_IMPORTS.has(path)) violations.push({ rule: 'resolve', file: path, detail: `${imp.opaque}: write the path out` });
        continue;
      }
      if (target.startsWith('absolute:')) {
        violations.push({ rule: 'resolve', file: path, detail: `${spec} is an absolute path: use ~/, ~~/ or a relative one` });
        continue;
      }
      if (target.startsWith('pkg:')) {
        const problem = packageProblem(spec, packages, area);
        if (problem) violations.push({ rule: 'resolve', file: path, detail: problem });
      } else if (!paths.has(target) && !ASSET.test(target)) {
        violations.push({ rule: 'resolve', file: path, detail: `${spec} names no file` });
        continue;
      }
      if (isPlainPart(area)) {
        const problem = plainProblem(spec, target);
        if (problem) violations.push({ rule: 'plain', file: path, detail: problem });
      }
      const to = areaOf(target);
      if (!target.startsWith('pkg:') && !(REACH[area.kind] ?? []).includes(to.kind)) {
        violations.push({ rule: 'reach', file: path, detail: `imports ${spec}: ${area.kind} code imports no ${to.kind} file (docs/architecture.md, "Where code goes")` });
      }
      if (to.kind !== 'domain') continue;

      if (area.kind === 'domain' && area.domain === to.domain) {
        if (to.part === 'entry' || to.part === 'ui-entry') {
          violations.push({ rule: 'own-entry', file: path, detail: `imports its own domain's entry (${spec}): import the file itself` });
        } else if (isPlainPart(area) && isUiPart(to)) {
          violations.push({ rule: 'plain', file: path, detail: `imports ${spec}: the plain part never reaches its UI` });
        }
        continue;
      }

      // Another domain, or code outside the domains.
      if (!['entry', 'ui-entry', 'view'].includes(to.part)) {
        violations.push({ rule: 'public-entry', file: path, detail: `deep import ${spec}: import app/domains/${to.domain}/index.ts or ui/index.ts` });
      } else if (to.part === 'view' && area.kind !== 'shell') {
        violations.push({ rule: 'public-entry', file: path, detail: `${spec} is a view, which only the shell (pages, layouts, error.vue) renders` });
      } else if (to.part !== 'entry' && !(isUiPart(area) || area.kind === 'shell')) {
        violations.push({ rule: 'plain', file: path, detail: `imports ${spec}: outside the app, import a domain's plain entry (index.ts)` });
      }
      if (area.kind === 'domain' && !manifests.get(area.domain)?.has(to.domain)) {
        violations.push({ rule: 'domain-dependency', file: path, detail: `imports ${to.domain}, which app/domains/${area.domain}/domain.json does not allow` });
      }
    }
  }
  return violations;
}
