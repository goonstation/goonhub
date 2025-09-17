<template>
  <div class="q-pa-md flex items-center gap-xs-md">
    <h6 class="q-my-none">Notes</h6>
    <q-space />
    <add-player-note-dialog
      :player-ckey="playerCkey"
      @success="onNoteAdded"
      size="sm"
      class="text-weight-bold"
    />
  </div>

  <q-table :rows="modelValue" :columns="columns" flat>
    <template v-slot:body-cell-id="props">
      <q-td :props="props">
        <Link :href="$route('admin.notes.show', props.row.id)">
          {{ props.row.id }}
        </Link>
      </q-td>
    </template>
    <template v-slot:body-cell-admin_ckey="props">
      <q-td :props="props">
        <Link
          v-if="props.row.game_admin"
          :href="$route('admin.game-admins.show', props.row.game_admin.id)"
        >
          {{ props.row.game_admin.alias || props.row.game_admin.player?.ckey }}
        </Link>
      </q-td>
    </template>
  </q-table>
</template>

<script>
import AddPlayerNoteDialog from '@/Components/AddPlayerNoteDialog.vue'

export default {
  emits: ['update:modelValue'],

  components: {
    AddPlayerNoteDialog,
  },

  props: {
    modelValue: Object,
    playerCkey: String,
  },

  data() {
    return {
      columns: [
        { name: 'id', field: 'id', label: 'ID', sortable: true },
        {
          name: 'note',
          field: 'note',
          label: 'Note',
          align: 'left',
          style: 'white-space: normal; min-width: 300px;',
        },
        {
          name: 'server_id',
          label: 'Server',
          field: 'server_id',
          sortable: true,
          format: (val, row) => {
            if (!val) return 'All'
            return row.game_server.short_name
          },
        },
        {
          name: 'admin_ckey',
          label: 'Admin',
          sortable: true,
        },
        {
          name: 'created_at',
          label: 'Created',
          field: 'created_at',
          sortable: true,
          format: this.$formats.date,
        },
      ],
    }
  },

  methods: {
    onNoteAdded(note) {
      this.$emit('update:modelValue', [note, ...this.modelValue])
    },
  },
}
</script>
