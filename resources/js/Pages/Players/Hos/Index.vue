<template>
  <app-layout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Head of Security Players</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <player-hos-table />
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import PlayerHosTable from '@/Components/Tables/Admin/HosTable.vue'
import AppLayout from '@/Layouts/AppLayout.vue'

export default {
  components: {
    AppLayout,
    PlayerHosTable,
  },
}
</script>
