<template>
  <medals-form
    :fields="fields"
    :submit-route="route('admin.medals.store')"
    success-message="Medal added"
  />
</template>

<script>
import MedalsForm from '@/Components/Forms/MedalsForm.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    MedalsForm
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Add Medal' }, () => page),

  data() {
    return {
      fields: {
        image: null,
        title: null,
        description: null,
        hidden: false,
      },
    }
  }
}
</script>
