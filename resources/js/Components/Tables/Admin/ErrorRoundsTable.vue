<template>
  <base-table
    v-bind="$attrs"
    :routes="routes"
    :columns="columns"
    :show-columns="['created_at']"
    :pagination="{ rowsPerPage: 30 }"
    flat
    dense
    no-timestamp-toggle
  >
    <template #top-left>
      <q-btn :href="route('admin.errors.summary')" type="a" color="primary" outline>
        Error Summary
      </q-btn>
    </template>

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
        fetch: '/admin/errors',
        view: '/admin/errors/_id',
      },
      columns: [
        {
          name: 'id',
          label: 'ID',
          field: 'id',
          sortable: true,
          filterable: true,
          headerClasses: 'q-table--col-auto-width',
        },
        {
          name: 'server_id',
          label: 'Server',
          field: 'server_id',
          sortable: true,
          format: (val, row) => {
            if (!val) return 'All'
            return row.server.short_name
          },
          filter: { type: 'SelectServers', options: { filters: { with_invisible: true } } },
        },
        {
          name: 'created_at',
          label: 'Round Started At',
          field: 'created_at',
          sortable: true,
          format: this.$formats.dateWithTime,
          filter: { type: 'DateRange' },
        },
        {
          name: 'ended_at',
          label: 'Round Ended At',
          field: 'ended_at',
          sortable: true,
          format: this.$formats.dateWithTime,
          filter: { type: 'DateRange' },
        },
        {
          name: 'errors_count',
          label: 'Errors',
          field: 'errors_count',
          sortable: true,
          format: this.$formats.number,
          filterable: false,
          headerClasses: 'q-table--col-auto-width',
        },
      ],
    }
  },
}
</script>
