<template>
  <div class="q-table__grid-item col-xs-12">
    <Link :href="`/rounds/${item.row.id}`" class="gh-link-card q-py-sm">
      <div v-if="item.row.server" class="text-caption opacity-60 q-mb-xs">
        {{ item.row.server.name }}
      </div>
      <div class="text-weight-bold ellipsis gh-link-card__title">
        {{ latestStationName }}
      </div>
      <div class="gh-details-list gh-details-list--small q-mt-sm">
        <div>
          <div>{{ started }}</div>
          <div>Started</div>
        </div>
        <div v-if="duration">
          <div>{{ duration }} minutes</div>
          <div>
            Duration
            <q-icon :name="ionInformationCircle" />
          </div>
          <q-tooltip :offset="[0, 5]" class="text-sm"> Ended {{ endedFromNow }} </q-tooltip>
        </div>
        <div v-if="item.row.map_record || item.row.map">
          <div>
            <template v-if="item.row.map_record">
              {{ item.row.map_record.name }}
            </template>
            <template v-else>
              {{ item.row.map }}
            </template>
          </div>
          <div>Map</div>
        </div>
        <div v-if="item.row.game_type">
          <div>{{ item.row.game_type }}</div>
          <div>Game Type</div>
        </div>
      </div>
      <div class="badges">
        <q-badge v-if="item.row.rp_mode" color="info" text-color="dark" class="text-weight-bold"
          >Roleplay</q-badge
        >
        <q-badge v-if="item.row.crashed" color="negative" text-color="dark" class="text-weight-bold"
          >Crashed</q-badge
        >
      </div>
    </Link>
  </div>
</template>

<style lang="scss" scoped>
.badges {
  display: flex;
  gap: 5px;
  position: absolute;
  top: -2px;
  right: -2px;
}
</style>

<script>
import { ionInformationCircle } from '@quasar/extras/ionicons-v6'
import dayjs from 'dayjs'

export default {
  setup() {
    return {
      dayjs,
      ionInformationCircle,
    }
  },

  props: {
    item: Object,
  },

  computed: {
    latestStationName() {
      if (!this.item.row.latest_station_name) return 'Space Station 13'
      return this.item.row.latest_station_name.name
    },

    started() {
      if (!this.item.row.created_at) return 'Unknown'
      return dayjs(this.item.row.created_at).format('YYYY-MM-DD [at] h:mma')
    },

    duration() {
      if (!this.item.row.ended_at) return
      return dayjs(this.item.row.ended_at).diff(dayjs(this.item.row.created_at), 'm')
    },

    endedFromNow() {
      if (!this.item.row.ended_at) return
      return dayjs(this.item.row.ended_at).fromNow()
    },
  },
}
</script>
