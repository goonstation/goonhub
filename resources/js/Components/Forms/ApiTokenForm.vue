<script setup>
import ActionMessage from '@/Components/ActionMessage.vue'
import { useForm } from '@inertiajs/vue3'
import {
  ionCheckmarkCircle,
  ionChevronDown,
  ionClose,
  ionCreate,
  ionEye,
  ionFlash,
  ionKey,
  ionRefresh,
  ionSearch,
  ionShieldCheckmark,
  ionTrash,
  ionWarning,
} from '@quasar/extras/ionicons-v6'
import { computed, ref } from 'vue'

const props = defineProps({
  availablePermissions: Array,
  token: {
    type: Object,
    default: null,
  },
  mode: {
    type: String,
    default: 'create',
    validator: (value) => ['create', 'edit'].includes(value),
  },
})

const isEditMode = computed(() => props.mode === 'edit')

const form = useForm({
  name: props.token?.name ?? '',
  permissions: props.token?.abilities ?? [],
  for_game_server: false,
})

const searchQuery = ref('')
const expandedGroups = ref({})
const activeFilter = ref('all') // 'all', 'view', 'add', 'update', 'delete', 'other'
const showSelectedOnly = ref(false)

// Initialize all groups as expanded
props.availablePermissions.forEach((group) => {
  expandedGroups.value[group.group] = true
})

const submitForm = () => {
  if (isEditMode.value) {
    form.put(route('web.api-tokens.update', props.token.id), {
      preserveScroll: true,
    })
  } else {
    form.post(route('web.api-tokens.store'), {
      preserveScroll: true,
      onSuccess: () => {
        form.reset()
        searchQuery.value = ''
        activeFilter.value = 'all'
        showSelectedOnly.value = false
      },
    })
  }
}

// Permission action classification
const getPermissionAction = (permissionValue) => {
  if (permissionValue.startsWith('view-')) return 'view'
  if (permissionValue.startsWith('add-')) return 'add'
  if (permissionValue.startsWith('update-')) return 'update'
  if (permissionValue.startsWith('delete-')) return 'delete'
  return 'other'
}

// Get action icon
const getActionIcon = (action) => {
  switch (action) {
    case 'view':
      return ionEye
    case 'add':
      return ionCreate
    case 'update':
      return ionRefresh
    case 'delete':
      return ionTrash
    default:
      return ionFlash
  }
}

// Action filter options
const actionFilters = [
  { value: 'all', label: 'All', icon: ionShieldCheckmark },
  { value: 'view', label: 'View', icon: ionEye },
  { value: 'add', label: 'Add', icon: ionCreate },
  { value: 'update', label: 'Update', icon: ionRefresh },
  { value: 'delete', label: 'Delete', icon: ionTrash },
  { value: 'other', label: 'Other', icon: ionFlash },
]

// Filter permissions based on search, action filter, and selected state
const filteredPermissions = computed(() => {
  let groups = props.availablePermissions

  // Apply action filter
  if (activeFilter.value !== 'all') {
    groups = groups
      .map((group) => ({
        ...group,
        permissions: group.permissions.filter(
          (p) => getPermissionAction(p.value) === activeFilter.value
        ),
      }))
      .filter((group) => group.permissions.length > 0)
  }

  // Apply selected only filter
  if (showSelectedOnly.value) {
    groups = groups
      .map((group) => ({
        ...group,
        permissions: group.permissions.filter((p) => form.permissions.includes(p.value)),
      }))
      .filter((group) => group.permissions.length > 0)
  }

  // Apply search filter
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase()
    groups = groups
      .map((group) => ({
        ...group,
        permissions: group.permissions.filter(
          (p) =>
            p.label.toLowerCase().includes(query) ||
            p.description?.toLowerCase().includes(query) ||
            p.value.toLowerCase().includes(query)
        ),
      }))
      .filter((group) => group.permissions.length > 0)
  }

  return groups
})

// Highlight search matches in text
const highlightMatch = (text, query) => {
  if (!query.trim() || !text) return text
  const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi')
  return text.replace(regex, '<mark>$1</mark>')
}

