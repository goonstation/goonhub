<template>
  <base-table
    ref="table"
    v-bind="$attrs"
    v-model:selected="selected"
    :routes="routes"
    :columns="columns"
    :pagination="{ rowsPerPage: 30 }"
    :extra-params="{ with_latest_connection: true }"
    :show-columns="['created_at']"
    :hide-columns="['mentor', 'hos', 'whitelist', 'bypass_cap']"
    :skeleton-options="{ rows: 15 }"
    @row-click="$inertia.visit(route('admin.players.show', $event.id))"
    selection="multiple"
    no-timestamp-toggle
    no-row-actions
    clickable-rows
    grid-filters
    dense
    flat
  >
    <template v-slot:grid-filters-actions="{ props }">
      <q-btn
        v-if="showMakeMentor"
        :loading="toggleMentorLoading"
        @click="toggleMentor(true, props.selected)"
        class="q-px-sm text-weight-bold"
        color="purple-4"
        size="sm"
      >
        <q-icon :name="ionAdd" class="q-mr-xs" />
        Make Mentor
      </q-btn>
      <q-btn
        v-if="showRemoveMentor"
        :loading="toggleMentorLoading"
        @click="toggleMentor(false, props.selected)"
        class="q-px-sm text-weight-bold"
        color="purple-4"
        size="sm"
      >
        <q-icon :name="ionRemove" class="q-mr-xs" />
        Remove Mentor
      </q-btn>
      <q-btn
        v-if="showMakeHos"
        :loading="toggleHosLoading"
        @click="toggleHos(true, props.selected)"
        class="q-px-sm text-weight-bold"
        color="orange"
        text-color="dark"
        size="sm"
      >
        <q-icon :name="ionAdd" class="q-mr-xs" />
        Make HOS
      </q-btn>
      <q-btn
        v-if="showRemoveHos"
        :loading="toggleHosLoading"
        @click="toggleHos(false, props.selected)"
        class="q-px-sm text-weight-bold"
        color="orange"
        text-color="dark"
        size="sm"
      >
        <q-icon :name="ionRemove" class="q-mr-xs" />
        Remove HOS
      </q-btn>
      <q-btn
        @click="toggleWhitelistedDialog = true"
        class="q-px-sm text-weight-bold"
        color="info"
        size="sm"
      >
        <q-icon :name="ionToggle" class="q-mr-xs" />
        Toggle Whitelisted
      </q-btn>
      <player-whitelist-dialog
        v-model="toggleWhitelistedDialog"
        :players="selected"
        @success="$refs.table.updateTable()"
      />
      <q-btn
        @click="toggleBypassCapDialog = true"
        class="q-px-sm text-weight-bold"
        color="green"
        text-color="dark"
        size="sm"
      >
        <q-icon :name="ionToggle" class="q-mr-xs" />
        Toggle Cap Bypass
      </q-btn>
      <player-bypass-cap-dialog
        v-model="toggleBypassCapDialog"
        :players="selected"
        @success="$refs.table.updateTable()"
      />
    </template>

    <template #cell-content-status="{ props }">
      <q-btn-group>
        <q-btn
          v-if="props.row.mentor"
          class="q-py-none q-px-sm text-weight-bold"
          color="purple-4"
          size="sm"
          dense
        >
          Mentor
        </q-btn>
        <q-btn
          v-if="props.row.hos"
          class="q-py-none q-px-sm text-weight-bold"
          color="orange"
          text-color="dark"
          size="sm"
          dense
        >
          HOS
        </q-btn>
        <q-btn
          v-if="props.row.whitelist"
          class="q-py-none q-px-sm text-weight-bold"
          color="info"
          size="sm"
          dense
        >
          Whitelisted
        </q-btn>
        <q-btn
          v-if="props.row.bypass_cap"
          class="q-py-none q-px-sm text-weight-bold"
          color="green"
          text-color="dark"
          size="sm"
          dense
        >
          Can Bypass Cap
        </q-btn>
      </q-btn-group>
    </template>
  </base-table>
</template>

