<template>
  <Spinner style="margin: 1rem auto 3rem auto" />
  <Alert type="info" style="margin-bottom: 0" hide-icon>
    <p><strong>Validating your credentials...</strong></p>
    <p>This window will close automatically when the validation is complete.</p>
  </Alert>
</template>

<script setup>
import Alert from '@/Components/Alert.vue'
import Spinner from '@/Components/Spinner.vue'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({
  layout: (h, page) => h(AppLayout, { title: 'Validating' }, () => page),
})
</script>
