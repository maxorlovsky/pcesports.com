import js from '@eslint/js';
import globals from 'globals';
import reactHooks from 'eslint-plugin-react-hooks';
import reactRefresh from 'eslint-plugin-react-refresh';
import tseslint from 'typescript-eslint';

export default tseslint.config(
  { ignores: ['dist'] },
  {
    extends: [js.configs.recommended, ...tseslint.configs.recommended],
    files: ['**/*.{ts,tsx}'],
    languageOptions: {
      ecmaVersion: 2022,
      globals: globals.browser,
      parserOptions: {
        ecmaFeatures: {
          jsx: true
        }
      }
    },
    plugins: {
      'react-hooks': reactHooks,
      'react-refresh': reactRefresh,
    },
    rules: {
      ...reactHooks.configs.recommended.rules,
      'react-refresh/only-export-components': [
        'warn',
        { allowConstantExport: true },
      ],
      'comma-dangle': 'off',
      'comma-spacing': [
        'error',
        {
          'after': true,
          'before': false
        }
      ],
      'comma-style': [
        'error',
        'last'
      ],
      indent: ['error', 2, {
        SwitchCase: 1
      }],
    },
    ignores: [
      '**/*{.,-}min.js',
      '**/dist/*',
      '.eslintrc.js'
    ]
  },
)