// Count selected permissions per group
const getGroupStats = (group) => {
  const total = group.permissions.length
  const selected = group.permissions.filter((p) => form.permissions.includes(p.value)).length
  return { selected, total }
}

// Check if all permissions in a group are selected
const isGroupFullySelected = (group) => {
  return group.permissions.every((p) => form.permissions.includes(p.value))
}

// Check if some (but not all) permissions in a group are selected
const isGroupPartiallySelected = (group) => {
  const hasAny = group.permissions.some((p) => form.permissions.includes(p.value))
  const hasAll = isGroupFullySelected(group)
  return hasAny && !hasAll
}

// Toggle all permissions in a group
const toggleGroup = (group) => {
  const allSelected = isGroupFullySelected(group)

  if (allSelected) {
    const groupValues = group.permissions.map((p) => p.value)
    form.permissions = form.permissions.filter((p) => !groupValues.includes(p))
  } else {
    group.permissions.forEach((p) => {
      if (!form.permissions.includes(p.value)) {
        form.permissions.push(p.value)
      }
    })
  }
}

// Toggle individual permission
const togglePermission = (permission) => {
  const index = form.permissions.indexOf(permission.value)
  if (index > -1) {
    form.permissions.splice(index, 1)
  } else {
    form.permissions.push(permission.value)
  }
}

// Clear all selections
const clearAll = () => {
  form.permissions = []
}

// Presets
const presets = [
  {
    name: 'Read Only',
    icon: ionEye,
    description: 'View-only access to all data',
    filter: (p) => p.startsWith('view-'),
  },
  {
    name: 'Full Access',
    icon: ionKey,
    description: 'Complete access to all permissions',
    filter: () => true,
  },
]

const applyPreset = (preset) => {
  form.permissions = []
  props.availablePermissions.forEach((group) => {
    group.permissions.forEach((p) => {
      if (preset.filter(p.value)) {
        form.permissions.push(p.value)
      }
    })
  })
}

// Total stats
const totalStats = computed(() => {
  const total = props.availablePermissions.reduce((acc, g) => acc + g.permissions.length, 0)
  const viewCount = form.permissions.filter((p) => p.startsWith('view-')).length
  const addCount = form.permissions.filter((p) => p.startsWith('add-')).length
  const updateCount = form.permissions.filter((p) => p.startsWith('update-')).length
  const deleteCount = form.permissions.filter((p) => p.startsWith('delete-')).length
  const otherCount = form.permissions.length - viewCount - addCount - updateCount - deleteCount

  return {
    selected: form.permissions.length,
    total,
    byAction: {
      view: viewCount,
      add: addCount,
      update: updateCount,
      delete: deleteCount,
      other: otherCount,
    },
  }
})

// Get count for a specific action filter
const getFilterCount = (filter) => {
  if (filter === 'all') return totalStats.value.selected
  return totalStats.value.byAction[filter] || 0
}

// Clear search
const clearSearch = () => {
  searchQuery.value = ''
}

// Expand/collapse all groups
const expandAll = () => {
  props.availablePermissions.forEach((group) => {
    expandedGroups.value[group.group] = true
  })
}

const collapseAll = () => {
  props.availablePermissions.forEach((group) => {
    expandedGroups.value[group.group] = false
  })
}

const allExpanded = computed(() => {
  return props.availablePermissions.every((group) => expandedGroups.value[group.group])
})
</script>

