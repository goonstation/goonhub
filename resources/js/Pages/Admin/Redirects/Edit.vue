<template>
  <redirects-form
    state="edit"
    :fields="fields"
    :submit-route="route('admin.redirects.update', { redirect: redirect.id })"
    submit-method="put"
    success-message="Redirect updated"
  />
</template>

<script>
import RedirectsForm from '@/Components/Forms/RedirectsForm.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    RedirectsForm,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Edit Redirect' }, () => page),

  props: {
    redirect: Object,
  },

  data() {
    return {
      fields: {
        from: this.redirect.from,
        to: this.redirect.to,
      },
    }
  },
}
</script>
