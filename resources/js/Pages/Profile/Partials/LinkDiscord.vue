<template>
  <Alert :type="linkedDiscord ? 'positive' : 'warning'">
    <div class="flex items-center justify-between gap-xs-sm">
      <span v-if="linkedDiscord">Linked to Discord account.</span>
      <span v-else>Not linked to Discord account.</span>
      <div>
        <q-btn
          v-bind="{ ...(!linkedDiscord && { href: $route('link-discord.redirect') }) }"
          :color="linkedDiscord ? 'positive' : 'warning'"
          :label="
            linkedDiscord ? linkedDiscord.name || linkedDiscord.discord_id : 'Link Discord Account'
          "
          class="text-sm q-px-md q-py-xs"
          type="button"
          outline
        />
        <q-btn
          v-if="linkedDiscord"
          @click="$inertia.visit($route('link-discord.unlink'))"
          :icon="ionClose"
          class="text-sm q-px-xs q-py-xs q-ml-sm"
          color="negative"
          type="button"
          flat
        >
          <q-tooltip>Unlink Discord Account</q-tooltip>
        </q-btn>
      </div>
    </div>
  </Alert>
</template>

<script setup>
import Alert from '@/Components/Alert.vue'
import { usePage } from '@inertiajs/vue3'
import { ionClose } from '@quasar/extras/ionicons-v7'
import { computed } from 'vue'

const page = usePage()
const linkedDiscord = computed(() => page.props.linked_discord)
</script>