<template>
  <q-form @submit="submitForm" class="token-form">
    <!-- Header: Token Name + Create Button -->
    <div class="form-header">
      <div class="header-input-section">
        <q-icon :name="ionKey" class="header-icon" />
        <q-input
          v-model="form.name"
          type="text"
          label="Token Name"
          placeholder="e.g., Production API, CI/CD Pipeline"
          filled
          dense
          required
          hide-bottom-space
          :error="!!form.errors.name"
          :error-message="form.errors.name"
          class="token-name-input"
        />
      </div>
      <div class="header-actions">
        <q-btn
          :label="isEditMode ? 'Update Token' : 'Create Token'"
          type="submit"
          color="primary"
          text-color="black"
          :loading="form.processing"
          :disable="!form.name || form.processing"
          unelevated
        />
        <ActionMessage :on="form.recentlySuccessful">{{
          isEditMode ? 'Updated!' : 'Created!'
        }}</ActionMessage>
      </div>
    </div>

    <!-- Main Permissions Panel -->
    <div class="permissions-panel">
      <!-- Sticky Header Container -->
      <div class="sticky-header">
        <!-- Panel Header with Stats -->
        <div class="panel-header">
          <div class="panel-title-row">
            <q-icon :name="ionShieldCheckmark" class="panel-icon" />
            <span class="panel-title">Permissions</span>
            <q-chip
              :label="`${totalStats.selected} of ${totalStats.total}`"
              class="total-chip"
              :class="{ 'has-selection': totalStats.selected > 0 }"
              dense
            />
          </div>

          <!-- Quick Presets -->
          <div class="presets-row">
            <span class="presets-label text-xs">Quick select:</span>
            <q-btn
              v-for="preset in presets"
              :key="preset.name"
              :label="preset.name"
              :icon="preset.icon"
              size="sm"
              flat
              dense
              class="preset-btn gh-btn--denser"
              @click="applyPreset(preset)"
            >
              <q-tooltip>{{ preset.description }}</q-tooltip>
            </q-btn>
            <q-btn
              label="Clear"
              :icon="ionClose"
              size="sm"
              flat
              dense
              :disable="form.permissions.length === 0"
              class="preset-btn clear-btn gh-btn--denser"
              @click="clearAll"
            />
          </div>
        </div>

        <!-- Filters Bar -->
        <div class="filters-bar">
          <!-- Action Filters -->
          <div class="action-filters">
            <q-btn
              v-for="filter in actionFilters"
              :key="filter.value"
              :icon="filter.icon"
              :label="filter.label"
              size="sm"
              :flat="activeFilter !== filter.value"
              :unelevated="activeFilter === filter.value"
              :color="activeFilter === filter.value ? 'primary' : undefined"
              :text-color="activeFilter === filter.value ? 'black' : undefined"
              dense
              class="filter-btn gh-btn--denser"
              @click="activeFilter = filter.value"
            >
              <q-badge
                v-if="getFilterCount(filter.value) > 0"
                color="primary"
                text-color="black"
                floating
                :label="getFilterCount(filter.value)"
                class="filter-badge"
              />
            </q-btn>
          </div>

          <!-- Search & Tools -->
          <div class="search-tools">
            <q-input
              v-model="searchQuery"
              placeholder="Search..."
              filled
              dense
              class="search-input gh-input--denser"
            >
              <template #prepend>
                <q-icon :name="ionSearch" class="search-icon" />
              </template>
              <template v-if="searchQuery" #append>
                <q-icon :name="ionClose" class="clear-icon" @click="clearSearch" />
              </template>
            </q-input>

            <q-separator vertical class="q-mx-sm" />

            <q-toggle
              v-model="showSelectedOnly"
              :icon="ionCheckmarkCircle"
              size="sm"
              color="accent"
              dense
              class="selected-toggle"
            >
              <q-tooltip>{{
                showSelectedOnly
                  ? 'Showing selected only – click to show all'
                  : 'Click to show selected only'
              }}</q-tooltip>
            </q-toggle>
            <span class="toggle-label text-xs">{{ showSelectedOnly ? 'Selected' : 'All' }}</span>

            <q-btn
              :label="allExpanded ? 'Collapse' : 'Expand'"
              size="sm"
              flat
              dense
              class="gh-btn--denser"
              @click="allExpanded ? collapseAll() : expandAll()"
            />
          </div>
        </div>
      </div>

      <!-- Permission Groups -->
      <div class="permission-groups">
        <TransitionGroup name="group-fade">
          <div
            v-for="group in filteredPermissions"
            :key="group.group"
            class="permission-group"
            :class="{ 'group-has-selection': getGroupStats(group).selected > 0 }"
          >
            <!-- Group Header -->
            <div
              class="group-header"
              @click="expandedGroups[group.group] = !expandedGroups[group.group]"
            >
              <q-checkbox
                :model-value="isGroupFullySelected(group)"
                :indeterminate-value="isGroupPartiallySelected(group) ? true : undefined"
                @click.stop="toggleGroup(group)"
                color="primary"
                class="group-checkbox"
                dense
              />
              <div class="group-title">{{ group.group }}</div>
              <q-chip
                :label="`${getGroupStats(group).selected}/${getGroupStats(group).total}`"
                :class="['group-counter', { 'has-selection': getGroupStats(group).selected > 0 }]"
                dense
                square
              />
              <q-icon
                :name="ionChevronDown"
                class="expand-icon"
                :class="{ expanded: expandedGroups[group.group] }"
              />
            </div>

            <!-- Group Body -->
            <Transition name="slide">
              <div v-show="expandedGroups[group.group]" class="group-body">
                <div class="permissions-list">
                  <div
                    v-for="permission in group.permissions"
                    :key="permission.value"
                    class="permission-item"
                    :class="[
                      `action-${getPermissionAction(permission.value)}`,
                      { selected: form.permissions.includes(permission.value) },
                    ]"
                    @click="togglePermission(permission)"
                  >
                    <q-checkbox
                      :model-value="form.permissions.includes(permission.value)"
                      color="primary"
                      class="permission-checkbox"
                      dense
                      @click.stop="togglePermission(permission)"
                    />

                    <q-icon
                      :name="getActionIcon(getPermissionAction(permission.value))"
                      class="action-icon"
                    />

                    <div class="permission-content">
                      <div
                        class="permission-label"
                        v-html="highlightMatch(permission.label, searchQuery)"
                      />
                      <div
                        v-if="permission.description"
                        class="permission-description"
                        v-html="highlightMatch(permission.description, searchQuery)"
                      />
                    </div>

                    <q-icon
                      v-if="getPermissionAction(permission.value) === 'delete'"
                      :name="ionWarning"
                      class="risk-indicator"
                    >
                      <q-tooltip>Destructive permission</q-tooltip>
                    </q-icon>

                    <q-icon
                      :name="ionCheckmarkCircle"
                      class="permission-check"
                      :class="{ visible: form.permissions.includes(permission.value) }"
                    />
                  </div>
                </div>
              </div>
            </Transition>
          </div>
        </TransitionGroup>

        <!-- Empty State -->
        <div v-if="filteredPermissions.length === 0" class="empty-state">
          <q-icon :name="ionSearch" size="2.5rem" class="empty-icon" />
          <div class="empty-text">
            <template v-if="searchQuery"> No permissions match "{{ searchQuery }}" </template>
            <template v-else-if="showSelectedOnly"> No permissions selected </template>
            <template v-else> No permissions available for this filter </template>
          </div>
          <q-btn
            v-if="searchQuery || showSelectedOnly"
            :label="searchQuery ? 'Clear Search' : 'Show All'"
            flat
            size="sm"
            @click="searchQuery ? clearSearch() : (showSelectedOnly = false)"
          />
        </div>
      </div>

      <!-- Footer Stats -->
      <div v-if="form.permissions.length > 0" class="panel-footer">
        <div class="stats-breakdown">
          <span class="stat-item" v-if="totalStats.byAction.view > 0">
            <q-icon :name="ionEye" size="xs" /> {{ totalStats.byAction.view }} view
          </span>
          <span class="stat-item" v-if="totalStats.byAction.add > 0">
            <q-icon :name="ionCreate" size="xs" /> {{ totalStats.byAction.add }} add
          </span>
          <span class="stat-item" v-if="totalStats.byAction.update > 0">
            <q-icon :name="ionRefresh" size="xs" /> {{ totalStats.byAction.update }} update
          </span>
          <span class="stat-item warning" v-if="totalStats.byAction.delete > 0">
            <q-icon :name="ionTrash" size="xs" /> {{ totalStats.byAction.delete }} delete
          </span>
          <span class="stat-item" v-if="totalStats.byAction.other > 0">
            <q-icon :name="ionFlash" size="xs" /> {{ totalStats.byAction.other }} other
          </span>
        </div>
        <div class="total-summary">
          {{ form.permissions.length }} permission{{ form.permissions.length !== 1 ? 's' : '' }}
          selected
        </div>
      </div>
    </div>
  </q-form>
