<script setup>
import Alert from '@/Components/Alert.vue'
import ApiTokenForm from '@/Components/Forms/ApiTokenForm.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { usePage } from '@inertiajs/vue3'

defineOptions({
  layout: (h, page) => h(DashboardLayout, { title: 'Edit API Token' }, () => page),
})

defineProps({
  token: Object,
  availablePermissions: Array,
})

const page = usePage()
</script>

<template>
  <div class="q-mx-auto q-mt-md" style="width: 100%; max-width: 1200px">
    <Alert v-if="page.props.flash.error" type="negative" class="q-mb-md">
      <span class="text-weight-medium">{{ page.props.flash.error }}</span>
    </Alert>

    <Alert v-if="page.props.flash.success" :opacity="20" type="positive" class="q-mb-md">
      <span class="text-weight-medium">{{ page.props.flash.success }}</span>
    </Alert>

    <q-card class="gh-card api-tokens-card q-mb-md" flat>
      <div class="gh-card__header q-pa-md bordered">
        <span>Edit API Token</span>
      </div>
      <q-card-section class="api-tokens-section">
        <ApiTokenForm :token="token" :available-permissions="availablePermissions" mode="edit" />
      </q-card-section>
    </q-card>
  </div>
</template>

<style lang="scss" scoped>
// Allow sticky positioning to work inside the card
.api-tokens-card {
  overflow: visible !important;
}

.api-tokens-section {
  overflow: visible !important;
}
</style>
