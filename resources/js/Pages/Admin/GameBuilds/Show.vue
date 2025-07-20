<template>
  <div class="flex column flex-grow no-wrap" style="height: 0">
    <div class="build-header bg-dark">
      <div class="q-py-xs q-pl-md q-pr-sm text-sm flex items-center">
        <q-icon :name="ionEllipse" class="q-mr-md" size="1em" color="primary" />
        <span class="build-header__title"
          >Build #{{ build.id }} for {{ build.game_server.short_name }}</span
        >
        <q-space />
        <div>
          <q-chip v-if="build.map_switch" class="q-my-none" color="grey" size="0.9em" outline square
            >Map Switch</q-chip
          >
          <q-chip
            v-if="!build.ended_at"
            class="q-my-none"
            color="primary"
            size="0.9em"
            outline
            square
            >Building</q-chip
          >
          <q-chip
            v-else-if="build.cancelled"
            class="q-my-none"
            color="primary"
            size="0.9em"
            outline
            square
            >Cancelled</q-chip
          >
          <q-chip
            v-else-if="build.failed"
            class="q-my-none"
            color="negative"
            size="0.9em"
            outline
            square
            >Failed</q-chip
          >
          <q-chip v-else class="q-my-none" color="positive" size="0.9em" outline square
            >Success</q-chip
          >

          <q-btn
            @click="$inertia.visit($store.ParentPage.url)"
            flat
            dense
            round
            :icon="ionCloseCircleOutline"
            class="q-ml-sm"
            color="grey"
            size="md"
          />
        </div>
      </div>

      <q-separator />

      <div class="build-header__details text-sm">
        <span class="text-opacity-80">
          Started <date-since :date="build.created_at" /> by
          {{ build.started_by.name || build.started_by.ckey }}
        </span>

        <span v-if="build.cancelled" class="text-opacity-80">
          Cancelled <date-since :date="build.ended_at" /> by
          {{ build.cancelled_by.name || build.cancelled_by.ckey }}
        </span>

        <span class="text-opacity-80">
          {{ build.branch }}
          <template v-if="build.commit">
            on
            <a
              :href="`https://github.com/goonstation/goonstation/commit/${build.commit}`"
              target="_blank"
            >
              {{ build.commit.substr(0, 7) }}
            </a>
          </template>
        </span>

        <span class="text-opacity-80">
          <q-btn
            @click="testMergesOpen = true"
            :disabled="!build.test_merges.length"
            class="q-ma-none"
            style="min-height: 0; line-height: 1"
            color="primary"
            size="11px"
            padding="xs"
            flat
          >
            {{ build.test_merges.length }}
            Testmerge<template v-if="build.test_merges.length !== 1">s</template>
          </q-btn>

          <test-merges-dialog
            v-model="testMergesOpen"
            :test-merges="build.test_merges"
            :authors="testMergeAuthors"
          />
        </span>

        <span v-if="build.map" class="text-opacity-80 build-header__map">
          Map
          <a :href="$route('maps.show', build.map_id.toLowerCase())" target="_blank">
            {{ build.map.name }}
          </a>
          <img :src="`/storage/maps/${build.map_id.toLowerCase()}/thumb.png`" class="gh-sprite" />
        </span>
      </div>

      <q-separator />
    </div>

    <div ref="logs" class="q-px-md q-py-sm text-sm overflow-auto">
      <template v-for="(group, groupIdx) in groups" :key="`group-${groupIdx}`">
        <q-expansion-item
          v-if="group.name"
          :expand-icon="ionChevronDown"
          :duration="100"
          class="log-group q-mt-xs q-mb-sm"
          header-class="log-group__header"
          default-opened
          dense-toggle
          dense
        >
          <template #header>
            <div class="log-group__header-label q-mr-sm">{{ group.name }}</div>
          </template>
          <div class="log-items">
            <template v-for="(log, logIdx) in group.logs" :key="`group-${groupIdx}-${logIdx}`">
              <pre>{{ formatDate(log.created_at) }}</pre>
              <pre
                :class="{ 'log-item--warn': log.type === 'err', 'log-item--error': log.error }"
                >{{ log.log }}</pre
              >
            </template>
          </div>
        </q-expansion-item>
        <div v-else class="log-items">
          <template v-for="(log, logIdx) in group.logs" :key="`group-${groupIdx}-${logIdx}`">
            <pre>{{ formatDate(log.created_at) }}</pre>
            <pre :class="{ 'log-item--warn': log.type === 'err', 'log-item--error': log.error }">{{
              log.log
            }}</pre>
          </template>
        </div>
      </template>

      <div v-if="!build.ended_at" class="q-pa-sm flex items-center">
        <q-spinner color="primary" size="16px" class="q-mr-md" />
        <pre class="q-ma-none">Waiting for output...</pre>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.build-header {
  position: sticky;
  top: 0;
  border-top-left-radius: 4px;
  border-top-right-radius: 4px;

  &__title {
    margin-top: 2px;
    font-weight: 500;
    letter-spacing: 0.02em;
  }

  &__details {
    display: grid;
    grid-auto-flow: column;
    grid-column-gap: 10px;
    overflow: hidden;

    > * {
      position: relative;
      padding: 8px 12px;

      &::before {
        content: '';
        position: absolute;
        top: 0;
        background-color: rgba(255, 255, 255, 0.28);
        z-index: 1;
        inline-size: 1px;
        block-size: 100%;
        inset-inline-start: -5px;
      }
    }
  }

  &__map {
    img {
      position: absolute;
      z-index: -1;
      top: 50%;
      right: 0;
      height: 50px;
      transform: translate(0%, -50%);
      mask-image: linear-gradient(to left, var(--q-dark) 70%, transparent 100%);
    }
  }
}

