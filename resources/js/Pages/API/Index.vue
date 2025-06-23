<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import ApiTokenManager from './Partials/ApiTokenManager.vue';

defineProps({
  tokens: Array,
  availablePermissions: Array,
  defaultPermissions: Array,
})
</script>

<template>
  <DashboardLayout title="API Tokens">
    <ApiTokenManager
      :tokens="tokens"
      :available-permissions="availablePermissions"
      :default-permissions="defaultPermissions"
    />
  </DashboardLayout>
</template>
