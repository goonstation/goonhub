import type { Axios } from 'axios'
import type Echo from 'laravel-echo'
import type Pusher from 'pusher-js'
import type { route as routeFn } from '../../../vendor/tightenco/ziggy'
import formatsPlugin from '../Plugins/formats'
import helpersPlugin from '../Plugins/helpers'
import storePlugin from '../Plugins/store'

declare global {
  var route: typeof routeFn
  var axios: Axios
  var Pusher: Pusher
  var Echo: Echo<"reverb">
  var Ziggy: Object
}

declare module 'vue' {
  type helpers = typeof helpersPlugin.install
  type formats = typeof formatsPlugin.install
  type store = typeof storePlugin.install

  interface ComponentCustomProperties {
    $route: typeof routeFn
    $helpers: ReturnType<helpers>
    $formats: ReturnType<formats>
    $store: ReturnType<store>
  }
}

export { }
