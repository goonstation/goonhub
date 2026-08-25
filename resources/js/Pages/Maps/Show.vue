<template>
  <app-head :title="map.name" />

  <div id="map" :class="{ ground: isOnGround }"></div>

  <Link href="/maps" class="go-back">
    <q-icon :name="ionArrowBack" />
    Return to Goonhub
  </Link>

  <select v-model="selectedMap" class="layer-select">
    <option v-for="(layer, id) in layers" :key="id" :value="id">
      {{ layer.name }}
    </option>
  </select>
</template>

<style lang="scss" scoped>
#map {
  background: black url('@img/space.png');
  width: 100dvw;
  height: 100dvh;
  image-rendering: pixelated;

  &.ground {
    background: #838c8c url('@img/cliffs.png');
  }
}

:deep(.leaflet-control-zoom) {
  margin-top: 50px;
}

:deep(.coord-marker) {
  display: flex;
  align-items: flex-start;
  white-space: nowrap;
}
:deep(.coord-marker div) {
  transform: translate(-50%, -100%);
  padding: 5px 8px;
  background: white;
  color: black;
  font-size: 11px;
  border-radius: 3px;
}

.go-back {
  position: absolute;
  top: 10px;
  left: 10px;
  z-index: 999;
  background: white;
  border-radius: 2px;
  padding: 3px 10px;
  line-height: 1.4;
  font-weight: 500;
  color: black;
}

.layer-select {
  position: absolute;
  top: 10px;
  right: 10px;
  z-index: 999;
  background: white;
  padding: 5px 10px 5px 5px;
  border-radius: 2px;
  border: none;
  color: black;
}
</style>

<script>
import AppHead from '@/Components/AppHead.vue'
import { router } from '@inertiajs/vue3'
import { ionArrowBack } from '@quasar/extras/ionicons-v6'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import { copyToClipboard } from 'quasar'

let leafletMap
let tileLayers = []
let coordMarker = null

