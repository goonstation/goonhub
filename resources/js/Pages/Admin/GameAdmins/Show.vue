<template>
  hi
</template>

<script>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  layout: (h, page) => h(DashboardLayout, { title: 'Admin' }, () => page),

  props: {
    gameAdmin: Object
  }
}
</script>
