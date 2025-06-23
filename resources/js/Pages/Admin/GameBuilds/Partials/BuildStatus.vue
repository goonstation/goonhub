<template>
  <q-item
    @click="$inertia.visit($route('admin.builds.show', build.build.id))"
    class="q-pa-none text-sm build-status"
    :class="[type === 'current' ? 'text-primary' : 'text-grey']"
    :clickable="!!build.build"
    :v-ripple="!!build.build"
  >
    <q-item-section class="q-pa-md">
      <q-item-label class="text-white" lines="1">{{ build.server.short_name }}</q-item-label>
      <q-item-label class="text-sm" caption>
        <template v-if="type === 'current'">Started</template>
        <template v-else>Queued</template>
        By {{ build.admin.name }}
      </q-item-label>
    </q-item-section>

    <q-item-section class="q-mr-sm" side>
      <q-btn
        v-if="!build.cancelled"
        @click.stop="confirmCancel = true"
        flat
        dense
        round
        :icon="ionCloseCircleOutline"
        color="negative"
        size="md"
      />
    </q-item-section>

    <q-item-section class="build-progress">
      <q-linear-progress
        :value="100"
        :indeterminate="type === 'current'"
        :color="type === 'current' ? 'primary' : 'grey'"
        size="xs"
        animation-speed="500"
      />
      <div class="build-progress__labels gap-xs-sm">
        <span v-if="build.cancelled" class="build-progress__cancelled text-negative">
          Cancelled
        </span>
        <span v-else class="build-progress__time">
          <template v-if="type === 'current' && !build.build">Starting</template>
          <template v-else>{{ timeSinceStarted }}</template>
        </span>
        <span v-if="build.mapSwitch" class="build-progress__map text-grey">Map Switch</span>
      </div>
    </q-item-section>

    <q-dialog v-model="confirmCancel">
      <q-card flat bordered>
        <q-card-section class="row items-center no-wrap">
          <q-avatar :icon="ionInformationCircleOutline" color="negative" text-color="dark" />
          <span class="q-ml-sm"> Are you sure you want to cancel this build? </span>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Close" v-close-popup />
          <q-btn flat label="Confirm" color="negative" @click="cancelBuild" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-item>
</template>

<style lang="scss" scoped>
.build-status {
  flex-wrap: wrap;
}

.build-progress {
  $self: &;
  position: relative;
  flex: 1 0 100%;

  &__labels {
    display: flex;
    align-items: center;
    position: absolute;
    top: -9px;
    left: 16px;
    height: 20px;

    span {
      display: inline-flex;
      align-items: center;
      font-size: 11px;
      font-weight: 600;
      letter-spacing: 0.1px;
      background: rgba($dark, 0.3);
      backdrop-filter: blur(4px);
      border: 1px solid rgba(255, 255, 255, 0.5);
      border-radius: 2px;
      padding: 0 5px;
    }

    #{$self}__cancelled {
      border-color: $negative;
    }
  }
}
</style>

<script>
import { router } from '@inertiajs/vue3'
import { ionCloseCircleOutline, ionInformationCircleOutline } from '@quasar/extras/ionicons-v6'
import axios from 'axios'
import { date } from 'quasar'

export default {
  props: {
    modelValue: {
      type: Object,
      required: true,
    },
    type: {
      type: String,
      required: false,
      default: 'current',
    },
  },

  setup() {
    return {
      router,
      ionInformationCircleOutline,
      ionCloseCircleOutline,
    }
  },

  data() {
    return {
      timeSinceStarted: '',
      confirmCancel: false,
      cancelling: false,
    }
  },

  computed: {
    build: {
      get() {
        return this.modelValue
      },
      set(val) {
        this.$emit('update:modelValue', val)
      },
    },
  },

  mounted() {
    this.updateTimeSinceStarted()
    setInterval(this.updateTimeSinceStarted, 1000)

    Echo.private('game-builds').listen('GameBuildStarted', this.onBuildStarted)
    Echo.private('game-builds').listen('GameBuildCancelled', this.onBuildCancelled)
  },

  beforeUnmount() {
    Echo.private('game-builds').stopListening('GameBuildStarted', this.onBuildStarted)
    Echo.private('game-builds').stopListening('GameBuildCancelled', this.onBuildCancelled)
  },

  methods: {
    onBuildStarted({ serverId, build }) {
      if (this.build.server.id === serverId) {
        this.build.build = build
      }
    },

    onBuildCancelled({ serverId, type }) {
      if (this.build.server.server_id === serverId && type === this.type) {
        this.build.cancelled = true
      }
    },

    updateTimeSinceStarted() {
      const diff = date.getDateDiff(new Date(), new Date(this.build.startedAt), 'seconds')
      const relativeDate = date.addToDate(new Date('2000-01-1'), { seconds: diff })
      this.timeSinceStarted = date.formatDate(relativeDate, 'mm:ss')
    },

    async cancelBuild() {
      if (this.cancelling) return
      this.cancelling = true
      try {
        await axios.post(route('admin.builds.cancel'), {
          server_id: this.build.server.server_id,
          type: this.type,
        })
      } catch (e) {
        this.$q.notify({
          message: e.response?.data?.message || 'Failed to cancel build, please try again.',
          color: 'negative',
        })
      }
      this.cancelling = false
      this.confirmCancel = false
    },
  },
}
</script>
