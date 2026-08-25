<script setup>
import PlayerAvatar from '@/Components/PlayerAvatar.vue'
import BaseSelect from '@/Components/Selects/BaseSelect.vue'
</script>

<template>
  <base-select v-bind="$attrs" load-route="api.players.index" option-value="id">
    <template #selected-item-label="{ opt: player }">
      <div v-if="typeof player === 'object'" class="flex items-center gap-xs-md q-mt-xs q-mr-xs">
        <player-avatar :player="player" size="sm" />
        <div>
          <div>{{ player.key || player.ckey }}</div>
          <div class="flex gap-xs-sm text-caption">
            <span v-if="player.is_mentor" class="text-purple-4">Mentor</span>
            <span v-if="player.is_hos" class="text-orange">HOS</span>
            <span v-if="player.is_whitelisted" class="text-info">Whitelisted</span>
            <span v-if="player.can_bypass_cap" class="text-green">Can Bypass Cap</span>
          </div>
        </div>
      </div>
      <div v-else>{{ player }}</div>
    </template>

    <template #option="{ itemProps, opt: player }">
      <q-item v-bind="itemProps">
        <q-item-section avatar>
          <player-avatar :player="player" size="sm" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ player.key || player.ckey }}</q-item-label>
          <q-item-label class="flex gap-xs-sm" caption>
            <span v-if="player.is_mentor" class="text-purple-4">Mentor</span>
            <span v-if="player.is_hos" class="text-orange">HOS</span>
            <span v-if="player.is_whitelisted" class="text-info">Whitelisted</span>
            <span v-if="player.can_bypass_cap" class="text-green">Can Bypass Cap</span>
          </q-item-label>
        </q-item-section>
      </q-item>
    </template>
  </base-select>
</template>
