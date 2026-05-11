<template>
  <WorkspaceLayout :title="project.name" subtitle="Command center untuk project: overview, timeline, team, deliverables, invoice, notes, dan activity log.">
    <template #actions>
      <button
        type="button"
        @click="goBack"
        class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-stone-300 hover:text-stone-950"
      >
        <ArrowLeft class="h-4 w-4" />
        <span>Back to Projects</span>
      </button>
    </template>

    <div class="space-y-6">
      <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Progress</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ project.progress }}%</p>
          <p class="mt-2 text-sm text-stone-500">{{ project.counts.completed_tasks }} / {{ project.counts.tasks }} tasks done</p>
        </article>
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Budget</p>
          <p class="mt-3 text-lg font-semibold tracking-[-0.04em] text-stone-950">{{ project.budget_label }}</p>
          <p class="mt-2 text-sm text-stone-500">Actual {{ project.actual_cost_label }}</p>
        </article>
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Files</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ project.counts.files }}</p>
          <p class="mt-2 text-sm text-stone-500">{{ project.counts.pending_approvals }} pending approval</p>
        </article>
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Meetings</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ project.counts.meetings }}</p>
          <p class="mt-2 text-sm text-stone-500">Invoices {{ project.counts.invoices }}</p>
        </article>
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Activity</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ project.counts.activities }}</p>
          <p class="mt-2 text-sm text-stone-500">{{ project.created_at_label }}</p>
        </article>
      </section>

      <section class="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div>
            <div class="flex flex-wrap items-center gap-2">
              <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em]" :class="statusClass(project.status)">
                {{ project.status_label }}
              </span>
              <span v-if="project.portal_enabled" class="rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-emerald-700">
                Client Portal Enabled
              </span>
              <span v-if="project.template" class="rounded-full bg-amber-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-amber-700">
                {{ project.template.name }}
              </span>
            </div>
            <h1 class="mt-4 text-4xl font-semibold tracking-[-0.06em] text-stone-950">{{ project.name }}</h1>
            <p class="mt-2 max-w-4xl text-base leading-7 text-stone-500">{{ project.description || 'Belum ada deskripsi scope project.' }}</p>
          </div>
          <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 px-5 py-4">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Timeline</p>
            <p class="mt-2 text-sm font-semibold text-stone-950">{{ project.timeline_label }}</p>
            <p class="mt-1 text-xs" :class="timelineTextClass(project.timeline_state)">{{ timelineCaption(project.timeline_state) }}</p>
          </div>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
          <div class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Client</p>
            <div class="mt-3 space-y-2 text-sm text-stone-700">
              <p>{{ project.client?.company_name || 'Belum terhubung ke client' }}</p>
              <p>{{ project.overview.portal.enabled ? 'Portal read-only aktif' : 'Portal belum aktif' }}</p>
            </div>
          </div>
          <div class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Finance</p>
            <div class="mt-3 space-y-2 text-sm text-stone-700">
              <p>Budget {{ project.budget_label }}</p>
              <p>Actual {{ project.actual_cost_label }}</p>
              <p>Remaining {{ project.remaining_budget_label }}</p>
            </div>
          </div>
          <div class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Team</p>
            <div class="mt-3 space-y-2 text-sm text-stone-700">
              <p>{{ project.members.length ? project.members.map((member) => member.name).join(', ') : 'Belum ada team member' }}</p>
              <p>{{ project.creator?.name || 'Unknown creator' }}</p>
            </div>
          </div>
          <div class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Signals</p>
            <div class="mt-3 space-y-2 text-sm text-stone-700">
              <p>{{ project.counts.pending_approvals }} deliverable menunggu approval</p>
              <p>{{ project.counts.approved_deliverables }} deliverable approved</p>
            </div>
          </div>
        </div>

        <div class="mt-8">
          <div class="flex items-center justify-between text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">
            <span>Overall Progress</span>
            <span>{{ project.progress }}%</span>
          </div>
          <div class="mt-2 h-3 rounded-full bg-stone-200">
            <div class="h-3 rounded-full transition-all" :class="progressBarClass(project.progress)" :style="{ width: `${project.progress}%` }"></div>
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

        <div v-if="activeTab === 'overview'" class="mt-6 grid gap-4 xl:grid-cols-[1.1fr_0.9fr]">
          <div class="space-y-4">
            <article class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Team Members</p>
              <div class="mt-4 grid gap-3 md:grid-cols-2">
                <div v-for="member in project.members" :key="member.id" class="rounded-[1.2rem] border border-white bg-white p-4">
                  <p class="text-sm font-semibold text-stone-950">{{ member.name }}</p>
                  <p class="mt-1 text-xs uppercase tracking-[0.16em] text-stone-500">{{ member.role || 'General member' }}</p>
                </div>
                <div v-if="project.members.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-200 bg-white px-4 py-8 text-center text-sm text-stone-500">
                  Belum ada team member.
                </div>
              </div>
            </article>

            <article class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tags & Scope Signals</p>
              <div class="mt-4 flex flex-wrap gap-2">
                <span
                  v-for="tag in project.tags"
                  :key="`${project.id}-${tag}`"
                  class="rounded-full bg-white px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-600"
                >
                  {{ tag }}
                </span>
                <span v-if="project.tags.length === 0" class="text-sm text-stone-500">Belum ada tags.</span>
              </div>
            </article>

            <article class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Default Task Blueprint</p>
              <ul class="mt-4 space-y-3">
                <li v-for="task in project.overview.default_tasks" :key="task" class="flex items-start gap-3 rounded-[1.1rem] border border-white bg-white px-4 py-3 text-sm text-stone-700">
                  <span class="mt-1.5 h-2 w-2 rounded-full bg-amber-500"></span>
                  <span>{{ task }}</span>
                </li>
                <li v-if="project.overview.default_tasks.length === 0" class="rounded-[1.1rem] border border-dashed border-stone-200 bg-white px-4 py-8 text-center text-sm text-stone-500">
                  Template belum memiliki default tasks.
                </li>
              </ul>
            </article>
          </div>

          <div class="space-y-4">
            <article class="rounded-[1.6rem] border border-stone-200 bg-stone-950 p-5 text-white">
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-amber-200/70">Client Portal Snapshot</p>
              <h3 class="mt-3 text-xl font-semibold">{{ project.overview.portal.client_name || 'No client linked' }}</h3>
              <p class="mt-3 text-sm leading-6 text-stone-300">
                {{ project.overview.portal.enabled ? 'Client dapat melihat progress secara read-only, approve deliverable, dan mengunduh invoice terkait project ini.' : 'Portal client belum aktif untuk project ini. Aktifkan di data client jika workflow approval client diperlukan.' }}
              </p>
              <div class="mt-5 rounded-[1.2rem] border border-white/10 bg-white/5 p-4">
                <p class="text-xs uppercase tracking-[0.18em] text-stone-400">Invoice Downloads</p>
                <p class="mt-2 text-lg font-semibold text-white">{{ project.overview.portal.invoice_download_count }}</p>
              </div>
            </article>

            <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Deliverable Approvals</p>
              <div class="mt-4 grid gap-3 md:grid-cols-2">
                <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-xs uppercase tracking-[0.18em] text-stone-400">Pending</p>
                  <p class="mt-2 text-2xl font-semibold text-stone-950">{{ project.overview.approvals.pending }}</p>
                </div>
                <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-xs uppercase tracking-[0.18em] text-stone-400">Approved</p>
                  <p class="mt-2 text-2xl font-semibold text-stone-950">{{ project.overview.approvals.approved }}</p>
                </div>
              </div>
              <p class="mt-4 text-sm leading-6 text-stone-500">
                Approval system deliverable dibaca dari file yang terhubung ke project ini. Saat file manager nanti diisi, status pending/approved akan langsung terlihat di overview project.
              </p>
            </article>

            <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Recent Delivery Snapshot</p>
              <div class="mt-4 grid gap-3">
                <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-xs uppercase tracking-[0.18em] text-stone-400">Tasks</p>
                  <p class="mt-2 text-sm text-stone-700">{{ project.counts.tasks }} total · {{ project.counts.completed_tasks }} selesai</p>
                </div>
                <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-xs uppercase tracking-[0.18em] text-stone-400">Files & Notes</p>
                  <p class="mt-2 text-sm text-stone-700">{{ project.counts.files }} files · {{ project.counts.notes }} notes/SOP</p>
                </div>
                <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-xs uppercase tracking-[0.18em] text-stone-400">Meetings & Invoices</p>
                  <p class="mt-2 text-sm text-stone-700">{{ project.counts.meetings }} meetings · {{ project.counts.invoices }} invoices</p>
                </div>
              </div>
            </article>
          </div>
        </div>

        <div v-else-if="activeTab === 'tasks'" class="mt-6 space-y-4">
          <article v-for="task in tabs.tasks" :key="task.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div>
                <h3 class="text-sm font-semibold text-stone-950">{{ task.title }}</h3>
                <p class="mt-2 text-xs text-stone-500">
                  {{ task.assignee?.name || 'Unassigned' }} · {{ task.priority }} · {{ task.subtask_count }} subtasks
                </p>
              </div>
              <div class="text-right">
                <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="taskStatusClass(task.status)">
                  {{ task.status }}
                </span>
                <p class="mt-2 text-xs text-stone-500">{{ task.due_date_label || 'No due date' }}</p>
              </div>
            </div>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
              <div class="rounded-[1.2rem] border border-white bg-white p-4 text-sm text-stone-700">
                Estimated {{ task.estimated_hours ?? 0 }}h
              </div>
              <div class="rounded-[1.2rem] border border-white bg-white p-4 text-sm text-stone-700">
                Actual {{ task.actual_hours ?? 0 }}h
              </div>
            </div>
          </article>
          <div v-if="tabs.tasks.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-14 text-center text-sm text-stone-500">
            Belum ada task di project ini.
          </div>
        </div>

        <div v-else-if="activeTab === 'files'" class="mt-6 grid gap-4 md:grid-cols-2">
          <article v-for="file in tabs.files" :key="file.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
            <div class="flex items-start justify-between gap-3">
              <div>
                <h3 class="text-sm font-semibold text-stone-950">{{ file.original_name || file.name }}</h3>
                <p class="mt-2 text-xs text-stone-500">{{ file.mime_type || 'Unknown type' }} · {{ file.size_label }}</p>
              </div>
              <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="approvalClass(file.approval_status)">
                {{ file.approval_status || 'draft' }}
              </span>
            </div>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
              <div class="rounded-[1.2rem] border border-white bg-white p-4 text-sm text-stone-700">
                Version {{ file.version }}
              </div>
              <div class="rounded-[1.2rem] border border-white bg-white p-4 text-sm text-stone-700">
                {{ file.created_at_label || 'Baru diunggah' }}
              </div>
            </div>
          </article>
          <div v-if="tabs.files.length === 0" class="md:col-span-2 rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-14 text-center text-sm text-stone-500">
            Belum ada file pada project ini.
          </div>
        </div>

        <div v-else-if="activeTab === 'notes'" class="mt-6 space-y-4">
          <article v-for="note in tabs.notes" :key="note.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div>
                <h3 class="text-sm font-semibold text-stone-950">{{ note.title }}</h3>
                <p class="mt-2 text-xs text-stone-500">Type {{ note.type }} · Version {{ note.version }}</p>
              </div>
              <div class="text-right text-xs text-stone-500">
                <p>{{ note.updated_at_label || 'No updates yet' }}</p>
                <p class="mt-2">{{ note.is_private ? 'Private' : 'Shared' }}</p>
              </div>
            </div>
          </article>
          <div v-if="tabs.notes.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-14 text-center text-sm text-stone-500">
            Belum ada notes atau SOP terkait project ini.
          </div>
        </div>

        <div v-else-if="activeTab === 'meetings'" class="mt-6 space-y-4">
          <article v-for="meeting in tabs.meetings" :key="meeting.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div>
                <h3 class="text-sm font-semibold text-stone-950">{{ meeting.title }}</h3>
                <p class="mt-2 text-xs text-stone-500">{{ meeting.scheduled_at_label || 'Belum dijadwalkan' }}</p>
                <p class="mt-2 text-xs text-stone-500">{{ meeting.meeting_url || 'Meeting link belum diisi' }}</p>
              </div>
              <div class="text-right">
                <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="meetingStatusClass(meeting.status)">
                  {{ meeting.status }}
                </span>
                <p class="mt-2 text-xs text-stone-500">{{ meeting.duration_minutes || 0 }} minutes</p>
              </div>
            </div>
          </article>
          <div v-if="tabs.meetings.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-14 text-center text-sm text-stone-500">
            Belum ada meeting untuk project ini.
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
                <td colspan="6" class="px-4 py-12 text-center text-sm text-stone-500">Belum ada invoice terkait project ini.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else-if="activeTab === 'activity'" class="mt-6 space-y-4">
          <article v-for="activity in activities" :key="activity.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div>
                <p class="text-sm font-semibold text-stone-900">{{ activity.description }}</p>
                <p class="mt-2 text-xs text-stone-500">{{ activity.user?.name || 'System' }} · {{ activity.created_at || '-' }}</p>
              </div>
              <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]" :class="activityBadgeClass(activity.metadata?.action)">
                {{ activity.metadata?.action || activity.type }}
              </span>
            </div>
          </article>
          <div v-if="activities.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-14 text-center text-sm text-stone-500">
            Belum ada activity log untuk project ini.
          </div>
        </div>
      </section>
    </div>
  </WorkspaceLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { ArrowLeft } from 'lucide-vue-next'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'

