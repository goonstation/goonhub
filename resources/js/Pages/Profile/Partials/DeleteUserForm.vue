<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const confirmingUserDeletion = ref(false)
const passwordInput = ref(null)

const form = useForm({
  password: '',
})

const confirmUserDeletion = () => {
  confirmingUserDeletion.value = true

  setTimeout(() => passwordInput.value.focus(), 250)
}

const deleteUser = () => {
  form.delete(route('current-user.destroy'), {
    preserveScroll: true,
    onSuccess: () => closeModal(),
    onError: () => passwordInput.value.focus(),
    onFinish: () => form.reset(),
  })
}

const closeModal = () => {
  confirmingUserDeletion.value = false

  form.reset()
}
</script>

<template>
  <div class="text-body2">
    Once your account is deleted, all of its resources and data will be permanently deleted. Before
    deleting your account, please download any data or information that you wish to retain.
  </div>

  <div class="q-mt-md text-right">
    <q-btn
      label="Delete Account"
      type="button"
      color="negative"
      text-color="black"
      class="text-weight-bold"
      @click="confirmUserDeletion"
    />
  </div>

  <!-- Delete Account Confirmation Modal -->
  <q-dialog v-model="confirmingUserDeletion" @hide="closeModal">
    <q-card flat>
      <q-card-section>
        <div class="text-h6">Delete Account</div>
        <div>
          Are you sure you want to delete your account? Once your account is deleted, all of its
          resources and data will be permanently deleted. Please enter your password to confirm you
          would like to permanently delete your account.
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
          @keyup.enter="deleteUser"
          :error="!!form.errors.password"
          :error-message="form.errors.password"
        />
      </q-card-section>

      <q-card-actions align="right">
        <q-btn flat label="Cancel" @click="closeModal" color="grey" />
        <q-btn
          label="Delete Account"
          flat
          type="button"
          color="primary"
          class="q-ml-md"
          :loading="form.processing"
          @click="deleteUser"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>
