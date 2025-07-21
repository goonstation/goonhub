<template>
  <q-form @submit="updateProfileInformation">
    <div>
      <div class="row gap-xs-lg">
        <div
          v-if="$page.props.jetstream.managesProfilePhotos"
          class="col-12 col-sm-auto text-center"
        >
          <div class="inline-block relative">
            <input
              ref="photoInput"
              @change="updatePhotoPreview"
              type="file"
              class="hidden"
              accept="image/png, image/jpeg"
            />
            <user-avatar :user="user" size="5rem" font-size="1.5rem" color="grey-10">
              <div
                v-if="photoPreview"
                :style="'background-image: url(\'' + photoPreview + '\'); background-size: cover'"
                class="absolute fit"
              ></div>
            </user-avatar>
            <q-btn
              @click="selectNewPhoto"
              :icon="ionPencil"
              class="absolute-bottom-right"
              style="margin: 0 -5px -5px 0"
              color="primary"
              text-color="dark"
              size="xs"
              round
            />
            <q-btn
              v-if="user.profile_photo_path"
              @click="deletePhoto"
              :icon="ionTrash"
              class="absolute-top-right"
              style="margin: -5px -5px 0 0"
              color="negative"
              text-color="dark"
              size="xs"
              round
            />
          </div>
        </div>

        <div class="col-12 col-sm">
          <q-input
            v-model="form.name"
            class="q-mb-sm"
            type="text"
            label="Name"
            filled
            required
            hide-bottom-space
            autocomplete="name"
            :error="!!form.errors.name"
            :error-message="form.errors.name"
          />

          <q-input
            v-model="form.email"
            class="q-mb-sm"
            type="email"
            label="Email"
            filled
            hide-bottom-space
            :error="!!form.errors.email"
            :error-message="form.errors.email"
          />

          <template v-if="$page.props.jetstream.hasEmailVerification">
            <Alert
              v-if="$page.props.sent_verification_email || verificationLinkSent"
              type="positive"
            >
              <span class="text-weight-medium">
                A new verification link has been sent to your email address.
              </span>
            </Alert>
            <Alert v-else-if="user.email_verified_at === null && !user.emailless" type="warning">
              <div class="flex items-center justify-between gap-xs-sm">
                <span>Your email address is unverified.</span>
                <q-btn
                  @click="sendEmailVerification"
                  label="Send verification email"
                  color="warning"
                  class="text-sm q-px-md q-py-xs"
                  type="button"
                  outline
                />
              </div>
            </Alert>
          </template>
        </div>
      </div>

      <q-banner
        v-if="form.errors.photo"
        class="text-center text-negative bg-opacity-20"
        rounded
        dense
      >
        {{ form.errors.photo }}
      </q-banner>
    </div>

    <div class="flex items-center gap-xs-sm q-mt-sm">
      <q-space />
      <div class="flex items-center gap-xs-sm q-ml-auto">
        <ActionMessage :on="form.recentlySuccessful">Saved</ActionMessage>
        <q-btn label="Save" type="submit" color="primary" :loading="form.processing" flat />
      </div>
    </div>
  </q-form>
</template>

<script>
import ActionMessage from '@/Components/ActionMessage.vue'
import Alert from '@/Components/Alert.vue'
import UserAvatar from '@/Components/UserAvatar.vue'
import { router, useForm } from '@inertiajs/vue3'
import { ionPencil, ionTrash } from '@quasar/extras/ionicons-v6'

export default {
  components: {
    ActionMessage,
    UserAvatar,
    Alert,
  },

  props: {
    user: Object,
  },

  setup() {
    return {
      ionPencil,
      ionTrash,
    }
  },

  data() {
    return {
      form: useForm({
        _method: 'PUT',
        name: this.user.name,
        email: this.user.emailless ? null : this.user.email,
        photo: null,
      }),
      verificationLinkSent: null,
      photoPreview: null,
    }
  },

  methods: {
    updateProfileInformation() {
      if (this.$refs.photoInput) {
        this.form.photo = this.$refs.photoInput.files[0]
      }

      this.form.post(route('user-profile-information.update'), {
        errorBag: 'updateProfileInformation',
        preserveScroll: true,
        onSuccess: () => this.clearPhotoFileInput(),
      })
    },

    sendEmailVerification() {
      router.post(route('verification.send'))
      this.verificationLinkSent = true
    },

    selectNewPhoto() {
      this.$refs.photoInput.click()
    },

    updatePhotoPreview() {
      const photo = this.$refs.photoInput.files[0]

      if (!photo) return

      const reader = new FileReader()

      reader.onload = (e) => {
        this.photoPreview = e.target.result
      }

      reader.readAsDataURL(photo)
    },

    deletePhoto() {
      router.delete(route('current-user-photo.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
          this.photoPreview = null
          this.clearPhotoFileInput()
        },
      })
    },

    clearPhotoFileInput() {
      if (this.$refs.photoInput?.value) {
        this.$refs.photoInput.value = null
      }
    },
  },
}
</script>
