<template>
  <base-table
    v-bind="$attrs"
    :routes="routes"
    :columns="columns"
    :show-columns="['created_at']"
    :pagination="{ rowsPerPage: 20 }"
    flat
    no-timestamp-toggle
    grid
  >
    <template v-slot:item="props">
      <antags-table-item :item="props" />
    </template>
  </base-table>
</template>

<script>
import BaseTable from './BaseTable.vue'
import AntagsTableItem from './Partials/AntagsTableItem.vue'

export default {
  components: { BaseTable, AntagsTableItem },
  data() {
    return {
      routes: { fetch: 'web.events.antags.index' },
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
          name: 'mob_name',
          label: 'Name',
          field: 'mob_name',
          sortable: true,
        },
        { name: 'mob_job', label: 'Job', field: 'mob_job', sortable: true },
        { name: 'traitor_type', label: 'Antagonist Type', field: 'traitor_type', sortable: true },
        {
          name: 'success',
          label: 'Succeeded',
          field: 'success',
          sortable: true,
          align: 'center',
          filter: { type: 'Boolean' },
        },
      ],
    }
  },
}
</script>
