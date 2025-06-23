<template>
  <redirects-form
    :fields="fields"
    :submit-route="route('admin.redirects.store')"
    success-message="Redirect added"
  />
</template>

<script>
import RedirectsForm from '@/Components/Forms/RedirectsForm.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    RedirectsForm,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Add Redirect' }, () => page),

  data() {
    return {
      fields: {
        from: '',
        to: '',
      },
    }
  },
}
</script>
