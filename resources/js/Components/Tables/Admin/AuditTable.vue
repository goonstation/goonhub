<template>
  <base-table
    v-bind="$attrs"
    :routes="routes"
    :columns="columns"
    :show-columns="['created_at']"
    :pagination="{ sortBy: 'created_at', rowsPerPage: 50 }"
    :skeleton-options="{ rows: 15 }"
    @row-click="onRowClick"
    flat
    dense
    no-row-actions
    no-timestamp-toggle
    clickable-rows
    grid-filters
  >
    <template #cell-content-user_id="{ props }">
      <Link v-if="props.row.user_id" :href="$route('admin.users.edit', props.row.user_id)">
        {{ props.row.user.name }}
      </Link>
      <template v-else>Anon</template>
    </template>
    <template #cell-content-auditable_type="{ props }">
      <auditable
        :id="props.row.auditable_id"
        :model="props.row.auditable_type"
        :event="props.row.event"
      />
    </template>
  </base-table>
</template>

<script>
import BaseTable from '../BaseTable.vue'
import Auditable from './Partials/Auditable.vue'

export default {
  components: { BaseTable, Auditable },
  data() {
    return {
      routes: {
        fetch: '/admin/audit',
        view: '/admin/audit/_id',
      },
      columns: [
        {
          name: 'user_id',
          label: 'User',
          field: 'user_id',
          sortable: true,
          filter: { type: 'Users' },
        },
        {
          name: 'event',
          label: 'Event',
          field: 'event',
          sortable: true,
        },
        {
          name: 'auditable_type',
          label: 'Auditable',
          field: 'auditable_type',
          sortable: true,
          filter: { type: 'AuditableTypes' },
        },
        {
          name: 'created_at',
          label: 'Timestamp',
          field: 'created_at',
          sortable: true,
          format: this.$formats.dateWithTime,
          filter: { type: 'DateRange' },
        },
      ],
    }
  },
  methods: {
    onRowClick(row) {
      this.$rtr.visit(route('admin.audit.show', row.id))
    },
  },
}
</script>
