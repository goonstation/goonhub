<script setup>
import PlayerAvatar from '@/Components/PlayerAvatar.vue'
import BaseSelect from '@/Components/Selects/BaseSelect.vue'
</script>

<template>
  <base-select
    v-bind="$attrs"
    load-route="api.game-admins.index"
    option-value="id"
    search-key="ckey"
  >
    <template #selected-item-label="{ opt: admin }">
      <div v-if="typeof admin === 'object'" class="flex items-center gap-xs-md q-mt-xs q-mr-xs">
        <player-avatar :player="admin.player" size="sm" />
        <div>{{ admin.alias || admin.player.key || admin.player.ckey }}</div>
        <div class="q-ml-xs text-caption text-opacity-60">{{ admin.rank.rank }}</div>
      </div>
      <div v-else>{{ admin }}</div>
    </template>

    <template #option="{ itemProps, opt: admin }">
      <q-item v-bind="itemProps">
        <q-item-section avatar>
          <player-avatar :player="admin.player" size="sm" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ admin.alias || admin.player.key || admin.player.ckey }}</q-item-label>
        </q-item-section>
        <q-item-section side>
          <q-item-label>{{ admin.rank.rank }}</q-item-label>
        </q-item-section>
      </q-item>
    </template>
  </base-select>
</template>
