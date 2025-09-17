<template>
  <bans-form
    state="edit"
    :fields="fields"
    :submit-route="route('admin.bans.update', { ban: ban.id })"
    submit-method="put"
    success-message="Ban updated"
  />
</template>

<script>
import BansForm from '@/Components/Forms/BansForm.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

export default {
  components: {
    BansForm,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Edit Ban' }, () => page),

  props: {
    ban: Object,
  },

  data() {
    return {
      fields: {}
    }
  },

  created() {
    this.fields = {
      game_admin_id: this.$page.props.auth.user.game_admin.id,
      ckey: this.ban.original_ban_detail.ckey,
      comp_id: this.ban.original_ban_detail.comp_id,
      ip: this.ban.original_ban_detail.ip,
      server_id: this.ban.server_id || 'all',
      reason: this.ban.reason,
      duration: this.ban.duration,
      expires_at: this.ban.expires_at,
      requires_appeal: this.ban.requires_appeal,
    }
  }
}
</script>
