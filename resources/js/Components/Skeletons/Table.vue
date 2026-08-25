<template>
  <q-markup-table :dense="dense" separator="none" flat>
    <thead v-if="gridFilters">
      <tr>
        <th colspan="100%">
          <q-skeleton
            animation="blink"
            type="rect"
            width="200px"
            height="30px"
            class="q-mx-xs q-mt-md q-mb-md"
          />
        </th>
      </tr>
    </thead>
    <thead v-else>
      <tr>
        <th colspan="100%" style="height: 68px"></th>
      </tr>
      <tr>
        <th
          v-for="column in columns"
          :key="`header-${column.name}`"
          :class="column.headerClasses"
          :align="column.align || 'right'"
        >
          <q-skeleton
            animation="blink"
            type="text"
            height="20px"
            class="q-mx-xs"
            style="min-width: 50px; max-width: 100px"
          />
        </th>
      </tr>
      <tr>
        <th
          v-for="column in columns"
          :key="`header-filter-${column.name}`"
          :align="column.align || 'right'"
        >
          <q-skeleton
            v-if="column.filterable !== false"
            animation="blink"
            type="rect"
            height="32px"
            class="q-mx-xs"
          />
        </th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="row of rowCount" :key="`row-${row}`">
        <td
          v-for="column in columns"
          :key="`column-${column.name}`"
          :align="column.align || 'right'"
        >
          <q-skeleton
            animation="blink"
            type="text"
            height="20px"
            class="q-mx-xs"
            style="max-width: 200px"
          />
        </td>
      </tr>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="100%" align="right" style="border-top: 1px solid rgba(255, 255, 255, 0.28)">
          <q-skeleton
            animation="blink"
            type="rect"
            width="330px"
            height="16px"
            class="q-mx-xs q-my-sm"
          />
        </td>
      </tr>
    </tfoot>
  </q-markup-table>
</template>

<script>
export default {
  props: {
    columns: {
      type: Array,
      required: true,
      default: () => [],
    },
    rows: {
      type: Number,
      required: false,
      default: 15,
    },
    dense: {
      type: Boolean,
      required: false,
      default: false,
    },
    options: {
      type: Object,
      required: false,
      default: () => ({}),
    },
    gridFilters: {
      type: Boolean,
      required: false,
      default: false,
    },
  },

  computed: {
    rowCount() {
      return this.options.rows || this.rows
    },
  },
}
</script>
