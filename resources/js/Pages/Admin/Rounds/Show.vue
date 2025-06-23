<template>
  <round-summary class="q-mb-sm" :round="round" dense />

  <pre>
    list of players that joined this round
      how long they played for (if possible?)
      antag status
      roundstart job
      link to round logs with ckey filter applied (i.e. show me logs for "this player").
    link to round logs
    same event explorer as on public round page
  </pre>
</template>

<script>
import RoundSummary from '@/Components/RoundSummary.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    RoundSummary,
  },

  layout: (h, page) =>
    h(
      DashboardLayout,
      {
        title: `Round #${page.props.round.id}`,
      },
      () => page
    ),

  props: {
    round: Object
  }
}
</script>
