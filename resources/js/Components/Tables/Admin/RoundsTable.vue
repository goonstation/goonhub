<template>
  <base-table
    v-bind="$attrs"
    :routes="routes"
    :columns="columns"
    :pagination="{ rowsPerPage: 30 }"
    :show-columns="['created_at']"
    flat
    dense
    no-timestamp-toggle
  >
    <template v-slot:cell-content-ended_at="{ props, col }">
      <template v-if="col.value">{{ col.value }}</template>
      <q-badge v-else color="primary" text-color="dark">Round in progress</q-badge>
    </template>
  </base-table>
</template>

<script>
import BaseTable from '../BaseTable.vue'

export default {
  components: { BaseTable },
  data() {
    return {
      routes: {
        fetch: '/admin/rounds',
        view: '/admin/rounds/_id',
      },
      columns: [
        {
          name: 'id',
          label: 'ID',
          field: 'id',
          sortable: true,
          headerClasses: 'q-table--col-auto-width',
        },
        {
          name: 'server_id',
          label: 'Server',
          field: 'server_id',
          sortable: true,
          format: (val, row) => row.server?.short_name,
          filter: { type: 'SelectServers', options: { filters: { with_invisible: true } } },
        },
        {
          name: 'map',
          label: 'Map',
          field: (row) => row.map_record?.name,
          sortable: true,
        },
        {
          name: 'game_type',
          label: 'Game Type',
          field: 'game_type',
          sortable: true,
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
          name: 'ended_at',
          label: 'Ended At',
          field: 'ended_at',
          sortable: true,
          format: this.$formats.dateWithTime,
          filter: { type: 'DateRange' },
        },
      ],
    }
  },
}
</script>
