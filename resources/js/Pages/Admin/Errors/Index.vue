<template>
  <error-rounds-table :initial="rounds" />
</template>

<script>
import ErrorRoundsTable from '@/Components/Tables/Admin/ErrorRoundsTable.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    ErrorRoundsTable,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Errors' }, () => page),

  props: {
    rounds: Object,
  },
}
</script>
