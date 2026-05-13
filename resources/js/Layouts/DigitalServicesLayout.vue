<template>
  <div class="space-y-6">
    <section class="relative overflow-hidden rounded-[2rem] border border-stone-200 bg-[linear-gradient(180deg,rgba(255,255,255,0.98)_0%,rgba(247,244,239,0.96)_100%)] p-3 shadow-[0_24px_60px_rgba(28,25,23,0.08)]">
      <div class="absolute inset-x-10 top-0 h-16 rounded-full bg-sky-100/40 blur-3xl"></div>
      <div class="absolute -right-12 top-8 h-24 w-24 rounded-full bg-emerald-100/35 blur-3xl"></div>

      <div class="relative space-y-3">
        <div class="grid gap-2 rounded-[1.5rem] bg-stone-950 p-2 shadow-[0_16px_40px_rgba(28,25,23,0.28)] sm:grid-cols-2 lg:grid-cols-4">
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
            <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-stone-400">Pusat Infrastruktur</p>
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
import { AppWindowMac, FileCode2, Globe, ServerCog } from 'lucide-vue-next'

const props = defineProps({
  workspace: {
    type: Object,
    required: true,
  },
})

const page = usePage()
const baseUrl = `/w/${encodeURIComponent(props.workspace.slug)}/digital-services`

function tabUrl(tab) {
  return `${baseUrl}?tab=${tab}`
}

const items = computed(() => [
  {
    key: 'websites',
    label: 'Situs & Rilis',
    icon: AppWindowMac,
    href: tabUrl('websites'),
    description: 'Pantau kesehatan situs web klien, SSL, uptime, dan riwayat rilis teknis terakhir.',
    pills: ['Uptime', 'SSL', 'Deployment', 'Stack'],
  },
  {
    key: 'domains',
    label: 'Radar Domain',
    icon: Globe,
    href: tabUrl('domains'),
    description: 'Daftar pusat domain klien dengan peringatan otomatis untuk tanggal kedaluwarsa.',
    pills: ['Registrar', 'Renewal', 'DNS', 'Peringatan'],
  },
  {
    key: 'servers',
    label: 'Inventaris Server',
    icon: ServerCog,
    href: tabUrl('servers'),
    description: 'Pusat data fisik VPS/Hosting: IP, Password (SSH), Provider, dan spesifikasi hardware.',
    pills: ['IP SSH', 'Password', 'Specs', 'Lokasi'],
  },
  {
    key: 'forms',
    label: 'Formulir Otomatis',
    icon: FileCode2,
    href: tabUrl('forms'),
    description: 'Bangun formulir web yang otomatis terintegrasi dengan pipeline prospek CRM.',
    pills: ['Lead Gen', 'Semat', 'Webhook', 'Log'],
  },
])

const activeItem = computed(() => {
  const query = new URLSearchParams(page.url.split('?')[1] ?? '')
  const tab = query.get('tab') || 'websites'
  // Map old tab keys to new ones if necessary
  if (tab === 'deployments') return 'websites'
  return tab
})

const activeMeta = computed(() => {
  return items.value.find((item) => item.key === activeItem.value) ?? items.value[0]
})
</script>
