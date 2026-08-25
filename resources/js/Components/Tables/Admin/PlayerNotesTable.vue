<script setup>
import useFormats from '@/Composables/formats'
import { ref } from 'vue'
import BaseTable from '../BaseTable.vue'

const formats = useFormats()

const routes = ref({
  fetch: 'admin.notes.index',
  view: 'admin.notes.show',
  create: 'admin.notes.create',
  edit: 'admin.notes.edit',
  delete: 'admin.notes.delete',
  deleteMulti: 'admin.notes.delete-multi',
})

const columns = ref([
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
    format: formats.date,
  },
  {
    name: 'updated_at',
    label: 'Updated',
    field: 'updated_at',
    sortable: true,
    format: formats.date,
  },
])
</script>

<template>
  <base-table
    v-bind="$attrs"
    :routes="routes"
    :columns="columns"
    :pagination="{ rowsPerPage: 30 }"
    create-button-text="Add Note"
    selection="multiple"
    clickable-rows
    grid-filters
    flat
    dense
  />
</template>
