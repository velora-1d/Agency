<template>
  <WorkspaceLayout :title="client.company_name" subtitle="Detail client, relasi project, invoice, kontrak, ticket support, activity, dan notes.">
    <template #actions>
      <button
        type="button"
        @click="goBack"
        class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-stone-300 hover:text-stone-950"
      >
        <ArrowLeft class="h-4 w-4" />
        <span>Back to Clients</span>
      </button>
    </template>

    <div class="space-y-6">
      <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Projects</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ client.counts.projects }}</p>
        </article>
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Invoices</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ client.counts.invoices }}</p>
        </article>
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Contracts</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ client.counts.contracts }}</p>
        </article>
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Support Tickets</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ client.counts.tickets }}</p>
        </article>
      </section>

      <section class="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div>
            <div class="flex flex-wrap items-center gap-2">
              <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em]" :class="statusClass(client.status)">
                {{ statusLabel(client.status) }}
              </span>
              <span v-if="client.portal_access" class="rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-emerald-700">
                Portal Enabled
              </span>
            </div>
            <h1 class="mt-4 text-4xl font-semibold tracking-[-0.06em] text-stone-950">{{ client.company_name }}</h1>
            <p class="mt-2 text-base text-stone-500">{{ client.pic_name || 'PIC belum diisi' }}</p>
          </div>
          <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 px-5 py-4">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Joined</p>
            <p class="mt-2 text-sm font-semibold text-stone-950">{{ client.created_at_label || '-' }}</p>
          </div>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
          <div class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Contact</p>
            <div class="mt-3 space-y-2 text-sm text-stone-700">
              <p>{{ client.email || 'Email belum diisi' }}</p>
              <p>{{ client.phone || 'WA/Telp belum diisi' }}</p>
            </div>
          </div>
          <div class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Industry & Location</p>
            <div class="mt-3 space-y-2 text-sm text-stone-700">
              <p>{{ client.industry || 'Industry belum diisi' }}</p>
              <p>{{ client.location || 'Lokasi belum diisi' }}</p>
            </div>
          </div>
          <div class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Assignment</p>
            <div class="mt-3 space-y-2 text-sm text-stone-700">
              <p>{{ client.assigned_user?.name || 'Belum ada assignee' }}</p>
              <p>{{ client.lead?.name || 'Belum terhubung ke lead' }}</p>
            </div>
          </div>
          <div class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Address</p>
            <div class="mt-3 space-y-2 text-sm text-stone-700">
              <p>{{ client.address || 'Alamat belum diisi' }}</p>
            </div>
          </div>
        </div>
      </section>

      <section class="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="flex flex-wrap gap-2 border-b border-stone-200 pb-4">
          <button
            v-for="tab in tabItems"
            :key="tab.id"
            type="button"
            @click="activeTab = tab.id"
            :class="[
              'rounded-full px-4 py-2 text-sm font-semibold transition-all',
              activeTab === tab.id ? 'bg-stone-950 text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 hover:text-stone-950',
            ]"
          >
            {{ tab.label }}
          </button>
        </div>

        <div v-if="activeTab === 'overview'" class="mt-6 grid gap-4 md:grid-cols-2">
          <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Portal Token</p>
            <p class="mt-3 text-sm text-stone-700">{{ client.portal_token || 'Belum tersedia' }}</p>
          </div>
          <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Linked Lead</p>
            <p class="mt-3 text-sm text-stone-700">{{ client.lead?.company || client.lead?.name || 'Belum ada lead asal' }}</p>
          </div>
        </div>

        <div v-else-if="activeTab === 'projects'" class="mt-6 space-y-4">
          <article v-for="project in tabs.projects" :key="project.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div>
                <h3 class="text-sm font-semibold text-stone-950">{{ project.name }}</h3>
                <p class="mt-2 text-xs text-stone-500">Status {{ project.status || 'unknown' }} - Progress {{ project.progress ?? 0 }}%</p>
                <p class="mt-2 text-xs text-stone-500">{{ project.budget_label }}</p>
              </div>
              <button type="button" @click="openProject(project.id)" class="inline-flex items-center gap-2 rounded-full border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-700 transition-all hover:border-stone-300 hover:text-stone-950">
                <span>Open</span>
                <ArrowUpRight class="h-3.5 w-3.5" />
              </button>
            </div>
          </article>
          <div v-if="tabs.projects.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-14 text-center text-sm text-stone-500">
            Belum ada project untuk client ini.
          </div>
        </div>

        <div v-else-if="activeTab === 'invoices'" class="mt-6 overflow-x-auto">
          <table class="min-w-full divide-y divide-stone-200 text-sm">
            <thead class="bg-stone-50 text-left text-[11px] font-bold uppercase tracking-[0.22em] text-stone-400">
              <tr>
                <th class="px-4 py-3">Number</th>
                <th class="px-4 py-3">Type</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Total</th>
                <th class="px-4 py-3">Paid</th>
                <th class="px-4 py-3">Due Date</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 bg-white">
              <tr v-for="invoice in tabs.invoices" :key="invoice.id">
                <td class="px-4 py-3">{{ invoice.number || '-' }}</td>
                <td class="px-4 py-3">{{ invoice.type || '-' }}</td>
                <td class="px-4 py-3">{{ invoice.status || '-' }}</td>
                <td class="px-4 py-3">{{ invoice.total_label }}</td>
                <td class="px-4 py-3">{{ invoice.paid_amount_label }}</td>
                <td class="px-4 py-3">{{ invoice.due_date_label || '-' }}</td>
              </tr>
              <tr v-if="tabs.invoices.length === 0">
                <td colspan="6" class="px-4 py-12 text-center text-sm text-stone-500">Belum ada invoice untuk client ini.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else-if="activeTab === 'contracts'" class="mt-6 overflow-x-auto">
          <table class="min-w-full divide-y divide-stone-200 text-sm">
            <thead class="bg-stone-50 text-left text-[11px] font-bold uppercase tracking-[0.22em] text-stone-400">
              <tr>
                <th class="px-4 py-3">Title</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Value</th>
                <th class="px-4 py-3">Start</th>
                <th class="px-4 py-3">End</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 bg-white">
              <tr v-for="contract in tabs.contracts" :key="contract.id">
                <td class="px-4 py-3">{{ contract.title }}</td>
                <td class="px-4 py-3">{{ contract.status || '-' }}</td>
                <td class="px-4 py-3">{{ contract.value_label }}</td>
                <td class="px-4 py-3">{{ contract.start_date_label || '-' }}</td>
                <td class="px-4 py-3">{{ contract.end_date_label || '-' }}</td>
              </tr>
              <tr v-if="tabs.contracts.length === 0">
                <td colspan="5" class="px-4 py-12 text-center text-sm text-stone-500">Belum ada contract untuk client ini.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else-if="activeTab === 'tickets'" class="mt-6 space-y-4">
          <article v-for="ticket in tabs.tickets" :key="ticket.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div>
                <h3 class="text-sm font-semibold text-stone-950">{{ ticket.title }}</h3>
                <p class="mt-2 text-xs text-stone-500">Priority {{ ticket.priority || '-' }} - Status {{ ticket.status || '-' }}</p>
                <p class="mt-2 text-xs text-stone-500">Assignee {{ ticket.assignee?.name || 'Belum ada' }} - Source {{ ticket.source || '-' }}</p>
              </div>
              <div class="text-right text-xs text-stone-500">
                <p>SLA {{ ticket.sla_due_at_label || '-' }}</p>
                <p class="mt-2">Resolved {{ ticket.resolved_at_label || '-' }}</p>
              </div>
            </div>
          </article>
          <div v-if="tabs.tickets.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-14 text-center text-sm text-stone-500">
            Belum ada support ticket untuk client ini.
          </div>
        </div>

        <div v-else-if="activeTab === 'activity'" class="mt-6">
          <form class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5" @submit.prevent="submitActivity">
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Add Activity</span>
              <textarea
                v-model="activityForm.content"
                rows="3"
                class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400"
                placeholder="Contoh: Follow up meeting, kirim invoice, update kontrak."
              ></textarea>
            </label>
            <p v-if="activityForm.errors.content" class="mt-2 text-xs text-rose-500">{{ activityForm.errors.content }}</p>
            <div class="mt-4 flex justify-end">
              <button
                type="submit"
                :disabled="activityForm.processing"
                class="rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white transition-all hover:bg-stone-800 disabled:cursor-not-allowed disabled:opacity-60"
              >
                {{ activityForm.processing ? 'Saving...' : 'Add Activity' }}
              </button>
            </div>
          </form>

          <div v-if="activities.length > 0" class="mt-6 space-y-4">
            <article v-for="activity in activities" :key="activity.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
              <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <p class="text-sm font-semibold text-stone-900">{{ activity.description }}</p>
                  <p class="mt-2 text-xs text-stone-500">{{ activity.user?.name || 'System' }} - {{ activity.created_at || '-' }}</p>
                </div>
                <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]" :class="activityBadgeClass(activity.metadata?.action)">
                  {{ activity.metadata?.action || activity.type }}
                </span>
              </div>
            </article>
          </div>
          <div v-else class="mt-6 rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-14 text-center text-sm text-stone-500">
            Belum ada activity history untuk client ini.
          </div>
        </div>

        <div v-else-if="activeTab === 'notes'" class="mt-6">
          <form class="space-y-3" @submit.prevent="submitNotes">
            <textarea
              v-model="notesForm.notes"
              rows="10"
              class="w-full rounded-[1.6rem] border border-stone-200 bg-stone-50 px-4 py-4 text-sm leading-7 text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
              placeholder="Tulis catatan client di sini..."
            ></textarea>
            <p v-if="notesForm.errors.notes" class="text-xs text-rose-500">{{ notesForm.errors.notes }}</p>
            <div class="flex justify-end">
              <button
                type="submit"
                :disabled="notesForm.processing"
                class="rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white transition-all hover:bg-stone-800 disabled:cursor-not-allowed disabled:opacity-60"
              >
                {{ notesForm.processing ? 'Saving...' : 'Save Notes' }}
              </button>
            </div>
          </form>
        </div>
      </section>
    </div>
  </WorkspaceLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { ArrowLeft, ArrowUpRight } from 'lucide-vue-next'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'

