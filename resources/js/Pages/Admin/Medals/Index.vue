<template>
  <medals-table :initial="medals" />
</template>

<script>
import MedalsTable from '@/Components/Tables/Admin/MedalsTable.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    MedalsTable
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Medals' }, () => page),

  props: {
    medals: Object
  }
}
</script>
