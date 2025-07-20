<template>
  <base-table
    ref="table"
    v-bind="$attrs"
    :routes="routes"
    :columns="columns"
    :show-columns="['created_at']"
    :hide-columns="['cancelled', 'failed', 'map_switch', 'success']"
    :pagination="{ sortBy: 'created_at' }"
    @row-click="onRowClick"
    flat
    dense
    no-row-actions
    no-timestamp-toggle
    clickable-rows
    grid-filters
  >
    <template #cell-content-duration="{ col }">
      {{ formatDuration(col.value) }}
    </template>
    <template #cell-content-status="{ props }">
      <q-chip
        v-if="props.row.map_switch"
        class="q-my-none"
        style="padding: 0 6px"
        color="grey"
        size="0.8em"
        outline
        square
        >Map Switch</q-chip
      >
      <q-chip
        v-if="!props.row.ended_at"
        class="q-my-none"
        style="padding: 0 6px"
        color="primary"
        size="0.8em"
        outline
        square
        >Building</q-chip
      >
      <q-chip
        v-else-if="props.row.cancelled"
        class="q-my-none"
        style="padding: 0 6px"
        color="primary"
        size="0.8em"
        outline
        square
        >Cancelled</q-chip
      >
      <q-chip
        v-else-if="props.row.failed"
        class="q-my-none"
        style="padding: 0 6px"
        color="negative"
        size="0.8em"
        outline
        square
        >Failed</q-chip
      >
      <q-chip
        v-else
        class="q-my-none"
        style="padding: 0 6px"
        color="positive"
        size="0.8em"
        outline
        square
        >Success</q-chip
      >
    </template>
  </base-table>
</template>

<style lang="scss" scoped>
:deep(.q-table__bottom) {
  padding-top: 0;
  padding-bottom: 0;
}
:deep(.q-table--dense .q-table tbody td) {
  height: 30px;
}
</style>

<script>
import { router } from '@inertiajs/vue3'
import { date } from 'quasar'
import BaseTable from '../BaseTable.vue'

export default {
  components: { BaseTable },

  data() {
    return {
      routes: {
        fetch: '/admin/builds',
        view: '/admin/builds/_id',
      },
      columns: [
        {
          name: 'server_id',
          label: 'Server',
          field: 'server_id',
          sortable: true,
          format: (val, row) => {
            return row.game_server.short_name
          },
          filter: { type: 'SelectServers', options: { filters: { with_invisible: true } } },
          headerClasses: 'q-table--col-auto-width',
        },
        {
          name: 'started_by',
          label: 'Started By',
          field: (row) => row.started_by?.name || row.started_by?.ckey,
          sortable: true,
          headerClasses: 'q-table--col-auto-width',
        },
        {
          name: 'created_at',
          label: 'Started At',
          field: 'created_at',
          sortable: true,
          format: this.$formats.dateWithTime,
          filter: { type: 'DateRange' },
        },
        {
          name: 'duration',
          label: 'Duration',
          field: 'duration',
          sortable: false,
          filterable: false,
          headerClasses: 'q-table--col-auto-width',
        },
        {
          name: 'status',
          label: 'Status',
          filterable: false,
          headerClasses: 'q-table--col-auto-width',
        },
        {
          name: 'cancelled',
          label: 'Cancelled',
          filter: { type: 'Boolean' },
        },
        {
          name: 'failed',
          label: 'Failed',
          filter: { type: 'Boolean' },
        },
        {
          name: 'map_switch',
          label: 'Map Switch',
          filter: { type: 'Boolean' },
        },
        {
          name: 'success',
          label: 'Successful',
          filter: { type: 'Boolean' },
        },
      ],
    }
  },

  mounted() {
    Echo.private('game-builds').listen('GameBuildFinished', this.onBuildFinished)
  },

  beforeUnmount() {
    Echo.private('game-builds').stopListening('GameBuildFinished', this.onBuildFinished)
  },

  methods: {
    onBuildFinished() {
      this.$refs.table.updateTable()
    },

    onRowClick(row) {
      router.visit(route('admin.builds.show', row.id))
    },

    formatDuration(seconds) {
      const relativeDate = date.addToDate(new Date('2000-01-1'), { seconds })
      return date.formatDate(relativeDate, 'mm:ss')
    },
  },
}
</script>
