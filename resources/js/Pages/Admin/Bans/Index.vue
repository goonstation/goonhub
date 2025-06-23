<template>
  <q-card class="gh-card gh-card--small overflow-hidden q-mb-md" flat>
    <q-card-section class="q-pa-none">
      <q-tabs indicator-color="transparent" align="left">
        <q-tab class="text-primary">Active Bans</q-tab>
        <q-tab>
          <Link :href="route('admin.bans.index-removed')" class="text-white">Inactive Bans</Link>
        </q-tab>
      </q-tabs>
    </q-card-section>
  </q-card>
  <search-bans v-model="search" />
  <bans-table :initial="bans" :search="search" @loaded="onTableLoaded" />
</template>

<script>
import BansTable from '@/Components/Tables/Admin/BansTable.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import SearchBans from './Partials/Search.vue'

export default {
  components: {
    BansTable,
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
