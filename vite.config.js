import { quasar, transformAssetUrls } from '@quasar/vite-plugin'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import path from 'path'
import VueMacros from 'unplugin-vue-macros/vite'
import { defineConfig } from 'vite'

export default defineConfig({
  server: {
    port: 5174,
    hmr: {
      host: 'localhost',
    },
    watch: {
      ignored: ['**/storage/app/**'],
    },
  },
  build: {
    sourcemap: true,
  },
  plugins: [
    laravel({
      input: ['resources/js/app.js', 'resources/css/game-auth.scss'],
      refresh: true,
    }),
    VueMacros({
      plugins: {
        vue: vue(),
      },
      template: { transformAssetUrls },
    }),
    quasar({
      sassVariables: path.resolve(__dirname, './resources/css/quasar-variables.scss'),
    }),
  ],
  resolve: {
    alias: {
      '@img': path.resolve(__dirname, './resources/img'),
      '@': path.resolve(__dirname, './resources/js'),
    },
  },
})
