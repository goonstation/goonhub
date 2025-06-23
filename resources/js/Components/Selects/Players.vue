<template>
  <base-select
    v-bind="$attrs"
    :load-route="$route('admin.players.index')"
    option-value="id"
    option-label="key"
  />
</template>

<script>
import BaseSelect from '@/Components/Selects/BaseSelect.vue'

export default {
  components: {
    BaseSelect,
  },
}
</script>
