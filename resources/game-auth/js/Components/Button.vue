<template>
  <component
    v-bind="{ ...$attrs, ...(href ? { href } : {}) }"
    :is="href ? 'a' : 'button'"
    :class="{ 'button--icon': !!icon, 'button--inline': inline }"
    class="button"
  >
    <span class="button__inner">
      <Icon v-if="icon" :name="icon" />
      <span><slot /></span>
    </span>
  </component>
</template>

<style lang="scss" scoped>
@use '@css/mixins' as *;

.button,
.button__inner {
  position: relative;
  display: block;
  width: 100%;
  @include slant;
}

.button {
  --bg-color: v-bind(bgColor);
  --bg-line-color: v-bind(bgLineColor);
  --border-color: v-bind(borderColor);

  background: none;
  color: white;
  border: 0;
  text-decoration: none;
  font-size: 1rem;
  font-weight: 600;
  letter-spacing: 1px;
  text-transform: uppercase;
  text-decoration: none;
  text-align: center;
  cursor: pointer;
  outline: none;
  overflow: hidden;

  // "Border" styles
  padding: 2px;
  background: var(--border-color);

  &--inline {
    display: inline-block;
    width: auto;
  }

  &:hover,
  &:focus {
    .button__inner::before {
      width: 100%;
    }
  }
}

.button__inner {
  --icon-width: 3.5em;

  padding: 1em 1.5em;
  background: repeating-linear-gradient(
    0deg,
    var(--bg-color),
    var(--bg-color) 2px,
    var(--bg-line-color) 2px,
    var(--bg-line-color) 4px
  );
  transition: all 0.2s;

  &::before {
    content: '';
    position: absolute;
    z-index: -1;
    top: 0;
    left: 0;
    bottom: 0;
    width: 0;
    background-color: var(--border-color);
    transition: width 0.2s ease-in-out;
    will-change: width;
  }

  .button--icon & {
    padding-left: calc(1.5em + var(--icon-width));

    &::before {
      left: var(--icon-width);
    }
  }

  .button--icon:not(.button--inline) & {
    padding-right: calc(1.5em + var(--icon-width));
  }

  > .icon {
    position: absolute;
    top: 0;
    left: 0;
    width: var(--icon-width);
    height: 100%;
    padding: 0.35em 0.83em;
    background: var(--border-color);
  }
}
</style>

<script setup>
import Icon from '@/Components/Icon.vue'

defineProps({
  href: {
    type: String,
    required: false,
  },
  icon: {
    type: String,
    required: false,
  },
  inline: {
    type: Boolean,
    required: false,
    default: false,
  },
  bgColor: {
    type: String,
    required: false,
    default: 'var(--color-primary-dark)',
  },
  bgLineColor: {
    type: String,
    required: false,
    default: 'var(--color-primary-darker)',
  },
  borderColor: {
    type: String,
    required: false,
    default: 'var(--color-primary)',
  },
})
</script>
