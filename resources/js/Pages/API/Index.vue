<script setup>
import Alert from '@/Components/Alert.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import {
  ionAdd,
  ionCheckmarkCircle,
  ionChevronDown,
  ionCloseCircle,
  ionCreate,
  ionCreateOutline,
  ionEye,
  ionFilter,
  ionFlash,
  ionKeyOutline,
  ionRefresh,
  ionSearch,
  ionServerOutline,
  ionShieldCheckmark,
  ionSwapVertical,
  ionTime,
  ionTimeOutline,
  ionTrash,
  ionTrashOutline,
  ionWarning,
} from '@quasar/extras/ionicons-v7'
import { date } from 'quasar'
import { computed, ref } from 'vue'

defineOptions({
  layout: (h, page) => h(DashboardLayout, { title: 'API Tokens' }, () => page),
})

const props = defineProps({
  tokens: Array,
})

const page = usePage()
const deleteForm = useForm({})
const tokenToDelete = ref(null)
const searchQuery = ref('')
const statusFilter = ref('all')
const sortBy = ref('created_desc')
const expandedTokens = ref(new Set())

// Constants
const STATUS_CONFIG = {
  expired: { color: 'negative', icon: ionCloseCircle, label: 'Expired' },
  unused: { color: 'grey-6', icon: ionTimeOutline, label: 'Never used' },
  active: { color: 'positive', icon: ionCheckmarkCircle, label: 'Active' },
}

const PERMISSION_GROUPS = [
  { key: 'view', label: 'View', icon: ionEye, color: 'info' },
  { key: 'add', label: 'Create', icon: ionCreate, color: 'positive' },
  { key: 'update', label: 'Update', icon: ionRefresh, color: 'warning' },
  { key: 'delete', label: 'Delete', icon: ionTrash, color: 'negative' },
  { key: 'other', label: 'Other', icon: ionFlash, color: 'purple' },
]

const statusOptions = [
  { label: 'All tokens', value: 'all' },
  { label: 'Active', value: 'active' },
  { label: 'Never used', value: 'unused' },
  { label: 'Expired', value: 'expired' },
  { label: 'Game Server', value: 'game_server' },
]

const sortOptions = [
  { label: 'Newest first', value: 'created_desc' },
  { label: 'Oldest first', value: 'created_asc' },
  { label: 'Name A-Z', value: 'name_asc' },
  { label: 'Name Z-A', value: 'name_desc' },
  { label: 'Recently used', value: 'last_used' },
]

// Helpers
const getTokenStatus = (token) => {
  if (token.expires_at && new Date(token.expires_at) < new Date()) return 'expired'
  if (!token.last_used_at) return 'unused'
  return 'active'
}

const formatPermissionLabel = (permission) => {
  const parts = permission.split('-')
  if (parts.length > 1) parts.shift()
  return parts.map((word) => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')
}

const groupAbilities = (abilities) => {
  if (!abilities?.length || abilities.includes('*')) return null

  const groups = { view: [], add: [], update: [], delete: [], other: [] }

  for (const ability of abilities) {
    const action = ability.startsWith('view-')
      ? 'view'
      : ability.startsWith('add-')
      ? 'add'
      : ability.startsWith('update-')
      ? 'update'
      : ability.startsWith('delete-')
      ? 'delete'
      : 'other'

    groups[action].push({ value: ability, label: formatPermissionLabel(ability) })
  }

  // Sort each group alphabetically
  for (const key of Object.keys(groups)) {
    groups[key].sort((a, b) => a.label.localeCompare(b.label))
  }

  return groups
}

// Processed tokens with pre-computed metadata
const processedTokens = computed(() => {
  return (props.tokens || []).map((token) => {
    const status = getTokenStatus(token)
    const statusConfig = STATUS_CONFIG[status] || STATUS_CONFIG.unused
    const groupedPermissions = groupAbilities(token.abilities)
    const hasFullAccess = token.abilities?.includes('*')
    const hasExpandable = token.abilities?.length > 0 && !hasFullAccess

    return {
      ...token,
      status,
      statusConfig,
      groupedPermissions,
      hasFullAccess,
      hasExpandable,
      abilitiesSummary: !token.abilities?.length
        ? 'No permissions'
        : hasFullAccess
        ? 'Full access'
        : `${token.abilities.length} permission${token.abilities.length !== 1 ? 's' : ''}`,
    }
  })
})

// Filtered and sorted tokens
const filteredTokens = computed(() => {
  let result = processedTokens.value

  if (searchQuery.value?.trim()) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter((token) => token.name.toLowerCase().includes(query))
  }

  if (statusFilter.value !== 'all') {
    result = result.filter((token) =>
      statusFilter.value === 'game_server'
        ? token.for_game_server
        : token.status === statusFilter.value
    )
  }

  return [...result].sort((a, b) => {
    switch (sortBy.value) {
      case 'created_desc':
        return new Date(b.created_at) - new Date(a.created_at)
      case 'created_asc':
        return new Date(a.created_at) - new Date(b.created_at)
      case 'name_asc':
        return a.name.localeCompare(b.name)
      case 'name_desc':
        return b.name.localeCompare(a.name)
      case 'last_used':
        if (!a.last_used_at && !b.last_used_at) return 0
        if (!a.last_used_at) return 1
        if (!b.last_used_at) return -1
        return new Date(b.last_used_at) - new Date(a.last_used_at)
      default:
        return 0
    }
  })
})

