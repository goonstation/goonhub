<template>
  <notes-form
    :fields="fields"
    :submit-route="route('admin.notes.store')"
    success-message="Note added"
  />
</template>

<script>
import NotesForm from '@/Components/Forms/NotesForm.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    NotesForm
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Add Note' }, () => page),

  data() {
    return {
      fields: {
        game_admin_id: this.$page.props.auth.user.game_admin.id,
        ckey: null,
        server_id: null,
        note: null,
      },
    }
  }
}
</script>
