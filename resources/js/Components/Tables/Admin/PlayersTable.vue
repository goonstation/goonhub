<template>
  <base-table
    ref="table"
    v-bind="$attrs"
    v-model:selected="selected"
    :routes="routes"
    :columns="columns"
    :pagination="{ rowsPerPage: 30 }"
    :extra-params="{ with_latest_connection: true }"
    selection="multiple"
    no-timestamp-toggle
    transparent
    grid
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
        color="orange"
        text-color="dark"
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
    </template>

    <template v-slot:header-bottom>
      <div
        class="player-columns text-xs text-uppercase text-opacity-80 text-weight-medium text-center q-mt-md"
      >
        <div></div>
        <div class="text-left">Ckey</div>
        <div>Connections</div>
        <div>Participations</div>
        <div>Comp ID</div>
        <div>IP</div>
        <div>Byond</div>
        <div></div>
        <div></div>
        <div class="text-right">Created</div>
      </div>
    </template>

    <template v-slot:item="props">
      <div class="q-table__grid-item">
        <q-card
          :class="{ 'gh-link-card--bar-on': props.selected }"
          class="gh-link-card gh-link-card--no-scale gh-link-card--bar-left gh-link-card--dense items-center gap-xs-md"
          flat
        >
          <q-checkbox
            v-model="props.selected"
            @update:model-value="
              (val, evt) => {
                Object.getOwnPropertyDescriptor(props, 'selected').set(val, evt)
              }
            "
            dense
          />
          <div class="q-py-xs">
            <Link :href="$route('admin.players.show', props.row.id)">
              {{ props.row.ckey }}
            </Link>
          </div>
          <div class="text-center text-caption">
            {{ $formats.number(props.row.connections_count) }}
          </div>
          <div class="text-center text-caption">
            {{ $formats.number(props.row.participations_count) }}
          </div>
          <div class="text-center text-caption">
            {{ props.row.latest_connection?.comp_id }}
          </div>
          <div class="text-center text-caption">
            {{ props.row.latest_connection?.ip }}
          </div>
          <div class="text-center text-caption">
            <template v-if="props.row.byond_major && props.row.byond_minor">
              {{ props.row.byond_major }}.{{ props.row.byond_minor }}
            </template>
            <template v-else>&nbsp;</template>
          </div>
          <div></div>
          <div class="text-right">
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
                Bypass Cap
              </q-btn>
            </q-btn-group>
          </div>
          <div class="text-right text-caption">{{ $formats.date(props.row.created_at) }}</div>
        </q-card>
      </div>
    </template>
  </base-table>
</template>

<style lang="scss" scoped>
.gh-link-card--dense {
  padding-top: 2px;
  padding-bottom: 2px;
}

:deep(.q-table--grid) {
  display: grid;
  grid-template:
    'select ckey connections participations compid ip byond spacer abilities created' /
    min-content max-content max-content max-content max-content max-content max-content 1fr max-content max-content;
  column-gap: map-get($space-md, 'x');

  > *,
  .q-table__top > * {
    grid-column: 1 / -1;
  }

  .q-table__top,
  .player-columns,
  .q-table__grid-content,
  .q-table__grid-item,
  .gh-link-card {
    display: grid;
    grid-column: 1 / -1;
    grid-template-columns: subgrid;
  }
}

.player-columns {
  padding: 0 4px;
}

.q-table__grid-item {
  padding-top: 2px;
  padding-bottom: 2px;
}

// @media (max-width: $breakpoint-sm-max) {
//   :deep(.q-table--grid) {
//     grid-template:
//       'select ckey connections participations spacer' 1fr
//       'compid ip byond abilities created' 1fr
//       / min-content max-content max-content max-content max-content;

//     > *,
//     .q-table__top,
//     .q-table__grid-content,
//     .q-table__grid-item,
//     .gh-link-card {
//       grid-row: span 2;
//     }

//     .player-columns {
//       display: none;
//     }
//   }
// }
</style>

<script>
import PlayerWhitelistDialog from '@/Components/Dialogs/PlayerWhitelist.vue'
import { ionAdd, ionRemove, ionToggle } from '@quasar/extras/ionicons-v6'
import BaseTable from '../BaseTable.vue'

export default {
  components: {
    BaseTable,
    PlayerWhitelistDialog
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
        },
        { name: 'ckey', label: 'Ckey', field: 'ckey', sortable: true },
        { name: 'key', label: 'Key', field: 'key', sortable: true },
        {
          name: 'connections_count',
          label: 'Connections',
          field: 'connections_count',
          sortable: true,
          format: this.$formats.number,
          filter: {
            type: 'range',
          },
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
        },
        { name: 'compid', label: 'Comp ID', field: 'compid', sortable: true },
        { name: 'ip', label: 'IP', field: 'ip', sortable: true },
        {
          name: 'byond_version',
          label: 'Byond',
          field: (row) => {
            if (!row.byond_major || !row.byond_minor) return ''
            return `${row.byond_major}.${row.byond_minor}`
          },
        },
        {
          name: 'mentor',
          label: 'Mentor',
          field: 'mentor',
          sortable: true,
          filter: { type: 'boolean' },
        },
        {
          name: 'hos',
          label: 'HOS',
          field: 'hos',
          sortable: true,
          filter: { type: 'boolean' },
        },
        {
          name: 'whitelist',
          label: 'Whitelist',
          field: 'whitelist',
          sortable: true,
          filter: { type: 'boolean' },
        },
        {
          name: 'bypass_cap',
          label: 'Bypass Cap',
          field: 'can_bypass_cap',
          sortable: true,
          filter: { type: 'boolean' },
        },
        {
          name: 'created_at',
          label: 'Created',
          field: 'created_at',
          sortable: true,
          format: this.$formats.date,
          filter: { type: 'DateRange' },
        },
        // {
        //   name: 'updated_at',
        //   label: 'Updated',
        //   field: 'updated_at',
        //   sortable: true,
        //   format: this.$formats.date,
        // },
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

    showMakeBypassCap() {
      return this.selected.some((row) => !row.can_bypass_cap)
    },

    showRemoveBypassCap() {
      return this.selected.some((row) => row.can_bypass_cap)
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

    // async toggleBypassCap(makeBypassCap, selected) {
    //   this.toggleBypassCapLoading = true
    //   const { data } = await axios.post(this.$route('admin.bypass-cap.bulk-toggle'), {
    //     player_ids: selected.map((row) => row.id),
    //     make_bypass_cap: makeBypassCap,
    //   })

    //   this.$q.notify({ message: data.message, color: 'positive' })
    //   this.toggleBypassCapLoading = false
    //   this.$refs.table.updateTable()
    // },
  },
}
</script>
