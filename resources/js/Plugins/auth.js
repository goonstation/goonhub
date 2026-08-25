import useAuth from '../Composables/auth'

export default {
  install: (app) => {
    app.config.globalProperties.$auth = useAuth()
  },
}
