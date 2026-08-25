import { reactive } from 'vue'
import Stores from '../Stores'

export default function useStore() {
  const storeStore = reactive({})

  for (const storeName in Stores) {
    const store = new Stores[storeName]()
    store.setup()
    storeStore[storeName] = store.obj
  }

  return storeStore
}
