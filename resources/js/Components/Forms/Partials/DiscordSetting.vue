<template>
  <div class="row q-col-gutter-md">
    <div class="col-12 col-md-6">
      <div class="text-weight-bold">{{ setting.name }}</div>
      <div class="text-caption">{{ setting.description }}</div>
    </div>
    <div class="col-12 col-md-6">
      <slot :setting="setting" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  setting: Object,
})
</script>
