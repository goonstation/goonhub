<script setup>
defineProps({
  row: {
    type: Object,
    required: true,
  },
})
</script>

<template>
  <span>
    <template v-if="row.game_server">
      {{ row.game_server.short_name }}
    </template>
    <template v-else-if="row.game_server_group"> {{ row.game_server_group.name }} Group </template>
    <template v-else> All </template>
  </span>
</template>
