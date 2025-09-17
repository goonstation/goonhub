<template>
  <q-dialog v-model="opened">
    <q-card style="max-width: 500px; width: 100%" flat bordered>
      <div class="gh-card__header q-pa-md bordered">
        <span>Whitelisted Servers</span>
      </div>

      <q-card-section class="q-pb-none">
        <game-servers-select
          v-model="servers"
          label="Servers"
          server-key="id"
          multiple
          with-invisible
          with-inactive
        />
      </q-card-section>

      <q-card-actions align="right">
        <q-btn flat label="Cancel" v-close-popup />
        <q-btn @click="toggleWhitelisted" label="Toggle" color="primary" :loading="loading" flat />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
import GameServersSelect from '@/Components/Selects/GameServers.vue'

export default {
  props: {
    modelValue: Boolean,
    players: Array,
  },

  components: {
    GameServersSelect,
  },

  data() {
    return {
      loading: false,
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
    async toggleWhitelisted() {
      this.loading = true
      const { data } = await axios.post(this.$route('admin.whitelist.bulk-toggle'), {
        player_ids: this.players.map((row) => row.id),
        server_ids: this.servers,
      })

      this.$q.notify({ message: data.message, color: 'positive' })
      this.loading = false
      this.opened = false
      this.$emit('success')
    },
  },

  watch: {
    players: {
      immediate: true,
      handler(players) {
        this.servers = [...new Set(
          players.map((row) => row.whitelist?.servers.map((row) => row.id)).flat().filter(Number)
        )]
      }
    }
  }
}
</script>
