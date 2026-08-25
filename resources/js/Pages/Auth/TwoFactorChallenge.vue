<template>
  <q-card class="gh-card" flat style="width: 100%; max-width: 500px">
    <div class="gh-card__header">
      <q-icon :name="ionLockClosed" size="22px" />
      <span>Two-factor Confirmation</span>
    </div>

    <q-card-section>
      <div class="q-mb-md text-sm">
        <template v-if="!recovery">
          Please confirm access to your account by entering the authentication code provided by your
          authenticator application.
        </template>

        <template v-else>
          Please confirm access to your account by entering one of your emergency recovery codes.
        </template>
      </div>

      <q-form @submit="submit">
        <div v-if="!recovery">
          <q-input
            v-model="form.code"
            ref="codeInput"
            type="text"
            inputmode="numeric"
            label="Code"
            filled
            lazy-rules
            required
            autofocus
            autocomplete="one-time-code"
            :error="!!form.errors.code"
            :error-message="form.errors.code"
          />
        </div>

        <div v-else>
          <q-input
            v-model="form.recovery_code"
            ref="recoveryCodeInput"
            type="text"
            label="Recovery Code"
            filled
            lazy-rules
            required
            autocomplete="one-time-code"
            :error="!!form.errors.recovery_code"
            :error-message="form.errors.recovery_code"
          />
        </div>

        <div class="flex">
          <q-space />

          <q-btn type="button" color="grey-6" flat @click="toggleRecovery">
            <template v-if="!recovery"> Use a recovery code </template>

            <template v-else> Use an authentication code </template>
          </q-btn>

          <q-btn
            label="Log In"
            type="submit"
            color="primary"
            text-color="black"
            class="q-ml-md"
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
import { ionLockClosed } from '@quasar/extras/ionicons-v6'
import { nextTick } from 'vue'

export default {
  layout: (h, page) => h(AuthLayout, { title: 'Two-factor Confirmation' }, () => page),

  setup() {
    return {
      ionLockClosed,
    }
  },

  props: {
    email: String,
    token: String,
  },

  data() {
    return {
      recovery: false,
      form: useForm({
        code: '',
        recovery_code: '',
      }),
    }
  },

  methods: {
    async toggleRecovery() {
      this.recovery ^= true

      await nextTick()

      if (this.recovery) {
        this.$refs.recoveryCodeInput.focus()
        this.form.code = ''
      } else {
        this.$refs.codeInput.focus()
        this.form.recovery_code = ''
      }
    },

    submit() {
      this.form.post(route('web.two-factor.login'))
    },
  },
}
</script>
