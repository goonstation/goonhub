<template>
  <div>Server is {{ serverName }}</div>
</template>

<script>
import axios from 'axios'

export default {
  props: {
    filter: null,
    filterOption: null,
  },

  data() {
    return {
      serverName: null,
    }
  },

  watch: {
    filter: {
      immediate: true,
      handler(val) {
        if (this.filterOption) {
          this.serverName = this.filterOption.name
        } else {
          this.getServer(val)
        }
      },
    },
  },

  methods: {
    async getServer(serverId) {
      const { data } = await axios.get(this.$route('web.game-servers.index'), {
        params: {
          filters: {
            server: serverId,
          },
        },
      })
      this.serverName = data?.data?.[0]?.name
    },
  },
}
</script>
