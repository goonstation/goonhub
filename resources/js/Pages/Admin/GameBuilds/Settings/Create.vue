<template>
  <div>
    <div class="settings-header">
      <div class="q-py-xs q-pl-md q-pr-sm text-sm flex items-center">
        <span class="settings-header__title">Create Build Settings</span>
        <q-space />
        <q-btn
          @click="router.visit($store.ParentPage.url)"
          flat
          dense
          round
          :icon="ionCloseCircleOutline"
          class="q-ml-sm"
          color="grey"
          size="md"
        />
      </div>
      <q-separator />
    </div>

    <game-build-setting-form
      :fields="fields"
      :submit-route="route('admin.builds.settings.store')"
      :existing-servers="existingServers"
      success-message="Settings added"
    />
  </div>
</template>

<style lang="scss" scoped>
.settings-header {
  &__title {
    margin-top: 2px;
    font-weight: 500;
    letter-spacing: 0.02em;
  }
}
</style>

<script>
import GameBuildSettingForm from '@/Components/Forms/GameBuildSettingForm.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { router } from '@inertiajs/vue3'
import { ionCloseCircleOutline } from '@quasar/extras/ionicons-v6'
import GameBuildsLayout from '../Layout.vue'

export default {
  layout: (h, page) => {
    return h(DashboardLayout, { title: 'Create Build Settings' }, () => h(GameBuildsLayout, () => page))
  },

  components: {
    GameBuildSettingForm,
  },

  props: {
    existingServers: Array,
  },

  setup() {
    return {
      router,
      ionCloseCircleOutline,
    }
  },

  data() {
    return {
      fields: {
        server_id: null,
        branch: null,
        byond_major: null,
        byond_minor: null,
        rustg_version: null,
        rp_mode: false,
        map_id: null,
      },
    }
  },
}
</script>
