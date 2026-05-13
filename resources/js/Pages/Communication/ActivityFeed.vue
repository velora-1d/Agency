<template>
  <Head :title="`Feed Aktivitas - ${workspace.name}`" />

  <WorkspaceLayout
    title="Feed Aktivitas"
    subtitle="Audit log, komentar, dan perubahan status workspace dibaca sebagai stream operasional yang rapi."
    :workspace-name="workspace.name"
    :workspace-slug="workspace.slug"
    :navigation="navigation"
  >
    <template #actions>
      <button
        type="button"
        @click="refreshFeed"
        class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
      >
        <ActivityIcon class="h-4 w-4" />
        <span>Muat Ulang Feed</span>
      </button>
    </template>

    <CommunicationLayout :workspace="workspace">
      <div class="space-y-6">
        <section class="project-hero-shell">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="project-hero-copy">
              <p class="project-hero-kicker">Communication / Feed Aktivitas</p>
              <h2 class="project-hero-title">Jejak perubahan, komentar, dan perpindahan kerja dibaca dalam satu alur yang gampang dipindai tim.</h2>
              <p class="project-hero-desc">
                Filter modul, anggota, dan rentang waktu dipakai untuk menelusuri pergerakan paling relevan tanpa menambah item sidebar baru.
              </p>
            </div>

            <div class="compact-stat-grid min-w-full gap-3 sm:min-w-[22rem] sm:grid-cols-4 xl:w-[34rem]">
              <div class="compact-stat-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Tampil</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ visibleActivities }}</p>
              </div>
              <div class="compact-stat-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Total</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ totalActivities }}</p>
              </div>
              <div class="compact-stat-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Modul</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ moduleCount }}</p>
              </div>
              <div class="compact-stat-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Komentar</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ visibleComments }}</p>
              </div>
            </div>
          </div>

          <div class="mt-4 grid gap-3 md:grid-cols-3">
            <div class="compact-stat-card">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Filter Aktif</p>
              <p class="mt-2 text-sm leading-6 text-stone-600">{{ activeFilterCount }} filter aktif dengan fokus {{ activeFilterSummary }}.</p>
            </div>
            <div class="compact-stat-card">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Cakupan Anggota</p>
              <p class="mt-2 text-sm leading-6 text-stone-600">{{ teamCount }} anggota bisa dipilih untuk menelusuri kontribusi atau approval tertentu.</p>
            </div>
            <div class="compact-stat-card">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Rentang</p>
              <p class="mt-2 text-sm leading-6 text-stone-600">{{ rangeLabel }} dipakai sebagai konteks pembacaan stream saat ini.</p>
            </div>
          </div>
        </section>

        <section class="project-panel-shell">
          <div class="filter-panel-head mb-5">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Filter Feed</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Pisahkan activity berdasarkan modul, anggota, dan periode tanpa keluar dari halaman ini.</h2>
            </div>
            <div class="filter-meta-badge">
              <span class="font-semibold text-stone-950">{{ visibleActivities }}</span> aktivitas tampil
            </div>
          </div>

          <div class="filter-toolbar grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Module</span>
              <select v-model="form.type" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="">Semua</option>
                <option v-for="type in typeOptions" :key="type" :value="type">{{ formatLabel(type) }}</option>
              </select>
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Member</span>
              <select v-model="form.user_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="">Semua</option>
                <option v-for="user in userOptions" :key="user.id" :value="user.id">{{ user.name }}</option>
              </select>
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tanggal Mulai</span>
              <input v-model="form.date_from" type="date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
            </label>

            <div class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_auto] xl:grid-cols-1 xl:gap-4">
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tanggal Selesai</span>
                <input v-model="form.date_to" type="date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
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

        <section class="grid gap-4 xl:grid-cols-[1.05fr_0.95fr]">
          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Arus Aktivitas</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Timeline komentar, perubahan, dan jejak modul di workspace.</h2>
              </div>
              <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">
                {{ visibleActivities }} item
              </span>
            </div>

            <div v-if="activities.data.length" class="mt-5 flow-root">
              <ul role="list" class="-mb-8">
                <li
                  v-for="(activity, index) in activities.data"
                  :key="activity.id"
                  class="pb-8 animate-reveal"
                  :style="{ animationDelay: `${index * 0.06}s` }"
                >
                  <ActivityItem :activity="activity" :is-last="index === activities.data.length - 1" />
                </li>
              </ul>

              <div v-if="activities.meta?.last_page > 1" class="mt-8 border-t border-stone-200 pt-6">
                <nav class="flex flex-wrap items-center gap-2">
                  <button
                    v-for="(link, index) in activities.meta?.links || []"
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

            <div v-else class="mt-5 rounded-[1.6rem] border border-dashed border-stone-300 bg-stone-50 px-6 py-16 text-center">
              <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-white text-stone-400 shadow-sm">
                <ActivityIcon class="h-6 w-6" />
              </div>
              <h3 class="mt-4 text-lg font-semibold text-stone-950">Belum ada aktivitas yang cocok</h3>
              <p class="mt-2 text-sm leading-6 text-stone-500">Coba longgarkan filter atau tunggu stream baru masuk dari anggota workspace.</p>
            </div>
          </article>

          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="border-b border-stone-200 pb-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Sinyal Feed</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Baca distribusi stream dan arahkan review ke titik yang paling padat.</h2>
            </div>

            <div class="mt-5 space-y-4">
              <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <div class="flex items-center justify-between gap-3">
                  <p class="text-sm font-semibold text-stone-950">Campuran Aktivitas</p>
                  <span class="rounded-full bg-white px-3 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-stone-500">{{ typeBreakdown.length }} module aktif</span>
                </div>

                <div class="mt-4 space-y-3">
                  <div
                    v-for="entry in typeBreakdown"
                    :key="entry.key"
                    class="rounded-[1.2rem] border border-white bg-white px-4 py-3"
                  >
                    <div class="flex items-center justify-between gap-3">
                      <p class="text-sm font-semibold text-stone-900">{{ entry.label }}</p>
                      <span class="text-sm font-semibold text-stone-950">{{ entry.count }}</span>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-stone-100">
                      <div class="h-full rounded-full bg-stone-950" :style="{ width: `${entry.percent}%` }"></div>
                    </div>
                  </div>

                  <div v-if="typeBreakdown.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-300 bg-white px-4 py-8 text-center text-sm text-stone-500">
                    Belum ada distribusi modul untuk dibaca di halaman ini.
                  </div>
                </div>
              </div>

              <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <p class="text-sm font-semibold text-stone-950">Scope Aktif</p>
                <div class="mt-4 flex flex-wrap gap-2">
                  <span class="rounded-full bg-white px-3 py-1.5 text-xs text-stone-500">{{ form.type ? formatLabel(form.type) : 'Semua Modul' }}</span>
                  <span class="rounded-full bg-white px-3 py-1.5 text-xs text-stone-500">{{ selectedUserLabel }}</span>
                  <span class="rounded-full bg-white px-3 py-1.5 text-xs text-stone-500">{{ rangeLabel }}</span>
                </div>
              </div>

              <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <p class="text-sm font-semibold text-stone-950">Cara Membaca</p>
                <div class="mt-4 space-y-3 text-sm leading-6 text-stone-600">
                  <p>Gunakan filter modul saat ingin audit perpindahan lintas domain seperti project, invoice, task, atau meeting.</p>
                  <p>Pilih satu anggota bila ingin review ownership, approval, atau komentar yang men-trigger handoff kerja.</p>
                  <p>Refresh feed dipakai untuk ambil event realtime terbaru tanpa mengubah posisi baca saat ini.</p>
                </div>
              </div>
            </div>
          </article>
        </section>
      </div>
    </CommunicationLayout>
  </WorkspaceLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed, onMounted, reactive, watch } from 'vue'
