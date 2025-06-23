<template>
  <q-card class="gh-card gh-card--small overflow-hidden q-mb-md" flat>
    <q-card-section class="q-pa-none">
      <q-tabs indicator-color="transparent" align="left">
        <q-tab>
          <Link :href="route('admin.bans.index')" class="text-white">Active Bans</Link>
        </q-tab>
        <q-tab class="text-primary">Inactive Bans</q-tab>
      </q-tabs>
    </q-card-section>
  </q-card>
  <search-bans v-model="search" />
  <bans-removed-table :initial="bans" :search="search" @loaded="onTableLoaded" />
</template>

<script>
import BansRemovedTable from '@/Components/Tables/Admin/BansRemovedTable.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import SearchBans from './Partials/Search.vue'

export default {
  components: {
    BansRemovedTable,
    SearchBans,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Bans' }, () => page),

  props: {
    bans: Object,
  },

  data() {
    return {
      search: null
    }
  },

  methods: {
    onTableLoaded({ filters }) {
      this.search = filters
    }
  }
}
</script>
