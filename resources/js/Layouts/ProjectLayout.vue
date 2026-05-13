<template>
  <div class="space-y-6">
    <section class="relative overflow-hidden rounded-[2rem] border border-stone-200 bg-[linear-gradient(180deg,rgba(255,255,255,0.98)_0%,rgba(247,244,239,0.96)_100%)] p-3 shadow-[0_24px_60px_rgba(28,25,23,0.08)]">
      <div class="absolute inset-x-10 top-0 h-16 rounded-full bg-amber-100/40 blur-3xl"></div>
      <div class="absolute -right-12 top-8 h-24 w-24 rounded-full bg-stone-200/40 blur-3xl"></div>

      <div class="relative space-y-3">
        <!-- Grid pilar dikembalikan ke 4 kolom -->
        <div class="grid gap-2 rounded-[1.5rem] bg-stone-950 p-2 shadow-[0_16px_40px_rgba(28,25,23,0.28)] sm:grid-cols-2 lg:grid-cols-4">
          <Link
            v-for="pillar in pillars"
            :key="pillar.key"
            :href="pillar.href"
            class="inline-flex w-full items-center justify-center gap-2 rounded-[1.1rem] px-4 py-3 text-sm font-semibold transition-all duration-300"
            :class="pillar.key === activePillar
              ? 'bg-white text-stone-950 shadow-[0_10px_24px_rgba(255,255,255,0.22)]'
              : 'text-stone-300 hover:bg-white/10 hover:text-white'"
          >
            <component :is="pillar.icon" class="h-4 w-4" />
            <span>{{ pillar.label }}</span>
          </Link>
        </div>

        <Transition name="project-subnav" mode="out-in">
          <div
            :key="activePillar"
            class="grid gap-4 rounded-[1.35rem] border border-stone-200/80 bg-white/92 px-4 py-4 shadow-[0_14px_34px_rgba(28,25,23,0.08)] backdrop-blur lg:grid-cols-[18rem_minmax(0,1fr)] lg:items-center"
          >
            <div class="min-w-0">
              <!-- Label diubah jadi Operasional -->
              <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-stone-400">Operasional</p>
              <p class="mt-1 text-sm font-semibold text-stone-900">{{ activePillarMeta.description }}</p>
            </div>

            <div class="flex flex-wrap items-stretch gap-2 lg:justify-end">
              <Link
                v-for="item in activePillarMeta.items"
                :key="item.key"
                :href="item.href"
                class="inline-flex min-w-[9.5rem] flex-1 items-center justify-center gap-2 rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.14em] transition-all duration-300 lg:flex-none"
                :class="item.key === activeItem
                  ? 'border-stone-900 bg-stone-950 text-white shadow-[0_10px_24px_rgba(28,25,23,0.18)]'
                  : 'border-stone-200 bg-stone-50 text-stone-500 hover:border-stone-300 hover:bg-white hover:text-stone-900'"
              >
                <component :is="item.icon" class="h-3.5 w-3.5" />
                <span>{{ item.label }}</span>
              </Link>
            </div>
          </div>
        </Transition>
      </div>
    </section>

    <slot />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
  CalendarDays,
  FolderKanban,
  FolderSearch,
  Gauge,
  BriefcaseBusiness,
  Layers3,
  BookOpenText
} from 'lucide-vue-next'

const props = defineProps({
  workspace: {
    type: Object,
    required: true,
  },
})

const page = usePage()
const baseUrl = `/w/${encodeURIComponent(props.workspace.slug)}`

function projectUrl(segment = '') {
  return segment ? `${baseUrl}/${segment}` : `${baseUrl}/projects`
}

