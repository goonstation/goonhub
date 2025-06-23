<template>
  <job-bans-table :initial="jobBans" />
</template>

<script>
import JobBansTable from '@/Components/Tables/Admin/JobBansTable.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    JobBansTable
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Job Bans' }, () => page),

  props: {
    jobBans: Object
  }
}
</script>
