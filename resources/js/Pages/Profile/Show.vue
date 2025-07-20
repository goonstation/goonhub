<template>
  <div class="q-mx-auto q-mt-md" style="width: 100%; max-width: 800px">
    <Alert v-if="page.props.flash.error" type="negative" class="q-mb-md">
      <span class="text-weight-medium">{{ page.props.flash.error }}</span>
    </Alert>

    <Alert v-if="page.props.flash.success" :opacity="20" type="positive" class="q-mb-md">
      <span class="text-weight-medium">{{ page.props.flash.success }}</span>
    </Alert>

    <q-card v-if="$page.props.jetstream.canUpdateProfileInformation" class="gh-card q-mb-md" flat>
      <div class="gh-card__header q-pa-md bordered">
        <span>Profile Information</span>
      </div>
      <q-card-section>
        <UpdateProfileInformationForm :user="$page.props.auth.user" />
      </q-card-section>
    </q-card>

    <q-card class="gh-card q-mb-md" flat>
      <div class="gh-card__header q-pa-md bordered">
        <span>Connections</span>
      </div>
      <q-card-section>
        <LinkByond class="q-mb-md" />
        <LinkDiscord />
      </q-card-section>
    </q-card>

    <q-card v-if="$page.props.jetstream.canUpdatePassword" class="gh-card q-mb-md" flat>
      <div class="gh-card__header q-pa-md bordered">
        <span>Update Password</span>
      </div>
      <q-card-section>
        <UpdatePasswordForm />
      </q-card-section>
    </q-card>

    <q-card
      v-if="$page.props.jetstream.canManageTwoFactorAuthentication && isGameAdmin"
      class="gh-card"
      flat
    >
      <div class="gh-card__header q-pa-md bordered">
        <span>Two Factor Authentication</span>
      </div>
      <q-card-section>
        <TwoFactorAuthenticationForm :requires-confirmation="confirmsTwoFactorAuthentication" />
      </q-card-section>
    </q-card>

    <!-- <q-card class="gh-card q-mb-md" flat>
      <div class="gh-card__header q-pa-md bordered">
        <span>Browser Sessions</span>
      </div>
      <q-card-section>
        <LogoutOtherBrowserSessionsForm :sessions="sessions" />
      </q-card-section>
    </q-card> -->

    <!-- <q-card v-if="$page.props.jetstream.hasAccountDeletionFeatures" class="gh-card" flat>
      <div class="gh-card__header q-pa-md bordered">
        <span>Delete Account</span>
      </div>
      <q-card-section>
        <DeleteUserForm />
      </q-card-section>
    </q-card> -->
  </div>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
// import DeleteUserForm from './Partials/DeleteUserForm.vue'
// import LogoutOtherBrowserSessionsForm from './Partials/LogoutOtherBrowserSessionsForm.vue'
import Alert from '@/Components/Alert.vue'
import LinkByond from './Partials/LinkByond.vue'
import LinkDiscord from './Partials/LinkDiscord.vue'
import TwoFactorAuthenticationForm from './Partials/TwoFactorAuthenticationForm.vue'
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue'
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue'

defineOptions({
  layout: (h, page) => h(DashboardLayout, { title: 'Profile' }, () => page),
})

defineProps({
  confirmsTwoFactorAuthentication: Boolean,
  sessions: Array,
})

const page = usePage()

const isGameAdmin = computed(() => {
  return !!page.props.auth.user.game_admin_id || !!page.props.auth.user.is_admin
})
</script>
