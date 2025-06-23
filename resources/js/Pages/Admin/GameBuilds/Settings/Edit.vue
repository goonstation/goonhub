<template>
  <div>
    <div class="settings-header">
      <div class="q-py-xs q-pl-md q-pr-sm text-sm flex items-center">
        <span class="settings-header__title">Settings for {{ setting.game_server.name }}</span>
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
      state="edit"
      :fields="fields"
      :submit-route="route('admin.builds.settings.update', { setting: setting.id })"
      submit-method="put"
      success-message="Settings updated"
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
    return h(DashboardLayout, { title: 'Edit Build Settings' }, () => h(GameBuildsLayout, () => page))
  },

  components: {
    GameBuildSettingForm,
  },

  props: {
    setting: Object,
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
        branch: this.setting.branch,
        byond_major: this.setting.byond_major,
        byond_minor: this.setting.byond_minor,
        rustg_version: this.setting.rustg_version,
        rp_mode: this.setting.rp_mode,
        map_id: this.setting.map_id,
      },
    }
  },
}
</script>
