<template>
  <q-dialog v-model="opened" @hide="router.visit($store.ParentPage.url, { preserveState: true })">
    <q-card style="max-width: 500px; width: 100%" flat bordered>
      <div class="gh-card__header q-pa-md bordered">
        <span>Add Test Merge</span>
      </div>
      <game-build-test-merge-form
        :fields="fields"
        :submit-route="route('admin.builds.test-merges.store')"
        class="q-pa-md"
        success-message="Test merge added"
      >
        <template #actions="{ state, loading }">
          <q-card-actions align="right">
            <q-btn flat label="Cancel" v-close-popup />
            <q-btn
              :label="(state === 'edit' ? 'Save' : 'Add') + ' Test Merge'"
              type="submit"
              color="primary"
              :loading="loading"
              flat
            />
          </q-card-actions>
        </template>
      </game-build-test-merge-form>
    </q-card>
  </q-dialog>
</template>

<script>
import GameBuildTestMergeForm from '@/Components/Forms/GameBuildTestMergeForm.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { router } from '@inertiajs/vue3'
import { ionCloseCircleOutline } from '@quasar/extras/ionicons-v6'
import GameBuildsIndexLayout from '../IndexLayout.vue'
import GameBuildsLayout from '../Layout.vue'
import GameBuildsTestMergesIndex from './Index.vue'

export default {
  layout: (h, page) => {
    return h(DashboardLayout, { title: 'Add Test Merge' }, () =>
      h(
        GameBuildsLayout,
        () => page,
        () =>
          h(
            GameBuildsIndexLayout,
            () => page,
            () => h(GameBuildsTestMergesIndex, () => page)
          )
      )
    )
  },

  components: {
    GameBuildTestMergeForm,
  },

  setup() {
    return {
      router,
      ionCloseCircleOutline,
    }
  },

  data() {
    return {
      opened: true,
      fields: {
        game_admin_id: this.$page.props.auth.user.game_admin.id,
        server_ids: [],
        pr_id: null,
        commit: null,
      },
    }
  },
}
</script>
