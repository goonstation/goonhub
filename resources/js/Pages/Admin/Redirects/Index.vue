<template>
  <redirects-table :initial="redirects" />
</template>

<script>
import RedirectsTable from '@/Components/Tables/Admin/RedirectsTable.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    RedirectsTable
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Redirects' }, () => page),

  props: {
    redirects: Object
  }
}
</script>
