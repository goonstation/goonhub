<template>
  <error-summary-table prop-key="errors" fetch-route="admin.errors.summary" />
</template>

<script>
import ErrorSummaryTable from '@/Components/Tables/ErrorSummaryTable.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    ErrorSummaryTable,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Errors' }, () => page),

  props: {
    errors: Object,
  },
}
</script>
