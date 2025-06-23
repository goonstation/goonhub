<template>
  <div class="q-px-md q-pb-md q-pt-sm">
    <div class="flex items-center q-mb-sm">
      <q-btn
        @click="$inertia.visit($route('admin.builds.test-merges.create'), { preserveState: true })"
        :icon="ionAddCircleOutline"
        label="Add Test Merge"
        color="primary"
        size="11px"
        padding="xs sm"
        flat
      />
      <q-space />
      <q-btn
        @click="toggleGroups"
        :label="allOpened ? 'Collapse All' : 'Expand All'"
        color="primary"
        size="11px"
        padding="xs sm"
        flat
      />
    </div>

    <test-merge-group
      v-for="(pullRequest, prId) in groups"
      v-model="opened[prId]"
      :key="`pr-${prId}`"
      :pr-id="parseInt(prId)"
      :group="pullRequest"
    />

    <slot />
  </div>
</template>

<script>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { ionAddCircleOutline } from '@quasar/extras/ionicons-v6'
import GameBuildsIndexLayout from '../IndexLayout.vue'
import GameBuildsLayout from '../Layout.vue'
import TestMergeGroup from './Partials/TestMergeGroup.vue'

export default {
  layout: (h, page) => {
    return h(DashboardLayout, { title: 'Build Test Merges' }, () =>
      h(
        GameBuildsLayout,
        () => page,
        () => h(GameBuildsIndexLayout, () => page)
      )
    )
  },

  components: {
    TestMergeGroup,
  },

  props: {
    pullRequests: Object,
  },

  setup() {
    return {
      ionAddCircleOutline,
    }
  },

  data() {
    return {
      opened: {},
    }
  },

  computed: {
    groups() {
      return this.pullRequests || this.$page.props.pullRequests
    },

    allOpened() {
      return Object.values(this.opened).every((val) => val)
    },
  },

  created() {
    const opened = {}
    for (const pullRequest in this.pullRequests) {
      opened[pullRequest] = true
    }
    this.opened = opened
  },

  methods: {
    toggleGroups() {
      const opened = {}
      for (const pullRequest in this.opened) {
        opened[pullRequest] = !this.allOpened
      }
      this.opened = opened
    },
  },
}
</script>
