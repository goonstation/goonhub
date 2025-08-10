<template>
  <Alert type="info" message="You have been logged out!" style="margin-bottom: 0" />
</template>

<script setup>
import Alert from '@/Components/Alert.vue'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({
  layout: (h, page) => h(AppLayout, { title: 'Logging out' }, () => page),
})

const props = defineProps({
  byondRef: {
    type: String,
    required: false,
  },
})

if (props.byondRef) {
  window.location = `byond://?src=${props.byondRef};logout=1`
}
</script>
