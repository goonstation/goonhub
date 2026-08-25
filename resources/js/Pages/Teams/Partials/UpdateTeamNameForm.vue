<script setup>
import ActionMessage from '@/Components/ActionMessage.vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  team: Object,
  permissions: Object,
})

const form = useForm({
  name: props.team.name,
})

const updateTeamName = () => {
  form.put(route('web.teams.update', props.team), {
    errorBag: 'updateTeamName',
    preserveScroll: true,
  })
}
</script>

<template>
  <div class="row q-col-gutter-sm">
    <div class="col-12 col-md-4">
      <h3 class="q-mb-sm text-h6">Team Name</h3>
      <div class="q-mb-md">The team's name and owner information.</div>
    </div>
    <div class="col-12 col-md-8">
      <q-card flat class="q-pa-sm">
        <q-card-section>
          <q-form @submit="updateTeamName">
            <div class="q-mb-md text-weight-medium text-body1">Team Owner</div>

            <div class="flex items-center q-mt-sm q-mb-md">
              <img
                style="width: 50px; border-radius: 50%"
                :src="team.owner.profile_photo_url"
                :alt="team.owner.name"
              />

              <div class="q-ml-md">
                <div>{{ team.owner.name }}</div>
                <div class="text-grey-6 text-sm">
                  {{ team.owner.email }}
                </div>
              </div>
            </div>

            <q-input
              v-model="form.name"
              class="q-mb-sm"
              type="text"
              label="Team Name"
              filled
              required
              hide-bottom-space
              :disabled="!permissions.canUpdateTeam"
              :error="!!form.errors.name"
              :error-message="form.errors.name"
            />

            <div v-if="permissions.canUpdateTeam" class="flex items-center q-mt-md">
              <q-space />
              <ActionMessage :on="form.recentlySuccessful" class="q-mr-sm"> Saved. </ActionMessage>
              <q-btn
                label="Save"
                type="submit"
                color="primary"
                text-color="black"
                :loading="form.processing"
              />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>
