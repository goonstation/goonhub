<template>
  <div class="q-table__grid-item col-xs-12">
    <Link :href="$route('web.events.antags.show', item.row.id)" class="gh-link-card">
      <div class="row items-center q-col-gutter-md">
        <div class="col">
          <span>{{ item.row.mob_name }}</span>
          <span v-if="item.row.mob_job" class="opacity-60 text-sm q-ml-sm"
            >&nbsp;the {{ item.row.mob_job }}</span
          >
        </div>
        <div class="col-xs-12 col-md-auto flex items-center q-ml-auto gap-xs-sm gap-md-md">
          <div>{{ traitorType }}</div>
          <q-chip
            v-if="item.row.success"
            class="q-mx-none text-weight-bold"
            size="sm"
            color="positive"
            text-color="dark"
            square
          >
            Succeeded
          </q-chip>
          <q-chip
            v-else
            class="q-mx-none text-weight-bold"
            size="sm"
            color="negative"
            text-color="dark"
            square
          >
            Failed
          </q-chip>
        </div>
      </div>
      <div v-if="item.row.objectives.length" class="text-caption">
        Objectives
        <q-chip color="grey-9" square size="sm" class="q-my-none">
          Completed {{ completedObjectives }}
        </q-chip>
        <q-chip color="grey-9" square size="sm" class="q-my-none">
          Failed {{ failedObjectives }}
        </q-chip>
      </div>
    </Link>
  </div>
</template>

<script>
import { startCase } from 'lodash'

export default {
  props: {
    item: Object,
  },

  computed: {
    traitorType() {
      if (!this.item.row.traitor_type) return ''
      return startCase(this.item.row.traitor_type.replace('_', ' '))
    },

    completedObjectives() {
      return this.item.row.objectives.filter((objective) => objective.success).length
    },

    failedObjectives() {
      return this.item.row.objectives.filter((objective) => !objective.success).length
    },
  },
}
</script>
