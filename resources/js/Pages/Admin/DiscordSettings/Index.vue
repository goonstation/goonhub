<template>
  <discord-settings-form
    state="edit"
    :fields="fields"
    :submit-route="$route('admin.discord-settings.update')"
    submit-method="put"
    success-message="Discord setting updated"
    class="q-mt-md"
  />
</template>

<script setup>
import DiscordSettingsForm from '@/Components/Forms/DiscordSettingsForm.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { ref } from 'vue'

defineOptions({
  layout: (h, page) => h(DashboardLayout, { title: 'Discord Settings' }, () => page),
})

const props = defineProps({
  settings: Object,
})

const fields = ref({
  settings: {},
})

for (const setting of props.settings) {
  fields.value.settings[setting.key] = setting
}
</script>
