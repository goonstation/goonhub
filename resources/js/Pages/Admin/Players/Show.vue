<template>
  <div class="flex-grow flex column">
    <div class="row gap-xs-sm q-mb-sm player-header">
      <q-card flat>
        <div>
          <q-card-section class="flex no-wrap gap-xs-md items-start q-px-lg q-pb-none q-mb-none">
            <player-avatar :player="player" class="q-mt-xs" />
            <div class="q-pb-xs">
              <div class="text-weight-bold text-h6">
                <template v-if="player.key">{{ player.key }}</template>
                <template v-else>{{ $formats.capitalize(player.ckey) }}</template>
              </div>
              <div class="text-caption text-grey-5">
                Last seen
                {{
                  player.latest_connection
                    ? dayjs(player.latest_connection.created_at).fromNow()
                    : 'never'
                }}
                <template v-if="latestRound">
                  on round
                  <Link :href="`/rounds/${latestRound.id}`">
                    <template v-if="latestRound.latest_station_name">
                      {{ latestRound.latest_station_name.name }}
                    </template>
                    <template v-else> #{{ latestRound.id }} </template>
                  </Link>
                </template>
              </div>
              <div class="text-caption text-grey-5">
                Started playing
                {{
                  player.first_connection
                    ? dayjs(player.first_connection.created_at).fromNow()
                    : 'never'
                }}
              </div>
            </div>
          </q-card-section>
        </div>
        <div>
          <q-separator class="q-mb-xs" />
          <q-card-section class="q-py-none">
            <p class="text-overline q-mb-none">Current Status</p>
          </q-card-section>
          <q-separator class="q-mt-xs q-mb-md" />
          <q-card-section class="q-pt-none flex flex-wrap gap-xs-sm status-chips">
            <q-chip
              v-if="isBanned"
              color="negative"
              text-color="dark"
              class="text-weight-bold"
              square
              >Banned</q-chip
            >
            <q-chip v-else color="positive" text-color="dark" class="text-weight-bold" square
              >Not Banned</q-chip
            >
            <q-chip
              v-if="player.vpn_whitelist"
              color="info"
              text-color="dark"
              class="text-weight-bold"
              square
            >
              VPN Whitelisted
            </q-chip>
            <q-chip
              v-if="player.is_mentor"
              color="purple-4"
              text-color="dark"
              class="text-weight-bold"
              square
            >
              Mentor
            </q-chip>
            <q-chip
              v-if="player.is_hos"
              color="orange"
              text-color="dark"
              class="text-weight-bold"
              square
            >
              HOS
            </q-chip>
            <q-chip
              v-if="player.whitelist"
              color="info"
              text-color="dark"
              class="text-weight-bold"
              square
            >
              Whitelisted
            </q-chip>
            <q-chip
              v-if="player.bypass_cap"
              color="green"
              text-color="dark"
              class="text-weight-bold"
              square
            >
              Can Bypass Cap
            </q-chip>
          </q-card-section>
        </div>
      </q-card>

      <q-card class="col" flat>
        <q-card-section class="flex items-center q-pa-lg">
          <div class="gh-details-list gh-details-list--small wrap">
            <div>
              <div>
                <template v-if="player.latest_connection">
                  {{ player.latest_connection.ip }}
                  <ips :ips="uniqueIps" class="q-ml-sm" />
                </template>
                <template v-else>
                  <em>N/A</em>
                </template>
              </div>
              <div>IP</div>
            </div>
            <div>
              <div>
                <template v-if="player.latest_connection">
                  {{ player.latest_connection.comp_id }}
                  <comp-ids
                    :comp-ids="uniqueCompIds"
                    :cursed-comp-ids="cursedCompIds"
                    class="q-ml-sm"
                  />
                </template>
                <template v-else>
                  <em>N/A</em>
                </template>
              </div>
              <div>Computer ID</div>
            </div>
            <div>
              <div>
                {{ $formats.number(player.participations_count) }}
                ({{
                  $formats.number(player.participations_count - player.participations_rp_count)
                }}
                Classic, {{ $formats.number(player.participations_rp_count) }} RP)
              </div>
              <div>Rounds Played</div>
            </div>
            <div>
              <div>
                {{ $formats.number(totalPlaytime) }}
              </div>
              <div>Hours Played</div>
            </div>
            <div>
              <div>
                <template v-if="player.byond_major && player.byond_minor">
                  {{ player.byond_major }}.{{ player.byond_minor }}
                </template>
                <template v-else><em>N/A</em></template>
              </div>
              <div>BYOND Version</div>
            </div>
          </div>
        </q-card-section>
        <div>
          <q-separator class="q-mb-xs" />
          <q-card-section class="q-py-none">
            <p class="text-overline q-mb-none">Quick Actions</p>
          </q-card-section>
          <q-separator class="q-mt-xs q-mb-md" />
          <q-card-section class="q-pt-none flex flex-wrap gap-xs-sm">
            <q-btn
              v-if="isBanned"
              @click="
                $inertia.visit(
                  $route('admin.bans.show-remove-details', {
                    ckey: player.ckey,
                    comp_id: player.latest_connection?.comp_id,
                    ip: player.latest_connection?.ip,
                  })
                )
              "
              :icon="mdiBird"
              color="primary"
              text-color="primary"
              class="text-weight-bold"
              label="Clear Evasion Data"
              size="11px"
              outline
            />
            <q-btn
              v-else
              @click="
                $inertia.visit(
                  $route('admin.bans.create', {
                    ckey: player.ckey,
                    comp_id: player.latest_connection?.comp_id,
                    ip: player.latest_connection?.ip,
                  })
                )
              "
              :icon="ionBan"
              color="negative"
              text-color="negative"
              class="text-weight-bold"
              label="Ban"
              size="11px"
              outline
            />
            <q-btn
              :loading="toggleMentorLoading"
              :icon="player.is_mentor ? ionRemove : ionAdd"
              :label="player.is_mentor ? 'Remove Mentor' : 'Make Mentor'"
              @click="toggleMentor(!player.is_mentor, [player])"
              color="purple-4"
              text-color="purple-4"
              class="text-weight-bold"
              size="11px"
              outline
            />
            <q-btn
              :loading="toggleHosLoading"
              :icon="player.is_hos ? ionRemove : ionAdd"
              :label="player.is_hos ? 'Remove HOS' : 'Make HOS'"
              @click="toggleHos(!player.is_hos, [player])"
              color="orange"
              text-color="orange"
              class="text-weight-bold"
              size="11px"
              outline
            />
            <q-btn
              :icon="ionToggle"
              @click="toggleWhitelistedDialog = true"
              label="Toggle Whitelisted"
              color="info"
              text-color="info"
              class="text-weight-bold"
              size="11px"
              outline
            />
            <player-whitelist-dialog
              v-model="toggleWhitelistedDialog"
              :player="{ ...player }"
              @success="({ whitelist }) => ($page.props.player.whitelist = whitelist)"
            />
            <q-btn
              :icon="ionToggle"
              @click="toggleBypassCapDialog = true"
              label="Toggle Cap Bypass"
              color="green"
              text-color="green"
              class="text-weight-bold"
              size="11px"
              outline
            />
            <player-bypass-cap-dialog
              v-model="toggleBypassCapDialog"
              :player="{ ...player }"
              @success="({ bypassCap }) => ($page.props.player.bypass_cap = bypassCap)"
            />
          </q-card-section>
        </div>
      </q-card>
    </div>

    <q-card class="gh-card gh-card--small flex-grow flex" flat>
      <q-card-section class="flex-grow row no-wrap q-pa-none" style="min-height: 500px">
        <q-tabs v-model="currentTab" active-color="primary" indicator-color="primary" vertical>
          <q-tab
            v-for="tab in tabs"
            :key="tab.name"
            :name="tab.name"
            content-class="full-width items-baseline q-px-sm"
            style="justify-content: initial; text-align: left"
          >
            <div class="full-width flex items-center justify-between gap-xs-md">
              <div class="q-tab__label">
                {{ tab.label }}
              </div>
              <q-chip
                v-if="tab.total"
                color="grey-7"
                size="12px"
                class="q-ma-none text-weight-bolder"
                square
              >
                {{ tab.total }}
              </q-chip>
            </div>
          </q-tab>
        </q-tabs>

        <q-separator vertical style="margin-left: -1px" />

        <div class="col scroll" style="width: 0">
          <q-tab-panels
            v-model="currentTab"
            vertical
            animated
            transition-prev="jump-up"
            transition-next="jump-up"
          >
            <q-tab-panel v-for="tab in tabs" :key="tab.name" :name="tab.name" class="q-pa-none">
              <component :is="tab.component" v-bind="tab.props" v-on="tab.on || {}" />
            </q-tab-panel>
          </q-tab-panels>
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>

