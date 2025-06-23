import legacy from '@vitejs/plugin-legacy'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import path from 'path'
import VueMacros from 'unplugin-vue-macros/vite'
import { defineConfig } from 'vite'

export default defineConfig({
  build: {
    sourcemap: true,
  },
  plugins: [
    laravel({
      input: ['resources/js/game-auth.js'],
      refresh: true,
      buildDirectory: 'build-game-auth',
    }),
    VueMacros({
      plugins: {
        vue: vue(),
      },
    }),
    legacy({
      targets: 'IE 11',
    }),
  ],
  resolve: {
    alias: {
      '@img': path.resolve(__dirname, './resources/img'),
      '@': path.resolve(__dirname, './resources/js'),
    },
  },
})
