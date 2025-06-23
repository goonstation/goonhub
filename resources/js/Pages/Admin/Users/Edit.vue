<template>
  <users-form
    state="edit"
    :fields="fields"
    :submit-route="route('admin.users.update', { user: editUser.id })"
    submit-method="put"
    success-message="User updated"
  />
</template>

<script>
import UsersForm from '@/Components/Forms/UsersForm.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    UsersForm,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Edit User' }, () => page),

  props: {
    editUser: Object,
  },

  data() {
    return {
      fields: {
        ...this.editUser,
        password: null,
        confirm_password: null,
      },
    }
  }
}
</script>