<style lang="scss" scoped>
.player-header {
  display: grid;
  grid-template-rows: min-content 1fr;

  > * {
    display: grid;
    grid-template-rows: subgrid;
    grid-row: span 2;
  }
}

@media (min-width: $breakpoint-md-min) {
  .player-header {
    grid-template-columns: max-content 1fr;

    > *:first-child {
      max-width: 400px;
    }
  }
}

.player-details {
  tr {
    td:first-child {
      width: 0;
      font-weight: 600;
    }
    td:last-child {
      white-space: unset;
    }
  }
}

.status-chips {
  .q-chip {
    margin: 0;
    font-size: 12px;
    font-weight: 600;
  }
}
</style>

<script>
import PlayerBypassCapDialog from '@/Components/Dialogs/PlayerBypassCap.vue'
import PlayerWhitelistDialog from '@/Components/Dialogs/PlayerWhitelist.vue'
import PlayerAvatar from '@/Components/PlayerAvatar.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import {
  ionAdd,
  ionBan,
  ionEarth,
  ionInformationCircleOutline,
  ionMedal,
  ionPencil,
  ionPeople,
  ionRemove,
  ionToggle,
} from '@quasar/extras/ionicons-v6'
import { mdiBird } from '@quasar/extras/mdi-v7'
import dayjs from 'dayjs'
import BanHistory from './Partials/BanHistory.vue'
import CompIds from './Partials/CompIds.vue'
// import Connections from './Partials/Connections.vue'
import Ips from './Partials/Ips.vue'
import JobBanHistory from './Partials/JobBanHistory.vue'
import Medals from './Partials/Medals.vue'
import Notes from './Partials/Notes.vue'
import OtherAccounts from './Partials/OtherAccounts.vue'

