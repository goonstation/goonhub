<template>
  <div v-if="!isDeleted" class="text-right q-mb-md">
    <Link :href="route('admin.bans.edit', ban.id)">
      <q-btn color="primary" outline> Edit Ban </q-btn>
    </Link>
    <q-btn @click="openConfirmDelete" color="negative" class="q-ml-md" outline> Delete Ban </q-btn>
  </div>

  <div class="row q-col-gutter-md">
    <div class="col-12 col-md-6">
      <q-card class="gh-card q-mb-md" flat>
        <div class="gh-card__header q-pa-md bordered">
          <span>Status</span>
        </div>
        <q-card-section class="q-pa-sm">
          <q-chip v-if="!isExpired && !isDeleted" color="positive" square> Active Ban </q-chip>
          <q-chip v-else color="negative" square> Inactive Ban </q-chip>
          <q-chip v-if="ban.requires_appeal" color="info" square> Requires Appeal </q-chip>
        </q-card-section>
      </q-card>

      <q-card class="gh-card q-mb-md" flat>
        <div class="gh-card__header q-pa-md bordered">
          <span>Player Details</span>
        </div>
        <q-card-section>
          <q-markup-table flat bordered>
            <tbody>
              <tr>
                <td><strong>Ckey</strong></td>
                <td>
                  <Link v-if="ban.original_ban_detail.ckey" :href="route('admin.player.show-by-ckey', ban.original_ban_detail.ckey)">
                    {{ ban.original_ban_detail.ckey }}
                  </Link>
                </td>
              </tr>
              <tr>
                <td><strong>Computer ID</strong></td>
                <td>{{ ban.original_ban_detail.comp_id }}</td>
              </tr>
              <tr>
                <td><strong>IP</strong></td>
                <td>{{ ban.original_ban_detail.ip }}</td>
              </tr>
            </tbody>
          </q-markup-table>
        </q-card-section>
      </q-card>

      <q-card class="gh-card" flat>
        <div class="gh-card__header q-pa-md bordered">
          <span>Ban Details</span>
        </div>
        <q-card-section>
          <q-markup-table flat bordered wrap-cells>
            <tbody>
              <tr>
                <td><strong>Banning Admin</strong></td>
                <td>{{ ban.game_admin.alias || ban.game_admin.player?.ckey }}</td>
              </tr>
              <tr>
                <td><strong>Server/s</strong></td>
                <td>
                  <template v-if="ban.server_id">
                    {{ ban.game_server.name }}
                  </template>
                  <template v-else> All </template>
                </td>
              </tr>
              <tr>
                <td><strong>Reason</strong></td>
                <td>{{ ban.reason }}</td>
              </tr>
              <tr>
                <td><strong>Added At</strong></td>
                <td>
                  {{ humanAddedAt }}
                  <span class="text-caption opacity-80 q-ml-xs">
                    ({{ dayjs(ban.created_at).fromNow() }})
                  </span>
                </td>
              </tr>
              <template v-if="isDeleted">
                <tr>
                  <td><strong>Removed At</strong></td>
                  <td>
                    {{ humanDeletedAt }}
                    <span class="text-caption opacity-80 q-ml-xs">
                      ({{ dayjs(ban.deleted_at).fromNow() }})
                    </span>
                  </td>
                </tr>
                <tr>
                  <td><strong>Removed By</strong></td>
                  <td>
                    <template v-if="ban.deleted_by_game_admin">
                      {{ ban.deleted_by_game_admin.alias || ban.deleted_by_game_admin.player?.ckey }}
                    </template>
                    <template v-else>Unknown</template>
                  </td>
                </tr>
              </template>
              <tr v-else>
                <td><strong>Expires At</strong></td>
                <td>
                  <template v-if="ban.expires_at">
                    <template v-if="isExpired"> Expired </template>
                    <template v-else>
                      {{ humanExpiresAt }}
                    </template>
                    <span class="text-caption opacity-80 q-ml-xs">
                      ({{ dayjs(ban.expires_at).fromNow() }})
                    </span>
                  </template>
                  <template v-else> Permanent </template>
                </td>
              </tr>
            </tbody>
          </q-markup-table>
        </q-card-section>
      </q-card>
    </div>

    <div class="col-12 col-md-6">
      <ban-details :model-value="ban" />
    </div>

    <q-dialog v-model="confirmDelete">
      <q-card flat bordered>
        <q-card-section class="row items-center no-wrap">
          <q-avatar :icon="ionInformationCircleOutline" color="negative" text-color="dark" />
          <span class="q-ml-sm"> Are you sure you want to delete this ban? </span>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn flat label="Confirm" color="negative" @click="deleteBan" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<style lang="scss" scoped>
tbody {
  td:first-child {
    width: 150px;
  }
}
</style>

<script>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { router } from '@inertiajs/vue3'
import { ionInformationCircleOutline } from '@quasar/extras/ionicons-v6'
import axios from 'axios'
import dayjs from 'dayjs'
import { date } from 'quasar'
import BanDetails from './Partials/BanDetails.vue'

export default {
  components: {
    BanDetails,
  },

  layout: (h, page) =>
    h(
      DashboardLayout,
      {
        title: `Ban #${page.props.ban.id}`,
      },
      () => page
    ),

  setup() {
    return {
      dayjs,
      router,
      ionInformationCircleOutline,
    }
  },

  data() {
    return {
      confirmDelete: false,
    }
  },

  props: {
    ban: Object,
  },

  computed: {
    isExpired() {
      if (!this.ban.expires_at) return false
      return new Date(this.ban.expires_at) <= new Date()
    },

    humanAddedAt() {
      const addedAt = new Date(this.ban.created_at)
      return date.formatDate(addedAt, 'YYYY/MM/DD HH:mm')
    },

    humanExpiresAt() {
      if (!this.ban.expires_at) return null
      const expiresAt = new Date(this.ban.expires_at)
      return date.formatDate(expiresAt, 'YYYY/MM/DD HH:mm')
    },

    isDeleted() {
      return !!this.ban.deleted_at
    },

    humanDeletedAt() {
      if (!this.ban.deleted_at) return null
      const deletedAt = new Date(this.ban.deleted_at)
      return date.formatDate(deletedAt, 'YYYY/MM/DD HH:mm')
    },
  },

  methods: {
    openConfirmDelete() {
      this.confirmDelete = true
    },

    async deleteBan() {
      try {
        const response = await axios.delete(route('admin.bans.delete', { ban: this.ban.id }))
        this.$q.notify({
          message: response.data.message || 'Item successfully deleted.',
          color: 'positive',
        })
        router.reload()
      } catch {
        this.$q.notify({
          message: 'Failed to delete ban, please try again.',
          color: 'negative',
        })
      }

      this.confirmDelete = false
    },
  },
}
</script>
