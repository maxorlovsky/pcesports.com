import js from '@eslint/js';
import globals from 'globals';
import pluginVue from 'eslint-plugin-vue';
import tseslint from 'typescript-eslint';

export default tseslint.config(
  { ignores: ['.output/**', '.nuxt/**', 'node_modules/**', 'experiments/**', 'frontend-react/**', 'old-vuejs/**', 'site/**', '.releases/**'] },
  js.configs.recommended,
  ...tseslint.configs.recommended,
  ...pluginVue.configs['flat/recommended'],
  {
    files: ['**/*.vue'],
    languageOptions: { parserOptions: { parser: tseslint.parser } },
  },
  {
    languageOptions: {
      ecmaVersion: 2024,
      sourceType: 'module',
      globals: { ...globals.browser, ...globals.node },
    },
    rules: {
      // Nuxt auto-imports (usePageSeo, SITE_URL…) are checked by `npm run typecheck` instead
      'no-undef': 'off',
      // Nuxt's file-based routes and layouts are single words (index, privacy, default, error)
      'vue/multi-word-component-names': 'off',
      'vue/max-attributes-per-line': 'off',
      'vue/singleline-html-element-content-newline': 'off',
      'vue/html-self-closing': ['error', { html: { void: 'never', normal: 'always', component: 'always' } }],
      'comma-spacing': ['error', { after: true, before: false }],
      'comma-style': ['error', 'last'],
      indent: ['error', 2, { SwitchCase: 1 }],
      quotes: ['error', 'single', { avoidEscape: true }],
      semi: ['error', 'always'],
    },
  },
);
