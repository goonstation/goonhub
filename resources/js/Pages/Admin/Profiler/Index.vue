<template>
  <div class="flex-grow column no-wrap">
    <q-card class="gh-card q-mb-md" flat>
      <div class="gh-card__header q-pa-md bordered">
        <span>Load Profile</span>
      </div>
      <q-card-section>
        <q-form class="row q-col-gutter-sm items-start" @submit="loadMain">
          <!-- TODO: Create an actual pathway for this data instead of this -->
          <q-input
            v-model="url"
            class="col-12 col-md"
            label="logs.goonhub.com profiler JSON URL"
            filled
            dense
          />
          <q-input v-model="username" class="col-6 col-md-2" label="Logs username" filled dense />
          <q-input
            v-model="password"
            class="col-6 col-md-2"
            label="Logs password"
            type="password"
            filled
            dense
          />
          <div class="col-12 col-md-auto flex gap-xs-sm">
            <q-btn
              type="submit"
              label="Load"
              color="primary"
              text-color="dark"
              :loading="loading"
              :disable="!canFetch(url)"
              unelevated
            />
            <q-btn label="Open File" color="primary" outline @click="openFile(setMain)" />
          </div>
        </q-form>
        <q-banner v-if="pendingLink && !canFetch(url)" class="bg-grey-9 q-mt-sm" dense rounded>
          Enter the logs.goonhub.com login and press Load to open the linked profile.
        </q-banner>
        <q-banner v-if="error" class="bg-negative text-white q-mt-sm" dense rounded>
          {{ error }}
        </q-banner>
      </q-card-section>
    </q-card>

    <q-card v-if="entries.length" class="gh-card flex-grow column no-wrap" flat>
      <div class="gh-card__header q-pa-md bordered gap-xs-md wrap">
        <span class="ellipsis" style="min-width: 0">{{ source }}</span>
        <q-badge color="grey-9" text-color="grey-4">
          {{ isSendmaps ? 'SendMaps' : 'Procs' }}
        </q-badge>
        <q-chip
          v-if="comparing"
          class="q-my-none"
          color="grey-9"
          text-color="grey-4"
          removable
          dense
          square
          @remove="clearBaseline"
        >
          vs {{ baselineSource }}
        </q-chip>
        <q-btn-dropdown v-else label="Compare" size="sm" color="primary" outline dense>
          <q-form class="q-pa-md column gap-xs-sm" style="min-width: 400px" @submit="loadBaseline">
            <q-input v-model="baseUrl" label="Different profiler URL" filled dense />
            <div class="flex gap-xs-sm justify-end">
              <q-btn label="Compare File" color="primary" outline @click="openFile(setBaseline)" />
              <q-btn
                type="submit"
                label="Load"
                color="primary"
                text-color="dark"
                :loading="loading"
                :disable="!canFetch(baseUrl)"
                unelevated
              />
            </div>
          </q-form>
        </q-btn-dropdown>
        <q-space />
        <q-tabs v-model="tab" dense inline-label no-caps>
          <q-tab name="summary" label="Summary" />
          <q-tab name="details" label="Details" />
        </q-tabs>
      </div>

      <profile-stats v-if="tab === 'summary'" :stats="stats" />

      <template v-else>
        <div class="q-px-md q-py-sm flex items-center gap-xs-md">
          <q-input
            v-model="search"
            class="col"
            style="min-width: 200px; max-width: 400px"
            :error="!!filterError"
            :error-message="filterError"
            hide-bottom-space
            clearable
            filled
            dense
          >
            <template #append>
              <q-icon :name="ionHelpCircleOutline" class="cursor-pointer">
                <q-tooltip class="text-body2" max-width="420px">
                  <div><code>-word</code> excludes matches.</div>
                  <div><code>a|b</code> matches either.</div>
                  <div><code>"two words"</code> matches exactly.</div>
                  <div><code>re:^/mob/.*Life$</code> matches a regex.</div>
                  <div>
                    <code>self&gt;5ms</code>, <code>pct&gt;1%</code>, <code>pct_delta&lt;0</code>,
                    etc. compare a column: {{ filterColumns }}.
                  </div>
                </q-tooltip>
              </q-icon>
            </template>
          </q-input>
          <q-select
            v-model="shownColumns"
            :clearable="customColumns"
            @clear="shownColumns = DEFAULT_COLUMNS"
            :options="columnOptions"
            label="Columns"
            style="min-width: 150px"
            display-value="Columns"
            multiple
            emit-value
            map-options
            filled
            dense
            options-dense
          />
          <q-checkbox v-model="merge" label="Merge overrides" dense> </q-checkbox>
          <q-space />
          <span class="text-caption text-grey-5">
            {{ $formats.number(rows.length) }} of {{ $formats.number(total) }}
            {{ isSendmaps ? 'entries' : 'procs' }}
          </span>
        </div>
        <div class="relative flex-grow">
          <q-table
            v-model:pagination="pagination"
            class="profiler-table absolute-full"
            :style="{ '--table-min-width': tableMinWidth }"
            :rows="rows"
            :columns="columns"
            :visible-columns="visibleColumns"
            row-key="id"
            column-sort-order="da"
            :virtual-scroll-item-size="26"
            :virtual-scroll-sticky-size-start="30"
            virtual-scroll
            binary-state-sort
            hide-bottom
            flat
            dense
          >
            <template #body-cell-name="props">
              <q-td :props="props" class="ellipsis">
                <span class="text-grey-6">{{ props.row.path }}</span>
                <span class="text-weight-bold">{{ props.row.proc }}</span>
                <span v-if="props.row.count > 1" class="text-grey-6 q-pl-xs">
                  (merged {{ props.row.count }})
                </span>
                <span class="float-right q-pl-sm">
                  <q-badge v-if="props.row.status === 'new'" color="negative" label="new" />
                  <q-badge v-else-if="props.row.status === 'gone'" color="grey-8" label="gone" />
                </span>
              </q-td>
            </template>
            <template #body-cell="props">
              <q-td :props="props">
                <span
                  v-if="props.col.kind === 'delta'"
                  :class="deltaClass(props.row[props.col.name])"
                >
                  {{ props.value }}
                </span>
                <template v-else-if="props.col.kind === 'time'">
                  <span
                    v-for="(part, i) in splitTime(props.row[props.col.name])"
                    :key="i"
                    :class="part.class"
                    >{{ part.text }}</span
                  >
                </template>
                <template v-else>{{ props.value }}</template>
              </q-td>
            </template>
          </q-table>
        </div>
      </template>
    </q-card>
  </div>
