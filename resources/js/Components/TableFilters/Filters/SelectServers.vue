<template>
  <base-select
    v-bind="$attrs"
    load-route="/game-servers"
    :filters="filters"
    :option-value="optionValue"
    option-label="name"
    field-label="short_name"
  />
</template>

<script>
import BaseSelect from '@/Components/Selects/BaseSelect.vue'

export default {
  components: {
    BaseSelect,
  },

  props: {
    optionValue: {
      type: String,
      default: 'server_id',
    },
    filters: {
      type: Object,
      default: () => ({}),
    },
  },
}
</script>
