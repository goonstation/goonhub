<template>
  <notes-form
    state="edit"
    :fields="fields"
    :submit-route="route('admin.notes.update', { note: note.id })"
    submit-method="put"
    success-message="Note updated"
  />
</template>

<script>
import NotesForm from '@/Components/Forms/NotesForm.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    NotesForm
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Edit Note' }, () => page),

  props: {
    note: Object,
  },

  data() {
    return {
      fields: {
        game_admin_id: this.$page.props.auth.user.game_admin.id,
        ckey: this.note?.player?.ckey || this.note.ckey,
        server_id: this.note.server_id || 'all',
        note: this.note.note,
      },
    }
  }
}
</script>
