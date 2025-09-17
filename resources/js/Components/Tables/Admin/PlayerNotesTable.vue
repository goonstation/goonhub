<template>
  <base-table
    v-bind="$attrs"
    :routes="routes"
    :columns="columns"
    :pagination="{ rowsPerPage: 30 }"
    create-button-text="Add Note"
    selection="multiple"
    flat
    dense
  />
</template>

<script>
import BaseTable from '../BaseTable.vue'

export default {
  components: { BaseTable },
  data() {
    return {
      routes: {
        fetch: '/admin/notes',
        view: '/admin/notes/_id',
        create: '/admin/notes/create',
        edit: '/admin/notes/edit/_id',
        delete: '/admin/notes/_id',
        deleteMulti: '/admin/notes',
      },
      columns: [
        {
          name: 'id',
          label: 'ID',
          field: 'id',
          sortable: true,
          filterable: true,
        },
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
          name: 'game_admin',
          label: 'Admin',
          field: (row) => row.game_admin?.alias || row.game_admin?.player?.ckey,
          sortable: true,
        },
        {
          name: 'ckey',
          label: 'Player',
          field: (row) => row.player?.ckey || row.ckey,
          sortable: true,
        },
        {
          name: 'note',
          label: 'Note',
          field: 'note',
          sortable: true,
          align: 'left',
          style: 'white-space: normal; min-width: 300px;',
        },
        {
          name: 'created_at',
          label: 'Created',
          field: 'created_at',
          sortable: true,
          format: this.$formats.date,
        },
        {
          name: 'updated_at',
          label: 'Updated',
          field: 'updated_at',
          sortable: true,
          format: this.$formats.date,
        },
      ],
    }
  },
}
</script>
