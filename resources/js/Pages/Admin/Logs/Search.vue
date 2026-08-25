<template>
  <div class="flex-grow flex column log-search-page">
    <!-- Search Bar -->
    <div class="search-bar">
      <q-form @submit.prevent="performSearch" class="search-form">
        <q-input
          v-model="searchQuery"
          placeholder="Search logs..."
          dense
          dark
          filled
          class="search-input"
          hide-bottom-space
          @keyup.enter="performSearch"
        >
          <template v-slot:prepend>
            <q-icon :name="ionSearch" size="xs" color="grey-6" />
          </template>
          <template v-slot:append>
            <q-icon
              v-if="searchQuery"
              :name="ionClose"
              size="xs"
              color="grey-6"
              class="cursor-pointer"
              @click="searchQuery = ''"
            />
          </template>
        </q-input>

        <q-select
          v-model="selectedTypes"
          :options="logTypeOptions"
          placeholder="All types"
          dense
          dark
          filled
          multiple
          clearable
          emit-value
          map-options
          options-dense
          popup-content-class="log-type-popup"
          class="type-select"
          hide-bottom-space
          :loading="loadingTypes"
          :display-value="typeDisplayValue"
        >
          <template v-slot:prepend>
            <q-icon :name="ionFilter" size="xs" color="grey-6" />
          </template>
        </q-select>

        <q-select
          v-model="selectedServer"
          :options="serverOptions"
          placeholder="All servers"
          dense
          dark
          filled
          clearable
          emit-value
          map-options
          options-dense
          class="server-select"
          hide-bottom-space
          :loading="loadingServers"
          :display-value="!selectedServer ? 'All servers' : selectedServer.label"
        >
          <template v-slot:prepend>
            <q-icon :name="ionServer" size="xs" color="grey-6" />
          </template>
        </q-select>

        <q-input
          v-model="roundFilter"
          placeholder="Round ID"
          type="number"
          dense
          dark
          filled
          clearable
          class="round-input"
          hide-bottom-space
        >
          <template v-slot:prepend>
            <q-icon :name="ionGameController" size="xs" color="grey-6" />
          </template>
        </q-input>

        <q-btn
          type="submit"
          color="primary"
          text-color="dark"
          :loading="loading"
          :disable="!canSearch"
          unelevated
          no-caps
          class="search-btn"
        >
          <q-icon :name="ionSearch" size="xs" class="q-mr-xs" />
          Search
        </q-btn>

        <q-btn
          v-if="hasSearched"
          color="grey-7"
          unelevated
          no-caps
          class="clear-btn"
          @click="clearSearch"
        >
          Clear
        </q-btn>
      </q-form>
    </div>

    <!-- Results Header -->
    <div v-if="hasSearched" class="results-header">
      <div class="results-info">
        <template v-if="loading">
          <q-spinner size="xs" class="q-mr-sm" />
          Searching...
        </template>
        <template v-else-if="totalResults > 0">
          <strong>{{ totalResults.toLocaleString() }}</strong
          >&nbsp;results
          <span class="results-meta">
            <template v-if="totalResults >= maxResults">
              (limited to {{ maxResults.toLocaleString() }})
            </template>
            &middot; {{ searchDuration }}ms
          </span>
        </template>
        <template v-else>No results found</template>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!hasSearched" class="empty-state">
      <q-icon :name="ionSearch" size="56px" class="empty-icon" />
      <div class="empty-title">Search game logs</div>
      <div class="empty-subtitle">Enter at least 3 characters to search across all rounds</div>
    </div>

    <!-- Results -->
    <log-viewer
      v-else
      :logs="logs"
      :log-types="logTypesForViewer"
      :loading="loading"
      :total-from-server="totalResults"
      :show-round-column="true"
      :show-relative-timestamps="false"
      :client-side-filtering="false"
      :hide-filters="true"
      :empty-message="emptyMessage"
      class="search-results"
    />

    <!-- Bottom Pagination -->
    <div v-if="hasSearched && totalPages > 1" class="bottom-pagination">
      <q-pagination
        v-model="currentPage"
        :max="totalPages"
        :max-pages="9"
        direction-links
        boundary-links
        color="grey-6"
        active-color="primary"
        active-text-color="dark"
        @update:model-value="onPageChange"
      />
    </div>
  </div>
</template>

<style lang="scss" scoped>
.log-search-page {
  background: var(--q-dark-page);
}

