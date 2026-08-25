<template>
  <q-card class="gh-card gh-card--small" flat>
    <q-card-section>
      <div v-if="round.server" class="text-caption opacity-60 q-mb-xs">
        {{ round.server.name }}
      </div>
      <div
        class="text-weight-bold gh-link-card__title"
        :class="{ 'text-lg': !dense, ellipsis: dense }"
      >
        {{ latestStationName }}
      </div>
      <div class="gh-details-list gh-details-list--small q-mt-sm">
        <div>
          <div>{{ started }}</div>
          <div>Started</div>
        </div>
        <div v-if="duration">
          <div>{{ duration }} minutes</div>
          <div>Duration</div>
        </div>
        <div v-if="duration">
          <div>{{ endedFromNow }}</div>
          <div>Ended</div>
        </div>
        <div v-if="map">
          <div>
            <a v-if="mapUri" :href="route('web.maps.show', mapUri)" target="_blank">
              {{ map }}
              <q-icon :name="ionOpenOutline" />
            </a>
            <template v-else>
              {{ map }}
            </template>
          </div>
          <div>Map</div>
        </div>
        <div v-if="round.game_type">
          <div>{{ round.game_type }}</div>
          <div>Game Type</div>
        </div>
      </div>
    </q-card-section>
    <div class="badges">
      <q-badge
        v-if="!round.ended_at"
        color="primary"
        text-color="dark"
        class="text-weight-bold"
        :class="{ 'q-pa-sm': !dense }"
        >In Progress</q-badge
      >
      <q-badge
        v-if="round.rp_mode"
        color="info"
        text-color="dark"
        class="text-weight-bold"
        :class="{ 'q-pa-sm': !dense }"
        >Roleplay</q-badge
      >
      <q-badge
        v-if="round.crashed"
        color="negative"
        text-color="dark"
        class="text-weight-bold"
        :class="{ 'q-pa-sm': !dense }"
        >Crashed</q-badge
      >
    </div>
  </q-card>
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
import { ionOpenOutline } from '@quasar/extras/ionicons-v6'
import dayjs from 'dayjs'

export default {
  props: {
    round: Object,
    dense: Boolean,
  },

  setup() {
    return {
      ionOpenOutline,
    }
  },

  computed: {
    latestStationName() {
      if (!this.round.latest_station_name) return 'Space Station 13'
      return this.round.latest_station_name.name
    },

    map() {
      if (this.round.map_record) return this.round.map_record.name
      return this.round.map
    },

    mapUri() {
      if (!this.round.map_record) return
      return this.round.map_record.map_id.toLowerCase()
    },

    started() {
      if (!this.round.created_at) return 'Unknown'
      return dayjs(this.round.created_at).format('YYYY-MM-DD [at] h:mma')
    },

    duration() {
      if (!this.round.ended_at) return
      return dayjs(this.round.ended_at).diff(dayjs(this.round.created_at), 'm')
    },

    endedFromNow() {
      if (!this.round.ended_at) return
      return dayjs(this.round.ended_at).fromNow()
    },
  },
}
</script>
