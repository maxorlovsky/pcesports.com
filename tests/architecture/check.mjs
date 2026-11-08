// The architecture rules of docs/architecture.md, checked against the real imports of every source file, the domain
// manifests (app/domains/<domain>/domain.json) and the auto-import lists Nuxt generates in .nuxt/.
// tests/architecture.test.mjs runs it on the repository, and on a small fixture per rule to prove the rule still bites.
//
// A rule fails closed: an import it can't resolve, a file in a folder it doesn't know, or an import it can't read
// (a computed path, import.meta.glob) is a violation, never a pass.
import { posix } from 'node:path';
import ts from 'typescript';

/** Source files: what the checks read. Anything else (CSS, images, JSON, Markdown) is data. */
export const SOURCE = /\.(vue|ts|mts|tsx|js|mjs|cjs|jsx)$/;

/** Folders whose files the rules place; a source file under them outside a known place breaks `layout`. */
const PLACED_ROOTS = ['app/', 'server/', 'shared/', 'layers/'];

/** What the plain part of a domain may not import: Vue, Nuxt and their virtual modules. */
const FRAMEWORK = /^(vue|vue-router|nuxt|h3|nitropack|@vue\/.*|@unhead\/.*|@nuxt\/.*|#app|#app\/.*|#imports|#components|#build\/.*|#internal\/.*)$/;

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
  if (path.startsWith('server/routes/')) return { kind: 'server' };
  if (path === 'nuxt.config.ts') return { kind: 'config' };
  if (path.startsWith('tests/')) return { kind: 'tests' };
  if (path.startsWith('tools/')) return { kind: 'tools' };
  return { kind: 'other' };
}

const isUiPart = (area) => area.kind === 'domain' && ['ui-entry', 'ui', 'view'].includes(area.part);
const isPlainPart = (area) => area.kind === 'domain' && ['entry', 'plain'].includes(area.part);

/** The script blocks of a single-file component, where its imports are. */
function scriptOf(path, text) {
  if (!path.endsWith('.vue')) return text;
  return [...text.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)].map((m) => m[1]).join('\n');
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
  else return `pkg:${spec}`;
  // `~/domains/x/` and `../` name a folder as surely as `~/domains/x` does.
  base = base.replace(/\/+$/, '');
  for (const extension of EXTENSIONS) if (files.has(`${base}${extension}`)) return `${base}${extension}`;
  return base;
}

/** Styles, images and fonts: imported by path, with no source file to resolve to. */
const ASSET = /\.(css|png|jpe?g|webp|svg|gif|ico|woff2?)$/;