.log-group {
  position: relative;
  min-height: 25px;

  &::before {
    content: '';
    position: absolute;
    top: 10px;
    right: 0;
    bottom: 0;
    left: 0;
    pointer-events: none;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 2px;
  }

  &__header-label {
    margin-top: 1px;
  }
}

:deep(.log-group__header) {
  display: inline-flex;
  align-items: center;
  margin: 0 10px;
  padding: 0 8px;
  background-color: $dark;
  min-height: 22px;
  border-radius: 4px;

  .q-item__section--side {
    padding-right: 0;
  }

  .q-icon {
    font-size: 14px;
  }
}

.log-items {
  display: grid;
  grid-template-columns: auto 1fr;
  grid-template-rows: 1fr;
  grid-column-gap: 0px;
  grid-row-gap: 0px;

  pre {
    margin: 0;
    padding: 0 8px;
    white-space: pre-wrap;
    word-break: break-word;

    &:nth-child(odd) {
      opacity: 0.6;
      padding-right: 4px;
    }

    &:last-child {
      padding-bottom: 4px;
    }

    &.log-item--warn {
      color: $accent;
      background-color: rgb(232 122 0 / 10%);
    }

    &.log-item--error {
      color: $negative;
      background-color: rgb(227 52 52 / 10%);
    }
  }
}
</style>

<script>
import DateSince from '@/Components/DateSince.vue'
import TestMergesDialog from '@/Components/Dialogs/TestMerges.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { router } from '@inertiajs/vue3'
import { ionChevronDown, ionCloseCircleOutline, ionEllipse } from '@quasar/extras/ionicons-v6'
import { date } from 'quasar'
import GameBuildsLayout from './Layout.vue'

export default {
  layout: (h, page) => {
    return h(DashboardLayout, { title: `Build #${page.props.build.id}` }, () =>
      h(GameBuildsLayout, () => page)
    )
  },

  components: {
    DateSince,
    TestMergesDialog,
  },

  props: {
    build: {
      type: Object,
      required: true,
    },
    testMergeAuthors: Array,
  },

  setup() {
    return {
      router,
      date,
      ionEllipse,
      ionCloseCircleOutline,
      ionChevronDown,
    }
  },

  data() {
    return {
      testMergesOpen: false,
      logs: [],
      currentLogGroup: null,
    }
  },

  computed: {
    groups() {
      const groups = [{ name: null, logs: [] }]
      let onGroup = null
      for (const log of this.logs) {
        if (log.group === onGroup) {
          groups[groups.length - 1].logs.push(log)
        } else {
          groups.push({ name: log.group, logs: [log] })
        }
        onGroup = log.group
      }
      return groups
    },
  },

  created() {
    this.onNewLogs({ buildId: this.build.id, logs: this.build.logs })
  },

  mounted() {
    if (!this.build.ended_at) {
      Echo.private('game-builds').listen('GameBuildLog', this.onNewLogs)
      Echo.private('game-builds').listen('GameBuildFinished', this.onBuildFinished)
    }
  },

  beforeUnmount() {
    Echo.private('game-builds').stopListening('GameBuildLog', this.onNewLogs)
    Echo.private('game-builds').stopListening('GameBuildFinished', this.onBuildFinished)
  },

  methods: {
    onNewLogs({ buildId, logs }) {
      if (this.build.id !== buildId) return
      for (const log of logs) {
        if (log.group) {
          if (log.group === 'reset' || log.group === 'error-reset') {
            if (log.group === 'error-reset') log.error = true
            log.group = null
            this.currentLogGroup = null
          } else if (log.group === 'error') {
            log.group = this.currentLogGroup
            log.error = true
          } else if (this.currentLogGroup !== log.group) {
            log.newGroup = true
            this.currentLogGroup = log.group
          }
        } else if (this.currentLogGroup) {
          log.group = this.currentLogGroup
        }

        this.logs.push(log)
      }
    },

    onBuildFinished({ serverId }) {
      if (this.build.game_server.id === serverId) {
        this.$inertia.reload()
        Echo.private('game-builds').stopListening('GameBuildLog', this.onNewLogs)
      }
    },

    formatDate(logDate) {
      const diff = date.getDateDiff(new Date(logDate), new Date(this.build.created_at), 'seconds')
      const relativeDate = date.addToDate(new Date('2000-01-1'), { seconds: diff })
      return date.formatDate(relativeDate, 'mm:ss')
    },
  },

  watch: {
    logs: {
      deep: true,
      async handler() {
        await this.$nextTick()
        this.$refs.logs.scroll({ top: this.$refs.logs.scrollHeight })
      },
    },
  },
}
</script>
