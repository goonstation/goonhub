<template>
  <q-card
    tag="a"
    :href="$route('play', server.server_id)"
    :class="mapId && `server-status--map-${mapId}`"
    target="_blank"
    class="server-status"
    flat
  >
    <q-card-section class="row items-center no-wrap full-height">
      <div class="server-status__info q-mr-md">
        <strong class="text-primary">
          {{ server.name }}
        </strong>
        <div class="server-status__station-name">
          <q-skeleton type="text" v-if="loading" />
          <div v-else-if="error" class="text-red text-weight-medium">
            Unable to reach the server.
          </div>
          <template v-else>{{ status.station_name }}</template>
        </div>
        <div class="text-caption row">
          <div v-if="error">&nbsp;</div>
          <q-skeleton type="text" v-else-if="loading" width="100%" />
          <template v-else>
            <span>{{ $formats.capitalize(status.mode || 'Secret') }} Mode</span>
            <q-separator vertical color="grey" class="q-mx-sm q-my-xs" />
            <span>{{ status.players }} players</span>
            <q-separator vertical color="grey" class="q-mx-sm q-my-xs" />
            <span>
              <template v-if="isPreRound">Lobby</template>
              <template v-else-if="isPostRound">Ended</template>
              <template v-else>{{ roundTime }}</template>
              <q-tooltip>Round time</q-tooltip>
            </span>
            <q-separator vertical color="grey" class="q-mx-sm q-my-xs" />
            <span>
              {{ status.map_name }}
              <q-tooltip>Current map</q-tooltip>
            </span>
          </template>
        </div>
      </div>
      <q-btn class="q-ml-auto text-weight-bolder" color="primary" text-color="dark" label="Join" />
    </q-card-section>
    <img
      v-if="mapId"
      :src="$helpers.publicUrl(`/maps/${mapId}/thumb.png`)"
      alt=""
      class="server-status__map"
    />
  </q-card>
</template>

<style lang="scss" scoped>
.server-status {
  $self: &;
  position: relative;
  display: block;
  text-decoration: none;
  overflow: hidden;

  &__map {
    position: absolute;
    top: 50%;
    right: 0;
    transform: translateY(-50%);
    z-index: 0;
    width: 50%;
    max-width: 300px;
    filter: blur(1px);
    mask-image: linear-gradient(to left, var(--q-dark) 0%, transparent 100%);
    transition: all 200ms;
  }

  // Help with centering some maps that don't have the station quite in the middle
  &--map-atlas &__map {
    margin-top: 30px;
    margin-right: 20px;
  }
  &--map-clarion &__map {
    margin-top: -20px;
    margin-right: -10px;
  }
  &--map-nadir &__map {
    margin-top: 5px;
  }
  &--map-kondaru &__map {
    margin-top: -15px;
    margin-right: 25px;
  }
  &--map-donut3 &__map {
    margin-top: -10px;
  }
  &--map-donut2 &__map {
    margin-top: -25px;
    margin-right: -40px;
  }

  &:after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    width: 4px;
    background: var(--q-primary);
    transition: all 200ms;
  }

  &:hover,
  &:focus {
    #{$self}__map {
      transform: translateY(-50%) scale(1.1);
    }

    &:after {
      width: 8px;
    }
  }

  .q-card__section {
    position: relative;
    z-index: 1;
    padding: 0.65rem 1rem;
    text-shadow: 1px 1px 1px black;
  }

  &__info {
    display: flex;
    justify-content: space-evenly;
    flex-direction: column;
    width: 0;
    height: 100%;
    flex: 1;
  }

  &__station-name {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
  }
}
</style>

<script>
import dayjs from 'dayjs'

export default {
  emits: ['refreshed'],

  props: {
    server: Object,
    waiting: Boolean,
  },

  data: () => {
    return {
      loading: true,
      error: false,
      status: {},
      refreshTimer: null,
      cancelToken: null,
      cleaned: false,
    }
  },

  computed: {
    isPreRound() {
      // GAME_STATE_PREGAME = 5
      return parseInt(this.status.gamestate) <= 5
    },

    isPostRound() {
      // GAME_STATE_FINISHED = 8
      return parseInt(this.status.gamestate) === 8
    },

    roundTime() {
      if (!this.status.round_duration || this.isPreRound) return 0
      return dayjs.duration(parseInt(this.status.round_duration), 'seconds').format('H[h] m[m]')
    },

    mapId() {
      if (!this.status.map_id) return ''
      return this.status.map_id.toLowerCase().replace(/\s/g, '')
    },
  },

  created() {
    this.$inertia.on('before', (e) => {
      if (!e.detail.visit.async) this.cleanup()
    })
  },

  beforeUnmount() {
    this.cleanup()
  },

  watch: {
    waiting: {
      immediate: true,
      handler(val) {
        if (!val) this.refresh()
      },
    },
  },

  methods: {
    async refresh() {
      this.error = false
      this.$inertia.reload({
        only: ['status'],
        data: { server: this.server.server_id },
        preserveUrl: true,
        preserveState: false,
        onCancelToken: (cancelToken) => (this.cancelToken = cancelToken),
        onSuccess: (page) => {
          if (page.props.status.error) {
            this.error = true
            this.status = {}
            return
          }
          this.status = page.props.status
        },
        onError: () => {
          this.error = true
          this.status = {}
        },
        onFinish: (visit) => {
          if (visit.cancelled) return
          this.refreshTimer = setTimeout(() => {
            this.refresh()
          }, 60 * 1000)

          this.$emit('refreshed', {
            serverId: this.server.server_id,
            status: this.status,
            error: this.error,
          })

          this.loading = false
        },
      })
    },

    cleanup() {
      if (this.cleaned) return
      this.cleaned = true
      if (this.cancelToken) this.cancelToken.cancel()
      if (this.refreshTimer) clearTimeout(this.refreshTimer)
    },
  },
}
</script>
