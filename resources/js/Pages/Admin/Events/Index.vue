<template>
  <events-table :event-types="eventTypes" prop-key="events" />
</template>

<script>
import EventsTable from '@/Components/Tables/Admin/EventsTable.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

export default {
  components: {
    EventsTable,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Events' }, () => page),

  props: {
    eventTypes: Array,
    events: Object,
  },
}
</script>