import debounce from 'lodash/debounce'
import { Activity as ActivityIcon } from 'lucide-vue-next'
import WorkspaceLayout from '../../Layouts/WorkspaceLayout.vue'
import CommunicationLayout from '../../Layouts/CommunicationLayout.vue'
import ActivityItem from '../../Components/domain/communication/ActivityItem.vue'

const props = defineProps({
  workspace: Object,
  navigation: Array,
  filters: Object,
  activities: Object,
})

const form = reactive({
  type: props.filters.values.type || '',
  user_id: props.filters.values.user_id || '',
  date_from: props.filters.values.date_from || '',
  date_to: props.filters.values.date_to || '',
})

const typeOptions = computed(() => props.filters?.options?.types || [])
const userOptions = computed(() => props.filters?.options?.users || [])
const totalActivities = computed(() => props.activities?.meta?.total ?? props.activities?.data?.length ?? 0)
const visibleActivities = computed(() => props.activities?.data?.length ?? 0)
const moduleCount = computed(() => typeOptions.value.length)
const teamCount = computed(() => userOptions.value.length)
const visibleComments = computed(() => (props.activities?.data || []).reduce((total, activity) => total + (activity.comments?.length || 0), 0))
const activeFilterCount = computed(() => [form.type, form.user_id, form.date_from, form.date_to].filter(Boolean).length)

