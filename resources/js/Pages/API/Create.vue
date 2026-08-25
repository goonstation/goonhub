<script setup>
import Alert from '@/Components/Alert.vue'
import ApiTokenForm from '@/Components/Forms/ApiTokenForm.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { usePage } from '@inertiajs/vue3'
import { ionCheckmarkCircle, ionCopy } from '@quasar/extras/ionicons-v6'
import { copyToClipboard } from 'quasar'
import { computed, ref, watch } from 'vue'

defineOptions({
  layout: (h, page) => h(DashboardLayout, { title: 'Create API Token' }, () => page),
})

defineProps({
  availablePermissions: Array,
})

const page = usePage()

// Token display dialog
const displayingToken = ref(false)
const copied = ref(false)

const flashToken = computed(() => page.props.jetstream?.flash?.token)

// Show dialog when a new token is created
watch(flashToken, (token) => {
  if (token) {
    displayingToken.value = true
    copied.value = false
  }
})

const copyToken = async () => {
  if (flashToken.value) {
    await copyToClipboard(flashToken.value)
    copied.value = true
  }
}
</script>

<template>
  <div class="q-mx-auto q-mt-md" style="width: 100%; max-width: 1200px">
    <Alert v-if="page.props.flash.error" type="negative" class="q-mb-md">
      <span class="text-weight-medium">{{ page.props.flash.error }}</span>
    </Alert>

    <Alert v-if="page.props.flash.success" :opacity="20" type="positive" class="q-mb-md">
      <span class="text-weight-medium">{{ page.props.flash.success }}</span>
    </Alert>

    <q-card class="gh-card api-tokens-card q-mb-md" flat>
      <div class="gh-card__header q-pa-md bordered">
        <span>Create API Token</span>
      </div>
      <q-card-section class="api-tokens-section">
        <ApiTokenForm :available-permissions="availablePermissions" />
      </q-card-section>
    </q-card>

    <!-- Token Created Dialog -->
    <q-dialog v-model="displayingToken">
      <q-card flat class="token-dialog">
        <q-card-section class="q-pb-sm">
          <div class="dialog-header">
            <q-icon :name="ionCheckmarkCircle" size="40px" color="positive" />
            <div class="text-h6 q-mt-sm">API Token Created</div>
          </div>
          <div class="text-body2 text-grey-5 text-center q-mt-sm">
            Please copy your new API token now. For your security, it won't be shown again.
          </div>
        </q-card-section>

        <q-card-section>
          <div class="token-display">
            <code class="token-value">{{ flashToken }}</code>
            <q-btn
              flat
              dense
              :icon="ionCopy"
              :color="copied ? 'positive' : 'grey-5'"
              @click="copyToken"
            >
              <q-tooltip>{{ copied ? 'Copied!' : 'Copy to clipboard' }}</q-tooltip>
            </q-btn>
          </div>
          <div v-if="copied" class="text-positive text-caption text-center q-mt-sm">
            <q-icon :name="ionCheckmarkCircle" size="14px" class="q-mr-xs" />
            Copied to clipboard
          </div>
        </q-card-section>

        <q-card-actions align="center" class="q-pb-md">
          <q-btn
            unelevated
            color="primary"
            text-color="dark"
            label="Done"
            class="q-px-xl"
            @click="$inertia.visit(route('web.api-tokens.index'))"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<style lang="scss" scoped>
@use 'quasar/src/css/variables' as q;

// Allow sticky positioning to work inside the card
.api-tokens-card {
  overflow: visible !important;
}

.api-tokens-section {
  overflow: visible !important;
}

// Token dialog
.token-dialog {
  min-width: 400px;
  max-width: 500px;
  background: q.$dark;
}

.dialog-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.token-display {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
}

.token-value {
  flex: 1;
  font-family: 'Fira Code', 'Monaco', 'Consolas', monospace;
  font-size: 0.85rem;
  color: var(--q-primary);
  word-break: break-all;
  line-height: 1.4;
}
</style>
