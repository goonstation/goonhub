<template>
  <whitelist-form
    :fields="fields"
    :submit-route="$route('admin.whitelist.update', { whitelistedPlayer: whitelistedPlayer.id })"
    success-message="Whitelisted player updated"
    submit-method="put"
    state="edit"
  />
</template>

<script>
import WhitelistForm from '@/Components/Forms/WhitelistForm.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

export default {
  components: {
    WhitelistForm,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Edit Whitelisted Player' }, () => page),

  props: {
    whitelistedPlayer: Object,
  },

  data() {
    return {
      fields: {
        player_ids: [this.whitelistedPlayer.player_id],
        server_ids: this.whitelistedPlayer.servers.map((server) => server.id),
      },
    }
  },
}
</script>
