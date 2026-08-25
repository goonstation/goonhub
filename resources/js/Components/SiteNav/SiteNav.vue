<template>
  <q-drawer show-if-above :model-value="open" side="left" :width="175" :breakpoint="breakpoint">
    <q-scroll-area class="fit" :content-style="scrollContentStyles">
      <div class="site-nav__wrap">
        <q-list class="site-nav" ref="siteNav">
          <Link class="block q-mt-lg q-mb-md logo" :href="home">
            <img src="@img/logo.png" alt="Logo" class="block q-mx-auto" width="100" height="97" />
          </Link>
          <template v-for="(menuItem, index) in items">
            <q-expansion-item
              v-if="menuItem.children"
              class="site-nav__item site-nav__item-parent"
              :key="`exp${index}`"
              :class="[
                {
                  'site-nav__item-parent--active': menuItem.expandedActive,
                },
              ]"
              :expand-icon="ionChevronDown"
              dense-toggle
              v-model="menuItem.active"
              @focusin="onNavItemEnter"
              @focusout="onNavItemLeave"
              @after-hide="onNavItemLeave"
            >
              <template #header>
                <q-item-section class="site-nav__label q-pl-sm">
                  {{ menuItem.label }}
                </q-item-section>
              </template>

              <div class="site-nav__exp-content">
                <template v-for="(childItem, childIndex) in menuItem.children">
                  <template v-if="canShowItem(childItem)">
                    <site-nav-item
                      :key="`child${childIndex}`"
                      :item="childItem"
                      @onNavItemEnter="onNavItemEnter"
                      @onNavItemLeave="onNavItemLeave"
                    />
                    <q-separator :key="'sep' + childIndex" v-if="childItem.separator" />
                  </template>
                </template>
              </div>
            </q-expansion-item>

            <template v-else-if="canShowItem(menuItem)">
              <site-nav-item
                :key="`item${index}`"
                :item="menuItem"
                @onNavItemEnter="onNavItemEnter"
                @onNavItemLeave="onNavItemLeave"
              />
              <q-separator :key="'sep' + index" v-if="menuItem.separator" />
            </template>
          </template>
          <div class="site-nav__bar" :class="[animateNavBar && 'active']" ref="navSlider"></div>
        </q-list>

        <div class="site-nav__bottom">
          <slot name="bottom" />
        </div>
      </div>
    </q-scroll-area>
  </q-drawer>
</template>

<style lang="scss">
.site-nav {
  $self: &;
  position: relative;

  &__wrap {
    flex: 1;
    display: flex;
    flex-direction: column;
    height: 100%;
    gap: 10px;
  }

  .logo {
    position: relative;

    &:before {
      content: '';
      background: url('@img/logo.png');
      background-size: contain;
      position: absolute;
      left: 50%;
      width: 100px;
      height: 96.5px;
      translate: -50%;
      scale: 1.1;
      transform-origin: center;
      filter: grayscale(1) brightness(500%);
      opacity: 0.3;
      transition: all 400ms ease-in-out;
    }

    img {
      position: relative;
      max-width: 100%;
      height: auto;
    }

    &:hover,
    &:focus {
      &:before {
        scale: 1.2;
        rotate: 360deg;
      }
    }
  }

  &__label {
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
  }

  &__exp-content {
    background: rgba(255, 255, 255, 0.075);

    #{$self}__label {
      font-size: 0.9em;
      font-weight: 500;
    }
  }

  &__bar {
    position: absolute;
    top: 0;
    left: 0;
    width: 5px;
    opacity: 1;
    background: var(--q-primary);
    border-top-right-radius: 4px;
    border-bottom-right-radius: 4px;

    &.active {
      transition: all 200ms;
    }
  }

  &__item {
    a {
      display: flex;
      align-items: center;
      flex: 1;
      color: white;
      transition: color 0.3s, background-color 0.3s;

      &:focus,
      &:hover {
        outline: 0;
        background: rgba(white, 0.15);
      }
    }

    &--active {
      a {
        color: var(--q-primary);

        &:focus,
        &:hover {
          background: color-mix(in srgb, var(--q-primary) 15%, transparent);
        }
      }
    }
  }

  &__bottom {
    margin-top: auto;
  }
}
</style>