</template>

<style lang="scss" scoped>
.profiler-table {
  background: $dark;

  :deep(table) {
    table-layout: fixed;
    // Scroll sideways rather than squeeze the proc column when many columns are shown
    min-width: var(--table-min-width);
  }

  :deep(thead th) {
    position: sticky;
    top: 0;
    z-index: 1;
    background: $dark;
    font-weight: 600;

    &.sorted {
      color: $primary;
    }
  }

  :deep(td),
  :deep(th) {
    border-color: $dark-page;
  }

  :deep(.num-col) {
    font-family: monospace;
  }
}
</style>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { ionHelpCircleOutline } from '@quasar/extras/ionicons-v6'
import axios from 'axios'
import { computed, onMounted, ref, watch } from 'vue'
import ProfileStats from './Partials/ProfileStats.vue'
import useProfiler, { deltaClass, splitTime } from './profiler'

defineOptions({
  layout: (h, page) => h(DashboardLayout, { title: 'Profiler' }, () => page),
})

const USERNAME_KEY = 'profiler-logs-username'
const PASSWORD_KEY = 'profiler-logs-password'
const COLUMNS_KEY = 'profiler-columns'
const MERGE_KEY = 'profiler-merge'

// The view (profiles, filter, sort, tab) lives in the query string so a link reopens it
const params = new URLSearchParams(window.location.search)

const url = ref(params.get('url') || '')
const baseUrl = ref(params.get('base') || '')
const username = ref(localStorage.getItem(USERNAME_KEY) || '')
// Session storage, so the password is forgotten when the tab closes
const password = ref(sessionStorage.getItem(PASSWORD_KEY) || '')
const loading = ref(false)
const error = ref('')

const entries = ref([])
const source = ref('')
const mainUrl = ref(null)
const baseline = ref([])
const baselineSource = ref('')
const baselineUrl = ref(null)

const tab = ref(params.get('tab') || 'summary')
const search = ref(params.get('q') || '')
const pagination = ref({
  sortBy: params.get('sort') || 'self',
  descending: params.get('desc') !== '0',
  rowsPerPage: 0,
})

const DEFAULT_COLUMNS = ['self', 'total', 'over', 'value', 'calls']
const shownColumns = ref(JSON.parse(localStorage.getItem(COLUMNS_KEY)) ?? DEFAULT_COLUMNS)
const merge = ref(localStorage.getItem(MERGE_KEY) !== 'false')

const { isSendmaps, comparing, metric, columns, rows, stats, total, filterError } = useProfiler(
  entries,
  baseline,
  search,
  merge
)

