<template>
  <q-item class="q-py-md">
    <q-item-section side>
      <div v-if="!status.status" class="status">
        <q-tooltip>Unknown</q-tooltip>
      </div>
      <div v-else-if="status.status === 'running'" class="status status--running">
        <q-tooltip>Running</q-tooltip>
      </div>
      <div v-else class="status status--down">
        <q-tooltip>Down</q-tooltip>
      </div>
    </q-item-section>
    <q-item-section>
      <q-item-label>{{ server.name }}</q-item-label>
      <q-item-label caption>
        <span v-if="!status.health" class="text-opacity-80">Unknown status</span>
        <template v-else>
          <span v-if="status.health === 'healthy'" class="text-positive">Healthy</span>
          <span v-else-if="status.health === 'starting'" class="text-accent">Starting</span>
          <!-- TODO: other health values? -->
          &bull;
          <span v-if="status.restarting" class="text-accent">Restarting</span>
          <span v-else-if="uptime" class="text-opacity-80">Up for {{ uptime }}</span>
        </template>
      </q-item-label>
    </q-item-section>
    <q-item-section side>
      <q-btn-dropdown
        :icon="ionSettingsOutline"
        color="grey-7"
        size="sm"
        padding="sm sm"
        outline
        dense
      >
        <q-list>
          <q-item
            :disable="loadingRestart"
            @click="restartOpened = true"
            class="text-accent"
            clickable
            v-close-popup
          >
            <q-item-section side>
              <q-icon :name="ionReloadCircleOutline" color="accent" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Restart</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-btn-dropdown>
    </q-item-section>

    <q-dialog v-model="restartOpened">
      <q-card flat bordered>
        <q-card-section class="row items-center no-wrap">
          <q-avatar :icon="ionInformationCircleOutline" color="negative" text-color="dark" />
          <span class="q-ml-md">
            Any players currently connected will lose connection and will be required to manually
            reconnect once the restart is complete. Are you sure you want to restart this server?
            <br /><br />
            The restart process may take up to five minutes to complete.
          </span>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn
            flat
            label="Confirm"
            color="negative"
            @click="restart"
            :loading="loadingRestart"
            v-close-popup
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-item>
</template>

<style lang="scss" scoped>
.status {
  position: relative;
  width: 1em;
  height: 1em;
  background: currentColor;
  border-radius: 50%;

  &::before {
    content: '';
    display: block;
    position: absolute;
    width: 100%;
    height: 100%;
    background: currentColor;
    border-radius: 50%;
    animation: pulse 3s infinite;
  }

  &--running {
    color: $positive;
  }

  &--down {
    color: $negative;
  }
}

@keyframes pulse {
  0% {
    transform: scale(1);
    opacity: 1;
  }

  33%,
  100% {
    transform: scale(2);
    opacity: 0;
  }
}
</style>

<script>
import {
  ionInformationCircleOutline,
  ionReloadCircleOutline,
  ionSettingsOutline,
} from '@quasar/extras/ionicons-v6'
import dayjs from 'dayjs'

export default {
  props: {
    server: {
      type: Object,
      required: true,
      default: () => ({}),
    },
    status: {
      type: Object,
      required: false,
      default: () => ({}),
    },
  },

  emits: ['restarted'],

  setup() {
    return {
      ionSettingsOutline,
      ionInformationCircleOutline,
      ionReloadCircleOutline,
    }
  },

  data() {
    return {
      restartOpened: false,
      loadingRestart: false,
    }
  },

  computed: {
    uptime() {
      if (!this.status?.startedAt) return
      return dayjs(this.status.startedAt).fromNow(true)
    },
  },

  methods: {
    async restart() {
      if (this.loadingRestart) return
      this.loadingRestart = true
      try {
        const { data: operation } = await axios.post(route('admin.overseer.restart'), {
          server: this.server.server_id,
        })
        let progress = operation
        const deadline = Date.now() + 5 * 60 * 1000
        while (!['succeeded', 'failed'].includes(progress.state)) {
          if (Date.now() >= deadline) {
            throw new Error('Restart is still in progress. Check server status before retrying.')
          }
          await new Promise((resolve) => setTimeout(resolve, 2000))
          const { data } = await axios.get(
            route('admin.overseer.operation', { id: operation.id, server: this.server.server_id })
          )
          progress = data
        }
        if (progress.state === 'failed') {
          throw new Error(progress.error || 'Server restart failed')
        }
        this.$q.notify({ message: 'Server restarted', color: 'positive' })
        this.$emit('restarted')
      } catch (e) {
        this.$q.notify({
          message: e.response?.data?.message || e.message || 'Something went wrong',
          color: 'negative',
        })
      }
      this.loadingRestart = false
    },
  },
}
</script>
