import useHelpers from '../Composables/helpers'

export default {
  install: (app) => {
    app.config.globalProperties.$helpers = useHelpers()
  },
}
