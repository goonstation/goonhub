<template>
  <div>
    <q-table
      ref="tableRef"
      v-show="!(lazyFetching && firstLoad)"
      v-bind="$attrs"
      v-model:pagination="_pagination"
      v-model:selected="selected"
      :rows="rows"
      :columns="_columns"
      :loading="loading"
      :rows-per-page-options="rowsPerPageOptions"
      :visible-columns="visibleColumns"
      :selection="selection"
      row-key="id"
      separator="none"
      binary-state-sort
      @request="onRequest"
      @selection="handleSelection"
      @vue:mounted="onTableMounted"
    >
      <template v-for="(_, name) in $slots" v-slot:[name]="slotData">
        <slot :name="name" v-bind="slotData" />
      </template>

      <template v-slot:top v-if="!hideTop">
        <div class="flex full-width gap-xs-sm bg-dark q-pa-md rounded-borders items-start no-wrap">
          <slot name="top-left" />

          <div v-if="showGridFilters" class="gh-grid-filters flex items-start gap-xs-sm">
            <!-- sort button/menu -->
            <q-btn color="grey-9" class="text-sm" padding="xs sm" dense no-caps unelevated>
              <q-icon :name="ionSwapVertical" size="xs" class="q-mr-sm" /> {{ sortedByLabel }}
              <q-menu :offset="[0, 10]">
                <q-list style="min-width: 100px">
                  <q-item>
                    <q-toggle
                      v-model="_pagination.descending"
                      :label="_pagination.descending ? 'Descending' : 'Ascending'"
                      @update:model-value="onSortChange({ descending: $event })"
                    />
                  </q-item>
                  <q-separator />
                  <q-item class="column">
                    <template v-for="col in columns">
                      <div v-if="col.sortable" :key="`sort-filter-${col.name}`">
                        <q-radio
                          v-model="_pagination.sortBy"
                          :val="col.name"
                          :label="col.label"
                          @update:model-value="onSortChange({ column: $event })"
                          size="sm"
                        />
                      </div>
                    </template>
                  </q-item>
                </q-list>
              </q-menu>
            </q-btn>

            <template v-for="(filter, name) in filters">
              <grid-header-filter
                v-if="isFilterSet(filter)"
                :key="`filter-${name}`"
                :column="columns.find((col) => col.name === name)"
                :filter="filter"
                :filter-option="filterOptions[name]?.option"
                @update="onFilterInput(name, $event)"
                @update:option="onFilterOptionSelect(name, $event)"
                @clear="filters[name] = null"
              >
              </grid-header-filter>
            </template>

            <q-btn class="text-sm" color="grey-9" padding="xs sm" :icon="ionAdd" dense unelevated>
              <q-tooltip anchor="center right" self="center left"> Add a filter </q-tooltip>
              <q-menu :offset="[0, 10]">
                <q-markup-table class="q-py-sm" separator="none" flat dense>
                  <tbody>
                    <template v-for="col in columns">
                      <tr v-if="col.filterable !== false" :key="`add-filter-${col.name}`">
                        <td style="width: 1px">
                          <q-chip color="grey-9" square>{{ col.label }}</q-chip>
                        </td>
                        <td>
                          <table-filter
                            :model-value="filters[col.name]"
                            @update:modelValue="onFilterInput(col.name, $event)"
                            @update:option="onFilterOptionSelect(col.name, $event)"
                            @clear="filters[col.name] = null"
                            :filter-type="col.filter?.type || 'text'"
                          />
                        </td>
                      </tr>
                    </template>
                  </tbody>
                </q-markup-table>
                <q-separator />
                <div class="row q-pa-sm">
                  <q-space />
                  <q-btn v-close-popup>Close</q-btn>
                </div>
              </q-menu>
            </q-btn>
          </div>

          <q-space />

          <div class="flex items-center gap-xs-sm">
            <slot name="header-right" />

            <q-btn
              v-if="routes.create"
              @click="router.visit(getRoute(routes.create))"
              color="primary"
              text-color="dark"
            >
              {{ createButtonText }}
            </q-btn>
          </div>

          <q-btn :icon="ionSettings" class="q-ml-md" dense unelevated>
            <q-tooltip>Table Settings</q-tooltip>
            <q-menu :offset="[0, 10]">
              <q-markup-table class="q-py-none" flat dense>
                <tbody>
                  <tr v-if="hasTimestamps && !noTimestampToggle">
                    <td>Toggle Timestamps</td>
                    <td>
                      <q-toggle v-model="showTimestamps" :icon="ionCalendar" />
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2" class="text-right">
                      <q-btn color="primary" text-color="dark" class="q-my-sm" @click="reset">
                        Reset
                      </q-btn>
                    </td>
                  </tr>
                </tbody>
              </q-markup-table>
            </q-menu>
          </q-btn>
        </div>

        <slot name="header-bottom" />
      </template>

      <template v-slot:header="props">
        <q-tr :props="props">
          <q-th v-if="canSelect" class="q-table--col-auto-width">
            <q-checkbox v-model="props.selected" dense />
          </q-th>
          <q-th v-for="col in props.cols" :key="col.name" :props="props" class="text-no-wrap">
            {{ col.label }}
          </q-th>
        </q-tr>
        <q-tr v-if="canSelect || !gridFilters" no-hover>
          <q-th v-if="canSelect" />
          <template v-if="!gridFilters">
            <q-th v-for="col in props.cols" :key="col.name">
              <table-filter
                v-if="col.filterable !== false"
                :model-value="filters[col.name]"
                @update:modelValue="onFilterInput(col.name, $event)"
                @clear="filters[col.name] = null"
                :filter-type="col.filter?.type || 'text'"
              />
            </q-th>
          </template>
        </q-tr>
      </template>

      <template v-slot:body="props">
        <slot name="body-prepend" :props="props" />
        <q-tr
          @click.capture="onRowClick(props)"
          :props="props"
          :class="{ 'clickable-row': clickableRows }"
          :style="props.rowIndex % 2 === 0 ? '' : 'background-color: rgba(255, 255, 255, 0.02);'"
        >
          <q-td v-if="canSelect">
            <q-checkbox
              :model-value="props.selected"
              @update:model-value="
                (val, evt) => {
                  Object.getOwnPropertyDescriptor(props, 'selected').set(val, evt)
                }
              "
              dense
            />
          </q-td>
          <q-td v-for="col in props.cols" :key="col.name" :props="props">
            <slot
              v-if="$slots[`cell-content-${col.name}`]"
              :name="`cell-content-${col.name}`"
              :props="props"
              :col="col"
            />
            <template v-else>
              <template v-if="col.name === 'actions'">
                <q-btn-dropdown @click="$event.stopPropagation()" menu-self="top middle" flat dense>
                  <q-list class="action-dropdown" dense>
                    <q-item
                      v-if="routes.view"
                      @click="router.visit(getRoute(routes.view, props.row))"
                      clickable
                      v-close-popup
                    >
                      <q-item-section avatar><q-icon :name="ionEye" /></q-item-section>
                      <q-item-section>View</q-item-section>
                    </q-item>
                    <q-item
                      v-if="routes.edit"
                      @click="router.visit(getRoute(routes.edit, props.row))"
                      clickable
                      v-close-popup
                    >
                      <q-item-section avatar><q-icon :name="ionPencil" /></q-item-section>
                      <q-item-section>Edit</q-item-section>
                    </q-item>
                    <q-item
                      v-if="routes.delete"
                      @click="openConfirmDelete(props.row)"
                      clickable
                      v-close-popup
                    >
                      <q-item-section avatar><q-icon :name="ionTrash" /></q-item-section>
                      <q-item-section>Delete</q-item-section>
                    </q-item>
                  </q-list>
                </q-btn-dropdown>
              </template>
              <template v-else-if="col.name === 'id'">
                <Link v-if="routes.view" :href="getRoute(routes.view, props.row)">
                  {{ col.value }}
                </Link>
                <template v-else>{{ col.value }}</template>
              </template>
              <template v-else-if="col.cell?.format">
                <table-format :model-value="col.value" :format-type="col.cell.format || 'text'" />
              </template>
              <template v-else>
                {{ col.value }}
              </template>
            </template>
          </q-td>
        </q-tr>
        <slot name="body-append" :props="props" />
      </template>

      <template v-slot:bottom="props">
        <slot name="bottom-left" :props="props" />
        <q-btn
          v-if="hasMultiDelete && selected.length"
          @click="confirmMultiDelete = true"
          color="negative"
          outline
        >
          Delete {{ selected.length }} item<template v-if="selected.length !== 1">s</template>
        </q-btn>
        <q-space />
        <div v-if="!hidePagination" class="flex items-center">
          <div class="flex items-center q-mr-sm">
            Records per page:
            <q-select
              v-model="_pagination.rowsPerPage"
              :options="rowsPerPageOptions"
              @update:model-value="updateTable"
              class="q-ml-sm"
              borderless
              dense
              options-dense
            />
          </div>
          <q-pagination
            v-if="!(props.pagesNumber === 1 && props.pagesNumber === _pagination.page)"
            v-model="_pagination.page"
            :max="props.pagesNumber"
            @update:model-value="onPageChange"
            color="grey"
            size="12px"
            input
            round
          />
        </div>
      </template>
    </q-table>

    <template v-if="lazyFetching && firstLoad">
      <table-skeleton
        v-if="!Object.keys($attrs).includes('grid')"
        :columns="_columns.filter((c) => visibleColumns.includes(c.name))"
        :rows="_pagination.rowsPerPage"
        :dense="Object.keys($attrs).includes('dense')"
        :options="skeletonOptions"
        :grid-filters="showGridFilters"
        class="flex flex-grow"
      />
    </template>

    <q-dialog v-if="routes.delete" v-model="confirmDelete">
      <q-card flat bordered>
        <q-card-section class="row items-center no-wrap">
          <q-avatar :icon="ionInformationCircleOutline" color="negative" text-color="dark" />
          <span class="q-ml-sm">
            <slot name="delete-confirm" :props="{ item: deletingItem }">
              Are you sure you want to delete this?
            </slot>
          </span>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn flat label="Confirm" color="negative" @click="deleteItem" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-if="hasMultiDelete" v-model="confirmMultiDelete">
      <q-card flat bordered>
        <q-card-section class="row items-center no-wrap">
          <q-avatar :icon="ionInformationCircleOutline" color="negative" text-color="dark" />
          <span class="q-ml-sm">
            Are you sure you want to delete {{ selected.length }} item<template
              v-if="selected.length !== 1"
              >s</template
            >
          </span>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn flat label="Confirm" color="negative" @click="deleteMultiItems" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<style lang="scss" scoped>
