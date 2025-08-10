import { createInertiaApp, Link } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createApp, h } from 'vue'
import { ZiggyVue } from '../../../vendor/tightenco/ziggy'

import '../css/main.scss'

createInertiaApp({
  resolve: (name) =>
    resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue, Ziggy)
      .use({
        install: (app) => {
          // eslint-disable-next-line vue/no-reserved-component-names
          app.component('Link', Link)
        },
      })
      .mount(el)
  },
  progress: {
    color: '#649dbb',
  },
})
