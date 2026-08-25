<template>
  <div class="row q-col-gutter-md">
    <div class="col-12 col-md-7 order-last order-md-first">
      <Link
        v-for="medal in medals"
        :key="medal.uuid"
        :href="$route('web.medals.show', medal.uuid)"
        class="gh-link-card"
        style="padding: 0"
      >
        <medal :medal="medal" />
      </Link>
    </div>
    <div class="col-12 col-md-5 order-first order-md-last">
      <q-card flat>
        <q-card-section>
          <div class="text-subtitle2 flex items-center">Recent Medals Earned</div>
        </q-card-section>
        <q-separator />
        <q-card-section class="q-pa-none">
          <div v-if="recentMedalsEarned.length">
            <q-markup-table flat wrap-cells>
              <tbody>
                <tr v-for="award in recentMedalsEarned" :key="award.id">
                  <td style="width: 0; padding-right: 0">
                    <medal-thumbnail :medal="award.medal" size="32" />
                  </td>
                  <td class="text-left">
                    {{ award.player.key ?? award.player.ckey }}
                    earned
                    <span class="text-primary text-weight-bold q-mr-xs">{{
                      award.medal.title
                    }}</span>
                    <span class="opacity-80">{{ $formats.fromNow(award.created_at) }}</span>
                  </td>
                </tr>
              </tbody>
            </q-markup-table>
          </div>
          <div v-else class="q-pa-md">
            <q-banner class="bg-grey-10 text-center">No one has earned medals recently.</q-banner>
          </div>
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script>
import MedalThumbnail from '@/Components/MedalThumbnail.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import Medal from './Partials/Medal.vue'

export default {
  layout: (h, page) => h(AppLayout, { title: 'Medals' }, () => page),

  components: {
    MedalThumbnail,
    Medal,
  },

  props: {
    medals: Object,
    recentMedalsEarned: Object,
  },
}
</script>
