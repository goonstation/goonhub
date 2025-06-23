<template>
  <bans-form
    :fields="fields"
    :submit-route="route('admin.bans.store')"
    success-message="Ban added"
  />
</template>

<script>
import BansForm from '@/Components/Forms/BansForm.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    BansForm
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Add Ban' }, () => page),

  data() {
    return {
      fields: {
        game_admin_id: this.$page.props.auth.user.game_admin_id,
        ckey: null,
        comp_id: null,
        ip: null,
        server_id: 'all',
        reason: null,
        duration: null,
        requires_appeal: false,
      },
    }
  }
}
</script>
