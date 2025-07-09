<template>
  <app-head :title="title" />
  <q-layout view="lhh LpR fff">
    <KeepAlive>
      <page-header :title="title" @onToggleLeftDrawer="siteNavOpen = !siteNavOpen" />
    </KeepAlive>

    <site-nav v-model:open="siteNavOpen" :items="siteNavItems">
      <template #bottom>
        <q-separator />
        <div class="site-nav__item">
          <Link :href="$route('dashboard')" class="dashboard-login site-nav__item q-pa-sm">
            <div class="site-nav__label">
              <q-icon :name="ionLogInOutline" size="2em" />
              Dashboard
            </div>
          </Link>
        </div>
      </template>
    </site-nav>

    <q-page-container>
      <q-page class="row column no-wrap q-pa-md page-wrapper">
        <div>
          <slot />
        </div>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<style lang="scss" scoped>
.page-wrapper {
  position: relative;
  z-index: 2;
  border-radius: 20px;
  margin-top: -20px;
  background: var(--q-dark-page);
  background-clip: padding-box;
  border-top: 1px solid rgba(255, 255, 255, 0.1);

  > div {
    max-width: 1500px;
  }
}

.dashboard-login {
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
import PageHeader from '@/Components/PageHeader.vue'
import SiteNav from '@/Components/SiteNav/SiteNav.vue'
import { ionLogInOutline } from '@quasar/extras/ionicons-v6'

export default {
  components: {
    AppHead,
    PageHeader,
    SiteNav,
  },

  props: {
    title: String,
  },

  setup() {
    return {
      ionLogInOutline,
    }
  },

  data() {
    return {
      siteNavOpen: true,
      siteNavItems: [
        {
          label: 'Home',
          href: route('home'),
        },
        {
          label: 'Rounds',
          href: route('rounds.index'),
        },
        {
          label: 'Players',
          match: route('players.index'),
          children: [
            {
              label: 'Overview',
              href: route('players.index'),
            },
            {
              label: 'Search',
              href: route('players.search'),
            },
          ],
        },
        {
          label: 'Events',
          match: route('events.index'),
          children: [
            {
              label: 'Overview',
              href: route('events.index'),
            },
            {
              label: 'Antagonists',
              href: route('antags.index'),
            },
            {
              label: 'Deaths',
              href: route('deaths.index'),
            },
            {
              label: 'Errors',
              href: route('errors.index'),
            },
            {
              label: 'Fines',
              href: route('fines.index'),
            },
            {
              label: 'Tickets',
              href: route('tickets.index'),
            },
          ],
        },
        {
          label: 'Medals',
          href: route('medals.index'),
        },
        {
          label: 'Maps',
          href: route('maps.index'),
        },
      ],
    }
  },
}
</script>
