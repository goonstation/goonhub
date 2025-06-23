<template>
  <game-build-settings-table prop-key="settings" />
</template>

<script>
import GameBuildSettingsTable from '@/Components/Tables/Admin/GameBuildSettingsTable.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import GameBuildsIndexLayout from '../IndexLayout.vue'
import GameBuildsLayout from '../Layout.vue'

export default {
  layout: (h, page) => {
    return h(DashboardLayout, { title: 'Build Settings' }, () =>
      h(
        GameBuildsLayout,
        () => page,
        () => h(GameBuildsIndexLayout, () => page)
      )
    )
  },

  components: {
    GameBuildSettingsTable,
  },
}
</script>
