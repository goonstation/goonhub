<template>
  <Alert :type="linkedByond ? 'positive' : 'warning'">
    <div class="flex items-center justify-between gap-xs-sm">
      <span v-if="linkedByond">Linked to BYOND account.</span>
      <span v-else>Not linked to BYOND account.</span>
      <div>
        <q-btn
          :href="
            linkedByond
              ? `https://www.byond.com/members/${linkedByond.ckey}`
              : $route('link-byond.redirect')
          "
          :target="linkedByond ? '_blank' : '_self'"
          :color="linkedByond ? 'positive' : 'warning'"
          :label="linkedByond ? linkedByond.ckey : 'Link BYOND Account'"
          class="text-sm q-px-md q-py-xs"
          type="button"
          outline
        />
        <q-btn
          v-if="linkedByond"
          @click="$inertia.visit($route('link-byond.unlink'))"
          :icon="ionClose"
          class="text-sm q-px-xs q-py-xs q-ml-sm"
          color="negative"
          type="button"
          flat
        >
          <q-tooltip>Unlink BYOND Account</q-tooltip>
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
const linkedByond = computed(() => page.props.linked_byond)
</script>
