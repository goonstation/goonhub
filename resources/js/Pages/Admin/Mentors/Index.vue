<template>
  <mentors-table :initial="mentors" />
</template>

<script>
import MentorsTable from '@/Components/Tables/Admin/MentorsTable.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

export default {
  components: {
    MentorsTable,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Mentors' }, () => page),

  props: {
    mentors: Object,
  },
}
</script>