/** Every static import, re-export, dynamic import, `import('…')` type and `<script src>` of a file. */
function importsOf(path, text, files) {
  const found = [];
  const add = (spec) => found.push({ spec, target: resolve(path, spec, files) });
  const opaque = (why) => found.push({ spec: why, target: 'opaque', opaque: why });
  const source = ts.createSourceFile(path, scriptOf(path, text), ts.ScriptTarget.Latest, true, ts.ScriptKind.TS);
  const visit = (node) => {
    if ((ts.isImportDeclaration(node) || ts.isExportDeclaration(node)) && node.moduleSpecifier && ts.isStringLiteralLike(node.moduleSpecifier)) {
      add(node.moduleSpecifier.text);
    } else if (ts.isCallExpression(node) && node.expression.kind === ts.SyntaxKind.ImportKeyword) {
      const [first] = node.arguments;
      if (first && ts.isStringLiteralLike(first)) add(first.text);
      else opaque('a dynamic import of a computed path');
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
  if (path.endsWith('.vue')) for (const m of text.matchAll(/<script\b[^>]*\bsrc\s*=\s*["']([^"']+)["']/g)) add(m[1]);
  return found;
}

/**
 * The names a file uses without declaring or importing them: in a domain's plain part, one of those that Nuxt
 * auto-imports (useHead, computed, defineEventHandler) ties the code to Nuxt as surely as an import would.
 */
function freeNames(path, text) {
  const source = ts.createSourceFile(path, text, ts.ScriptTarget.Latest, true, ts.ScriptKind.TS);
  const declared = new Set();
  const used = new Set();
  const visit = (node) => {
    if (ts.isIdentifier(node)) {
      const parent = node.parent;
      const isDeclaration = parent && 'name' in parent && parent.name === node &&
        !ts.isPropertyAccessExpression(parent) && !ts.isQualifiedName(parent);
      const isMember = parent && ((ts.isPropertyAccessExpression(parent) && parent.name === node) ||
        (ts.isQualifiedName(parent) && parent.right === node) || (ts.isPropertyAssignment(parent) && parent.name === node) ||
        ((ts.isBindingElement(parent) || ts.isImportSpecifier(parent) || ts.isExportSpecifier(parent)) && parent.propertyName === node));
      if (isDeclaration) declared.add(node.text);
      else if (!isMember) used.add(node.text);
    }
    ts.forEachChild(node, visit);
  };
  visit(source);
  return [...used].filter((name) => !declared.has(name));
}

/** The files Nuxt writes its auto-import lists to (`nuxt prepare`, which `npm ci` runs). */
export const GENERATED = [
  '.nuxt/components.d.ts',
  '.nuxt/imports.d.ts',
  '.nuxt/types/components.d.ts',
  '.nuxt/types/imports.d.ts',
  '.nuxt/types/nitro-imports.d.ts',
  '.nuxt/types/shared-imports.d.ts',
];

/** The names Nuxt auto-imports anywhere, read from its generated declarations: `const useHead: typeof import(…)`. */
function autoImportedNames(generated) {
  const names = new Set();
  for (const text of generated.values()) for (const m of text.matchAll(/^\s*const (\w+): typeof import\(/gm)) names.add(m[1]);
  return names;
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

/**
 * Every rule broken in `files` (repository-relative path -> text: every file under app/, server/, shared/, layers/,
 * tests/ and tools/, plus nuxt.config.ts), given `generated` (the GENERATED paths -> their text).
 */
export function checkArchitecture(files, generated) {
  const violations = [];
  const paths = new Set(files.keys());
  const domains = [...new Set([...paths].map((p) => p.match(/^app\/domains\/([^/]+)\//)?.[1]).filter(Boolean))].sort();
  const manifests = checkManifests(files, domains, violations);

  // Project code is never auto-imported: Nuxt's generated lists name only packages.
  for (const path of GENERATED) {
    const text = generated.get(path);
    if (text === undefined) {
      violations.push({ rule: 'auto-import', file: path, detail: 'missing: run `npx nuxt prepare` (npm ci does)' });
      continue;
    }
    for (const m of text.matchAll(/(?:import\(|from\s)\s*["']([^"']+)["']/g)) {
      const spec = m[1];
      // Relative paths point into .nuxt itself (`../fetch`) or node_modules; anything else is the project's own.
      const at = spec.startsWith('.') ? posix.normalize(posix.join(posix.dirname(path), spec)) : spec;
      if ((spec.startsWith('.') || spec.startsWith('/')) && !at.includes('node_modules/') && !at.startsWith('.nuxt/')) {
        violations.push({ rule: 'auto-import', file: path, detail: `auto-imports project code: ${spec}` });
      }
    }
  }
  const autoNames = autoImportedNames(generated);

  for (const [path, text] of files) {
    if (!SOURCE.test(path)) continue;
    const area = areaOf(path);
    if (PLACED_ROOTS.some((root) => path.startsWith(root)) && (area.kind === 'other' || area.part === 'misplaced')) {
      violations.push({ rule: 'layout', file: path, detail: 'a source file outside the known places (docs/architecture.md, "Where code goes")' });
    }
    if (area.kind === 'domain' && area.part === 'plain' && !/\.(ts|mts)$/.test(path)) {
      violations.push({ rule: 'layout', file: path, detail: "a domain's plain part is TypeScript (.ts)" });
    }
    if (isPlainPart(area)) {
      for (const name of freeNames(path, text)) {
        if (autoNames.has(name)) violations.push({ rule: 'plain', file: path, detail: `uses ${name}, which only Nuxt's auto-imports provide` });
      }
    }

    for (const imp of importsOf(path, text, paths)) {
      const { spec, target } = imp;
      // Node scripts (tests, tools) may compute a path, e.g. to load Playwright from its machine-wide install.
      if (imp.opaque && ['tests', 'tools'].includes(area.kind)) continue;
      if (imp.opaque) {
        violations.push({ rule: 'resolve', file: path, detail: `${imp.opaque}: write the path out` });
        continue;
      }
      if (!target.startsWith('pkg:') && !paths.has(target) && !ASSET.test(target)) {
        violations.push({ rule: 'resolve', file: path, detail: `${spec} names no file` });
        continue;
      }
      if (isPlainPart(area) && ((target.startsWith('pkg:') && FRAMEWORK.test(target.slice(4))) || target.endsWith('.vue'))) {
        violations.push({ rule: 'plain', file: path, detail: `imports ${spec}: a domain's plain part runs without Vue or Nuxt` });
      }
      const to = areaOf(target);
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
