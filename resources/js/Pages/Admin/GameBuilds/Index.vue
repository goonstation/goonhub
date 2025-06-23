<template>
  <div class="q-px-md q-pt-md">
    <div class="row q-col-gutter-sm">
      <div v-for="card in countCards" :key="card.title" class="col-6 col-md">
        <q-card flat>
          <q-card-section class="q-px-md q-py-none">
            <Deferred data="counts">
              <template #fallback>
                <q-skeleton type="rect" width="80px" height="21px" class="q-mb-xs" />
                <q-skeleton type="rect" width="50px" height="31px" />
              </template>

              <div class="text-weight-medium q-mb-xs">{{ card.title }}</div>
              <div class="flex items-center">
                <span class="text-weight-bold text-lg text-primary">
                  {{ card.value }}
                </span>
                <span
                  v-if="card.percent"
                  class="q-ml-sm text-caption text-opacity-80 text-weight-medium"
                >
                  {{ $formats.percent(card.percent) }}
                </span>
              </div>
            </Deferred>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </div>

  <div class="q-pl-sm q-pr-md overflow-hidden" style="height: 300px">
    <Deferred data="chart">
      <template #fallback>
        <chart-skeleton class="q-px-md q-pt-xs q-pb-md" />
      </template>
      <game-builds-over-time :data="chart" />
    </Deferred>
  </div>

  <q-separator />

  <div class="builds-table flex flex-grow">
    <game-builds-table prop-key="builds" />
  </div>
</template>

<style lang="scss" scoped>
.builds-table :deep(> div) {
  display: flex;
  flex-grow: 1;
}

.builds-table :deep(.q-table__container) {
  flex-grow: 1;
  width: 0;
}
</style>

<script>
import GameBuildsOverTime from '@/Components/Charts/GameBuildsOverTime.vue'
import ChartSkeleton from '@/Components/Skeletons/Chart.vue'
import GameBuildsTable from '@/Components/Tables/Admin/GameBuildsTable.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { Deferred } from '@inertiajs/vue3'
import { date } from 'quasar'
import GameBuildsIndexLayout from './IndexLayout.vue'
import GameBuildsLayout from './Layout.vue'

export default {
  layout: (h, page) => {
    return h(DashboardLayout, { title: 'Builds' }, () =>
      h(
        GameBuildsLayout,
        () => page,
        () => h(GameBuildsIndexLayout, () => page)
      )
    )
  },

  components: {
    Deferred,
    ChartSkeleton,
    GameBuildsTable,
    GameBuildsOverTime,
  },

  props: {
    builds: Object,
    counts: Object,
    chart: Object,
  },

  computed: {
    countCards() {
      return [
        { title: 'Total', value: this.$formats.number(this.counts?.total) ?? 0 },
        {
          title: 'Successful',
          value: this.$formats.number(this.counts?.success) ?? 0,
          percent:
            this.counts?.total && this.counts?.success
              ? this.counts.success / this.counts.total
              : 0,
        },
        {
          title: 'Failed',
          value: this.$formats.number(this.counts?.failed) ?? 0,
          percent:
            this.counts?.total && this.counts?.failed ? this.counts.failed / this.counts.total : 0,
        },
        {
          title: 'Cancelled',
          value: this.$formats.number(this.counts?.cancelled) ?? 0,
          percent:
            this.counts?.total && this.counts?.cancelled
              ? this.counts.cancelled / this.counts.total
              : 0,
        },
        {
          title: 'Avg Duration',
          value: this.counts?.avg_duration
            ? date.formatDate(
                date.addToDate(new Date('2000-01-1'), { seconds: this.counts.avg_duration }),
                'mm:ss'
              )
            : 0,
        },
      ]
    },
  },
}
</script>
