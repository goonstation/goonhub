<template>
  <app-head :title="title" />
  <q-layout view="lhh LpR fff" style="max-width: 1920px">
    <q-header class="bg-transparent">
      <q-toolbar class="q-pt-md">
        <q-btn dense flat round :icon="ionMenu" @click="siteNavOpen = !siteNavOpen" />
        <q-toolbar-title>
          <page-back class="q-mr-sm" />
          <slot v-if="$slots.header" name="header" />
          <template v-else>{{ title }}</template>
        </q-toolbar-title>
        <div>
          <q-btn
            v-if="$page.props.jetstream.hasTeamFeatures && $page.props.auth.user.current_team"
            :label="$page.props.auth.user.current_team.name"
            class="q-px-sm q-mr-md"
            :icon-right="ionChevronDown"
            flat
            no-caps
          >
            <q-menu>
              <q-list style="min-width: 150px">
                <q-item-label header>Manage Team</q-item-label>
                <q-item
                  clickable
                  @click="router.visit($route('teams.show', $page.props.auth.user.current_team))"
                  v-close-popup
                >
                  <q-item-section>Team Settings</q-item-section>
                </q-item>
                <q-item
                  v-if="$page.props.jetstream.canCreateTeams"
                  clickable
                  @click="router.visit($route('teams.create'))"
                  v-close-popup
                >
                  <q-item-section>Create New Team</q-item-section>
                </q-item>

                <q-separator />

                <q-item-label header>Switch Teams</q-item-label>
                <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                  <q-item clickable @click="switchToTeam(team)">
                    <q-item-section>{{ team.name }}</q-item-section>
                    <q-item-section avatar style="padding-left: 0; min-width: 35px">
                      <q-icon
                        v-if="team.id == $page.props.auth.user.current_team_id"
                        :name="ionCheckmarkCircleOutline"
                        color="accent"
                      />
                    </q-item-section>
                  </q-item>
                </template>
              </q-list>
            </q-menu>
          </q-btn>

          <q-btn round flat>
            <user-avatar :user="$page.props.auth.user" />
            <q-menu>
              <q-list style="min-width: 150px">
                <q-item clickable @click="router.visit($route('profile.show'))" v-close-popup>
                  <q-item-section>Profile</q-item-section>
                </q-item>
                <q-item
                  v-if="$page.props.jetstream.hasApiFeatures && user.is_admin"
                  clickable
                  @click="router.visit($route('api-tokens.index'))"
                  v-close-popup
                >
                  <q-item-section>API Tokens</q-item-section>
                </q-item>

                <q-separator />

                <template v-if="user.is_admin">
                  <q-item-label header>Admin Tools</q-item-label>

                  <q-item
                    clickable
                    @click="router.visit($route('admin.users.index'))"
                    v-close-popup
                  >
                    <q-item-section>Users</q-item-section>
                  </q-item>

                  <q-separator />
                </template>

                <q-item clickable @click="logout" v-close-popup>
                  <q-item-section>Log Out</q-item-section>
                </q-item>
              </q-list>
            </q-menu>
          </q-btn>
        </div>
      </q-toolbar>
    </q-header>

    <site-nav v-model:open="siteNavOpen" :home="$route('dashboard')" :items="siteNavItems">
      <template #bottom>
        <q-separator />
        <div class="site-nav__item">
          <Link :href="$route('home')" class="back-to-site site-nav__item q-pa-sm">
            <div class="site-nav__label">
              <q-icon :name="ionArrowBackCircleOutline" size="2em" />
              Back To Site
            </div>
          </Link>
        </div>
      </template>
    </site-nav>

    <q-page-container>
      <q-page class="row column no-wrap q-pa-sm q-pa-md-md page-wrapper">
        <slot />
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<style lang="scss" scoped>
.back-to-site {
  font-size: 0.8em;
  opacity: 0.8;

  .site-nav__label {
    display: flex;
    align-items: center;
    gap: 10px;
    line-height: 1;
  }
}
</style>

