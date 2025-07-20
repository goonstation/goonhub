<template>
  <div :class="`alert alert--${type} bordered rounded-borders`">
    <div
      :class="`text-${type}`"
      class="alert__top q-px-md q-py-sm flex items-center no-wrap gap-xs-md"
    >
      <div v-if="icon" class="alert__left flex items-center">
        <q-icon :name="icon" size="sm" />
      </div>
      <div class="alert__content flex-grow">
        <slot />
      </div>
      <div v-if="$slots.right" class="alert__right self-start flex items-center q-ml-auto">
        <slot name="right" />
      </div>
    </div>
    <div v-if="$slots.bottom" class="alert__bottom q-pa-md">
      <slot name="bottom" />
    </div>
  </div>
</template>

<style lang="scss" scoped>
.alert {
  &--positive {
    border-color: color-mix(in srgb, var(--q-positive) 40%, transparent);
  }
  &--negative {
    border-color: color-mix(in srgb, var(--q-negative) 40%, transparent);
  }
  &--warning {
    border-color: color-mix(in srgb, var(--q-warning) 40%, transparent);
  }
  &--info {
    border-color: color-mix(in srgb, var(--q-info) 40%, transparent);
  }

  &__top {
    background-color: color-mix(in srgb, currentColor v-bind(opacityPercentage), transparent);
  }
}
</style>

<script setup>
import {
  ionCheckmarkCircleOutline,
  ionInformationCircleOutline,
  ionWarningOutline,
} from '@quasar/extras/ionicons-v7'
import { computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'info',
    validator(value) {
      return ['positive', 'negative', 'warning', 'info'].includes(value)
    },
  },
  icon: {
    default: true,
  },
  opacity: {
    type: Number,
    default: 10,
  },
})

const icon = computed(() => {
  if (!props.icon) return null
  if (props.icon !== true) return props.icon
  if (props.type === 'positive') return ionCheckmarkCircleOutline
  if (props.type === 'negative') return ionWarningOutline
  if (props.type === 'warning') return ionWarningOutline
  if (props.type === 'info') return ionInformationCircleOutline
  return null
})

const opacityPercentage = computed(() => `${props.opacity}%`)
</script>
