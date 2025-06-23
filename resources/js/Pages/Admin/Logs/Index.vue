<template>
  <log-rounds-table :initial="rounds" />
</template>

<script>
import LogRoundsTable from '@/Components/Tables/Admin/LogRoundsTable.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

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
