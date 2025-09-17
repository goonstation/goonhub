<template>
  <base-table
    v-bind="$attrs"
    :routes="routes"
    :columns="columns"
    :pagination="{ rowsPerPage: 30 }"
    create-button-text="Add Redirect"
    flat
    dense
  />
</template>

<script>
import BaseTable from '../BaseTable.vue';

export default {
  components: { BaseTable },
  data() {
    return {
      routes: {
        fetch: '/admin/redirects',
        // view: '/admin/redirects/_id',
        create: '/admin/redirects/create',
        edit: '/admin/redirects/edit/_id',
        delete: '/admin/redirects/_id',
      },
      columns: [
        {
          name: 'from',
          label: 'From',
          field: (row) => `${window.location.origin}/r/${row.from}`,
          sortable: true,
          align: 'left',
        },
        {
          name: 'to',
          label: 'To',
          field: 'to',
          sortable: true,
          align: 'left',
        },
        {
          name: 'visits',
          label: 'Visits',
          field: 'visits',
          sortable: true,
          filterable: false,
        },
        {
          name: 'created_by',
          label: 'Created By',
          field: (row) => row.created_by_user?.game_admin.alias || row.created_by_user?.game_admin.player?.ckey,
          sortable: false,
          filterable: false,
        },
        {
          name: 'updated_by',
          label: 'Updated By',
          field: (row) => row.updated_by_user?.game_admin.alias || row.updated_by_user?.game_admin.player?.ckey,
          sortable: false,
          filterable: false,
        },
        {
          name: 'created_at',
          label: 'Created',
          field: 'created_at',
          sortable: true,
          format: this.$formats.date,
          filter: { type: 'DateRange' },
        },
        {
          name: 'updated_at',
          label: 'Updated',
          field: 'updated_at',
          sortable: true,
          format: this.$formats.date,
          filter: { type: 'DateRange' },
        },
      ],
    }
  },
}
</script>
