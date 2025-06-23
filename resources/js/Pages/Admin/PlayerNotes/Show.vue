<template>
  <div class="text-right q-mb-md">
    <Link :href="route('admin.notes.edit', note.id)">
      <q-btn color="primary" outline> Edit Note </q-btn>
    </Link>
    <q-btn @click="openConfirmDelete" color="negative" class="q-ml-md" outline>
      Delete Note
    </q-btn>
  </div>

  <div class="row q-col-gutter-md">
    <div class="col-12 col-md-6">
      <q-card class="gh-card q-mb-md" flat>
        <div class="gh-card__header q-pa-md bordered">
          <span>Note Details</span>
        </div>
        <q-card-section>
          <q-markup-table flat bordered wrap-cells>
            <tbody>
              <tr>
                <td><strong>Player</strong></td>
                <td>
                  <Link :href="route('admin.player.show-by-ckey', note.player?.ckey || note.ckey)">
                    {{ note.player?.ckey || note.ckey }}
                  </Link>
                </td>
              </tr>
              <tr>
                <td><strong>Admin</strong></td>
                <td>{{ note.game_admin?.name || note.game_admin?.ckey || '(None)' }}</td>
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
import { router } from '@inertiajs/vue3'
import { ionInformationCircleOutline } from '@quasar/extras/ionicons-v6'
import axios from 'axios'
import dayjs from 'dayjs'

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
        router.visit(route('admin.notes.index'))
      } catch {
        this.$q.notify({
          message: 'Failed to delete note, please try again.',
          color: 'negative',
        })
      }

      this.confirmDelete = false
    },
  }
}
</script>
