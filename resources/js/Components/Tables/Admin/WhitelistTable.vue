<template>
  <base-table
    v-bind="$attrs"
    :routes="routes"
    :columns="columns"
    :pagination="{ rowsPerPage: 30 }"
    selection="multiple"
    create-button-text="Add Whitelisted Players"
    flat
    dense
  >
    <template #cell-content-id="{ props }">
      <Link :href="$route('admin.players.show', props.row.player_id)">
        {{ props.row.id }}
      </Link>
    </template>

    <template #cell-content-servers="{ props }">
      <q-chip
        v-for="server in props.row.servers"
        :key="server.id"
        color="grey-5"
        text-color="dark"
        class="text-weight-bold"
        size="sm"
        square
      >
        {{ server.short_name }}
      </q-chip>
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
        fetch: '/admin/whitelist',
        create: '/admin/whitelist/create',
        edit: '/admin/whitelist/edit/_id',
        delete: '/admin/whitelist/_id',
        deleteMulti: '/admin/whitelist',
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
          name: 'servers',
          label: 'Servers',
          field: 'servers',
          filter: {
            type: 'SelectServers',
            options: { optionValue: 'id', filters: { with_invisible: true } },
          },
          sortable: false,
        },
        {
          name: 'created_at',
          label: 'Whitelisted Since',
          field: 'created_at',
          sortable: true,
          format: this.$formats.date,
        },
      ],
    }
  },
}
</script>