</template>

<style lang="scss" scoped>
@use 'quasar/src/css/variables' as q;
@use '@css/quasar-variables' as *;

// Design tokens - using project conventions
$border-color: $dark-page;
$text-secondary: q.$grey-6;
$text-muted: q.$grey-7;

// Action colors
$color-view: $info;
$color-add: $positive;
$color-update: $warning;
$color-delete: $negative;
$color-other: #ba68c8;

.token-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

// Header
.form-header {
  display: flex;
  gap: 1rem;
  align-items: center;
  flex-wrap: wrap;

  .header-input-section {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 300px;

    .header-icon {
      font-size: 1.5rem;
      color: var(--q-primary);
      opacity: 0.8;
    }

    .token-name-input {
      flex: 1;
      max-width: 400px;
    }
  }

  .header-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
  }
}

// Main Panel
.permissions-panel {
  display: flex;
  flex-direction: column;
  border: 1px solid $border-color;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.02);
}

// Sticky Header Container
.sticky-header {
  position: sticky;
  top: 0;
  z-index: 10;
  border-radius: 8px 8px 0 0;
  background: q.$dark-page;
}

// Panel Header - Compact
.panel-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.375rem 0.75rem;
  background: $dark-page;
  border-bottom: 1px solid $border-color;
  flex-wrap: wrap;

  .panel-title-row {
    display: flex;
    align-items: center;
    gap: 0.375rem;

    .panel-icon {
      font-size: 1rem;
      color: var(--q-primary);
    }

    .panel-title {
      font-size: 0.85rem;
      font-weight: 600;
    }

    .total-chip {
      background: q.$grey-9;
      color: $text-secondary;
      font-weight: 600;
      font-size: 0.65rem;
      min-height: 18px;
      transition: all 0.2s;

      &.has-selection {
        background: color-mix(in srgb, var(--q-primary) 20%, transparent);
        color: var(--q-primary);
      }
    }
  }

  .presets-row {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    margin-left: auto;

    .presets-label {
      color: $text-muted;
      margin-right: 0.25rem;
    }

    .preset-btn {
      &.clear-btn {
        color: q.$grey-5;
      }
    }
  }
}

