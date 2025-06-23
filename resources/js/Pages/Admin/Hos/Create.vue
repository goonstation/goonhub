<template>
  <hos-form
    :fields="fields"
    :submit-route="$route('admin.hos.store')"
    success-message="Heads of Staff added"
  />
</template>

<script>
import HosForm from '@/Components/Forms/HosForm.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

export default {
  components: {
    HosForm,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Add Heads of Staff' }, () => page),

  data() {
    return {
      fields: {
        player_ids: [],
      },
    }
  },
}
</script>
