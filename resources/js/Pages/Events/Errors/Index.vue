<template>
  <div>
    <error-summary-table prop-key="errors" />
  </div>
</template>

<script>
import ErrorSummaryTable from '@/Components/Tables/ErrorSummaryTable.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import EventsLayout from '@/Layouts/EventsLayout.vue'

export default {
  components: {
    ErrorSummaryTable,
  },

  layout: (h, page) => {
    return h(AppLayout, { title: 'Errors' }, () => h(EventsLayout, () => page))
  },

  props: {
    errors: Object,
  },
}
</script>
