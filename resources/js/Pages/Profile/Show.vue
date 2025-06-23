<template>
  <div>
    <div v-if="$page.props.jetstream.canUpdateProfileInformation">
      <div class="row q-col-gutter-sm">
        <div class="col-12 col-md-4">
          <h3 class="q-mb-sm text-h6">Profile Information</h3>
          <div class="q-mb-md">Update your account's profile information and email address.</div>
        </div>
        <div class="col-12 col-md-8">
          <UpdateProfileInformationForm :user="$page.props.auth.user" />
        </div>
      </div>
    </div>

    <div v-if="$page.props.jetstream.canUpdatePassword" class="q-mt-lg">
      <div class="row q-col-gutter-sm">
        <div class="col-12 col-md-4">
          <h3 class="q-mb-sm text-h6">Update Password</h3>
          <div class="q-mb-md">
            Ensure your account is using a long, random password to stay secure.
          </div>
        </div>
        <div class="col-12 col-md-8">
          <UpdatePasswordForm />
        </div>
      </div>
    </div>

    <div
      v-if="$page.props.jetstream.canManageTwoFactorAuthentication && isGameAdmin"
      class="q-mt-lg"
    >
      <div class="row q-col-gutter-sm">
        <div class="col-12 col-md-4">
          <h3 class="q-mb-sm text-h6">Two Factor Authentication</h3>
          <div class="q-mb-md">
            Add additional security to your account using two factor authentication.
          </div>
        </div>
        <div class="col-12 col-md-8">
          <TwoFactorAuthenticationForm :requires-confirmation="confirmsTwoFactorAuthentication" />
        </div>
      </div>
    </div>

    <div class="row q-col-gutter-sm q-mt-lg">
      <div class="col-12 col-md-4">
        <h3 class="q-mb-sm text-h6">Browser Sessions</h3>
        <div class="q-mb-md">
          Manage and log out your active sessions on other browsers and devices.
        </div>
      </div>
      <div class="col-12 col-md-8">
        <LogoutOtherBrowserSessionsForm :sessions="sessions" />
      </div>
    </div>

    <template v-if="$page.props.jetstream.hasAccountDeletionFeatures">
      <div class="row q-col-gutter-sm q-mt-lg">
        <div class="col-12 col-md-4">
          <h3 class="q-mb-sm text-h6">Delete Account</h3>
          <div class="q-mb-md">
            Once your account is deleted, all of its resources and data will be permanently deleted.
            Before deleting your account, please download any data or information that you wish to
            retain.
          </div>
        </div>
        <div class="col-12 col-md-8">
          <DeleteUserForm />
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DeleteUserForm from './Partials/DeleteUserForm.vue'
import LogoutOtherBrowserSessionsForm from './Partials/LogoutOtherBrowserSessionsForm.vue'
import TwoFactorAuthenticationForm from './Partials/TwoFactorAuthenticationForm.vue'
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue'
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue'

export default {
  components: {
    DeleteUserForm,
    LogoutOtherBrowserSessionsForm,
    TwoFactorAuthenticationForm,
    UpdatePasswordForm,
    UpdateProfileInformationForm,
  },

  props: {
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
  },

  layout: (h, page) => h(DashboardLayout, { title: 'Profile' }, () => page),

  computed: {
    isGameAdmin() {
      return !!this.$page.props.auth.user.game_admin_id || !!this.$page.props.auth.user.is_admin
    },
  },
}
</script>