// Search bar
.search-bar {
  padding: 12px 16px;
  background: #151515;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.search-form {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.search-input {
  flex: 1;
  min-width: 180px;
  max-width: 320px;
}

.round-input {
  width: 110px;
}

.search-btn {
  padding: 0 16px;
  height: 40px;
}

.clear-btn {
  padding: 0 12px;
  height: 40px;
}

// Results header
.results-header {
  display: flex;
  align-items: center;
  padding: 8px 16px;
  background: #0d0d0d;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  min-height: 36px;
}

.results-info {
  font-size: 13px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.results-meta {
  color: #666;
}

// Empty state
.empty-state {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 48px 24px;
}

.empty-icon {
  color: var(--q-primary);
}

.empty-title {
  font-size: 16px;
  font-weight: 500;
  margin-top: 16px;
}

.empty-subtitle {
  font-size: 13px;
  margin-top: 4px;
}

// Results area - remove the top bar and fix loading state
.search-results {
  :deep(.table-top) {
    display: none;
  }

  // Prevent layout shift during loading
  :deep(tbody tr td) {
    &:has(.q-pa-md) {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
    }
  }
}

// Bottom pagination
.bottom-pagination {
  display: flex;
  justify-content: center;
  padding: 8px 16px;
  background: #0d0d0d;
  border-top: 1px solid rgba(255, 255, 255, 0.06);

  :deep(.q-btn) {
    min-width: 36px;
    min-height: 36px;
  }
}

// Input styling overrides
.search-input,
.type-select,
.round-input,
.server-select {
  :deep(.q-field__control) {
    height: 40px;
  }

  :deep(.q-field__native),
  :deep(.q-field__prefix),
  :deep(.q-field__input) {
    color: #ddd;
  }

  :deep(.q-field__control::before) {
    border-color: rgba(255, 255, 255, 0.1);
  }
}

// Hide number input spinners
.round-input :deep(input[type='number']) {
  -moz-appearance: textfield;
  appearance: textfield;

  &::-webkit-outer-spin-button,
  &::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }
}
</style>

<script>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { router } from '@inertiajs/vue3'
import {
  ionClose,
  ionFilter,
  ionGameController,
  ionSearch,
  ionServer,
} from '@quasar/extras/ionicons-v6'
import axios from 'axios'
import LogViewer from './Partials/LogViewer.vue'

