<template>
  <Head :title="`Kampanye: ${campaign.name}`" />

  <WorkspaceLayout :title="campaign.name" :subtitle="`Detail analitik, pengeluaran, dan performa kampanye ${campaign.type}.` ">
    <template #actions>
      <div class="flex gap-2">
        <button
          type="button"
          @click="router.get(route('workspace.marketing.index', workspace.slug))"
          class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-600 transition hover:bg-stone-50 hover:text-stone-950"
        >
          <ArrowLeft class="h-4 w-4" />
          <span>Kembali</span>
        </button>
      </div>
    </template>

    <div class="space-y-6">
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5 shadow-sm">
          <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Total Pengeluaran</p>
          <p class="mt-3 text-2xl font-semibold text-stone-950">{{ formatCurrency(campaign.spend) }}</p>
          <p class="mt-1 text-xs text-stone-500">Dari anggaran {{ formatCurrency(campaign.budget) }}</p>
        </div>
        <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5 shadow-sm">
          <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">ROI %</p>
          <p class="mt-3 text-2xl font-semibold text-stone-950">{{ campaign.roi_percentage }}%</p>
          <p class="mt-1 text-xs text-emerald-600 font-medium">Performa Positif</p>
        </div>
        <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5 shadow-sm">
          <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Leads Dihasilkan</p>
          <p class="mt-3 text-2xl font-semibold text-stone-950">{{ campaign.leads_generated }}</p>
          <p class="mt-1 text-xs text-stone-500">Kontak potensial baru</p>
        </div>
        <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5 shadow-sm">
          <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Sesi Trafik</p>
          <p class="mt-3 text-2xl font-semibold text-stone-950">{{ campaign.traffic_sessions }}</p>
          <p class="mt-1 text-xs text-stone-500">Klik / Kunjungan unik</p>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <section class="lg:col-span-2 space-y-6">
            <article class="rounded-[2.2rem] border border-stone-200 bg-white p-8 shadow-sm">
                <h4 class="text-sm font-bold uppercase tracking-widest text-stone-400 mb-6">Ringkasan Eksekutif</h4>
                <div class="prose prose-stone max-w-none text-stone-700 leading-relaxed">
                    <p>Kampanye <strong>{{ campaign.name }}</strong> yang berjalan di platform <strong>{{ campaign.type }}</strong> saat ini berstatus <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-700 uppercase">{{ translateStatus(campaign.status) }}</span>.</p>
                    <p class="mt-4">Dimulai pada {{ campaign.start_date || '-' }} dan dijadwalkan berakhir pada {{ campaign.end_date || '-' }}. Sumber utama trafik berasal dari <strong>{{ campaign.primary_source }}</strong>.</p>
                </div>

                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-stone-100 bg-stone-50 p-5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Efisiensi Biaya</p>
                        <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-stone-200">
                            <div class="h-full bg-stone-950 transition-all duration-1000" :style="{ width: `${(campaign.spend / campaign.budget) * 100}%` }"></div>
                        </div>
                        <p class="mt-2 text-xs font-bold text-stone-700">{{ Math.round((campaign.spend / campaign.budget) * 100) }}% Anggaran Terpakai</p>
                    </div>
                    <div class="rounded-2xl border border-stone-100 bg-stone-50 p-5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Referensi Eksternal</p>
                        <p class="mt-3 text-sm font-mono text-stone-600 truncate">{{ campaign.external_reference || 'Tidak ada referensi ID' }}</p>
                    </div>
                </div>
            </article>
        </section>

        <aside class="space-y-6">
            <article class="rounded-[2.2rem] border border-stone-200 bg-white p-6 shadow-sm">
                <h4 class="text-sm font-bold uppercase tracking-widest text-stone-400 mb-4">Aksi Cepat</h4>
                <div class="space-y-3">
                    <button class="w-full flex items-center justify-between rounded-2xl border border-stone-100 bg-stone-50 p-4 text-left transition hover:border-stone-400">
                        <div class="flex items-center gap-3">
                            <Send class="h-4 w-4 text-stone-400" />
                            <span class="text-sm font-semibold text-stone-700">Kirim Blast WA</span>
                        </div>
                        <ChevronRight class="h-4 w-4 text-stone-300" />
                    </button>
                    <button class="w-full flex items-center justify-between rounded-2xl border border-stone-100 bg-stone-50 p-4 text-left transition hover:border-stone-400">
                        <div class="flex items-center gap-3">
                            <FileText class="h-4 w-4 text-stone-400" />
                            <span class="text-sm font-semibold text-stone-700">Ekspor Laporan</span>
                        </div>
                        <ChevronRight class="h-4 w-4 text-stone-300" />
                    </button>
                </div>
            </article>

            <div class="rounded-[2.2rem] bg-stone-950 p-8 text-white shadow-xl shadow-stone-900/20">
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">Tips Performa</p>
                <h5 class="mt-4 text-lg font-bold leading-snug">ROI di atas 10% adalah indikator sehat untuk agensi.</h5>
                <p class="mt-3 text-sm leading-relaxed text-stone-400">Pastikan pelacakan UTM di setiap link kampanye terpasang dengan benar untuk akurasi data di sistem ini.</p>
            </div>
        </aside>
      </div>
    </div>
  </WorkspaceLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3'
import {
  ArrowLeft,
  Send,
  FileText,
  ChevronRight,
  Target,
  Zap,
  Users
} from 'lucide-vue-next'
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue'

const props = defineProps({
  workspace: Object,
  campaign: Object,
})

function formatCurrency(value) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value || 0)
}

function translateStatus(status) {
    return { planning: 'Perencanaan', active: 'Aktif', completed: 'Selesai' }[status] || status
}
</script>
