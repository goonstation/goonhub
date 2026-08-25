<template>
  <base-table
    v-bind="$attrs"
    :routes="routes"
    :columns="columns"
    :pagination="{ sortBy: 'game_server_id', descending: false }"
    :skeleton-options="{ rows: 5 }"
    @row-click="onRowClick"
    clickable-rows="edit"
    flat
    grid-filters
  >
    <template #header-right>
      <q-btn
        @click="$inertia.visit($route('admin.builds.settings.create'))"
        class="text-no-wrap q-mt-xs"
        color="primary"
        text-color="dark"
        size="11px"
        padding="xs sm"
      >
        New Settings
      </q-btn>
    </template>
  </base-table>
</template>

<script>
import { ionCheckmarkCircleOutline, ionEllipseOutline } from '@quasar/extras/ionicons-v6'
import BaseTable from '../BaseTable.vue'

export default {
  components: { BaseTable },
  setup() {
    return {
      ionEllipseOutline,
      ionCheckmarkCircleOutline,
    }
  },
  data() {
    return {
      routes: {
        fetch: 'admin.builds.settings.index',
        // create: 'admin.builds.settings.create',
        edit: 'admin.builds.settings.edit',
        delete: 'admin.builds.settings.delete',
      },
      columns: [
        {
          name: 'game_server_id',
          label: 'Server',
          field: 'server_id',
          sortable: true,
          align: 'left',
          format: (val, row) => {
            return row.game_server.short_name
          },
          filterable: false,
        },
        {
          name: 'branch',
          label: 'Branch',
          field: 'branch',
          sortable: true,
          headerClasses: 'q-table--col-auto-width',
        },
        {
          name: 'rp_mode',
          label: 'Roleplay',
          field: 'rp_mode',
          align: 'center',
          sortable: true,
          filter: { type: 'Boolean' },
          cell: { format: 'Boolean' },
          headerClasses: 'q-table--col-auto-width',
        },
        {
          name: 'byond_version',
          label: 'Byond Version',
          field: (row) => {
            if (!row.byond_major || !row.byond_minor) return ''
            return `${row.byond_major}.${row.byond_minor}`
          },
          headerClasses: 'q-table--col-auto-width',
        },
        {
          name: 'rustg_version',
          label: 'Rust-G Version',
          field: 'rustg_version',
          sortable: true,
          headerClasses: 'q-table--col-auto-width',
        },
        {
          name: 'map',
          label: 'Map',
          field: (row) => {
            return row.map?.name || ''
          },
          sortable: true,
          headerClasses: 'q-table--col-auto-width',
        },
      ],
    }
  },
}
</script>
