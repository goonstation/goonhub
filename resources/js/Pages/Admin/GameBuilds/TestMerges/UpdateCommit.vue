<template>
  <q-dialog v-model="opened" @hide="$inertia.visit($store.ParentPage.url, { preserveState: true })">
    <q-card style="max-width: 500px; width: 100%" flat bordered>
      <div class="gh-card__header q-pa-md bordered">
        <span>Update Test Merge Commit</span>
      </div>
      <game-build-test-merge-commit-form
        :fields="fields"
        :submit-route="$route('admin.builds.test-merges.update-commit', testMerge.id)"
        :pr-id="testMerge.pr_id"
        class="q-pa-md"
        success-message="Test merge commit updated"
        state="edit"
        submit-method="put"
      >
        <template #actions="{ state, loading }">
          <q-card-actions align="right">
            <q-btn flat label="Cancel" v-close-popup />
            <q-btn
              :label="(state === 'edit' ? 'Update' : 'Add') + ' Commit'"
              type="submit"
              color="primary"
              :loading="loading"
              flat
            />
          </q-card-actions>
        </template>
      </game-build-test-merge-commit-form>
    </q-card>
  </q-dialog>
</template>

<script>
import GameBuildTestMergeCommitForm from '@/Components/Forms/GameBuildTestMergeCommitForm.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { ionCloseCircleOutline } from '@quasar/extras/ionicons-v6'
import GameBuildsIndexLayout from '../IndexLayout.vue'
import GameBuildsLayout from '../Layout.vue'
import GameBuildsTestMergesIndex from './Index.vue'

export default {
  layout: (h, page) => {
    return h(DashboardLayout, { title: 'Update Test Merge Commit' }, () =>
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
    GameBuildTestMergeCommitForm,
  },

  props: {
    testMerge: Object,
  },

  setup() {
    return {
      ionCloseCircleOutline,
    }
  },

  data() {
    return {
      opened: true,
      fields: {
        game_admin_id: this.$page.props.auth.user.game_admin_id,
        commit: this.testMerge.commit,
      },
    }
  },
}
</script>
