<template>
  <game-admin-ranks-table :initial="gameAdminRanks" />
</template>

<script>
import GameAdminRanksTable from '@/Components/Tables/Admin/GameAdminRanksTable.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    GameAdminRanksTable
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Admin Ranks' }, () => page),

  props: {
    gameAdminRanks: Object
  }
}
</script>
