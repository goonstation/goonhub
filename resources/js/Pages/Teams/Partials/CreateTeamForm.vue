<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
})

const createTeam = () => {
  form.post(route('web.teams.store'), {
    errorBag: 'createTeam',
    preserveScroll: true,
  })
}
</script>

<template>
  <div class="row q-col-gutter-sm">
    <div class="col-12 col-md-4">
      <h3 class="q-mb-sm text-h6">Team Details</h3>
      <div class="q-mb-md">Create a new team to collaborate with others on projects.</div>
    </div>
    <div class="col-12 col-md-8">
      <q-card flat class="q-pa-sm">
        <q-card-section>
          <q-form @submit="createTeam">
            <div class="q-mb-md text-weight-medium text-body1">Team Owner</div>

            <div class="flex items-center q-mt-sm q-mb-md">
              <img
                style="width: 50px; border-radius: 50%"
                :src="$page.props.auth.user.profile_photo_url"
                :alt="$page.props.auth.user.name"
              />

              <div class="q-ml-md">
                <div>{{ $page.props.auth.user.name }}</div>
                <div class="text-grey-6 text-sm">
                  {{ $page.props.auth.user.email }}
                </div>
              </div>
            </div>

            <q-input
              v-model="form.name"
              class="q-mb-sm"
              type="text"
              label="Team Name"
              filled
              required
              hide-bottom-space
              autofocus
              :error="!!form.errors.name"
              :error-message="form.errors.name"
            />

            <div class="flex items-center q-mt-md">
              <q-space />
              <q-btn
                label="Create"
                type="submit"
                color="primary"
                text-color="black"
                :loading="form.processing"
              />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>