const props = defineProps({
  workspace: {
    type: Object,
    required: true,
  },
  client: {
    type: Object,
    required: true,
  },
  tabs: {
    type: Object,
    required: true,
  },
  activities: {
    type: Array,
    default: () => [],
  },
})

const workspaceBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}`
const clientsBaseUrl = `${workspaceBaseUrl}/crm/clients`
const projectsBaseUrl = `${workspaceBaseUrl}/projects`
const client = props.client
const tabs = props.tabs
const activities = props.activities
const activeTab = ref('overview')
const tabItems = [
  { id: 'overview', label: 'Overview' },
  { id: 'projects', label: `Projects (${tabs.projects.length})` },
  { id: 'invoices', label: `Invoices (${tabs.invoices.length})` },
  { id: 'contracts', label: `Contracts (${tabs.contracts.length})` },
  { id: 'tickets', label: `Support Tickets (${tabs.tickets.length})` },
  { id: 'activity', label: `Activity (${activities.length})` },
  { id: 'notes', label: 'Notes' },
]

const notesForm = useForm({
  notes: client.notes || '',
})
const activityForm = useForm({
  content: '',
})

function goBack() {
  router.get(clientsBaseUrl)
}

function openProject(projectId) {
  router.get(`${projectsBaseUrl}/${encodeURIComponent(projectId)}`)
}

function submitNotes() {
  notesForm.patch(`${clientsBaseUrl}/${encodeURIComponent(client.id)}/notes`, {
    preserveScroll: true,
  })
}

function submitActivity() {
  activityForm.post(`${clientsBaseUrl}/${encodeURIComponent(client.id)}/activities`, {
    preserveScroll: true,
    onSuccess: () => {
      activityForm.reset()
    },
  })
}

function statusLabel(status) {
  const map = {
    active: 'Active',
    inactive: 'Inactive',
    on_hold: 'On Hold',
  }

  return map[status] || status
}

function statusClass(status) {
  const map = {
    active: 'bg-emerald-100 text-emerald-700',
    inactive: 'bg-stone-100 text-stone-600',
    on_hold: 'bg-amber-100 text-amber-700',
  }

  return map[status] || 'bg-stone-100 text-stone-600'
}

function activityBadgeClass(action) {
  const map = {
    create: 'bg-emerald-100 text-emerald-700',
    note: 'bg-amber-100 text-amber-700',
    update: 'bg-amber-100 text-amber-700',
    delete: 'bg-rose-100 text-rose-700',
  }

  return map[action] || 'bg-stone-100 text-stone-600'
}
</script>