const hasTokens = computed(() => processedTokens.value.length > 0)

const tokenStats = computed(() => {
  const tokens = processedTokens.value
  return {
    total: tokens.length,
    active: tokens.filter((t) => t.status === 'active').length,
    unused: tokens.filter((t) => t.status === 'unused').length,
    expired: tokens.filter((t) => t.status === 'expired').length,
    gameServer: tokens.filter((t) => t.for_game_server).length,
  }
})

// Actions
const confirmDelete = (token) => (tokenToDelete.value = token)

const deleteToken = () => {
  deleteForm.delete(route('web.api-tokens.destroy', tokenToDelete.value.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => (tokenToDelete.value = null),
  })
}

const toggleTokenExpanded = (tokenId) => {
  const newSet = new Set(expandedTokens.value)
  if (newSet.has(tokenId)) newSet.delete(tokenId)
  else newSet.add(tokenId)
  expandedTokens.value = newSet
}

const isTokenExpanded = (tokenId) => expandedTokens.value.has(tokenId)

const formatDate = (dateString) =>
  dateString ? date.formatDate(new Date(dateString), 'MMM D, YYYY') : null

const formatDateFull = (dateString) =>
  dateString ? date.formatDate(new Date(dateString), 'MMMM D, YYYY [at] h:mm A') : null

const isExpired = (expiresAt) => expiresAt && new Date(expiresAt) < new Date()

const clearFilters = () => {
  searchQuery.value = ''
  statusFilter.value = 'all'
}
</script>

