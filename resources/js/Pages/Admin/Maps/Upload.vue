<template>
  <div class="row">
    <div class="col-12 col-md-6">
      <q-form @submit="submit">
        <q-banner class="bg-grey-10 items-center q-py-md q-mb-md">
          <template v-slot:avatar>
            <q-icon :name="ionInformationCircleOutline" color="primary" size="md" />
          </template>
          This tool takes a ZIP of images produced by the "Map-World" in-game verb, and builds a new
          map for the web viewer, as well as new thumbnails. Please be aware that due to processing
          times, it may take up to a minute for the map to update publicly.
        </q-banner>

        <q-card class="gh-card q-mb-md" flat>
          <q-card-section>
            <q-select
              v-model="form.map"
              :options="maps"
              :error="!!form.errors.map"
              :error-message="form.errors.map"
              label="Map"
              option-value="map_id"
              option-label="name"
              filled
              lazy-rules
              emit-value
              map-options
              required
              hide-bottom-space
              class="q-mb-md"
            />

            <q-file
              v-model="file"
              :error="!!fileValidationError"
              filled
              bottom-slots
              label="Map Images"
              accept=".zip"
              class="q-mb-md"
              required
            >
              <template v-slot:prepend>
                <q-icon :name="ionCloudUpload" @click.stop.prevent />
              </template>
              <template v-slot:append>
                <q-icon :name="ionClose" @click.stop.prevent="file = null" class="cursor-pointer" />
              </template>

              <template v-slot:hint>
                A ZIP file containing map images produced by the ingame "Map-World" verb
              </template>
              <template v-slot:error>
                {{ fileValidationError }}
              </template>
            </q-file>
          </q-card-section>
        </q-card>

        <q-linear-progress
          v-if="fileUploading"
          :value="progress"
          color="primary"
          class="q-mb-md"
          instant-feedback
        />

        <q-banner v-if="error" class="bg-negative q-mb-md">
          {{ error }}
        </q-banner>

        <div class="flex">
          <q-space />
          <q-btn
            label="Submit"
            type="button"
            color="primary"
            text-color="black"
            @click="confirm = true"
            :disabled="!form.map"
            :loading="fileUploading || form.processing"
          />
        </div>

        <q-dialog v-model="confirm">
          <q-card flat>
            <q-card-section class="row items-center no-wrap">
              <q-avatar :icon="ionInformationCircleOutline" color="primary" text-color="dark" />
              <span class="q-ml-sm">
                This will replace the "{{ selectedMapName }}" map with your images. This cannot be
                undone, so please be sure you have selected the correct map.
              </span>
            </q-card-section>

            <q-card-actions align="right">
              <q-btn flat label="Cancel" v-close-popup />
              <q-btn flat label="Submit" color="primary" @click="submit" />
            </q-card-actions>
          </q-card>
        </q-dialog>
      </q-form>
    </div>
  </div>
</template>

<script>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { ionClose, ionCloudUpload, ionInformationCircleOutline } from '@quasar/extras/ionicons-v6'
import Resumable from 'resumablejs'

export default {
  layout: (h, page) => h(DashboardLayout, { title: 'Upload Map' }, () => page),

  setup() {
    return {
      ionInformationCircleOutline,
      ionCloudUpload,
      ionClose,
    }
  },

  props: {
    maps: Array,
  },

  data() {
    return {
      form: useForm({
        map: null,
        fileName: null,
        filePath: null,
      }),
      file: null,
      fileValidationError: null,
      progress: 0,
      fileUploading: false,
      error: null,
      confirm: false,
    }
  },

  computed: {
    selectedMapName() {
      const map = this.maps.find((map) => map.map_id === this.form.map)
      if (!map) return ''
      return map.name
    }
  },

  methods: {
    uploadFile() {
      const resumable = new Resumable({
        target: route('admin.maps.upload-file'),
        query: { _token: this.$page.props.csrf_token },
        chunkSize: 1 * 1024 * 1024, // 1MB
        simultaneousUploads: 3,
        testChunks: false,
        throttleProgressCallbacks: 1,
      })

      let uploadingFile = null
      resumable.on('fileAdded', (file) => {
        uploadingFile = file
        resumable.upload()
      })
      resumable.on('fileProgress', (file) => {
        this.progress = file.progress()
      })
      resumable.on('fileSuccess', (file, message) => {
        message = JSON.parse(message)
        this.form.fileName = message.name
        this.form.filePath = message.path
        this.submitForm()
      })
      resumable.on('complete', () => {
        resumable.removeFile(uploadingFile)
        this.file = null
        this.fileUploading = false
      })
      resumable.on('error', (message) => {
        message = JSON.parse(message)
        this.error = message.error
      })

      this.fileUploading = true
      resumable.addFile(this.file)
    },

    submitForm() {
      this.form.post(route('admin.maps.upload-update'), {
        onSuccess: () => {
          this.form.reset()
          this.$q.notify({
            message: 'Map successfully uploaded',
            color: 'positive',
          })
        },
        onError: (errors) => {
          const error = errors.error || 'An error occurred, please try again.'
          this.$q.notify({
            message: error,
            color: 'negative',
          })
        },
      })
    },

    submit() {
      this.confirm = false
      this.fileValidationError = null
      if (this.fileUploading) return
      if (!this.form.map) {
        this.form.setError('map', 'Please select a map')
        return
      }
      if (!this.file) {
        this.fileValidationError = 'Please upload a file'
        return
      }
      this.error = null
      this.uploadFile()
    },
  },
}
</script>
