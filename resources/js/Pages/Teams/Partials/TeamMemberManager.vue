<script setup>
import ActionMessage from '@/Components/ActionMessage.vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  team: Object,
  availableRoles: Array,
  userPermissions: Object,
})

const addTeamMemberForm = useForm({
  email: '',
  role: null,
})

const updateRoleForm = useForm({
  role: null,
})

const leaveTeamForm = useForm({})
const removeTeamMemberForm = useForm({})

const currentlyManagingRole = ref(false)
const managingRoleFor = ref(null)
const confirmingLeavingTeam = ref(false)
const teamMemberBeingRemoved = ref(null)

const addTeamMember = () => {
  addTeamMemberForm.post(route('web.team-members.store', props.team), {
    errorBag: 'addTeamMember',
    preserveScroll: true,
    onSuccess: () => addTeamMemberForm.reset(),
  })
}

const cancelTeamInvitation = (invitation) => {
  router.delete(route('web.team-invitations.destroy', invitation), {
    preserveScroll: true,
  })
}

const manageRole = (teamMember) => {
  managingRoleFor.value = teamMember
  updateRoleForm.role = teamMember.membership.role
  currentlyManagingRole.value = true
}

const updateRole = () => {
  updateRoleForm.put(route('web.team-members.update', [props.team, managingRoleFor.value]), {
    preserveScroll: true,
    onSuccess: () => (currentlyManagingRole.value = false),
  })
}

const confirmLeavingTeam = () => {
  confirmingLeavingTeam.value = true
}

const leaveTeam = () => {
  leaveTeamForm.delete(route('web.team-members.destroy', [props.team, usePage().props.auth.user]))
}

const confirmTeamMemberRemoval = (teamMember) => {
  teamMemberBeingRemoved.value = teamMember
}

const removeTeamMember = () => {
  removeTeamMemberForm.delete(
    route('web.team-members.destroy', [props.team, teamMemberBeingRemoved.value]),
    {
      errorBag: 'removeTeamMember',
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => (teamMemberBeingRemoved.value = null),
    }
  )
}

const displayableRole = (role) => {
  return props.availableRoles.find((r) => r.key === role).name
}
</script>

