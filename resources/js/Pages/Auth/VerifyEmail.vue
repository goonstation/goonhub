<template>
  <q-card class="gh-card" flat style="width: 100%; max-width: 500px">
    <div class="gh-card__header">
      <q-icon :name="ionMail" size="22px" />
      <span>Email Verification</span>
    </div>

    <q-card-section>
      <q-banner v-if="$page.props.flash.error" class="bg-negative q-mb-md" dense>
        {{ $page.props.flash.error }}
      </q-banner>

      <div class="q-mb-md text-sm text-grey">
        Before continuing, could you verify your email address by clicking on the link we just
        emailed to you? If you didn't receive the email, we will gladly send you another.
      </div>

      <div v-if="verificationLinkSent" class="q-mb-md text-sm text-green">
        A new verification link has been sent to the email address you provided in your profile
        settings.
      </div>

      <q-form @submit="submit" class="flex items-center justify-center q-mb-lg">
        <q-btn
          label="Resend Verification Email"
          type="submit"
          color="primary"
          text-color="black"
          :loading="form.processing"
        />
      </q-form>

      <div class="flex items-center justify-between text-sm">
        <Link :href="route('web.profile.show')">Edit Profile</Link>
        <a href="" @click.prevent="logout">Log Out</a>
      </div>
    </q-card-section>
  </q-card>
</template>

<script>
import AuthLayout from '@/Layouts/AuthLayout.vue'
import { router, useForm } from '@inertiajs/vue3'
import { ionMail } from '@quasar/extras/ionicons-v6'

export default {
  layout: (h, page) => h(AuthLayout, { title: 'Email Verification' }, () => page),

  props: {
    status: String,
  },

  setup() {
    return {
      ionMail,
    }
  },

  data() {
    return {
      form: useForm({}),
    }
  },

  computed: {
    verificationLinkSent() {
      return this.status === 'verification-link-sent'
    },
  },

  methods: {
    submit() {
      this.form.post(route('web.verification.send'))
    },

    logout() {
      router.post(route('web.logout'))
    },
  },
}
</script>
