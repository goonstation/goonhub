<script setup>
import { nextTick, reactive, ref } from 'vue'

const emit = defineEmits(['confirmed'])

defineProps({
  title: {
    type: String,
    default: 'Confirm Password',
  },
  content: {
    type: String,
    default: 'For your security, please confirm your password to continue.',
  },
  button: {
    type: String,
    default: 'Confirm',
  },
})

const confirmingPassword = ref(false)

const form = reactive({
  password: '',
  error: '',
  processing: false,
})

const passwordInput = ref(null)

const startConfirmingPassword = () => {
  axios.get(route('password.confirmation')).then((response) => {
    if (response.data.confirmed) {
      emit('confirmed')
    } else {
      confirmingPassword.value = true

      setTimeout(() => passwordInput.value.focus(), 250)
    }
  })
}

const confirmPassword = () => {
  form.processing = true

  axios
    .post(route('password.confirm'), {
      password: form.password,
    })
    .then(() => {
      form.processing = false

      closeModal()
      nextTick().then(() => emit('confirmed'))
    })
    .catch((error) => {
      form.processing = false
      form.error = error.response.data.errors.password[0]
      passwordInput.value.focus()
    })
}

const closeModal = () => {
  confirmingPassword.value = false
  form.password = ''
  form.error = ''
}
</script>

<template>
  <span>
    <span @click="startConfirmingPassword">
      <slot />
    </span>

    <q-dialog
      v-model="confirmingPassword"
      @hide="closeModal"
      backdrop-filter="blur(4px) grayscale(100%)"
    >
      <q-card class="bordered border-opacity-20" flat>
        <q-card-section>
          <div class="text-h6">{{ title }}</div>
          <div>{{ content }}</div>
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
            @keyup.enter="confirmPassword"
            :error="!!form.error"
            :error-message="form.error"
          />
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancel" @click="closeModal" color="grey" />
          <q-btn
            :label="button"
            flat
            type="button"
            color="primary"
            class="q-ml-md"
            :loading="form.processing"
            @click="confirmPassword"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </span>
</template>
