<template>
  <div class="flex-grow flex column">
    <round-summary class="q-mb-sm" :round="round" dense />

    <div class="relative flex-grow flex column" :class="{ 'table-full': fullscreen }">
      <div class="table-top flex">
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
              :modelValue="filters"
              :log-types="logTypes"
              :has-search-filters="hasSearchFilters"
              @search="search"
              @clear-search="clearSearch"
            />
          </div>
        </q-btn-dropdown>
        <div class="flex items-center q-ml-md">Showing {{ $formats.number(logs.length) }} logs</div>
        <q-space />
        <q-btn square dense size="sm" class="q-mr-sm" @click="toggleSidebar">
          <template v-if="sidebarEnabled">Hide</template>
          <template v-else>Show</template>
          Sidebar
        </q-btn>
        <q-btn square dense @click="fullscreen = !fullscreen" :icon="ionExpand">
          <q-tooltip>Toggle Fullscreen</q-tooltip>
        </q-btn>
      </div>
      <div v-if="sidebarEnabled" class="log-filters-sidebar-wrap q-pa-sm bg-dark rounded-borders">
        <log-filters
          :modelValue="filters"
          :log-types="logTypes"
          :has-search-filters="hasSearchFilters"
          @search="search"
          @clear-search="clearSearch"
          sidebar
        />
      </div>
      <div class="relative flex-grow">
        <q-virtual-scroll
          type="table"
          style="position: absolute; top: 0; left: 0; right: 0; bottom: 0"
          :virtual-scroll-item-size="24"
          :virtual-scroll-sticky-size-start="28"
          :virtual-scroll-slice-size="60"
          :virtual-scroll-slice-ratio-before="10"
          :virtual-scroll-slice-ratio-after="10"
          :items="logs"
          flat
          dense
        >
          <template v-slot:before>
            <thead class="thead-sticky text-left">
              <tr>
                <th style="width: 0">Time</th>
                <th style="width: 89px">Type</th>
                <th>Log</th>
              </tr>
            </thead>
            <tbody v-if="loading">
              <tr>
                <td colspan="100%" class="text-center">
                  <div class="q-pa-md">Loading logs...</div>
                </td>
              </tr>
            </tbody>
          </template>

          <template v-slot="{ item: row, index }">
            <log-entry
              :key="index"
              :log="row"
              :relative-timestamps="filters.relativeTimestamps"
              :round-started-at="allLogs[0].created_at"
              :search-terms="logEntrySearchTerms"
            />
          </template>
        </q-virtual-scroll>
      </div>
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
}
</style>

<script>
import RoundSummary from '@/Components/RoundSummary.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { ionExpand } from '@quasar/extras/ionicons-v6'
import axios from 'axios'
import LogFilters from './Partials/Filters.vue'
import LogEntry from './Partials/LogEntry.vue'

const logMessageRenderer = document.createElement('div')

export default {
  components: {
    RoundSummary,
    LogFilters,
    LogEntry,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Logs' }, () => page),

  setup() {
    return {
      ionExpand,
    }
  },

  props: {
    round: Object,
  },

  data() {
    return {
      allLogs: [],
      loading: true,
      logs: [],
      searchFilters: {
        and: [],
        or: [],
        not: [],
      },
      logTypes: [],
      filters: {
        searchInput: '',
        logTypesToShow: [],
        relativeTimestamps: false,
      },
      fullscreen: false,
      sidebarEnabled: false,
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
  },

  created() {
    this.sidebarEnabled = !!localStorage.getItem('log-viewer-sidebar')
    this.getLogs()
  },

  methods: {
    async getLogs() {
      try {
        const response = await axios.get(route('admin.logs.get-logs', { gameRound: this.round.id }))
        this.allLogs = response.data

        const logTypes = [...new Set(this.allLogs.map((log) => log.type))].sort()
        this.logTypes = logTypes.map((logType) => {
          return {
            label: logType,
            value: logType,
          }
        })
        this.filters.logTypesToShow = logTypes

        // Append ckey to player element inner texts
        const poptsRegex =
          /(<a href='.*?\?src=%admin_ref%;action=adminplayeropts;targetckey=(.*?)' title='Player Options'>)(.*?)(<\/a>)/gi
        for (const logIdx in this.allLogs) {
          const logEntry = this.allLogs[logIdx]
          if (logEntry.source)
            logEntry.source = logEntry.source.replaceAll(poptsRegex, '$1$3 ($2)$4')
          if (logEntry.message)
            logEntry.message = logEntry.message.replaceAll(poptsRegex, '$1$3 ($2)$4')
          this.allLogs[logIdx] = logEntry
        }

        this.filterLogs()
      } catch (e) {
        console.log(e)
      }

      this.loading = false
    },

    filterLogs() {
      this.logs = this.allLogs.filter((log) => {
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

    search(filters) {
      this.searchFilters = filters
      this.filterLogs()
    },

    clearSearch() {
      this.searchFilters = { and: [], or: [], not: [] }
      this.filterLogs()
    },

    toggleSidebar() {
      this.sidebarEnabled = !this.sidebarEnabled

      if (this.sidebarEnabled) {
        localStorage.setItem('log-viewer-sidebar', true)
      } else {
        localStorage.removeItem('log-viewer-sidebar')
      }
    },
  },

  watch: {
    'filters.logTypesToShow': {
      handler() {
        this.filterLogs()
      },
    },
  },
}
</script>
