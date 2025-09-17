<template>
  <base-table
    v-bind="$attrs"
    :routes="routes"
    :columns="columns"
    :hide-columns="['expires_at', 'deleted_at']"
    :pagination="{ rowsPerPage: 30 }"
    dense
    flat
  >
    <template v-slot:cell-content-details="{ props, col }">
      <q-btn
        class="q-pa-xs q-pl-sm full-width text-weight-regular"
        style="font-size: 0.9em"
        no-wrap
        flat
        @click="props.expand = !props.expand"
      >
        <span class="q-mr-xs">{{ col.value }}</span>
        <span>
          <q-icon :name="props.expand ? ionCaretUp : ionCaretDown" />
        </span>
      </q-btn>
    </template>

    <template v-slot:cell-content-status="{ props }">
      <q-badge v-if="props.row.deleted_at" color="negative"> Removed </q-badge>
      <q-badge v-else-if="isBanExpired(props.row.expires_at)" color="primary" text-color="black">
        Expired
      </q-badge>
    </template>

    <template v-slot:body-append="{ props }">
      <q-tr v-show="props.expand" :props="props" class="qr-row--expansion">
        <q-td colspan="100%">
          <ban-details-table :expand="props.expand" :row="props.row" />
        </q-td>
      </q-tr>
    </template>
  </base-table>
</template>

<script>
import { ionCaretDown, ionCaretUp } from '@quasar/extras/ionicons-v6'
import BaseTable from '../BaseTable.vue'
import BanDetailsTable from './BanDetailsTable.vue'

export default {
  components: {
    BaseTable,
    BanDetailsTable,
  },

  setup() {
    return {
      ionCaretDown,
      ionCaretUp,
    }
  },

  data() {
    return {
      routes: {
        fetch: '/admin/bans/removed',
        view: '/admin/bans/_id',
      },
      columns: [
        {
          name: 'id',
          label: 'ID',
          field: 'id',
          sortable: true,
          filterable: false,
        },
        { name: 'round_id', label: 'Round', field: 'round_id', sortable: true, filterable: false },
        {
          name: 'server_id',
          label: 'Server',
          field: 'server_id',
          sortable: true,
          format: (val, row) => {
            if (!val) return 'All'
            return row.game_server.short_name
          },
          filter: { type: 'SelectServers', options: { filters: { with_invisible: true } } },
        },
        {
          name: 'admin_ckey',
          label: 'Admin',
          field: (row) => row.game_admin.alias || row.game_admin.player?.ckey,
          sortable: true,
        },
        {
          name: 'original_ban_ckey',
          label: 'Player',
          field: (row) => row.original_ban_detail.ckey,
          sortable: true,
          filterable: false,
        },
        {
          name: 'reason',
          label: 'Reason',
          field: 'reason',
          sortable: true,
          align: 'left',
          style: 'white-space: normal; min-width: 300px;',
        },
        {
          name: 'details',
          label: 'Details',
          field: (row) => row.details_count || 1,
          sortable: true,
          filter: {
            type: 'range',
          },
        },
        {
          name: 'created_at',
          label: 'Created',
          field: 'created_at',
          sortable: true,
          format: this.$formats.date,
          filter: { type: 'DateRange' },
        },
        {
          name: 'updated_at',
          label: 'Last Updated',
          field: 'updated_at',
          sortable: true,
          format: this.$formats.date,
          filter: { type: 'DateRange' },
        },
        {
          name: 'expires_at',
          label: 'Expired',
          field: 'expires_at',
          sortable: true,
          format: this.$formats.date,
          filter: { type: 'DateRange' },
        },
        {
          name: 'deleted_at',
          label: 'Deleted',
          field: 'deleted_at',
          sortable: true,
          format: this.$formats.date,
          filter: { type: 'DateRange' },
        },
        {
          name: 'status',
          label: '',
          headerClasses: 'q-table--col-auto-width',
          filterable: false,
        },
      ],
    }
  },

  methods: {
    isBanExpired(expiresAt) {
      if (!expiresAt) return false
      return new Date(expiresAt) <= new Date()
    },
  },
}
</script>
