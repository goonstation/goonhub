<template>
  <div>
    <q-table
      ref="tableRef"
      v-show="!(lazyFetching && firstLoad)"
      v-bind="$attrs"
      v-model:pagination="internalPagination"
      v-model:selected="selectedModel"
      :rows="rows"
      :columns="internalColumns"
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
        <div
          class="flex full-width gap-xs-sm q-pa-md items-start no-wrap relative"
          style="z-index: 2"
          :class="{ 'bg-dark': !transparent }"
          :style="{
            'border-bottom': showGridFiltersActions ? '1px solid var(--q-dark-page)' : 'none',
          }"
        >
          <slot name="top-left" />

          <div v-if="showGridFilters" class="gh-grid-filters flex items-start gap-xs-sm">
            <!-- <q-checkbox
              v-if="canSelect"
              :model-value="allSelected"
              @click="handleSelectAll"
              class="q-mr-sm"
              style="margin-top: 6px"
              dense
            /> -->

            <!-- sort button/menu -->
            <q-btn
              :color="transparent ? 'grey-10' : 'grey-9'"
              class="text-sm"
              padding="xs sm"
              dense
              no-caps
              unelevated
            >
              <q-icon :name="ionSwapVertical" size="xs" class="q-mr-sm" /> {{ sortedByLabel }}
              <q-menu :offset="[0, 10]">
                <q-list style="min-width: 100px">
                  <q-item>
                    <q-toggle
                      v-model="internalPagination.descending"
                      :label="internalPagination.descending ? 'Descending' : 'Ascending'"
                      @update:model-value="onSortChange({ descending: $event })"
                    />
                  </q-item>
                  <q-separator />
                  <q-item class="column">
                    <template v-for="col in columns">
                      <div v-if="col.sortable" :key="`sort-filter-${col.name}`">
                        <q-radio
                          v-model="internalPagination.sortBy"
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
                :color="transparent ? 'grey-10' : 'grey-9'"
                @update="onFilterInput(name, $event)"
                @update:option="onFilterOptionSelect(name, $event)"
                @clear="filters[name] = null"
              >
              </grid-header-filter>
            </template>

            <q-btn
              :color="transparent ? 'grey-10' : 'grey-9'"
              class="text-sm"
              padding="xs sm"
              :icon="ionAdd"
              dense
              unelevated
            >
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
            <q-menu :offset="[0, 10]" class="shadow-0 bordered border-opacity-20">
              <q-list>
                <q-item v-if="hasTimestamps && !noTimestampToggle">
                  <q-item-section>
                    <q-item-label class="text-no-wrap">Toggle Timestamps</q-item-label>
                  </q-item-section>
                  <q-item-section side>
                    <q-toggle v-model="showTimestamps" :icon="ionCalendar" />
                  </q-item-section>
                </q-item>
                <q-item>
                  <q-btn
                    color="primary"
                    text-color="dark"
                    class="full-width q-my-xs"
                    @click="reset"
                  >
                    Reset
                  </q-btn>
                </q-item>
              </q-list>
            </q-menu>
          </q-btn>
        </div>

        <div class="full-width">
          <Vue3SlideUpDown :model-value="showGridFiltersActions" :duration="150">
            <div
              :class="{ 'bg-dark q-pb-md': !transparent }"
              :style="[!transparent && { marginTop: '-5px', paddingTop: '21px' }]"
              class="flex gap-xs-sm q-px-md rounded-borders items-start no-wrap"
            >
              <q-btn
                v-if="hasMultiEdit"
                :to="getRoute(routes.editMulti, { selected: selectedModel })"
                color="primary"
                size="sm"
                outline
              >
                Edit {{ selectedModel.length }} item<template v-if="selectedModel.length !== 1"
                  >s</template
                >
              </q-btn>
              <q-btn
                v-if="hasMultiDelete"
                @click="confirmMultiDelete = true"
                color="negative"
                size="sm"
                outline
              >
                Delete {{ selectedModel.length }} item<template v-if="selectedModel.length !== 1"
                  >s</template
                >
              </q-btn>

              <slot name="grid-filters-actions" :props="{ selected: selectedModel }" />
            </div>
          </Vue3SlideUpDown>
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
                v-bind="col.filter?.options || {}"
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
          @click="onRowClick(props)"
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
                <q-btn-dropdown @click.stop menu-self="top middle" flat dense>
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
          v-if="hasMultiEdit && selectedModel.length"
          :to="getRoute(routes.editMulti, { selected: selectedModel })"
          color="primary"
          outline
        >
          Edit {{ selectedModel.length }} item<template v-if="selectedModel.length !== 1"
            >s</template
          >
        </q-btn>
        <q-btn
          v-if="hasMultiDelete && selectedModel.length"
          @click="confirmMultiDelete = true"
          color="negative"
          outline
        >
          Delete {{ selectedModel.length }} item<template v-if="selectedModel.length !== 1"
            >s</template
          >
        </q-btn>
        <q-space />
        <div v-if="!hidePagination" class="flex items-center">
          <div class="flex items-center q-mr-sm">
            Records per page:
            <q-select
              v-model="internalPagination.rowsPerPage"
              :options="rowsPerPageOptions"
              @update:model-value="updateTable"
              class="q-ml-sm"
              borderless
              dense
              options-dense
            />
          </div>
          <!-- Simple pagination: only show prev/next buttons without page numbers -->
          <template v-if="isSimplePagination">
            <q-btn
              :disable="internalPagination.page <= 1"
              @click="
                () => {
                  internalPagination.page--
                  onPageChange()
                }
              "
              :icon="ionChevronBack"
              color="grey"
              size="sm"
              round
              flat
              dense
            />
            <span class="q-mx-sm text-grey">Page {{ internalPagination.page }}</span>
            <q-btn
              :disable="props.pagesNumber <= internalPagination.page"
              @click="
                () => {
                  internalPagination.page++
                  onPageChange()
                }
              "
              :icon="ionChevronForward"
              color="grey"
              size="sm"
              round
              flat
              dense
            />
          </template>
          <!-- Full pagination with page numbers when total is known -->
          <q-pagination
            v-else-if="!(props.pagesNumber === 1 && props.pagesNumber === internalPagination.page)"
            v-model="internalPagination.page"
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
        :columns="internalColumns.filter((c) => visibleColumns.includes(c.name))"
        :rows="internalPagination.rowsPerPage"
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
            Are you sure you want to delete {{ selectedModel.length }} item<template
              v-if="selectedModel.length !== 1"
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
  ionChevronBack,
  ionChevronForward,
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
import { Vue3SlideUpDown } from 'vue3-slide-up-down'
import GridHeaderFilter from './Partials/GridHeaderFilter.vue'

