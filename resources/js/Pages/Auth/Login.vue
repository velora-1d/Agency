<template>
  <Head title="Masuk" />

  <section class="flex h-screen w-full items-stretch overflow-hidden bg-[#f7f1e8]">
    <!-- Left: Branding Area -->
    <div class="relative hidden w-[45%] flex-col justify-between overflow-hidden bg-[#1f1a17] p-12 text-stone-50 lg:flex">
      <div class="absolute inset-0 opacity-20 [background-image:radial-gradient(#5a4632_1.5px,transparent_1.5px)] [background-size:28px_28px]"></div>

      <div class="relative z-10">
        <div class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-2xl bg-amber-200"></div>
          <p class="text-lg font-bold uppercase tracking-[0.4em] text-amber-300">Kantor Digital</p>
        </div>

        <div class="mt-28 max-w-lg">
          <h1 class="text-5xl font-semibold leading-[1.05] tracking-tighter lg:text-6xl">
            Masuk ke workspace tanpa distraksi, cukup email dan kata sandi.
          </h1>
        </div>
      </div>

      <div class="relative z-10 flex flex-wrap gap-4">
        <div class="w-[200px] rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur">
          <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-stone-500">Metode</p>
          <p class="mt-3 text-2xl font-semibold tracking-[-0.04em]">Email + PW</p>
          <p class="mt-2 text-sm leading-6 text-stone-300">Tanpa login sosial. Alur autentikasi dibuat ringkas dan jelas.</p>
        </div>
      </div>
    </div>

    <!-- Right: Auth Form -->
    <main class="flex flex-1 flex-col items-center justify-center p-6 lg:p-12">
      <div class="w-full max-w-[440px]">
        <header class="mb-10 text-center lg:hidden">
          <p class="text-sm font-bold uppercase tracking-[0.4em] text-[#8f5724]">Kantor Digital</p>
        </header>

        <div class="rounded-[2.5rem] border border-stone-200/60 bg-white/80 p-8 shadow-[0_24px_60px_rgba(40,30,20,0.06)] backdrop-blur-xl lg:p-10">
          <div class="mb-10">
            <p class="text-xs uppercase tracking-[0.32em] text-[#8f5724]">Masuk Aman</p>
            <h2 class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">Selamat Datang</h2>
            <p class="mt-3 text-sm leading-relaxed text-stone-500">
              Masuk dengan akun internal yang sudah diizinkan. Tidak ada login Google di halaman ini.
            </p>
          </div>

          <form @submit.prevent="submit" class="space-y-6">
            <div class="space-y-1">
              <div v-if="demoAccounts.length" class="mb-6 grid gap-2 sm:grid-cols-2">
                <button
                  v-for="account in demoAccounts"
                  :key="account.email"
                  type="button"
                  class="rounded-2xl border border-stone-100 bg-stone-50 p-3 text-left transition hover:border-amber-300 hover:bg-white"
                  @click="fillDemo(account.email)"
                >
                  <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-amber-700">{{ account.role }}</p>
                  <p class="mt-1 truncate text-xs uppercase tracking-[0.18em] text-stone-500">{{ account.email }}</p>
                </button>
              </div>

              <label for="email" class="text-xs uppercase tracking-[0.22em] text-stone-500">Email</label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                autocomplete="email"
                required
                class="w-full rounded-2xl border border-stone-200 bg-stone-50/50 px-5 py-4 text-stone-900 outline-none transition-all focus:border-stone-400 focus:bg-white focus:ring-4 focus:ring-stone-100"
              />
              <p v-if="form.errors.email" class="mt-2 text-sm text-rose-700">{{ form.errors.email }}</p>
            </div>

            <div class="space-y-1">
              <div class="flex items-center justify-between">
                <label for="password" class="text-xs uppercase tracking-[0.22em] text-stone-500">Kata Sandi</label>
                <button
                  type="button"
                  class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400 transition hover:text-stone-950"
                  @click="showPassword = !showPassword"
                >
                  {{ showPassword ? 'Sembunyikan' : 'Tampilkan' }}
                </button>
              </div>
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                required
                class="w-full rounded-2xl border border-stone-200 bg-stone-50/50 px-5 py-4 text-stone-900 outline-none transition-all focus:border-stone-400 focus:bg-white focus:ring-4 focus:ring-stone-100"
              />
              <p v-if="form.errors.password" class="mt-2 text-sm text-rose-700">{{ form.errors.password }}</p>
            </div>

            <div class="pt-4">
              <button
                type="submit"
                :disabled="form.processing"
                class="group relative flex w-full items-center justify-center gap-3 overflow-hidden rounded-2xl bg-stone-950 px-8 py-5 text-sm font-bold tracking-[0.24em] text-white transition-all active:scale-[0.98] disabled:opacity-50"
              >
                <div class="absolute inset-0 bg-[linear-gradient(45deg,transparent_25%,rgba(255,255,255,0.05)_50%,transparent_75%)] [background-size:250%_250%] transition-all duration-1000 group-hover:[background-position:100%_100%]"></div>
                <span class="uppercase">Masuk Sekarang</span>
                <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-1" />
              </button>
            </div>
          </form>

          <div class="mt-10 border-t border-stone-100 pt-10 text-center">
            <div class="inline-flex flex-col items-center">
              <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                <ShieldCheck class="h-5 w-5" />
              </div>
              <p class="mt-2 text-sm font-semibold text-stone-950">Email & Kata Sandi</p>
              <p class="mt-1 text-[11px] leading-relaxed text-stone-400">
                Akses dibatasi hanya untuk personel yang terdaftar.
              </p>
            </div>
          </div>
        </div>

        <p class="mt-12 text-center text-xs tracking-[0.18em] text-stone-400">
          © 2026 KANTOR DIGITAL
        </p>
      </div>
    </main>
  </section>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { ArrowRight, ShieldCheck } from 'lucide-vue-next'

defineProps({
  demoAccounts: {
    type: Array,
    default: () => [],
  },
})

const showPassword = ref(false)

const form = useForm({
  email: '',
  password: '',
})

function fillDemo(email) {
  form.email = email
  if (!form.password) {
    form.password = 'password'
  }
}

function submit() {
  form.post('/login', {
    onFinish: () => {
      form.password = ''
      showPassword.value = false
    },
  })
}
</script>
