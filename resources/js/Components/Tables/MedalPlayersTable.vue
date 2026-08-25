<template>
  <base-table
    v-bind="$attrs"
    :routes="routes"
    :columns="columns"
    :pagination="{ rowsPerPage: 30, sortBy: 'earned_at' }"
    no-timestamp-toggle
    grid
  >
    <template v-slot:item="props">
      <div class="q-table__grid-item col-12 q-pa-none">
        <Link :href="$route('web.players.show', props.row.id)" class="gh-link-card">
          <div class="flex items-center no-wrap">
            <player-avatar :player="props.row" class="q-mr-sm" size="md" />
            <div style="line-height: 1">
              <div class="text-weight-bold ellipsis">
                <template v-if="props.row.key">{{ props.row.key }}</template>
                <template v-else>{{ $formats.capitalize(props.row.ckey) }}</template>
              </div>
              <div class="earned-at text-caption opacity-60">
                Earned {{ $formats.fromNow(props.row.earned_at) }}
                <q-tooltip :offset="[0, 5]" class="text-sm">{{
                  $formats.dateWithTime(props.row.earned_at)
                }}</q-tooltip>
              </div>
            </div>
          </div>
        </Link>
      </div>
    </template>
  </base-table>
</template>

<style lang="scss" scoped>
.gh-link-card {
  padding: 0.5rem;

  &:before {
    height: 3px;
    background: $dark-page;
  }

  &:hover,
  &:focus {
    &:before {
      background: $primary;
    }
  }
}

.earned-at {
  display: inline-flex;
  border-bottom: 1px dashed rgba(255, 255, 255, 0.6);
}

:deep(.q-table__grid-content) {
  margin: 0 1rem;
}
</style>

<script>
import PlayerAvatar from '@/Components/PlayerAvatar.vue'
import BaseTable from './BaseTable.vue'

export default {
  components: {
    BaseTable,
    PlayerAvatar,
  },

  props: {
    medalUuid: String,
  },

  data() {
    return {
      routes: {
        fetch: {
          route: 'web.medals.players',
          params: () => this.medalUuid,
        },
      },
      columns: [
        {
          name: 'name',
          label: 'Name',
          field: 'name',
          sortable: true,
        },
        {
          name: 'earned_at',
          label: 'Earned At',
          field: 'earned_at',
          sortable: true,
          filterable: false,
        },
      ],
    }
  },
}
</script>
