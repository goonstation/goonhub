<template>
  <q-card class="gh-card" flat style="width: 100%; max-width: 500px">
    <div class="gh-card__header">
      <q-icon :name="ionHelpCircle" size="22px" />
      <span>Forgot Password</span>
    </div>

    <q-card-section>
      <div class="q-mb-md text-sm text-grey-5">
        Forgot your password? No problem. Just let us know your email address and we will email you
        a password reset link that will allow you to choose a new one.
      </div>

      <q-banner v-if="status" class="bg-positive text-dark q-mb-md" dense>
        {{ status }}
      </q-banner>

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

        <div class="flex q-mt-sm">
          <q-btn
            @click="$inertia.visit($route('login'))"
            label="Back to Login"
            color="primary"
            flat
          />
          <q-space />
          <q-btn
            label="Email Password Reset Link"
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
  layout: (h, page) => h(AuthLayout, { title: 'Forgot Password' }, () => page),

  setup() {
    return {
      ionHelpCircle,
    }
  },

  props: {
    status: String,
  },

  data() {
    return {
      form: useForm({
        email: '',
      }),
    }
  },

  methods: {
    submit() {
      this.form.post(route('password.email'))
    },
  },
}
</script>
