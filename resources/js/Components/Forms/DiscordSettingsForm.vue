<template>
  <div class="row">
    <div class="col-12 col-md-6">
      <q-form @submit="submit">
        <q-card class="gh-card q-mb-md" flat>
          <q-card-section>
            <discord-setting :setting="form.settings.grant_role_when_linked">
              <template #default="{ setting }">
                <discord-roles-select
                  v-model="setting.value"
                  :error="!!form.errors[`settings.${setting.key}.key`]"
                  :error-message="form.errors[`settings.${setting.key}.key`]"
                  lazy-rules
                  clearable
                  outlined
                  hide-bottom-space
                />
              </template>
            </discord-setting>
          </q-card-section>
        </q-card>

        <div class="flex">
          <q-space />
          <q-btn
            label="Save"
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
import DiscordRolesSelect from '@/Components/Selects/DiscordRoles.vue'
import { ionClose } from '@quasar/extras/ionicons-v7'
import BaseForm from './BaseForm.vue'
import DiscordSetting from './Partials/DiscordSetting.vue'

export default {
  extends: BaseForm,

  components: {
    DiscordSetting,
    DiscordRolesSelect,
  },

  setup() {
    return {
      ionClose,
    }
  },
}
</script>
