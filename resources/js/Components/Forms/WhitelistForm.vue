<template>
  <div class="row">
    <div class="col-12 col-md-6">
      <q-form @submit="submit">
        <q-card class="gh-card q-mb-md" flat>
          <q-card-section>
            <player-select
              v-model="form.player_ids"
              class="q-mb-md"
              label="Players"
              search-key="ckey"
              filled
              lazy-rules
              emit-value
              map-options
              hide-bottom-space
              use-input
              multiple
              use-chips
              :disable="state === 'edit'"
              :filters="{ whitelist: state === 'edit' }"
              :error="!!form.errors.player_ids"
              :error-message="form.errors.player_ids"
            />
            <game-servers-select
              v-model="form.server_ids"
              label="Servers"
              server-key="id"
              multiple
              with-invisible
              with-inactive
              :error="form.errors.server_ids"
            />
          </q-card-section>
        </q-card>

        <div class="flex">
          <q-space />
          <q-btn
            :label="(state === 'edit' ? 'Edit' : 'Add') + ' Whitelisted Players'"
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
}
</script>
