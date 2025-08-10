<template>
  <Alert v-if="noErrors" message="An unknown error occurred." type="error" />
  <Button icon="reload" href="byond://winset?reset=true;command=.reconnect"> Reconnect </Button>
</template>

<script setup>
import Alert from '@/Components/Alert.vue'
import Button from '@/Components/Button.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { computed, inject } from 'vue'

defineOptions({
  layout: (h, page) => h(AppLayout, { title: 'Error' }, () => page),
})

const errors = inject('errors')

const noErrors = computed(() => !errors.value || errors.value.length === 0)
</script>