:deep(.q-table__top) {
  padding: 0 4px;
}

.action-dropdown {
  .q-item__section--avatar {
    min-width: 0;
  }
}

.clickable-row {
  cursor: pointer;
  transition: background-color 200ms;

  &:hover,
  &:focus {
    background-color: rgba($primary, 0.25) !important;
  }
}
</style>

<script>
import TableSkeleton from '@/Components/Skeletons/Table.vue'
import TableFilter from '@/Components/TableFilters/BaseFilter.vue'
import TableFormat from '@/Components/TableFormats/BaseFormat.vue'
import { router } from '@inertiajs/vue3'
import {
  ionAdd,
  ionCalendar,
  ionCheckmark,
  ionClose,
  ionEye,
  ionInformationCircleOutline,
  ionPencil,
  ionSettings,
  ionSwapVertical,
  ionTrash,
} from '@quasar/extras/ionicons-v6'
import axios from 'axios'
import { isEmpty, isEqual, merge } from 'lodash'
import { toRaw } from 'vue'
import GridHeaderFilter from './Partials/GridHeaderFilter.vue'

export default {
  setup() {
    return {
      router,
      ionCalendar,
      ionCheckmark,
      ionClose,
      ionSwapVertical,
      ionAdd,
      ionEye,
      ionPencil,
      ionTrash,
      ionSettings,
      ionInformationCircleOutline,
    }
  },

  components: {
    TableSkeleton,
    TableFilter,
    TableFormat,
    GridHeaderFilter,
  },

  props: {
    propKey: {
      type: String,
      default: '',
    },
    initial: {
      type: Object,
      default: () => ({}),
    },
    routes: {
      type: Object,
      default: () => ({}),
    },
    columns: {
      type: Array,
      default: () => [],
    },
    search: {
      type: Object,
      default: () => ({}),
    },
    pagination: {
      type: Object,
      default: () => ({}),
    },
    rowsPerPageOptions: {
      type: Array,
      default: () => [3, 5, 7, 10, 15, 20, 25, 30, 50],
    },
    hideColumns: {
      type: Array,
      default: () => [],
    },
    showColumns: {
      type: Array,
      default: () => [],
    },
    noTimestampToggle: {
      type: Boolean,
      default: false,
    },
    noFilters: {
      type: Boolean,
      default: false,
    },
    gridFilters: {
      type: Boolean,
      default: false,
    },
    createButtonText: {
      type: String,
      default: 'Create',
    },
    selection: {
      type: String,
      default: 'none',
    },
    extraParams: {
      type: Object,
      default: () => ({}),
    },
    hideTop: {
      type: Boolean,
      default: false,
    },
    hidePagination: {
      type: Boolean,
      default: false,
    },
    fetchOnLoad: {
      type: Boolean,
      default: false,
    },
    noRowActions: {
      type: Boolean,
      default: false,
    },
    clickableRows: {
      type: Boolean,
      default: false,
    },
    skeletonOptions: {
      type: Object,
      default: () => ({}),
    },
  },

  data() {
    return {
      rows: [],
      _columns: [],
      loading: false,
      firstLoad: true,
      _pagination: {
        sortBy: 'id',
        descending: true,
        page: 1,
        rowsPerPage: 15,
        rowsNumber: 0,
      },
      defaultPagination: {},
      defaultFilters: {},
      filters: {},
      filterOptions: {},
      errors: {},
      settingFiltersFromUrl: false,
      showTimestamps: false,
      timestampColumns: ['created_at', 'updated_at'],
      confirmDelete: false,
      confirmMultiDelete: false,
      deletingItem: null,
      selected: [],
      storedSelectedRow: null,
      scrollToTop: false,
    }
  },

  computed: {
    hasTimestamps() {
      return this.columns.some((column) => {
        return this.timestampColumns.includes(column.name)
      })
    },

    visibleColumns() {
      const visible = []
      for (const column of this.columns) {
        if (
          !this.showTimestamps &&
          this.timestampColumns.includes(column.name) &&
          !this.showColumns.includes(column.name)
        )
          // If we're not showing timestamps, and this is a timestamp, and we're not forcing it to show
          continue
        if (this.hideColumns.includes(column.name) && !this.showColumns.includes(column.name))
          continue
        visible.push(column.name)
      }
      return visible
    },

    showGridFilters() {
      return !this.noFilters && (Object.keys(this.$attrs).includes('grid') || this.gridFilters)
    },

    currentSortColumn() {
      return this.columns.find((column) => column.name === this._pagination.sortBy)
    },

    sortedByLabel() {
      if (!this.currentSortColumn) return
      const dir = this._pagination.descending ? 'descending' : 'ascending'
      return `Sorted by ${this.currentSortColumn.label.toLowerCase()} ${dir}`
    },

    hasActions() {
      if (this.noRowActions) return false
      let ret = false
      const actionRoutes = ['view', 'edit', 'delete']
      for (const route in this.routes) {
        if (actionRoutes.includes(route)) {
          ret = true
          break
        }
      }
      return ret
    },

    canSelect() {
      return this.selection !== 'none'
    },

    hasMultiDelete() {
      return !!this.routes.deleteMulti
    },

    lazyFetching() {
      return !!this.propKey
    },
  },

  created() {
    const mergedPagination = merge(this._pagination, this.pagination)
    this.defaultFilters = Object.assign({}, this.search)
    this.defaultPagination = Object.assign({}, mergedPagination)
    this._pagination = mergedPagination
  },

  mounted() {
    if (!this.loadUrlParams()) {
      if (this.fetchOnLoad || this.lazyFetching) this.updateTable()
    }
    this.$emit('loaded', { filters: this.filters })
  },

  methods: {
    async fetchFromServer(page, fetchCount, sortBy, descending) {
      const options = {
        params: {
          page,
          per_page: fetchCount,
          sort_by: sortBy,
          descending,
          filters: this.filters,
          ...this.extraParams,
        },
      }

      if (this.propKey) {
        options.headers = {
          'X-Inertia': true,
          'X-Inertia-Partial-Data': this.propKey,
          'X-Inertia-Partial-Component': this.$page.component,
          'X-Inertia-Version': this.$page.version,
        }
      }

      const res = await axios.get(this.routes.fetch, options)
      return this.propKey ? { data: res.data.props[this.propKey] } : res
    },

    async onRequest(tableProps) {
      const { page, rowsPerPage, sortBy, descending } = tableProps.pagination
      this.loading = true
      this.$emit('fetch-start')

      let data
      try {
        const res = await this.fetchFromServer(page, rowsPerPage, sortBy, descending)
        data = res.data
      } catch {
        this.loading = false
        this.firstLoad = false
        return
      }
      this.rows.splice(0, this.rows.length, ...data.data)

      this._pagination.page = data.current_page
      this._pagination.rowsPerPage = data.per_page
      this._pagination.sortBy = sortBy
      this._pagination.descending = descending
      this._pagination.rowsNumber = data.total
      this.setUrlParams()

      if (this.scrollToTop) {
        this.$refs.tableRef.$el.scrollIntoView({ behavior: 'smooth' })
        this.scrollToTop = false
      }

      this.loading = false
      this.firstLoad = false
      this.$emit('fetch-end', data)
    },

    loadUrlParams() {
      const url = new URL(window.location.href)
      const urlSearch = new URLSearchParams(url.search)
      const newFilters = Object.assign({}, this.filters)
      urlSearch.forEach((param, key) => {
        const match = key.match(/filters\[(.*?)\]/)
        if (match && match[1]) {
          newFilters[match[1]] = param
        } else if (key === 'page') this._pagination.page = parseInt(param)
        else if (key === 'sort_by') this._pagination.sortBy = param
        else if (key === 'descending') this._pagination.descending = param === 'true'
        else if (key === 'per_page') this._pagination.rowsPerPage = parseInt(param)
      })
      if (!isEqual(this.filters, newFilters)) {
        this.settingFiltersFromUrl = true
        this.filters = merge(this.filters, newFilters)
        return true
      }
      return false
    },

    setUrlParams() {
      const url = new URL(window.location.origin + window.location.pathname)
      const urlSearch = new URLSearchParams(url.search)

      if (this._pagination.page !== this.defaultPagination.page)
        urlSearch.append('page', this._pagination.page)
      if (this._pagination.sortBy !== this.defaultPagination.sortBy)
        urlSearch.append('sort_by', this._pagination.sortBy)
      if (this._pagination.descending !== this.defaultPagination.descending)
        urlSearch.append('descending', this._pagination.descending)
      if (this._pagination.rowsPerPage !== this.defaultPagination.rowsPerPage)
        urlSearch.append('per_page', this._pagination.rowsPerPage)

      for (const p in this.extraParams) {
        urlSearch.append(p, this.extraParams[p])
      }

      for (const p in this.filters) {
        const filter = this.filters[p]
        const propKey = `filters[${p}]`
        const filterExists = this.isFilterSet(filter)
        if (!filterExists && urlSearch.has(propKey)) {
          urlSearch.delete(propKey)
        } else if (filterExists) {
          urlSearch.append(propKey, filter)
        }
      }

      const newParams = decodeURI(urlSearch.toString())
      let newUrl = window.location.pathname
      if (newParams) {
        newUrl += `?${newParams}`
      }
      router.push({ url: newUrl, preserveState: true, preserveScroll: true })
    },

    isFilterSet(val) {
      if (typeof val === 'boolean' || typeof val === 'number') {
        return true
      }
      return !!val
    },

    onFiltersChange() {
      const options = {}
      for (const column in this.filterOptions) {
        const filterOption = this.filterOptions[column]
        if (this.filters[column] === filterOption.filterValue) {
          options[column] = filterOption
        }
      }
      this.filterOptions = options
      this.updateTable()
    },

    onFilterInput(col, val) {
      this.filters[col] = val
    },

    onFilterOptionSelect(col, val) {
      this.filterOptions[col] = {
        filterValue: this.filters[col],
        option: val,
      }
    },

    onSortChange({ column, descending }) {
      if (column) {
        this._pagination.sortBy = column
      }
      if (descending) {
        this._pagination.descending = descending
      }
      this.updateTable()
    },

    onPageChange() {
      this.scrollToTop = true
      this.updateTable()
    },

    getRoute(goToRoute, row) {
      if (!row) return goToRoute
      return goToRoute.replace('_id', row.id)
    },

    reset() {
      if (!isEmpty(this.filters)) {
        this.filters = Object.assign({}, this.defaultFilters)
      }
      this._pagination = Object.assign({}, this.defaultPagination)
      this.$emit('reset', { filters: this.filters })
    },

    openConfirmDelete(item) {
      this.deletingItem = item
      this.confirmDelete = true
    },

    async deleteItem() {
      const deleteRoute = this.getRoute(this.routes.delete, this.deletingItem)
      try {
        const response = await axios.delete(deleteRoute)
        this.$q.notify({
          message: response.data.message || 'Item successfully deleted.',
          color: 'positive',
        })
      } catch {
        this.deletingItem = null
        this.confirmDelete = false
        this.$q.notify({
          message: 'Failed to delete item, please try again.',
          color: 'negative',
        })
        return
      }

      this.deletingItem = null
      this.confirmDelete = false
      this.updateTable()
    },

    async deleteMultiItems() {
      const deleteRoute = this.getRoute(this.routes.deleteMulti)
      try {
        const response = await axios.delete(deleteRoute, {
          data: {
            ids: this.selected.map((item) => item.id),
          },
        })
        this.$q.notify({
          message: response.data.message || 'Items successfully deleted.',
          color: 'positive',
        })
      } catch {
        this.confirmMultiDelete = false
        this.$q.notify({
          message: 'Failed to delete items, please try again.',
          color: 'negative',
        })
        return
      }

      this.selected = []
      this.confirmMultiDelete = false
      this.updateTable()
    },

    // Expands selection functionality to enable shift/ctrl modifiers for selecting ranges
    handleSelection({ rows, added, evt }) {
      // ignore selection change from header if not from a direct click event
      if (rows.length !== 1 || evt === void 0) {
        return
      }

      const oldSelectedRow = this.storedSelectedRow
      const [newSelectedRow] = rows
      const { shiftKey } = evt

      if (shiftKey !== true) {
        this.storedSelectedRow = newSelectedRow
      }

      // wait for the default selection to be performed
      this.$nextTick(() => {
        if (shiftKey === true) {
          const tableRows = this.$refs.tableRef.filteredSortedRows
          let firstIndex = tableRows.indexOf(oldSelectedRow)
          let lastIndex = tableRows.indexOf(newSelectedRow)

          if (firstIndex < 0) {
            firstIndex = 0
          }

          if (firstIndex > lastIndex) {
            ;[firstIndex, lastIndex] = [lastIndex, firstIndex]
          }

          const rangeRows = tableRows.slice(firstIndex, lastIndex + 1)
          // we need the original row object so we can match them against the rows in range
          const selectedRows = this.selected.map(toRaw)

          this.selected =
            added === true
              ? selectedRows.concat(rangeRows.filter((row) => selectedRows.includes(row) === false))
              : selectedRows.filter((row) => rangeRows.includes(row) === false)
        }
      })
    },

    updateTable() {
      this.$refs.tableRef.requestServerInteraction()
    },

    onRowClick(props) {
      this.$emit('row-click', props.row)
    },

    onTableMounted() {
      const init = this.propKey ? this.$page.props[this.propKey] : this.initial

      // For an initial server-built packet of data
      if (!isEmpty(init)) {
        this.rows = init.data
        // if (this.initial.current_page > 1) {
        //   mergedPagination.page = this.initial.current_page
        // }
        // mergedPagination.rowsPerPage = this.initial.per_page || 15
        this._pagination.rowsNumber = init.total
      }
    },
  },

  watch: {
    columns: {
      deep: true,
      immediate: true,
      handler(newColumns) {
        newColumns = Object.assign([], newColumns)
        if (this.hasActions) {
          newColumns.unshift({
            name: 'actions',
            label: '',
            field: 'actions',
            required: true,
            headerClasses: 'q-table--col-auto-width',
            filterable: false,
            sortable: false,
          })
        }

        newColumns.forEach((column) => (this.filters[column.name] = null))
        this._columns = newColumns
      },
    },

    filters: {
      deep: true,
      handler() {
        // Skip a server fetch if we're setting filters from the URL
        // Because we can assume our initial data from the server is already filtered
        if (this.settingFiltersFromUrl && Object.keys(this.initial).length) {
          this.settingFiltersFromUrl = false
          return
        }
        this.onFiltersChange()
      },
    },

    search: {
      deep: true,
      handler(val) {
        this.filters = merge(this.filters, val)
      },
    },

    extraParams: {
      deep: true,
      handler() {
        this.updateTable()
      },
    },

    '$page.props.errors.table': {
      immediate: true,
      handler(val) {
        this.errors = val
        if (!val || !Object.keys(val).length) return
        for (const error of Object.values(val)) {
          this.$q.notify({
            message: error,
            color: 'negative',
          })
        }
      },
    },
  },
}
</script>
