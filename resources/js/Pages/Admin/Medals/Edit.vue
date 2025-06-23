<template>
  <medals-form
    :fields="fields"
    :submit-route="route('admin.medals.update', { medal: medal.id })"
    :medal="medal"
    state="edit"
    success-message="Medal updated"
    submit-method="post"
  />
</template>

<script>
import MedalsForm from '@/Components/Forms/MedalsForm.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    MedalsForm
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Edit Medal' }, () => page),

  props: {
    medal: Object,
  },

  data() {
    return {
      fields: {
        _method: 'put',
        image: null,
        title: this.medal.title,
        description: this.medal.description,
        hidden: this.medal.hidden,
        clear_image: false,
      },
    }
  }
}
</script>
