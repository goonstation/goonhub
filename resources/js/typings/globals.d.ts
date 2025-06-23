import axios from 'axios'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
// import { defineComponent } from 'vue'
import { route as routeFn } from '../../../vendor/tightenco/ziggy'
import formatsPlugin from '../Plugins/formats'
import helpersPlugin from '../Plugins/helpers'
import storePlugin from '../Plugins/store'

const axiosFn = axios
const pusherFn = Pusher
const echoFn = Echo<any>

declare global {
  var route: typeof routeFn
  var axios: typeof axiosFn
  var Pusher: typeof pusherFn
  var Echo: typeof echoFn.prototype
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

// declare module '*.vue' {
//   const component: typeof defineComponent
//   export default component
// }

export { }

