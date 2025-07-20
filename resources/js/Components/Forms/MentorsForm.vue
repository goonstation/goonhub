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
              :filters="{ mentor: false }"
              :error="!!form.errors.player_ids"
              :error-message="form.errors.player_ids"
            />
          </q-card-section>
        </q-card>

        <div class="flex">
          <q-space />
          <q-btn
            :label="(state === 'edit' ? 'Edit' : 'Add') + ' Mentors'"
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
import PlayerSelect from '@/Components/Selects/Players.vue'
import BaseForm from './BaseForm.vue'

export default {
  extends: BaseForm,

  components: {
    PlayerSelect,
  },
}
</script>
