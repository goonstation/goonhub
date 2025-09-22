<template>
  <q-form @submit="submit">
    <game-servers-select
      v-model:server="form.server_id"
      :error="form.errors.server_id"
      label="Server"
      class="q-mb-md"
      with-invisible
      servers-only
    />
    <base-select
      v-model="form.map"
      label="Map"
      hint="Optional"
      option-value="map_id"
      option-label="name"
      filled
      lazy-rules
      emit-value
      map-options
      :load-route="$route('maps.index')"
      :error="!!form.errors.map"
      :error-message="form.errors.map"
    />
    <div>
      <slot name="actions" :state="state" :loading="form.processing" />
    </div>
  </q-form>
</template>

<script>
import BaseSelect from '@/Components/Selects/BaseSelect.vue'
import GameServersSelect from '../Selects/GameServers.vue'
import BaseForm from './BaseForm.vue'

export default {
  extends: BaseForm,

  components: {
    BaseSelect,
    GameServersSelect,
  },
}
</script>
