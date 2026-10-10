<template>
  <pre class="q-ma-md">{{ values }}</pre>
</template>

<script>
export default {
  props: {
    metadata: Array,
  },

  computed: {
    values() {
      return JSON.stringify(
        this.metadata.map((row) => row.metadata),
        null,
        2
      )
    },
  },
}
</script>