<template>
  <div class="api-tokens-page q-mx-auto q-mt-md" style="width: 100%; max-width: 1200px">
    <Alert v-if="page.props.flash.error" type="negative" class="q-mb-md">
      <span class="text-weight-medium">{{ page.props.flash.error }}</span>
    </Alert>

    <Alert v-if="page.props.flash.success" :opacity="20" type="positive" class="q-mb-md">
      <span class="text-weight-medium">{{ page.props.flash.success }}</span>
    </Alert>

    <q-card class="gh-card api-tokens-card q-mb-md" flat>
      <!-- Header -->
      <div class="gh-card__header q-pa-md bordered">
        <div class="header-row">
          <div class="header-left">
            <div class="header-title">
              <q-icon :name="ionKeyOutline" size="24px" class="q-mr-sm text-primary" />
              <span>API Tokens</span>
              <q-chip v-if="hasTokens" dense class="stats-chip q-ml-sm">
                {{ tokenStats.total }}
              </q-chip>
            </div>

            <div v-if="hasTokens && tokenStats.total > 1" class="stats-bar">
              <div
                v-for="stat in [
                  { key: 'active', dot: 'bg-positive', count: tokenStats.active },
                  { key: 'unused', dot: 'bg-grey-6', count: tokenStats.unused },
                  {
                    key: 'expired',
                    dot: 'bg-negative',
                    count: tokenStats.expired,
                    show: tokenStats.expired > 0,
                  },
                ]"
                :key="stat.key"
                v-show="stat.show !== false"
                class="stat-item"
                :class="{ active: statusFilter === stat.key }"
                @click="statusFilter = stat.key"
              >
                <span class="stat-dot" :class="stat.dot"></span>
                <span class="stat-label">{{ stat.count }} {{ stat.key }}</span>
              </div>
              <div
                v-if="tokenStats.gameServer > 0"
                class="stat-item"
                :class="{ active: statusFilter === 'game_server' }"
                @click="statusFilter = 'game_server'"
              >
                <q-icon :name="ionServerOutline" size="12px" class="q-mr-xs text-info" />
                <span class="stat-label">{{ tokenStats.gameServer }} game server</span>
              </div>
              <div
                v-if="statusFilter !== 'all'"
                class="stat-item clear-filter"
                @click="statusFilter = 'all'"
              >
                <span class="stat-label">Clear filter</span>
              </div>
            </div>
          </div>

          <Link :href="route('web.api-tokens.create')" class="header-action">
            <q-btn color="primary" text-color="dark" no-caps unelevated>
              <q-icon :name="ionAdd" size="xs" class="q-mr-xs" />
              Create Token
            </q-btn>
          </Link>
        </div>
      </div>

      <!-- Toolbar -->
      <div v-if="hasTokens && tokenStats.total > 1" class="tokens-toolbar q-px-md q-py-sm">
        <div class="toolbar-left">
          <q-input
            v-model="searchQuery"
            placeholder="Search tokens..."
            dense
            filled
            class="search-input"
            clearable
          >
            <template #prepend>
              <q-icon :name="ionSearch" size="18px" />
            </template>
          </q-input>
        </div>
        <div class="toolbar-right">
          <q-select
            v-model="statusFilter"
            :options="statusOptions"
            dense
            filled
            emit-value
            map-options
            class="filter-select"
          >
            <template #prepend>
              <q-icon :name="ionFilter" size="16px" />
            </template>
          </q-select>
          <q-select
            v-model="sortBy"
            :options="sortOptions"
            dense
            filled
            emit-value
            map-options
            class="sort-select"
          >
            <template #prepend>
              <q-icon :name="ionSwapVertical" size="16px" />
            </template>
          </q-select>
        </div>
      </div>

      <!-- Empty State -->
      <q-card-section v-if="!hasTokens" class="empty-state text-center q-py-xl">
        <div class="empty-icon-wrapper q-mb-lg">
          <q-icon :name="ionKeyOutline" size="72px" color="grey-7" />
        </div>
        <div class="text-h5 text-grey-4 q-mb-sm">No API Tokens Yet</div>
        <div class="text-body1 text-grey-6 q-mb-lg" style="max-width: 400px; margin: 0 auto">
          API tokens allow external services and applications to securely authenticate with Goonhub
          on your behalf.
        </div>
        <Link :href="route('web.api-tokens.create')">
          <q-btn color="primary" text-color="dark" no-caps unelevated size="md" class="q-px-lg">
            <q-icon :name="ionAdd" size="xs" class="q-mr-sm" />
            Create Your First Token
          </q-btn>
        </Link>
      </q-card-section>

      <!-- No Results State -->
      <q-card-section
        v-else-if="filteredTokens.length === 0"
        class="empty-state text-center q-py-xl"
      >
        <q-icon :name="ionSearch" size="48px" color="grey-6" class="q-mb-md" />
        <div class="text-h6 text-grey-5 q-mb-sm">No tokens found</div>
        <div class="text-body2 text-grey-6 q-mb-md">No tokens match your current filters.</div>
        <q-btn flat color="primary" no-caps @click="clearFilters">Clear filters</q-btn>
      </q-card-section>

      <!-- Token List -->
      <q-card-section v-else class="q-pa-none">
        <TransitionGroup name="token-list" tag="div" class="tokens-list">
          <div
            v-for="token in filteredTokens"
            :key="token.id"
            class="token-item"
            :class="[`status-${token.status}`, { 'game-server': token.for_game_server }]"
          >
            <div class="status-bar" :class="`bg-${token.statusConfig.color}`"></div>

            <div class="token-content">
              <div class="token-main">
                <div class="token-identity">
                  <div class="token-icon-wrapper" :class="`border-${token.statusConfig.color}`">
                    <q-icon :name="ionKeyOutline" size="20px" />
                  </div>
                  <div class="token-info">
                    <div class="token-name-row">
                      <span class="token-name">{{ token.name }}</span>
                      <q-badge
                        v-if="token.for_game_server"
                        color="info"
                        text-color="dark"
                        class="token-badge"
                      >
                        <q-icon :name="ionServerOutline" size="11px" class="q-mr-xs" />
                        Game Server
                      </q-badge>
                      <q-badge
                        v-if="token.status === 'expired'"
                        color="negative"
                        class="token-badge"
                      >
                        <q-icon :name="ionWarning" size="11px" class="q-mr-xs" />
                        Expired
                      </q-badge>
                    </div>
                    <div class="token-meta">
                      <span class="meta-item">
                        <q-icon :name="ionTime" size="13px" />
                        Created {{ formatDate(token.created_at) }}
                        <q-tooltip>{{ formatDateFull(token.created_at) }}</q-tooltip>
                      </span>
                      <span class="meta-divider">•</span>
                      <span class="meta-item" :class="{ 'text-grey-5': !token.last_used_at }">
                        <template v-if="token.last_used_ago"
                          >Used {{ token.last_used_ago }}</template
                        >
                        <template v-else>
                          <q-icon :name="ionTimeOutline" size="13px" />
                          Never used
                        </template>
                      </span>
                      <template v-if="token.expires_at">
                        <span class="meta-divider">•</span>
                        <span
                          class="meta-item"
                          :class="isExpired(token.expires_at) ? 'text-negative' : 'text-grey-6'"
                        >
                          {{ isExpired(token.expires_at) ? 'Expired' : 'Expires' }}
                          {{ formatDate(token.expires_at) }}
                        </span>
                      </template>
                    </div>
                  </div>
                </div>

                <div class="token-permissions">
                  <q-chip
                    :color="token.hasFullAccess ? 'warning' : 'grey-9'"
                    :text-color="token.hasFullAccess ? 'dark' : 'grey-4'"
                    class="permissions-chip"
                    :class="{ clickable: token.hasExpandable }"
                    :clickable="token.hasExpandable"
                    @click="token.hasExpandable && toggleTokenExpanded(token.id)"
                  >
                    <q-icon :name="ionShieldCheckmark" size="14px" class="q-mr-xs" />
                    {{ token.abilitiesSummary }}
                    <q-icon
                      v-if="token.hasExpandable"
                      :name="ionChevronDown"
                      size="14px"
                      class="expand-icon q-ml-xs"
                      :class="{ expanded: isTokenExpanded(token.id) }"
                    />
                  </q-chip>
                </div>

                <div class="token-actions">
                  <Link :href="route('web.api-tokens.edit', token.id)">
                    <q-btn flat round dense :icon="ionCreateOutline" size="sm">
                      <q-tooltip>Edit token</q-tooltip>
                    </q-btn>
                  </Link>
                  <q-btn
                    flat
                    round
                    dense
                    color="negative"
                    :icon="ionTrashOutline"
                    size="sm"
                    @click="confirmDelete(token)"
                  >
                    <q-tooltip>Delete token</q-tooltip>
                  </q-btn>
                </div>
              </div>

              <!-- Expanded Permissions Panel -->
              <Transition name="permissions-expand">
                <div
                  v-if="isTokenExpanded(token.id) && token.groupedPermissions"
                  class="permissions-panel"
                >
                  <div class="permissions-grid">
                    <div
                      v-for="group in PERMISSION_GROUPS"
                      :key="group.key"
                      v-show="token.groupedPermissions[group.key]?.length"
                      class="permission-group"
                    >
                      <div class="permission-group-header">
                        <q-icon :name="group.icon" size="14px" :class="`text-${group.color}`" />
                        <span>{{ group.label }}</span>
                        <q-badge
                          :label="token.groupedPermissions[group.key]?.length"
                          :color="group.color"
                          :text-color="group.color === 'warning' ? 'dark' : undefined"
                        />
                      </div>
                      <div class="permission-tags">
                        <span
                          v-for="perm in token.groupedPermissions[group.key]"
                          :key="perm.value"
                          class="permission-tag"
                          :class="`action-${group.key}`"
                        >
                          {{ perm.label }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </Transition>
            </div>
          </div>
        </TransitionGroup>
      </q-card-section>
    </q-card>

    <!-- Delete Confirmation Dialog -->
    <q-dialog :model-value="tokenToDelete != null" @hide="tokenToDelete = null">
      <q-card flat class="delete-dialog">
        <q-card-section class="q-pb-none">
          <div class="dialog-icon q-mb-md">
            <q-icon :name="ionWarning" size="40px" color="negative" />
          </div>
          <div class="text-h6 text-center">Delete API Token</div>
        </q-card-section>
        <q-card-section class="text-center">
          <p class="q-mb-sm">
            Are you sure you want to delete <strong>"{{ tokenToDelete?.name }}"</strong>?
          </p>
          <p class="text-grey-6 text-body2 q-mb-none">
            Any applications or services using this token will immediately lose access. This action
            cannot be undone.
          </p>
        </q-card-section>
        <q-card-actions align="center" class="q-px-lg q-pb-lg">
          <q-btn flat label="Cancel" color="grey" class="q-px-lg" @click="tokenToDelete = null" />
          <q-btn
            label="Delete Token"
            unelevated
            color="negative"
            class="q-px-lg"
            :loading="deleteForm.processing"
            @click="deleteToken"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<style lang="scss" scoped>
@use 'quasar/src/css/variables' as q;
@use '@css/quasar-variables' as *;

// Action color map
$action-colors: (
  'view': q.$info,
  'add': q.$positive,
  'update': q.$warning,
  'delete': q.$negative,
  'other': #ba68c8,
);

.api-tokens-page {
  --status-active: #{q.$positive};
  --status-unused: #{q.$grey-6};
  --status-expired: #{q.$negative};
}

// Header
.header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
  width: 100%;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  flex-wrap: wrap;
  flex: 1;
  min-width: 0;
}

