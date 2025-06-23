<template>
  <users-table :initial="users" />
</template>

<script>
import UsersTable from '@/Components/Tables/Admin/UsersTable.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    UsersTable
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Users' }, () => page),

  props: {
    users: Object
  }
}
</script>
