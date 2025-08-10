<template>
  <div class="spinner"></div>
</template>

<style scoped>
.spinner {
  width: v-bind(size);
  height: v-bind(size);
  border: calc(v-bind(size) / 10) solid color-mix(in srgb, v-bind(color), transparent 50%);
  border-top-color: color-mix(in srgb, v-bind(color), white 50%);
  border-radius: 50%;
  animation: spin 750ms linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}
</style>

<script setup>
defineProps({
  color: {
    type: String,
    required: false,
    default: 'var(--color-primary)',
  },
  size: {
    type: String,
    required: false,
    default: '5rem',
  },
})
</script>
