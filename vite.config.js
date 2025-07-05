import { quasar, transformAssetUrls } from '@quasar/vite-plugin'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import path from 'path'
import VueMacros from 'unplugin-vue-macros/vite'
import { defineConfig } from 'vite'

const domain = process.env.APP_URL ? process.env.APP_URL.replace(/https?:\/\//gi, '') : undefined

export default defineConfig({
  server: {
    port: 5174,
    hmr: {
      host: domain || 'localhost',
    },
    watch: {
      ignored: ['**/storage/app/**'],
    },
    https: {
      key: domain ? path.resolve(__dirname, `./docker/develop/certs/${domain}.key`) : undefined,
      cert: domain ? path.resolve(__dirname, `./docker/develop/certs/${domain}.crt`) : undefined,
    },
  },
  build: {
    sourcemap: true,
  },
  plugins: [
    laravel({
      input: ['resources/js/app.js'],
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
