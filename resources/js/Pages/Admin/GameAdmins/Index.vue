<template>
  <game-admins-table prop-key="gameAdmins" />
</template>

<script>
import GameAdminsTable from '@/Components/Tables/Admin/GameAdminsTable.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

export default {
  components: {
    GameAdminsTable,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Admins' }, () => page),

  props: {
    gameAdmins: Object,
  },
}
</script>
