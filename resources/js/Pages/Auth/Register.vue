<template>
  <q-card class="gh-card" flat style="width: 100%; max-width: 500px">
    <div class="gh-card__header">
      <q-icon :name="ionPersonAdd" size="22px" />
      <span>Register</span>
    </div>

    <q-card-section>
      <q-banner v-if="$page.props.flash.error" class="bg-negative q-mb-md" dense>
        {{ $page.props.flash.error }}
      </q-banner>

      <q-form @submit="submit">
        <q-input
          v-model="form.name"
          class="q-mb-md"
          type="text"
          label="Name"
          autocomplete="name"
          pattern="^[a-zA-Z0-9\s]+$"
          title="The name can only contain letters, numbers, and spaces."
          filled
          lazy-rules
          required
          autofocus
          hide-bottom-space
          :error="!!form.errors.name || !!form.errors.ckey"
          :error-message="form.errors.name || form.errors.ckey"
        />

        <q-input
          v-model="form.email"
          class="q-mb-md"
          type="email"
          label="Email"
          autocomplete="email"
          filled
          lazy-rules
          required
          hide-bottom-space
          :error="!!form.errors.email"
          :error-message="form.errors.email"
        />

        <q-input
          v-model="form.password"
          class="q-mb-md"
          type="password"
          label="Password"
          autocomplete="new-password"
          filled
          lazy-rules
          required
          hide-bottom-space
          :error="!!form.errors.password"
          :error-message="form.errors.password"
        />

        <q-input
          v-model="form.password_confirmation"
          class="q-mb-md"
          type="password"
          label="Confirm Password"
          autocomplete="new-password"
          filled
          lazy-rules
          required
          hide-bottom-space
          :error="!!form.errors.password_confirmation"
          :error-message="form.errors.password_confirmation"
        />

        <div class="flex">
          <q-btn
            @click="$inertia.visit($route('login'))"
            label="Already registered?"
            color="primary"
            flat
          />
          <q-space />
          <q-btn
            label="Register"
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

<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { ionPersonAdd } from '@quasar/extras/ionicons-v6'

defineOptions({
  layout: (h, page) => h(AuthLayout, { title: 'Register' }, () => page),
})

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  terms: false,
})

const submit = () => {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>