<script>
import PlayerBypassCapDialog from '@/Components/Dialogs/BulkPlayerBypassCap.vue'
import PlayerWhitelistDialog from '@/Components/Dialogs/BulkPlayerWhitelist.vue'
import { ionAdd, ionRemove, ionToggle } from '@quasar/extras/ionicons-v6'
import BaseTable from '../BaseTable.vue'

export default {
  components: {
    BaseTable,
    PlayerWhitelistDialog,
    PlayerBypassCapDialog,
  },

  setup() {
    return {
      ionAdd,
      ionRemove,
      ionToggle,
    }
  },

  data() {
    return {
      toggleMentorLoading: false,
      toggleHosLoading: false,
      toggleWhitelistedDialog: false,
      toggleBypassCapDialog: false,
      routes: {
        fetch: '/admin/players',
        view: '/admin/players/_id',
      },
      columns: [
        {
          name: 'id',
          label: 'ID',
          field: 'id',
          sortable: true,
          filterable: false,
          style: 'width: 1px;',
        },
        { name: 'ckey', label: 'Key', field: 'ckey', sortable: true },
        // { name: 'key', label: 'Key', field: 'key', sortable: true },
        {
          name: 'connections_count',
          label: 'Connections',
          field: 'connections_count',
          sortable: true,
          format: this.$formats.number,
          filter: {
            type: 'range',
          },
          style: 'width: 1px;',
        },
        {
          name: 'participations_count',
          label: 'Participations',
          field: 'participations_count',
          sortable: true,
          format: this.$formats.number,
          filter: {
            type: 'range',
          },
          style: 'width: 1px;',
        },
        {
          name: 'compid',
          label: 'Comp ID',
          sortable: true,
          field: (row) => row.latest_connection?.comp_id,
        },
        {
          name: 'ip',
          label: 'IP',
          sortable: true,
          field: (row) => row.latest_connection?.ip,
        },
        {
          name: 'byond_version',
          label: 'Byond',
          sortable: true,
          field: (row) => {
            if (!row.byond_major || !row.byond_minor) return ''
            return `${row.byond_major}.${row.byond_minor}`
          },
        },
        {
          name: 'mentor',
          label: 'Is Mentor',
          field: 'mentor',
          sortable: true,
          filter: { type: 'boolean' },
        },
        {
          name: 'hos',
          label: 'Is HOS',
          field: 'hos',
          sortable: true,
          filter: { type: 'boolean' },
        },
        {
          name: 'whitelist',
          label: 'Is Whitelisted',
          field: 'whitelist',
          sortable: true,
          filter: { type: 'boolean' },
        },
        {
          name: 'bypass_cap',
          label: 'Can Bypass Cap',
          field: 'bypass_cap',
          sortable: true,
          filter: { type: 'boolean' },
        },
        {
          name: 'created_at',
          label: 'Player Since',
          field: 'created_at',
          sortable: true,
          format: this.$formats.date,
          filter: { type: 'DateRange' },
        },
        {
          name: 'status',
          label: 'Status',
          field: 'status',
          sortable: false,
          filterable: false,
        },
      ],
      selected: [],
    }
  },

  computed: {
    showMakeMentor() {
      return this.selected.some((row) => !row.mentor)
    },

    showRemoveMentor() {
      return this.selected.some((row) => row.mentor)
    },

    showMakeHos() {
      return this.selected.some((row) => !row.hos)
    },

    showRemoveHos() {
      return this.selected.some((row) => row.hos)
    },
  },

  methods: {
    async toggleMentor(makeMentor, selected) {
      this.toggleMentorLoading = true
      const { data } = await axios.post(this.$route('admin.mentors.bulk-toggle'), {
        player_ids: selected.map((row) => row.id),
        make_mentor: makeMentor,
      })

      this.$q.notify({ message: data.message, color: 'positive' })
      this.toggleMentorLoading = false
      this.$refs.table.updateTable()
    },

    async toggleHos(makeHos, selected) {
      this.toggleHosLoading = true
      const { data } = await axios.post(this.$route('admin.hos.bulk-toggle'), {
        player_ids: selected.map((row) => row.id),
        make_hos: makeHos,
      })

      this.$q.notify({ message: data.message, color: 'positive' })
      this.toggleHosLoading = false
      this.$refs.table.updateTable()
    },
  },
}
</script>
