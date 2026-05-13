<template>
  <Head :title="`Kalender - ${workspace.name}`" />

  <WorkspaceLayout title="Kalender" subtitle="Agenda meeting, deadline, task, campaign, dan event khusus dibaca dari satu workspace view.">
    <template #actions>
      <button
        type="button"
        @click="openCreateModal()"
        class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
      >
        <Plus class="h-4 w-4" />
        <span>Event Baru</span>
      </button>
    </template>

    <CommunicationLayout :workspace="workspace">
      <div class="space-y-6">
        <section class="project-hero-shell">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="project-hero-copy">
              <p class="project-hero-kicker">Komunikasi / Kalender</p>
              <h2 class="project-hero-title">Agenda lintas tugas, rapat, kampanye, dan acara khusus diringkas jadi kalender kerja yang lebih terbaca.</h2>
              <p class="project-hero-desc">
                Filter jenis tetap tinggal di halaman ini supaya navigasi utama tidak menumpuk, tapi ritme kerja harian tetap mudah dibaca.
              </p>
            </div>

            <div class="compact-stat-grid min-w-full gap-3 sm:min-w-[22rem] sm:grid-cols-4 xl:w-[34rem]">
              <div class="compact-stat-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Tampil</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ filteredEvents.length }}</p>
              </div>
              <div class="compact-stat-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Custom</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ customEventCount }}</p>
              </div>
              <div class="compact-stat-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Mendatang</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ upcomingEvents.length }}</p>
              </div>
              <div class="compact-stat-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Tipe Aktif</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ activeFilters.length }}</p>
              </div>
            </div>
          </div>

          <div class="mt-4 grid gap-3 md:grid-cols-3">
            <div class="compact-stat-card">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Rentang</p>
              <p class="mt-2 text-sm leading-6 text-stone-600">{{ currentRangeLabel }} sedang dimuat di grid kalender.</p>
            </div>
            <div class="compact-stat-card">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Mix</p>
              <p class="mt-2 text-sm leading-6 text-stone-600">{{ topTypeSummary }} menjadi distribusi event yang paling dominan di tampilan aktif.</p>
            </div>
            <div class="compact-stat-card">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Lensa Jadwal</p>
              <p class="mt-2 text-sm leading-6 text-stone-600">{{ allEvents.length }} item agenda terhubung ke workspace ini untuk dibaca lintas fungsi.</p>
            </div>
          </div>
        </section>

        <section class="project-panel-shell">
          <div class="filter-panel-head mb-5">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Filter Kalender</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Pilih jenis agenda yang mau dibaca tanpa pindah halaman dan tanpa nambah sub-menu di sidebar.</h2>
            </div>
            <div class="filter-meta-badge">
              <span class="font-semibold text-stone-950">{{ filteredEvents.length }}</span> event tampil
            </div>
          </div>

          <div class="flex flex-wrap gap-3">
            <button
              type="button"
              @click="selectAllFilters"
              class="inline-flex items-center gap-2 rounded-2xl border px-4 py-3 text-sm font-semibold transition"
              :class="allTypesActive ? 'border-stone-900 bg-stone-950 text-white' : 'border-stone-200 bg-white text-stone-700 hover:border-stone-300 hover:text-stone-950'"
            >
              <Filter class="h-4 w-4" />
              <span>Semua Tipe</span>
            </button>

            <button
              v-for="type in eventTypes"
              :key="type.id"
              type="button"
              @click="toggleFilter(type.id)"
              class="inline-flex items-center gap-2 rounded-2xl border px-4 py-3 text-sm font-semibold transition"
              :class="activeFilters.includes(type.id) ? 'border-stone-900 bg-stone-950 text-white' : 'border-stone-200 bg-white text-stone-700 hover:border-stone-300 hover:text-stone-950'"
            >
              <span :class="['h-2.5 w-2.5 rounded-full', type.color]"></span>
              <span>{{ type.label }}</span>
              <Check v-if="activeFilters.includes(type.id)" class="h-4 w-4" />
            </button>
          </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-[1.08fr_0.92fr]">
          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Grid Kalender</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Timeline bulanan dan agenda list untuk membaca ritme eksekusi workspace.</h2>
              </div>
              <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">{{ currentRangeLabel }}</span>
            </div>

            <div class="relative mt-5 h-[calc(100vh-22rem)] min-h-[38rem]">
              <CalendarGrid
                :events="filteredEvents"
                @range-change="handleRangeChange"
                @select-event="handleSelectEvent"
                @event-dropped="handleEventDropped"
              />

              <div v-if="isLoading" class="absolute inset-0 z-20 flex items-center justify-center rounded-[1.5rem] bg-white/70 backdrop-blur-[2px]">
                <div class="flex flex-col items-center gap-3">
                  <div class="relative">
                    <div class="h-12 w-12 rounded-full border-4 border-stone-200"></div>
                    <Loader2 class="absolute inset-0 h-12 w-12 animate-spin text-stone-950" />
                  </div>
                  <span class="text-xs font-bold uppercase tracking-[0.2em] text-stone-500">Sinkronkan kalender</span>
                </div>
              </div>
            </div>
          </article>

          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="border-b border-stone-200 pb-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Sinyal Jadwal</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Lihat agenda terdekat dan distribusi jenis tanpa perlu buka modal dulu.</h2>
            </div>

            <div class="mt-5 space-y-4">
              <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <div class="flex items-center justify-between gap-3">
                  <p class="text-sm font-semibold text-stone-950">Jadwal Mendatang</p>
                  <span class="rounded-full bg-white px-3 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-stone-500">{{ upcomingEvents.length }} item</span>
                </div>

                <div class="mt-4 space-y-3">
                  <button
                    v-for="event in upcomingEvents"
                    :key="event.id"
                    type="button"
                    @click="handleSelectEvent(event)"
                    class="flex w-full items-start gap-3 rounded-[1.2rem] border border-white bg-white px-4 py-3 text-left transition hover:border-stone-200 hover:bg-stone-50"
                  >
                    <span :class="['mt-1 h-2.5 w-2.5 flex-none rounded-full', getEventBg(event.color)]"></span>
                    <div class="min-w-0 flex-1">
                      <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-semibold text-stone-950">{{ event.title }}</p>
                        <span class="rounded-full bg-stone-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-stone-500">{{ formatOption(event.type) }}</span>
                      </div>
                      <p class="mt-2 text-sm leading-6 text-stone-600">{{ formatEventDateTime(event.start, event.end) }}</p>
                    </div>
                  </button>

                  <div v-if="upcomingEvents.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-300 bg-white px-4 py-8 text-center text-sm text-stone-500">
                    Belum ada event mendatang pada filter aktif.
                  </div>
                </div>
              </div>

              <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <p class="text-sm font-semibold text-stone-950">Campuran Acara</p>
                <div class="mt-4 space-y-3">
                  <div
                    v-for="entry in eventMix"
                    :key="entry.id"
                    class="rounded-[1.2rem] border border-white bg-white px-4 py-3"
                  >
                    <div class="flex items-center justify-between gap-3">
                      <div class="flex items-center gap-2">
                        <span :class="['h-2.5 w-2.5 rounded-full', entry.color]"></span>
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
                <p class="text-sm font-semibold text-stone-950">Aturan Operasional</p>
                <div class="mt-4 space-y-3 text-sm leading-6 text-stone-600">
                  <p>Tugas, rapat, invoice, posting sosial, dan kampanye tetap dibaca sebagai agenda supaya perencanaan tidak pecah ke layar lain.</p>
                  <p>Acara khusus dipakai untuk agenda manual seperti kunjungan klien, pelatihan, atau checkpoint internal yang belum punya modul sendiri.</p>
                  <p>Tarik dan lepas di grid membantu baca simulasi perpindahan jadwal, lalu pembaruan backend bisa diteruskan dari endpoint kalender.</p>
                </div>
              </div>
            </div>
          </article>
        </section>
      </div>

      <Transition name="modal">
        <div v-if="selectedEvent" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 p-4 backdrop-blur-md">
          <div class="w-full max-w-md overflow-hidden rounded-[2rem] border border-white/20 bg-white shadow-2xl">
            <div :class="['h-3 w-full', getEventBg(selectedEvent.color)]"></div>
            <div class="p-8">
              <div class="mb-6 flex items-start justify-between">
                <div class="flex items-center gap-2 rounded-full bg-stone-100 px-3 py-1.5">
                  <component :is="getEventIcon(selectedEvent.type)" class="h-4 w-4 text-stone-600" />
                  <span class="text-[10px] font-black uppercase tracking-[0.2em] text-stone-600">{{ formatOption(selectedEvent.type) }}</span>
                </div>
                <button type="button" @click="selectedEvent = null" class="rounded-full p-2 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700">
                  <X class="h-5 w-5" />
                </button>
              </div>

              <h3 class="text-2xl font-bold tracking-tight text-stone-950">{{ selectedEvent.title }}</h3>
              <p v-if="selectedEvent.description" class="mt-3 text-sm leading-6 text-stone-500">{{ selectedEvent.description }}</p>

              <div class="mb-10 mt-8 space-y-5">
                <div class="flex items-center gap-4">
                  <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-stone-100">
                    <CalendarIcon class="h-5 w-5 text-stone-700" />
                  </div>
                  <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Tanggal</p>
                    <p class="text-sm font-semibold text-stone-900">{{ formatFullDate(selectedEvent.start) }}</p>
                  </div>
                </div>
                <div class="flex items-center gap-4">
                  <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-stone-100">
                    <Clock class="h-5 w-5 text-stone-700" />
                  </div>
                  <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Waktu</p>
                    <p class="text-sm font-semibold text-stone-900">{{ formatTimeRange(selectedEvent.start, selectedEvent.end) }}</p>
                  </div>
                </div>
              </div>

              <div class="flex flex-col gap-3">
                <div class="grid grid-cols-2 gap-3">
                  <button
                    type="button"
                    @click="editEvent(selectedEvent)"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl border border-stone-200 px-6 py-3 text-sm font-bold text-stone-700 transition hover:bg-stone-50"
                  >
                    <Edit3 class="h-4 w-4" />
                    <span>Ubah</span>
                  </button>
                  <button
                    v-if="selectedEvent.type === 'event'"
                    type="button"
                    @click="deleteEvent(selectedEvent)"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl border border-rose-200 px-6 py-3 text-sm font-bold text-rose-600 transition hover:bg-rose-50"
                  >
                    <Trash2 class="h-4 w-4" />
                    <span>Hapus</span>
                  </button>
                  <button
                    v-else
                    type="button"
                    disabled
                    class="inline-flex cursor-not-allowed items-center justify-center gap-2 rounded-2xl border border-stone-100 px-6 py-3 text-sm font-bold text-stone-300"
                  >
                    <Trash2 class="h-4 w-4" />
                    <span>Hapus</span>
                  </button>
                </div>

                <button
                  type="button"
                  @click="openView(selectedEvent)"
                  class="w-full rounded-2xl bg-stone-950 py-4 text-sm font-bold text-white transition hover:bg-stone-800"
                >
                  Buka Detail
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>

      <Transition name="modal">
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 p-4 backdrop-blur-md">
          <div class="w-full max-w-md overflow-hidden rounded-[2rem] border border-white/20 bg-white shadow-2xl">
            <div class="p-8">
              <div class="mb-6 flex items-center justify-between">
                <div>
                  <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Event Kalender</p>
                  <h3 class="mt-2 text-xl font-bold text-stone-900">{{ isEditing ? 'Ubah Event' : 'Buat Event Baru' }}</h3>
                </div>
                <button type="button" @click="closeCreateModal" class="rounded-full p-2 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700">
                  <X class="h-5 w-5" />
                </button>
              </div>

              <form @submit.prevent="submit" class="space-y-4">
                <label class="space-y-2 text-sm">
                  <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Judul Event</span>
                  <input
                    v-model="form.title"
                    type="text"
                    class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
                    required
                  />
                  <p v-if="form.errors.title" class="text-[10px] text-rose-500">{{ form.errors.title }}</p>
                </label>

                <div class="grid grid-cols-2 gap-4">
                  <label class="space-y-2 text-sm">
                    <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tanggal Mulai</span>
                    <input
                      v-model="form.start_at"
                      type="datetime-local"
                      class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
                      required
                    />
                  </label>

                  <label class="space-y-2 text-sm">
                    <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tanggal Selesai</span>
                    <input
                      v-model="form.end_at"
                      type="datetime-local"
                      class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
                    />
                  </label>
                </div>

                <div class="space-y-2">
                  <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tag Warna</span>
                  <div class="flex gap-2">
                    <button
                      v-for="color in ['blue', 'purple', 'emerald', 'amber', 'sky', 'rose']"
                      :key="color"
                      type="button"
                      @click="form.color = color"
                      :class="[
                        'h-8 w-8 rounded-full border-2 transition-all',
                        form.color === color ? 'scale-110 border-stone-900 shadow-lg' : 'border-transparent',
                        getEventBg(color)
                      ]"
                    ></button>
                  </div>
                </div>

                <label class="space-y-2 text-sm">
                  <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Deskripsi</span>
                  <textarea
                    v-model="form.description"
                    rows="3"
                    class="w-full resize-none rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
                  ></textarea>
                </label>

                <button
                  type="submit"
                  :disabled="form.processing"
                  class="w-full rounded-2xl bg-stone-950 py-4 text-sm font-bold text-white transition hover:bg-stone-800 disabled:opacity-50"
                >
                  {{ isEditing ? (form.processing ? 'Memperbarui Event' : 'Perbarui Event') : (form.processing ? 'Membuat Event' : 'Buat Event') }}
                </button>
              </form>
            </div>
          </div>
        </div>
      </Transition>

      <div v-if="toast" class="fixed bottom-8 left-1/2 z-[100] -translate-x-1/2">
        <div class="flex items-center gap-3 rounded-full border border-white/10 bg-stone-950 px-6 py-3 text-white shadow-2xl">
          <CheckCircle2 class="h-5 w-5 text-emerald-400" />
          <span class="text-sm font-medium">{{ toast }}</span>
        </div>
      </div>
    </CommunicationLayout>
  </WorkspaceLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import {
  Calendar as CalendarIcon,
  Check,
  CheckCircle2,
  Clock,
  Edit3,
  FileText,
  Filter,
  Info,
  Loader2,
  Plus,
  Rocket,
  Share2,
  Trash2,
  Video,
  X,
} from 'lucide-vue-next'
import WorkspaceLayout from '../../Layouts/WorkspaceLayout.vue'
import CommunicationLayout from '../../Layouts/CommunicationLayout.vue'
import CalendarGrid from '../../Components/domain/communication/CalendarGrid.vue'

