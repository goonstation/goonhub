<template>
  <q-select
    ref="select"
    v-model="visibleModel"
    v-bind="$attrs"
    :options="options"
    :clearable="!!model"
    :loading="loading"
    :option-value="optionValue"
    :option-label="optionLabel"
    :option-disable="optionDisable"
    map-options
    emit-value
    @virtual-scroll="onScroll"
    @filter="filterFn"
  >
    <template v-for="(_, name) in $slots" v-slot:[name]="slotData">
      <slot :name="name" v-bind="slotData" />
    </template>
    <template #selected-item="{ index, opt, removeAtIndex }">
      <q-chip
        v-if="Object.keys($attrs).includes('use-chips')"
        @remove="removeAtIndex(index)"
        removable
        dense
      >
        {{ getSelectedLabel(opt) }}
      </q-chip>
      <template v-else>{{ getSelectedLabel(opt) }}</template>
    </template>
  </q-select>
</template>

<script>
import axios from 'axios'

export default {
  emits: ['update:modelValue', 'update:option'],

  props: {
    modelValue: null,
    loadRoute: String,
    optionValue: [String, Function],
    optionLabel: [String, Function],
    fieldLabel: String,
    filters: Object,
    defaultItems: Array,
    searchKey: String,
    filterClientside: {
      type: Boolean,
      required: false,
      default: false,
    },
    disabledItems: {
      type: Array,
      required: false,
      default: () => [],
    },
  },

  computed: {
    model: {
      get() {
        if (!this.modelValue) return
        // if (this.$helpers.isNumeric(this.modelValue)) return parseInt(this.modelValue)
        return this.modelValue
      },
      set(val) {
        this.$emit('update:modelValue', val)
        this.$emit(
          'update:option',
          this.options.find((option) => option[this.optionValue] === val)
        )
      },
    },

    visibleModel: {
      get() {
        if (this.firstLoad) return
        return this.model
      },
      set(val) {
        this.model = val
      },
    },
  },

  data() {
    return {
      options: [],
      initialOptions: [],
      pagination: {
        currentPage: 0,
        lastPage: 1,
        perPage: 50,
      },
      ourFilters: {},
      loading: false,
      firstLoad: true,
      loadedDefaultItem: false,
      search: '',
    }
  },

  created() {
    // Handle an existing item being selected
    if (this.model) {
      if (this.searchKey) this.search = this.model
      this.ourFilters[this.optionValue] = this.model
      this.load().then(() => {
        // Reset state so future calls can correctly query the rest of the resources
        this.pagination.currentPage = 0
        this.pagination.lastPage = 1
        this.pagination.perPage = 50
        delete this.ourFilters[this.optionValue]
        this.loadedDefaultItem = true
      })
    }
  },

  methods: {
    async load(newSearch = false) {
      if (this.pagination.currentPage >= this.pagination.lastPage) return

      this.loading = true
      let newOptions = []

      if (this.filterClientside && !this.firstLoad) {
        if (this.search && this.searchKey) {
          newOptions = this.initialOptions.filter((option) =>
            option[this.searchKey].toLowerCase().includes(this.search.toLowerCase())
          )
        } else {
          newSearch = true
          newOptions = this.initialOptions
        }
      } else {
        let filters = this.ourFilters
        if (this.search && this.searchKey) {
          filters = { ...filters, [this.searchKey]: this.search }
        }

        const response = await axios.get(this.loadRoute, {
          params: {
            page: this.pagination.currentPage + 1,
            per_page: this.pagination.perPage,
            filters,
          },
        })

        this.pagination.currentPage = response.data.current_page
        this.pagination.lastPage = response.data.last_page
        this.pagination.perPage = response.data.per_page

        newOptions = response.data.data
      }

      // Ensure we don't have duplicate items if we already loaded a default item
      if (!newSearch) {
        for (const option of this.options) {
          const existingItemIdx = newOptions.findIndex(
            (newOption) => newOption[this.optionValue] === option[this.optionValue]
          )
          if (existingItemIdx >= 0) {
            newOptions.splice(existingItemIdx, 1)
          }
        }
      }

      if (newSearch && this.defaultItems?.length) {
        newOptions = [...this.defaultItems, ...newOptions]
      }

      this.options = newSearch ? newOptions : this.options.concat(newOptions)
      // this.$refs.select.refresh()

      if (this.firstLoad) {
        this.initialOptions = [...this.options]
      }

      this.loading = false
      this.firstLoad = false
      this.loadedDefaultItem = false
    },

    onScroll({ to }) {
      if (this.loading || this.firstLoad) return
      const lastIndex = this.options.length - 1
      if (to === lastIndex) {
        this.load()
      }
    },

    filterFn(val, update) {
      let newSearch = false
      if (this.searchKey && val !== this.search) {
        // new search
        newSearch = true
        this.search = val
        if (!this.filterClientside) {
          this.pagination.currentPage = 0
          this.pagination.lastPage = 1
        }
      }

      update(
        () => {
          this.load(newSearch)
        },
        (ref) => {
          if (val !== '' && ref.options.length > 0) {
            ref.setOptionIndex(-1)
            ref.moveOptionSelection(1, true)
          }
        }
      )
    },

    optionDisable(option) {
      return option.disable || this.disabledItems.includes(option[this.optionValue])
    },

    getSelectedLabel(option) {
      if (this.fieldLabel) {
        return option[this.fieldLabel]
      }

      if (typeof this.optionLabel === 'function') {
        return this.optionLabel(option)
      }

      return option[this.optionLabel]
    }
  },

  watch: {
    filters: {
      immediate: true,
      deep: true,
      handler(val) {
        this.ourFilters = { ...this.ourFilters, ...val }
      },
    },
    defaultItems: {
      immediate: true,
      deep: true,
      handler(val) {
        if (!val) return
        for (const defaultOption of val) {
          if (
            !this.options.find(
              (option) => defaultOption[this.optionValue] === option[this.optionValue]
            )
          ) {
            this.options.unshift(defaultOption)
          }
        }
      },
    },
  },
}
</script>
