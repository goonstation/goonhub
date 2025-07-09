<template>
  <q-card class="gh-card" flat style="width: 100%; max-width: 500px">
    <div class="gh-card__header">
      <q-icon :name="ionLogIn" size="22px" />
      <span>Login</span>
    </div>

    <q-card-section>
      <q-banner v-if="status" class="bg-positive text-dark q-mb-md" dense>
        {{ status }}
      </q-banner>
      <q-banner v-if="$page.props.flash.error" class="bg-negative q-mb-md" dense>
        {{ $page.props.flash.error }}
      </q-banner>

      <a :href="$route('auth.discord-redirect')" class="login-discord q-mb-md rounded-borders">
        <q-icon :name="ionLogoDiscord" size="30px" class="q-mr-sm" />
        Login with Discord
      </a>

      <div class="q-mb-md text-center">Or login with email and password</div>

      <q-form @submit="submit">
        <q-input
          v-model="form.email"
          class="q-mb-md"
          type="email"
          label="Email"
          filled
          lazy-rules
          required
          autofocus
          hide-bottom-space
          :error="!!form.errors.email"
          :error-message="form.errors.email"
        />

        <q-input
          v-model="form.password"
          class="q-mb-md"
          type="password"
          label="Password"
          filled
          lazy-rules
          required
          autocomplete="current-password"
          hide-bottom-space
          :error="!!form.errors.password"
          :error-message="form.errors.password"
        />

        <div class="flex items-center q-mb-md">
          <q-toggle v-model="form.remember" label="Remember me" />
          <q-space />
          <Link v-if="canResetPassword" :href="$route('password.request')">
            Forgot your password?
          </Link>
        </div>

        <div class="flex">
          <q-btn
            @click="$inertia.visit($route('register'))"
            label="Create Account"
            color="primary"
            flat
          />
          <q-space />
          <q-btn
            label="Log In"
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

<style lang="scss" scoped>
@use 'sass:color';

.login-discord {
  display: block;
  padding: 10px;
  background: #7289da;
  text-align: center;
  color: white;
  font-size: 1.1em;
  font-weight: 500;
  text-transform: uppercase;

  &:hover,
  &:focus {
    background: color.adjust(#7289da, $lightness: -3%);
  }
}
</style>

<script>
import AuthLayout from '@/Layouts/AuthLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { ionLogIn, ionLogoDiscord } from '@quasar/extras/ionicons-v6'

export default {
  layout: (h, page) => h(AuthLayout, { title: 'Login' }, () => page),

  props: {
    canResetPassword: Boolean,
    status: String,
  },

  setup() {
    return {
      ionLogIn,
      ionLogoDiscord,
    }
  },

  data() {
    return {
      url: null,
      form: useForm({
        email: '',
        password: '',
        remember: false,
      }),
    }
  },

  created() {
    const urlParams = new URLSearchParams(window.location.search)
    const prev = urlParams.get('prev')
    if (prev) this.url = route('login', { prev })
    else this.url = route('login')
  },

  methods: {
    submit() {
      this.form
        .transform((data) => ({
          ...data,
          remember: this.form.remember ? 'on' : '',
        }))
        .post(this.url, {
          onFinish: () => this.form.reset('password'),
        })
    },
  },
}
</script>