export default {
  components: {
    LogViewer,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Log Search' }, () => page),

  setup() {
    return {
      ionSearch,
      ionClose,
      ionFilter,
      ionGameController,
      ionServer,
    }
  },

  data() {
    return {
      searchQuery: '',
      selectedTypes: [],
      roundFilter: null,
      selectedServer: null,
      logs: [],
      loading: false,
      loadingTypes: false,
      loadingServers: false,
      hasSearched: false,
      searchError: null,
      totalResults: 0,
      currentPage: 1,
      perPage: 100,
      searchDuration: 0,
      logTypeOptions: [],
      serverOptions: [],
      maxResults: 10000,
      initialLoadDone: false,
    }
  },

  computed: {
    canSearch() {
      return this.searchQuery && this.searchQuery.trim().length >= 3
    },

    totalPages() {
      if (this.totalResults === 0) return 0
      return Math.ceil(Math.min(this.totalResults, this.maxResults) / this.perPage)
    },

    logTypesForViewer() {
      const types = [...new Set(this.logs.map((log) => log.type))].sort()
      return types.map((type) => ({
        label: type,
        value: type,
      }))
    },

    emptyMessage() {
      if (this.searchError) {
        return this.searchError
      }
      return 'No logs found matching your search criteria.'
    },

    typeDisplayValue() {
      if (!this.selectedTypes || this.selectedTypes.length === 0) {
        return 'All types'
      }
      if (this.selectedTypes.length === 1) {
        return this.selectedTypes[0]
      }
      return `${this.selectedTypes.length} types`
    },
  },

  created() {
    this.loadLogTypes()
    this.loadServers()
    this.loadUrlParams()
  },

  methods: {
    async loadLogTypes() {
      this.loadingTypes = true
      try {
        const response = await axios.get(route('admin.logs.types'))
        this.logTypeOptions = response.data.map((type) => ({
          label: type,
          value: type,
        }))
      } catch (e) {
        console.error('Failed to load log types:', e)
      }
      this.loadingTypes = false
    },

    async loadServers() {
      this.loadingServers = true
      try {
        const response = await axios.get(route('web.game-servers.index'))
        this.serverOptions = response.data.data.map((server) => ({
          label: server.short_name || server.name,
          value: server.server_id,
        }))
      } catch (e) {
        console.error('Failed to load servers:', e)
      }
      this.loadingServers = false
    },

    loadUrlParams() {
      const url = new URL(window.location.href)
      const params = url.searchParams

      // Parse filters using the standard filters[key]=value format
      params.forEach((value, key) => {
        const match = key.match(/^filters\[(.+)\]$/)
        if (match) {
          const filterKey = match[1]
          if (filterKey === 'search') {
            this.searchQuery = value
          } else if (filterKey === 'type') {
            this.selectedTypes = value.split(',').filter(Boolean)
          } else if (filterKey === 'round') {
            this.roundFilter = value
          } else if (filterKey === 'server') {
            this.selectedServer = value
          }
        }
      })

      if (params.has('page')) {
        this.currentPage = parseInt(params.get('page')) || 1
      }

      // Auto-search if we have a query from URL
      if (this.canSearch) {
        this.performSearch()
      }

      this.initialLoadDone = true
    },

    setUrlParams() {
      const urlSearch = new URLSearchParams()

      // Use standard filters[key]=value format
      if (this.searchQuery) {
        urlSearch.set('filters[search]', this.searchQuery.trim())
      }
      if (this.selectedTypes && this.selectedTypes.length > 0) {
        urlSearch.set('filters[type]', this.selectedTypes.join(','))
      }
      if (this.roundFilter) {
        urlSearch.set('filters[round]', this.roundFilter)
      }
      if (this.selectedServer) {
        urlSearch.set('filters[server]', this.selectedServer)
      }
      if (this.currentPage > 1) {
        urlSearch.set('page', this.currentPage)
      }

      // Decode URI to show brackets properly (same as BaseTable)
      const newParams = decodeURI(urlSearch.toString())
      let newUrl = window.location.pathname
      if (newParams) {
        newUrl += `?${newParams}`
      }

      // Use Inertia router.push for history support (same as BaseTable)
      if (window.location.pathname + window.location.search !== newUrl) {
        router.push({ url: newUrl, preserveState: true, preserveScroll: true })
      }
    },

    async performSearch() {
      if (!this.canSearch) {
        this.searchError = 'Please enter at least 3 characters to search.'
        return
      }

      this.loading = true
      this.searchError = null
      this.hasSearched = true

      const startTime = performance.now()

      try {
        const params = {
          filters: {
            search: this.searchQuery.trim(),
          },
          page: this.currentPage,
          per_page: this.perPage,
        }

        // Ensure selectedTypes is an array before checking length
        const types = Array.isArray(this.selectedTypes) ? this.selectedTypes : []
        if (types.length > 0) {
          params.filters.type = types
        }

        if (this.roundFilter) {
          params.filters.round = this.roundFilter
        }

        if (this.selectedServer) {
          params.filters.server = this.selectedServer
        }

        const response = await axios.get(route('admin.logs.search-logs'), { params })
        const data = response.data

        // Process logs to append ckey to player elements
        const poptsRegex =
          /(<a href='.*?\?src=%admin_ref%;action=adminplayeropts;targetckey=(.*?)' title='Player Options'>)(.*?)(<\/a>)/gi
        this.logs = data.data.map((log) => {
          if (log.source) {
            log.source = log.source.replaceAll(poptsRegex, '$1$3 ($2)$4')
          }
          if (log.message) {
            log.message = log.message.replaceAll(poptsRegex, '$1$3 ($2)$4')
          }
          return log
        })

        this.totalResults = data.total
        this.currentPage = data.current_page

        if (data.message) {
          this.searchError = data.message
        }

        // Update URL with current search state
        this.setUrlParams()
      } catch (e) {
        console.error('Search failed:', e)
        this.searchError = 'An error occurred while searching. Please try again.'
        this.logs = []
        this.totalResults = 0
      }

      this.searchDuration = Math.round(performance.now() - startTime)
      this.loading = false
    },

    onPageChange() {
      this.performSearch()
    },

    clearSearch() {
      this.searchQuery = ''
      this.selectedTypes = []
      this.roundFilter = null
      this.selectedServer = null
      this.logs = []
      this.hasSearched = false
      this.searchError = null
      this.totalResults = 0
      this.currentPage = 1

      // Clear URL params using Inertia router for history support
      if (window.location.search) {
        router.push({ url: window.location.pathname, preserveState: true, preserveScroll: true })
      }
    },
  },
}
</script>
