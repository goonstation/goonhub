<template>
  <game-admins-table :initial="gameAdmins" />
</template>

<script>
import GameAdminsTable from '@/Components/Tables/Admin/GameAdminsTable.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    GameAdminsTable
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Admins' }, () => page),

  props: {
    gameAdmins: Object
  }
}
</script>