const props = defineProps({
  workspace: {
    type: Object,
    required: true,
  },
  project: {
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
const projectsBaseUrl = `${workspaceBaseUrl}/projects`
const project = props.project
const tabs = props.tabs
const activities = props.activities
const activeTab = ref('overview')

const tabItems = [
  { id: 'overview', label: 'Overview' },
  { id: 'tasks', label: `Tasks (${tabs.tasks.length})` },
  { id: 'files', label: `Files (${tabs.files.length})` },
  { id: 'notes', label: `Notes / SOP (${tabs.notes.length})` },
  { id: 'meetings', label: `Meetings (${tabs.meetings.length})` },
  { id: 'invoices', label: `Invoices (${tabs.invoices.length})` },
  { id: 'activity', label: `Activity (${activities.length})` },
]

function goBack() {
  router.get(projectsBaseUrl)
}

function statusClass(status) {
  const map = {
    planning: 'bg-slate-100 text-slate-700',
    active: 'bg-emerald-100 text-emerald-700',
    on_hold: 'bg-amber-100 text-amber-700',
    completed: 'bg-stone-200 text-stone-700',
  }

  return map[status] || 'bg-stone-100 text-stone-600'
}

function progressBarClass(progress) {
  if (progress >= 100) return 'bg-stone-950'
  if (progress >= 70) return 'bg-emerald-500'
  if (progress >= 35) return 'bg-amber-500'
  return 'bg-slate-400'
}

function timelineTextClass(state) {
  const map = {
    overdue: 'text-rose-600',
    today: 'text-amber-600',
    completed: 'text-emerald-600',
    unscheduled: 'text-stone-500',
    scheduled: 'text-stone-500',
  }

  return map[state] || 'text-stone-500'
}

function timelineCaption(state) {
  const map = {
    overdue: 'Butuh recovery plan',
    today: 'Deadline hari ini',
    completed: 'Sudah selesai',
    unscheduled: 'Belum ada deadline',
    scheduled: 'On track',
  }

  return map[state] || 'Timeline update'
}

function taskStatusClass(status) {
  const map = {
    todo: 'bg-slate-100 text-slate-700',
    in_progress: 'bg-blue-100 text-blue-700',
    review: 'bg-amber-100 text-amber-700',
    done: 'bg-emerald-100 text-emerald-700',
  }

  return map[status] || 'bg-stone-100 text-stone-600'
}

function approvalClass(status) {
  const map = {
    pending: 'bg-amber-100 text-amber-700',
    approved: 'bg-emerald-100 text-emerald-700',
    rejected: 'bg-rose-100 text-rose-700',
  }

  return map[status] || 'bg-stone-100 text-stone-600'
}

function meetingStatusClass(status) {
  const map = {
    scheduled: 'bg-blue-100 text-blue-700',
    completed: 'bg-emerald-100 text-emerald-700',
    cancelled: 'bg-rose-100 text-rose-700',
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
