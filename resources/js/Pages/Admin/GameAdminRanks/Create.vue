<template>
  <game-admin-rank-form />
</template>

<script>
import GameAdminRankForm from '@/Components/Forms/GameAdminRankForm.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    GameAdminRankForm
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Create Admin Rank' }, () => page),
}
</script>
