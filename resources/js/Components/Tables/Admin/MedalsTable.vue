<template>
  <base-table
    v-bind="$attrs"
    :routes="routes"
    :columns="columns"
    :pagination="{ rowsPerPage: 30, sortBy: 'title', descending: false }"
    create-button-text="Add Medal"
    wrap-cells
    dense
    flat
  >
    <template #cell-content-image="{ props }">
      <medal-thumbnail :medal="props.row" />
    </template>

    <template #cell-content-hidden="{ props }">
      <div class="flex items-center gap-xs-xs">
        <q-badge v-if="props.row.hidden" color="negative" text-color="black"> Hidden </q-badge>
        <q-badge v-else color="positive" text-color="black"> Visible </q-badge>
      </div>
    </template>

    <template #delete-confirm="{ props }">
      Deleting this medal will remove it from {{ props.item.earned_count }} players! <br /><br />
      Are you absolutely sure you want to do this?
    </template>
  </base-table>
</template>

<script>
import MedalThumbnail from '@/Components/MedalThumbnail.vue'
import BaseTable from '../BaseTable.vue'

export default {
  components: {
    BaseTable,
    MedalThumbnail,
  },

  data() {
    return {
      routes: {
        fetch: 'admin.medals.index',
        // view: 'admin.medals.show',
        create: 'admin.medals.create',
        edit: 'admin.medals.edit',
        // delete: 'admin.medals.delete',
        // deleteMulti: 'admin.medals.delete-multi',
      },
      columns: [
        // {
        //   name: 'id',
        //   label: 'ID',
        //   field: 'id',
        //   sortable: true,
        //   filterable: true,
        //   headerClasses: 'q-table--col-auto-width',
        // },
        {
          name: 'image',
          label: 'Image',
          field: 'image',
          sortable: false,
          filterable: false,
        },
        {
          name: 'title',
          label: 'Title',
          field: 'title',
          sortable: true,
        },
        {
          name: 'description',
          label: 'Description',
          field: 'description',
          sortable: true,
        },
        {
          name: 'hidden',
          label: 'Hidden',
          field: 'hidden',
          headerClasses: 'q-table--col-auto-width',
          sortable: true,
          filter: { type: 'Boolean' },
        },
        {
          name: 'pm.earned_count',
          label: 'Players Earned',
          field: 'earned_count',
          sortable: true,
          format: this.$formats.number,
          filter: { type: 'Range' },
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
