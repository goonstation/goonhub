<template>
  <span class="flex items-center">
    <span class="flex-grow q-mr-xs">{{ commit ? commit.substring(0, 7) : '' }}</span>
    <q-btn
      @click="
        $inertia.visit($route('admin.builds.test-merges.edit-commit', testMergeId), {
          preserveState: true,
        })
      "
      :icon="ionCreate"
      color="primary"
      size="sm"
      flat
      dense
      round
    >
      <q-tooltip :offset="[14, 4]" :transition-duration="100">
        <span style="font-size: 11px">Edit commit</span>
      </q-tooltip>
    </q-btn>
  </span>
</template>

<script>
import { ionCreate } from '@quasar/extras/ionicons-v6'

export default {
  props: {
    modelValue: {
      type: String,
      required: false,
    },
    testMergeId: {
      type: Number,
      required: true,
    },
  },

  setup() {
    return {
      ionCreate,
    }
  },

  computed: {
    commit: {
      get() {
        return this.modelValue
      },
      set(value) {
        this.$emit('update:modelValue', value)
      },
    },
  },
}
</script>
