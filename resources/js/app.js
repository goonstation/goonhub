import './bootstrap'

import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { Notify, Quasar } from 'quasar'
import { createApp, h } from 'vue'
import VueDOMPurifyHTML from 'vue-dompurify-html'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'

// import '@quasar/extras/animate/slideInDown.css'
// import '@quasar/extras/animate/slideOutUp.css'

import dayjs from 'dayjs'
import advancedFormat from 'dayjs/plugin/advancedFormat'
import duration from 'dayjs/plugin/duration'
import isSameOrBefore from 'dayjs/plugin/isSameOrBefore'
import relativeTime from 'dayjs/plugin/relativeTime'
import timezone from 'dayjs/plugin/timezone'
import utc from 'dayjs/plugin/utc'
dayjs.extend(utc)
dayjs.extend(timezone)
dayjs.extend(relativeTime)
dayjs.extend(duration)
dayjs.extend(isSameOrBefore)
dayjs.extend(advancedFormat)

import VueApexCharts from 'vue3-apexcharts'

// Import icon libraries
import { ionChevronDown } from '@quasar/extras/ionicons-v7'
import iconSet from 'quasar/icon-set/svg-ionicons-v6'
iconSet.arrow.dropdown = ionChevronDown

// Import Quasar css
import 'quasar/src/css/flex-addon.sass'
import 'quasar/src/css/index.sass'

import '../css/app.scss'

// Plugins!
import formats from './Plugins/formats'
import globals from './Plugins/globals'
import helpers from './Plugins/helpers'
import route from './Plugins/route'
import store from './Plugins/store'

createInertiaApp({
  resolve: (name) =>
    resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue, Ziggy)
      .use(Quasar, {
        plugins: {
          Notify,
        },
        config: {
          dark: true,
          notify: {
            position: 'top-right',
          },
        },
        iconSet,
        cssAddon: true,
      })
      .use(store)
      .use(route)
      .use(helpers)
      .use(formats)
      .use(globals)
      .use(VueApexCharts)
      .use(VueDOMPurifyHTML)
      .mount(el)
  },
  progress: {
    color: '#ffd125',
  },
})
