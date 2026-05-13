<template>
  <div class="min-h-screen bg-stone-50 px-4 py-12 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-3xl">
      <!-- Header -->
      <div class="text-center">
        <h2 v-if="contract.workspace_logo" class="flex justify-center mb-6">
            <img :src="contract.workspace_logo" class="h-12 w-auto" alt="Logo" />
        </h2>
        <p class="text-xs font-bold uppercase tracking-widest text-stone-400">Permintaan Tanda Tangan Digital</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-stone-950 sm:text-4xl">{{ contract.title }}</h1>
        <p class="mt-4 text-sm text-stone-600">
          Diterbitkan oleh <span class="font-semibold text-stone-900">{{ contract.workspace_name }}</span> untuk <span class="font-semibold text-stone-900">{{ contract.client_name }}</span>
        </p>
      </div>

      <!-- Contract Content -->
      <div class="mt-12 overflow-hidden rounded-[2rem] border border-stone-200 bg-white shadow-xl">
        <div class="p-8 sm:p-12">
          <div class="prose prose-stone max-w-none text-stone-700 leading-relaxed whitespace-pre-wrap">
            {{ contract.content }}
          </div>
        </div>

        <!-- Signing Form -->
        <div class="border-t border-stone-100 bg-stone-50/50 p-8 sm:p-12">
          <h3 class="text-lg font-bold text-stone-900">Tanda Tangani Secara Digital</h3>
          <p class="mt-1 text-sm text-stone-500">Dengan menandatangani secara digital, Anda menyetujui seluruh isi kontrak di atas.</p>

          <form @submit.prevent="submitSignature" class="mt-8 space-y-6">
            <div class="grid gap-6 sm:grid-cols-2">
              <div class="space-y-2">
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-stone-400">Nama Lengkap</label>
                <input 
                  type="text" 
                  id="name" 
                  v-model="form.name"
                 
                  class="block w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 shadow-sm outline-none focus:border-stone-950"
                  required
                />
                <p v-if="form.errors.name" class="text-xs text-rose-600">{{ form.errors.name }}</p>
              </div>
            </div>

            <div class="relative flex items-start">
              <div class="flex h-6 items-center">
                <input 
                  id="agreement" 
                  type="checkbox" 
                  v-model="form.agreement"
                  class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-950"
                  required
                />
              </div>
              <div class="ml-3 text-sm leading-6">
                <label for="agreement" class="font-medium text-stone-700">Saya menyatakan bahwa saya memiliki wewenang untuk menandatangani dokumen ini.</label>
                <p v-if="form.errors.agreement" class="text-xs text-rose-600">{{ form.errors.agreement }}</p>
              </div>
            </div>

            <div class="flex items-center justify-between gap-4 pt-4">
              <div class="text-xs text-stone-400">
                <p>Alamat IP: Tercatat Otomatis</p>
                <p>Token Keamanan: {{ token.substring(0, 8) }}...</p>
              </div>
              <button 
                type="submit" 
                :disabled="form.processing"
                class="rounded-2xl bg-stone-950 px-8 py-3 text-sm font-bold text-white shadow-lg transition-all hover:bg-stone-800 disabled:opacity-50"
              >
                {{ form.processing ? 'Menandatangani...' : 'Tandatangani Kontrak' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Footer -->
      <div class="mt-12 text-center text-xs text-stone-400">
        <p>&copy; 2026 {{ contract.workspace_name }}. Sistem Tanda Tangan Digital Aman.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  contract: Object,
  token: String,
})

const form = useForm({
  name: '',
  agreement: false,
})

function submitSignature() {
  form.post(`/contracts/esign/${props.token}`, {
    onFinish: () => form.reset('agreement'),
  })
}
</script>