.header-title {
  display: flex;
  align-items: center;
  font-size: 1rem;
  font-weight: 600;
  flex-shrink: 0;

  .stats-chip {
    background: q.$grey-9;
    color: q.$grey-4;
    font-weight: 600;
    font-size: 0.7rem;
    min-height: 20px;
  }
}

.header-action {
  flex-shrink: 0;
}

// Stats Bar
.stats-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  align-items: center;
}

.stat-item {
  display: flex;
  align-items: center;
  padding: 0.25rem 0.6rem;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.15s ease;
  background: rgba(255, 255, 255, 0.03);

  &:hover {
    background: rgba(255, 255, 255, 0.08);
  }

  &.active {
    background: color-mix(in srgb, var(--q-primary) 15%, transparent);
    .stat-label {
      color: var(--q-primary);
    }
  }

  &.clear-filter .stat-label {
    color: q.$grey-5;
    font-size: 0.7rem;
  }
}

.stat-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  margin-right: 0.375rem;
}

.stat-label {
  font-size: 0.75rem;
  color: q.$grey-5;
}

// Toolbar
.tokens-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  border-bottom: 1px solid $dark-page;
  background: rgba(0, 0, 0, 0.15);
  flex-wrap: wrap;
}

.toolbar-left {
  flex: 1;
  min-width: 200px;
  max-width: 300px;
}

