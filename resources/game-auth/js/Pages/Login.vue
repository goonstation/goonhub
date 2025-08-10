<template>
  <Button
    :href="route('game-auth.discord-redirect')"
    icon="logo-discord"
    bg-color="#222539"
    bg-line-color="#191b2b"
    border-color="#5865f2"
  >
    Login with Discord
  </Button>

  <div class="separator">
    <span>Or</span>
  </div>

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

    <fieldset>
      <label for="password">Password</label>
      <input
        v-model="form.password"
        id="password"
        type="password"
        name="password"
        placeholder=" "
        required
      />
    </fieldset>
    <div v-if="form.errors.password" class="input-error">{{ form.errors.password }}</div>

    <div class="form-actions">
      <Button :disabled="form.processing" icon="log-in" type="submit">Login</Button>

      <div class="links">
        <Link :href="route('game-auth.show-forgot')">Forgot password?</Link>
        <Button
          :href="route('game-auth.show-register')"
          icon="arrow-redo"
          class="register"
          bg-color="var(--color-secondary-dark)"
          bg-line-color="var(--color-secondary-darker)"
          border-color="var(--color-secondary)"
          inline
        >
          Create an account
        </Button>
      </div>
    </div>
  </form>
</template>

<style lang="scss" scoped>
.separator {
  display: grid;
  grid-template-columns: 1fr min-content 1fr;
  align-items: center;
  margin: 2rem 0;
  text-align: center;

  span {
    display: inline-block;
    position: relative;
    padding: 0 1rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-weight: 600;
    font-size: 0.9rem;
  }

  &:before,
  &:after {
    content: '';
    height: 1px;
    background: rgba(255, 255, 255, 0.07);
  }
}

.form-actions .register {
  margin-bottom: 0;
  font-size: 0.85em;
}
</style>

<script setup>
import Button from '@/Components/Button.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'

defineOptions({
  layout: (h, page) => h(AppLayout, { title: 'Login' }, () => page),
})

const form = useForm({
  email: '',
  password: '',
})

const submit = () => {
  form.post(route('game-auth.login'), {
    onFinish: () => form.reset('password'),
  })
}
</script>
