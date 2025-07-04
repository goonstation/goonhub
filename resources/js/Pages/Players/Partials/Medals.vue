<template>
  <q-card flat>
    <q-card-section class="flex items-center justify-between gap-xs-sm">
      <div class="text-subtitle1 flex items-center">
        <q-icon :name="ionMedal" size="md" class="q-mr-md" />
        <template v-if="showUnearned"> Unearned Medals ({{ filteredUnearned.length }}) </template>
        <template v-else> Medals ({{ medals.length }}) </template>
      </div>
      <div>
        <q-btn @click="showUnearned = !showUnearned" color="grey-5" size="sm" outline>
          <template v-if="showUnearned">Show earned medals</template>
          <template v-else>Show unearned medals</template>
        </q-btn>
      </div>
    </q-card-section>
    <q-separator />
    <q-card-section class="q-pa-sm">
      <template v-if="showUnearned">
        <div v-if="filteredUnearned.length" class="row q-col-gutter-xs-xs">
          <div
            v-for="medal in filteredUnearned"
            class="items-center gap-xs-md col-xs-12 col-sm-12 col-md-12 col-lg-6 col-xl-4"
          >
            <div class="medal">
              <medal-thumbnail :medal="medal" />
              <div>
                <div class="text-primary text-bold">{{ medal.title }}</div>
                <div class="text-sm opacity-80">{{ medal.description }}</div>
              </div>
            </div>
          </div>
        </div>
        <q-banner v-else class="bg-grey-10 q-ma-sm text-center">
          No unearned medals to show.
        </q-banner>
      </template>
      <template v-else>
        <div v-if="medals.length" class="row q-col-gutter-xs-xs">
          <div
            v-for="award in medals"
            class="items-center gap-xs-md col-xs-12 col-sm-12 col-md-12 col-lg-6 col-xl-4"
          >
            <div v-if="award.medal.hidden" class="medal">
              <div class="flex items-center justify-center" style="width: 64px; height: 64px">
                <q-icon :name="ionHelp" size="lg" />
              </div>
              <div>
                <div class="text-grey-5 text-bold">Hidden Medal</div>
                <div class="text-sm opacity-80">What could it be?</div>
                <div class="earned-at text-sm opacity-80">
                  Earned {{ $formats.fromNow(award.created_at) }}
                  <q-tooltip :offset="[0, 5]" class="text-sm">{{
                    $formats.dateWithTime(award.created_at)
                  }}</q-tooltip>
                </div>
              </div>
            </div>
            <div v-else class="medal">
              <medal-thumbnail :medal="award.medal" />
              <div>
                <div class="text-primary text-bold">{{ award.medal.title }}</div>
                <div class="text-sm opacity-80">{{ award.medal.description }}</div>
                <div class="earned-at text-sm opacity-80">
                  Earned {{ $formats.fromNow(award.created_at) }}
                  <q-tooltip :offset="[0, 5]" class="text-sm">{{
                    $formats.dateWithTime(award.created_at)
                  }}</q-tooltip>
                </div>
              </div>
            </div>
          </div>
        </div>
        <q-banner v-else class="bg-grey-10 q-ma-sm text-center">
          This player has no medals yet.
        </q-banner>
      </template>
    </q-card-section>
  </q-card>
</template>

<style lang="scss" scoped>
.medal {
  display: flex;
  gap: 10px;
  align-items: center;
  height: 100%;
  padding: 10px;

  .earned-at {
    display: inline-flex;
    border-bottom: 1px dashed rgba(255, 255, 255, 0.6);
  }
}
</style>

<script>
import { ionMedal, ionHelp } from '@quasar/extras/ionicons-v6'
import MedalThumbnail from '@/Components/MedalThumbnail.vue'

export default {
  components: {
    MedalThumbnail,
  },

  setup() {
    return {
      ionMedal,
      ionHelp,
    }
  },

  props: {
    medals: Object,
    unearned: Object,
  },

  data() {
    return {
      showUnearned: false,
    }
  },

  computed: {
    filteredUnearned() {
      // Filter out hidden medals from the unearned list
      return this.unearned.filter((medal) => !medal.hidden)
    },
  },
}
</script>
