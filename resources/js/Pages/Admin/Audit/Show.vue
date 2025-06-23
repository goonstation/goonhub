<template>
  <q-card class="gh-card q-mb-md" flat>
    <div class="gh-card__header q-pa-md bordered">
      <span>Audit Details</span>
    </div>
    <q-card-section>
      <q-markup-table class="audit-metadata" flat>
        <tbody>
          <tr>
            <td><strong>Auditable</strong></td>
            <td>{{ audit.auditable_label }}</td>
          </tr>
          <tr>
            <td><strong>Event</strong></td>
            <td>{{ audit.event }}</td>
          </tr>
          <tr>
            <td><strong>User</strong></td>
            <td>
              <Link :href="$route('admin.users.edit', audit.user.id)">
                {{ audit.user.name }}
              </Link>
            </td>
          </tr>
          <tr>
            <td><strong>IP Address</strong></td>
            <td>{{ audit.ip_address }}</td>
          </tr>
          <tr>
            <td><strong>User Agent</strong></td>
            <td>{{ audit.user_agent }}</td>
          </tr>
          <tr>
            <td><strong>URL</strong></td>
            <td>{{ audit.url }}</td>
          </tr>
          <tr>
            <td><strong>Tags</strong></td>
            <td>{{ audit.tags?.join() }}</td>
          </tr>
          <tr>
            <td><strong>Timestamp</strong></td>
            <td>{{ $formats.dateWithTime(audit.created_at) }}</td>
          </tr>
        </tbody>
      </q-markup-table>
    </q-card-section>
  </q-card>

  <q-card class="gh-card" flat>
    <div class="gh-card__header q-pa-md bordered">
      <span>Changes Made</span>
    </div>
    <q-card-section>
      <q-markup-table flat>
        <thead>
          <tr>
            <th class="text-left"><strong>Attribute</strong></th>
            <th class="text-left"><strong>Old</strong></th>
            <th class="text-left"><strong>New</strong></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(value, attribute) in modified" :key="attribute">
            <td>{{ attribute }}</td>
            <td class="text-negative">{{ value.old }}</td>
            <td class="text-positive">{{ value.new }}</td>
          </tr>
        </tbody>
      </q-markup-table>
    </q-card-section>
  </q-card>
</template>

<style lang="scss" scoped>
.audit-metadata tbody {
  td:first-child {
    width: 150px;
  }
}
</style>

<script>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

export default {
  layout: (h, page) =>
    h(
      DashboardLayout,
      {
        title: `Audit #${page.props.audit.id}`,
      },
      () => page
    ),

  props: {
    audit: Object,
    modified: Object,
  },
}
</script>