const pillars = computed(() => [
  {
    key: 'overview',
    label: 'Ringkasan',
    icon: BriefcaseBusiness,
    href: projectUrl('projects'),
    description: 'Ringkasan portofolio proyek, tahap, anggaran, dan status keseluruhan.',
    items: [
      { key: 'projects', label: 'Proyek', icon: FolderKanban, href: projectUrl('projects') },
    ],
  },
  {
    key: 'delivery',
    label: 'Eksekusi',
    icon: Layers3,
    href: projectUrl('tasks'),
    description: 'Eksekusi operasional harian, tugas, rapat, dan item tindakan.',
    items: [
      { key: 'tasks', label: 'Tugas', icon: Gauge, href: projectUrl('tasks') },
      { key: 'meetings', label: 'Rapat', icon: CalendarDays, href: projectUrl('meetings') },
    ],
  },
  {
    key: 'documents',
    label: 'SOP & Catatan',
    icon: BookOpenText,
    href: projectUrl('notes'),
    description: 'Buku panduan proyek, aturan kerja (SOP), log revisi, dan instruksi internal.',
    items: [
      { key: 'notes', label: 'SOP & Catatan', icon: BookOpenText, href: projectUrl('notes') },
    ],
  },
  {
    key: 'assets',
    label: 'Berkas',
    icon: FolderSearch,
    href: projectUrl('files'),
    description: 'Manajer aset desain, dokumen kerja, tautan berbagi, dan pelacakan versi file.',
    items: [
      { key: 'files', label: 'Berkas', icon: FolderSearch, href: projectUrl('files') },
    ],
  },
])

const items = computed(() => [
  {
    key: 'projects',
    label: 'Proyek',
    icon: FolderKanban,
    href: projectUrl('projects'),
    description: 'Portofolio proyek, tahap, snapshot anggaran, dan koordinasi lintas tim.',
    pills: ['Portofolio', 'Tahap', 'Pemilik', 'Persetujuan'],
  },
  {
    key: 'tasks',
    label: 'Tugas',
    icon: Gauge,
    href: projectUrl('tasks'),
    description: 'Eksekusi kerja harian, ketergantungan, templat tugas, dan pelacakan kemajuan tim.',
    pills: ['Papan', 'Linimasa', 'Ketergantungan', 'Pelacakan'],
  },
  {
    key: 'meetings',
    label: 'Rapat',
    icon: CalendarDays,
    href: projectUrl('meetings'),
    description: 'Sinkronisasi agenda, catatan rapat, peserta, dan item tindakan yang turun ke tugas.',
    pills: ['Agenda', 'Catatan', 'Peserta', 'Item Tindakan'],
  },
  {
    key: 'notes',
    label: 'SOP & Catatan',
    icon: BookOpenText,
    href: projectUrl('notes'),
    description: 'Aturan kerja proyek, referensi standar operasional, log perubahan, dan instruksi internal.',
    pills: ['SOP', 'Aturan', 'Panduan', 'Folder'],
  },
  {
    key: 'files',
    label: 'Berkas',
    icon: FolderSearch,
    href: projectUrl('files'),
    description: 'Manajer aset desain/kode, persetujuan file, dan pusat berbagi file proyek.',
    pills: ['Aset', 'Persetujuan', 'Berbagi', 'Versi'],
  },
])

const activeItem = computed(() => {
  const url = page.url

  if (url.includes('/tasks')) return 'tasks'
  if (url.includes('/meetings')) return 'meetings'
  if (url.includes('/notes')) return 'notes'
  if (url.includes('/files')) return 'files'
  return 'projects'
})

const activePillar = computed(() => {
  if (['projects'].includes(activeItem.value)) return 'overview'
  if (['tasks', 'meetings'].includes(activeItem.value)) return 'delivery'
  if (['notes'].includes(activeItem.value)) return 'documents'
  if (['files'].includes(activeItem.value)) return 'assets'
  return 'overview'
})

const activePillarMeta = computed(() => {
  return pillars.value.find((pillar) => pillar.key === activePillar.value) ?? pillars.value[0]
})
</script>

<style scoped>
.project-subnav-enter-active,
.project-subnav-leave-active {
  transition: all 0.28s ease;
}

.project-subnav-enter-from,
.project-subnav-leave-to {
  opacity: 0;
  transform: translateY(-10px) scale(0.985);
}
</style>