const selectedUserLabel = computed(() => {
  const user = userOptions.value.find((item) => String(item.id) === String(form.user_id))
  return user ? user.name : 'Semua Anggota'
})

const activeFilterSummary = computed(() => {
  if (activeFilterCount.value === 0) {
    return 'semua modul dan anggota'
  }

  const labels = []

  if (form.type) labels.push(formatLabel(form.type))
  if (form.user_id) labels.push(selectedUserLabel.value)
  if (form.date_from || form.date_to) labels.push('periode tertentu')

  return labels.join(' / ')
})

const rangeLabel = computed(() => {
  if (!form.date_from && !form.date_to) {
    return 'Semua periode'
  }

  if (form.date_from && form.date_to) {
    return `${formatDateShort(form.date_from)} - ${formatDateShort(form.date_to)}`
  }

  return form.date_from ? `Dari ${formatDateShort(form.date_from)}` : `Sampai ${formatDateShort(form.date_to)}`
})

const typeBreakdown = computed(() => {
  const total = visibleActivities.value || 1
  const counts = new Map()

  for (const activity of props.activities?.data || []) {
    const key = activity.type || 'other'
    counts.set(key, (counts.get(key) || 0) + 1)
  }

  return [...counts.entries()]
    .map(([key, count]) => ({
      key,
      count,
      label: formatLabel(key),
      percent: Math.max(10, Math.round((count / total) * 100)),
    }))
    .sort((left, right) => right.count - left.count)
})

onMounted(() => {
  if (window.Echo) {
    window.Echo.private(`workspace.${props.workspace.id}`)
      .listen('.ActivityCreated', () => {
        router.reload({
          only: ['activities'],
          preserveScroll: true,
        })
      })
      .listen('.CommentAdded', () => {
        router.reload({
          only: ['activities'],
          preserveScroll: true,
        })
      })
  }
})

const submitFilters = debounce(() => {
  router.get(route('workspace.communication.activity-feed', props.workspace.slug), form, {
    preserveState: true,
    preserveScroll: true,
    only: ['activities', 'filters'],
  })
}, 300)

watch(form, () => {
  submitFilters()
})

function refreshFeed() {
  router.reload({
    only: ['activities'],
    preserveScroll: true,
  })
}

function resetFilters() {
  form.type = ''
  form.user_id = ''
  form.date_from = ''
  form.date_to = ''
}

function goToPage(url) {
  if (!url) return

  router.get(url, form, {
    preserveState: true,
    preserveScroll: true,
    only: ['activities', 'filters'],
  })
}

function formatLabel(value) {
  return String(value || '').replaceAll('-', ' ').replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase())
}

function formatDateShort(value) {
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
</script>

<style scoped>
.animate-reveal {
  opacity: 0;
  animation: reveal 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes reveal {
  from {
    opacity: 0;
    transform: translateY(16px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
