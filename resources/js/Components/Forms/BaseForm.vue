<script>
import { useForm } from '@inertiajs/vue3'

export default {
  props: {
    state: {
      type: String,
      required: false,
      default: 'create',
    },
    fields: {
      type: Object,
      default: () => ({}),
      required: true,
    },
    submitRoute: {
      type: String,
      required: true,
    },
    submitMethod: {
      type: String,
      required: false,
      default: 'post',
    },
    successMessage: {
      type: String,
      required: false,
    },
  },

  data: () => {
    return {
      form: {},
    }
  },

  created() {
    this.form = useForm(this.fields)
    this.$emit('created', this.form)
  },

  methods: {
    submit() {
      if (this.disabled) return
      this.$emit('submit')
      this.form.submit(this.submitMethod, this.submitRoute, {
        onSuccess: () => {
          this.$emit('success')
          if (this.successMessage) {
            this.$q.notify({
              message: this.successMessage,
              color: 'positive',
            })
          }
        },
        onError: (errors) => {
          const error = errors.error || 'An error occurred, please try again.'
          this.$emit('error')
          this.$q.notify({
            message: error,
            color: 'negative',
          })
        },
      })
    },
  },
}
</script>
