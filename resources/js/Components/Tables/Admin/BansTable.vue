<template>
  <base-table
    v-bind="$attrs"
    :routes="routes"
    :columns="columns"
    :pagination="{ rowsPerPage: 30 }"
    :hide-columns="['ckey', 'compId', 'ip', 'active', 'permanent', 'expires_at', 'deleted_at']"
    selection="multiple"
    create-button-text="Add Ban"
    clickable-rows
    grid-filters
    dense
    flat
  >
    <template #header-right>
      <q-btn
        @click="router.visit($route('admin.bans.show-remove-details'))"
        color="primary"
        text-color="dark"
      >
        Remove Bans
      </q-btn>
    </template>

    <template #cell-content-details="{ props, col }">
      <q-btn
        @click.stop="props.expand = !props.expand"
        class="q-pa-xs q-pl-sm full-width text-weight-regular"
        style="font-size: 0.9em"
        no-wrap
        flat
      >
        <span class="q-mr-xs">{{ col.value }}</span>
        <span>
          <q-icon :name="props.expand ? ionCaretUp : ionCaretDown" />
        </span>
      </q-btn>
    </template>

    <template #cell-content-expires_at="{ col }">
      <template v-if="col.value">{{ col.value }}</template>
      <q-badge v-else color="negative">Permanent</q-badge>
    </template>

    <template #cell-content-status="{ props }">
      <q-badge v-if="props.row.deleted_at" color="positive" text-color="black" class="status-badge">
        Removed At {{ $formats.dateWithTime(props.row.deleted_at) }}
      </q-badge>
      <q-badge
        v-else-if="isBanExpired(props.row.expires_at)"
        color="positive"
        text-color="black"
        class="status-badge"
      >
        Expired At {{ $formats.dateWithTime(props.row.expires_at) }}
      </q-badge>
      <q-badge v-else-if="props.row.expires_at" color="warning" class="status-badge">
        Expires At {{ $formats.dateWithTime(props.row.expires_at) }}
      </q-badge>
      <q-badge v-else color="negative" class="status-badge"> Permanent </q-badge>
    </template>

    <template #body-append="{ props }">
      <q-tr v-show="props.expand" :props="props" class="qr-row--expansion">
        <q-td colspan="100%">
          <ban-details-table :expand="props.expand" :row="props.row" />
        </q-td>
      </q-tr>
    </template>
  </base-table>
</template>

<style lang="scss" scoped>
.status-badge {
  padding: 4px 10px 5px 10px;
  font-weight: 500;
}
</style>

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
        fetch: 'admin.bans.index',
        view: 'admin.bans.show',
        create: 'admin.bans.create',
        edit: 'admin.bans.edit',
        delete: 'admin.bans.delete',
        deleteMulti: 'admin.bans.delete-multi',
      },
      columns: [
        {
          name: 'id',
          label: 'ID',
          field: 'id',
          headerClasses: 'q-table--col-auto-width',
          sortable: true,
          filterable: true,
        },
        {
          name: 'round_id',
          label: 'Round',
          field: 'round_id',
          headerClasses: 'q-table--col-auto-width',
          sortable: true,
          filterable: false,
        },
        {
          name: 'server_id',
          label: 'Server',
          field: 'server_id',
          sortable: true,
          cell: { format: 'Server' },
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
        { name: 'ckey', label: 'Ckey' },
        { name: 'compId', label: 'Comp ID' },
        { name: 'ip', label: 'IP' },
        {
          name: 'reason',
          label: 'Reason',
          field: 'reason',
          sortable: true,
          align: 'left',
          style: 'white-space: normal; min-width: 300px;',
        },
        {
          name: 'status',
          label: '',
          headerClasses: 'q-table--col-auto-width',
          filterable: false,
        },
        {
          name: 'active',
          label: 'Active',
          filter: { type: 'Boolean' },
        },
        {
          name: 'permanent',
          label: 'Permanent',
          filter: { type: 'Boolean' },
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
          name: 'deleted_at',
          label: 'Deleted At',
          field: 'deleted_at',
          sortable: true,
          format: this.$formats.date,
          filter: { type: 'DateRange' },
        },
        {
          name: 'created_at',
          label: 'Created At',
          field: 'created_at',
          sortable: true,
          format: this.$formats.date,
          filter: { type: 'DateRange' },
        },
        {
          name: 'updated_at',
          label: 'Updated At',
          field: 'updated_at',
          sortable: true,
          format: this.$formats.date,
          filter: { type: 'DateRange' },
        },
        {
          name: 'details',
          label: 'Details',
          headerClasses: 'q-table--col-auto-width',
          field: (row) => row.details_count || 1,
          sortable: true,
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