// Filters Bar
.filters-bar {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.25rem 0.75rem;
  background: rgba(0, 0, 0, 0.15);
  border-bottom: 1px solid $border-color;
  flex-wrap: wrap;

  .action-filters {
    display: flex;
    gap: 0.125rem;

    .filter-btn {
      position: relative;

      .filter-badge {
        font-size: 0.6rem;
        min-height: 14px;
        padding: 0 4px;
        top: -4px;
        right: -4px;
      }
    }
  }

  .search-tools {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    margin-left: auto;
    flex-wrap: wrap;

    .search-input {
      width: 150px;

      .search-icon {
        color: $text-secondary;
      }

      .clear-icon {
        cursor: pointer;
        color: $text-secondary;
        transition: color 0.2s;

        &:hover {
          color: q.$grey-4;
        }
      }
    }

    .selected-toggle {
      margin-left: 0;
    }

    .toggle-label {
      color: $text-secondary;
      margin-left: 0.125rem;
      user-select: none;
    }
  }
}

// Permission Groups
.permission-groups {
  display: flex;
  flex-direction: column;
  position: relative; // Required for leave transition absolute positioning
}

.permission-group {
  border-bottom: 1px solid $border-color;
  transition: background 0.2s;

  &:last-child {
    border-bottom: none;
  }

  &.group-has-selection {
    background: color-mix(in srgb, var(--q-primary) 3%, transparent);
  }
}

