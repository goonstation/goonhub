// @ts-check

import eslint from '@eslint/js'
import { defineConfig, globalIgnores } from 'eslint/config'
import tseslint from 'typescript-eslint'
import eslintConfigPrettier from 'eslint-config-prettier'
import pluginVue from 'eslint-plugin-vue'
import globals from 'globals'

export default defineConfig([
  globalIgnores(['resources/js/Pages/Terminal/jsterm/js/jsterm.js']),
  {
    basePath: 'resources/js',
    files: ['**/*.{ts,js,mjs,cjs,vue}'],
  },
  {
    languageOptions: {
      globals: {
        ...globals.browser,
        ...globals.node,
        route: true,
        axios: true,
        Pusher: true,
        Echo: true,
        Ziggy: true,
      },
    },
  },
  eslint.configs.recommended,
  tseslint.configs.recommended,
  ...pluginVue.configs['flat/essential'],
  eslintConfigPrettier,
  {
    rules: {
      'vue/multi-word-component-names': 'off',
    },
  },
])