<template>
  <div>
    <div v-if="userPermissions.canAddTeamMembers" class="q-mt-lg">
      <!-- Add Team Member -->
      <div class="row q-col-gutter-sm">
        <div class="col-12 col-md-4">
          <h3 class="q-mb-sm text-h6">Add Team Member</h3>
          <div class="q-mb-md">
            Add a new team member to your team, allowing them to collaborate with you.
          </div>
        </div>
        <div class="col-12 col-md-8">
          <q-card flat class="q-pa-sm">
            <q-card-section>
              <q-form @submit="addTeamMember">
                <div class="q-mb-md text-body2">
                  Please provide the email address of the person you would like to add to this team.
                </div>

                <q-input
                  v-model="addTeamMemberForm.email"
                  class="q-mb-md"
                  type="text"
                  label="Email"
                  filled
                  required
                  hide-bottom-space
                  :error="!!addTeamMemberForm.errors.email"
                  :error-message="addTeamMemberForm.errors.email"
                />

                <div v-if="availableRoles.length > 0">
                  <div class="text-body1 q-mb-sm">Role</div>
                  <div v-if="addTeamMemberForm.errors.role" class="q-mt-md text-negative">
                    {{ addTeamMemberForm.errors.role }}
                  </div>

                  <q-btn
                    v-for="role in availableRoles"
                    :key="role.key"
                    type="button"
                    class="q-my-xs full-width"
                    no-caps
                    outline
                    stack
                    :color="addTeamMemberForm.role == role.key ? 'primary' : 'grey'"
                    @click="addTeamMemberForm.role = role.key"
                  >
                    <div class="self-start text-left q-py-sm">
                      <div class="text-body1 text-weight-bold">
                        {{ role.name }}
                      </div>
                      <div class="q-mt-xs">
                        {{ role.description }}
                      </div>
                    </div>
                  </q-btn>
                </div>

                <div class="flex items-center q-mt-sm">
                  <q-space />
                  <ActionMessage :on="addTeamMemberForm.recentlySuccessful" class="q-mr-md">
                    Added.
                  </ActionMessage>
                  <q-btn
                    label="Add"
                    type="submit"
                    color="primary"
                    text-color="black"
                    :loading="addTeamMemberForm.processing"
                  />
                </div>
              </q-form>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <div v-if="team.team_invitations.length > 0 && userPermissions.canAddTeamMembers">
      <div class="row q-col-gutter-sm q-mt-lg">
        <div class="col-12 col-md-4">
          <h3 class="q-mb-sm text-h6">Pending Team Invitations</h3>
          <div class="q-mb-md">
            These people have been invited to your team and have been sent an invitation email. They
            may join the team by accepting the email invitation.
          </div>
        </div>
        <div class="col-12 col-md-8">
          <q-card flat class="q-pa-sm">
            <q-card-section>
              <div
                v-for="invitation in team.team_invitations"
                :key="invitation.id"
                class="flex items-center justify-between"
              >
                <div>{{ invitation.email }}</div>

                <q-btn
                  v-if="userPermissions.canRemoveTeamMembers"
                  label="Cancel"
                  color="negative"
                  flat
                  @click="cancelTeamInvitation(invitation)"
                />
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <div v-if="team.users.length > 0">
      <!-- Manage Team Members -->
      <div class="row q-col-gutter-sm q-mt-lg">
        <div class="col-12 col-md-4">
          <h3 class="q-mb-sm text-h6">Team Members</h3>
          <div class="q-mb-md">All of the people that are part of this team.</div>
        </div>
        <div class="col-12 col-md-8">
          <q-card flat class="q-pa-sm">
            <q-card-section>
              <div
                v-for="user in team.users"
                :key="user.id"
                class="flex items-center justify-between"
              >
                <div class="flex items-center">
                  <img
                    style="width: 40px; border-radius: 50%"
                    :src="user.profile_photo_url"
                    :alt="user.name"
                  />
                  <div class="q-ml-md">
                    {{ user.name }}
                  </div>
                </div>

                <div>
                  <q-btn
                    v-if="availableRoles.length"
                    :label="displayableRole(user.membership.role)"
                    class="q-ml-sm"
                    color="grey"
                    flat
                    :disable="!userPermissions.canAddTeamMembers"
                    @click="manageRole(user)"
                  />

                  <q-btn
                    v-if="$page.props.auth.user.id === user.id"
                    label="Leave"
                    class="q-ml-xs"
                    color="negative"
                    flat
                    @click="confirmLeavingTeam"
                  />

                  <q-btn
                    v-else-if="userPermissions.canRemoveTeamMembers"
                    label="Remove"
                    class="q-ml-xs"
                    color="negative"
                    flat
                    @click="confirmTeamMemberRemoval(user)"
                  />
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <!-- Role Management Modal -->
    <q-dialog v-model="currentlyManagingRole" @hide="currentlyManagingRole = false">
      <q-card flat>
        <q-card-section>
          <div class="text-h6">Manage Role</div>
        </q-card-section>

        <q-card-section v-if="managingRoleFor">
          <q-btn
            v-for="role in availableRoles"
            :key="role.key"
            type="button"
            class="q-my-xs full-width"
            no-caps
            outline
            stack
            :color="updateRoleForm.role == role.key ? 'primary' : 'grey'"
            @click="updateRoleForm.role = role.key"
          >
            <div class="self-start text-left q-py-sm">
              <div class="text-body1 text-weight-bold">
                {{ role.name }}
              </div>
              <div class="q-mt-xs">
                {{ role.description }}
              </div>
            </div>
          </q-btn>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" @click="currentlyManagingRole = false" color="grey" />
          <q-btn
            label="Save"
            flat
            type="button"
            color="primary"
            class="q-ml-md"
            :loading="updateRoleForm.processing"
            @click="updateRole"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Leave Team Confirmation Modal -->
    <q-dialog v-model="confirmingLeavingTeam" @hide="confirmingLeavingTeam = false">
      <q-card flat>
        <q-card-section>
          <div class="text-h6">Leave Team</div>
          <div>Are you sure you would like to leave this team?</div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" @click="confirmingLeavingTeam = false" color="grey" />
          <q-btn
            label="Leave"
            flat
            type="button"
            color="negative"
            class="q-ml-md"
            :loading="leaveTeamForm.processing"
            @click="leaveTeam"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Remove Team Member Confirmation Modal -->
    <q-dialog :model-value="teamMemberBeingRemoved != null" @hide="teamMemberBeingRemoved = null">
      <q-card flat>
        <q-card-section>
          <div class="text-h6">Remove Team Member</div>
          <div>Are you sure you would like to remove this person from the team?</div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" @click="teamMemberBeingRemoved = null" color="grey" />
          <q-btn
            label="Remove"
            flat
            type="button"
            color="negative"
            class="q-ml-md"
            :loading="removeTeamMemberForm.processing"
            @click="removeTeamMember"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>
