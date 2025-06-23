<template>
  <div class="row">
    <div class="col-12 col-md-6">
      <q-form @submit="submit">
        <q-card class="gh-card q-mb-md" flat>
          <div class="gh-card__header q-pa-md bordered">
            <span>Player Details</span>
          </div>
          <q-card-section>
            <q-input
              v-model="form.ckey"
              label="Ckey"
              filled
              lazy-rules
              dense
              required
              :error="!!form.errors.ckey"
              :error-message="form.errors.ckey"
            />
          </q-card-section>
        </q-card>

        <q-card class="gh-card q-mb-md" flat>
          <div class="gh-card__header q-pa-md bordered">
            <span>Ban Details</span>
          </div>
          <q-card-section>
            <base-select
              v-model="form.server_id"
              label="Server"
              load-route="/game-servers"
              option-value="server_id"
              option-label="name"
              filled
              lazy-rules
              dense
              emit-value
              map-options
              :filters="{ with_invisible: true }"
              :error="!!form.errors.server_id"
              :error-message="form.errors.server_id"
              :default-items="[{ name: 'All', server_id: 'all' }]"
            />
            <job-ban-jobs
              v-model="form.job"
              label="Job"
              filled
              lazy-rules
              dense
              clearable
              :error="!!form.errors.job"
              :error-message="form.errors.job"
            />
            <q-input
              v-model="form.reason"
              type="textarea"
              label="Reason"
              filled
              lazy-rules
              required
              dense
              :error="!!form.errors.reason"
              :error-message="form.errors.reason"
            />
            <div>
              <template v-if="state === 'edit' && !editingDuration">
                <q-banner class="bg-grey-10 items-center">
                  <template v-slot:avatar>
                    <q-icon :name="ionInformationCircleOutline" color="primary" size="md" />
                  </template>
                  <template v-if="isExpired"> This ban expired on {{ humanExpiresAt }}. </template>
                  <template v-else>
                    This ban expires {{ getExpiresAtFromDuration(form.duration) }}. Would you like
                    to set a new duration? This will clear the existing duration.
                  </template>
                  <template v-if="!isExpired" v-slot:action>
                    <div class="q-mt-sm">
                      <q-btn @click="editingDuration = true" flat> Edit Duration </q-btn>
                    </div>
                  </template>
                </q-banner>
              </template>

              <template v-if="state === 'create' || editingDuration">
                <div class="q-mb-sm">Duration</div>
                <q-option-group
                  v-model="form.duration"
                  @update:model-value="onDurationChange"
                  :options="durationOptions"
                  inline
                />
                <div>or</div>
                <div class="q-mt-sm q-ml-sm">
                  <span class="q-mr-md">Ban Until</span>
                  <q-btn
                    :label="durationTimeUntil ? durationTimeUntil : 'Select a date'"
                    :icon="ionCalendarClearOutline"
                    color="grey-10"
                  >
                    <q-popup-proxy transition-show="scale" transition-hide="scale" class="row">
                      <div>
                        <div class="row">
                          <q-date
                            v-model="durationTimeUntil"
                            @update:model-value="onDurationTimeUntilChange($event, form)"
                            mask="YYYY/MM/DD HH:mm"
                            text-color="black"
                          />
                          <q-time
                            v-model="durationTimeUntil"
                            @update:model-value="onDurationTimeUntilChange($event, form)"
                            mask="YYYY/MM/DD HH:mm"
                            text-color="black"
                          />
                        </div>
                        <div class="row items-center justify-end">
                          <q-btn v-close-popup label="Close" color="primary" flat />
                        </div>
                      </div>
                    </q-popup-proxy>
                  </q-btn>
                </div>
                <div>or</div>
                <div class="row items-center q-gutter-md">
                  <span class="col-auto q-ml-lg">Ban After</span>
                  <div class="col">
                    <div class="row q-col-gutter-sm">
                      <q-input
                        v-model="durationTimeAfterYears"
                        @update:model-value="onDurationTimeAfterChange(form)"
                        class="col-6 col-sm-3"
                        type="number"
                        label="Years"
                        filled
                        dense
                      />
                      <q-input
                        v-model="durationTimeAfterDays"
                        @update:model-value="onDurationTimeAfterChange(form)"
                        class="col-6 col-sm-3"
                        type="number"
                        label="Days"
                        filled
                        dense
                      />
                      <q-input
                        v-model="durationTimeAfterHours"
                        @update:model-value="onDurationTimeAfterChange(form)"
                        class="col-6 col-sm-3"
                        type="number"
                        label="Hours"
                        filled
                        dense
                      />
                      <q-input
                        v-model="durationTimeAfterMinutes"
                        @update:model-value="onDurationTimeAfterChange(form)"
                        class="col-6 col-sm-3"
                        type="number"
                        label="Minutes"
                        filled
                        dense
                      />
                    </div>
                  </div>
                </div>
                <q-banner class="bg-grey-10 items-center q-mt-md">
                  <template v-slot:avatar>
                    <q-icon
                      :name="ionInformationCircleOutline"
                      class="q-mt-xs"
                      color="primary"
                      size="md"
                    />
                  </template>
                  This ban will expire {{ getExpiresAtFromDuration(form.duration) }}
                </q-banner>
              </template>
            </div>
          </q-card-section>
        </q-card>

        <q-banner v-if="isDisabled" class="bg-grey-10 items-center q-mb-md">
          <template v-slot:avatar>
            <q-icon :name="ionAlertCircleOutline" class="q-mt-xs" color="negative" size="md" />
          </template>
          <template v-if="isExpired"> You cannot edit a ban that has expired. </template>
          <template v-else-if="form.deleted_at">
            You cannot edit a ban that has been removed.
          </template>
        </q-banner>

        <div class="flex">
          <q-space />
          <q-btn
            :label="(state === 'edit' ? 'Edit' : 'Add') + ' Job Ban'"
            type="submit"
            color="primary"
            text-color="black"
            :disabled="isDisabled"
            :loading="form.processing"
          />
        </div>
      </q-form>
    </div>
  </div>
