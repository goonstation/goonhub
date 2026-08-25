<template>
  <div class="row">
    <div class="col-12 col-md-6">
      <q-form @submit="submit">
        <q-card class="gh-card q-mb-md" flat>
          <q-card-section>
            <player-select
              v-model="form.ckey"
              class="q-mb-md"
              label="Player"
              search-key="name"
              option-value="ckey"
              filled
              lazy-rules
              hide-bottom-space
              use-input
              :error="!!form.errors.ckey"
              :error-message="form.errors.ckey"
              :default-items="defaultPlayers"
              @new-value="createNewPlayer"
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
            <game-servers-select
              v-model:server="form.server_id"
              :error="form.errors.server_id"
              label="Server"
              with-invisible
              servers-only
              clearable
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
import GameServersSelect from '@/Components/Selects/GameServers.vue'
import PlayerSelect from '@/Components/Selects/Players.vue'
import BaseForm from './BaseForm.vue'

export default {
  extends: BaseForm,

  components: {
    PlayerSelect,
    GameServersSelect,
  },

  data() {
    return {
      defaultPlayers: [],
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
