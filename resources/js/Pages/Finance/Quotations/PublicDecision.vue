<template>
  <main class="flex min-h-screen items-center justify-center bg-[linear-gradient(180deg,#fafaf9_0%,#f5f5f4_100%)] px-4 py-10 text-stone-900">
    <section class="w-full max-w-2xl rounded-[2rem] border border-stone-200 bg-white p-8 text-center shadow-[0_28px_90px_rgba(28,25,23,0.12)]">
      <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-stone-400">Decision Saved</p>
      <h1 class="mt-4 text-4xl font-semibold tracking-[-0.06em] text-stone-950">{{ title }}</h1>
      <p class="mt-4 text-sm leading-7 text-stone-600">
        Keputusan Anda untuk proposal <span class="font-semibold text-stone-950">{{ quotation_title }}</span> sudah tercatat di sistem.
      </p>
      <div class="mt-6 inline-flex rounded-full px-4 py-2 text-xs font-bold uppercase tracking-[0.18em]" :class="badgeClass">
        {{ label }}
      </div>
    </section>
  </main>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  quotation_title: { type: String, required: true },
  decision: { type: String, required: true },
})

const label = computed(() => props.decision === 'approved' ? 'Approved' : 'Rejected')
const title = computed(() => props.decision === 'approved' ? 'Proposal Approved' : 'Proposal Rejected')
const badgeClass = computed(() => props.decision === 'approved'
  ? 'bg-emerald-100 text-emerald-700'
  : 'bg-rose-100 text-rose-700')
</script>