.toolbar-right {
  display: flex;
  gap: 0.5rem;
}

.search-input,
.filter-select,
.sort-select {
  :deep(.q-field__control) {
    background: rgba(255, 255, 255, 0.05);
  }
}

.filter-select,
.sort-select {
  min-width: 140px;
}

// Empty State
.empty-state .empty-icon-wrapper {
  display: inline-flex;
  padding: 1.5rem;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.03);
  border: 2px dashed rgba(255, 255, 255, 0.1);
}

// Token List
.tokens-list {
  position: relative;
}

.token-item {
  display: flex;
  position: relative;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  transition: background 0.15s ease;

  &:last-child {
    border-bottom: none;
  }

  &:hover {
    background: rgba(255, 255, 255, 0.02);
  }

  &.status-expired {
    opacity: 0.7;
    .token-name {
      text-decoration: line-through;
      text-decoration-color: rgba(255, 255, 255, 0.3);
    }
  }

  &.status-unused .token-icon-wrapper {
    opacity: 0.6;
  }
}

.status-bar {
  width: 3px;
  flex-shrink: 0;
}

.token-content {
  flex: 1;
  padding: 1rem;
  min-width: 0;
}

.token-main {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.token-identity {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex: 1;
  min-width: 250px;
}

.token-icon-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.05);
  border: 2px solid transparent;
  flex-shrink: 0;
  color: var(--q-primary);

  &.border-positive {
    border-color: color-mix(in srgb, var(--status-active) 40%, transparent);
  }
  &.border-grey-6 {
    border-color: rgba(255, 255, 255, 0.1);
    color: q.$grey-5;
  }
  &.border-negative {
    border-color: color-mix(in srgb, var(--status-expired) 40%, transparent);
    color: var(--status-expired);
  }
}

