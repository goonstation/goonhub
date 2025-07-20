<template>
  <q-expansion-item
    v-model="opened"
    :expand-icon="ionChevronDown"
    :duration="100"
    :header-class="'pr-group gap-xs-sm bg-grey-10 rounded-borders' + (!opened ? ' q-mb-md' : '')"
    header-style="transition: margin 100ms;"
    default-opened
    expand-icon-toggle
  >
    <template #header>
      <div
        class="flex items-center justify-between no-wrap gap-xs-sm gap-md-md q-py-xs pr-group__header"
      >
        <div class="flex flex-grow items-center justify-between gap-xs-sm gap-md-md q-py-xs">
          <div>
            <div class="q-mb-xs text-weight-medium">
              Pull Request
              <a :href="`https://github.com/goonstation/goonstation/pull/${prId}`" target="_blank"
                >#{{ prId }}</a
              >
            </div>
            <div class="text-sm text-opacity-80">
              <template v-if="loadingPrDetails">
                <div><q-skeleton type="text" width="200px" /></div>
                <div><q-skeleton type="text" width="200px" /></div>
              </template>
              <q-banner
                v-else-if="errorLoadingPrDetails"
                class="pr-details-error q-py-xs q-px-sm q-mt-sm bordered"
                dense
                rounded
              >
                <span class="text-sm">{{ errorLoadingPrDetails }}</span>
              </q-banner>
              <template v-else-if="Object.keys(prDetails).length">
                <div><q-icon :name="ionDocumentTextOutline" /> {{ prDetails.title }}</div>
                <div>
                  <q-icon :name="ionPersonOutline" size="1em" /> Created by
                  <a :href="prDetails.user?.html_url" target="_blank">{{
                    prDetails.user?.login
                  }}</a>
                  {{ $formats.fromNow(prDetails.created_at) }}
                </div>
              </template>
            </div>
          </div>
        </div>
        <div>
          <q-btn
            :icon="ionTrash"
            @click="deleteMultipleOpened = true"
            color="negative"
            size="sm"
            flat
            round
          />
        </div>
        <q-badge v-if="!opened" color="primary" class="q-px-sm q-py-xs" floating outline>
          Test merged to {{ group.length }} server<template v-if="group.length !== 1">s</template>
        </q-badge>
      </div>
    </template>
    <div class="flex q-px-md">
      <q-separator size="3px" vertical />
      <q-markup-table class="text-right flex-grow q-mt-sm" flat dense>
        <thead>
          <tr>
            <th>Server</th>
            <th>Commit</th>
            <th>Added By</th>
            <th>Updated By</th>
            <th>Added At</th>
            <th>Updated At</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="testMerge in group" :key="`merge-${testMerge.id}`">
            <td>{{ testMerge.build_settings.game_server.short_name }}</td>
            <td><commit v-model="testMerge.commit" :test-merge-id="testMerge.id" /></td>
            <td>{{ testMerge.added_by.name }}</td>
            <td>{{ testMerge.updated_by?.name }}</td>
            <td>{{ $formats.date(testMerge.created_at) }}</td>
            <td>{{ $formats.date(testMerge.updated_at) }}</td>
            <td>
              <q-btn
                :icon="ionTrash"
                @click="openDelete(testMerge.id)"
                color="negative"
                size="sm"
                flat
                dense
                round
              />
            </td>
          </tr>
          <tr>
            <td colspan="100%" align="left">
              <div class="flex gap-xs-md">
                <q-btn
                  @click="router.visit(route('admin.builds.test-merges.create', { pr_id: prId }))"
                  :icon="ionAddCircleOutline"
                  label="Add new server"
                  color="primary"
                  size="11px"
                  padding="xs sm"
                  flat
                />
                <q-btn
                  @click="updateAllCommitsOpened = true"
                  :icon="ionRefreshCircleOutline"
                  :loading="loadingUpdateCommits"
                  label="Update all commits"
                  color="primary"
                  size="11px"
                  padding="xs sm"
                  flat
                />
              </div>
            </td>
          </tr>
        </tbody>
      </q-markup-table>
    </div>

    <q-dialog v-model="updateAllCommitsOpened">
      <q-card flat bordered>
        <q-card-section class="row items-center no-wrap">
          <q-avatar :icon="ionInformationCircleOutline" color="primary" text-color="dark" />
          <span class="q-ml-sm"
            >Are you sure you want to update the commit to latest for all test merges on pull
            request #{{ prId }}?</span
          >
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn
            flat
            label="Confirm"
            color="primary"
            @click="updateAllCommits"
            :loading="loadingUpdateCommits"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
    <delete-dialog
      v-model="deleteOpened"
      :item="deletingItem"
      route="admin.builds.test-merges.delete"
      @deleted="onItemDeleted"
    />
    <delete-multiple-dialog
      v-model="deleteMultipleOpened"
      :items="group.map((item) => item.id)"
      route="admin.builds.test-merges.delete-multi"
      @deleted="onGroupDeleted"
    />
  </q-expansion-item>
