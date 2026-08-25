<template>
  <bans-table prop-key="bans" />
</template>

<script>
import BansTable from '@/Components/Tables/Admin/BansTable.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

export default {
  components: {
    BansTable,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Bans' }, () => page),

  props: {
    bans: Object,
  },
}
</script>
