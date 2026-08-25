import type { Axios } from 'axios'
import type Echo from 'laravel-echo'
import type Pusher from 'pusher-js'
import type { Config, route as routeFn } from '../../../vendor/tightenco/ziggy'
import type useAuth from '../Composables/auth'
import type useFormats from '../Composables/formats'
import type useHelpers from '../Composables/helpers'
import type useStore from '../Composables/store'

declare global {
  var route: typeof routeFn
  var axios: Axios
  var Pusher: Pusher
  var Echo: Echo<"reverb">
  var Ziggy: Config
}

declare module 'vue' {
  interface ComponentCustomProperties {
    $route: typeof routeFn
    $helpers: ReturnType<typeof useHelpers>
    $formats: ReturnType<typeof useFormats>
    $store: ReturnType<typeof useStore>
    $auth: ReturnType<typeof useAuth>
  }
}

export { }
