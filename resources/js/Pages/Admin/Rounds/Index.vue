<template>
  <rounds-table :initial="rounds" />
</template>

<script>
import RoundsTable from '@/Components/Tables/Admin/RoundsTable.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    RoundsTable
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Rounds' }, () => page),

  props: {
    rounds: Object
  }
}
</script>
