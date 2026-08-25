import { usePage } from '@inertiajs/vue3'
import { reactive, watch } from 'vue'
import * as AllPermissions from '../Access/Permissions'
import Roles from '../Access/Roles'

class Auth {
  #page
  permissions = reactive({})
  roles = reactive({})

  constructor() {
    if (Auth.instance) return Auth.instance
    Auth.instance = this

    Object.assign(this.permissions, Object.fromEntries(
      Object.values(AllPermissions).flatMap((p) => {
          return Object.values(p).map((n) => [n, false])
        })
      )
    )

    Object.assign(this.roles, Object.fromEntries(
      Object.values(Roles).map((r) => [r, false])
    ))

    this.#page = usePage()
    watch(
      [
        () => !!this.user?.id,
        () => this.user?.permissions,
        () => this.user?.roles,
      ],
      ([hasUser, permissions, roles], [oldHasUser]) => {
        if (!hasUser) {
          if (oldHasUser) this.reset()
          return
        }

        if (Array.isArray(permissions)) {
          Object.keys(this.permissions).forEach((p) => {
            this.permissions[p] = permissions.includes(p)
          })
        }

        if (Array.isArray(roles)) {
          Object.keys(this.roles).forEach((r) => {
            this.roles[r] = roles.includes(r)
          })
        }
      }
    )
  }

  reset() {
    Object.keys(this.permissions).forEach((p) => {
      this.permissions[p] = false
    })

    Object.keys(this.roles).forEach((r) => {
      this.roles[r] = false
    })
  }

  get user() {
    return this.#page.props?.auth?.user
  }

  isLoggedIn() {
    return !!this.user?.id
  }

  can(permission) {
    if (Array.isArray(permission)) {
      if (permission.length === 0) return true
      return permission.some((p) => this.permissions[p])
    }
    if (!permission) return true
    return this.permissions[permission]
  }

  cannot(permission) {
    return !this.can(permission)
  }

  hasRole(role) {
    if (Array.isArray(role)) {
      if (role.length === 0) return true
      return role.some((r) => this.roles[r])
    }
    if (!role) return true
    return this.roles[role]
  }

  missingRole(role) {
    return !this.hasRole(role)
  }

  isSuperAdmin() {
    return this.hasRole(Roles.SUPER_ADMIN)
  }

  canVisit(route) {
    if (!Ziggy.routes[route]) return false
    const permissions = Ziggy.routes[route]?.permissions || []
    const roles = Ziggy.routes[route]?.roles || []
    if (permissions.length === 0 && roles.length === 0) return true
    return this.can(permissions) && this.hasRole(roles)
  }
}

export default function useAuth() {
  return new Auth()
}
