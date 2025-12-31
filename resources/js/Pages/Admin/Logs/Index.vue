<template>
  <log-rounds-table prop-key="rounds" />
</template>

<script>
import LogRoundsTable from '@/Components/Tables/Admin/LogRoundsTable.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

export default {
  components: {
    LogRoundsTable,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Logs' }, () => page),

  props: {
    rounds: Object,
  },
}
</script>
