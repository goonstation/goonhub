<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DeleteTeamForm from './Partials/DeleteTeamForm.vue'
import TeamMemberManager from './Partials/TeamMemberManager.vue'
import UpdateTeamNameForm from './Partials/UpdateTeamNameForm.vue'

defineProps({
  team: Object,
  availableRoles: Array,
  permissions: Object,
})
</script>

<template>
  <DashboardLayout title="Team Settings">
    <UpdateTeamNameForm :team="team" :permissions="permissions" />

    <TeamMemberManager
      :team="team"
      :available-roles="availableRoles"
      :user-permissions="permissions"
    />

    <template v-if="permissions.canDeleteTeam && !team.personal_team">
      <DeleteTeamForm class="q-mt-lg" :team="team" />
    </template>
  </DashboardLayout>
</template>
