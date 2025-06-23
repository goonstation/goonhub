<template>
  <error-summary-table fetch-on-load />
</template>

<script>
import ErrorSummaryTable from '@/Components/Tables/ErrorSummaryTable.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    ErrorSummaryTable,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Errors' }, () => page),
}
</script>
