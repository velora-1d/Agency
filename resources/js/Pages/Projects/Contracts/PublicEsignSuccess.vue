<template>
  <div class="min-h-screen bg-stone-50 px-4 py-12 sm:px-6 lg:px-8 flex items-center justify-center">
    <div class="max-w-md w-full text-center space-y-8">
      <div class="rounded-[2.5rem] bg-white p-12 shadow-2xl border border-stone-100">
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
          <CheckCircle2 class="h-10 w-10" />
        </div>
        <h2 class="mt-8 text-3xl font-bold tracking-tight text-stone-900">Signed Successfully!</h2>
        <p class="mt-4 text-sm text-stone-600 leading-relaxed">
          Kontrak <span class="font-bold text-stone-900">"{{ contract_title }}"</span> telah berhasil ditandatangani secara digital pada <span class="font-semibold text-stone-900">{{ signed_at }}</span>.
        </p>
        <div class="mt-10">
          <p class="text-xs text-stone-400">Salinan dokumen ini telah dikirimkan ke pihak terkait.</p>
          <p class="mt-1 text-xs text-stone-400 font-medium italic">You can now close this window.</p>
        </div>
      </div>
      <p class="text-xs text-stone-400 font-bold uppercase tracking-widest tracking-[0.2em]">Secure Signature System</p>
    </div>
  </div>
</template>

<script setup>
import { CheckCircle2 } from 'lucide-vue-next'

defineProps({
  contract_title: String,
  signed_at: String,
})
</script>
