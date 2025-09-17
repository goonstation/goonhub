<template>
  <div>
    <base-table
      v-bind="$attrs"
      :routes="routes"
      :columns="columns"
      :pagination="{ sortBy: 'overview_count', rowsPerPage: 987654321 }"
      :search="search"
      :virtual-scroll-sticky-size-start="48"
      :virtual-scroll-slice-size="100"
      :virtual-scroll-slice-ratio-before="10"
      :virtual-scroll-slice-ratio-after="10"
      :virtual-scroll-item-size="28"
      @loaded="onTableLoaded"
      @reset="onTableReset"
      @fetch-end="onTableFetch"
      class="error-summary-table"
      style="max-height: 1000px"
      flat
      dense
      wrap-cells
      no-timestamp-toggle
      hide-pagination
      virtual-scroll
    >
      <template #top-left>
        <div class="flex gap-xs-md items-center">
          <base-select
            ref="serverSelect"
            v-model="search.server"
            :clearable="false"
            style="min-width: 200px"
            label="Server"
            load-route="/game-servers"
            option-value="server_id"
            option-label="name"
            filled
            lazy-rules
            dense
            emit-value
            map-options
            :filters="{ with_invisible: true }"
            :default-items="[{ name: 'All', server_id: 'all' }]"
          />
          <q-select
            v-model="search.time_range"
            :options="timeRangeOptions"
            style="min-width: 200px"
            label="Time Range"
            option-value="value"
            option-label="label"
            filled
            dense
            emit-value
            map-options
          />
          <div v-if="search.overview_round_id">
            Showing for round {{ search.overview_round_id }}
            <q-btn
              v-if="search"
              flat
              round
              color="white"
              padding="xs"
              :icon="ionCloseOutline"
              @click="search.overview_round_id = null"
            />
          </div>
        </div>
      </template>

      <template #header-bottom>
        <div class="full-width">
          <errors-by-round :data="errors" @on-round-select="onChartRoundSelect" />
        </div>
      </template>

      <template #cell-content-overview_name="{ props }">
        <a href="" @click.prevent="openRoundErrors(props.row)">{{ props.row.overview_name }}</a>
      </template>

      <template #cell-content-file="{ props }">
        <a
          :href="`https://github.com/goonstation/goonstation/blob/master/${props.row.file}#L${props.row.line}`"
          target="_blank"
        >
          {{ props.row.file }}
          <q-icon :name="ionOpenOutline" style="margin-top: -3px" />
        </a>
      </template>
    </base-table>

    <q-dialog v-model="roundErrorsDialog">
      <q-card flat bordered>
        <q-card-section>
          {{ viewingError.overview_name }}
          <br /><br />
          {{ viewingError.file }}:{{ viewingError.line }}
          <q-markup-table flat bordered class="q-mt-md">
            <thead>
              <tr>
                <th>Server</th>
                <th>Round ID</th>
                <th>Occurrences</th>
              </tr>
            </thead>
            <tbody class="text-center">
              <tr v-for="(roundError, roundId) in viewingError.round_error_counts" :key="roundId">
                <td>
                  {{ $helpers.serverIdToFriendlyName(roundError.server_id, true) }}
                </td>
                <td>
                  <Link v-if="isGameAdmin" :href="route('admin.errors.show', roundId)">
                    {{ roundId }}
                  </Link>
                  <template v-else>{{ roundId }}</template>
                </td>
                <td>{{ $formats.number(roundError.count) }}</td>
              </tr>
            </tbody>
          </q-markup-table>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Close" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<style lang="scss" scoped>
:deep(.error-summary-table) {
  thead {
    tr {
      th {
        background: var(--q-dark);
        position: sticky;
        z-index: 1;
        top: 0;
      }

      &:last-child th {
        top: 28px;
      }
    }
  }

  tbody {
    scroll-margin-top: 48px;
  }
}
</style>

<script>
import ErrorsByRound from '@/Components/Charts/ErrorsByRound.vue'
import BaseSelect from '@/Components/Selects/BaseSelect.vue'
import { ionCloseOutline, ionOpenOutline } from '@quasar/extras/ionicons-v6'
import BaseTable from './BaseTable.vue'

export default {
  components: { BaseTable, BaseSelect, ErrorsByRound },
  setup() {
    return {
      ionCloseOutline,
      ionOpenOutline,
    }
  },
  data() {
    return {
      routes: {
        fetch: '/events/errors',
      },
      columns: [
        {
          name: 'overview_count',
          label: 'Count',
          field: 'overview_count',
          sortable: true,
          filterable: true,
          filter: { type: 'Range' },
          format: this.$formats.number,
          headerClasses: 'q-table--col-auto-width',
        },
        {
          name: 'overview_round_count',
          label: 'Round Count',
          field: 'overview_round_count',
          sortable: true,
          filterable: true,
          filter: { type: 'Range' },
          format: this.$formats.number,
          headerClasses: 'q-table--col-auto-width',
        },
        {
          name: 'overview_name',
          label: 'Name',
          field: 'overview_name',
          sortable: true,
          align: 'left',
          style: 'word-break: break-all; min-width: 300px;',
        },
        {
          name: 'file',
          label: 'File',
          field: 'file',
          sortable: true,
          align: 'left',
          style: 'word-break: break-all; min-width: 150px;',
        },
        {
          name: 'line',
          label: 'Line',
          field: 'line',
          sortable: true,
          filter: { type: 'Number' },
          format: this.$formats.number,
        },
      ],
      search: {
        server: 'all',
        time_range: '1week',
        overview_round_id: null,
      },
      timeRangeOptions: [
        { label: 'Last Week', value: '1week' },
        { label: 'Last 3 Days', value: '3days' },
        { label: 'Last Day', value: '1day' },
      ],
      errors: [],
      roundErrorsDialog: false,
      viewingError: {},
    }
  },

  computed: {
    isGameAdmin() {
      return this.$page.props.auth?.user?.game_admin?.id
    },
  },

  created() {
    this.errors = this.$attrs?.initial?.data.length ? this.$attrs.initial.data : []
  },

  methods: {
    onTableLoaded({ filters }) {
      if (filters.server) this.search.server = filters.server
      if (filters.time_range) this.search.time_range = filters.time_range
      if (filters.overview_round_id) this.search.overview_round_id = filters.overview_round_id
    },

    onTableReset({ filters }) {
      this.search.server = filters.server
      this.search.time_range = filters.time_range
      this.search.overview_round_id = null
    },

    onTableFetch(data) {
      this.errors = data.data.length ? data.data : []
    },

    openRoundErrors(error) {
      this.viewingError = error
      this.roundErrorsDialog = true
    },

    onChartRoundSelect(roundId) {
      this.search.overview_round_id = roundId
    },
  },
}
</script>
