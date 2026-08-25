<template>
  <Deferred data="maps">
    <template #fallback>
      <map-skeleton v-for="i in 5" :key="i" />
    </template>
    <Link
      v-for="map in maps.data"
      :key="map.id"
      class="gh-link-card gh-link-card--bar-left gh-link-card--bar-on q-mb-sm text-weight-medium"
      :href="`/maps/${map.map_id.toLowerCase()}`"
    >
      <map-thumbnail :map="map" />
      <div>
        <div class="text-weight-medium q-mb-xs">{{ map.name }}</div>
        <div class="text-sm opacity-60">
          <div>Last updated {{ $formats.fromNow(map.last_built_at) }}</div>
          <div v-if="map.latest_game_round">
            Last played {{ $formats.fromNow(map.latest_game_round.ended_at) }}
          </div>
        </div>
      </div>
    </Link>
  </Deferred>
</template>

<style lang="scss" scoped>
.gh-link-card {
  display: flex;
  align-items: center;

  span:first-child {
    font-size: 1.1em;
  }
}
</style>

<script>
import MapThumbnail from '@/Components/MapThumbnail.vue'
import MapSkeleton from '@/Components/Skeletons/Map.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Deferred } from '@inertiajs/vue3'

export default {
  layout: (h, page) => h(AppLayout, { title: 'Maps' }, () => page),

  components: {
    Deferred,
    MapThumbnail,
    MapSkeleton,
  },

  props: {
    maps: Object,
  },
}
</script>