export default {
  components: {
    AppHead,
  },

  props: {
    map: {
      type: Object,
      required: true,
      default: () => ({}),
    },
  },

  setup() {
    return {
      ionArrowBack,
    }
  },

  data() {
    return {
      loading: true,
      selectedMap: null,
      layers: {},
      selecting: null,
      removingCoordMarker: false,
    }
  },

  computed: {
    isOnGround() {
      return this.map.map_id === 'OSHAN' || this.map.map_id === 'NADIR'
    },

    currentLayer() {
      return this.layers[this.selectedMap]
    },
  },

  mounted() {
    this.buildMap()
    this.buildLayer(this.map)
    for (const layer of this.map.layers) {
      this.buildLayer(layer)
    }
    this.loadUrlParams()
    if (!this.selectedMap) {
      this.selectedMap = this.map.map_id
    }
  },

  methods: {
    buildMap() {
      L.CRS.myCRS = L.extend({}, L.CRS.Simple, {})
      leafletMap = L.map('map', { crs: L.CRS.myCRS, attributionControl: false })

      leafletMap.on('click', (e) => {
        this.markTile(leafletMap.mouseEventToLatLng(e.originalEvent))
      })

      leafletMap.on('moveend', () => {
        this.setUrlParams()
      })
    },

    buildLayer(layer) {
      const tileSizeWidth = (layer.tile_width / 10) * 32
      const tileSizeHeight = (layer.tile_height / 10) * 32
      const tilesPerRow = layer.tile_width / (layer.tile_width / 10)
      const tilesPerColumn = layer.tile_height / (layer.tile_height / 10)
      const mapSizeWidth = tilesPerRow * tileSizeWidth
      const mapSizeHeight = tilesPerColumn * tileSizeHeight

      const southWest = leafletMap.unproject([0, mapSizeHeight], 0)
      const northEast = leafletMap.unproject([mapSizeWidth, 0], 0)
      const bounds = L.latLngBounds(southWest, northEast)

      const mapUri = layer.map_id.toLowerCase()
      const version = layer.updated_at ? encodeURI(layer.updated_at) : ''
      const layerBuilder = L.TileLayer.extend({
        options: {
          tileSize: L.point(tileSizeWidth, tileSizeHeight),
          bounds,
          minZoom: -3,
          maxZoom: 2,
          minNativeZoom: 0,
          maxNativeZoom: 0,
          noWrap: true,
        },
        getTileUrl: (coords) => {
          const tileUri = `${mapUri}/${coords.x},${coords.y}.png?v=${version}`
          if (layer.admin_only) {
            return `/maps/private/${tileUri}`
          }
          return `/storage/maps/${tileUri}`
        },
      })
      L.tileLayer[layer.map_id] = (opts) => {
        return new layerBuilder(opts)
      }
      tileLayers[layer.map_id] = L.tileLayer[layer.map_id]()
      this.layers[layer.map_id] = { name: layer.name, bounds, mapSizeWidth, mapSizeHeight }
    },

    changeLayer(mapId) {
      this.unmarkTile()
      leafletMap.eachLayer((tileLayer) => leafletMap.removeLayer(tileLayer))
      const tileLayer = tileLayers[mapId]
      leafletMap.setMaxBounds(this.currentLayer.bounds)
      leafletMap.addLayer(tileLayer)
      leafletMap.setView(this.currentLayer.bounds.getCenter(), -2)
    },

    setUrlParams() {
      if (this.loading) return

      const zoomLevel = leafletMap.getZoom()
      const center = leafletMap.getCenter()
      const centerCoords = leafletMap.project(center, 0)
      const x = Math.floor(centerCoords.x / 32) + 1
      const y = Math.floor((this.currentLayer.mapSizeHeight - centerCoords.y) / 32) + 1

      const url = new URL(window.location.origin + window.location.pathname)
      const urlSearch = new URLSearchParams(url.search)
      urlSearch.append('x', x)
      urlSearch.append('y', y)
      urlSearch.append('zoom', zoomLevel)
      if (!this.currentLayer) urlSearch.delete('layer')
      else urlSearch.append('layer', this.selectedMap.toLowerCase())
      if (this.selecting) {
        urlSearch.append('sx', this.selecting.x)
        urlSearch.append('sy', this.selecting.y)
      }

      const newParams = decodeURI(urlSearch.toString())
      let newUrl = window.location.pathname
      if (newParams) newUrl += `?${newParams}`
      router.replace({ url: newUrl, preserveState: true })
    },

    async loadUrlParams() {
      const url = new URL(window.location.href)
      const urlSearch = new URLSearchParams(url.search)

      let layerMapId = urlSearch.get('layer')
      if (layerMapId) {
        layerMapId = layerMapId.toUpperCase()
        if (!this.layers[layerMapId]) {
          this.loading = false
          return
        }
        this.selectedMap = layerMapId
        await this.$nextTick()
      }

      const coords = { x: 0, y: 0 }
      const selectCoords = { x: 0, y: 0 }
      urlSearch.forEach((param, key) => {
        if (key === 'x') coords.x = parseInt(param)
        else if (key === 'y') coords.y = parseInt(param)
        else if (key === 'zoom') leafletMap.setZoom(parseInt(param), { animate: false })
        else if (key === 'sx') selectCoords.x = parseInt(param)
        else if (key === 'sy') selectCoords.y = parseInt(param)
      })

      if (selectCoords.x && selectCoords.y) {
        this.markCoords(selectCoords)
        this.moveToCoords(selectCoords)
      } else if (coords.x || coords.y) this.moveToCoords(coords)
      this.loading = false
    },

    moveToCoords(coords) {
      const x = (coords.x - 1) * 32 + 16
      const y = this.currentLayer.mapSizeHeight - coords.y * 32 + 16
      const latlng = leafletMap.unproject([x, y], 0)
      leafletMap.panTo(latlng, { animate: false })
    },

    markCoords(coords) {
      const x = (coords.x - 1) * 32 + 16
      const y = this.currentLayer.mapSizeHeight - coords.y * 32 + 16
      const latlng = leafletMap.unproject([x, y], 0)
      this.markTile(latlng)
    },

    markTile(latlng) {
      if (this.removingCoordMarker) {
        this.removingCoordMarker = false
        return
      }
      if (!this.currentLayer.bounds.contains(latlng)) return
      if (coordMarker) leafletMap.removeLayer(coordMarker)

      const coords = leafletMap.project(latlng, 0)
      const x = Math.floor(coords.x / 32)
      const y = Math.floor(coords.y / 32)

      const leftEdge = x * 32
      const topEdge = y * 32
      const topLeft = leafletMap.unproject([leftEdge - 1, topEdge - 1], 0)
      const bottomRight = leafletMap.unproject([leftEdge + 32, topEdge + 32], 0)
      const bounds = [
        [topLeft.lat, topLeft.lng],
        [bottomRight.lat, bottomRight.lng],
      ]
      const rect = L.rectangle(bounds, { color: '#ff7800', weight: 1 })

      const topMiddle = leafletMap.unproject([leftEdge + 16, topEdge], 0)
      const displayX = x + 1
      const displayY = Math.floor((this.currentLayer.mapSizeHeight - coords.y) / 32) + 1
      const coordText = `(x: ${displayX}, y: ${displayY})`
      const marker = L.marker(topMiddle, {
        icon: L.divIcon({
          html: `<div>${coordText}</div>`,
          className: 'coord-marker',
          iconSize: [1, 1],
        }),
      })

      this.selecting = { x: displayX, y: displayY }
      coordMarker = L.layerGroup([rect, marker])
      coordMarker.addTo(leafletMap)
      this.setUrlParams()

      marker.on('click', () => {
        copyToClipboard(coordText).then(() => {
          this.$q.notify({
            message: 'Copied text',
            color: 'positive',
          })
        })
      })

      rect.on('click', () => {
        this.removingCoordMarker = true
        this.unmarkTile()
      })
    },

    unmarkTile() {
      if (!this.selecting) return
      this.selecting = null
      if (coordMarker) leafletMap.removeLayer(coordMarker)
      this.setUrlParams()
    },
  },

  watch: {
    selectedMap(val) {
      this.changeLayer(val)
    },
  },
}
</script>
