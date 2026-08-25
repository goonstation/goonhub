<template>
  <div class="row q-col-gutter-md">
    <div class="col-12 col-md-6">
      <q-card class="gh-card q-mb-md" flat>
        <div class="gh-card__header q-pa-md bordered">
          <span>Note Details</span>
          <q-space />
          <div class="flex items-center gap-xs-sm">
            <Link
              :href="$route('admin.notes.edit', note.id)"
              :as="QBtn"
              color="primary"
              size="12px"
              outline
            >
              <q-icon :name="ionPencil" class="q-mr-sm" />
              Edit
            </Link>
            <q-btn @click="openConfirmDelete" color="negative" size="12px" outline>
              <q-icon :name="ionTrash" class="q-mr-sm" />
              Delete
            </q-btn>
          </div>
        </div>
        <q-card-section>
          <q-markup-table flat bordered wrap-cells>
            <tbody>
              <tr>
                <td><strong>Player</strong></td>
                <td>
                  <Link
                    :href="$route('admin.players.show-by-ckey', note.player?.ckey || note.ckey)"
                  >
                    {{ note.player?.ckey || note.ckey }}
                  </Link>
                </td>
              </tr>
              <tr>
                <td><strong>Admin</strong></td>
                <td>{{ note.game_admin?.alias || note.game_admin?.player?.ckey || '(None)' }}</td>
              </tr>
              <tr>
                <td><strong>Server</strong></td>
                <td>{{ note.game_server?.short_name || 'All' }}</td>
              </tr>
              <tr>
                <td><strong>Note</strong></td>
                <td>{{ note.note }}</td>
              </tr>
              <tr>
                <td><strong>Created</strong></td>
                <td>{{ $formats.dateWithTime(note.created_at) }}</td>
              </tr>
              <tr>
                <td><strong>Updated</strong></td>
                <td>{{ $formats.dateWithTime(note.updated_at) }}</td>
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
          <span class="q-ml-sm"> Are you sure you want to delete this note? </span>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn flat label="Confirm" color="negative" @click="deleteNote" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<style lang="scss" scoped>
tbody {
  td:first-child {
    width: 100px;
  }
}
</style>

<script>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { ionInformationCircleOutline, ionPencil, ionTrash } from '@quasar/extras/ionicons-v7'
import axios from 'axios'
import dayjs from 'dayjs'
import { QBtn } from 'quasar'

export default {
  layout: (h, page) =>
    h(
      DashboardLayout,
      {
        title: `Note #${page.props.note.id}`,
      },
      () => page
    ),

  setup() {
    return {
      dayjs,
      ionInformationCircleOutline,
      ionPencil,
      ionTrash,
      QBtn,
    }
  },

  data() {
    return {
      confirmDelete: false,
    }
  },

  props: {
    note: Object,
  },

  methods: {
    openConfirmDelete() {
      this.confirmDelete = true
    },

    async deleteNote() {
      try {
        const response = await axios.delete(route('admin.notes.delete', { note: this.note.id }))
        this.$q.notify({
          message: response.data.message || 'Item successfully deleted.',
          color: 'positive',
        })
        this.$inertia.visit(route('admin.notes.index'))
      } catch {
        this.$q.notify({
          message: 'Failed to delete note, please try again.',
          color: 'negative',
        })
      }

      this.confirmDelete = false
    },
  },
}
</script>
