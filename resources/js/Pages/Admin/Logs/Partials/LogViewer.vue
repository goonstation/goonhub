<template>
  <div class="relative flex-grow flex column" :class="{ 'table-full': fullscreen }">
    <div class="table-top flex">
      <template v-if="!hideFilters">
        <q-btn-dropdown
          v-if="!sidebarEnabled"
          class="q-ml-sm"
          label="Filters"
          menu-anchor="bottom start"
          menu-self="top start"
          square
          dense
        >
          <div class="q-pa-md">
            <log-filters
              v-model="filters"
              :log-types="logTypes"
              :has-search-filters="hasSearchFilters"
              :show-relative-timestamps="showRelativeTimestamps"
              @search="handleSearch"
              @clear-search="clearSearch"
            />
          </div>
        </q-btn-dropdown>
        <div class="flex items-center q-ml-md">
          <template v-if="loading">Loading...</template>
          <template v-else>Showing {{ $formats.number(filteredLogs.length) }} logs</template>
          <template v-if="totalFromServer && totalFromServer > filteredLogs.length">
            of {{ $formats.number(totalFromServer) }}
          </template>
        </div>
        <q-space />
        <q-btn square dense size="sm" class="q-mr-sm" @click="toggleSidebar">
          <template v-if="sidebarEnabled">Hide</template>
          <template v-else>Show</template>
          Sidebar
        </q-btn>
      </template>
      <q-space v-else />
      <q-btn square dense @click="fullscreen = !fullscreen" :icon="ionExpand">
        <q-tooltip>Toggle Fullscreen</q-tooltip>
      </q-btn>
    </div>
    <div
      v-if="!hideFilters && sidebarEnabled"
      class="log-filters-sidebar-wrap q-pa-sm bg-dark rounded-borders"
    >
      <log-filters
        v-model="filters"
        :log-types="logTypes"
        :has-search-filters="hasSearchFilters"
        :show-relative-timestamps="showRelativeTimestamps"
        sidebar
        @search="handleSearch"
        @clear-search="clearSearch"
      />
    </div>
    <div class="relative flex-grow">
      <q-virtual-scroll
        ref="virtualScroll"
        type="table"
        style="position: absolute; top: 0; left: 0; right: 0; bottom: 0"
        :virtual-scroll-item-size="24"
        :virtual-scroll-sticky-size-start="28"
        :virtual-scroll-slice-size="60"
        :virtual-scroll-slice-ratio-before="10"
        :virtual-scroll-slice-ratio-after="10"
        :items="filteredLogs"
        flat
        dense
      >
        <template v-slot:before>
          <thead class="thead-sticky text-left">
            <tr>
              <th style="width: 0">Time</th>
              <th style="width: 89px">Type</th>
              <th v-if="showRoundColumn" style="width: 0">Server</th>
              <th v-if="showRoundColumn" style="width: 0">Round</th>
              <th>Log</th>
            </tr>
          </thead>
          <tbody v-if="loading && !hideFilters">
            <tr>
              <td colspan="100%" class="text-center">
                <div class="q-pa-md">Loading logs...</div>
              </td>
            </tr>
          </tbody>
          <tbody v-else-if="!loading && !filteredLogs.length">
            <tr>
              <td colspan="100%" class="text-center">
                <div class="q-pa-md">{{ emptyMessage }}</div>
              </td>
            </tr>
          </tbody>
        </template>

        <template v-slot="{ item: row, index }">
          <log-entry
            :key="index"
            :log="row"
            :relative-timestamps="filters.relativeTimestamps"
            :round-started-at="roundStartedAt"
            :search-terms="logEntrySearchTerms"
            :show-round-column="showRoundColumn"
            :highlighted="highlightedLogId && String(highlightedLogId) === String(row.id)"
          />
        </template>
      </q-virtual-scroll>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.table-full {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 5999;
}

.table-top {
  background: black;
  padding: 5px;
}

.log-filters-sidebar-wrap {
  position: absolute;
  z-index: 2;
  top: 50px;
  right: 10px;
  border: 1px solid #616161;
  overflow: auto;
  max-height: calc(100% - 50px);
}

.thead-sticky tr > * {
  position: sticky;
  opacity: 1;
  z-index: 1;
  background: black;
}

.thead-sticky tr:last-child > * {
  top: 0;
}
</style>

<style lang="scss">
.log-type-label {
  background: #5f5f5f;
}

.log-type-filter,
.log-type-row {
  &.log-type-ahelp {
    background: #10107f;
  }
  .log-type-label-ahelp {
    background: #36365f;
  }
  &.log-type-mhelp {
    background: #4b135e;
  }
  .log-type-label-mhelp {
    background: #52365f;
  }
  &.log-type-admin {
    background: #003652;
  }
  .log-type-label-admin {
    background: #134b5e;
  }
  &.log-type-bombing {
    background: #484e51;
  }
  .log-type-label-bombing {
    background: #6d777c;
  }
  &.log-type-chemistry {
    background: #8c4d0f;
  }
  .log-type-label-chemistry {
    background: #c26100;
  }
  &.log-type-debug {
    background: #523600;
  }
  .log-type-label-debug {
    background: #834100;
  }
  &.log-type-diary {
    background: #743400;
  }
  .log-type-label-diary {
    background: #634221;
  }
  &.log-type-ooc {
    background: #303074;
  }
  .log-type-label-ooc {
    background: #3f3f96;
  }
  &.log-type-pdamsg {
    background: #323d0f;
  }
  .log-type-label-pdamsg {
    background: #536026;
  }
  &.log-type-say {
    background: #262a2b;
  }
  .log-type-label-say {
    background: #303436;
  }
  &.log-type-combat {
    background: #470000;
  }
  .log-type-label-combat {
    background: #720000;
  }
  &.log-type-whisper {
    background: #1c1d1f;
  }
  .log-type-label-whisper {
    background: #313638;
  }
  &.log-type-tgui {
    background: #003539;
  }
  .log-type-label-tgui {
    background: #274b4c;
  }

  // Highlighted log row (when navigating from search)
  &.log-highlighted {
    background: rgba(255, 209, 37, 0.4);
    animation: highlight-pulse 5s ease-out forwards;

    // Persistent left accent bar
    td:first-child {
      position: relative;

      &::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, #ffd125 0%, #ffab00 100%);
        box-shadow: 0 0 8px rgba(255, 209, 37, 0.6);
      }
    }
  }
}

