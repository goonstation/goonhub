<template>
  <a v-bind="$attrs" href="" @click.prevent="dialog = true"> See All </a>
  <q-dialog v-model="dialog">
    <q-card flat bordered>
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">IPs</div>
        <q-space />
        <q-btn :icon="ionClose" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section>
        <q-table
          :rows="ips"
          :columns="columns"
          :pagination="{ sortBy: 'last_seen', descending: true, rowsPerPage: 20 }"
          flat
          bordered
        >
          <template v-slot:body-cell-ip="props">
            <q-td :props="props">
              <Link :href="$route('admin.players.index', { filters: { ip: props.row.ip } })">
                {{ props.row.ip }}
                <q-tooltip>Search for other players with this IP</q-tooltip>
              </Link>
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
    ips: Object,
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
          name: 'ip',
          field: 'ip',
          label: 'IP',
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
      ],
    }
  },
}
</script>
