<template>
  <base-select
    v-bind="$attrs"
    :load-route="$route('admin.discord-settings.roles')"
    option-value="id"
    option-label="name"
    search-key="name"
    use-input
    filter-clientside
  >
    <template #selected-item="{ opt }">
      <span :style="`color: ${roleColor(opt.colors?.primary_color)}`">
        {{ opt.name }}
      </span>
    </template>

    <template #option="scope">
      <q-item v-bind="scope.itemProps">
        <q-item-section>
          <q-item-label :style="`color: ${roleColor(scope.opt.colors?.primary_color)}`">{{
            scope.opt.name
          }}</q-item-label>
        </q-item-section>
      </q-item>
    </template>
  </base-select>
</template>

<script setup>
import BaseSelect from '@/Components/Selects/BaseSelect.vue'

const roleColor = (color) => {
  if (!color) return 'currentColor'
  return `#${color.toString(16)}`
}
</script>
