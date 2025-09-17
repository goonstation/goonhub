<template>
  <q-item class="q-py-md">
    <q-item-section>
      <q-item-label>
        Pull Request
        <a
          :href="`https://github.com/goonstation/goonstation/pull/${testMerge.pr_id}`"
          target="_blank"
        >
          #{{ testMerge.pr_id }}
        </a>
      </q-item-label>

      <q-item-label v-if="testMerge.commit" caption>
        Merged at commit
        <a
          :href="`https://github.com/goonstation/goonstation/pull/${testMerge.pr_id}/commits/${testMerge.commit}`"
          target="_blank"
        >
          {{ testMerge.commit.substr(0, 7) }}
        </a>
      </q-item-label>
    </q-item-section>
    <q-item-section side>
      <q-item-label caption>Added {{ $formats.fromNow(testMerge.created_at) }}</q-item-label>
      <q-item-label v-if="authors.added_by" caption>By {{ authors.added_by.alias || authors.added_by.player.ckey }}</q-item-label>
    </q-item-section>
  </q-item>
  <q-item class="q-py-md">
    <q-item-section>
      <q-item-label caption>
        <template v-if="loadingPrDetails">
          <q-skeleton type="text" width="100%" height="1.5em" />
          <q-skeleton type="text" width="100%" height="1.5em" />
        </template>
        <template v-else>{{ prDetails.title }}</template>
      </q-item-label>
    </q-item-section>
    <q-item-section side>
      <q-item-label caption> Created {{ $formats.fromNow(prDetails.created_at) }} </q-item-label>
      <q-item-label caption>
        By
        <a :href="prDetails.user?.html_url" target="_blank">{{ prDetails.user?.login }}</a>
      </q-item-label>
    </q-item-section>
  </q-item>
</template>

<script>
export default {
  props: {
    testMerge: Object,
    authors: {
      type: Object,
      default: () => ({}),
    },
  },

  data() {
    return {
      prDetails: {},
      loadingPrDetails: false,
      errorLoadingPrDetails: true,
    }
  },

  mounted() {
    this.getPrDetails()
  },

  methods: {
    async getPrDetails() {
      this.errorLoadingPrDetails = false
      this.loadingPrDetails = true
      try {
        const { data } = await axios.get(
          route('admin.builds.test-merges.pr-details', this.testMerge.pr_id)
        )
        this.prDetails = data
      } catch (e) {
        this.errorLoadingPrDetails =
          e.response.data.message || 'Failed to fetch pull request details.'
      }
      this.loadingPrDetails = false
    },
  },
}
</script>
