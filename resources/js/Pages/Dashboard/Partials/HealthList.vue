<template>
  <div class="health">
    <div class="health__header">
      <div>Goonhub Health</div>
      <div>Checked {{ lastChecked }}</div>
    </div>
    <div class="checks">
      <template v-if="checks.length">
        <health-check v-for="check in checks" :key="check.name" :check="check" />
      </template>
      <health-check v-else :check="{ label: 'Unable to fetch health checks', status: 'none' }" />
    </div>
  </div>
</template>

<style lang="scss" scoped>
.health {
  border-top: 1px solid rgba(255, 255, 255, 0.2);

  &__header {
    display: flex;
    justify-content: space-between;
    margin: -15px 0 0px 0;
    padding: 0 10px;
    font-size: 12px;

    > * {
      padding: 5px;
      background: $dark-page;
    }

    > :first-child {
      font-weight: 500;
    }

    > :last-child {
      font-size: 11px;
      color: rgba(255, 255, 255, 0.75);
    }
  }
}

.checks {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}
</style>

<script>
import HealthCheck from './HealthCheck.vue'

export default {
  components: {
    HealthCheck,
  },

  props: {
    health: {
      type: [Object, null],
      required: true,
    },
  },

  data: () => {
    return {
      lastChecked: null,
      refreshTimer: null,
      cancelToken: null,
      cleaned: false,
    }
  },

  computed: {
    checks() {
      if (!Array.isArray(this.health?.storedCheckResults)) return []
      return this.health.storedCheckResults.filter((check) => check.status !== 'skipped')
    },
  },

  created() {
    this.refreshTimer = setTimeout(() => {
      this.refresh()
    }, 60 * 1000)

    this.$inertia.on('before', (e) => {
      if (!e.detail.visit.async) this.cleanup()
    })
  },

  beforeUnmount() {
    this.cleanup()
  },

  methods: {
    async refresh() {
      this.$inertia.reload({
        only: ['health'],
        preserveUrl: true,
        preserveState: false,
        onCancelToken: (cancelToken) => (this.cancelToken = cancelToken),
        onFinish: (visit) => {
          if (visit.cancelled) return
          this.refreshTimer = setTimeout(() => {
            this.refresh()
          }, 60 * 1000)
        },
      })
    },

    cleanup() {
      if (this.cleaned) return
      this.cleaned = true
      this.cancelToken && this.cancelToken.cancel()
      this.refreshTimer && clearTimeout(this.refreshTimer)
    },
  },

  watch: {
    '$store.Ticker.tick': {
      immediate: true,
      handler() {
        this.lastChecked = this.$formats.fromNow(this.health.finishedAt)
      },
    },
  },
}
</script>
