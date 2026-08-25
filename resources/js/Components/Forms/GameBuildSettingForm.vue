<template>
  <div class="row">
    <div class="col-12 col-md-6">
      <q-form @submit="submit">
        <q-card class="gh-card q-mb-md" flat>
          <q-card-section>
            <base-select
              v-if="state === 'create'"
              v-model="form.server_id"
              label="Server"
              option-value="server_id"
              option-label="name"
              filled
              lazy-rules
              dense
              emit-value
              map-options
              required
              :load-route="route('web.game-servers.index', { with_invisible: 1 })"
              :disabled-items="existingServers"
              :error="!!form.errors.server_id"
              :error-message="form.errors.server_id"
            />
            <base-select
              v-model="form.map_id"
              label="Map"
              option-value="map_id"
              option-label="name"
              filled
              lazy-rules
              dense
              emit-value
              map-options
              :load-route="route('web.maps.index')"
              :error="!!form.errors.map_id"
              :error-message="form.errors.map_id"
            />
            <q-input
              v-model="form.branch"
              class="q-mb-sm"
              label="Branch"
              hint="Optional. Default is master"
              placeholder="master"
              filled
              lazy-rules
              dense
              :error="!!form.errors.branch"
              :error-message="form.errors.branch"
            />
            <div class="q-ml-sm q-mb-xs text-caption">Byond Version</div>
            <div class="flex no-wrap items-baseline">
              <q-input
                v-model="form.byond_major"
                type="number"
                label="Major"
                filled
                lazy-rules
                required
                dense
                :error="!!form.errors.byond_major"
                :error-message="form.errors.byond_major"
              />
              <span class="q-mx-sm">.</span>
              <q-input
                v-model="form.byond_minor"
                type="number"
                label="Minor"
                filled
                lazy-rules
                required
                dense
                :error="!!form.errors.byond_minor"
                :error-message="form.errors.byond_minor"
              />
            </div>
            <q-input
              v-model="form.rustg_version"
              label="Rust-G Version"
              filled
              lazy-rules
              required
              dense
              :error="!!form.errors.rustg_version"
              :error-message="form.errors.rustg_version"
            />
            <q-toggle v-model="form.rp_mode" label="Roleplay Mode" />

            <div class="flex">
              <q-space />
              <q-btn
                :label="(state === 'edit' ? 'Save' : 'Add') + ' Settings'"
                type="submit"
                color="primary"
                text-color="black"
                :loading="form.processing"
              />
            </div>
          </q-card-section>
        </q-card>
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

  props: {
    existingServers: Array,
  },
}
</script>
