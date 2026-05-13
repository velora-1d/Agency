<template>
  <div class="space-y-6">
    <section class="relative overflow-hidden rounded-[2rem] border border-stone-200 bg-[linear-gradient(180deg,rgba(255,255,255,0.98)_0%,rgba(247,244,239,0.96)_100%)] p-3 shadow-[0_24px_60px_rgba(28,25,23,0.08)]">
      <div class="absolute inset-x-10 top-0 h-16 rounded-full bg-amber-100/40 blur-3xl"></div>
      <div class="absolute -right-12 top-8 h-24 w-24 rounded-full bg-stone-200/40 blur-3xl"></div>

      <div class="relative space-y-3">
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

        <Transition name="finance-subnav" mode="out-in">
          <div
            :key="activePillar"
            class="grid gap-4 rounded-[1.35rem] border border-stone-200/80 bg-white/92 px-4 py-4 shadow-[0_14px_34px_rgba(28,25,23,0.08)] backdrop-blur lg:grid-cols-[18rem_minmax(0,1fr)] lg:items-center"
          >
            <div class="min-w-0">
              <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-stone-400">Alur Keuangan</p>
              <p class="mt-1 text-sm font-semibold text-stone-900">{{ activePillarMeta.description }}</p>
            </div>

            <div class="flex flex-wrap items-stretch gap-2 lg:justify-end">
              <Link
                v-for="item in activePillarMeta.items"
                :key="item.key"
                :href="item.href"
                class="inline-flex min-w-[9.5rem] flex-1 items-center justify-center gap-2 rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.14em] transition-all duration-300 lg:flex-none"
                :class="item.key === activeSub
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
  ArrowLeftRight,
  BarChart3,
  BriefcaseBusiness,
  ChartColumnBig,
  CreditCard,
  FileSpreadsheet,
  FileStack,
  Landmark,
  ReceiptText,
  ScrollText,
  Users,
  WalletCards,
} from 'lucide-vue-next'

const props = defineProps({
  workspace: {
    type: Object,
    required: true,
  },
})

const page = usePage()
const financeBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}/finance`

function financeUrl(segment = '') {
  return segment ? `${financeBaseUrl}/${segment}` : financeBaseUrl
}

const pillars = computed(() => [
  {
    key: 'analytics',
    label: 'Analitik',
    icon: BarChart3,
    href: financeUrl(),
    description: 'Pantau ringkasan arus kas, laporan inti, dan arah angka utama.',
    items: [
      { key: 'overview', label: 'Ringkasan', icon: ChartColumnBig, href: financeUrl() },
      { key: 'reports', label: 'Laporan', icon: FileSpreadsheet, href: financeUrl('reports') },
    ],
  },
  {
    key: 'sales',
    label: 'Penjualan',
    icon: FileStack,
    href: financeUrl('invoices'),
    description: 'Kelola dokumen pendapatan dari penawaran sampai penagihan aktif.',
    items: [
      { key: 'quotation', label: 'Penawaran', icon: ScrollText, href: financeUrl('quotations') },
      { key: 'invoices', label: 'Tagihan', icon: ReceiptText, href: financeUrl('invoices') },
      { key: 'subscriptions', label: 'Langganan', icon: CreditCard, href: financeUrl('subscriptions') },
      { key: 'billing', label: 'Penagihan', icon: BriefcaseBusiness, href: financeUrl('billings') },
    ],
  },
  {
    key: 'cash',
    label: 'Kas',
    icon: WalletCards,
    href: financeUrl('transactions'),
    description: 'Lihat mutasi, akun bank, dan pengeluaran tanpa kehilangan konteks.',
    items: [
      { key: 'transactions', label: 'Mutasi', icon: ArrowLeftRight, href: financeUrl('transactions') },
      { key: 'cash-bank', label: 'Bank', icon: Landmark, href: financeUrl('cash-bank') },
      { key: 'expenses', label: 'Pengeluaran', icon: ReceiptText, href: financeUrl('expenses') },
    ],
  },
  {
    key: 'payroll',
    label: 'Gaji',
    icon: Users,
    href: financeUrl('payroll'),
    description: 'Atur pembagian fee tim dan postur pembayaran kerja internal.',
    items: [
      { key: 'payroll', label: 'Gaji', icon: Users, href: financeUrl('payroll') },
    ],
  },
])

const activeSub = computed(() => {
  const url = page.url

  if (url === financeBaseUrl || url.startsWith(`${financeBaseUrl}?`)) return 'overview'
  if (url.includes('/finance/quotations')) return 'quotation'
  if (url.includes('/finance/invoices')) return 'invoices'
  if (url.includes('/finance/subscriptions')) return 'subscriptions'
  if (url.includes('/finance/billings')) return 'billing'
  if (url.includes('/finance/transactions')) return 'transactions'
  if (url.includes('/finance/cash-bank')) return 'cash-bank'
  if (url.includes('/finance/expenses')) return 'expenses'
  if (url.includes('/finance/reports')) return 'reports'
  if (url.includes('/finance/payroll')) return 'payroll'

  return 'overview'
})

const activePillar = computed(() => {
  if (['overview', 'reports'].includes(activeSub.value)) return 'analytics'
  if (['quotation', 'invoices', 'subscriptions', 'billing'].includes(activeSub.value)) return 'sales'
  if (['transactions', 'cash-bank', 'expenses'].includes(activeSub.value)) return 'cash'
  if (activeSub.value === 'payroll') return 'payroll'
  return 'analytics'
})

const activePillarMeta = computed(() => {
  return pillars.value.find((pillar) => pillar.key === activePillar.value) ?? pillars.value[0]
})
</script>

<style scoped>
.finance-subnav-enter-active,
.finance-subnav-leave-active {
  transition: all 0.28s ease;
}

.finance-subnav-enter-from,
.finance-subnav-leave-to {
  opacity: 0;
  transform: translateY(-10px) scale(0.985);
}
</style>
