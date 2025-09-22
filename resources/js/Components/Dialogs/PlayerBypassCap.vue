<template>
  <q-dialog v-model="opened">
    <q-card style="max-width: 500px; width: 100%" flat bordered>
      <div class="gh-card__header q-pa-md bordered">
        <span>Bypass Cap Servers</span>
      </div>

      <q-card-section class="q-pb-none">
        <game-servers-select
          v-model:groups="serverGroups"
          v-model:servers="servers"
          label="Servers"
          multiple
          with-invisible
          with-inactive
        />
      </q-card-section>

      <q-card-actions align="right">
        <q-btn flat label="Cancel" v-close-popup />
        <q-btn @click="toggleBypassCap" label="Toggle" color="primary" :loading="loading" flat />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
import GameServersSelect from '@/Components/Selects/GameServers.vue'

export default {
  props: {
    modelValue: Boolean,
    player: Object,
  },

  components: {
    GameServersSelect,
  },

  data() {
    return {
      loading: false,
      serverGroups: [],
      servers: [],
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
    async toggleBypassCap() {
      this.loading = true
      const removing = this.serverGroups.length === 0 && this.servers.length === 0
      const { data } = await axios.post(this.$route('admin.bypass-cap.toggle', this.player.id), {
        server_group_ids: this.serverGroups,
        server_ids: this.servers,
      })

      this.$q.notify({ message: data.message, color: 'positive' })
      this.loading = false
      this.opened = false
      this.$emit('success', { bypassCap: removing ? null : data.bypassCap })
    },
  },

  watch: {
    opened: {
      immediate: true,
      handler(opened) {
        if (!opened) return
        this.serverGroups = this.player.bypass_cap?.server_groups
          ? [...this.player.bypass_cap.server_groups.map((row) => row.id)]
          : []
        this.servers = this.player.bypass_cap?.servers
          ? [...this.player.bypass_cap.servers.map((row) => row.id)]
          : []
      },
    },
  },
}
</script>
