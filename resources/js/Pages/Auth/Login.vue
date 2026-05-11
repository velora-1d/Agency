<template>
  <Head title="Login" />

  <section class="relative min-h-screen overflow-hidden bg-[#f6ecdf] text-stone-950">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(185,102,34,0.22),_transparent_28%),radial-gradient(circle_at_bottom_right,_rgba(32,84,92,0.18),_transparent_24%),linear-gradient(180deg,_#fbf4eb_0%,_#f1e3d0_100%)]" />
    <div class="absolute -left-20 top-24 h-72 w-72 rounded-full bg-[#d09d57]/20 blur-3xl" />
    <div class="absolute bottom-10 right-0 h-96 w-96 rounded-full bg-[#3a6a6d]/12 blur-3xl" />

    <div class="relative mx-auto grid min-h-screen max-w-[1480px] gap-8 px-4 py-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(420px,0.85fr)] lg:px-8">
      <section class="hidden rounded-[2.4rem] border border-white/40 bg-[#1d1714] p-8 text-stone-50 shadow-[0_30px_90px_rgba(28,20,16,0.24)] lg:flex lg:flex-col lg:justify-between">
        <div>
          <div class="inline-flex rounded-full border border-white/10 bg-white/5 px-4 py-2 text-[11px] uppercase tracking-[0.32em] text-amber-200/90">
            Kantor Digital
          </div>
          <p class="mt-8 text-xs uppercase tracking-[0.36em] text-amber-200/70">Agency Control Room</p>
          <h1 class="mt-5 max-w-3xl text-6xl font-semibold leading-[0.94] tracking-[-0.06em]" style="font-family: var(--font-display)">
            Masuk ke workspace tanpa distraksi, cukup email dan password.
          </h1>
          <p class="mt-6 max-w-2xl text-base leading-8 text-stone-300">
            Satu pintu masuk untuk operasional Velora dan Maven. Fokusnya cepat, bersih, dan langsung ke dashboard kerja begitu sesi valid.
          </p>
        </div>

        <div class="grid gap-4 xl:grid-cols-3">
          <article class="rounded-[1.8rem] border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
            <p class="text-[11px] uppercase tracking-[0.24em] text-stone-400">Workspace</p>
            <p class="mt-3 text-2xl font-semibold tracking-[-0.04em]">2 Agency</p>
            <p class="mt-2 text-sm leading-6 text-stone-300">Velora dan Maven tetap terisolasi per akses user.</p>
          </article>
          <article class="rounded-[1.8rem] border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
            <p class="text-[11px] uppercase tracking-[0.24em] text-stone-400">Masuk</p>
            <p class="mt-3 text-2xl font-semibold tracking-[-0.04em]">Email + PW</p>
            <p class="mt-2 text-sm leading-6 text-stone-300">Tanpa social login. Flow auth dibuat ringkas dan jelas.</p>
          </article>
          <article class="rounded-[1.8rem] border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
            <p class="text-[11px] uppercase tracking-[0.24em] text-stone-400">Output</p>
            <p class="mt-3 text-2xl font-semibold tracking-[-0.04em]">Direct Access</p>
            <p class="mt-2 text-sm leading-6 text-stone-300">Setelah valid, user langsung diarahkan ke workspace yang bisa diakses.</p>
          </article>
        </div>
      </section>

      <section class="flex items-center justify-center">
        <div class="w-full max-w-[520px] rounded-[2.2rem] border border-stone-200/80 bg-white/90 p-6 shadow-[0_24px_80px_rgba(86,58,32,0.16)] backdrop-blur xl:p-8">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs uppercase tracking-[0.32em] text-[#8f5724]">Secure Login</p>
              <h2 class="mt-3 text-4xl font-semibold tracking-[-0.05em] text-stone-950" style="font-family: var(--font-display)">
                Welcome back.
              </h2>
              <p class="mt-3 max-w-md text-sm leading-7 text-stone-600">
                Masuk dengan akun internal yang sudah diizinkan. Tidak ada login Google di halaman ini.
              </p>
            </div>

            <div class="hidden rounded-full border border-stone-200 bg-stone-50 px-4 py-2 text-[11px] uppercase tracking-[0.22em] text-stone-500 sm:block">
              Internal Access
            </div>
          </div>

          <div class="mt-8 rounded-[1.8rem] border border-[#e8d9c5] bg-[#fbf7f1] p-4">
            <p class="text-[11px] uppercase tracking-[0.22em] text-stone-500">Demo Accounts</p>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
              <button
                v-for="account in demoAccounts"
                :key="account.email"
                type="button"
                class="rounded-[1.2rem] border border-stone-200 bg-white px-4 py-3 text-left transition hover:-translate-y-0.5 hover:border-[#d6ae78]"
                @click="fillDemo(account.email)"
              >
                <p class="text-sm font-semibold text-stone-950">{{ account.label }}</p>
                <p class="mt-1 truncate text-xs uppercase tracking-[0.18em] text-stone-500">{{ account.email }}</p>
              </button>
            </div>
          </div>

          <form class="mt-8 space-y-5" @submit.prevent="submit">
            <div>
              <label for="email" class="text-xs uppercase tracking-[0.22em] text-stone-500">Email</label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                autocomplete="email"
                class="mt-2 w-full rounded-[1.35rem] border border-stone-200 bg-[#fffdfa] px-4 py-4 text-base text-stone-950 outline-none transition placeholder:text-stone-400 focus:border-[#c9802a] focus:ring-4 focus:ring-[#e8bb84]/30"
                placeholder="owner@kantordigital.test"
              />
              <p v-if="form.errors.email" class="mt-2 text-sm text-rose-700">{{ form.errors.email }}</p>
            </div>

            <div>
              <div class="flex items-center justify-between gap-4">
                <label for="password" class="text-xs uppercase tracking-[0.22em] text-stone-500">Password</label>
                <button
                  type="button"
                  class="text-xs uppercase tracking-[0.18em] text-stone-500 transition hover:text-stone-950"
                  @click="showPassword = !showPassword"
                >
                  {{ showPassword ? 'Hide' : 'Show' }}
                </button>
              </div>
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                class="mt-2 w-full rounded-[1.35rem] border border-stone-200 bg-[#fffdfa] px-4 py-4 text-base text-stone-950 outline-none transition placeholder:text-stone-400 focus:border-[#c9802a] focus:ring-4 focus:ring-[#e8bb84]/30"
                placeholder="Masukkan password"
              />
              <p v-if="form.errors.password" class="mt-2 text-sm text-rose-700">{{ form.errors.password }}</p>
            </div>

            <label class="flex items-center gap-3 rounded-[1.2rem] border border-stone-200 bg-stone-50 px-4 py-3">
              <input v-model="form.remember" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-[#d39c57]" />
              <span class="text-sm text-stone-700">Tetap masuk di device ini</span>
            </label>

            <button
              type="submit"
              class="group inline-flex w-full items-center justify-between rounded-[1.45rem] bg-stone-950 px-5 py-4 text-left text-stone-50 transition hover:bg-[#2a211d] disabled:cursor-not-allowed disabled:opacity-70"
              :disabled="form.processing"
            >
              <span>
                <span class="block text-[11px] uppercase tracking-[0.26em] text-amber-200/80">Authentication</span>
                <span class="mt-1 block text-lg font-semibold tracking-[-0.03em]">
                  {{ form.processing ? 'Memvalidasi akses...' : 'Masuk ke workspace' }}
                </span>
              </span>
              <span class="rounded-full border border-white/10 bg-white/5 px-3 py-2 text-[11px] uppercase tracking-[0.22em] text-amber-100">
                Go
              </span>
            </button>
          </form>

          <div class="mt-8 grid gap-3 sm:grid-cols-3">
            <article class="rounded-[1.35rem] border border-stone-200 bg-stone-50 px-4 py-4">
              <p class="text-[11px] uppercase tracking-[0.2em] text-stone-500">Method</p>
              <p class="mt-2 text-sm font-semibold text-stone-950">Email & Password</p>
            </article>
            <article class="rounded-[1.35rem] border border-stone-200 bg-stone-50 px-4 py-4">
              <p class="text-[11px] uppercase tracking-[0.2em] text-stone-500">Routing</p>
              <p class="mt-2 text-sm font-semibold text-stone-950">Session Laravel</p>
            </article>
            <article class="rounded-[1.35rem] border border-stone-200 bg-stone-50 px-4 py-4">
              <p class="text-[11px] uppercase tracking-[0.2em] text-stone-500">Access</p>
              <p class="mt-2 text-sm font-semibold text-stone-950">Workspace Scoped</p>
            </article>
          </div>
        </div>
      </section>
    </div>
  </section>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  demoAccounts: {
    type: Array,
    default: () => [],
  },
})

const showPassword = ref(false)

const form = useForm({
  email: '',
  password: '',
  remember: true,
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