.token-info {
  flex: 1;
  min-width: 0;
}

.token-name-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.token-name {
  font-weight: 600;
  font-size: 0.95rem;
  color: q.$grey-3;
}

.token-badge {
  font-size: 0.65rem;
  padding: 0.125rem 0.375rem;
}

.token-meta {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.25rem;
  margin-top: 0.25rem;
  font-size: 0.75rem;
  color: q.$grey-6;
}

.meta-item {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
}

.meta-divider {
  color: q.$grey-8;
  margin: 0 0.125rem;
}

.token-permissions {
  flex-shrink: 0;

  .permissions-chip {
    font-size: 0.7rem;
    font-weight: 500;

    &.clickable {
      cursor: pointer;
      transition: all 0.15s ease;
      &:hover {
        filter: brightness(1.1);
      }
    }

    .expand-icon {
      transition: transform 0.2s ease;
      &.expanded {
        transform: rotate(180deg);
      }
    }
  }
}

.token-actions {
  display: flex;
  gap: 0.25rem;
  flex-shrink: 0;
}

// Permissions Panel
.permissions-panel {
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.permissions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.permission-group {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 6px;
  padding: 0.75rem;
}

.permission-group-header {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  margin-bottom: 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: q.$grey-4;

  .q-badge {
    font-size: 0.6rem;
    min-height: 16px;
    padding: 0 5px;
  }
}

.permission-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.375rem;
}

.permission-tag {
  display: inline-block;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 500;
  background: rgba(255, 255, 255, 0.05);
  color: q.$grey-5;
  border: 1px solid transparent;

  @each $action, $color in $action-colors {
    &.action-#{$action} {
      border-color: color-mix(in srgb, $color 30%, transparent);
      color: $color;
    }
  }
}

// Transitions
.permissions-expand-enter-active {
  transition: all 0.25s ease-out;
}
.permissions-expand-leave-active {
  transition: all 0.2s ease-in;
}
.permissions-expand-enter-from,
.permissions-expand-leave-to {
  opacity: 0;
  max-height: 0;
  margin-top: 0;
  padding-top: 0;
  overflow: hidden;
}
.permissions-expand-enter-to,
.permissions-expand-leave-from {
  max-height: 500px;
}

.token-list-enter-active {
  transition: all 0.3s ease;
}
.token-list-leave-active {
  transition: all 0.2s ease;
  position: absolute;
  left: 0;
  right: 0;
}
.token-list-enter-from {
  opacity: 0;
  transform: translateX(-20px);
}
.token-list-leave-to {
  opacity: 0;
  transform: translateX(20px);
}
.token-list-move {
  transition: transform 0.3s ease;
}

// Delete Dialog
.delete-dialog {
  min-width: 380px;
  max-width: 420px;
  background: q.$dark;
}

.dialog-icon {
  display: flex;
  justify-content: center;
}

// Responsive
@media (max-width: q.$breakpoint-sm-max) {
  .header-row {
    flex-direction: column;
    align-items: stretch;
    gap: 0.75rem;
  }
  .header-left {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }
  .header-action {
    align-self: flex-start;
  }
}

@media (max-width: q.$breakpoint-xs-max) {
  .tokens-toolbar {
    flex-direction: column;
    align-items: stretch;
  }
  .toolbar-left {
    max-width: none;
  }
  .toolbar-right {
    flex-wrap: wrap;
  }
  .filter-select,
  .sort-select {
    flex: 1;
    min-width: 120px;
  }
  .token-main {
    flex-direction: column;
    align-items: flex-start;
  }
  .token-identity {
    width: 100%;
  }
  .token-permissions {
    margin-left: 52px;
  }
  .token-actions {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
  }
}
</style>
