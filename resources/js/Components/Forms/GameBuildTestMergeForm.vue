<template>
  <q-form @submit="submit">
    <base-select
      v-model="form.server_ids"
      class="q-mb-md"
      label="Servers"
      option-value="server_id"
      option-label="name"
      field-label="short_name"
      filled
      lazy-rules
      required
      multiple
      use-chips
      hide-bottom-space
      :load-route="route('web.game-servers.index', { with_invisible: 1 })"
      :rules="[(val) => !!val.length || 'Field is required']"
      :error="!!form.errors.server_ids"
      :error-message="form.errors.server_ids"
    />
    <div>
      <q-select
        v-model="form.pr_id"
        :options="pullRequestOptions"
        :loading="loadingPullRequests"
        :disable="loadingPullRequests"
        :virtual-scroll-slice-size="pullRequestOptions.length"
        class="q-mb-md"
        option-value="number"
        option-label="number"
        label="Pull Request"
        input-debounce="0"
        map-options
        emit-value
        filled
        lazy-rules
        use-input
        clearable
        hide-bottom-space
        :rules="[(val) => !!val || 'Field is required']"
        :error="!!form.errors.pr_id"
        :error-message="form.errors.pr_id"
        @filter="onPullRequestFiltered"
        @update:model-value="onPullRequestSelected"
      >
        <template #selected-item="{ opt }"> {{ opt.number }} </template>
        <template #option="scope">
          <q-item v-bind="scope.itemProps" :style="{ 'max-width': `${pullRequestSelectWidth}px` }">
            <q-item-section avatar> #{{ scope.opt.number }} </q-item-section>
            <q-item-section>
              <q-item-label>{{ scope.opt.title }}</q-item-label>
              <q-item-label caption>{{ scope.opt.user.login }}</q-item-label>
            </q-item-section>
          </q-item>
        </template>
      </q-select>
      <q-resize-observer @resize="onPullRequestSelectResize" />
    </div>
    <pull-request-card v-if="selectedPullRequest" :pr="selectedPullRequest" class="q-mb-md" />
    <q-input
      v-model="form.commit"
      class="q-mb-md"
      label="Commit"
      filled
      lazy-rules
      hide-bottom-space
      :error="!!form.errors.commit"
      :error-message="form.errors.commit"
    />
    <div>
      <slot name="actions" :state="state" :loading="form.processing" />
    </div>
  </q-form>
</template>

<script>
import PullRequestCard from '@/Components/PullRequestCard.vue'
import BaseSelect from '@/Components/Selects/BaseSelect.vue'
import BaseForm from './BaseForm.vue'

export default {
  extends: BaseForm,

  components: {
    BaseSelect,
    PullRequestCard,
  },

  data() {
    return {
      pullRequests: [],
      pullRequestOptions: [],
      loadingPullRequests: true,
      selectedPullRequest: null,
      pullRequestSelectWidth: 0,
    }
  },

  created() {
    const url = new URL(window.location.href)
    const urlSearch = new URLSearchParams(url.search)
    if (urlSearch.has('pr_id')) {
      const prId = parseInt(urlSearch.get('pr_id'))
      this.form.pr_id = prId
    }
  },

  mounted() {
    this.getPullRequests()
  },

  methods: {
    async getPullRequests() {
      this.loadingPullRequests = true
      try {
        const { data } = await axios.get(route('admin.builds.test-merges.pr'))
        this.pullRequests = data
        this.pullRequestOptions = data
        if (this.form.pr_id) this.onPullRequestSelected(this.form.pr_id)
      } catch {
        // pass
      }
      this.loadingPullRequests = false
    },

    onPullRequestFiltered(val, update) {
      update(() => {
        const needle = val.toLocaleLowerCase()
        this.pullRequestOptions = this.pullRequests.filter((pr) => {
          return (
            pr.number.toString().indexOf(needle) > -1 ||
            pr.title.toLocaleLowerCase().indexOf(needle) > -1
          )
        })
      })
    },

    onPullRequestSelected(val) {
      if (!val) {
        this.form.commit = null
        this.selectedPullRequest = null
        return
      }

      const pr = this.pullRequests.find((pr) => pr.number === val)
      if (!pr) return
      this.form.commit = pr.head.sha
      this.selectedPullRequest = pr
    },

    onPullRequestSelectResize({ width }) {
      this.pullRequestSelectWidth = width
    },
  },
}
</script>
