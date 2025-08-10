<template>
  <component v-if="icon" :is="icon" class="icon" />
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'

const props = defineProps({
  name: {
    type: String,
    required: true,
  },
})

const icon = computed(() => defineAsyncComponent(() => import(`@img/icons/${props.name}.svg`)))
</script>
