<template>
  <job-bans-form
    :fields="fields"
    :submit-route="route('admin.job-bans.store')"
    success-message="Job ban added"
  />
</template>

<script>
import JobBansForm from '@/Components/Forms/JobBansForm.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    JobBansForm
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Add Job Ban' }, () => page),

  data() {
    return {
      fields: {
        game_admin_id: this.$page.props.auth.user.game_admin.id,
        ckey: null,
        server_id: 'all',
        reason: null,
        job: null,
        duration: null,
      },
    }
  }
}
</script>