@keyframes highlight-pulse {
  0%,
  75% {
    background: rgba(255, 209, 37, 0.5);
  }
  100% {
    background: rgba(255, 209, 37, 0.15);
  }
}
</style>

<script>
import { ionExpand } from '@quasar/extras/ionicons-v6'
import LogFilters from './Filters.vue'
import LogEntry from './LogEntry.vue'

const logMessageRenderer = document.createElement('div')

export default {
  components: {
    LogFilters,
    LogEntry,
  },

  props: {
    logs: {
      type: Array,
      required: true,
    },
    logTypes: {
      type: Array,
      default: () => [],
    },
    loading: {
      type: Boolean,
      default: false,
    },
    totalFromServer: {
      type: Number,
      default: null,
    },
    roundStartedAt: {
      type: String,
      default: null,
    },
    showRoundColumn: {
      type: Boolean,
      default: false,
    },
    showRelativeTimestamps: {
      type: Boolean,
      default: true,
    },
    emptyMessage: {
      type: String,
      default: 'No logs found.',
    },
    clientSideFiltering: {
      type: Boolean,
      default: true,
    },
    hideFilters: {
      type: Boolean,
      default: false,
    },
    highlightedLogId: {
      type: [Number, String],
      default: null,
    },
  },

  emits: ['search', 'clear-search'],

  setup() {
    return {
      ionExpand,
    }
  },

  data() {
    return {
      searchFilters: {
        and: [],
        or: [],
        not: [],
      },
      filters: {
        searchInput: '',
        logTypesToShow: [],
        relativeTimestamps: false,
      },
      fullscreen: false,
      sidebarEnabled: false,
      hasScrolledToHighlight: false,
    }
  },

  computed: {
    hasSearchFilters() {
      return !!(
        this.searchFilters.and.length ||
        this.searchFilters.or.length ||
        this.searchFilters.not.length
      )
    },

    logEntrySearchTerms() {
      if (!this.hasSearchFilters) return []
      return this.searchFilters.and.concat(this.searchFilters.or)
    },

    filteredLogs() {
      if (!this.clientSideFiltering) {
        return this.logs
      }

      return this.logs.filter((log) => {
        let valid = this.filters.logTypesToShow.includes(log.type)
        if (valid && this.hasSearchFilters) {
          logMessageRenderer.innerHTML = (log.source + ' ' + log.message).toLowerCase()
          const logMessage = logMessageRenderer.textContent

          if (this.searchFilters.not.length) {
            valid = this.searchFilters.not.every((notFilter) => {
              return !logMessage.includes(notFilter)
            })
          }

          if (valid && this.searchFilters.and.length) {
            valid = this.searchFilters.and.every((andFilter) => {
              return logMessage.includes(andFilter)
            })
          }

          if (valid && this.searchFilters.or.length) {
            valid = this.searchFilters.or.some((orFilter) => {
              return logMessage.includes(orFilter)
            })
          }
        }
        return valid
      })
    },
  },

  watch: {
    logTypes: {
      immediate: true,
      handler(newTypes) {
        if (newTypes.length && !this.filters.logTypesToShow.length) {
          this.filters.logTypesToShow = newTypes.map((t) => t.value)
        }
      },
    },
    'filters.logTypesToShow': {
      handler() {
        // Trigger re-filtering and scroll to highlighted log if needed
        if (this.highlightedLogId && this.filteredLogs.length > 0) {
          this.scrollToHighlightedLog()
        }
      },
    },
    filteredLogs: {
      handler(newLogs) {
        if (newLogs.length > 0 && this.highlightedLogId && !this.hasScrolledToHighlight) {
          this.scrollToHighlightedLog()
        }
      },
    },
  },

  created() {
    this.sidebarEnabled = !!localStorage.getItem('log-viewer-sidebar')
  },

  methods: {
    handleSearch(filters) {
      this.searchFilters = filters
      this.$emit('search', filters)
    },

    clearSearch() {
      this.searchFilters = { and: [], or: [], not: [] }
      this.$emit('clear-search')
    },

    toggleSidebar() {
      this.sidebarEnabled = !this.sidebarEnabled

      if (this.sidebarEnabled) {
        localStorage.setItem('log-viewer-sidebar', true)
      } else {
        localStorage.removeItem('log-viewer-sidebar')
      }
    },

    scrollToHighlightedLog() {
      if (!this.highlightedLogId) return

      // Find the index of the highlighted log in the filtered logs
      // Use == for comparison to handle string/number type differences
      const targetId = this.highlightedLogId
      const logIndex = this.filteredLogs.findIndex((log) => String(log.id) === String(targetId))
      if (logIndex === -1) return

      this.hasScrolledToHighlight = true

      this.$nextTick(() => {
        // Use the virtual scroll's scrollTo method
        if (this.$refs.virtualScroll) {
          this.$refs.virtualScroll.scrollTo(logIndex, 'center')
        }
      })
    },
  },
}
</script>
