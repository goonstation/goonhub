<template>
  <base-table
    v-bind="$attrs"
    :routes="routes"
    :columns="columns"
    :show-columns="['created_at']"
    :pagination="{ rowsPerPage: 20 }"
    flat
    no-timestamp-toggle
    grid
  >
    <template v-slot:item="props">
      <div class="q-table__grid-item col-xs-12">
        <Link :href="$route('web.events.tickets.show', props.row.id)" class="gh-link-card">
          <div class="text-sm text-weight-medium">
            {{ props.row.target }}
            <span class="opacity-60 text-sm">ticketed by</span>
            {{ props.row.issuer }}
          </div>
          <div>"{{ props.row.reason }}"</div>
          <vote-control
            class="q-mt-xs"
            v-model:votes="props.row.votes"
            v-model:userVotes="props.row.user_votes"
            voteable-type="ticket"
            :voteable-id="props.row.id"
          />
        </Link>
      </div>
    </template>
  </base-table>
</template>

<style lang="scss" scoped>
.gh-link-card {
  padding: 0.6rem 1rem;
}

.gh-details-list {
  line-height: 1.2;
}
</style>

<script>
import VoteControl from '@/Components/VoteControl.vue'
import BaseTable from './BaseTable.vue'

export default {
  components: { BaseTable, VoteControl },
  data() {
    return {
      routes: { fetch: 'web.events.tickets.index' },
      columns: [
        {
          name: 'id',
          label: 'Recent',
          field: 'id',
          sortable: true,
          filterable: false,
          format: this.$formats.number,
        },
        {
          name: 'target',
          label: 'Target',
          field: 'target',
          sortable: true,
        },
        { name: 'reason', label: 'Reason', field: 'reason', sortable: true },
        {
          name: 'issuer',
          label: 'Issuer',
          field: 'issuer',
          sortable: true,
        },
        {
          name: 'votes',
          label: 'Votes',
          field: 'votes',
          sortable: true,
          filterable: false,
        },
        // {
        //   name: 'issuer_job',
        //   label: 'Issuer Job',
        //   field: 'issuer_job',
        //   sortable: true,
        // },
        // {
        //   name: 'created_at',
        //   label: 'Created At',
        //   field: 'created_at',
        //   sortable: true,
        //   format: this.$formats.date,
        //   filter: { type: 'GridDateRange' },
        // },
      ],
    }
  },
}
</script>
