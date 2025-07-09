<template>
  <a
    :href="$route('auth.discord-redirect')"
    class="discord-redirect-button q-mb-md rounded-borders"
  >
    <q-icon :name="ionLogoDiscord" size="30px" class="q-mr-sm" />
    <slot>Login with Discord</slot>
  </a>
</template>

<style lang="scss" scoped>
@use 'sass:color';

.discord-redirect-button {
  display: block;
  padding: 10px;
  background: #7289da;
  text-align: center;
  color: white;
  font-size: 1.1em;
  font-weight: 500;
  text-transform: uppercase;

  &:hover,
  &:focus {
    background: color.adjust(#7289da, $lightness: -3%);
  }
}
</style>

<script setup>
import { ionLogoDiscord } from '@quasar/extras/ionicons-v6'
</script>
