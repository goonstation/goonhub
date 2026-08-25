<template>
  <q-table
    :columns="columns"
    :rows="rows"
    :pagination="{ rowsPerPage: 0 }"
    grid
    dense
    hide-header
    hide-bottom
  >
    <template v-slot:item="props">
      <div class="q-table__grid-item col-xs-12 col-sm-4 col-md-3 col-lg-2 relative-position">
        <q-card class="q-py-xs" flat bordered>
          <q-list>
            <q-item v-for="col in props.cols" :key="col.name">
              <q-item-section>
                <q-item-label class="q-table__grid-item-title">{{ col.label }}</q-item-label>
                <q-item-label class="q-table__grid-item-value">
                  <template v-if="col.value">{{ col.value }}</template>
                  <template v-else><em>None</em></template>
                </q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-card>
        <q-badge
          v-if="props.row.id === rows[rows.length - 1].id"
          class="bg-dark q-mt-xs q-mr-xs"
          color="primary"
          outline
          floating
        >
          Original
        </q-badge>
      </div>
    </template>
  </q-table>
</template>

<script>
import axios from 'axios'

export default {
  props: {
    expand: Boolean,
    row: {
      type: Object,
      default: () => ({}),
    },
  },

  data: () => {
    return {
      loading: false,
      columns: [
        { name: 'ckey', label: 'Player', field: 'ckey' },
        { name: 'comp_id', label: 'Computer ID', field: 'comp_id' },
        { name: 'ip', label: 'IP', field: 'ip' },
      ],
      rows: [],
    }
  },

  methods: {
    async getDetails(banId) {
      return await axios.get(this.$route('admin.bans.get-details'), { params: { ban_id: banId } })
    },

    async showDetails() {
      this.loading = true
      const res = await this.getDetails(this.row.id)
      this.rows = res.data
      this.loading = false
    },
  },

  watch: {
    expand(val) {
      if (val) this.showDetails()
    },
  },
}
</script>
