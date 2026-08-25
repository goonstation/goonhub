export default function useHelpers() {
  return {
    serverIdToFriendlyName: (val, short = false) => {
      if (val === 'dev') {
        return short ? 'Goon Dev' : 'Goonstation Development'
      } else if (val === 'main1') {
        return short ? 'Goon 1' : 'Goonstation 1 Classic: Heisenbee'
      } else if (val === 'main2') {
        return short ? 'Goon 2' : 'Goonstation 2 Classic: Bombini'
      } else if (val === 'main3') {
        return short ? 'Goon 3 RP' : 'Goonstation 3 Roleplay: Morty'
      } else if (val === 'main4') {
        return short ? 'Goon 4 RP' : 'Goonstation 4 Roleplay: Sylvester'
      } else if (val === 'main5') {
        return short ? 'Goon 5 Event' : 'Goonstation 5 Event: Rocko'
      } else if (val === 'streamer1') {
        return short ? 'Tomato 1' : 'Tomato 1'
      } else if (val === 'streamer2') {
        return short ? 'Tomato 2' : 'Tomato 2'
      } else if (val === 'streamer3') {
        return short ? 'Tomato 3' : 'Tomato 3'
      } else if (val === 'streamer4') {
        return short ? 'Tomato 4' : 'Tomato 4'
      } else {
        return short ? 'Goon' : 'Goonstation'
      }
    },

    isNumeric: (val) => {
      if (typeof val != 'string') return false
      return (
        !isNaN(val) && // use type coercion to parse the _entirety_ of the string (`parseFloat` alone does not do this)...
        !isNaN(parseFloat(val)) // ...and ensure strings of whitespace fail
      )
    },

    getChartColorForServer(serverId) {
      if (serverId === 'main1') {
        return '#ffd125'
      } else if (serverId === 'main2') {
        return '#ffd125'
      } else if (serverId === 'main3') {
        return '#24c024'
      } else if (serverId === 'main4') {
        return '#e33434'
      } else if (serverId === 'main5') {
        return '#5490ff'
      } else {
        return '#ffd125'
      }
    },

    publicUrl(path) {
      if (!path || typeof path !== 'string') return ''
      if (path[0] !== '/') path = `/${path}`
      return `/storage${path}`
    },
  }
}
