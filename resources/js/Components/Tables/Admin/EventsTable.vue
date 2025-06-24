<template>
  <base-table
    v-bind="$attrs"
    :routes="routes"
    :columns="columns"
    :search="search"
    :pagination="{ rowsPerPage: 30 }"
    :show-columns="['created_at']"
    @loaded-url-params="loadedUrlParams"
    flat
    dense
  >
    <template #top-left>
      <q-select
        v-model="search.type"
        :options="eventOptions"
        label="Event Type"
        option-value="type"
        option-label="name"
        style="width: 200px"
        filled
        emit-value
        map-options
        hide-bottom-space
        dense
      />
    </template>

    <template #cell-content-data="{ props }">
      <event-data :event="props.row" />
    </template>
  </base-table>
</template>

<script>
import BaseTable from '../BaseTable.vue'
import EventData from './Partials/EventData.vue'

export default {
  components: { BaseTable, EventData },
  props: {
    eventTypes: Array,
  },
  data() {
    return {
      search: {
        type: null,
      },
      routes: {
        fetch: '/admin/events',
      },
      columns: [
        {
          name: 'id',
          label: 'ID',
          field: 'id',
          sortable: true,
          filterable: true,
          headerClasses: 'q-table--col-auto-width',
        },
        {
          name: 'round_id',
          label: 'Round',
          field: 'round_id',
          sortable: true,
          filterable: true,
          headerClasses: 'q-table--col-auto-width',
        },
        {
          name: 'created_at',
          label: 'Created',
          field: 'created_at',
          sortable: true,
          format: this.$formats.dateWithTime,
          filter: { type: 'DateRange' },
          headerClasses: 'q-table--col-auto-width',
        },
        {
          name: 'data',
          label: 'Data',
          filterable: false,
          sortable: false,
          align: 'left',
        },
      ],
    }
  },
  computed: {
    eventOptions() {
      return this.eventTypes.map((eventType) => {
        return {
          name: eventType
            .replace('events_', '')
            .replaceAll('_', ' ')
            .replace(/(^\w|\s\w)/g, (m) => m.toUpperCase()),
          type: eventType,
        }
      })
    },
  },
  methods: {
    loadedUrlParams({ filters }) {
      this.search.type = filters.type
    },
  },
}
</script>
