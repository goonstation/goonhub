<template>
  <q-btn v-bind="$attrs" color="primary" text-color="dark" @click="open = true">Add Note</q-btn>

  <q-dialog v-model="open" @hide="closeDialog">
    <q-card flat bordered>
      <q-form @submit="submit">
        <q-card-section>
          <div class="text-h6">Add Note</div>
        </q-card-section>

        <q-card-section>
          <q-input
            v-model="form.note"
            type="textarea"
            label="Note"
            filled
            lazy-rules
            required
            :error="!!form.errors.note"
            :error-message="form.errors.note"
          />
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancel" @click="closeDialog" color="grey" />
          <q-btn
            label="Add Note"
            flat
            type="submit"
            color="primary"
            class="q-ml-md"
            :loading="form.processing"
          />
        </q-card-actions>
      </q-form>
    </q-card>
  </q-dialog>
</template>

<style lang="scss" scoped>
.q-form {
  width: 500px;
  max-width: 100%;
}
</style>

<script>
import { useForm } from '@inertiajs/vue3'
import axios from 'axios'

export default {
  props: {
    playerCkey: String,
  },

  emits: ['success', 'error'],

  data() {
    return {
      open: false,
      form: useForm({
        return_note: true,
        ckey: this.playerCkey,
        note: '',
      }),
    }
  },

  methods: {
    closeDialog() {
      this.open = false
      this.form.reset()
    },

    async submit() {
      try {
        const response = await axios.post(route('admin.notes.store'), this.form.data())
        this.$emit('success', response.data.data)
        this.$q.notify({
          message: 'Added note',
          color: 'positive',
        })
        this.closeDialog()
      } catch (e) {
        this.$emit('error')
        this.$q.notify({
          message: e.response.data.message || 'An error occurred, please try again.',
          color: 'negative',
        })
      }
    },
  },
}
</script>
