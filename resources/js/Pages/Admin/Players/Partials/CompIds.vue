<template>
  <a v-bind="$attrs" href="" @click.prevent="dialog = true"> See All </a>
  <q-dialog v-model="dialog">
    <q-card flat bordered>
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">Computer Ids</div>
        <q-space />
        <q-btn :icon="ionClose" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section>
        <q-table
          :rows="compIds"
          :columns="columns"
          :pagination="{ sortBy: 'last_seen', descending: true, rowsPerPage: 20 }"
          flat
          bordered
        >
          <template v-slot:body-cell-comp_id="props">
            <q-td :props="props">
              <Link
                :href="$route('admin.players.index', { filters: { compId: props.row.comp_id } })"
              >
                {{ props.row.comp_id }}
                <q-tooltip>Search for other players with this Comp ID</q-tooltip>
              </Link>
            </q-td>
          </template>
          <template v-slot:body-cell-cursed="props">
            <q-td :props="props">
              <q-chip
                v-if="getCursedCompId(props.row.comp_id)"
                color="negative"
                text-color="dark"
                class="text-weight-bold"
                square
                dense
              >
                Cursed
                <q-tooltip>
                  {{ getCursedCompId(props.row.comp_id).reason }}
                </q-tooltip>
              </q-chip>
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script>
import { ionClose } from '@quasar/extras/ionicons-v6'

export default {
  props: {
    compIds: Object,
    cursedCompIds: Object,
  },

  setup() {
    return {
      ionClose,
    }
  },

  data() {
    return {
      dialog: false,
      columns: [
        {
          name: 'comp_id',
          field: 'comp_id',
          label: 'Comp ID',
        },
        {
          name: 'connections',
          field: 'connections',
          label: 'Connections',
          sortable: true,
        },
        {
          name: 'last_seen',
          field: 'last_seen',
          label: 'Last Seen',
          sortable: true,
          format: this.$formats.dateWithTime,
        },
        {
          name: 'cursed',
          label: '',
          headerClasses: 'q-table--col-auto-width',
        },
      ],
    }
  },

  methods: {
    getCursedCompId(compId) {
      return this.cursedCompIds.find((cursedCompId) => {
        return cursedCompId.comp_id === compId
      })
    },
  },
}
</script>
