<template>
  <hos-table :initial="hos" />
</template>

<script>
import HosTable from '@/Components/Tables/Admin/HosTable.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

export default {
  components: {
    HosTable,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Heads of Staff' }, () => page),

  props: {
    hos: Object,
  },
}
</script>
