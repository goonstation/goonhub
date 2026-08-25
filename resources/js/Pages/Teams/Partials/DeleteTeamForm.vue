<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  team: Object,
})

const confirmingTeamDeletion = ref(false)
const form = useForm({})

const confirmTeamDeletion = () => {
  confirmingTeamDeletion.value = true
}

const deleteTeam = () => {
  form.delete(route('web.teams.destroy', props.team), {
    errorBag: 'deleteTeam',
  })
}
</script>

<template>
  <div class="row q-col-gutter-sm">
    <div class="col-12 col-md-4">
      <h3 class="q-mb-sm text-h6">Delete Team</h3>
      <div class="q-mb-md">Permanently delete this team.</div>
    </div>
    <div class="col-12 col-md-8">
      <q-card flat class="q-pa-sm">
        <q-card-section>
          <div>
            Once a team is deleted, all of its resources and data will be permanently deleted.
            Before deleting this team, please download any data or information regarding this team
            that you wish to retain.
          </div>

          <q-btn
            label="Delete Team"
            type="button"
            color="negative"
            text-color="black"
            class="q-mt-md"
            @click="confirmTeamDeletion"
          />

          <q-dialog v-model="confirmingTeamDeletion" @hide="confirmingTeamDeletion = false">
            <q-card flat>
              <q-card-section>
                <div class="text-h6">Delete Team</div>
                <div>
                  Are you sure you want to delete this team? Once a team is deleted, all of its
                  resources and data will be permanently deleted.
                </div>
              </q-card-section>

              <q-card-actions align="right">
                <q-btn flat label="Cancel" @click="confirmingTeamDeletion = false" color="grey" />
                <q-btn
                  label="Delete Team"
                  flat
                  type="button"
                  color="negative"
                  class="q-ml-md"
                  :loading="form.processing"
                  @click="deleteTeam"
                />
              </q-card-actions>
            </q-card>
          </q-dialog>
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>
