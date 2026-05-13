<template>
  <div class="space-y-6">
    <section class="relative overflow-hidden rounded-[2rem] border border-stone-200 bg-[linear-gradient(180deg,rgba(255,255,255,0.98)_0%,rgba(247,244,239,0.96)_100%)] p-3 shadow-[0_24px_60px_rgba(28,25,23,0.08)]">
      <div class="absolute inset-x-10 top-0 h-16 rounded-full bg-sky-100/50 blur-3xl"></div>
      <div class="absolute -right-12 top-8 h-24 w-24 rounded-full bg-amber-100/40 blur-3xl"></div>

      <div class="relative space-y-3">
        <div class="grid gap-2 rounded-[1.5rem] bg-stone-950 p-2 shadow-[0_16px_40px_rgba(28,25,23,0.28)] sm:grid-cols-2 xl:grid-cols-5">
          <Link
            v-for="item in items"
            :key="item.key"
            :href="item.href"
            class="inline-flex w-full items-center justify-center gap-2 rounded-[1.1rem] px-4 py-3 text-sm font-semibold transition-all duration-300"
            :class="item.key === activeItem
              ? 'bg-white text-stone-950 shadow-[0_10px_24px_rgba(255,255,255,0.22)]'
              : 'text-stone-300 hover:bg-white/10 hover:text-white'"
          >
            <component :is="item.icon" class="h-4 w-4" />
            <span>{{ item.label }}</span>
          </Link>
        </div>

        <div class="grid gap-4 rounded-[1.35rem] border border-stone-200/80 bg-white/92 px-4 py-4 shadow-[0_14px_34px_rgba(28,25,23,0.08)] backdrop-blur lg:grid-cols-[18rem_minmax(0,1fr)] lg:items-center">
          <div class="min-w-0">
            <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-stone-400">Pusat Komunikasi</p>
            <p class="mt-1 text-sm font-semibold text-stone-900">{{ activeMeta.description }}</p>
          </div>

          <div class="flex flex-wrap items-stretch gap-2 lg:justify-end">
            <div
              v-for="pill in activeMeta.pills"
              :key="pill"
              class="inline-flex min-w-[8.5rem] items-center justify-center rounded-full border border-stone-200 bg-stone-50 px-4 py-2 text-xs font-semibold uppercase tracking-[0.14em] text-stone-500"
            >
              {{ pill }}
            </div>
          </div>
        </div>
      </div>
    </section>

    <slot />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Bell, CalendarDays, Inbox, LifeBuoy, Radar } from 'lucide-vue-next'

const props = defineProps({
  workspace: {
    type: Object,
    required: true,
  },
})

const page = usePage()
const baseUrl = `/w/${encodeURIComponent(props.workspace.slug)}/communication`

function communicationUrl(segment = '') {
  return segment ? `${baseUrl}/${segment}` : `${baseUrl}/inbox`
}

const items = computed(() => [
  {
    key: 'inbox',
    label: 'Kotak Masuk',
    icon: Inbox,
    href: communicationUrl('inbox'),
    description: 'Pusat pesan masuk lintas channel supaya percakapan tim dan klien tidak tercecer.',
    pills: ['DM', 'WhatsApp', 'Penyebutan', 'Antrian'],
  },
  {
    key: 'activity-feed',
    label: 'Aktivitas',
    icon: Radar,
    href: communicationUrl('activity-feed'),
    description: 'Jejak aktivitas, komentar, dan perubahan status untuk audit operasional harian.',
    pills: ['Jejak Audit', 'Komentar', 'Penyebutan', 'Waktu Nyata'],
  },
  {
    key: 'calendar',
    label: 'Kalender',
    icon: CalendarDays,
    href: communicationUrl('calendar'),
    description: 'Agenda workspace untuk rapat, tenggat waktu, kampanye, dan acara koordinasi.',
    pills: ['Rapat', 'Tenggat Waktu', 'Kampanye', 'Jadwal'],
  },
  {
    key: 'support-tickets',
    label: 'Tiket',
    icon: LifeBuoy,
    href: communicationUrl('support-tickets'),
    description: 'Antrian dukungan klien dengan SLA, prioritas, dan penugasan yang mudah dibaca.',
    pills: ['SLA', 'Prioritas', 'Pemilik', 'Status'],
  },
  {
    key: 'notifications',
    label: 'Peringatan',
    icon: Bell,
    href: communicationUrl('notifications'),
    description: 'Ringkasan peringatan penting, tindak lanjut, dan sinyal yang butuh respons cepat.',
    pills: ['Peringatan', 'Pengingat', 'Eskalasi', 'Ringkasan'],
  },
])

const activeItem = computed(() => {
  const url = page.url

  if (url.includes('/communication/activity-feed')) return 'activity-feed'
  if (url.includes('/communication/calendar')) return 'calendar'
  if (url.includes('/communication/support-tickets')) return 'support-tickets'
  if (url.includes('/communication/notifications')) return 'notifications'
  return 'inbox'
})

const activeMeta = computed(() => {
  return items.value.find((item) => item.key === activeItem.value) ?? items.value[0]
})
</script>
