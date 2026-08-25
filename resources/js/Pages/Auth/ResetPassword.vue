<template>
  <q-card class="gh-card" flat style="width: 100%; max-width: 500px">
    <div class="gh-card__header">
      <q-icon :name="ionHelpCircle" size="22px" />
      <span>Reset Password</span>
    </div>

    <q-card-section>
      <q-form @submit="submit">
        <q-input
          v-model="form.email"
          type="email"
          label="Email"
          filled
          lazy-rules
          required
          autofocus
          :error="!!form.errors.email"
          :error-message="form.errors.email"
        />

        <q-input
          v-model="form.password"
          type="password"
          label="Password"
          filled
          lazy-rules
          required
          autocomplete="new-password"
          :error="!!form.errors.password"
          :error-message="form.errors.password"
        />

        <q-input
          v-model="form.password_confirmation"
          type="password"
          label="Confirm Password"
          filled
          lazy-rules
          required
          autocomplete="new-password"
          :error="!!form.errors.password_confirmation"
          :error-message="form.errors.password_confirmation"
        />

        <div class="flex">
          <q-space />
          <q-btn
            label="Reset Password"
            type="submit"
            color="primary"
            text-color="black"
            :loading="form.processing"
          />
        </div>
      </q-form>
    </q-card-section>
  </q-card>
</template>

<script>
import AuthLayout from '@/Layouts/AuthLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { ionHelpCircle } from '@quasar/extras/ionicons-v6'

export default {
  layout: (h, page) => h(AuthLayout, { title: 'Reset Password' }, () => page),

  setup() {
    return {
      ionHelpCircle,
    }
  },

  props: {
    email: String,
    token: String,
  },

  data() {
    return {
      form: useForm({
        token: this.token,
        email: this.email,
        password: '',
        password_confirmation: '',
      }),
    }
  },

  methods: {
    submit() {
      this.form.post(route('web.password.update'), {
        onFinish: () => this.form.reset('password', 'password_confirmation'),
      })
    },
  },
}
</script>
