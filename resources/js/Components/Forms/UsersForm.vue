<template>
  <div class="row">
    <div class="col-12 col-md-6">
      <q-form @submit="submit">
        <q-card class="gh-card q-mb-md" flat>
          <q-card-section>
            <q-input
              v-model="form.name"
              class="q-mb-md"
              label="Name"
              filled
              lazy-rules
              required
              dense
              hide-bottom-space
              :error="!!form.errors.name"
              :error-message="form.errors.name"
            />
            <q-input
              v-model="form.email"
              class="q-mb-md"
              label="Email"
              type="email"
              filled
              lazy-rules
              required
              dense
              hide-bottom-space
              :error="!!form.errors.email"
              :error-message="form.errors.email"
            />
            <q-input
              v-model="form.password"
              class="q-mb-md"
              label="Password"
              type="password"
              filled
              lazy-rules
              dense
              hide-bottom-space
              :error="!!form.errors.password"
              :error-message="form.errors.password"
            />
            <q-input
              v-model="form.confirm_password"
              class="q-mb-md"
              label="Confirm Password"
              type="password"
              filled
              lazy-rules
              dense
              hide-bottom-space
              :error="!!form.errors.confirm_password"
              :error-message="form.errors.confirm_password"
            />
            <q-input
              v-model="form.discord_id"
              class="q-mb-md"
              label="Discord ID"
              filled
              lazy-rules
              dense
              hide-bottom-space
              :error="!!form.errors.discord_id"
              :error-message="form.errors.discord_id"
            />
            <select-game-admins
              v-model="form.game_admin_id"
              class="q-mb-md"
              label="Game Admin"
              filled
              lazy-rules
              dense
              hide-bottom-space
              :error="!!form.errors.game_admin_id"
              :error-message="form.errors.game_admin_id"
            />
            <q-toggle
              v-model="form.is_admin"
              :disable="!canSetIsAdmin"
              label="Super Admin"
            />
            <div class="text-caption q-px-sm">
              Super Admins can perform any action without additional permission checks.
            </div>
            <div v-if="!canSetIsAdmin" class="text-caption q-px-sm text-accent">
              Only existing Super Admins can set this field for other users.
            </div>
          </q-card-section>
        </q-card>

        <div class="flex">
          <q-space />
          <q-btn
            :label="(state === 'edit' ? 'Edit' : 'Add') + ' User'"
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
import SelectGameAdmins from '@/Components/Selects/GameAdmins.vue'
import BaseForm from './BaseForm.vue'

export default {
  extends: BaseForm,

  components: {
    SelectGameAdmins,
  },

  computed: {
    canSetIsAdmin() {
      // Can't edit own is_admin
      if (this.form.id === this.$page.props.auth.user.id) return false
      return this.$page.props.auth.user.is_admin
    },
  },
}
</script>
