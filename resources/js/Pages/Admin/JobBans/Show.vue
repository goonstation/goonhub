<template>
  <div v-if="!isDeleted" class="text-right q-mb-md">
    <Link :href="route('admin.job-bans.edit', jobBan.id)">
      <q-btn color="primary" outline> Edit Job Ban </q-btn>
    </Link>
    <q-btn @click="openConfirmDelete" color="negative" class="q-ml-md" outline>
      Delete Job Ban
    </q-btn>
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
                  <Link :href="route('admin.player.show-by-ckey', jobBan.ckey)">
                    {{ jobBan.ckey }}
                  </Link>
                </td>
              </tr>
            </tbody>
          </q-markup-table>
        </q-card-section>
      </q-card>

      <q-card class="gh-card" flat>
        <div class="gh-card__header q-pa-md bordered">
          <span>Job Ban Details</span>
        </div>
        <q-card-section>
          <q-markup-table flat bordered wrap-cells>
            <tbody>
              <tr>
                <td><strong>Banning Admin</strong></td>
                <td>{{ jobBan.game_admin.name || jobBan.game_admin.ckey }}</td>
              </tr>
              <tr>
                <td><strong>Server/s</strong></td>
                <td>
                  <template v-if="jobBan.server_id">
                    {{ jobBan.game_server.name }}
                  </template>
                  <template v-else> All </template>
                </td>
              </tr>
              <tr>
                <td><strong>Job</strong></td>
                <td>{{ jobBan.banned_from_job }}</td>
              </tr>
              <tr>
                <td><strong>Reason</strong></td>
                <td>{{ jobBan.reason }}</td>
              </tr>
              <template v-if="isDeleted">
                <tr>
                  <td><strong>Removed At</strong></td>
                  <td>
                    {{ humanDeletedAt }}
                    <span class="text-caption opacity-80 q-ml-xs">
                      ({{ dayjs(jobBan.deleted_at).fromNow() }})
                    </span>
                  </td>
                </tr>
                <tr>
                  <td><strong>Removed By</strong></td>
                  <td>
                    <template v-if="jobBan.deleted_by_game_admin">
                      {{ jobBan.deleted_by_game_admin.name || jobBan.deleted_by_game_admin.ckey }}
                    </template>
                    <template v-else>Unknown</template>
                  </td>
                </tr>
              </template>
              <tr v-else>
                <td><strong>Expires At</strong></td>
                <td>
                  <template v-if="jobBan.expires_at">
                    <template v-if="isExpired"> Expired </template>
                    <template v-else>
                      {{ humanExpiresAt }}
                    </template>
                    <span class="text-caption opacity-80 q-ml-xs">
                      ({{ dayjs(jobBan.expires_at).fromNow() }})
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

    <q-dialog v-model="confirmDelete">
      <q-card flat bordered>
        <q-card-section class="row items-center no-wrap">
          <q-avatar :icon="ionInformationCircleOutline" color="negative" text-color="dark" />
          <span class="q-ml-sm"> Are you sure you want to delete this job ban? </span>
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

export default {
  layout: (h, page) =>
    h(
      DashboardLayout,
      {
        title: `Job Ban #${page.props.jobBan.id}`,
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
    jobBan: Object,
  },

  computed: {
    isExpired() {
      if (!this.jobBan.expires_at) return false
      return new Date(this.jobBan.expires_at) <= new Date()
    },

    humanExpiresAt() {
      if (!this.jobBan.expires_at) return null
      const expiresAt = new Date(this.jobBan.expires_at)
      return date.formatDate(expiresAt, 'YYYY/MM/DD HH:mm')
    },

    isDeleted() {
      return !!this.jobBan.deleted_at
    },

    humanDeletedAt() {
      if (!this.jobBan.deleted_at) return null
      const deletedAt = new Date(this.jobBan.deleted_at)
      return date.formatDate(deletedAt, 'YYYY/MM/DD HH:mm')
    },
  },

  methods: {
    openConfirmDelete() {
      this.confirmDelete = true
    },

    async deleteBan() {
      try {
        const response = await axios.delete(
          route('admin.job-bans.delete', { jobBan: this.jobBan.id })
        )
        this.$q.notify({
          message: response.data.message || 'Item successfully deleted.',
          color: 'positive',
        })
        router.reload()
      } catch {
        this.$q.notify({
          message: 'Failed to delete job ban, please try again.',
          color: 'negative',
        })
      }

      this.confirmDelete = false
    },
  },
}
</script>
