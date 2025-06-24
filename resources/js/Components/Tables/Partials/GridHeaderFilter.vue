<template>
  <q-btn-group v-if="column">
    <q-btn :color="color" class="text-sm" padding="xs sm" dense no-caps unelevated>
      <component
        v-if="filterContentComponent"
        :is="filterContentComponent"
        :filter="filter"
        :filter-option="filterOption"
      />
      <template v-else>
        {{ fullLabel }}
      </template>
      <q-menu :offset="[0, 10]">
        <div class="q-pa-sm flex items-center">
          <span class="q-mr-sm text-caption">{{ column.label }}</span>
          <table-filter
            :model-value="filter"
            @update:modelValue="$emit('update', $event)"
            @update:option="$emit('update:option', $event)"
            @clear="$emit('clear')"
            :filter-type="column.filter?.type || 'text'"
          />
        </div>
      </q-menu>
    </q-btn>
    <q-btn :color="color" class="text-sm" padding="xs xs" @click="$emit('clear')" dense unelevated>
      <q-icon :name="ionClose" size="xs" />
    </q-btn>
  </q-btn-group>
</template>

<script>
import TableFilter from '@/Components/TableFilters/BaseFilter.vue'
import { ionClose } from '@quasar/extras/ionicons-v6'
import { defineAsyncComponent } from 'vue'

const componentNames = import.meta.glob('./GridFilterContent/*.vue')
const components = {}
for (const name in componentNames) {
  const cleanName = name.replace(/(^.\/)|(\.vue$)|(GridFilterContent\/)/g, '')
  components[cleanName] = defineAsyncComponent(() => import(`./GridFilterContent/${cleanName}.vue`))
}

export default {
  emits: ['update', 'update:option', 'clear'],

  setup() {
    return {
      ionClose,
    }
  },

  components: {
    TableFilter,
    ...components,
  },

  props: {
    column: Object,
    filter: null,
    filterOption: null,
    color: String,
  },

  computed: {
    filterType() {
      return this.column.filter?.type.toLowerCase() ?? ''
    },

    prefix() {
      if (this.filterType === 'boolean' && this.filter === 0) {
        return 'Not'
      }
      return ''
    },

    comparator() {
      if (this.filterType === 'daterange' || this.filterType === 'griddaterange') {
        if (this.filter.includes('-')) return 'is between'
        else return 'is on'
      } else if (this.filterType === 'boolean') {
        return ''
      } else if (this.filterType === 'range') {
        return ''
      } else {
        return 'contains'
      }
    },

    prettyFilter() {
      if (this.filterType === 'daterange' || this.filterType === 'griddaterange') {
        return this.filter.replace('-', ' and ')
      }
      if (this.filterType === 'boolean') {
        return ''
      }
      return this.filter
    },

    fullLabel() {
      let label = ''
      if (this.prefix) label += `${this.prefix} `
      label += this.column.label
      if (this.comparator) label += ` ${this.comparator}`
      if (this.prettyFilter) label += ` ${this.prettyFilter}`
      return label
    },

    filterContentComponent() {
      if (!this.filterType) return null
      const name = Object.keys(components).find(
        (name) => name.toLowerCase() === this.filterType.toLowerCase()
      )
      if (name) {
        return name
      }

      return null
    },
  },
}
</script>
