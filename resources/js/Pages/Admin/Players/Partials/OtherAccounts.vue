<template>
  <q-banner class="bg-grey-10 q-ma-md q-pa-md">
    <template v-slot:avatar>
      <q-icon :name="ionInformationCircleOutline" color="primary" size="md" class="q-mt-xs" />
    </template>
    These are accounts that have connected with the same IP Address or Computer ID as this player.
    Please note that this doesn't always mean they are played by the same person. This information
    is provided for investigation purposes only.
  </q-banner>

  <q-table :rows="accounts" :columns="accountsColumns" flat>
    <template v-slot:body-cell-id="props">
      <q-td :props="props">
        <Link :href="route('admin.players.show', props.row.id)">
          {{ props.row.id }}
        </Link>
      </q-td>
    </template>
    <template v-slot:body-cell-ckey="props">
      <q-td :props="props">
        <player-avatar :player="props.row" class="q-mr-sm" size="md" />
        {{ props.row.ckey }}
      </q-td>
    </template>
    <template v-slot:body-cell-last_seen="props">
      <q-td :props="props">
        <template v-if="props.row.latest_connection">
          {{ upperFirst(dayjs(props.row.latest_connection.created_at).fromNow()) }}
        </template>
        <template v-else>Never</template>
      </q-td>
    </template>
    <template v-slot:body-cell-match_ip="props">
      <q-td :props="props">
        <q-icon
          :name="props.row._matchedOnIp === true ? ionCheckmark : ionClose"
          :color="props.row._matchedOnIp === true ? 'positive' : 'negative'"
          size="xs"
        />
      </q-td>
    </template>
    <template v-slot:body-cell-match_comp_id="props">
      <q-td :props="props">
        <q-icon
          :name="props.row._matchedOnCompId === true ? ionCheckmark : ionClose"
          :color="props.row._matchedOnCompId === true ? 'positive' : 'negative'"
          size="xs"
        />
      </q-td>
    </template>
  </q-table>
</template>

<script>
import PlayerAvatar from '@/Components/PlayerAvatar.vue'
import { ionCheckmark, ionClose, ionInformationCircleOutline } from '@quasar/extras/ionicons-v6'
import dayjs from 'dayjs'
import { upperFirst } from 'lodash'

export default {
  components: {
    PlayerAvatar,
  },

  props: {
    accounts: Object,
  },

  setup() {
    return {
      dayjs,
      upperFirst,
      ionCheckmark,
      ionClose,
      ionInformationCircleOutline,
    }
  },

  data() {
    return {
      accountsColumns: [
        {
          name: 'id',
          field: 'id',
          label: 'ID',
          headerClasses: 'q-table--col-auto-width',
          sortable: true,
        },
        {
          name: 'ckey',
          label: 'Ckey',
          field: 'ckey',
          sortable: true,
          align: 'left',
        },
        {
          name: 'last_seen',
          label: 'Last Seen',
          filterable: false,
        },
        {
          name: 'match_ip',
          label: 'Same IP',
          field: '_matchedOnIp',
          headerClasses: 'q-table--col-auto-width',
        },
        {
          name: 'match_comp_id',
          label: 'Same Comp ID',
          field: '_matchedOnCompId',
          headerClasses: 'q-table--col-auto-width',
        },
      ],
    }
  },
}
</script>
