<template>
  <q-dialog v-model="opened">
    <q-card style="max-width: 500px; width: 100%" flat bordered>
      <q-card-section class="q-pb-none">
        <q-list
          v-for="testMerge in testMerges"
          :key="testMerge.pr_id"
          class="q-mb-md"
          bordered
          separator
        >
          <test-merge :test-merge="testMerge" :authors="getAuthors(testMerge.pr_id)" />
        </q-list>
      </q-card-section>

      <q-card-actions class="q-pt-none" align="right">
        <q-btn flat label="Close" v-close-popup />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
import TestMerge from './Partials/TestMerge.vue'

export default {
  props: {
    modelValue: Boolean,
    testMerges: Array,
    authors: {
      type: Array,
      default: () => [],
    },
  },

  components: {
    TestMerge,
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

  methods: {
    getTestMergeAuthors() {
      if (this.authors.length) return
      this.$inertia.reload({ only: ['testMergeAuthors'] })
    },

    getAuthors(prId) {
      return this.authors.find((author) => author.pr_id === prId) || {}
    },
  },

  watch: {
    opened: {
      handler(val) {
        if (val) this.getTestMergeAuthors()
      },
    },
  },
}
</script>
