<template>
  <base-table
    v-bind="$attrs"
    :routes="routes"
    :columns="columns"
    :pagination="{ rowsPerPage: 30, sortBy: 'name', descending: false }"
    dense
    flat
  >
    <template #header-right>
      <q-btn @click="router.visit(route('admin.maps.upload'))" color="primary" text-color="dark">
        Upload Tiles
      </q-btn>
    </template>

    <template #cell-content-thumbnail="{ props }">
      <map-thumbnail v-if="props.row.is_layer" :map="props.row" />
      <a v-else :href="route('maps.show', props.row.map_id.toLowerCase())" target="_blank">
        <map-thumbnail :map="props.row" />
      </a>
    </template>

    <template #cell-content-status="{ props }">
      <div class="flex items-center gap-xs-xs">
        <q-badge v-if="props.row.active" color="positive" text-color="black"> Active </q-badge>
        <q-badge v-else color="negative" text-color="black"> Inactive </q-badge>
        <q-badge v-if="props.row.is_layer" color="primary" text-color="black"> Layer </q-badge>
        <q-badge v-if="props.row.admin_only" color="negative" text-color="black"> Admin Only </q-badge>
      </div>
    </template>
  </base-table>
</template>

<script>
import MapThumbnail from '@/Components/MapThumbnail.vue'
import { router } from '@inertiajs/vue3'
import BaseTable from '../BaseTable.vue'

export default {
  components: {
    BaseTable,
    MapThumbnail
  },

  setup() {
    return {
      router,
    }
  },

  data() {
    return {
      routes: {
        fetch: '/admin/maps',
        // view: '/admin/maps/_id',
        create: '/admin/maps/create',
        edit: '/admin/maps/edit/_id',
        delete: '/admin/maps/_id',
      },
      columns: [
        {
          name: 'thumbnail',
          label: '',
          field: 'thumbnail',
          filterable: false,
          headerClasses: 'q-table--col-auto-width',
        },
        { name: 'map_id', label: 'ID', field: 'map_id', sortable: true },
        { name: 'name', label: 'Name', field: 'name', sortable: true },
        {
          name: 'size',
          label: 'Size',
          field: 'size',
          format: (val, row) => {
            return `${row.tile_width}x${row.tile_height}`
          },
          filterable: false,
        },
        {
          name: 'last_built_by',
          label: 'Last Built By',
          field: 'last_built_by',
          format: (val, row) => {
            if (!val) return
            return row.game_admin.alias || row.game_admin.player?.ckey
          },
        },
        {
          name: 'last_built_at',
          label: 'Last Built At',
          field: 'last_built_at',
          sortable: true,
          format: this.$formats.dateWithTime,
          filter: { type: 'DateRange' },
        },
        {
          name: 'status',
          label: '',
          headerClasses: 'q-table--col-auto-width',
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
