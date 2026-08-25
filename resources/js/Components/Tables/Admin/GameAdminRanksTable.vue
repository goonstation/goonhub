<template>
  <base-table
    v-bind="$attrs"
    :routes="routes"
    :columns="columns"
    :pagination="{ rowsPerPage: 30 }"
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
        fetch: 'admin.game-admin-ranks.index',
      },
      columns: [
        {
          name: 'id',
          label: 'ID',
          field: 'id',
          sortable: true,
          filterable: false,
          headerClasses: 'q-table--col-auto-width',
        },
        { name: 'rank', label: 'Rank', field: 'rank', sortable: true },
        {
          name: 'admins_count',
          label: 'Admins',
          field: 'admins_count',
          sortable: true,
          filterable: false,
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
