<template>
  <mentors-form
    :fields="fields"
    :submit-route="$route('admin.mentors.store')"
    success-message="Mentors added"
  />
</template>

<script>
import MentorsForm from '@/Components/Forms/MentorsForm.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

export default {
  components: {
    MentorsForm,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Add Mentors' }, () => page),

  data() {
    return {
      fields: {
        player_ids: [],
      },
    }
  },
}
</script>
