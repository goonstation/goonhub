<template>
  <div class="q-pa-md">
    <div class="row q-col-gutter-md q-mb-md">
      <div v-for="tile in tiles" :key="tile.label" class="col-6 col-md">
        <div class="stat-tile q-pa-md rounded-borders">
          <div class="text-caption text-grey-5">{{ tile.label }}</div>
          <div class="text-h6">
            {{ tile.value }}
            <span v-if="tile.baseline" class="text-body2 text-grey-5">vs {{ tile.baseline }}</span>
          </div>
        </div>
      </div>
    </div>

    <div v-if="stats.changes" class="row q-col-gutter-md q-mb-md">
      <div v-for="list in changeLists" :key="list.title" class="col-12 col-lg-6">
        <div class="text-subtitle2 q-mb-sm">{{ list.title }}</div>
        <q-markup-table class="change-table" flat dense>
          <thead>
            <tr>
              <th class="text-left">proc</th>
              <th class="text-right">share</th>
              <th class="text-right">Δ share</th>
              <th class="text-right">Δ {{ perCallLabel }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in list.rows" :key="row.name">
              <td class="ellipsis" style="max-width: 0">
                <span class="text-grey-6">{{ row.path }}</span>
                <span class="text-weight-bold">{{ row.proc }}</span>
              </td>
              <td class="text-right">
                {{ formats.percent(row.pct - row.pct_delta) }} → {{ formats.percent(row.pct) }}
              </td>
              <td class="text-right" :class="deltaClass(row.pct_delta)">
                {{ signed(row.pct_delta, formats.percent(row.pct_delta)) }}
              </td>
              <td class="text-right" :class="deltaClass(row[perCallDelta])">
                {{ signed(row[perCallDelta], micros(row[perCallDelta])) }}
              </td>
            </tr>
            <tr v-if="!list.rows.length">
              <td colspan="4" class="text-grey-6">None</td>
            </tr>
          </tbody>
        </q-markup-table>
      </div>
    </div>

    <div class="row q-col-gutter-md">
      <div class="col-12" :class="{ 'col-lg-6': stats.topTypes.length }">
        <div class="text-subtitle2 q-mb-sm">Top procs by {{ stats.metric }}</div>
        <apexchart type="bar" :height="chartHeight" :options="procOptions" :series="procSeries" />
      </div>
      <div v-if="stats.topTypes.length" class="col-12 col-lg-6">
        <div class="text-subtitle2 q-mb-sm">Top types by {{ stats.metric }}</div>
        <apexchart type="bar" :height="chartHeight" :options="typeOptions" :series="typeSeries" />
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.stat-tile {
  background: $dark-page;
}

.change-table {
  background: $dark;
  table-layout: fixed;

  th:not(:first-child),
  td:not(:first-child) {
    width: 130px;
    font-family: monospace;
  }
}
</style>

<script setup>
import useFormats from '@/Composables/formats'
import { computed } from 'vue'
import { deltaClass } from '../profiler'

const props = defineProps({
  stats: { type: Object, required: true },
})

const formats = useFormats()

const ms = (seconds) => `${formats.number(Math.round(seconds * 1e5) / 100)} ms`
const micros = (seconds) => `${formats.number(Math.round(seconds * 1e8) / 100)} µs`
const signed = (value, text) => (value > 0 ? `+${text}` : text)

const perCallDelta = computed(() => `${props.stats.metric}_call_delta`)
const perCallLabel = computed(() => `${props.stats.metric}/call`)
const changeLists = computed(() => [
  { title: 'Grew the most', rows: props.stats.changes.grew },
  { title: 'Shrank the most', rows: props.stats.changes.shrank },
])

// Each headline number of the profile, next to the baseline's when comparing
const tiles = computed(() => {
  const { metric, baseline } = props.stats
  const headline = (summary) => [
    { label: `Total ${metric} time`, value: ms(summary.total) },
    { label: 'Calls', value: formats.number(summary.calls) },
    { label: 'Procs', value: formats.number(summary.procs) },
    ...summary.topShare.map(({ count, share }) => ({
      label: `Top ${count} procs' share`,
      value: formats.percent(share, 1),
    })),
  ]
  const baselineTiles = baseline ? headline(baseline) : []
  return headline(props.stats).map((tile, i) => ({ ...tile, baseline: baselineTiles[i]?.value }))
})

const chartHeight = computed(() => Math.max(props.stats.topProcs.length, 1) * 28 + 40)
const procLabels = computed(() => props.stats.topProcs.map((row) => row.name))
const typeLabels = computed(() => props.stats.topTypes.map((row) => row.type))
const seriesOf = (rows) => [
  {
    name: `${props.stats.metric} (ms)`,
    data: rows.map((row) => Math.round(row[props.stats.metric] * 1e5) / 100),
  },
]
const procSeries = computed(() => seriesOf(props.stats.topProcs))
const typeSeries = computed(() => seriesOf(props.stats.topTypes))

const chartOptions = (categories) => ({
  chart: {
    background: 'transparent',
    foreColor: '#fff',
    toolbar: { show: false },
    zoom: { enabled: false },
  },
  theme: { mode: 'dark', monochrome: { enabled: true, color: '#ffd125' } },
  plotOptions: { bar: { horizontal: true, barHeight: '70%' } },
  dataLabels: { enabled: false },
  grid: { borderColor: '#303030' },
  tooltip: { theme: 'dark', y: { formatter: (value) => `${formats.number(value)} ms` } },
  xaxis: { categories, labels: { formatter: (value) => `${formats.number(value)} ms` } },
  yaxis: { labels: { maxWidth: 360 } },
})
const procOptions = computed(() => chartOptions(procLabels.value))
const typeOptions = computed(() => chartOptions(typeLabels.value))
</script>