const props = defineProps({
  workspace: Object,
  initialEvents: Array,
  filters: Object,
})

const calendarBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}/communication/calendar`
const eventTypes = [
  { id: 'task', label: 'Tasks', color: 'bg-emerald-500' },
  { id: 'meeting', label: 'Meetings', color: 'bg-purple-500' },
  { id: 'invoice', label: 'Invoices', color: 'bg-amber-500' },
  { id: 'social', label: 'Social Posts', color: 'bg-sky-500' },
  { id: 'campaign', label: 'Campaigns', color: 'bg-rose-500' },
  { id: 'event', label: 'Custom Events', color: 'bg-blue-500' },
]

const allEvents = ref(props.initialEvents || [])
const selectedEvent = ref(null)
const showCreateModal = ref(false)
const isEditing = ref(false)
const editingEventId = ref(null)
const toast = ref('')
const isLoading = ref(false)
const activeFilters = ref(eventTypes.map((item) => item.id))
const currentRange = ref({
  start: props.filters?.start || '',
  end: props.filters?.end || '',
})

const form = useForm({
  title: '',
  description: '',
  start_at: '',
  end_at: '',
  all_day: false,
  color: 'blue',
})

watch(
  () => props.initialEvents,
  (events) => {
    allEvents.value = events || []

    if (!selectedEvent.value) {
      return
    }

    const updated = allEvents.value.find((event) => event.id === selectedEvent.value.id)
    selectedEvent.value = updated || null
  },
  { deep: true }
)

const allTypesActive = computed(() => activeFilters.value.length === eventTypes.length)
const filteredEvents = computed(() => allEvents.value.filter((event) => activeFilters.value.includes(event.type)))
const customEventCount = computed(() => filteredEvents.value.filter((event) => event.type === 'event').length)

const upcomingEvents = computed(() => {
  const now = new Date()

  return [...filteredEvents.value]
    .filter((event) => new Date(event.end || event.start) >= now)
    .sort((left, right) => new Date(left.start) - new Date(right.start))
    .slice(0, 6)
})

const eventMix = computed(() => {
  const total = filteredEvents.value.length || 1

  return eventTypes.map((type) => {
    const count = filteredEvents.value.filter((event) => event.type === type.id).length

    return {
      ...type,
      count,
      percent: count === 0 ? 0 : Math.max(10, Math.round((count / total) * 100)),
    }
  }).filter((entry) => entry.count > 0)
})

const topTypeSummary = computed(() => {
  if (eventMix.value.length === 0) {
    return 'Belum ada event pada filter ini'
  }

  const top = [...eventMix.value].sort((left, right) => right.count - left.count)[0]
  return `${top.label} (${top.count})`
})

const currentRangeLabel = computed(() => {
  if (!currentRange.value.start && !currentRange.value.end) {
    return 'Current period'
  }

  if (currentRange.value.start && currentRange.value.end) {
    return `${formatShortDate(currentRange.value.start)} - ${formatShortDate(currentRange.value.end)}`
  }

  return currentRange.value.start || currentRange.value.end
})

function resetForm() {
  form.reset()
  form.clearErrors()
  form.color = 'blue'
  isEditing.value = false
  editingEventId.value = null
}

function openCreateModal() {
  resetForm()
  showCreateModal.value = true
}

function closeCreateModal() {
  showCreateModal.value = false
  resetForm()
}

function editEvent(event) {
  if (event.type !== 'event') {
    openView(event)
    return
  }

  isEditing.value = true
  editingEventId.value = event.id
  form.title = event.title
  form.description = event.description || ''
  form.start_at = formatDateTimeLocal(event.start)
  form.end_at = event.end ? formatDateTimeLocal(event.end) : ''
  form.all_day = event.all_day
  form.color = event.color
  selectedEvent.value = null
  showCreateModal.value = true
}

function openView(event) {
  let url = '#'

  if (!event || !event.type) return

  switch (event.type) {
    case 'task':
      if (event.raw?.project_id) {
        url = `/w/${props.workspace.slug}/projects/${event.raw.project_id}?task=${event.id}`
      }
      break
    case 'meeting':
      url = `/w/${props.workspace.slug}/meetings?meeting=${event.id}`
      break
    case 'invoice':
      url = '#'
      break
    default:
      url = '#'
  }

  if (url !== '#') {
    router.get(url)
    return
  }

  showToast('Detail view belum tersedia untuk event type ini')
}

function submit() {
  const url = isEditing.value ? `${calendarBaseUrl}/${editingEventId.value}` : calendarBaseUrl
  const method = isEditing.value ? form.patch : form.post

  method(url, {
    onSuccess: () => {
      const wasEditing = isEditing.value
      closeCreateModal()
      showToast(wasEditing ? 'Event berhasil diperbarui' : 'Event berhasil dibuat')
    },
  })
}

function deleteEvent(event) {
  if (!confirm('Hapus event ini?')) return

  router.delete(`${calendarBaseUrl}/${event.id}`, {
    onSuccess: () => {
      selectedEvent.value = null
      showToast('Event berhasil dihapus')
    },
  })
}

function toggleFilter(type) {
  if (activeFilters.value.includes(type)) {
    if (activeFilters.value.length > 1) {
      activeFilters.value = activeFilters.value.filter((item) => item !== type)
    }
    return
  }

  activeFilters.value.push(type)
}

function selectAllFilters() {
  activeFilters.value = eventTypes.map((item) => item.id)
}

function handleRangeChange(range) {
  isLoading.value = true
  currentRange.value = range

  router.reload({
    data: { start: range.start, end: range.end },
    only: ['initialEvents'],
    onSuccess: (page) => {
      allEvents.value = page.props.initialEvents
    },
    onFinish: () => {
      isLoading.value = false
    },
  })
}

function handleSelectEvent(event) {
  selectedEvent.value = event
}

function handleEventDropped({ event, newDate }) {
  const index = allEvents.value.findIndex((item) => item.id === event.id)

  if (index !== -1) {
    allEvents.value[index].start = newDate
    showToast(`Event dipindah ke ${formatShortDate(newDate)}`)
  }
}

function showToast(message) {
  toast.value = message
  window.setTimeout(() => {
    toast.value = ''
  }, 3000)
}

function getEventIcon(type) {
  const map = {
    task: CheckCircle2,
    meeting: Video,
    invoice: FileText,
    social: Share2,
    campaign: Rocket,
    event: Info,
  }

  return map[type] || Info
}

function getEventBg(color) {
  const map = {
    blue: 'bg-blue-500',
    purple: 'bg-purple-500',
    emerald: 'bg-emerald-500',
    amber: 'bg-amber-500',
    sky: 'bg-sky-500',
    rose: 'bg-rose-500',
  }

  return map[color] || 'bg-blue-500'
}

function formatFullDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

function formatTimeRange(start, end) {
  const startTime = new Date(start).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })

  if (!end) {
    return startTime
  }

  const endTime = new Date(end).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
  return `${startTime} - ${endTime}`
}

function formatEventDateTime(start, end) {
  return `${formatFullDate(start)} / ${formatTimeRange(start, end)}`
}

function formatOption(value) {
  return String(value || '').replaceAll('-', ' ').replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase())
}

function formatShortDate(value) {
  const date = new Date(value)

  if (Number.isNaN(date.getTime())) {
    return value
  }

  return date.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

function formatDateTimeLocal(value) {
  const date = new Date(value)

  if (Number.isNaN(date.getTime())) {
    return ''
  }

  const offset = date.getTimezoneOffset()
  const localDate = new Date(date.getTime() - offset * 60000)
  return localDate.toISOString().slice(0, 16)
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
