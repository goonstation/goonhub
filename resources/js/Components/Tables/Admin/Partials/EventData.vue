<template>
  <div style="white-space: pre-wrap">
    <q-chip
      v-for="[key, value] in Object.entries(eventSpecificData)"
      :key="key"
      style="font-size: 13px"
      square
    >
      <q-avatar color="grey" text-color="black">{{ key }}</q-avatar>
      {{ value }}
    </q-chip>
  </div>
</template>

<style lang="scss" scoped>
.q-avatar {
  width: auto;
  padding-left: 5px;
  padding-right: 5px;
}
</style>

<script>
export default {
  props: {
    event: Object,
  },

  computed: {
    eventSpecificData() {
      const clone = { ...this.event }
      delete clone.id
      delete clone.round_id
      delete clone.player_id
      delete clone.created_at
      delete clone.updated_at
      return clone
    },
  },
}
</script>
