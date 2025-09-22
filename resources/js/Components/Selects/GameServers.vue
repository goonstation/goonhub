<template>
  <q-field
    ref="field"
    :model-value="multiple ? [...modelGroups, ...modelServers] : [modelGroups, modelServers]"
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
      <q-item v-else v-for="groupItem in options" :key="groupItem.id" style="padding: 0 0 0 4px">
        <q-list class="full-width" dense>
          <q-item
            v-if="!serversOnly"
            :active="multiple ? groups.includes(groupItem.id) : group === groupItem.id"
            style="padding-left: 4px"
            tag="label"
            v-ripple
          >
            <q-item-section side>
              <component
                :is="multiple ? QCheckbox : QRadio"
                v-model="modelGroups"
                :val="groupItem.id"
                size="sm"
              />
            </q-item-section>
            <q-item-section>
              <q-item-label class="flex items-center gap-xs-sm">
                <q-chip
                  class="q-ma-none text-xs text-weight-bold text-dark"
                  style="height: auto; padding: 0.2em 0.6em"
                  color="primary"
                  square
                  dense
                  >Group</q-chip
                >
                {{ groupItem.name }}
              </q-item-label>
            </q-item-section>
          </q-item>
          <template v-if="!groupsOnly">
            <q-item
              v-for="serverItem in groupItem.servers"
              :key="serverItem.id"
              :active="multiple ? servers.includes(serverItem.id) : server === serverItem.id"
              :disable="multiple ? groups.includes(groupItem.id) : group === groupItem.id"
              :class="!groupsOnly && !serversOnly ? 'q-ml-md' : ''"
              style="padding-left: 4px"
              tag="label"
              v-ripple
            >
              <q-item-section side>
                <component
                  :is="multiple ? QCheckbox : QRadio"
                  :model-value="!multiple && group === groupItem.id ? serverItem.id : modelServers"
                  @update:model-value="selectServer(serverItem.id)"
                  :val="serverItem.id"
                  size="sm"
                />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ serverItem.name }}</q-item-label>
              </q-item-section>
              <q-item-section v-if="withInactive" side>
                <q-item-label caption>
                  <span v-if="serverItem.active" class="text-positive">Active</span>
                  <span v-else class="text-accent">Inactive</span>
                </q-item-label>
              </q-item-section>
            </q-item>
          </template>
        </q-list>
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
  emits: ['update:groups', 'update:servers', 'update:group', 'update:server'],

  props: {
    groups: {
      type: Array,
      default: () => [],
    },
    group: {
      type: Number,
      default: null,
    },
    servers: {
      type: Array,
      default: () => [],
    },
    server: {
      type: Number,
      default: null,
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
    groupsOnly: {
      type: Boolean,
      default: false,
    },
    serversOnly: {
      type: Boolean,
      default: false,
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
      options: [],
      loading: true,
      fetchError: true,
      showServerError: true,
    }
  },

  computed: {
    modelGroups: {
      get() {
        return this.multiple ? this.groups : this.group
      },
      set(val) {
        if (this.multiple) {
          this.$emit('update:groups', val)
          if (val.length > 0) {
            // Deselect all servers in the selected group
            const selectedGroupServers = this.options
              .find((group) => val.includes(group.id))
              .servers.map((server) => server.id)
            this.modelServers = this.servers.filter(
              (server) => !selectedGroupServers.includes(server)
            )
          }
        } else {
          this.$emit('update:group', val)
          if (val) {
            this.modelServers = null
          }
        }
      },
    },

    modelServers: {
      get() {
        if (this.multiple) {
          // So we can show checked boxes for servers in the selected groups
          // without them being actually selected in the model
          const selectedServersInGroups = this.options
            .filter((group) => this.groups.includes(group.id))
            .map((group) => group.servers.map((server) => server.id))
            .flat()
          return [...new Set([...this.servers, ...selectedServersInGroups])]
        } else {
          const selectedServersInGroup =
            this.options
              ?.find((group) => group.id === this.group)
              ?.servers?.map((server) => server.id) || []
          return selectedServersInGroup.length > 0 ? selectedServersInGroup : this.server
        }
      },
      set(val) {
        if (this.multiple) {
          this.$emit('update:servers', val)
        } else {
          this.$emit('update:server', val)
          if (val) {
            this.modelGroups = null
          }
        }
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
        if (this.withInvisible) params.filters.with_invisible = true
        const { data } = await axios.get(route('game-servers.index', params))

        const groups = []

        for (const server of data.data) {
          if (!server.group) continue
          let group = groups.find((group) => group.id === server.group.id)
          if (!group) {
            group = {
              id: server.group.id,
              name: server.group.name,
              selected: false,
              servers: [],
            }
            groups.push(group)
          }
          group.servers.push(server)
        }

        this.options = groups
      } catch (e) {
        console.error(e)
        this.fetchError = true
      }
      this.loading = false
    },

    selectServer(id) {
      if (this.multiple) {
        const selectedGroups = this.options.filter((group) => this.groups.includes(group.id))
        if (!selectedGroups.some((group) => group.servers.some((s) => s.id === id))) {
          // Only allow selecting servers that are not in the selected groups
          this.modelServers = [...new Set([...this.servers, id])]
        }
      } else {
        const selectedGroup = this.options.find((group) => group.id === this.group)
        if (!selectedGroup?.servers?.some((s) => s.id === id)) {
          // Only allow selecting servers that are not in the selected group
          this.modelServers = id
        }
      }
    },
  },

  watch: {
    error() {
      this.showServerError = true
    },

    // groups: {
    //   deep: true,
    //   handler(val) {
    //     for (const group of val) {
    //       group.selected = this.value.includes(group.id)
    //     }
    //   },
    // },
  },
}
</script>
