<template>
  <base-table
    v-bind="$attrs"
    :routes="routes"
    :columns="columns"
    :pagination="{ rowsPerPage: 30 }"
    dense
    flat
  >
    <template v-slot:cell-content-profile_photo_url="{ props }">
      <user-avatar :user="props.row" />
    </template>
  </base-table>
</template>

<script>
import UserAvatar from '@/Components/UserAvatar.vue'
import BaseTable from '../BaseTable.vue'

export default {
  components: { BaseTable, UserAvatar },
  data() {
    return {
      routes: {
        fetch: '/admin/users',
        create: '/admin/users/create',
        edit: '/admin/users/edit/_id',
      },
      columns: [
        {
          name: 'id',
          label: 'ID',
          field: 'id',
          sortable: true,
          filterable: false,
        },
        { name: 'profile_photo_url', label: 'Avatar', field: 'profile_photo_url' },
        { name: 'name', label: 'Name', field: 'name', sortable: true },
        { name: 'email', label: 'Email', field: 'email', sortable: true },
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
