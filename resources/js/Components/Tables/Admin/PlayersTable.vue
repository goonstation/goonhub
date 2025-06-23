<template>
  <base-table
    ref="table"
    v-bind="$attrs"
    :routes="routes"
    :columns="columns"
    :pagination="{ rowsPerPage: 30 }"
    selection="multiple"
    no-timestamp-toggle
    grid
    flat
  >
    <template v-slot:grid-filters-actions="{ props }">
      <q-btn
        :loading="toggleMentorLoading"
        @click="toggleMentor(true, props.selected)"
        color="purple-4"
        size="sm"
        outline
      >
        Make Mentor
      </q-btn>
    </template>

    <template v-slot:header-bottom>
      <div
        class="player-columns text-xs text-uppercase text-opacity-80 text-weight-medium text-center q-mt-md"
      >
        <div></div>
        <div class="text-left">Ckey</div>
        <div>Connections</div>
        <div>Participations</div>
        <div>Byond Version</div>
        <div></div>
        <div>Mentor</div>
        <div>HOS</div>
        <div>Whitelisted</div>
        <div>Bypass Cap</div>
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
          <div class="q-mr-md">
            <Link :href="$route('admin.players.show', props.row.id)">
              {{ props.row.ckey }}
            </Link>
          </div>
          <div class="text-center">
            {{ $formats.number(props.row.connections_count) }}
          </div>
          <div class="text-center">
            {{ $formats.number(props.row.participations_count) }}
          </div>
          <div class="text-center">
            <template v-if="props.row.byond_major && props.row.byond_minor">
              {{ props.row.byond_major }}.{{ props.row.byond_minor }}
            </template>
            <template v-else>&nbsp;</template>
          </div>
          <div></div>
          <div class="text-center">
            <q-chip
              v-if="props.row.is_mentor"
              class="text-weight-bold"
              color="purple-4"
              label="Mentor"
              size="sm"
              square
              dense
            />
            <template v-else>&nbsp;</template>
          </div>
          <div class="text-center">
            <q-chip
              v-if="props.row.is_hos"
              class="text-weight-bold"
              color="orange"
              label="HOS"
              size="sm"
              square
              dense
            />
            <template v-else>&nbsp;</template>
          </div>
          <div class="text-center">
            <q-chip
              v-if="props.row.whitelist"
              class="text-weight-bold"
              color="info"
              label="Whitelisted"
              size="sm"
              square
              dense
            />
            <template v-else>&nbsp;</template>
          </div>
          <div class="text-center">
            <q-chip
              v-if="props.row.bypass_cap"
              class="text-weight-bold"
              color="green"
              label="Bypass Cap"
              size="sm"
              square
              dense
            />
            <template v-else>&nbsp;</template>
          </div>
          <div class="text-right">{{ $formats.date(props.row.created_at) }}</div>
        </q-card>
      </div>
    </template>
  </base-table>
</template>

<style lang="scss" scoped>
:deep(.q-table--grid) {
  display: grid;
  grid-template:
    'select ckey connections participations byond spacer mentor hos whitelist bypass_cap created' /
    min-content max-content max-content max-content max-content 1fr max-content max-content max-content max-content;
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

@media (max-width: $breakpoint-sm-max) {
  :deep(.q-table--grid) {
    grid-template:
      'select ckey connections participations byond spacer' 1fr
      'mentor hos whitelist bypass_cap created spacer' 1fr
      / min-content max-content max-content max-content max-content 1fr;

    > *,
    .q-table__top,
    .q-table__grid-content,
    .q-table__grid-item,
    .gh-link-card {
      grid-row: span 2;
    }

    .player-columns {
      display: none;
    }
  }
}
</style>

<script>
import BaseTable from '../BaseTable.vue'

export default {
  components: { BaseTable },
  data() {
    return {
      toggleMentorLoading: false,
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
        {
          name: 'byond_version',
          label: 'Byond Version',
          field: (row) => {
            if (!row.byond_major || !row.byond_minor) return ''
            return `${row.byond_major}.${row.byond_minor}`
          },
        },
        {
          name: 'mentor',
          label: 'Mentor',
          field: 'is_mentor',
          sortable: true,
          format: this.$formats.boolean,
          filter: { type: 'boolean' },
        },
        {
          name: 'hos',
          label: 'HOS',
          field: 'is_hos',
          sortable: true,
          format: this.$formats.boolean,
          filter: { type: 'boolean' },
        },
        {
          name: 'whitelist',
          label: 'Whitelist',
          field: 'whitelist',
          sortable: true,
          // format: this.$formats.boolean,
          filter: { type: 'boolean' },
        },
        {
          name: 'bypass_cap',
          label: 'Bypass Cap',
          field: 'can_bypass_cap',
          sortable: true,
          // format: this.$formats.boolean,
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
    }
  },

  methods: {
    async toggleMentor(makeMentor, selected) {
      this.toggleMentorLoading = true
      const { data } = await axios.post(this.$route('admin.mentors.bulk-toggle'), {
        player_ids: selected.map((row) => row.id),
        make_mentor: makeMentor,
      })

      this.$q.notify(data.message)
      this.toggleMentorLoading = false
      this.$refs.table.updateTable()
    },
  },
}
</script>
