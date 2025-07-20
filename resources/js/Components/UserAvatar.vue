<template>
  <q-avatar
    v-bind="$attrs"
    class="overflow-hidden text-uppercase"
    color="dark"
    font-size="1rem"
    text-color="primary"
  >
    <img v-if="user.profile_photo_url" :src="user.profile_photo_url" :alt="user.name" />
    <template v-else>{{ initials }}</template>
    <slot />
  </q-avatar>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  user: Object,
})

const initials = computed(() => {
  const names = props.user.name.split(' ')
  let initials = names[0].substring(0, 1).toUpperCase()
  if (names.length > 1) {
    initials += names[names.length - 1].substring(0, 1).toUpperCase()
  } else {
    initials += names[0].substring(1, 2).toUpperCase()
  }
  return initials
})
</script>