.group-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: rgba(0, 0, 0, 0.2);
  cursor: pointer;
  user-select: none;
  transition: background 0.15s;

  &:hover {
    background: rgba(0, 0, 0, 0.3);
  }

  .group-title {
    font-weight: 600;
    font-size: 0.85rem;
  }

  .group-counter {
    margin-left: auto;
    background: q.$grey-9;
    color: $text-secondary;
    font-size: 0.65rem;
    font-weight: 500;
    min-height: 18px;
    padding: 0 6px;

    &.has-selection {
      background: color-mix(in srgb, var(--q-primary) 25%, transparent);
      color: var(--q-primary);
    }
  }

  .expand-icon {
    font-size: 0.9rem;
    color: $text-secondary;
    transition: transform 0.2s ease;

    &.expanded {
      transform: rotate(180deg);
    }
  }
}

.group-body {
  border-top: 1px solid $border-color;
}

// Permissions List
.permissions-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
}

.permission-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  cursor: pointer;
  transition: background 0.1s;
  border-bottom: 1px solid rgba($border-color, 0.5);
  border-right: 1px solid rgba($border-color, 0.5);

  &:hover {
    background: $dark-page;
  }

  &.selected {
    background: color-mix(in srgb, var(--q-primary) 8%, transparent);

    .permission-label {
      color: var(--q-primary);
    }
  }

  // Action styling
  .action-icon {
    font-size: 0.9rem;
    opacity: 0.6;
    flex-shrink: 0;
  }

  &.action-view .action-icon {
    color: $color-view;
  }
  &.action-add .action-icon {
    color: $color-add;
  }
  &.action-update .action-icon {
    color: $color-update;
  }
  &.action-delete .action-icon {
    color: $color-delete;
  }
  &.action-other .action-icon {
    color: $color-other;
  }

  .permission-content {
    flex: 1;
    min-width: 0;
  }

  .permission-label {
    font-size: 0.8rem;
    font-weight: 500;
    line-height: 1.3;
    transition: color 0.1s;

    :deep(mark) {
      background: color-mix(in srgb, var(--q-primary) 40%, transparent);
      color: inherit;
      padding: 0 2px;
      border-radius: 2px;
    }
  }

  .permission-description {
    font-size: 0.7rem;
    color: $text-secondary;
    line-height: 1.3;
    margin-top: 2px;

    :deep(mark) {
      background: color-mix(in srgb, var(--q-primary) 30%, transparent);
      color: inherit;
      padding: 0 2px;
      border-radius: 2px;
    }
  }

  .risk-indicator {
    color: $color-delete;
    font-size: 0.85rem;
    flex-shrink: 0;
    opacity: 0.7;
  }

  .permission-check {
    color: var(--q-primary);
    font-size: 1rem;
    flex-shrink: 0;
    opacity: 0;
    transition: opacity 0.15s;

    &.visible {
      opacity: 1;
    }
  }
}

// Empty State
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  padding: 3rem 1rem;
  text-align: center;

  .empty-icon {
    color: $text-secondary;
    opacity: 0.5;
  }

  .empty-text {
    color: $text-secondary;
    font-size: 0.9rem;
  }
}

// Panel Footer
.panel-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.5rem 1rem;
  background: $dark-page;
  border-top: 1px solid $border-color;
  flex-wrap: wrap;
  gap: 0.5rem;

  .stats-breakdown {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;

    .stat-item {
      display: flex;
      align-items: center;
      gap: 0.25rem;
      font-size: 0.7rem;
      color: $text-secondary;

      &.warning {
        color: $color-delete;
      }
    }
  }

  .total-summary {
    font-size: 0.75rem;
    color: $text-muted;
  }
}

// Transitions
.slide-enter-active,
.slide-leave-active {
  transition: all 0.2s ease;
  overflow: hidden;
}

.slide-enter-from,
.slide-leave-to {
  opacity: 0;
  max-height: 0;
}

.slide-enter-to,
.slide-leave-from {
  max-height: 1000px;
}

.group-fade-enter-active {
  transition: all 0.25s ease;
}

.group-fade-leave-active {
  transition: all 0.2s ease;
  position: absolute;
  left: 0;
  right: 0;
}

.group-fade-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.group-fade-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

.group-fade-move {
  transition: transform 0.25s ease;
}
</style>
