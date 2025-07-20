<template>
  <div class="text-body2">
    If necessary, you may log out of all of your other browser sessions across all of your devices.
    Some of your recent sessions are listed below; however, this list may not be exhaustive. If you
    feel your account has been compromised, you should also update your password.
  </div>

  <!-- Other Browser Sessions -->
  <div v-if="sessions.length > 0" class="q-mt-md">
    <div v-for="(session, i) in sessions" :key="i" class="flex items-center q-my-sm">
      <div>
        <svg
          v-if="session.agent.is_desktop"
          class="text-grey-5"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke-width="1.5"
          stroke="currentColor"
          style="width: 40px"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25"
          />
        </svg>

        <svg
          v-else
          class="text-grey-5"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke-width="1.5"
          stroke="currentColor"
          style="width: 40px"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"
          />
        </svg>
      </div>

      <div class="q-ml-md">
        <div>
          {{ session.agent.platform ? session.agent.platform : 'Unknown' }} -
          {{ session.agent.browser ? session.agent.browser : 'Unknown' }}
        </div>

        <div>
          <div class="text-sm text-grey-5">
            {{ session.ip_address }},

            <span v-if="session.is_current_device" class="text-weight-medium text-positive"
              >This device</span
            >
            <span v-else>Last active {{ session.last_active }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="flex items-center q-mt-md">
    <q-btn
      label="Log Out Other Browser Sessions"
      type="button"
      color="primary"
      text-color="black"
      @click="confirmLogout"
    />

    <ActionMessage :on="form.recentlySuccessful" class="q-ml-md">Done</ActionMessage>
  </div>

  <!-- Log Out Other Devices Confirmation Modal -->
  <q-dialog v-model="confirmingLogout" @hide="closeModal">
    <q-card flat>
      <q-card-section>
        <div class="text-h6">Log Out Other Browser Sessions</div>
        <div>
          Please enter your password to confirm you would like to log out of your other browser
          sessions across all of your devices.
        </div>
      </q-card-section>

      <q-card-section>
        <q-input
          v-model="form.password"
          ref="passwordInput"
          type="password"
          label="Password"
          filled
          required
          hide-bottom-space
          @keyup.enter="logoutOtherBrowserSessions"
          :error="!!form.errors.password"
          :error-message="form.errors.password"
        />
      </q-card-section>

      <q-card-actions align="right" class="text-primary">
        <q-btn flat label="Cancel" @click="closeModal" color="grey" />
        <q-btn
          label="Log Out Other Browser Sessions"
          flat
          type="button"
          color="primary"
          class="q-ml-md"
          :loading="form.processing"
          @click="logoutOtherBrowserSessions"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import ActionMessage from '@/Components/ActionMessage.vue'
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps({
  sessions: Array,
})

const confirmingLogout = ref(false)
const passwordInput = ref(null)

const form = useForm({
  password: '',
})

const confirmLogout = () => {
  confirmingLogout.value = true

  setTimeout(() => passwordInput.value.focus(), 250)
}

const logoutOtherBrowserSessions = () => {
  form.delete(route('other-browser-sessions.destroy'), {
    preserveScroll: true,
    onSuccess: () => closeModal(),
    onError: () => passwordInput.value.focus(),
    onFinish: () => form.reset(),
  })
}

const closeModal = () => {
  confirmingLogout.value = false

  form.reset()
}
</script>
