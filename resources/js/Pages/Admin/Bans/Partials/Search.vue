<template>
  <q-card class="gh-card gh-card--small q-mb-md" flat>
    <q-card-section>
      <form @submit.prevent="search" class="row q-col-gutter-md">
        <div class="col-xs-12 col-md-auto">
          <q-input v-model="fields.ckey" label="Ckey" dense filled clearable />
        </div>
        <div class="col-xs-12 col-md-auto">
          <q-input v-model="fields.compId" label="Computer ID" dense filled clearable />
        </div>
        <div class="col-xs-12 col-md-auto">
          <q-input v-model="fields.ip" label="IP" dense filled clearable />
        </div>
        <div class="self-center q-ml-auto q-ml-md-none">
          <q-btn type="submit" color="primary" text-color="dark">Search</q-btn>
          <q-btn @click="reset" class="q-ml-sm">Clear</q-btn>
        </div>
      </form>
    </q-card-section>
  </q-card>
</template>

<script>
export default {
  props: {
    modelValue: Object,
  },

  data() {
    return {
      fields: {
        ckey: null,
        compId: null,
        ip: null,
      },
    }
  },

  methods: {
    search() {
      this.$emit('update:model-value', Object.assign({}, this.fields))
    },

    reset() {
      Object.keys(this.fields).forEach((v) => (this.fields[v] = null))
      this.search()
    },
  },

  watch: {
    modelValue: {
      immediate: true,
      handler(newFilters) {
        if (!newFilters) return
        Object.keys(this.fields).forEach((field) => {
          if (field in newFilters) {
            this.fields[field] = newFilters[field]
          }
        })
      },
    },
  },
}
</script>
