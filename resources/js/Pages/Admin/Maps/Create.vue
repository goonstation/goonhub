<template>
  <maps-form
    :fields="fields"
    :submit-route="route('admin.maps.store')"
    :maps="maps"
    success-message="Map added"
  />
</template>

<script>
import MapsForm from '@/Components/Forms/MapsForm.vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  components: {
    MapsForm
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Add Map' }, () => page),

  props: {
    maps: Object,
  },

  data() {
    return {
      fields: {
        map_id: null,
        name: null,
        active: false,
        admin_only: false,
        is_layer: false,
        tile_width: 300,
        tile_height: 300,
        layers: [],
        base_maps: [],
      },
    }
  }
}
</script>
