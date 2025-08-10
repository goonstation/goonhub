<template>
  <Alert v-if="status" :message="status" type="success" />

  <form method="POST" @submit.prevent="submit">
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

    <div class="form-actions">
      <Button :disabled="form.processing" icon="refresh" type="submit">Reset Password</Button>

      <div class="links" style="justify-content: center">
        <Link :href="route('game-auth.show-login')">Remembered your password?</Link>
      </div>
    </div>
  </form>
</template>

<script setup>
import Alert from '@/Components/Alert.vue'
import Button from '@/Components/Button.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'

defineOptions({
  layout: (h, page) => h(AppLayout, { title: 'Reset Password' }, () => page),
})

defineProps({
  status: String,
})

const form = useForm({
  email: '',
})

const submit = () => {
  form.post(route('password.email'), {
    onFinish: () => form.reset('email'),
  })
}
</script>