export default {
  layout: (h, page) =>
    h(
      DashboardLayout,
      {
        title: `Player ${page.props.player.key || page.props.player.ckey}`,
      },
      () => page
    ),

  components: {
    PlayerAvatar,
    Ips,
    CompIds,
    // Connections,
    BanHistory,
    JobBanHistory,
    Notes,
    Medals,
    OtherAccounts,
    PlayerBypassCapDialog,
    PlayerWhitelistDialog,
  },

  setup() {
    return {
      dayjs,
      ionEarth,
      ionBan,
      ionAdd,
      ionRemove,
      ionPencil,
      ionPeople,
      ionInformationCircleOutline,
      ionMedal,
      mdiBird,
      ionToggle,
    }
  },

  props: {
    player: Object,
    // connectionsByDay: Object,
    latestRound: Object,
    banHistory: Object,
    otherAccounts: Object,
    cursedCompIds: Object,
    uniqueIps: Object,
    uniqueCompIds: Object,
  },

  data() {
    return {
      currentTab: 'bans',
      toggleMentorLoading: false,
      toggleHosLoading: false,
      toggleWhitelistedDialog: false,
      toggleBypassCapDialog: false,
    }
  },

  computed: {
    tabs() {
      return [
        // {
        //   name: 'connections',
        //   label: 'Connections',
        //   component: Connections,
        //   props: {
        //     connectionsByDay: this.connectionsByDay,
        //   },
        // },
        {
          name: 'bans',
          label: 'Bans',
          total: this.banHistory.length,
          component: BanHistory,
          props: { bans: this.banHistory, ckey: this.player.ckey },
        },
        {
          name: 'job-bans',
          label: 'Job Bans',
          total: this.player.job_bans.length,
          component: JobBanHistory,
          props: { bans: this.player.job_bans },
        },
        {
          name: 'notes',
          label: 'Notes',
          total: this.player.notes.length,
          component: Notes,
          props: { modelValue: this.player.notes, playerCkey: this.player.ckey },
          on: {
            'update:modelValue': (e) => {
              this.$page.props.player.notes = e
            },
          },
        },
        {
          name: 'medals',
          label: 'Medals',
          total: this.player.medals.length,
          component: Medals,
          props: { modelValue: this.player.medals, playerId: this.player.id },
          on: {
            'update:modelValue': (e) => {
              this.$page.props.player.medals = e
            },
          },
        },
        {
          name: 'other-accounts',
          label: 'Other Accounts',
          total: this.otherAccounts.length,
          component: OtherAccounts,
          props: { accounts: this.otherAccounts },
        },
      ]
    },

    totalPlaytime() {
      if (!this.player.playtime.length) return 0
      const seconds = this.player.playtime
        .map((item) => item.seconds_played)
        .reduce((prev, next) => prev + next)
      return Math.round((seconds / 3600 + Number.EPSILON) * 100) / 100
    },

    isBanned() {
      let banned = false
      for (const ban of this.banHistory) {
        if (ban.active && ban.player_has_active_details) {
          banned = true
          break
        }
      }
      return banned
    },
  },

  methods: {
    async toggleMentor(makeMentor, selected) {
      this.toggleMentorLoading = true
      const { data } = await axios.post(this.$route('admin.mentors.bulk-toggle'), {
        player_ids: selected.map((row) => row.id),
        make_mentor: makeMentor,
      })

      this.$q.notify({ message: data.message, color: 'positive' })
      this.toggleMentorLoading = false
      this.$page.props.player.is_mentor = makeMentor
    },

    async toggleHos(makeHos, selected) {
      this.toggleHosLoading = true
      const { data } = await axios.post(this.$route('admin.hos.bulk-toggle'), {
        player_ids: selected.map((row) => row.id),
        make_hos: makeHos,
      })

      this.$q.notify({ message: data.message, color: 'positive' })
      this.toggleHosLoading = false
      this.$page.props.player.is_hos = makeHos
    },
  },
}
</script>
