<template>
  <whitelist-form
    :fields="fields"
    :submit-route="$route('admin.whitelist.store')"
    success-message="Whitelisted players added"
  />
</template>

<script>
import WhitelistForm from '@/Components/Forms/WhitelistForm.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

export default {
  components: {
    WhitelistForm,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Add Whitelisted Players' }, () => page),

  data() {
    return {
      fields: {
        player_ids: [],
        server_ids: [],
      },
    }
  },
}
</script>
