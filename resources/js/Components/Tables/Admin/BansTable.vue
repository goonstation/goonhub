<template>
  <base-table
    v-bind="$attrs"
    :routes="routes"
    :columns="columns"
    :pagination="{ rowsPerPage: 30 }"
    selection="multiple"
    create-button-text="Add Ban"
    dense
    flat
  >
    <template v-slot:header-right>
      <q-btn
        @click="router.visit(route('admin.bans.show-remove-details'))"
        color="primary"
        text-color="dark"
      >
        Remove Bans
      </q-btn>
    </template>

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

    <template v-slot:cell-content-expires_at="{ props, col }">
      <template v-if="col.value">{{ col.value }}</template>
      <q-badge v-else color="negative">Permanent</q-badge>
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
import { router } from '@inertiajs/vue3'
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
      router,
      ionCaretDown,
      ionCaretUp,
    }
  },

  data() {
    return {
      routes: {
        fetch: '/admin/bans',
        view: '/admin/bans/_id',
        create: '/admin/bans/create',
        edit: '/admin/bans/edit/_id',
        delete: '/admin/bans/_id',
        deleteMulti: '/admin/bans',
      },
      columns: [
        {
          name: 'id',
          label: 'ID',
          field: 'id',
          sortable: true,
          filterable: true,
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
          field: (row) => row.game_admin.name || row.game_admin.ckey,
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
          name: 'expires_at',
          label: 'Expires At',
          field: 'expires_at',
          sortable: true,
          format: this.$formats.dateWithTime,
          filter: { type: 'DateRange' },
        },
        {
          name: 'details',
          label: 'Details',
          field: (row) => row.details_count || 1,
          sortable: true,
          filterable: false,
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
