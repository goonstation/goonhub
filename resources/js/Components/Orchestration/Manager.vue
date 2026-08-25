<template>
  <q-card class="gh-card q-mb-md" flat>
    <div class="q-pa-md flex items-center justify-between">
      <span class="text-weight-medium">Game Server Management</span>
      <q-icon :name="ionHelpCircleOutline" color="grey" size="1.75em">
        <q-tooltip class="text-sm text-center">
          This tool manages the docker containers running the game servers.<br />All information
          presented refers to the containers, and not the BYOND instances.
        </q-tooltip>
      </q-icon>
    </div>
    <q-separator />
    <q-card-section v-if="errorLoadingStatus && !servers.length" class="q-px-sm q-pt-sm q-pb-none">
      <q-banner class="q-py-xs q-px-sm text-accent bordered" rounded dense>
        <span class="text-sm">Failed to fetch game status.</span>
      </q-banner>
    </q-card-section>
    <q-list separator>
      <template v-if="servers.length">
        <orchestrated-server
          v-for="server in servers"
          :key="server.id"
          :server="server"
          :status="status[server.server_id]"
          @restarted="onRestarted"
        />
      </template>
      <q-item v-else v-for="i in 5" :key="i" class="q-py-md">
        <q-item-section side>
          <q-skeleton type="circle" size="1em" />
        </q-item-section>
        <q-item-section>
          <q-skeleton type="rect" class="q-mb-sm" height="1em" />
          <q-skeleton type="rect" width="150px" height="1em" />
        </q-item-section>
        <q-item-section side>
          <q-skeleton type="rect" width="58px" height="33px" />
        </q-item-section>
      </q-item>
    </q-list>
  </q-card>
</template>

<script>
import { ionHelpCircleOutline } from '@quasar/extras/ionicons-v6'
import OrchestratedServer from './Server.vue'

let statusInterval = 0

export default {
  components: {
    OrchestratedServer,
  },

  setup() {
    return {
      ionHelpCircleOutline,
    }
  },

  data() {
    return {
      servers: [],
      loadingServers: true,
      status: {},
      loadingStatus: true,
      errorLoadingStatus: false,
    }
  },

  mounted() {
    this.getServers()
    this.getStatus()
    statusInterval = setInterval(this.getStatus, 60 * 1000)
  },

  beforeUnmount() {
    clearInterval(statusInterval)
  },

  methods: {
    async getServers() {
      this.loadingServers = true
      try {
        const { data } = await axios.get(
          route('web.game-servers.index', {
            filters: {
              active: true,
            },
            with_invisible: true,
          })
        )
        this.servers = data.data
      } catch {
        //
      }
      this.loadingServers = false
    },

    async getStatus() {
      this.loadingStatus = true
      this.errorLoadingStatus = false
      try {
        const { data } = await axios.get(route('admin.orchestration.status'))
        this.status = data
      } catch {
        this.errorLoadingStatus = true
      }
      this.loadingStatus = false
    },

    onRestarted() {
      clearInterval(statusInterval)
      this.getStatus()
      statusInterval = setInterval(this.getStatus, 60 * 1000)
    },
  },
}
</script>