export default {
  emits: [
    'loaded',
    'fetch-start',
    'fetch-end',
    'reset',
    'row-click',
    'update:selected',
    'loaded-url-params',
  ],

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
      ionChevronBack,
      ionChevronForward,
    }
  },

  components: {
    TableSkeleton,
    TableFilter,
    TableFormat,
    GridHeaderFilter,
    Vue3SlideUpDown,
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
      validator(value) {
        return ['none', 'single', 'multiple'].includes(value)
      },
    },
    selected: {
      type: Array,
      default: () => [],
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
    transparent: {
      type: Boolean,
      default: false,
    },
  },

  data() {
    return {
      rows: [],
      internalColumns: [],
      loading: false,
      firstLoad: true,
      internalPagination: {
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
      selectedInternal: null,
      storedSelectedRow: null,
      scrollToTop: false,
      isSimplePagination: false,
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

    showGridFiltersActions() {
      return this.showGridFilters && this.selectedModel.length > 0
    },

    currentSortColumn() {
      return this.columns.find((column) => column.name === this.internalPagination.sortBy)
    },

    sortedByLabel() {
      if (!this.currentSortColumn) return
      const dir = this.internalPagination.descending ? 'descending' : 'ascending'
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

    selectedModel: {
      get() {
        return !this.selectedInternal ? this.selected : this.selectedInternal
      },
      set(val) {
        this.selectedInternal = val
        this.$emit('update:selected', val)
      },
    },

    canSelect() {
      return this.selection !== 'none'
    },

    allSelected() {
      if (this.selectedModel.length === 0) return false
      return this.rows.every((row) => this.selectedModel.includes(row)) || null
    },

    hasMultiEdit() {
      return !!this.routes.editMulti
    },

    hasMultiDelete() {
      return !!this.routes.deleteMulti
    },

    lazyFetching() {
      return !!this.propKey
    },
  },

  created() {
    const mergedPagination = merge(this.internalPagination, this.pagination)
    this.defaultFilters = Object.assign({}, this.search)
    this.defaultPagination = Object.assign({}, mergedPagination)
    this.internalPagination = mergedPagination
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
          filters: {
            ...this.filters,
            sort: sortBy,
            order: descending ? 'desc' : 'asc',
          },
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

      for (const idx in this.selectedModel) {
        const row = this.rows.find((r) => r.id === this.selectedModel[idx].id)
        if (row) this.selectedModel[idx] = row
      }

      this.internalPagination.page = data.current_page
      this.internalPagination.rowsPerPage = data.per_page
      this.internalPagination.sortBy = sortBy
      this.internalPagination.descending = descending

      // Handle both LengthAwarePaginator (has total) and SimplePaginator (no total)
      if (data.total !== undefined) {
        this.internalPagination.rowsNumber = data.total
        this.isSimplePagination = false
      } else {
        // SimplePaginator: estimate rowsNumber based on whether there are more pages
        // This allows q-table to show next/prev navigation without knowing total
        const hasMore = data.next_page_url !== null && data.next_page_url !== undefined
        const currentCount = data.current_page * data.per_page
        this.internalPagination.rowsNumber = hasMore ? currentCount + 1 : currentCount
        this.isSimplePagination = true
      }
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
          if (match[1] === 'sort') this.internalPagination.sortBy = param
          else if (match[1] === 'order') this.internalPagination.descending = param === 'desc'
          else newFilters[match[1]] = param
        } else if (key === 'page') this.internalPagination.page = parseInt(param)
        else if (key === 'per_page') this.internalPagination.rowsPerPage = parseInt(param)
      })
      if (!isEqual(this.filters, newFilters)) {
        this.settingFiltersFromUrl = true
        this.filters = merge(this.filters, newFilters)
        this.$emit('loaded-url-params', { filters: this.filters })
        return true
      }
      return false
    },

    setUrlParams() {
      const url = new URL(window.location.origin + window.location.pathname)
      const urlSearch = new URLSearchParams(url.search)

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

      if (this.internalPagination.page !== this.defaultPagination.page)
        urlSearch.append('page', this.internalPagination.page)
      if (this.internalPagination.sortBy !== this.defaultPagination.sortBy)
        urlSearch.append('filters[sort]', this.internalPagination.sortBy)
      if (this.internalPagination.descending !== this.defaultPagination.descending)
        urlSearch.append('filters[order]', this.internalPagination.descending ? 'desc' : 'asc')
      if (this.internalPagination.rowsPerPage !== this.defaultPagination.rowsPerPage)
        urlSearch.append('per_page', this.internalPagination.rowsPerPage)

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
        this.internalPagination.sortBy = column
      }
      if (descending) {
        this.internalPagination.descending = descending
      }
      this.updateTable()
    },

    onPageChange() {
      this.scrollToTop = true
      this.updateTable()
    },

    getRoute(goToRoute, row) {
      if (!row) return goToRoute
      return goToRoute?.replace('_id', row.id) || ''
    },

    reset() {
      if (!isEmpty(this.filters)) {
        this.filters = Object.assign({}, this.defaultFilters)
      }
      this.internalPagination = Object.assign({}, this.defaultPagination)
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
            ids: this.selectedModel.map((item) => item.id),
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

      this.selectedModel = []
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
          const selectedRows = this.selectedModel.map(toRaw)

          this.selectedModel =
            added === true
              ? selectedRows.concat(rangeRows.filter((row) => selectedRows.includes(row) === false))
              : selectedRows.filter((row) => rangeRows.includes(row) === false)
        }
      })
    },

    handleSelectAll() {
      if (this.allSelected || this.allSelected === null) {
        this.selectedModel = []
      } else {
        this.selectedModel = this.rows
      }
    },

    updateTable() {
      if (!this.$refs.tableRef) return
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

        // Handle both LengthAwarePaginator (has total) and SimplePaginator (no total)
        if (init.total !== undefined) {
          this.internalPagination.rowsNumber = init.total
          this.isSimplePagination = false
        } else {
          // SimplePaginator: estimate rowsNumber based on whether there are more pages
          const hasMore = init.next_page_url !== null && init.next_page_url !== undefined
          const currentCount = (init.current_page || 1) * (init.per_page || 30)
          this.internalPagination.rowsNumber = hasMore ? currentCount + 1 : currentCount
          this.isSimplePagination = true
        }
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
        this.internalColumns = newColumns
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
      handler(newVal, oldVal) {
        if (isEqual(newVal, oldVal)) return
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
