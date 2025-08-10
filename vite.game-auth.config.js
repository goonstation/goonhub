import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import path from 'path'
import VueMacros from 'unplugin-vue-macros/vite'
import { defineConfig } from 'vite'
import svgLoader from 'vite-svg-loader'

const domain = process.env.APP_URL ? process.env.APP_URL.replace(/https?:\/\//gi, '') : undefined

export default defineConfig({
  base: '/build-game-auth/',
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
    outDir: 'public/build-game-auth',
    sourcemap: true,
  },
  plugins: [
    laravel({
      input: ['resources/game-auth/js/app.js'],
      refresh: ['resources/views/game-auth/**'],
    }),
    VueMacros({
      plugins: {
        vue: vue(),
      },
    }),
    svgLoader(),
  ],
  resolve: {
    alias: {
      '@img': path.resolve(__dirname, './resources/game-auth/img'),
      '@css': path.resolve(__dirname, './resources/game-auth/css'),
      '@': path.resolve(__dirname, './resources/game-auth/js'),
      '@main': path.resolve(__dirname, './resources/js'),
    },
  },
})