</template>

<script>
import BaseSelect from '@/Components/Selects/BaseSelect.vue'
import JobBanJobs from '@/Components/Selects/JobBanJobs.vue'
import {
  ionAlertCircleOutline,
  ionCalendarClearOutline,
  ionInformationCircleOutline,
} from '@quasar/extras/ionicons-v6'
import { date } from 'quasar'
import BaseForm from './BaseForm.vue'

export default {
  extends: BaseForm,

  components: {
    BaseSelect,
    JobBanJobs,
  },

  setup() {
    return {
      ionCalendarClearOutline,
      ionInformationCircleOutline,
      ionAlertCircleOutline,
    }
  },

  data() {
    return {
      durationOptions: [
        { label: 'Half Hour', value: 30 * 60 },
        { label: 'One Hour', value: 60 * 60 },
        { label: 'Six Hours', value: 6 * 60 * 60 },
        { label: 'Half a Week', value: 3.5 * 24 * 60 * 60 },
        { label: 'One Week', value: 7 * 24 * 60 * 60 },
        { label: 'Two Weeks', value: 14 * 24 * 60 * 60 },
        { label: 'One Month', value: 30 * 24 * 60 * 60 },
        { label: 'Permanent', value: null },
      ],
      durationTimeUntil: null,
      durationTimeAfterYears: null,
      durationTimeAfterDays: null,
      durationTimeAfterHours: null,
      durationTimeAfterMinutes: null,
      editingDuration: false,
    }
  },

  computed: {
    isExpired() {
      if (!this.form.expires_at) return false
      return new Date(this.form.expires_at) <= new Date()
    },

    humanExpiresAt() {
      if (!this.form.expires_at) return null
      const expiresAt = new Date(this.form.expires_at)
      return date.formatDate(expiresAt, 'YYYY/MM/DD HH:mm')
    },

    isDisabled() {
      return this.isExpired || this.form.deleted_at
    },
  },

  created() {
    if (this.form.expires_at) {
      const expiresAt = new Date(this.form.expires_at)
      this.durationTimeUntil = date.formatDate(expiresAt, 'YYYY/MM/DD HH:mm')
    }
  },

  methods: {
    getSecondsUntil(date) {
      const now = new Date()
      const until = new Date(date)
      return Math.round((until.getTime() - now.getTime()) / 1000)
    },

    onDurationChange() {
      this.durationTimeUntil = null
      this.durationTimeAfterYears = null
      this.durationTimeAfterDays = null
      this.durationTimeAfterHours = null
      this.durationTimeAfterMinutes = null
    },

    onDurationTimeUntilChange(val, form) {
      this.durationTimeAfterYears = null
      this.durationTimeAfterDays = null
      this.durationTimeAfterHours = null
      this.durationTimeAfterMinutes = null

      const secondsDiff = this.getSecondsUntil(val)
      form.duration = secondsDiff
    },

    onDurationTimeAfterChange(form) {
      this.durationTimeUntil = null

      const yearsSeconds = (this.durationTimeAfterYears || 0) * 365 * 24 * 60 * 60
      const daysSeconds = (this.durationTimeAfterDays || 0) * 24 * 60 * 60
      const hoursSeconds = (this.durationTimeAfterHours || 0) * 60 * 60
      const minutesSeconds = (this.durationTimeAfterMinutes || 0) * 60

      const seconds = yearsSeconds + daysSeconds + hoursSeconds + minutesSeconds
      form.duration = seconds
    },

    getExpiresAt(expiresAt) {
      if (!expiresAt) return 'never'
      const expiresAtDate = new Date(expiresAt)
      const userTz = new Date()
        .toLocaleDateString(undefined, { day: '2-digit', timeZoneName: 'short' })
        .substring(4)
      return 'on ' + expiresAtDate.toLocaleString('en-GB') + ' ' + userTz
    },

    getExpiresAtFromDuration(duration) {
      if (!duration) return 'never'
      const expiresAtDate = new Date(new Date().getTime() + duration * 1000)
      const userTz = new Date()
        .toLocaleDateString(undefined, { day: '2-digit', timeZoneName: 'short' })
        .substring(4)

      return 'on ' + expiresAtDate.toLocaleString('en-GB') + ' ' + userTz
    },
  },
}
</script>
