<script setup>
import Alert from '@/Components/Alert.vue'
import ConfirmsPassword from '@/Components/ConfirmsPassword.vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'

const props = defineProps({
  requiresConfirmation: Boolean,
})

const enabling = ref(false)
const confirming = ref(false)
const disabling = ref(false)
const qrCode = ref(null)
const setupKey = ref(null)
const recoveryCodes = ref([])

const confirmationForm = useForm({
  code: '',
})

const twoFactorEnabled = computed(
  () => !enabling.value && usePage().props.auth.user?.two_factor_enabled
)

watch(twoFactorEnabled, () => {
  if (!twoFactorEnabled.value) {
    confirmationForm.reset()
    confirmationForm.clearErrors()
  }
})

const enableTwoFactorAuthentication = () => {
  enabling.value = true

  router.post(
    '/user/two-factor-authentication',
    {},
    {
      preserveScroll: true,
      onSuccess: () => Promise.all([showQrCode(), showSetupKey(), showRecoveryCodes()]),
      onFinish: () => {
        enabling.value = false
        confirming.value = props.requiresConfirmation
      },
    }
  )
}

const showQrCode = () => {
  return axios.get('/user/two-factor-qr-code').then((response) => {
    qrCode.value = response.data.svg
  })
}

const showSetupKey = () => {
  return axios.get('/user/two-factor-secret-key').then((response) => {
    setupKey.value = response.data.secretKey
  })
}

const showRecoveryCodes = () => {
  return axios.get('/user/two-factor-recovery-codes').then((response) => {
    recoveryCodes.value = response.data
  })
}

const confirmTwoFactorAuthentication = () => {
  confirmationForm.post('/user/confirmed-two-factor-authentication', {
    errorBag: 'confirmTwoFactorAuthentication',
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      confirming.value = false
      qrCode.value = null
      setupKey.value = null
    },
  })
}

const regenerateRecoveryCodes = () => {
  axios.post('/user/two-factor-recovery-codes').then(() => showRecoveryCodes())
}

const disableTwoFactorAuthentication = () => {
  disabling.value = true

  router.delete('/user/two-factor-authentication', {
    preserveScroll: true,
    onSuccess: () => {
      disabling.value = false
      confirming.value = false
    },
  })
}
</script>

<template>
  <Alert v-if="twoFactorEnabled && !confirming" type="positive">
    <div class="flex items-center justify-between gap-xs-sm">
      <span>You have enabled two factor authentication.</span>
      <div class="flex gap-xs-sm">
        <ConfirmsPassword v-if="recoveryCodes.length > 0" @confirmed="regenerateRecoveryCodes">
          <q-btn
            label="Regenerate Recovery Codes"
            type="button"
            color="positive"
            class="text-sm q-px-sm q-py-xs"
            flat
          />
        </ConfirmsPassword>
        <ConfirmsPassword v-if="recoveryCodes.length === 0" @confirmed="showRecoveryCodes">
          <q-btn
            label="Show Recovery Codes"
            type="button"
            color="positive"
            class="text-sm q-px-sm q-py-xs"
            flat
          />
        </ConfirmsPassword>
        <ConfirmsPassword @confirmed="disableTwoFactorAuthentication">
          <q-btn
            label="Disable"
            type="button"
            color="negative"
            class="text-sm text-weight-medium q-px-sm q-py-xs"
            :loading="disabling"
            flat
          />
        </ConfirmsPassword>
      </div>
    </div>
    <template v-if="recoveryCodes.length > 0" #bottom>
      <div class="text-body2">
        <p>
          Store these recovery codes in a secure password manager. They can be used to recover
          access to your account if your two factor authentication device is lost.
        </p>
      </div>

      <div class="q-pa-md text-sm rounded-lg bg-grey-10">
        <div v-for="code in recoveryCodes" :key="code" class="q-my-xs">
          {{ code }}
        </div>
      </div>
    </template>
  </Alert>

  <Alert v-else type="warning">
    <div class="flex items-center justify-between gap-xs-sm">
      <span v-if="confirming">Finish enabling two factor authentication.</span>
      <span v-else>You have not enabled two factor authentication.</span>
      <div v-if="!confirming">
        <ConfirmsPassword @confirmed="enableTwoFactorAuthentication">
          <q-btn
            label="Enable"
            type="button"
            color="warning"
            class="text-sm q-px-md q-py-xs"
            :loading="enabling"
            outline
          />
        </ConfirmsPassword>
      </div>
    </div>
    <template #bottom>
      <div v-if="twoFactorEnabled && qrCode" class="flex items-center no-wrap gap-xs-lg">
        <div
          v-html="qrCode"
          class="q-pa-xs bg-white bordered inline-block rounded-borders self-start"
          style="line-height: 1"
        />

        <div>
          <div class="text-body2">
            <p v-if="confirming">
              To finish enabling two factor authentication, scan the QR code using your phone's
              authenticator application or enter the setup key and provide the generated OTP code.
            </p>
            <p v-else>
              Two factor authentication is now enabled. Scan the following QR code using your
              phone's authenticator application or enter the setup key.
            </p>
          </div>

          <div v-if="setupKey" class="q-mt-sm text-sm">
            <p>Setup Key: <span v-html="setupKey"></span></p>
          </div>

          <div v-if="confirming" class="mt-4">
            <q-input
              v-model="confirmationForm.code"
              class="q-mb-sm"
              type="text"
              inputmode="numeric"
              label="Code"
              filled
              required
              hide-bottom-space
              autofocus
              autocomplete="one-time-code"
              :error="!!confirmationForm.errors.code"
              :error-message="confirmationForm.errors.code"
            />
          </div>

          <div class="flex gap-xs-sm">
            <q-space />
            <ConfirmsPassword @confirmed="disableTwoFactorAuthentication">
              <q-btn
                label="Cancel"
                type="button"
                color="grey"
                class="text-sm q-px-md q-py-xs"
                :loading="disabling"
                outline
              />
            </ConfirmsPassword>
            <ConfirmsPassword @confirmed="confirmTwoFactorAuthentication">
              <q-btn
                label="Confirm"
                type="button"
                color="warning"
                class="text-sm q-px-md q-py-xs"
                :loading="enabling"
                outline
              />
            </ConfirmsPassword>
          </div>
        </div>
      </div>
      <div v-else class="text-body2">
        <p class="q-mb-none">
          When two factor authentication is enabled, you will be prompted for a secure, random token
          during authentication. You may retrieve this token from your phone's Google Authenticator
          application.
        </p>
      </div>
    </template>
  </Alert>
</template>
