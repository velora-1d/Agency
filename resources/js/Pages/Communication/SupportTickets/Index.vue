<template>
  <Head :title="`Tiket Dukungan - ${workspace.name}`" />

  <WorkspaceLayout
    title="Tiket Dukungan"
    subtitle="Kelola tiket dukungan pelanggan, pantau SLA, dan jaga ritme respons client dari satu queue."
    :workspace-name="workspace.name"
    :workspace-slug="workspace.slug"
    :navigation="navigation"
  >
    <template #actions>
      <button
        type="button"
        @click="openCreateModal"
        class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
      >
        <Ticket class="h-4 w-4" />
        <span>Buat Tiket Baru</span>
      </button>
    </template>

    <ProjectLayout :workspace="workspace">
      <div class="space-y-6">
        <section class="project-hero-shell">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="project-hero-copy">
              <p class="project-hero-kicker">Project Management / Tiket Dukungan</p>
              <h2 class="project-hero-title">Antrian support client dibaca sebagai queue prioritas, SLA, dan owner yang siap ditindak.</h2>
              <p class="project-hero-desc">
                Menu ini sekarang dipindahkan ke dalam kategori Project Management agar mempermudah monitoring deliverable dan support.
              </p>
            </div>

            <div class="compact-stat-grid min-w-full gap-3 sm:min-w-[22rem] sm:grid-cols-4 xl:w-[34rem]">
              <div class="compact-stat-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Tampil</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ visibleCount }}</p>
              </div>
              <div class="compact-stat-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Open</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ openCount }}</p>
              </div>
              <div class="compact-stat-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Urgent</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ urgentCount }}</p>
              </div>
              <div class="compact-stat-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Overdue</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ overdueCount }}</p>
              </div>
            </div>
          </div>

          <div class="mt-4 grid gap-3 md:grid-cols-3">
            <div class="compact-stat-card">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Status Antrian</p>
              <p class="mt-2 text-sm leading-6 text-stone-600">{{ totalCount }} tiket tercatat, dengan {{ inProgressCount }} sedang diproses dan {{ resolvedCount }} sudah selesai.</p>
            </div>
            <div class="compact-stat-card">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Filter Aktif</p>
              <p class="mt-2 text-sm leading-6 text-stone-600">{{ activeFilterCount }} filter aktif dengan fokus {{ activeFilterSummary }}.</p>
            </div>
            <div class="compact-stat-card">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Ownership</p>
              <p class="mt-2 text-sm leading-6 text-stone-600">{{ assignedCount }} tiket sudah punya PIC dan {{ unassignedCount }} masih belum di-assign.</p>
            </div>
          </div>
        </section>

        <section class="project-panel-shell">
          <div class="filter-panel-head mb-5">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Filter Antrian</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Saring tiket berdasarkan isi, status, dan prioritas tanpa pindah halaman.</h2>
            </div>
            <div class="filter-meta-badge">
              <span class="font-semibold text-stone-950">{{ visibleCount }}</span> tiket tampil
            </div>
          </div>

          <div class="filter-toolbar grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <label class="space-y-2 text-sm xl:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Cari</span>
              <input
                v-model="filterForm.search"
                type="text"
                class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
              />
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
              <select v-model="filterForm.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="">Semua</option>
                <option value="open">Open</option>
                <option value="in_progress">In Progress</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
              </select>
            </label>

            <div class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_auto] xl:grid-cols-1 xl:gap-4">
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Priority</span>
                <select v-model="filterForm.priority" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Semua</option>
                  <option value="low">Rendah</option>
                  <option value="medium">Sedang</option>
                  <option value="high">Tinggi</option>
                  <option value="urgent">Urgent</option>
                </select>
              </label>

              <button
                type="button"
                @click="resetFilters"
                class="rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900 xl:self-end"
              >
                Atur Ulang Filter
              </button>
            </div>
          </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-[1.06fr_0.94fr]">
          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Antrian Tiket</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Daftar tiket aktif, status SLA, dan owner support.</h2>
              </div>
              <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">
                {{ tickets.from || 0 }}-{{ tickets.to || 0 }} / {{ totalCount }}
              </span>
            </div>

            <div class="mt-5 space-y-4">
              <article
                v-for="ticket in ticketItems"
                :key="ticket.id"
                class="rounded-[1.6rem] border p-5 transition"
                :class="ticket.isOverdue ? 'border-rose-200 bg-rose-50/40' : 'border-stone-200 bg-stone-50 hover:border-stone-300 hover:bg-white'"
              >
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div class="max-w-2xl">
                    <div class="flex flex-wrap items-center gap-2">
                      <h3 class="text-base font-semibold text-stone-950">{{ ticket.title }}</h3>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="getStatusClass(ticket.status)">
                        {{ formatOption(ticket.status) }}
                      </span>
                      <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="getPriorityClass(ticket.priority)">
                        <span class="h-1.5 w-1.5 rounded-full" :class="getPriorityDotClass(ticket.priority)"></span>
                        {{ formatOption(ticket.priority) }}
                      </span>
                      <span v-if="ticket.isOverdue" class="rounded-full bg-rose-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-rose-700">
                        Terlambat
                      </span>
                    </div>

                    <p class="mt-2 text-sm leading-6 text-stone-600">{{ ticket.description || 'Belum ada deskripsi tambahan untuk tiket ini.' }}</p>

                    <div class="mt-4 flex flex-wrap gap-2 text-xs text-stone-500">
                      <span class="rounded-full bg-white px-3 py-1.5">#{{ ticket.shortId }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">{{ ticket.clientLabel }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">{{ ticket.assigneeLabel }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">{{ ticket.sourceLabel }}</span>
                    </div>
                  </div>

                  <div class="min-w-[14rem] space-y-3">
                    <div class="rounded-[1.2rem] border border-white bg-white px-4 py-3">
                      <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">SLA Due</p>
                      <p class="mt-2 text-sm font-semibold" :class="getSlaClass(ticket.sla_due_at, ticket.status)">{{ formatDate(ticket.sla_due_at) }}</p>
                      <p class="mt-1 text-xs text-stone-500">{{ getTimeAgo(ticket.sla_due_at) }}</p>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                      <button
                        type="button"
                        @click="editTicket(ticket.raw)"
                        class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950"
                      >
                        <Pencil class="h-4 w-4" />
                      </button>
                      <button
                        type="button"
                        @click="confirmDelete(ticket.raw)"
                        class="inline-flex items-center justify-center rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50"
                      >
                        <Trash2 class="h-4 w-4" />
                      </button>
                    </div>
                  </div>
                </div>
              </article>

              <div v-if="ticketItems.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-300 bg-stone-50 px-6 py-16 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-white text-stone-400 shadow-sm">
                  <Ticket class="h-6 w-6" />
                </div>
                <h3 class="mt-4 text-lg font-semibold text-stone-950">Tidak ada tiket ditemukan</h3>
                <p class="mt-2 text-sm leading-6 text-stone-500">Ubah filter atau buat tiket baru untuk mengisi queue support workspace ini.</p>
              </div>
            </div>

            <div v-if="tickets.links.length > 3" class="mt-6 border-t border-stone-200 pt-6">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-[11px] uppercase tracking-[0.18em] text-stone-400">
                  Showing {{ tickets.from }} to {{ tickets.to }} of {{ totalCount }} tickets
                </p>
                <nav class="flex flex-wrap gap-2">
                  <button
                    v-for="(link, index) in tickets.links"
                    :key="`${link.label}-${index}`"
                    type="button"
                    @click="goToPage(link.url)"
                    :disabled="!link.url || link.active"
                    class="rounded-2xl px-4 py-2 text-xs font-semibold transition"
                    :class="[
                      link.active ? 'bg-stone-950 text-white' : 'border border-stone-200 bg-white text-stone-600 hover:border-stone-300 hover:text-stone-950',
                      !link.url ? 'cursor-not-allowed opacity-50' : 'cursor-pointer'
                    ]"
                    v-html="link.label"
                  />
                </nav>
              </div>
            </div>
          </article>

          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="border-b border-stone-200 pb-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Support Signals</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Baca distribusi risiko, prioritas, dan ticket yang butuh perhatian cepat.</h2>
            </div>

            <div class="mt-5 space-y-4">
              <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <div class="flex items-center justify-between gap-3">
                  <p class="text-sm font-semibold text-stone-950">Priority Mix</p>
                  <span class="rounded-full bg-white px-3 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-stone-500">{{ priorityMix.length }} levels</span>
                </div>

                <div class="mt-4 space-y-3">
                  <div
                    v-for="entry in priorityMix"
                    :key="entry.key"
                    class="rounded-[1.2rem] border border-white bg-white px-4 py-3"
                  >
                    <div class="flex items-center justify-between gap-3">
                      <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full" :class="entry.dotClass"></span>
                        <p class="text-sm font-semibold text-stone-900">{{ entry.label }}</p>
                      </div>
                      <span class="text-sm font-semibold text-stone-950">{{ entry.count }}</span>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-stone-100">
                      <div class="h-full rounded-full bg-stone-950" :style="{ width: `${entry.percent}%` }"></div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <div class="flex items-center justify-between gap-3">
                  <p class="text-sm font-semibold text-stone-950">Tickets At Risk</p>
                  <span class="rounded-full bg-white px-3 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-stone-500">{{ ticketsAtRisk.length }} items</span>
                </div>

                <div class="mt-4 space-y-3">
                  <button
                    v-for="ticket in ticketsAtRisk"
                    :key="ticket.id"
                    type="button"
                    @click="editTicket(ticket.raw)"
                    class="flex w-full items-start gap-3 rounded-[1.2rem] border border-white bg-white px-4 py-3 text-left transition hover:border-stone-200 hover:bg-stone-50"
                  >
                    <AlertCircle class="mt-0.5 h-4 w-4 flex-none text-rose-500" />
                    <div class="min-w-0 flex-1">
                      <p class="text-sm font-semibold text-stone-950">{{ ticket.title }}</p>
                      <p class="mt-1 text-xs uppercase tracking-[0.16em] text-stone-400">{{ ticket.assigneeLabel }} / {{ ticket.clientLabel }}</p>
                      <p class="mt-2 text-sm leading-6 text-stone-600">{{ formatDate(ticket.sla_due_at) }} / {{ getTimeAgo(ticket.sla_due_at) }}</p>
                    </div>
                  </button>

                  <div v-if="ticketsAtRisk.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-300 bg-white px-4 py-8 text-center text-sm text-stone-500">
                    Tidak ada tiket kritis atau overdue pada daftar yang sedang tampil.
                  </div>
                </div>
              </div>

              <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <p class="text-sm font-semibold text-stone-950">Aturan Antrian</p>
                <div class="mt-4 space-y-3 text-sm leading-6 text-stone-600">
                  <p>Status open menandai tiket yang baru masuk, sedangkan in progress dipakai saat owner sudah mulai menindak.</p>
                  <p>Priority urgent dan high sebaiknya selalu dibaca bersama SLA due untuk tahu mana yang benar-benar butuh respons cepat.</p>
                  <p>Assign owner sejak awal membantu support queue tetap terbaca dan tidak menumpuk di tiket tanpa PIC.</p>
                </div>
              </div>
            </div>
          </article>
        </section>
      </div>
    </ProjectLayout>

    <Transition name="modal">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 p-4 backdrop-blur-sm">
        <div class="w-full max-w-2xl overflow-hidden rounded-[2rem] bg-white shadow-2xl">
          <div class="flex items-center justify-between border-b border-stone-100 bg-stone-50/50 px-8 py-6">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Support Ticket</p>
              <h3 class="mt-2 text-xl font-bold text-stone-900">{{ isEditing ? 'Edit Tiket' : 'Buat Tiket Baru' }}</h3>
            </div>
            <button type="button" @click="closeModal" class="rounded-full p-2 text-stone-500 transition hover:bg-stone-200">
              <X class="h-5 w-5" />
            </button>
          </div>

          <form @submit.prevent="submit" class="space-y-6 p-8">
            <div class="grid grid-cols-2 gap-6">
              <label class="col-span-2 space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Judul Tiket</span>
                <input
                  v-model="form.title"
                  type="text"
                  required
                  class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
                />
                <span v-if="form.errors.title" class="text-[10px] text-rose-500">{{ form.errors.title }}</span>
              </label>

              <label class="col-span-2 space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Deskripsi Masalah</span>
                <textarea
                  v-model="form.description"
                  rows="4"
                  class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
                ></textarea>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Klien</span>
                <select v-model="form.client_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option :value="null">Tanpa Klien</option>
                  <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.company_name }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Assign Ke</span>
                <select v-model="form.assigned_to" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option :value="null">Belum Di-assign</option>
                  <option v-for="member in team" :key="member.id" :value="member.id">{{ member.name }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Prioritas</span>
                <select v-model="form.priority" required class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                  <option value="urgent">Urgent</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Sumber</span>
                <select v-model="form.source" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="portal">Portal Klien</option>
                  <option value="whatsapp">WhatsApp</option>
                </select>
              </label>

              <label v-if="isEditing" class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
                <select v-model="form.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="open">Terbuka</option>
                  <option value="in_progress">Diproses</option>
                  <option value="resolved">Selesai</option>
                  <option value="closed">Ditutup</option>
                </select>
              </label>
            </div>

            <div class="flex justify-end gap-3 pt-2">
              <button
                type="button"
                @click="closeModal"
                class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="form.processing"
                class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-50"
              >
                {{ isEditing ? 'Simpan Perubahan' : 'Buat Tiket' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </WorkspaceLayout>
</template>

<script setup>
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, reactive, ref, watch } from 'vue'
import debounce from 'lodash/debounce'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import {
  AlertCircle,
  Globe,
  MessageSquare,
  Pencil,
  Ticket,
  Trash2,
  X,
} from 'lucide-vue-next'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'
import ProjectLayout from '../../../Layouts/ProjectLayout.vue'

dayjs.extend(relativeTime)

const props = defineProps({
  workspace: Object,
  navigation: Array,
  tickets: Object,
  clients: Array,
  team: Array,
  filters: Object,
})

const filterForm = reactive({
  search: props.filters.search || '',
  status: props.filters.status || '',
  priority: props.filters.priority || '',
})

const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)

const form = useForm({
  title: '',
  description: '',
  client_id: null,
  assigned_to: null,
  priority: 'medium',
  source: 'portal',
  status: 'open',
})

const visibleTickets = computed(() => props.tickets?.data || [])
const totalCount = computed(() => props.tickets?.total ?? visibleTickets.value.length)
const visibleCount = computed(() => visibleTickets.value.length)
const openCount = computed(() => visibleTickets.value.filter((ticket) => ticket.status === 'open').length)
const inProgressCount = computed(() => visibleTickets.value.filter((ticket) => ticket.status === 'in_progress').length)
const resolvedCount = computed(() => visibleTickets.value.filter((ticket) => ['resolved', 'closed'].includes(ticket.status)).length)
const urgentCount = computed(() => visibleTickets.value.filter((ticket) => ticket.priority === 'urgent').length)
const overdueCount = computed(() => visibleTickets.value.filter((ticket) => isOverdue(ticket)).length)
const assignedCount = computed(() => visibleTickets.value.filter((ticket) => ticket.assigned_to).length)
const unassignedCount = computed(() => visibleTickets.value.filter((ticket) => !ticket.assigned_to).length)
const activeFilterCount = computed(() => [filterForm.search, filterForm.status, filterForm.priority].filter(Boolean).length)

const activeFilterSummary = computed(() => {
  if (activeFilterCount.value === 0) {
    return 'semua ticket'
  }

  const labels = []

  if (filterForm.search) labels.push('kata kunci aktif')
  if (filterForm.status) labels.push(formatOption(filterForm.status))
  if (filterForm.priority) labels.push(formatOption(filterForm.priority))

  return labels.join(' / ')
})

const ticketItems = computed(() => visibleTickets.value.map((ticket) => ({
  ...ticket,
  raw: ticket,
  shortId: String(ticket.id).split('-')[0],
  clientLabel: ticket.client?.company_name || 'Tanpa Klien',
  assigneeLabel: ticket.assignee?.name || 'Unassigned',
  sourceLabel: formatOption(ticket.source),
  isOverdue: isOverdue(ticket),
})))

const priorityMix = computed(() => {
  const total = visibleCount.value || 1
  const levels = ['urgent', 'high', 'medium', 'low']

  return levels
    .map((key) => {
      const count = visibleTickets.value.filter((ticket) => ticket.priority === key).length

      return {
        key,
        count,
        label: formatOption(key),
        percent: count === 0 ? 0 : Math.max(10, Math.round((count / total) * 100)),
        dotClass: getPriorityDotClass(key),
      }
    })
    .filter((entry) => entry.count > 0)
})

const ticketsAtRisk = computed(() => {
  return ticketItems.value
    .filter((ticket) => ticket.isOverdue || ticket.priority === 'urgent')
    .sort((left, right) => {
      if (left.isOverdue !== right.isOverdue) {
        return left.isOverdue ? -1 : 1
      }

      if (left.priority !== right.priority) {
        return left.priority === 'urgent' ? -1 : 1
      }

      return dayjs(left.sla_due_at).valueOf() - dayjs(right.sla_due_at).valueOf()
    })
    .slice(0, 5)
})

const submitFilters = debounce(() => {
  router.get(route('workspace.communication.support-tickets.index', props.workspace.slug), filterForm, {
    preserveState: true,
    preserveScroll: true,
  })
}, 300)

watch(filterForm, () => {
  submitFilters()
})

function resetFilters() {
  filterForm.search = ''
  filterForm.status = ''
  filterForm.priority = ''
}

function openCreateModal() {
  isEditing.value = false
  editingId.value = null
  form.reset()
  form.clearErrors()
  form.priority = 'medium'
  form.source = 'portal'
  form.status = 'open'
  showModal.value = true
}

function editTicket(ticket) {
  isEditing.value = true
  editingId.value = ticket.id
  form.clearErrors()
  form.title = ticket.title
  form.description = ticket.description
  form.client_id = ticket.client_id
  form.assigned_to = ticket.assigned_to
  form.priority = ticket.priority
  form.source = ticket.source
  form.status = ticket.status
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  form.reset()
  form.clearErrors()
}

function submit() {
  if (isEditing.value) {
    form.patch(route('workspace.communication.support-tickets.update', {
      workspace: props.workspace.slug,
      supportTicket: editingId.value,
    }), {
      onSuccess: () => closeModal(),
    })
    return
  }

  form.post(route('workspace.communication.support-tickets.store', props.workspace.slug), {
    onSuccess: () => closeModal(),
  })
}

function confirmDelete(ticket) {
  if (!confirm('Apakah Anda yakin ingin menghapus tiket ini?')) return

  router.delete(route('workspace.communication.support-tickets.destroy', {
    workspace: props.workspace.slug,
    supportTicket: ticket.id,
  }))
}

function goToPage(url) {
  if (!url) return

  router.get(url, filterForm, {
    preserveState: true,
    preserveScroll: true,
  })
}

function getStatusClass(status) {
  return {
    'bg-amber-100 text-amber-700': status === 'open',
    'bg-sky-100 text-sky-700': status === 'in_progress',
    'bg-emerald-100 text-emerald-700': status === 'resolved',
    'bg-stone-200 text-stone-700': status === 'closed',
  }
}

function getPriorityClass(priority) {
  return {
    'bg-stone-100 text-stone-500': priority === 'low',
    'bg-sky-100 text-sky-700': priority === 'medium',
    'bg-amber-100 text-amber-700': priority === 'high',
    'bg-rose-100 text-rose-700': priority === 'urgent',
  }
}

function getPriorityDotClass(priority) {
  return {
    'bg-stone-300': priority === 'low',
    'bg-sky-400': priority === 'medium',
    'bg-amber-400': priority === 'high',
    'bg-rose-500': priority === 'urgent',
  }
}

function getSlaClass(date, status) {
  if (!date) return 'text-stone-400'
  if (status === 'resolved' || status === 'closed') return 'text-stone-400'
  return dayjs().isAfter(dayjs(date)) ? 'text-rose-600 font-bold' : 'text-stone-700'
}

function getSourceIcon(source) {
  return source === 'whatsapp' ? MessageSquare : Globe
}

function formatDate(date) {
  if (!date) return 'No SLA'
  return dayjs(date).format('DD MMM YYYY, HH:mm')
}

function getTimeAgo(date) {
  if (!date) return 'Tanpa deadline'
  return dayjs(date).fromNow()
}

function formatOption(value) {
  return String(value || '').replaceAll('-', ' ').replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase())
}

function isOverdue(ticket) {
  if (!ticket?.sla_due_at) return false
  if (ticket.status === 'resolved' || ticket.status === 'closed') return false
  return dayjs().isAfter(dayjs(ticket.sla_due_at))
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: all 0.25s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.96);
}
</style>
tyle>
