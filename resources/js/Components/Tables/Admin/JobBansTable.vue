<template>
  <base-table
    v-bind="$attrs"
    :routes="routes"
    :columns="columns"
    :pagination="{ rowsPerPage: 30 }"
    selection="multiple"
    create-button-text="Add Job Ban"
    dense
    flat
  >
    <template v-slot:cell-content-expires_at="{ props, col }">
      <template v-if="col.value">{{ col.value }}</template>
      <q-badge v-else color="negative">Permanent</q-badge>
    </template>
  </base-table>
</template>

<script>
import BaseTable from '../BaseTable.vue'

export default {
  components: {
    BaseTable,
  },

  data() {
    return {
      routes: {
        fetch: '/admin/job-bans',
        view: '/admin/job-bans/_id',
        create: '/admin/job-bans/create',
        edit: '/admin/job-bans/edit/_id',
        delete: '/admin/job-bans/_id',
        deleteMulti: '/admin/job-bans',
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
        { name: 'round_id', label: 'Round', field: 'round_id', sortable: true, filterable: false },
        {
          name: 'server_id',
          label: 'Server',
          field: 'server_id',
          sortable: true,
          format: (val, row) => {
            if (!val) return 'All'
            return row.game_server.short_name
          },
          filter: { type: 'SelectServers', options: { filters: { with_invisible: true } } },
        },
        {
          name: 'admin_ckey',
          label: 'Admin',
          field: (row) => row.game_admin.alias || row.game_admin.player?.ckey,
          sortable: true,
        },
        {
          name: 'ckey',
          label: 'Player',
          field: 'ckey',
          sortable: true,
        },
        {
          name: 'banned_from_job',
          label: 'Job',
          field: 'banned_from_job',
          sortable: true,
        },
        {
          name: 'reason',
          label: 'Reason',
          field: 'reason',
          sortable: true,
          align: 'left',
          style: 'white-space: normal; min-width: 300px;',
        },
        {
          name: 'expires_at',
          label: 'Expires At',
          field: 'expires_at',
          sortable: true,
          format: this.$formats.dateWithTime,
          filter: { type: 'DateRange' },
        },
        {
          name: 'created_at',
          label: 'Created',
          field: 'created_at',
          sortable: true,
          format: this.$formats.dateWithTime,
          filter: { type: 'DateRange' },
        },
        {
          name: 'updated_at',
          label: 'Last Updated',
          field: 'updated_at',
          sortable: true,
          format: this.$formats.dateWithTime,
          filter: { type: 'DateRange' },
        },
      ],
    }
  },
}
</script>
