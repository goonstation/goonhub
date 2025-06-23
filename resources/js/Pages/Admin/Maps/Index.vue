<template>
  <maps-table :initial="maps" />
</template>

<script>
import MapsTable from '@/Components/Tables/Admin/MapsTable.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    MapsTable
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Maps' }, () => page),

  props: {
    maps: Object
  }
}
</script>
