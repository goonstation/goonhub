<template>
  <div class="alert" :class="`alert--${type}`">
    <slot>
      <p v-if="typeof message === 'string'">{{ message }}</p>
      <p v-else v-for="item in message" :key="item">{{ item }}</p>
    </slot>
    <Icon v-if="icon" :name="icon" />
  </div>
</template>

<script setup>
import Icon from '@/Components/Icon.vue'
import { computed } from 'vue'

const props = defineProps({
  message: {
    type: [String, Array],
    required: false,
  },
  type: {
    type: String,
    default: 'error',
    validator: (value) => ['error', 'success', 'info'].includes(value),
  },
  hideIcon: {
    type: Boolean,
    default: false,
  },
})

const icon = computed(() => {
  if (props.hideIcon) return null

  switch (props.type) {
    case 'error':
      return 'alert-circle'
    case 'success':
      return 'checkmark-circle'
    case 'info':
      return 'information-circle'
    default:
      return null
  }
})
</script>
