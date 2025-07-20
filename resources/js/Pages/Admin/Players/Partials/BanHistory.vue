<template>
  <div class="q-pa-md flex items-center gap-xs-md">
    <h6 class="q-my-none">Bans</h6>
  </div>

  <q-table :rows="bans" :columns="banHistoryColumns" flat>
    <template v-slot:body-cell-id="props">
      <q-td :props="props">
        <Link :href="$route('admin.bans.show', props.row.id)">
          {{ props.row.id }}
        </Link>
      </q-td>
    </template>
    <template v-slot:body-cell-admin_ckey="props">
      <q-td :props="props">
        <Link :href="$route('admin.game-admins.show', props.row.game_admin.id)">
          {{ props.row.game_admin.name || props.row.game_admin.ckey }}
        </Link>
      </q-td>
    </template>
    <template v-slot:body-cell-original_ban_ckey="props">
      <q-td :props="props">
        <Link
          v-if="props.row.original_ban_detail.ckey"
          :href="$route('admin.player.show-by-ckey', props.row.original_ban_detail.ckey)"
        >
          {{ props.row.original_ban_detail.ckey }}
        </Link>
      </q-td>
    </template>
    <template v-slot:body-cell-status="props">
      <q-td :props="props">
        <q-badge v-if="isBanRemoved(props.row)" color="negative"> Removed </q-badge>
        <q-badge v-else-if="isBanExpired(props.row.expires_at)" color="primary" text-color="black">
          Expired
        </q-badge>
      </q-td>
    </template>
  </q-table>
</template>

<script>
export default {
  props: {
    bans: Object,
    ckey: String,
  },

  data() {
    return {
      banHistoryColumns: [
        { name: 'id', field: 'id', label: 'ID', sortable: true },
        {
          name: 'reason',
          field: 'reason',
          label: 'Reason',
          align: 'left',
          style: 'white-space: normal; min-width: 300px;',
        },
        {
          name: 'server_id',
          label: 'Server',
          field: 'server_id',
          sortable: true,
          format: (val, row) => {
            if (!val) return 'All'
            return row.game_server.short_name
          },
        },
        {
          name: 'admin_ckey',
          label: 'Admin',
          sortable: true,
        },
        {
          name: 'original_ban_ckey',
          label: 'Player',
          sortable: true,
        },
        {
          name: 'original_ban_ip',
          label: 'IP',
          field: (row) => row.original_ban_detail.ip,
          sortable: true,
        },
        {
          name: 'original_ban_comp_id',
          label: 'Comp ID',
          field: (row) => row.original_ban_detail.comp_id,
          sortable: true,
        },
        {
          name: 'created_at',
          label: 'Created',
          field: 'created_at',
          sortable: true,
          format: this.$formats.date,
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

    isBanRemoved(ban) {
      return !!ban.deleted_at || !ban.player_has_active_details
    },
  },
}
</script>
