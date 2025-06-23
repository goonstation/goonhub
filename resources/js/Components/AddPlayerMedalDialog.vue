<template>
  <q-btn v-bind="$attrs" color="primary" text-color="dark" @click="open = true">Grant Medal</q-btn>

  <q-dialog v-model="open" @hide="closeDialog">
    <q-card flat bordered>
      <q-form @submit="submit">
        <q-card-section>
          <div class="text-h6">Grant Medal</div>
        </q-card-section>

        <q-card-section>
          <base-select
            v-model="form.medal_uuid"
            label="Medal"
            :load-route="route('admin.medals.unawarded-to-player', playerId)"
            option-value="uuid"
            option-label="title"
            search-key="title"
            filled
            lazy-rules
            dense
            emit-value
            map-options
            use-input
            :error="!!form.errors.medal_uuid"
            :error-message="form.errors.medal_uuid"
          >
            <template #option="scope">
              <q-item v-bind="scope.itemProps">
                <q-item-section avatar>
                  <medal-thumbnail :medal="scope.opt" size="32" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ scope.opt.title }}</q-item-label>
                  <q-item-label caption>{{ scope.opt.description }}</q-item-label>
                </q-item-section>
              </q-item>
            </template>
          </base-select>
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancel" @click="closeDialog" color="grey" />
          <q-btn
            label="Grant Medal"
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
import MedalThumbnail from '@/Components/MedalThumbnail.vue'
import BaseSelect from '@/Components/Selects/BaseSelect.vue'
import { useForm } from '@inertiajs/vue3'
import axios from 'axios'

export default {
  components: {
    BaseSelect,
    MedalThumbnail,
  },

  props: {
    playerId: Number,
  },

  emits: ['success', 'error'],

  data() {
    return {
      open: false,
      form: useForm({
        player_id: this.playerId,
        medal_uuid: null,
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
        const response = await axios.post(route('admin.medals.add-to-player'), this.form.data())
        this.$emit('success', response.data)
        this.$q.notify({
          message: 'Granted medal',
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