const columnOptions = computed(() =>
  columns.value
    .filter((col) => !col.required && col.kind !== 'delta') // delta cols aren't picked
    .map((col) => ({ label: col.label, value: col.name }))
)
const visibleColumns = computed(() =>
  columns.value
    // A delta column shows alongside the column it compares
    .filter((col) => shownColumns.value.includes(col.of ?? col.name))
    .map((col) => col.name)
)
const filterColumns = computed(() =>
  columns.value
    .filter((col) => !col.required)
    .map((col) => col.name)
    .join(', ')
)
const tableMinWidth = computed(() => {
  const widths = columns.value
    .filter((col) => visibleColumns.value.includes(col.name))
    .map((col) => col.width)
  return `calc(${[...widths, '400px'].join(' + ')})`
})

watch(username, (val) => localStorage.setItem(USERNAME_KEY, val))
// Only a selection that differs from the default is saved, and clearing the select goes back to it
const customColumns = computed(
  () =>
    !!shownColumns.value &&
    (shownColumns.value.length !== DEFAULT_COLUMNS.length ||
      shownColumns.value.some((name) => !DEFAULT_COLUMNS.includes(name)))
)
watch(shownColumns, (val) => {
  if (!val) return
  if (customColumns.value) localStorage.setItem(COLUMNS_KEY, JSON.stringify(val))
  else localStorage.removeItem(COLUMNS_KEY)
})
watch(merge, (val) => localStorage.setItem(MERGE_KEY, String(val)))

watch(
  [
    mainUrl,
    baselineUrl,
    search,
    tab,
    () => pagination.value.sortBy,
    () => pagination.value.descending,
  ],
  () => {
    const view = {
      url: mainUrl.value,
      base: baselineUrl.value,
      q: search.value,
      tab: tab.value === 'summary' ? null : tab.value,
      sort: pagination.value.sortBy,
      desc: pagination.value.descending ? null : '0',
    }
    const pageUrl = new URL(window.location.href)
    pageUrl.search = new URLSearchParams(
      Object.entries(view).filter(([, value]) => value)
    ).toString()
    window.history.replaceState(window.history.state, '', pageUrl)
  }
)

const canFetch = (target) => !!(target && username.value && password.value)

function checkProfile(data) {
  if (
    !Array.isArray(data) ||
    !data.length ||
    !data.every((entry) => typeof entry?.name === 'string')
  ) {
    throw new Error('That is not world.Profile() JSON output.')
  }
  return data
}

function sortBy(column) {
  pagination.value = { ...pagination.value, sortBy: column, descending: true }
}

function setMain(data, name, from = null) {
  entries.value = checkProfile(data)
  source.value = name
  mainUrl.value = from
  clearBaseline()
  // Each profile starts sorted by self time, largest first; a shared link re-applies its own sort
  sortBy(metric.value)
}

function setBaseline(data, name, from = null) {
  checkProfile(data)
  if ('value' in data[0] !== isSendmaps.value) {
    throw new Error('The baseline must be the same kind of profile (procs or SendMaps).')
  }
  baseline.value = data
  baselineSource.value = name
  baselineUrl.value = from
}

function clearBaseline() {
  baseline.value = []
  baselineSource.value = ''
  baselineUrl.value = null
  if (pagination.value.sortBy?.endsWith('_delta')) sortBy(metric.value)
}

async function fetchInto(setter, target) {
  loading.value = true
  error.value = ''
  try {
    const response = await axios.post(route('admin.profiler.fetch'), {
      url: target,
      username: username.value,
      password: password.value,
    })
    setter(response.data, target.split('/').pop(), target)
    sessionStorage.setItem(PASSWORD_KEY, password.value)
  } catch (e) {
    error.value = e.response?.data?.message || e.message
  }
  loading.value = false
}

// A shared link opens once: straight away when the login is remembered, otherwise on the first Load
const pendingLink = ref(!!params.get('url'))

async function loadMain() {
  await fetchInto(setMain, url.value)
  if (!pendingLink.value || !entries.value.length) return
  pendingLink.value = false
  if (canFetch(baseUrl.value)) await loadBaseline()
  // Re-apply the linked sort, which loading the profiles may have replaced
  if (params.get('sort')) {
    pagination.value = {
      ...pagination.value,
      sortBy: params.get('sort'),
      descending: params.get('desc') !== '0',
    }
  }
}

const loadBaseline = () => fetchInto(setBaseline, baseUrl.value)

function openFile(setter) {
  const input = Object.assign(document.createElement('input'), {
    type: 'file',
    accept: '.json,application/json',
  })
  input.addEventListener('change', async () => {
    const file = input.files[0]
    if (!file) return
    error.value = ''
    try {
      setter(JSON.parse(await file.text()), file.name)
    } catch (e) {
      error.value = e instanceof SyntaxError ? 'That file is not valid JSON.' : e.message
    }
  })
  input.click()
}

onMounted(() => {
  if (canFetch(url.value)) loadMain()
})
</script>
