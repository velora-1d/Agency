<template>
  <Head :title="`Detail Newsletter: ${newsletter.subject}`" />

  <WorkspaceLayout :title="newsletter.subject" subtitle="Analitik pengiriman, keterbacaan, dan pratinjau isi newsletter email.">
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
          <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Total Penerima</p>
          <p class="mt-3 text-2xl font-semibold text-stone-950">{{ newsletter.recipients_count }}</p>
          <p class="mt-1 text-xs text-stone-500">Pelanggan aktif</p>
        </div>
        <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5 shadow-sm">
          <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Rasio Dibuka (Open)</p>
          <p class="mt-3 text-2xl font-semibold text-stone-950">{{ newsletter.open_rate }}%</p>
          <p class="mt-1 text-xs text-emerald-600 font-medium">Bagus</p>
        </div>
        <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5 shadow-sm">
          <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Rasio Klik</p>
          <p class="mt-3 text-2xl font-semibold text-stone-950">{{ newsletter.click_rate }}%</p>
          <p class="mt-1 text-xs text-stone-500">CTA interaction</p>
        </div>
        <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5 shadow-sm">
          <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Status</p>
          <div class="mt-3">
              <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest" :class="statusClass(newsletter.status)">
                {{ translateStatus(newsletter.status) }}
              </span>
          </div>
          <p class="mt-2 text-xs text-stone-500">{{ newsletter.sent_at ? `Terkirim: ${newsletter.sent_at}` : `Jadwal: ${newsletter.scheduled_at || '-'}` }}</p>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <section class="lg:col-span-2 space-y-6">
            <article class="rounded-[2.2rem] border border-stone-200 bg-white shadow-sm overflow-hidden">
                <div class="bg-stone-50 px-8 py-5 border-b border-stone-100 flex items-center justify-between">
                    <h4 class="text-sm font-bold uppercase tracking-widest text-stone-500">Pratinjau Email</h4>
                    <button class="text-xs font-bold text-stone-950 underline decoration-amber-400 underline-offset-4">Kirim Tes ke Saya</button>
                </div>
                <div class="p-8">
                    <div class="rounded-2xl border border-stone-100 bg-white p-8 shadow-inner min-h-[400px]">
                        <h1 class="text-2xl font-bold text-stone-900 mb-6">{{ newsletter.subject }}</h1>
                        <div class="prose prose-stone max-w-none text-stone-700 leading-relaxed whitespace-pre-wrap" v-html="newsletter.content"></div>
                    </div>
                </div>
            </article>
        </section>

        <aside class="space-y-6">
            <article class="rounded-[2.2rem] border border-stone-200 bg-white p-6 shadow-sm">
                <h4 class="text-sm font-bold uppercase tracking-widest text-stone-400 mb-4">Aksi Buletin</h4>
                <div class="space-y-3">
                    <button class="w-full flex items-center justify-between rounded-2xl border border-stone-100 bg-stone-50 p-4 text-left transition hover:border-stone-400">
                        <div class="flex items-center gap-3">
                            <Send class="h-4 w-4 text-stone-400" />
                            <span class="text-sm font-semibold text-stone-700">Kirim Sekarang</span>
                        </div>
                        <ChevronRight class="h-4 w-4 text-stone-300" />
                    </button>
                    <button class="w-full flex items-center justify-between rounded-2xl border border-stone-100 bg-stone-50 p-4 text-left transition hover:border-stone-400">
                        <div class="flex items-center gap-3">
                            <Users class="h-4 w-4 text-stone-400" />
                            <span class="text-sm font-semibold text-stone-700">Pilih Audiens</span>
                        </div>
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
  ArrowLeft,
  Send,
  Users,
  ChevronRight,
  Mail
} from 'lucide-vue-next'
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue'

const props = defineProps({
  workspace: Object,
  newsletter: Object,
})

function translateStatus(status) {
    const map = {
        draft: 'Draf',
        scheduled: 'Terjadwal',
        sending: 'Mengirim',
        sent: 'Terkirim'
    }
    return map[status] || status
}

function statusClass(status) {
    const map = {
        draft: 'bg-stone-100 text-stone-600',
        scheduled: 'bg-blue-100 text-blue-700',
        sending: 'bg-amber-100 text-amber-700',
        sent: 'bg-emerald-100 text-emerald-700'
    }
    return map[status] || 'bg-stone-100 text-stone-600'
}
</script>
