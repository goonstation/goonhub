<template>
  <players-table prop-key="players" />
</template>

<script>
import PlayersTable from '@/Components/Tables/Admin/PlayersTable.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

export default {
  components: {
    PlayersTable,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Players' }, () => page),

  props: {
    players: Object,
  },
}
</script>
