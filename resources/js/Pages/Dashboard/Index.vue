<template>
  <div v-if="isGameAdmin">
    <health-list :health="health" class="q-my-md" />

    <q-card class="gh-card q-mb-md" flat>
      <div class="gh-card__header q-pa-md bordered">
        <span>Hello</span>
      </div>
      <q-card-section>
        This is the admin dashboard. It is in active development so large parts are missing or
        incomplete. Please make a Github issue
        <a href="https://github.com/goonstation/goonhub/issues" target="_blank">here</a> if you have
        a bug report or feature request.
      </q-card-section>
    </q-card>

    <div class="row q-col-gutter-md">
      <div class="col col-md-6 col-lg-4">
        <server-orchestration />
      </div>
    </div>
  </div>
  <div v-else>
    <q-card class="gh-card q-mb-md" flat>
      <div class="gh-card__header q-pa-md bordered">
        <span>Hello</span>
      </div>
      <q-card-section>
        <p>
          Welcome to the Goonstation Dashboard, this is a work in progress and will be updated in
          the future.
        </p>
        <p class="q-mb-none">
          For now, you can manage your profile by clicking the profile button in the top right.
        </p>
      </q-card-section>
    </q-card>
  </div>
</template>

<script>
import ServerOrchestration from '@/Components/Orchestration/Manager.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import HealthList from './Partials/HealthList.vue'

export default {
  layout: (h, page) => h(DashboardLayout, { title: 'Dashboard' }, () => page),

  components: {
    ServerOrchestration,
    HealthList,
  },

  props: {
    health: [Object, null],
  },

  computed: {
    isGameAdmin() {
      return !!this.$page.props.auth.user.game_admin_id || !!this.$page.props.auth.user.is_admin
    },
  },
}
</script>
