<template>
  <div class="flex column flex-grow no-wrap full-width">
    <q-tabs v-model="tab" active-color="primary" align="left">
      <q-tab
        v-for="item in tabs"
        :key="item.name"
        :name="item.name"
        :label="item.name"
        @click.prevent="$inertia.visit(item.href, { preserveState: true })"
      />
    </q-tabs>

    <q-separator style="margin-top: -1px" />

    <slot />
  </div>
</template>

<script>
export default {
  data() {
    return {
      tab: null,
      tabs: [
        { name: 'Overview', href: route('admin.builds.index') },
        { name: 'Test Merges', href: route('admin.builds.test-merges.index') },
        { name: 'Settings', href: route('admin.builds.settings.index') },
      ],
    }
  },

  watch: {
    '$page.url': {
      immediate: true,
      handler() {
        const currentRoute = window.location.href
        const tab = this.tabs.findLast((item) => {
          return currentRoute.startsWith(item.href)
        })
        this.tab = tab && tab.name
      },
    },
  },
}
</script>