</template>

<style lang="scss" scoped>
.pr-group {
  &__header {
    width: 100%;
  }
}

.pr-details-error {
  background-color: rgba($negative, 0.1);
  border-color: rgba($negative, 0.6);
}
</style>

<script>
import DeleteDialog from '@/Components/Dialogs/Delete.vue'
import DeleteMultipleDialog from '@/Components/Dialogs/DeleteMultiple.vue'
import { router } from '@inertiajs/vue3'
import {
  ionAddCircleOutline,
  ionChevronDown,
  ionDocumentTextOutline,
  ionInformationCircleOutline,
  ionPersonOutline,
  ionRefreshCircleOutline,
  ionTrash,
} from '@quasar/extras/ionicons-v6'
import axios from 'axios'
import Commit from './Commit.vue'

export default {
  props: {
    modelValue: {
      type: Boolean,
      default: true,
    },
    prId: Number,
    group: Object,
  },

  components: {
    DeleteDialog,
    DeleteMultipleDialog,
    Commit,
  },

  setup() {
    return {
      router,
      ionChevronDown,
      ionDocumentTextOutline,
      ionPersonOutline,
      ionTrash,
      ionAddCircleOutline,
      ionRefreshCircleOutline,
      ionInformationCircleOutline,
    }
  },

  data() {
    return {
      prDetails: {},
      loadingPrDetails: true,
      errorLoadingPrDetails: false,
      loadingUpdateCommits: false,
      deleteOpened: false,
      deletingItem: null,
      deleteMultipleOpened: false,
      updateAllCommitsOpened: false,
    }
  },

  computed: {
    opened: {
      get() {
        return this.modelValue
      },
      set(val) {
        this.$emit('update:modelValue', val)
      },
    },
  },

  mounted() {
    this.getPrDetails()
  },

  methods: {
    async getPrDetails() {
      this.errorLoadingPrDetails = false
      this.loadingPrDetails = true
      try {
        const { data } = await axios.get(route('admin.builds.test-merges.pr-details', this.prId))
        this.prDetails = data
      } catch (e) {
        this.errorLoadingPrDetails =
          e.response.data.message || 'Failed to fetch pull request details.'
      }
      this.loadingPrDetails = false
    },

    openDelete(id) {
      this.deletingItem = id
      this.deleteOpened = true
    },

    onItemDeleted() {
      this.deletingItem = null
      router.reload()
    },

    onGroupDeleted() {
      router.reload()
    },

    async updateAllCommits() {
      if (this.loadingUpdateCommits) return
      this.loadingUpdateCommits = true
      try {
        await axios.put(route('admin.builds.test-merges.update-commits', this.prId))
        router.reload()
        this.$q.notify({
          message: `Updated all commits on PR #${this.prId} to latest.`,
          color: 'positive',
        })
      } catch (e) {
        this.$q.notify({
          message: e.response.data.message || 'An error occurred, please try again.',
          color: 'negative',
        })
      }
      this.loadingUpdateCommits = false
      this.updateAllCommitsOpened = false
    },
  },
}
</script>
