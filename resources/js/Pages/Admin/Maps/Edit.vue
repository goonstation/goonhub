<template>
  <maps-form
    state="edit"
    :fields="fields"
    :submit-route="route('admin.maps.update', { map: map.id })"
    :maps="maps"
    submit-method="put"
    success-message="Map updated"
  />
</template>

<script>
import MapsForm from '@/Components/Forms/MapsForm.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    MapsForm,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Edit Map' }, () => page),

  props: {
    map: Object,
    maps: Object,
    baseMaps: Array,
  },

  data() {
    return {
      fields: {
        map_id: this.map.map_id,
        name: this.map.name,
        active: this.map.active,
        admin_only: this.map.admin_only,
        is_layer: this.map.is_layer,
        tile_width: this.map.tile_width,
        tile_height: this.map.tile_height,
        layers: this.map.layers ? this.map.layers.map((layer) => layer.id) : [],
        base_maps: this.baseMaps,
      },
    }
  },
}
</script>