<script>
import { router } from '@inertiajs/vue3'
import { ionChevronDown } from '@quasar/extras/ionicons-v6'
import SiteNavItem from './SiteNavItem.vue'

export default {
  emits: ['update:open'],

  components: {
    SiteNavItem,
  },

  props: {
    home: {
      type: String,
      default: route('web.home'),
    },
    items: Array,
    open: Boolean,
  },

  setup() {
    return {
      ionChevronDown,
    }
  },

  data() {
    return {
      breakpoint: 600,
      pageLoading: false,
      navSlider: null,
      animateNavBar: false,
      scrollContentStyles: {
        display: 'flex',
        'flex-direction': 'column',
      },
    }
  },

  created() {
    router.on('start', () => {
      this.pageLoading = true
    })
    router.on('finish', () => {
      this.pageLoading = false
    })
  },

  mounted() {
    if (window.innerWidth <= this.breakpoint) {
      this.$emit('update:open', false)
    }

    this.onNavItemLeave()
    this.animateNavBar = true

    const navItems = Array.from(
      this.$refs.siteNav.$el.querySelectorAll('.site-nav__item:not(.site-nav__item-parent)')
    )
    const parentNavItems = Array.from(
      this.$refs.siteNav.$el.querySelectorAll('.site-nav__item-parent .q-item')
    )
    navItems.concat(parentNavItems).forEach((item) => {
      item.addEventListener('mouseenter', this.onNavItemEnter)
      item.addEventListener('mouseleave', this.onNavItemLeave)
    })
  },

  methods: {
    moveNavSliderTo(el) {
      if (!el) return
      const eleRect = el.getBoundingClientRect()
      const targetRect = this.$refs.siteNav.$el.getBoundingClientRect()
      const top = eleRect.top - targetRect.top

      this.$refs.navSlider.style.top = `${top}px`
      this.$refs.navSlider.style.height = `${el.offsetHeight}px`
    },

    onNavItemEnter(e) {
      this.moveNavSliderTo(e.target)
    },

    onNavItemLeave() {
      if (this.pageLoading) return
      let activeItem = document.querySelector('.site-nav__item--active')
      if (activeItem && !activeItem.offsetParent) {
        activeItem = document.querySelector('.site-nav__item-parent--active .q-item')
      }
      this.moveNavSliderTo(activeItem)
    },

    onNavItemClick(e, href) {
      this.moveNavSliderTo(e.target.closest('a'))
      router.visit(href)
    },

    checkItems(menuItems) {
      let activeItem
      menuItems.forEach((item) => {
        item.active = false
        item.expandedActive = false
        if (this.isActive(item)) {
          activeItem = item
        }
        if (item.children) {
          const activeChildItem = this.checkItems(item.children)
          if (activeChildItem) {
            item.expandedActive = true
            activeChildItem.active = true
          }
        }
      })
      return activeItem
    },

    getCurrentUrl() {
      let currentUrl = window.location.href
      if (window.location.pathname === '/') {
        currentUrl = currentUrl.slice(0, -1)
      }
      return currentUrl
    },

    isActive(menuItem) {
      const currentUrl = this.getCurrentUrl()
      if (menuItem.match && Array.isArray(menuItem.match)) {
        for (const matchItem of menuItem.match) {
          if (currentUrl.startsWith(matchItem)) return true
        }
      } else {
        const href = menuItem.match ? menuItem.match : menuItem.href
        return currentUrl.startsWith(href)
      }
    },

    canShowItem(menuItem) {
      if (this.$page.props.auth.user?.is_admin) return true // admins can access everything
      if (typeof menuItem.canShow === 'function') {
        return menuItem.canShow()
      }
      return true
    },
  },

  watch: {
    '$page.url': {
      immediate: true,
      handler() {
        const activeItem = this.checkItems(this.items)
        if (activeItem) activeItem.active = true

        setTimeout(() => {
          this.onNavItemLeave()
        }, 1)
      },
    },
  },
}
</script>
