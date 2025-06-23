<template>
  <audit-table prop-key="audits" />
</template>

<script>
import AuditTable from '@/Components/Tables/Admin/AuditTable.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  layout: (h, page) => h(DashboardLayout, { title: 'Audit' }, () => page),

  components: {
    AuditTable,
  },

  props: {
    audits: Object,
  },
}
</script>
