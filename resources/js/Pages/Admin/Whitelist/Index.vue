<template>
  <whitelist-table :initial="whitelistedPlayers" />
</template>

<script>
import WhitelistTable from '@/Components/Tables/Admin/WhitelistTable.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

export default {
  components: {
    WhitelistTable,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Whitelisted Players' }, () => page),

  props: {
    whitelistedPlayers: Object,
  },
}
</script>
