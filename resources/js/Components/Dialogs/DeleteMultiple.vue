<template>
  <q-dialog v-model="opened">
    <q-card flat bordered>
      <q-card-section class="row items-center no-wrap">
        <q-avatar :icon="ionInformationCircleOutline" color="negative" text-color="dark" />
        <span class="q-ml-sm">
          Are you sure you want to delete {{ items.length }} item<template v-if="items.length !== 1"
            >s</template
          >?
        </span>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn flat label="Cancel" v-close-popup />
        <q-btn flat label="Confirm" color="negative" @click="confirm" :loading="loading" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
import { ionInformationCircleOutline } from '@quasar/extras/ionicons-v6'

export default {
  props: {
    modelValue: Boolean,
    route: String,
    items: {
      type: Array,
      default: () => [],
    },
  },

  setup() {
    return {
      ionInformationCircleOutline,
    }
  },

  data() {
    return {
      loading: false,
    }
  },

  computed: {
    opened: {
      get() {
        return this.modelValue
      },
      set(val) {
        this.$emit('update:modelValue', val)
      },
    },
  },

  methods: {
    async confirm() {
      if (this.loading) return
      this.loading = true

      try {
        const response = await axios.delete(route(this.route), {
          data: { ids: this.items },
        })
        this.$q.notify({
          message: response.data.message || 'Items successfully deleted.',
          color: 'positive',
        })
        this.$emit('deleted')
      } catch {
        this.$q.notify({
          message: 'Failed to delete items, please try again.',
          color: 'negative',
        })
      }

      this.opened = false
      this.loading = false
    },
  },
}
</script>
