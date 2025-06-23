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
            <error-filters
              :modelValue="filters"
              :has-search-filters="hasSearchFilters"
              @search="search"
              @clear-search="clearSearch"
            />
          </div>
        </q-btn-dropdown>
        <div class="flex items-center q-ml-md">
          Showing {{ $formats.number(errors.length) }} errors
        </div>
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
      <div v-if="sidebarEnabled" class="error-filters-sidebar-wrap q-pa-sm bg-dark rounded-borders">
        <error-filters
          :modelValue="filters"
          :has-search-filters="hasSearchFilters"
          @search="search"
          @clear-search="clearSearch"
          sidebar
        />
      </div>
      <div class="relative flex-grow">
        <q-virtual-scroll
          type="table"
          style="position: absolute; top: 0; left: 0; right: 0; bottom: 0;"
          table-colspan="2"
          :virtual-scroll-item-size="24"
          :virtual-scroll-sticky-size-start="28"
          :virtual-scroll-slice-size="60"
          :virtual-scroll-slice-ratio-before="10"
          :virtual-scroll-slice-ratio-after="10"
          :items="errors"
          flat
          dense
        >
          <template v-slot:before>
            <thead class="thead-sticky text-left" style="width: 100%;">
              <tr>
                <th style="width: 100px;">Time</th>
                <th style="width: auto">Error</th>
              </tr>
            </thead>
            <tbody v-if="loading">
              <tr>
                <td colspan="2" class="text-center">
                  <div class="q-pa-md">Loading errors...</div>
                </td>
              </tr>
            </tbody>
          </template>

          <template v-slot="{ item: row, index }">
            <error-entry
              :key="index"
              :error="row"
              :relative-timestamps="filters.relativeTimestamps"
              :round-started-at="allErrors[0].created_at"
              :search-terms="errorEntrySearchTerms"
              :error-count="getErrorCount(row)"
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

.error-filters-sidebar-wrap {
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

:deep(.q-table) {
  table-layout: fixed;
}
</style>

<script>
import RoundSummary from '@/Components/RoundSummary.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { ionExpand } from '@quasar/extras/ionicons-v6'
import axios from 'axios'
import ErrorEntry from './Partials/ErrorEntry.vue'
import ErrorFilters from './Partials/Filters.vue'

export default {
  components: {
    RoundSummary,
    ErrorFilters,
    ErrorEntry,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Errors' }, () => page),

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
      allErrors: [],
      loading: true,
      errors: [],
      errorCounts: {},
      searchFilters: {
        and: [],
        or: [],
        not: [],
      },
      filters: {
        searchInput: '',
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

    errorEntrySearchTerms() {
      if (!this.hasSearchFilters) return []
      return this.searchFilters.and.concat(this.searchFilters.or)
    },
  },

  created() {
    this.sidebarEnabled = !!localStorage.getItem('error-viewer-sidebar')
    this.getErrors()
  },

  methods: {
    async getErrors() {
      try {
        const response = await axios.get(
          route('admin.errors.get-errors', { gameRound: this.round.id })
        )
        this.allErrors = response.data

        for (const error of this.allErrors) {
          const key = `${error.file}${error.line}${error.name}`
          if (this.errorCounts.hasOwnProperty(key)) {
            this.errorCounts[key]++
          } else {
            this.errorCounts[key] = 1
          }
        }

        this.filterErrors()
      } catch (e) {
        console.log(e)
      }

      this.loading = false
    },

    filterErrors() {
      this.errors = this.allErrors.filter((error) => {
        let valid = true
        if (this.hasSearchFilters) {
          const errorMessage =
            `${error.name} ${error.file} ${error.line} ${error.desc} ${error.user} ${error.user_ckey}`.toLowerCase()

          if (this.searchFilters.not.length) {
            valid = this.searchFilters.not.every((notFilter) => {
              return !errorMessage.includes(notFilter)
            })
          }

          if (valid && this.searchFilters.and.length) {
            valid = this.searchFilters.and.every((andFilter) => {
              return errorMessage.includes(andFilter)
            })
          }

          if (valid && this.searchFilters.or.length) {
            valid = this.searchFilters.or.some((orFilter) => {
              return errorMessage.includes(orFilter)
            })
          }
        }
        return valid
      })
    },

    search(filters) {
      this.searchFilters = filters
      this.filterErrors()
    },

    clearSearch() {
      this.searchFilters = { and: [], or: [], not: [] }
      this.filterErrors()
    },

    toggleSidebar() {
      this.sidebarEnabled = !this.sidebarEnabled

      if (this.sidebarEnabled) {
        localStorage.setItem('error-viewer-sidebar', true)
      } else {
        localStorage.removeItem('error-viewer-sidebar')
      }
    },

    getErrorCount(error) {
      return this.errorCounts[`${error.file}${error.line}${error.name}`]
    }
  },
}
</script>
