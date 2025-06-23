<template>
  <q-field
    ref="field"
    :model-value="value"
    :rules="[(val) => !!val || 'Field is required']"
    :error="!!error && showServerError"
    :error-message="error"
    class="q-pa-none"
    lazy-rules
    dense
    borderless
    no-error-icon
    hide-bottom-space
    label-slot
    stack-label
  >
    <template #label><span class="q-ml-sm text-md">Server</span></template>
    <q-list class="full-width" dense>
      <q-banner v-if="fetchError" class="text-negative bordered" rounded dense>
        Failed to fetch game servers, please try again.
      </q-banner>
      <template v-else-if="loading">
        <q-item v-for="i in 3" :key="i" style="padding-left: 4px">
          <q-item-section side><q-skeleton type="QRadio" size="20px" /></q-item-section>
          <q-item-section>
            <q-item-label><q-skeleton type="text" height="2em" /></q-item-label>
          </q-item-section>
        </q-item>
      </template>
      <q-item
        v-else
        v-for="server in servers"
        :key="server.id"
        :active="multiple ? value.includes(server[serverKey]) : value === server[serverKey]"
        style="padding-left: 4px"
        tag="label"
        v-ripple
      >
        <q-item-section side>
          <component
            :is="multiple ? QCheckbox : QRadio"
            v-model="value"
            :val="server[serverKey]"
            size="sm"
          />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ server.name }}</q-item-label>
        </q-item-section>
        <q-item-section v-if="withInactive" side>
          <q-item-label caption>
            <span v-if="server.active" class="text-positive">Active</span>
            <span v-else class="text-accent">Inactive</span>
          </q-item-label>
        </q-item-section>
      </q-item>
    </q-list>
  </q-field>
</template>

<style lang="scss" scoped>
.q-field.q-field--dense {
  :deep(.q-field__control-container) {
    padding-top: 22px;
  }

  :deep(.q-field__label) {
    top: 0;
    transform: none;
  }

  :deep(.q-field__bottom) {
    padding-left: 12px;
  }
}
</style>

<script>
import { QCheckbox, QRadio } from 'quasar'

export default {
  props: {
    modelValue: null,
    serverKey: {
      type: String,
      default: 'server_id',
    },
    multiple: {
      type: Boolean,
      default: false,
    },
    required: {
      type: Boolean,
      default: false,
    },
    error: {
      type: String,
      default: '',
    },
    withInactive: {
      type: Boolean,
      default: false,
    },
    withInvisible: {
      type: Boolean,
      default: false,
    },
  },

  setup() {
    return {
      QRadio,
      QCheckbox,
    }
  },

  data() {
    return {
      servers: [],
      loading: true,
      fetchError: true,
      showServerError: true,
    }
  },

  computed: {
    value: {
      get() {
        return this.modelValue
      },
      set(val) {
        this.$refs.field.resetValidation()
        this.showServerError = false
        this.$emit('update:modelValue', val)
      },
    },
  },

  mounted() {
    this.getServers()
  },

  methods: {
    async getServers() {
      this.fetchError = false
      this.loading = true
      try {
        const params = { filters: {} }
        if (!this.withInactive) params.filters.active = true
        if (this.withInvisible) params.with_invisible = true
        const { data } = await axios.get(route('game-servers.index', params))
        this.servers = data.data
      } catch {
        this.fetchError = true
      }
      this.loading = false
    },
  },

  watch: {
    error() {
      this.showServerError = true
    },
  },
}
</script>
