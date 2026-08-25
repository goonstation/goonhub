<template>
  <component :is="formatType" v-bind="$attrs" :row="row" />
</template>

<script>
import { defineAsyncComponent } from 'vue'

const componentNames = import.meta.glob('./Formats/*.vue')
const components = []
for (const name in componentNames) {
  const cleanName = name.replace(/(^.\/)|(\.vue$)|(Formats\/)/g, '')
  components[cleanName] = defineAsyncComponent(() => import(`./Formats/${cleanName}.vue`))
}

export default {
  components,

  props: {
    formatType: {
      type: String,
      required: true,
    },
    row: {
      type: Object,
      required: false,
    },
  },
}
</script>
