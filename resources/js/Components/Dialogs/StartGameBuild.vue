<template>
  <q-dialog v-model="opened">
    <q-card style="max-width: 500px; width: 100%" flat bordered>
      <div class="gh-card__header q-pa-md bordered">
        <span>Start Build</span>
      </div>
      <game-build-form
        :fields="fields"
        :submit-route="$route('admin.builds.store')"
        @success="opened = false"
        class="q-pa-md"
        success-message="Build started"
      >
        <template #actions="{ loading }">
          <q-card-actions align="right">
            <q-btn flat label="Cancel" v-close-popup />
            <q-btn label="Start" type="submit" color="primary" :loading="loading" flat />
          </q-card-actions>
        </template>
      </game-build-form>
    </q-card>
  </q-dialog>
</template>

<script>
import GameBuildForm from '@/Components/Forms/GameBuildForm.vue'

export default {
  props: {
    modelValue: Boolean,
  },

  components: {
    GameBuildForm,
  },

  data() {
    return {
      fields: {
        game_admin_id: this.$page.props.auth.user.game_admin.id,
        server_id: null,
        map: null,
      },
    }
  },

  computed: {
    opened: {
      get() {
        return this.modelValue
      },
      set(val) {
        this.$emit('update:modelValue', val)
      },
    },
  },
}
</script>
