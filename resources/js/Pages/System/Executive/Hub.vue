<template>
  <Head title="Pusat Kendali Owner" />

  <WorkspaceLayout title="Pusat Kendali" subtitle="Agregasi data eksekutif lintas brand (Velora ID & Maven Forge) untuk pemantauan profitabilitas grup agensi.">
    <div class="space-y-6">
      <!-- Global Summary Cards -->
      <section class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm">
          <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Total Pendapatan Grup</p>
          <p class="mt-4 text-3xl font-bold tracking-tight text-stone-950">{{ summary.total_revenue_label }}</p>
          <div class="mt-4 flex items-center gap-2">
            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700 uppercase">Sehat</span>
            <p class="text-xs text-stone-500">Margin Profit: {{ summary.profit_margin }}%</p>
          </div>
        </div>
        <div class="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm">
          <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Total Pengeluaran</p>
          <p class="mt-4 text-3xl font-bold tracking-tight text-stone-950">{{ summary.total_expense_label }}</p>
          <p class="mt-4 text-xs text-stone-500">Operasional, Gaji, & Vendor</p>
        </div>
        <div class="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm">
          <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Laba Bersih (Net)</p>
          <p class="mt-4 text-3xl font-bold tracking-tight text-emerald-600">{{ summary.net_profit_label }}</p>
          <p class="mt-4 text-xs text-stone-500">Setelah dikurangi semua beban</p>
        </div>
        <div class="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm">
          <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Piutang (Receivables)</p>
          <p class="mt-4 text-3xl font-bold tracking-tight text-amber-600">{{ summary.receivables_label }}</p>
          <p class="mt-4 text-xs text-stone-500">{{ summary.active_projects_count }} proyek aktif berjalan</p>
        </div>
      </section>

      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Revenue Trend Chart (Simple Placeholder) -->
        <section class="lg:col-span-2 space-y-6">
            <article class="rounded-[2.5rem] border border-stone-200 bg-white p-8 shadow-sm">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h4 class="text-lg font-bold text-stone-950">Tren Pendapatan Grup</h4>
                        <p class="text-sm text-stone-500">Akumulasi 6 bulan terakhir</p>
                    </div>
                    <div class="flex gap-2">
                        <span class="flex items-center gap-1.5 text-xs font-semibold text-stone-600">
                            <span class="h-2 w-2 rounded-full bg-stone-950"></span>
                            Pendapatan
                        </span>
                    </div>
                </div>

                <div class="flex items-end justify-between gap-2 h-64 px-4">
                    <div v-for="point in revenueTrend" :key="point.month" class="flex flex-col items-center gap-4 flex-1 group">
                        <div class="relative w-full flex justify-center">
                            <div 
                                class="w-12 bg-stone-950 rounded-t-xl transition-all duration-700 group-hover:bg-amber-400"
                                :style="{ height: `${(point.amount / Math.max(...revenueTrend.map(p => p.amount))) * 200}px` }"
                            ></div>
                            <div class="absolute -top-8 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap bg-stone-900 text-white text-[10px] px-2 py-1 rounded-md">
                                {{ point.label }}
                            </div>
                        </div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-stone-400">{{ point.month }}</p>
                    </div>
                </div>
            </article>

            <!-- Brand Comparison -->
            <div class="grid gap-4 md:grid-cols-2">
                <article v-for="brand in brandPerformance" :key="brand.slug" class="rounded-[2rem] border border-stone-200 bg-stone-50 p-6 transition hover:border-stone-400 hover:bg-white">
                    <div class="flex items-center justify-between">
                        <h5 class="text-base font-bold text-stone-950">{{ brand.name }}</h5>
                        <button @click="router.get(`/w/${brand.slug}`)" class="text-[10px] font-bold uppercase tracking-widest text-amber-700 hover:underline">Buka Kantor</button>
                    </div>
                    <div class="mt-6 grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[9px] font-bold uppercase tracking-widest text-stone-400">Revenue</p>
                            <p class="mt-1 text-sm font-bold text-stone-900">{{ brand.revenue_label }}</p>
                        </div>
                        <div>
                            <p class="text-[9px] font-bold uppercase tracking-widest text-stone-400">Proyek</p>
                            <p class="mt-1 text-sm font-bold text-stone-900">{{ brand.projects_count }}</p>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        <!-- Right Column -->
        <aside class="space-y-6">
            <div class="rounded-[2.5rem] bg-[#1c1917] p-8 text-white shadow-2xl">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-500/20 text-amber-500 mb-6">
                    <TrendingUp class="h-6 w-6" />
                </div>
                <h4 class="text-xl font-bold leading-tight">Insight Eksekutif</h4>
                <p class="mt-4 text-sm leading-relaxed text-stone-400">
                    Bulan ini, **{{ brandPerformance[0]?.name }}** memberikan kontribusi terbesar terhadap kas grup. 
                    Pastikan piutang di **{{ brandPerformance[1]?.name || 'brand lain' }}** segera tertagih untuk menjaga arus kas tetap stabil.
                </p>
                <div class="mt-8 pt-8 border-t border-white/10">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-stone-500">Rasio Pertumbuhan</p>
                    <p class="mt-2 text-2xl font-bold">+12.4% <span class="text-xs font-normal text-stone-500">v last month</span></p>
                </div>
            </div>

            <article class="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm">
                <h4 class="text-sm font-bold uppercase tracking-widest text-stone-400 mb-6">Aksi Master</h4>
                <div class="space-y-3">
                    <button class="w-full flex items-center justify-between rounded-2xl border border-stone-100 bg-stone-50 p-4 text-left transition hover:border-stone-400">
                        <span class="text-sm font-semibold text-stone-700">Ekspor Konsolidasi (PDF)</span>
                        <ChevronRight class="h-4 w-4 text-stone-300" />
                    </button>
                    <button class="w-full flex items-center justify-between rounded-2xl border border-stone-100 bg-stone-50 p-4 text-left transition hover:border-stone-400">
                        <span class="text-sm font-semibold text-stone-700">Audit Keamanan Grup</span>
                        <ChevronRight class="h-4 w-4 text-stone-300" />
                    </button>
                </div>
            </article>
        </aside>
      </div>
    </div>
  </WorkspaceLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3'
import {
  TrendingUp,
  ChevronRight,
  BarChart3,
  PieChart,
  ArrowUpRight
} from 'lucide-vue-next'
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue'

const props = defineProps({
  workspace: Object,
  summary: Object,
  brandPerformance: Array,
  revenueTrend: Array,
  workspaces: Array,
})
</script>
