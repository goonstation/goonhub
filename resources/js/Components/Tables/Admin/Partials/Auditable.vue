<template>
  <Link v-if="target" :href="target">
    {{ label }}
  </Link>
  <template v-else>{{ label }}</template>
</template>

<script>
export default {
  props: {
    id: {
      type: Number,
      required: true,
    },
    model: {
      type: String,
      required: true,
    },
    event: {
      type: String,
      required: true,
    },
  },

  computed: {
    type() {
      return this.model.replaceAll('\\', '').replace('AppModels', '')
    },

    label() {
      return this.type.replace(/([A-Z])/g, ' $1').trim() + ` #${this.id}`
    },

    target() {
      if (this.event === 'deleted') return ''

      switch (this.type) {
        case 'Ban':
          return this.$route('admin.bans.show', this.id)
        case 'GameAdmin':
          return this.$route('admin.game-admins.show', this.id)
        case 'GameBuildSetting':
          return this.$route('admin.builds.settings.edit', this.id)
        case 'GameBuildTestMerge':
          return this.$route('admin.builds.test-merges.edit', this.id)
        case 'GameRound':
          return this.$route('admin.rounds.show', this.id)
        case 'JobBan':
          return this.$route('admin.job-bans.show', this.id)
        case 'Map':
          return this.$route('admin.maps.edit', this.id)
        case 'Medal':
          return this.$route('admin.medals.edit', this.id)
        case 'Player':
          return this.$route('admin.players.show', this.id)
        case 'PlayerNote':
          return this.$route('admin.notes.show', this.id)
        case 'Redirect':
          return this.$route('admin.redirects.edit', this.id)
      }

      return ''
    },
  },
}
</script>
