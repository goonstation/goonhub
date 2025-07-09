<template>
  <base-table
    v-bind="$attrs"
    :routes="routes"
    :columns="columns"
    :pagination="{ rowsPerPage: 30 }"
    selection="multiple"
    create-button-text="Add Heads of Security"
    flat
    dense
  >
    <template #cell-content-id="{ props }">
      <Link :href="$route('admin.players.show', props.row.player_id)">
        {{ props.row.id }}
      </Link>
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
        fetch: '/admin/hos',
        create: '/admin/hos/create',
        delete: '/admin/hos/_id',
        deleteMulti: '/admin/hos',
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
        {
          name: 'ckey',
          label: 'Player',
          field: 'ckey',
          format: (val, row) => {
            return row.player.key || row.player.ckey
          },
          sortable: true,
        },
        {
          name: 'created_at',
          label: 'HoS Since',
          field: 'created_at',
          sortable: true,
          format: this.$formats.date,
        },
      ],
    }
  },
}
</script>
