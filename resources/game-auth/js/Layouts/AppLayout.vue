<template>
  <Head :title="title" />

  <Background />

  <div class="page">
    <div class="content-wrapper">
      <div class="content">
        <div class="content-header">
          <h1>{{ title ?? 'Auth' }}</h1>
        </div>

        <div class="content-body">
          <Alert v-if="errors.length" :message="errors" type="error" />

          <slot />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Alert from '@/Components/Alert.vue'
import Background from '@/Components/Background.vue'
import { Head, usePage } from '@inertiajs/vue3'
import { provide, ref } from 'vue'

const page = usePage()

defineProps({
  title: String,
})

const errors = ref(Object.values(page.props.errors))

const sessionError = sessionStorage.getItem('game-auth-error')
if (sessionError) {
  errors.value.push(sessionError)
  sessionStorage.removeItem('game-auth-error')
}

provide('errors', errors)

window.GoonhubAuth = {
  onError: (error) => {
    sessionStorage.setItem('game-auth-error', error)
    window.location.href = route('game-auth.error')
  },
}
</script>
