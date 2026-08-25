<template>
  <div
    class="row q-col-gutter-md"
    :class="{
      'log-filters-sidebar': sidebar,
    }"
  >
    <div :class="[sidebar ? 'col-12' : 'col']">
      <q-form @submit="search">
        <q-input
          :model-value="modelValue.searchInput"
          class="q-mb-xs"
          :class="{ 'text-sm': sidebar }"
          type="textarea"
          placeholder="One term per line&#10;Term: Match must match any&#10;!Term: Match must include&#10;-Term: Match must not include"
          filled
          dense
          @update:model-value="updateSearchInput"
        />
        <div class="flex gap-xs-xs">
          <q-btn
            v-if="hasSearchFilters"
            :class="{ 'full-width': sidebar }"
            color="grey"
            text-color="dark"
            size="sm"
            @click="clearSearch"
            >Clear Filters</q-btn
          >
          <q-space />
          <q-btn
            :class="{ 'full-width': sidebar }"
            type="submit"
            color="primary"
            text-color="dark"
            size="sm"
            >Apply Filters</q-btn
          >
        </div>
      </q-form>
    </div>
    <div :class="[sidebar ? 'col-12' : 'col']">
      <div class="flex flex-wrap gap-xs-xs">
        <div class="log-type-filter">
          <q-checkbox v-model="logTypesAll" val="all" label="All" dense />
        </div>
        <div
          v-for="logType in logTypes"
          :key="logType.value"
          class="log-type-filter"
          :class="`log-type-${logType.value}`"
        >
          <q-checkbox
            :model-value="modelValue.logTypesToShow"
            :val="logType.value"
            :label="logType.label"
            dense
            @update:model-value="updateLogTypesToShow"
          />
        </div>
      </div>
      <template v-if="showRelativeTimestamps">
        <hr class="q-mt-md" style="border-color: grey" />
        <q-checkbox
          :model-value="modelValue.relativeTimestamps"
          label="Relative Timestamps"
          :dense="sidebar"
          @update:model-value="updateRelativeTimestamps"
        />
      </template>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.log-filters-sidebar {
  width: 200px;
}

.log-type-filter {
  display: inline-block;
  background: grey;
  border-radius: 3px;
  padding: 3px 5px;
  line-height: 1;
}
</style>

<script>
export default {
  props: {
    modelValue: Object,
    sidebar: Boolean,
    logTypes: Array,
    hasSearchFilters: Boolean,
    showRelativeTimestamps: {
      type: Boolean,
      default: true,
    },
  },

  emits: ['update:modelValue', 'search', 'clear-search'],

  computed: {
    logTypesAll: {
      get() {
        return this.logTypes.length === this.modelValue.logTypesToShow.length
      },
      set(val) {
        const newLogTypes = []
        if (val) {
          for (const logType of this.logTypes) {
            newLogTypes.push(logType.value)
          }
        }
        this.updateLogTypesToShow(newLogTypes)
      },
    },
  },

  methods: {
    emitUpdate(updates) {
      this.$emit('update:modelValue', { ...this.modelValue, ...updates })
    },

    updateSearchInput(value) {
      this.emitUpdate({ searchInput: value })
    },

    updateLogTypesToShow(value) {
      this.emitUpdate({ logTypesToShow: value })
    },

    updateRelativeTimestamps(value) {
      this.emitUpdate({ relativeTimestamps: value })
    },

    search() {
      const terms = this.modelValue.searchInput.split('\n')
      const filters = { and: [], or: [], not: [] }
      for (let term of terms) {
        if (term.length < 3) continue

        term = term.toLowerCase()
        if (term.startsWith('-')) {
          filters.not.push(term.substring(1))
        } else if (term.startsWith('!')) {
          filters.and.push(term.substring(1))
        } else {
          filters.or.push(term)
        }
      }
      this.$emit('search', filters)
    },

    clearSearch() {
      this.emitUpdate({ searchInput: '' })
      this.$emit('clear-search')
    },
  },
}
</script>
