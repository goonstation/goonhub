<template>
  <tr class="log-type-row" :class="`log-type-${log.type}`">
    <td>{{ createdAt }}</td>
    <td>
      <q-chip class="log-type-label" :class="`log-type-label-${log.type}`" square dense>
        <div class="content">{{ log.type }}</div>
      </q-chip>
    </td>
    <td class="log-message" style="white-space: pre-wrap">
      <div v-html="message"></div>
    </td>
  </tr>
</template>

<style lang="scss" scoped>
.q-chip {
  width: 100%;
  font-size: 12px;

  .content {
    width: 100%;
    text-align: center;
  }
}

.log-type-row td {
  padding-top: 0;
  padding-bottom: 0;
}

:deep(a) {
  display: inline-block;
  white-space: normal;
}

:deep(a #innerContent) {
  color: black;
}

:deep(.name) {
  font-weight: bold;
  color: #ffd125;
}

:deep(.log-player) {
  display: inline-block;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid grey;
  border-radius: 3px;
  padding: 0 6px;
}

:deep(.log-loc) {
  color: #ffd125;
}

:deep(.log-loc-name) {
  font-weight: bold;
}

:deep(.log-damage) {
  white-space: nowrap;
}

:deep(.log-damage span) {
  display: inline-block;
  padding: 0 4px;
}

:deep(.dmg-brain) {
  background: grey;
}
:deep(.dmg-oxy) {
  background: powderblue;
  color: black;
}
:deep(.dmg-tox) {
  background: lightgreen;
  color: black;
}
:deep(.dmg-burn) {
  background: burlywood;
  color: black;
}
:deep(.dmg-brute) {
  background: deeppink;
}

:deep(.log-message-highlight) {
  background: yellow;
  color: black;
}

:deep(.traitor-marker) {
  display: inline-block;
  border-radius: 3px;
  padding: 0 5px;
  background: red;
}
</style>

<script>
import { date } from 'quasar'

export default {
  props: {
    log: Object,
    relativeTimestamps: Boolean,
    roundStartedAt: String,
    searchTerms: Array,
  },

  computed: {
    createdAt() {
      let dateToFormat
      if (this.relativeTimestamps) {
        const msDiff = new Date(this.log.created_at) - new Date(this.roundStartedAt)
        dateToFormat = date.addToDate(new Date('2000-01-1'), { milliseconds: msDiff })
      } else {
        dateToFormat = new Date(this.log.created_at)
      }
      return date.formatDate(dateToFormat, 'HH:mm:ss.SSS')
    },

    message() {
      let message = this.log.source || ''
      if (message) message += ' '
      message += this.log.message || ''

      const poptsRegex =
        /<a href='.*?\?src=%admin_ref%;action=adminplayeropts;targetckey=.*?' title='Player Options'>(.*?) \((.*?)\)<\/a>/gi
      message = message.replaceAll(
        poptsRegex,
        '<span class="log-player">$1 (<a href="/admin/players/$2" target="_blank">$2</a>)</span>'
      )

      const locRegex =
        /\(<a href='.*?\?src=%admin_ref%;action=jumptocoords;.*?title='Jump to Coords'>(.*?)<\/a> in (.*?)(\)?)\)/gi
      message = message.replaceAll(
        locRegex,
        '<span class="log-loc"><span class="log-loc-name">$2$3</span> ($1)</span>'
      )

      const privMsgRegex = /<a href="byond:\/\/\?action=priv_msg&target=.*?">(.*?)<\/a>/g
      message = message.replaceAll(privMsgRegex, '$1')

      const damageRegex = /\(<b>Damage:<\/b> <i>(.*?), (.*?), (.*?), (.*?), (.*?)<\/i>\)/g
      message = message.replaceAll(
        damageRegex,
        `<span class="log-damage">
        <span class="dmg-brain" title="Brain">$1</span>
        <span class="dmg-oxy" title="Oxygen">$2</span>
        <span class="dmg-tox" title="Toxin">$3</span>
        <span class="dmg-burn" title="Burn">$4</span>
        <span class="dmg-brute" title="Burn">$5</span>
      </span>`
      )

      const traitorRegex = /\[<span class='traitorTag'>T<\/span>\]/g
      message = message.replaceAll(traitorRegex, '<span class="traitor-marker">T</span>')

      if (this.searchTerms.length) {
        const searchRegex = new RegExp(
          this.searchTerms.map((term) => `(${this.escapeRegExp(term)})`).join('|'),
          'gim'
        )

        const renderer = document.createElement('div')
        renderer.innerHTML = message
        this.iterateTextNodes(renderer, (node) => {
          const newContent = node.nodeValue.replace(
            searchRegex,
            '<span class="log-message-highlight">$&</span>'
          )
          node.replaceWith(document.createRange().createContextualFragment(newContent))
        })

        return renderer.innerHTML
      }

      return message
    },
  },

  methods: {
    escapeRegExp(string) {
      return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
    },

    iterateTextNodes(container, callback) {
      Array.from(container.childNodes).forEach((node) => {
        if (node.nodeType === node.TEXT_NODE) {
          callback(node)
        } else {
          this.iterateTextNodes(node, callback)
        }
      })
    },
  },
}
</script>
