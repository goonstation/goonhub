<template>
  <q-form @submit="updatePassword">
    <q-input
      v-if="!$page.props.auth.user.passwordless"
      v-model="form.current_password"
      ref="currentPasswordInput"
      class="q-mb-sm"
      type="password"
      label="Current Password"
      filled
      required
      hide-bottom-space
      autocomplete="current-password"
      :error="!!form.errors.current_password"
      :error-message="form.errors.current_password"
    />

    <q-input
      v-model="form.password"
      ref="passwordInput"
      class="q-mb-sm"
      type="password"
      label="New Password"
      filled
      required
      hide-bottom-space
      autocomplete="new-password"
      :error="!!form.errors.password"
      :error-message="form.errors.password"
    />

    <q-input
      v-model="form.password_confirmation"
      class="q-mb-sm"
      type="password"
      label="Confirm Password"
      filled
      required
      hide-bottom-space
      autocomplete="new-password"
      :error="!!form.errors.password_confirmation"
      :error-message="form.errors.password_confirmation"
    />

    <div class="flex items-center q-mt-md">
      <q-space />
      <ActionMessage :on="form.recentlySuccessful" class="q-mr-sm">Saved</ActionMessage>
      <q-btn label="Save" type="submit" color="primary" :loading="form.processing" flat />
    </div>
  </q-form>
</template>

<script>
import ActionMessage from '@/Components/ActionMessage.vue'
import { useForm } from '@inertiajs/vue3'

export default {
  components: {
    ActionMessage,
  },

  data() {
    return {
      form: useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
      }),
    }
  },

  methods: {
    updatePassword() {
      this.form.put(route('user-password.update'), {
        errorBag: 'updatePassword',
        preserveScroll: true,
        onSuccess: () => {
          this.$q.notify({
            message: 'Password updated',
            color: 'positive',
          })
          this.form.reset()
        },
        onError: () => {
          if (this.form.errors.password) {
            this.form.reset('password', 'password_confirmation')
            this.$refs.passwordInput.focus()
          }

          if (this.form.errors.current_password) {
            this.form.reset('current_password')
            this.$refs.currentPasswordInput.focus()
          }
        },
      })
    },
  },
}
</script>
