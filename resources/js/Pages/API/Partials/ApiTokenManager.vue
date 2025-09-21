<script setup>
import ActionMessage from '@/Components/ActionMessage.vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const page = usePage()

const props = defineProps({
  tokens: Array,
  availablePermissions: Array,
  defaultPermissions: Array,
})

const isAdmin = computed(() => page.props.auth.user.is_admin)

const createApiTokenForm = useForm({
  name: '',
  permissions: props.defaultPermissions,
  for_game_server: false,
})

const updateApiTokenForm = useForm({
  permissions: [],
  for_game_server: false,
})

const deleteApiTokenForm = useForm({})

const displayingToken = ref(false)
const managingPermissionsFor = ref(null)
const apiTokenBeingDeleted = ref(null)

const createApiToken = () => {
  createApiTokenForm.post(route('api-tokens.store'), {
    preserveScroll: true,
    onSuccess: () => {
      displayingToken.value = true
      createApiTokenForm.reset()
    },
  })
}

const manageApiTokenPermissions = (token) => {
  updateApiTokenForm.permissions = token.abilities
  updateApiTokenForm.for_game_server = token.for_game_server
  managingPermissionsFor.value = token
}

const updateApiToken = () => {
  updateApiTokenForm.put(route('api-tokens.update', managingPermissionsFor.value), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => (managingPermissionsFor.value = null),
  })
}

const confirmApiTokenDeletion = (token) => {
  apiTokenBeingDeleted.value = token
}

const deleteApiToken = () => {
  deleteApiTokenForm.delete(route('api-tokens.destroy', apiTokenBeingDeleted.value), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => (apiTokenBeingDeleted.value = null),
  })
}
</script>

<template>
  <div>
    <!-- Generate API Token -->
    <div class="row q-col-gutter-sm">
      <div class="col-12 col-md-4">
        <h3 class="q-mb-sm text-h6">Create API Token</h3>
        <div class="q-mb-md">
          API tokens allow third-party services to authenticate with our application on your behalf.
        </div>
      </div>
      <div class="col-12 col-md-8">
        <q-card flat>
          <q-card-section>
            <q-form @submit="createApiToken">
              <q-input
                v-model="createApiTokenForm.name"
                class="q-mb-md"
                type="text"
                label="Name"
                filled
                required
                hide-bottom-space
                autofocus
                :error="!!createApiTokenForm.errors.name"
                :error-message="createApiTokenForm.errors.name"
              />

              <div v-if="availablePermissions.length > 0" class="q-mb-md">
                <div class="text-body1 q-mb-md">Permissions</div>

                <div v-for="permission in availablePermissions" :key="permission">
                  <label class="flex items-center">
                    <q-checkbox
                      v-model="createApiTokenForm.permissions"
                      :val="permission"
                      :label="permission"
                    />
                  </label>
                </div>
              </div>

              <div v-if="isAdmin">
                <q-checkbox
                  v-model="createApiTokenForm.for_game_server"
                  label="For Game Server"
                />
              </div>

              <div class="flex items-center q-mt-md">
                <q-space />
                <ActionMessage :on="createApiTokenForm.recentlySuccessful" class="q-mr-sm">
                  Created.
                </ActionMessage>
                <q-btn
                  label="Create"
                  type="submit"
                  color="primary"
                  text-color="black"
                  :loading="createApiTokenForm.processing"
                />
              </div>
            </q-form>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <div v-if="tokens.length > 0" class="q-mt-lg">
      <!-- Manage API Tokens -->
      <div class="row q-col-gutter-sm">
        <div class="col-12 col-md-4">
          <h3 class="q-mb-sm text-h6">Manage API Token</h3>
          <div class="q-mb-md">
            You may delete any of your existing tokens if they are no longer needed.
          </div>
        </div>
        <div class="col-12 col-md-8">
          <q-card flat>
            <q-card-section>
              <div class="column gap-xs-sm">
                <div
                  v-for="token in tokens"
                  :key="token.id"
                  class="flex items-center justify-between"
                >
                  <div class="break-all">
                    {{ token.name }}
                  </div>

                  <div class="flex items-center q-ml-sm">
                    <div v-if="token.last_used_ago" class="text-sm text-grey-6">
                      Last used {{ token.last_used_ago }}
                    </div>

                    <q-btn
                      v-if="availablePermissions.length > 0"
                      type="button"
                      label="Permissions"
                      flat
                      color="grey-5"
                      class="q-ml-md"
                      @click="manageApiTokenPermissions(token)"
                    />

                    <q-btn
                      type="button"
                      label="Delete"
                      flat
                      color="negative"
                      class="q-ml-xs"
                      @click="confirmApiTokenDeletion(token)"
                    />
                  </div>
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <!-- Token Value Modal -->
    <q-dialog v-model="displayingToken" @hide="displayingToken = false">
      <q-card flat>
        <q-card-section>
          <div class="text-h6">API Token</div>
          <div>Please copy your new API token. For your security, it won't be shown again.</div>
        </q-card-section>

        <q-card-section>
          <div
            v-if="$page.props.jetstream.flash.token"
            class="bg-grey-1 q-px-md q-py-sm rounded-borders text-sm text-dark break-all"
          >
          {{ $page.props.jetstream.flash.token }}
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Close" @click="displayingToken = false" color="grey" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- API Token Permissions Modal -->
    <q-dialog :model-value="managingPermissionsFor != null" @hide="managingPermissionsFor = null">
      <q-card flat>
        <q-card-section>
          <div class="text-h6">API Token Permissions</div>
        </q-card-section>

        <q-card-section>
          <div v-for="permission in availablePermissions" :key="permission">
            <label class="flex items-center">
              <q-checkbox
                v-model="updateApiTokenForm.permissions"
                :val="permission"
                :label="permission"
              />
            </label>
          </div>

          <div v-if="isAdmin" class="q-mt-md">
            <q-checkbox
              v-model="updateApiTokenForm.for_game_server"
              label="For Game Server"
            />
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" @click="managingPermissionsFor = null" color="grey" />
          <q-btn
            label="Save"
            flat
            type="button"
            color="primary"
            class="q-ml-md"
            :loading="updateApiTokenForm.processing"
            @click="updateApiToken"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Delete Token Confirmation Modal -->
    <q-dialog :model-value="apiTokenBeingDeleted != null" @hide="apiTokenBeingDeleted = null">
      <q-card flat>
        <q-card-section>
          <div class="text-h6">Delete API Token</div>
          <div>
            Are you sure you would like to delete this API token?
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" @click="apiTokenBeingDeleted = null" color="grey" />
          <q-btn
            label="Delete"
            flat
            type="button"
            color="negative"
            class="q-ml-md"
            :loading="deleteApiTokenForm.processing"
            @click="deleteApiToken"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>
