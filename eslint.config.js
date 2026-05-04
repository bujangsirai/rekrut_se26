import fs from 'node:fs'
import path from 'node:path'
import { defineConfigWithVueTs, vueTsConfigs } from '@vue/eslint-config-typescript'
import prettier from 'eslint-config-prettier'
import boundaries from 'eslint-plugin-boundaries'
import importPlugin from 'eslint-plugin-import'
import vue from 'eslint-plugin-vue'

const modulesRoot = path.resolve('Modules')
const moduleNames = fs.existsSync(modulesRoot)
  ? fs
      .readdirSync(modulesRoot, { withFileTypes: true })
      .filter((entry) => entry.isDirectory())
      .map((entry) => entry.name)
  : []

const restrictedModules = moduleNames.filter(
  (moduleName) => moduleName !== 'Core' && moduleName !== 'Shared',
)

const crossModuleZones = restrictedModules.flatMap((fromModule) =>
  restrictedModules
    .filter((targetModule) => targetModule !== fromModule)
    .map((targetModule) => ({
      from: [`Modules/${fromModule}/resources/js/**/*`],
      target: `Modules/${targetModule}/resources/js/**/*`,
    })),
)

export default defineConfigWithVueTs(
  // Base
  vue.configs['flat/essential'],
  vueTsConfigs.recommended,

  // Ignores
  {
    ignores: [
      'vendor',
      'node_modules',
      'public',
      'bootstrap/ssr',
      'tailwind.config.js',
      'vite.config.ts',
      'resources/js/components/ui/*',
    ],
  },

  // Main rules/plugins
  {
    files: ['**/*.{js,jsx,ts,tsx,vue}'],
    plugins: {
      import: importPlugin,
      boundaries,
    },
    settings: {
      'import/resolver': {
        typescript: {
          alwaysTryTypes: true,
          project: './tsconfig.json',
        },
      },

      /**
       * Boundaries: definisikan area kode
       * - app: resources/js (core)
       * - shared: Modules/Shared/resources/js (always-on shared)
       * - module: Modules/<Name>/resources/js (feature modules)
       */
      'boundaries/elements': [
        { type: 'app', pattern: 'resources/js/**/*' },
        { type: 'shared', pattern: 'Modules/Shared/resources/js/**/*' },
        { type: 'core', pattern: 'Modules/Core/resources/js/**/*' },

        // capture nama modul dari path: Modules/<ModuleName>/resources/js/...
        { type: 'module', pattern: 'Modules/*/resources/js/**/*', capture: ['module'] },
      ],
    },

    rules: {
      // rules kamu
      'vue/multi-word-component-names': 'off',
      '@typescript-eslint/no-explicit-any': 'off',
      '@typescript-eslint/consistent-type-imports': [
        'error',
        { prefer: 'type-imports', fixStyle: 'separate-type-imports' },
      ],
      'import/order': [
        'error',
        {
          groups: ['builtin', 'external', 'internal', 'parent', 'sibling', 'index'],
          alphabetize: { order: 'asc', caseInsensitive: true },
        },
      ],
      'import/no-restricted-paths': [
        'error',
        {
          zones: [...crossModuleZones,]
        },
      ],

      /**
       * Enforce boundaries:
       * - app boleh import app + shared + module (biasanya app sebagai entrypoint/registry)
       * - shared hanya boleh import app + shared (shared jangan tergantung feature module)
       * - module hanya boleh import:
       *   - dirinya sendiri
       *   - app
       *   - shared
       */
      'boundaries/no-unknown': 'error',
      'boundaries/element-types': [
        'error',
        {
          default: 'disallow',
          rules: [
            { from: 'app', allow: ['app', 'shared', 'module'] },
            { from: 'shared', allow: ['app', 'shared'] },
            { from: 'core', allow: ['app', 'shared', 'module', 'core'] },
            {
              from: 'module',
              allow: ['app', 'shared', ['module', { module: '${from.module}' }]],
            },
          ],
        },
      ],
    },
  },

  // Prettier last
  prettier,
)
