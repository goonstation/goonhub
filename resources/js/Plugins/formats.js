import useFormats from '../Composables/formats'

export default {
  install: (app) => {
    app.config.globalProperties.$formats = useFormats()
  },
}
