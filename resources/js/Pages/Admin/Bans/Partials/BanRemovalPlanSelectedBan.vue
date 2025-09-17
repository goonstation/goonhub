<template>
  <div>
    <div class="flex items-center q-mb-md">
      <div class="text-lg q-mr-xs">Ban #{{ ban.id }}</div>
      <div class="q-ml-auto">
        <a :href="route('admin.bans.show', ban.id)" target="_blank">
          Open in new tab
        </a>
      </div>
    </div>

    <q-markup-table class="q-mb-md" flat bordered>
      <thead>
        <tr>
          <th colspan="2" class="text-left">
            <strong>Player Details</strong>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><strong>Ckey</strong></td>
          <td>{{ ban.plan.item.original_ban_detail.ckey }}</td>
        </tr>
        <tr>
          <td><strong>Computer ID</strong></td>
          <td>{{ ban.plan.item.original_ban_detail.comp_id }}</td>
        </tr>
        <tr>
          <td><strong>IP</strong></td>
          <td>{{ ban.plan.item.original_ban_detail.ip }}</td>
        </tr>
      </tbody>
    </q-markup-table>

    <q-markup-table class="q-mb-md" flat bordered wrap-cells>
      <thead>
        <tr>
          <th colspan="2" class="text-left">
            <strong>Ban Details</strong>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><strong>Banning Admin</strong></td>
          <td>{{ ban.plan.item.game_admin.alias || ban.plan.item.game_admin.player?.ckey }}</td>
        </tr>
        <tr>
          <td><strong>Server/s</strong></td>
          <td>
            <template v-if="ban.plan.item.server_id">
              {{ ban.plan.item.game_server.name }}
            </template>
            <template v-else> All </template>
          </td>
        </tr>
        <tr>
          <td><strong>Reason</strong></td>
          <td>{{ ban.plan.item.reason }}</td>
        </tr>
        <tr>
          <td><strong>Added At</strong></td>
          <td>
            {{ humanAddedAt }}
            <span class="text-caption opacity-80 q-ml-xs">
              ({{ dayjs(ban.plan.item.created_at).fromNow() }})
            </span>
          </td>
        </tr>
        <tr>
          <td><strong>Expires At</strong></td>
          <td>
            <template v-if="ban.plan.item.expires_at">
              <template v-if="isExpired"> Expired </template>
              <template v-else>
                {{ humanExpiresAt }}
              </template>
              <span class="text-caption opacity-80 q-ml-xs">
                ({{ dayjs(ban.plan.item.expires_at).fromNow() }})
              </span>
            </template>
            <template v-else> Permanent </template>
          </td>
        </tr>
      </tbody>
    </q-markup-table>

    <q-banner v-if="ban.plan.delete" class="bg-negative q-py-sm q-px-md" dense>
      <template v-slot:avatar>
        <q-icon :name="ionWarningOutline" color="white" size="md" />
      </template>
      <div>This ban will be deleted.</div>
    </q-banner>
  </div>
</template>

<script>
import { ionWarningOutline } from '@quasar/extras/ionicons-v6'
import dayjs from 'dayjs'
import { date } from 'quasar'

export default {
  props: {
    ban: Object,
  },

  setup() {
    return {
      dayjs,
      ionWarningOutline
    }
  },

  computed: {
    humanAddedAt() {
      const addedAt = new Date(this.ban.plan.item.created_at)
      return date.formatDate(addedAt, 'YYYY/MM/DD HH:mm')
    },

    humanExpiresAt() {
      if (!this.ban.plan.item.expires_at) return null
      const expiresAt = new Date(this.ban.plan.item.expires_at)
      return date.formatDate(expiresAt, 'YYYY/MM/DD HH:mm')
    },
  },
}
</script>
