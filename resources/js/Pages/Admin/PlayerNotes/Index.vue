<template>
  <player-notes-table :initial="playerNotes" />
</template>

<script>
import PlayerNotesTable from '@/Components/Tables/Admin/PlayerNotesTable.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    PlayerNotesTable
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Notes' }, () => page),

  props: {
    playerNotes: Object
  }
}
</script>
