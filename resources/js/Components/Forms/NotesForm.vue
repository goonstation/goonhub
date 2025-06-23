<template>
  <div class="row">
    <div class="col-12 col-md-6">
      <q-form @submit="submit">
        <q-card class="gh-card q-mb-md" flat>
          <q-card-section>
            <base-select
              v-model="form.ckey"
              class="q-mb-md"
              label="Player"
              load-route="/admin/players"
              option-value="ckey"
              option-label="ckey"
              search-key="ckey"
              filled
              lazy-rules
              dense
              emit-value
              map-options
              hide-bottom-space
              use-input
              :error="!!form.errors.ckey"
              :error-message="form.errors.ckey"
              :default-items="defaultPlayers"
              @new-value="createNewPlayer"
            />
            <base-select
              v-model="form.server_id"
              class="q-mb-md"
              label="Server"
              load-route="/game-servers"
              option-value="server_id"
              option-label="name"
              filled
              lazy-rules
              dense
              emit-value
              map-options
              hide-bottom-space
              :filters="{ with_invisible: true }"
              :error="!!form.errors.server_id"
              :error-message="form.errors.server_id"
              :default-items="[{ name: 'All', server_id: 'all' }]"
            />
            <q-input
              v-model="form.note"
              class="q-mb-md"
              type="textarea"
              label="Note"
              filled
              lazy-rules
              required
              hide-bottom-space
              :error="!!form.errors.note"
              :error-message="form.errors.note"
            />
          </q-card-section>
        </q-card>

        <div class="flex">
          <q-space />
          <q-btn
            :label="(state === 'edit' ? 'Edit' : 'Add') + ' Note'"
            type="submit"
            color="primary"
            text-color="black"
            :loading="form.processing"
          />
        </div>
      </q-form>
    </div>
  </div>
</template>

<script>
import BaseSelect from '@/Components/Selects/BaseSelect.vue'
import BaseForm from './BaseForm.vue'

export default {
  extends: BaseForm,

  components: {
    BaseSelect,
  },

  data() {
    return {
      defaultPlayers: [],
    }
  },

  created() {
    if (this.form.ckey) {
      this.defaultPlayers = [{ ckey: this.form.ckey }]
    }
  },

  methods: {
    createNewPlayer(val, done) {
      if (val.length > 2) {
        this.defaultPlayers = [{ ckey: val }]
        done({ ckey: val }, 'toggle')
      }
    },
  },
}
</script>
