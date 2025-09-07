<template>
  <base-select
    v-bind="$attrs"
    :load-route="$route('admin.players.index')"
    :option-label="(item) => item.key || item.ckey"
    option-value="id"
  />
</template>

<script>
import BaseSelect from '@/Components/Selects/BaseSelect.vue';

export default {
  components: {
    BaseSelect,
  },
}
</script>
