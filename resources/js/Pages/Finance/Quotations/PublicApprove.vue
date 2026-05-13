<template>
  <main class="min-h-screen bg-[radial-gradient(circle_at_top_right,#fef3c7_0%,transparent_18%),linear-gradient(180deg,#fafaf9_0%,#f5f5f4_100%)] px-4 py-10 text-stone-900">
    <div class="mx-auto max-w-6xl space-y-6">
      <section class="rounded-[2rem] bg-stone-950 p-8 text-white shadow-[0_28px_90px_rgba(28,25,23,0.22)]">
        <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-amber-200/75">Client Approval Portal</p>
        <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
          <div class="max-w-3xl">
            <h1 class="text-4xl font-semibold tracking-[-0.06em]">{{ quotation.title }}</h1>
            <div class="mt-4 flex flex-wrap gap-3 text-sm text-stone-300">
              <span>{{ quotation.number }}</span>
              <span>{{ quotation.client_name || 'General proposal' }}</span>
              <span>{{ quotation.valid_until_label || 'Tanpa kedaluwarsa' }}</span>
            </div>
          </div>
          <div class="rounded-[1.4rem] border border-white/10 bg-white/10 px-4 py-3">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-300">Total Proposal</p>
            <p class="mt-2 text-3xl font-semibold tracking-[-0.05em]">{{ quotation.total_label }}</p>
          </div>
        </div>
      </section>

      <section class="grid gap-6 xl:grid-cols-[1fr_0.88fr]">
        <article class="space-y-6 rounded-[2rem] border border-stone-200 bg-white p-6 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-[1.4rem] border border-stone-200 bg-stone-50 p-4">
              <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Surat Pengantar</p>
              <p class="mt-3 whitespace-pre-wrap text-sm leading-6 text-stone-700">{{ quotation.cover_letter || 'Belum diisi.' }}</p>
            </div>
            <div class="rounded-[1.4rem] border border-stone-200 bg-stone-50 p-4">
              <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Ruang Lingkup Pekerjaan</p>
              <p class="mt-3 whitespace-pre-wrap text-sm leading-6 text-stone-700">{{ quotation.scope_of_work || 'Belum diisi.' }}</p>
            </div>
            <div class="rounded-[1.4rem] border border-stone-200 bg-stone-50 p-4">
              <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Timeline</p>
              <p class="mt-3 whitespace-pre-wrap text-sm leading-6 text-stone-700">{{ quotation.timeline || 'Belum diisi.' }}</p>
            </div>
          </div>

          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Itemized Costs</p>
            <div class="mt-4 space-y-3">
              <article v-for="(item, index) in quotation.items" :key="`${item.name}-${index}`" class="rounded-[1.4rem] border border-stone-200 bg-stone-50 p-4">
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div>
                    <div class="flex flex-wrap items-center gap-2">
                      <p class="text-sm font-semibold text-stone-950">{{ item.name }}</p>
                      <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-stone-500">{{ item.category || 'custom' }}</span>
                    </div>
                    <p class="mt-2 text-sm leading-6 text-stone-600">{{ item.description || 'No description' }}</p>
                    <p class="mt-2 text-xs text-stone-500">{{ item.quantity }} {{ item.unit || 'unit' }} x {{ item.unit_price_label }}</p>
                  </div>
                  <div class="text-right">
                    <p class="text-sm font-semibold text-stone-950">{{ item.subtotal_label }}</p>
                    <p class="mt-2 text-xs text-stone-500">Discount {{ item.discount_amount_label }}</p>
                  </div>
                </div>
              </article>
            </div>
          </div>

          <div class="rounded-[1.5rem] border border-stone-200 bg-white p-5">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Terms & Conditions</p>
            <div class="mt-4 whitespace-pre-wrap rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4 text-sm leading-6 text-stone-700">{{ quotation.terms_conditions || 'Belum ada terms & conditions.' }}</div>
          </div>
        </article>

        <aside class="space-y-6">
          <section class="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Financial Summary</p>
            <div class="mt-5 grid gap-3 sm:grid-cols-2">
              <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                <p class="text-xs uppercase tracking-[0.16em] text-stone-400">Subtotal</p>
                <p class="mt-2 text-lg font-semibold text-stone-950">{{ quotation.subtotal_label }}</p>
              </div>
              <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                <p class="text-xs uppercase tracking-[0.16em] text-stone-400">Discount</p>
                <p class="mt-2 text-lg font-semibold text-stone-950">{{ quotation.discount_amount_label }}</p>
              </div>
              <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                <p class="text-xs uppercase tracking-[0.16em] text-stone-400">Tax</p>
                <p class="mt-2 text-lg font-semibold text-stone-950">{{ quotation.tax_amount_label }}</p>
              </div>
              <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                <p class="text-xs uppercase tracking-[0.16em] text-stone-400">Total</p>
                <p class="mt-2 text-lg font-semibold text-stone-950">{{ quotation.total_label }}</p>
              </div>
              <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                <p class="text-xs uppercase tracking-[0.16em] text-stone-400">DP</p>
                <p class="mt-2 text-lg font-semibold text-stone-950">{{ quotation.dp_amount_label }}</p>
              </div>
              <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                <p class="text-xs uppercase tracking-[0.16em] text-stone-400">Remaining</p>
                <p class="mt-2 text-lg font-semibold text-stone-950">{{ quotation.remaining_amount_label }}</p>
              </div>
            </div>
          </section>

          <section class="rounded-[2rem] border border-stone-200 bg-stone-950 p-6 text-white shadow-[0_20px_60px_rgba(28,25,23,0.14)]">
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-amber-200/70">Approval Action</p>
            <h2 class="mt-3 text-2xl font-semibold tracking-[-0.04em]">Setujui atau tolak proposal ini.</h2>
            <p class="mt-3 text-sm leading-6 text-stone-300">Keputusan Anda akan langsung tercatat pada sistem agency.</p>

            <div class="mt-6 grid gap-3">
              <button @click="submitDecision('approved')" :disabled="form.processing" class="rounded-2xl bg-emerald-500 px-5 py-4 text-sm font-semibold text-white transition hover:bg-emerald-400 disabled:opacity-60">
                Approve Proposal
              </button>
              <button @click="submitDecision('rejected')" :disabled="form.processing" class="rounded-2xl border border-white/10 bg-white/10 px-5 py-4 text-sm font-semibold text-white transition hover:bg-white/15 disabled:opacity-60">
                Reject Proposal
              </button>
            </div>

            <div class="mt-6 rounded-[1.4rem] border border-white/10 bg-white/5 px-4 py-4 text-sm leading-6 text-stone-300">
              Signature section pada proposal ini menggunakan digital approval. Tindakan approve dianggap sebagai persetujuan client terhadap scope dan biaya proposal.
            </div>
          </section>
        </aside>
      </section>
    </div>
  </main>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  quotation: { type: Object, required: true },
  token: { type: String, required: true },
})

const form = useForm({
  decision: 'approved',
})

function submitDecision(decision) {
  form.decision = decision
  form.post(`/quotations/approve/${encodeURIComponent(props.token)}`)
}
</script>
