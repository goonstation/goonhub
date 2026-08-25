import useStore from '../Composables/store'

export default {
  install: (app) => {
    app.config.globalProperties.$store = useStore()
  },
}
