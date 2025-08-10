<template>
  <form method="POST" @submit.prevent="submit">
    <fieldset>
      <label for="name">Username</label>
      <input
        v-model="form.name"
        id="name"
        type="text"
        name="name"
        value=""
        maxlength="255"
        autocomplete="name"
        placeholder=" "
        pattern="^[a-zA-Z0-9\s]+$"
        title="The username can only contain letters, numbers, and spaces."
        required
      />
    </fieldset>
    <div v-if="form.errors.name" class="input-error">{{ form.errors.name }}</div>

    <fieldset>
      <label for="email">Email</label>
      <input
        v-model="form.email"
        id="email"
        type="email"
        name="email"
        value=""
        maxlength="255"
        autocomplete="email"
        placeholder=" "
        required
      />
    </fieldset>
    <div v-if="form.errors.email" class="input-error">{{ form.errors.email }}</div>

    <fieldset>
      <label for="password">Password</label>
      <input
        v-model="form.password"
        id="password"
        type="password"
        name="password"
        value=""
        autocomplete="new-password"
        placeholder=" "
        required
      />
    </fieldset>
    <div v-if="form.errors.password" class="input-error">{{ form.errors.password }}</div>

    <fieldset>
      <label for="password_confirmation">Confirm Password</label>
      <input
        v-model="form.password_confirmation"
        id="password_confirmation"
        type="password"
        name="password_confirmation"
        value=""
        autocomplete="new-password"
        placeholder=" "
        required
      />
    </fieldset>
    <div v-if="form.errors.password_confirmation" class="input-error">
      {{ form.errors.password_confirmation }}
    </div>

    <div class="form-actions">
      <Button :disabled="form.processing" icon="log-in" type="submit">Register</Button>

      <div class="links" style="justify-content: center">
        <Link :href="route('game-auth.show-login')">Already registered?</Link>
      </div>
    </div>
  </form>
</template>

<script setup>
import Button from '@/Components/Button.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'

defineOptions({
  layout: (h, page) => h(AppLayout, { title: 'Register' }, () => page),
})

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post(route('game-auth.register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>
