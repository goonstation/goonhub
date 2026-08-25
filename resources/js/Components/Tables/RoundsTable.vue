<template>
  <base-table
    v-bind="$attrs"
    :routes="routes"
    :columns="columns"
    :show-columns="['created_at']"
    :pagination="{ rowsPerPage: 30 }"
    flat
    no-timestamp-toggle
    grid
  >
    <template v-slot:item="props">
      <rounds-table-item :item="props" />
    </template>
  </base-table>
</template>

<script>
import BaseTable from './BaseTable.vue'
import RoundsTableItem from './Partials/RoundsTableItem.vue'

export default {
  components: { BaseTable, RoundsTableItem },
  data() {
    return {
      routes: { fetch: 'web.rounds.index' },
      columns: [
        {
          name: 'id',
          label: 'Recent',
          field: 'id',
          sortable: true,
          filterable: false,
          format: this.$formats.number,
        },
        {
          name: 'server_id',
          label: 'Server',
          field: 'server_id',
          sortable: true,
          filter: { type: 'SelectServers' },
        },
        {
          name: 'station_name',
          label: 'Station Name',
          field: 'station_name',
          sortable: false,
        },
        { name: 'map', label: 'Map', field: 'map', sortable: true },
        { name: 'game_type', label: 'Game Type', field: 'game_type', sortable: true },
        {
          name: 'rp_mode',
          label: 'Roleplay',
          field: 'rp_mode',
          sortable: true,
          align: 'center',
          filter: { type: 'Boolean' },
        },
        {
          name: 'crashed',
          label: 'Crashed',
          field: 'crashed',
          sortable: true,
          align: 'center',
          filter: { type: 'Boolean' },
        },
        {
          name: 'created_at',
          label: 'Started At',
          field: 'created_at',
          sortable: true,
          format: this.$formats.date,
          filter: { type: 'GridDateRange' },
        },
        {
          name: 'ended_at',
          label: 'Ended At',
          field: 'ended_at',
          sortable: true,
          format: this.$formats.date,
          filter: { type: 'GridDateRange' },
        },
        // {
        //   name: 'updated_at',
        //   label: 'Updated',
        //   field: 'updated_at',
        //   sortable: true,
        //   format: this.$formats.date,
        //   filter: { type: 'GridDateRange' },
        // },
      ],
    }
  },
}
</script>
