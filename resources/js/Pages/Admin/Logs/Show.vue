<template>
  <div class="flex-grow flex column">
    <round-summary class="q-mb-sm" :round="round" dense />

    <log-viewer
      :logs="allLogs"
      :log-types="logTypes"
      :loading="loading"
      :round-started-at="roundStartedAt"
      :show-relative-timestamps="true"
      :highlighted-log-id="highlightedLogId"
      empty-message="No logs found for this round."
    />
  </div>
</template>

<script>
import RoundSummary from '@/Components/RoundSummary.vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import axios from 'axios'
import LogViewer from './Partials/LogViewer.vue'

export default {
  components: {
    RoundSummary,
    LogViewer,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Logs' }, () => page),

  props: {
    round: Object,
  },

  data() {
    return {
      allLogs: [],
      loading: true,
      logTypes: [],
      highlightedLogId: null,
    }
  },

  computed: {
    roundStartedAt() {
      if (this.allLogs.length > 0) {
        return this.allLogs[0].created_at
      }
      return null
    },
  },

  created() {
    this.loadHighlightedLogFromUrl()
    this.getLogs()
  },

  methods: {
    loadHighlightedLogFromUrl() {
      const url = new URL(window.location.href)
      const logParam = url.searchParams.get('log')
      if (logParam) {
        this.highlightedLogId = parseInt(logParam)
      }
    },

    async getLogs() {
      try {
        const response = await axios.get(route('admin.logs.get-logs', { gameRound: this.round.id }))
        this.allLogs = response.data

        const logTypes = [...new Set(this.allLogs.map((log) => log.type))].sort()
        this.logTypes = logTypes.map((logType) => {
          return {
            label: logType,
            value: logType,
          }
        })

        // Append ckey to player element inner texts
        const poptsRegex =
          /(<a href='.*?\?src=%admin_ref%;action=adminplayeropts;targetckey=(.*?)' title='Player Options'>)(.*?)(<\/a>)/gi
        for (const logIdx in this.allLogs) {
          const logEntry = this.allLogs[logIdx]
          if (logEntry.source)
            logEntry.source = logEntry.source.replaceAll(poptsRegex, '$1$3 ($2)$4')
          if (logEntry.message)
            logEntry.message = logEntry.message.replaceAll(poptsRegex, '$1$3 ($2)$4')
          this.allLogs[logIdx] = logEntry
        }
      } catch (e) {
        console.log(e)
      }

      this.loading = false
    },
  },
}
</script>