<script>
import AppHead from '@/Components/AppHead.vue'
import PageBack from '@/Components/PageBack.vue'
import SiteNav from '@/Components/SiteNav/SiteNav.vue'
import UserAvatar from '@/Components/UserAvatar.vue'
import { router } from '@inertiajs/vue3'
import {
  ionArrowBackCircleOutline,
  ionCheckmarkCircleOutline,
  ionChevronDown,
  ionMenu,
} from '@quasar/extras/ionicons-v6'

export default {
  components: {
    AppHead,
    SiteNav,
    PageBack,
    UserAvatar,
  },

  props: {
    title: String,
  },

  setup() {
    return {
      router,
      ionMenu,
      ionChevronDown,
      ionCheckmarkCircleOutline,
      ionArrowBackCircleOutline,
    }
  },

  data() {
    return {
      siteNavOpen: true,
      siteNavItems: [],
    }
  },

  computed: {
    user() {
      return this.$page.props.auth.user
    },
  },

  created() {
    this.siteNavItems = this.buildSiteNavItems()
  },

  methods: {
    switchToTeam(team) {
      router.put(route('current-team.update'), { team_id: team.id }, { preserveState: false })
    },

    logout() {
      router.post(route('logout'))
    },

    buildSiteNavItems() {
      const items = [
        {
          label: 'Dashboard',
          href: route('dashboard'),
          separator: true,
        },
      ]

      if (!!this.user.game_admin_id || !!this.user.is_admin) {
        items.push(
          {
            label: 'Admins',
            match: [route('admin.game-admins.index'), route('admin.game-admin-ranks.index')],
            children: [
              {
                label: 'Admin List',
                href: route('admin.game-admins.index'),
              },
              {
                label: 'Ranks',
                href: route('admin.game-admin-ranks.index'),
              },
            ],
          },
          {
            label: 'Players',
            match: [
              route('admin.players.index'),
              route('admin.bans.index'),
              route('admin.job-bans.index'),
              route('admin.notes.index'),
              route('admin.mentors.index'),
              route('admin.hos.index'),
              route('admin.whitelist.index'),
            ],
            children: [
              {
                label: 'Player List',
                href: route('admin.players.index'),
              },
              {
                label: 'Bans',
                href: route('admin.bans.index'),
              },
              {
                label: 'Job Bans',
                href: route('admin.job-bans.index'),
              },
              {
                label: 'Notes',
                href: route('admin.notes.index'),
              },
              {
                label: 'Mentors',
                href: route('admin.mentors.index'),
              },
              {
                label: 'Heads of Staff',
                href: route('admin.hos.index'),
              },
              {
                label: 'Whitelist',
                href: route('admin.whitelist.index'),
              },
            ],
          },
          {
            label: 'Game',
            match: [
              route('admin.maps.index'),
              route('admin.events.index'),
              route('admin.logs.index'),
              route('admin.errors.index'),
              route('admin.medals.index'),
              route('admin.builds.index'),
            ],
            children: [
              {
                label: 'Builds',
                href: route('admin.builds.index'),
              },
              {
                label: 'Errors',
                href: route('admin.errors.index'),
              },
              {
                label: 'Events',
                href: route('admin.events.index'),
              },
              {
                label: 'Logs',
                href: route('admin.logs.index'),
              },
              {
                label: 'Maps',
                href: route('admin.maps.index'),
              },
              {
                label: 'Medals',
                href: route('admin.medals.index'),
              },
            ],
          },
          {
            label: 'Site',
            match: [route('admin.audit.index'), route('admin.redirects.index')],
            children: [
              {
                label: 'Audit Logs',
                href: route('admin.audit.index'),
              },
              {
                label: 'Redirects',
                href: route('admin.redirects.index'),
              },
            ],
          }
        )
      }

      return items
    },
  },
}
</script>
