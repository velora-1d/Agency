<template>
  <Head title="Masuk Portal" />

  <section class="flex min-h-screen w-full items-stretch overflow-hidden bg-[#f7f1e8] font-sans">
    <!-- Left: Premium Branding Area -->
    <div class="relative hidden w-[48%] flex-col justify-between overflow-hidden bg-[#181412] p-14 text-stone-50 lg:flex">
      <!-- Elegant background mesh grid -->
      <div class="absolute inset-0 opacity-25 bg-[radial-gradient(#6e543c_1.5px,transparent_1.5px)] bg-size-[32px_32px]"></div>
      <div class="absolute -left-20 -top-20 h-96 w-96 rounded-full bg-amber-500/10 blur-3xl"></div>
      <div class="absolute -bottom-20 -right-20 h-96 w-96 rounded-full bg-amber-700/10 blur-3xl"></div>

      <!-- Top Header -->
      <div class="relative z-10 flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-linear-to-br from-amber-300 to-amber-500 shadow-lg shadow-amber-500/20">
            <span class="text-xl font-black text-stone-950">V</span>
          </div>
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.4em] text-amber-400">Velora ID</p>
            <p class="text-lg font-bold tracking-tight text-white">Kantor Digital</p>
          </div>
        </div>
        <div class="rounded-full border border-white/10 bg-white/5 px-4 py-1.5 backdrop-blur-md">
          <span class="text-[11px] font-semibold tracking-wider text-amber-200/80">Enterprise Portal v3.0</span>
        </div>
      </div>

      <!-- Center Value Proposition -->
      <div class="relative z-10 my-auto max-w-xl pr-6">
        <div class="inline-flex items-center gap-2 rounded-full border border-amber-400/20 bg-amber-400/10 px-4 py-1.5 mb-6 text-amber-300">
          <Sparkles class="h-4 w-4" />
          <span class="text-xs font-bold uppercase tracking-widest">Ekosistem Terintegrasi</span>
        </div>
        <h1 class="text-5xl font-bold leading-[1.12] tracking-tight text-white lg:text-6xl">
          Satu gerbang untuk seluruh <span class="bg-linear-to-r from-amber-200 via-amber-300 to-amber-400 bg-clip-text text-transparent">workspace digital</span> Anda.
        </h1>
        <p class="mt-6 text-lg leading-relaxed text-stone-300/90">
          Akses aman, manajemen multi-tenant terpusat, dan alur kerja otomatis tanpa hambatan untuk Velora & Maven.
        </p>

        <!-- Feature Highlights -->
        <div class="mt-12 grid grid-cols-2 gap-4">
          <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm transition-all hover:border-amber-400/30 hover:bg-white/10">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-400/10 text-amber-400">
              <Shield class="h-5 w-5" />
            </div>
            <p class="mt-4 font-semibold text-white">Keamanan Mutlak</p>
            <p class="mt-1 text-xs leading-relaxed text-stone-400">Isolasi data tingkat tinggi untuk setiap entitas agensi.</p>
          </div>

          <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm transition-all hover:border-amber-400/30 hover:bg-white/10">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-400/10 text-amber-400">
              <Layers class="h-5 w-5" />
            </div>
            <p class="mt-4 font-semibold text-white">Multi-Workspace</p>
            <p class="mt-1 text-xs leading-relaxed text-stone-400">Beralih antar workspace Velora dan Maven dalam satu klik.</p>
          </div>
        </div>
      </div>

      <!-- Bottom Footer Info -->
      <div class="relative z-10 flex items-center justify-between border-t border-white/10 pt-6 text-xs text-stone-400">
        <p>© 2026 Mahin Utsman Nawawi, S.H. / Velora ID</p>
        <div class="flex gap-6">
          <span>Privasi & Keamanan</span>
          <span>Bantuan Internal</span>
        </div>
      </div>
    </div>

    <!-- Right: Auth Form Area -->
    <main class="flex flex-1 flex-col items-center justify-center p-6 lg:p-16">
      <div class="w-full max-w-[460px]">
        <!-- Mobile Header -->
        <header class="mb-10 flex flex-col items-center text-center lg:hidden">
          <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-linear-to-br from-amber-400 to-amber-600 shadow-lg shadow-amber-500/20 mb-3">
            <span class="text-xl font-black text-stone-950">V</span>
          </div>
          <p class="text-sm font-bold uppercase tracking-[0.4em] text-[#8f5724]">Velora ID</p>
          <h1 class="mt-1 text-2xl font-bold tracking-tight text-stone-900">Kantor Digital</h1>
        </header>

        <!-- Premium Login Card -->
        <div class="rounded-[2.5rem] border border-white/60 bg-white/80 p-8 shadow-[0_32px_64px_rgba(40,30,20,0.08)] backdrop-blur-2xl lg:p-12">
          <div class="mb-10">
            <div class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-[#8f5724] mb-3">
              <Lock class="h-3 w-3" />
              <span>Autentikasi Internal</span>
            </div>
            <h2 class="text-3xl font-bold tracking-tight text-stone-950">Selamat Datang</h2>
            <p class="mt-2 text-sm leading-relaxed text-stone-500">
              Silakan masukkan kredensial resmi Anda untuk mengakses dasbor agensi.
            </p>
          </div>

          <form @submit.prevent="submit" class="space-y-6">
            <!-- Email Field -->
            <div class="space-y-2">
              <label for="email" class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-600">Alamat Email</label>
              <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-stone-400">
                  <Mail class="h-5 w-5" />
                </div>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  autocomplete="email"
                  required
                  placeholder="nama@agensi.com"
                  class="w-full rounded-2xl border border-stone-200 bg-stone-50/50 py-4 pl-12 pr-5 text-sm text-stone-900 placeholder:text-stone-400 outline-none transition-all focus:border-amber-400 focus:bg-white focus:ring-4 focus:ring-amber-100"
                />
              </div>
              <p v-if="form.errors.email" class="mt-2 text-xs font-semibold text-rose-600">{{ form.errors.email }}</p>
            </div>

            <!-- Password Field -->
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <label for="password" class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-600">Kata Sandi</label>
                <button
                  type="button"
                  class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#8f5724] transition hover:text-stone-950"
                  @click="showPassword = !showPassword"
                >
                  {{ showPassword ? 'Sembunyikan' : 'Tampilkan' }}
                </button>
              </div>
              <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-stone-400">
                  <KeyRound class="h-5 w-5" />
                </div>
                <input
                  id="password"
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  autocomplete="current-password"
                  required
                  placeholder="••••••••••••"
                  class="w-full rounded-2xl border border-stone-200 bg-stone-50/50 py-4 pl-12 pr-5 text-sm text-stone-900 placeholder:text-stone-400 outline-none transition-all focus:border-amber-400 focus:bg-white focus:ring-4 focus:ring-amber-100"
                />
              </div>
              <p v-if="form.errors.password" class="mt-2 text-xs font-semibold text-rose-600">{{ form.errors.password }}</p>
            </div>

            <!-- Submit Button -->
            <div class="pt-4">
              <button
                type="submit"
                :disabled="form.processing"
                class="group relative flex w-full items-center justify-center gap-3 overflow-hidden rounded-2xl bg-stone-950 px-8 py-5 text-sm font-bold tracking-[0.24em] text-white transition-all active:scale-[0.98] disabled:opacity-50 shadow-lg shadow-stone-950/20 hover:bg-stone-900"
              >
                <div class="absolute inset-0 bg-[linear-gradient(45deg,transparent_25%,rgba(255,255,255,0.08)_50%,transparent_75%)] bg-size-[250%_250%] transition-all duration-1000 group-hover:bg-position-[100%_100%]"></div>
                <span class="uppercase">Masuk Ke Dasbor</span>
                <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-1 text-amber-400" />
              </button>
            </div>
          </form>

          <!-- Security Badge -->
          <div class="mt-10 border-t border-stone-100 pt-8 text-center">
            <div class="inline-flex items-center gap-3 rounded-2xl bg-stone-50 px-4 py-3 border border-stone-100">
              <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                <ShieldCheck class="h-4 w-4" />
              </div>
              <div class="text-left">
                <p class="text-xs font-bold text-stone-900">Enkripsi Tingkat Lanjut</p>
                <p class="text-[11px] text-stone-500">Sesi dilindungi dengan token aman & RLS.</p>
              </div>
            </div>
          </div>
        </div>

        <p class="mt-12 text-center text-xs tracking-[0.2em] text-stone-400">
          © 2026 KANTOR DIGITAL • VELORA ID
        </p>
      </div>
    </main>
  </section>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { ArrowRight, ShieldCheck, Mail, KeyRound, Lock, Sparkles, Shield, Layers } from 'lucide-vue-next'

const showPassword = ref(false)

const form = useForm({
  email: '',
  password: '',
  remember: true,
})

function submit() {
  form.post('/login', {
    onFinish: () => {
      form.password = ''
      showPassword.value = false
    },
  })
}
</script>

