<template>
  <WorkspaceLayout
    title="API Key"
    subtitle="Menu 27 difokuskan untuk pembuatan, scope, pencabutan, IP whitelist, rate limit, dan log pemakaian API key workspace."
  >
    <template #actions>
      <button
        type="button"
        @click="openKeyModal()"
        class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
      >
        <Plus class="h-4 w-4" />
        <span>Key Baru</span>
      </button>
    </template>

    <AutomationLayout :workspace="workspace">
      <div class="space-y-6">
        <section class="rounded-[1.6rem] border border-stone-200 bg-white p-4 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="max-w-3xl">
              <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-stone-400">Menu 27 / API Keys</p>
              <h2 class="mt-2 text-2xl font-semibold tracking-[-0.05em] text-stone-950">Akses API dibaca seperti akses produksi: jelas scope-nya, jelas batasnya.</h2>
              <p class="mt-2 text-sm leading-6 text-stone-500">
                Nama key, scope, whitelist, rate limit, expiry, dan jejak pemakaian ditempatkan dalam satu panel yang bisa dibaca cepat.
              </p>
            </div>

            <div class="grid min-w-full gap-3 sm:min-w-[22rem] sm:grid-cols-4 xl:w-[34rem]">
              <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Aktif</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ apiKeySummary.active_keys }}</p>
              </div>
              <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Total</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ apiKeySummary.total_keys }}</p>
              </div>
              <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Whitelist</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ apiKeySummary.whitelisted_keys }}</p>
              </div>
              <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Batas Rate</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ apiKeySummary.rate_budget }}</p>
              </div>
            </div>
          </div>

          <div class="mt-4 grid gap-3 md:grid-cols-3">
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Dicabut</p>
              <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ apiKeySummary.revoked_keys }}</p>
            </div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Endpoint Webhook</p>
              <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ apiKeySummary.webhook_endpoints }}</p>
            </div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Workspace</p>
              <p class="mt-2 text-sm font-semibold text-stone-950">{{ apiKeys.workspace_name }}</p>
            </div>
          </div>
        </section>

        <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <div class="filter-panel-head mb-5">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Filter</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Cari key berdasarkan nama, status, atau scope akses yang dibuka.</h2>
            </div>
            <div class="filter-meta-badge">
              <span class="font-semibold text-stone-950">{{ filteredKeys.length }}</span> key tampil
            </div>
          </div>

          <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <label class="space-y-2 text-sm xl:col-span-3">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Cari</span>
              <input v-model="filterState.search" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
              <select v-model="filterState.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option v-for="item in filters.statuses" :key="item.value || 'all-status'" :value="item.value">{{ item.label }}</option>
              </select>
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Cakupan</span>
              <select v-model="filterState.scope" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option v-for="item in filters.scopes" :key="item.value || 'all-scope'" :value="item.value">{{ item.label }}</option>
              </select>
            </label>
          </div>

          <div class="mt-5 flex flex-wrap items-center gap-3">
            <button type="button" @click="resetFilters" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900">
              <RotateCcw class="h-4 w-4" />
              <span>Atur Ulang</span>
            </button>
          </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-[0.92fr_1.08fr]">
          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Papan Key</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Daftar key yang masih hidup dan yang sudah diputus.</h2>
              </div>
              <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">
                {{ filteredKeys.length }} key
              </span>
            </div>

            <div class="mt-5 space-y-4">
              <article
                v-for="item in filteredKeys"
                :key="item.id"
                class="rounded-[1.6rem] border p-5 transition"
                :class="selectedKey?.id === item.id ? 'border-stone-900 bg-stone-950 text-white shadow-[0_18px_50px_rgba(28,25,23,0.18)]' : 'border-stone-200 bg-stone-50 hover:border-stone-300 hover:bg-white'"
              >
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div class="max-w-2xl">
                    <div class="flex flex-wrap items-center gap-2">
                      <button type="button" @click="selectKey(item.id)" class="text-left text-base font-semibold transition" :class="selectedKey?.id === item.id ? 'text-white' : 'text-stone-950 hover:text-sky-700'">
                        {{ item.name }}
                      </button>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="statusClass(item.status)">
                        {{ item.status }}
                      </span>
                    </div>

                    <p class="mt-2 text-sm" :class="selectedKey?.id === item.id ? 'text-stone-300' : 'text-stone-500'">
                      {{ item.masked_key }}
                    </p>

                    <div class="mt-4 flex flex-wrap gap-2 text-xs">
                      <span v-for="scope in item.scope_labels" :key="scope" class="rounded-full px-3 py-1.5" :class="selectedKey?.id === item.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">
                        {{ scope }}
                      </span>
                    </div>
                  </div>

                  <div class="text-right">
                    <p class="text-sm font-semibold" :class="selectedKey?.id === item.id ? 'text-white' : 'text-stone-950'">{{ item.rate_limit_label }}</p>
                    <p class="mt-1 text-xs" :class="selectedKey?.id === item.id ? 'text-stone-300' : 'text-stone-500'">{{ item.last_used_label }}</p>
                  </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                  <button type="button" @click="selectKey(item.id)" class="inline-flex items-center gap-2 rounded-2xl border px-3.5 py-2 text-xs font-semibold transition" :class="selectedKey?.id === item.id ? 'border-white/15 text-white hover:bg-white/10' : 'border-stone-200 bg-white text-stone-700 hover:border-stone-300 hover:text-stone-950'">
                    <Eye class="h-3.5 w-3.5" />
                    <span>Rincian</span>
                  </button>
                  <button type="button" @click="openKeyModal(item)" class="inline-flex items-center gap-2 rounded-2xl border px-3.5 py-2 text-xs font-semibold transition" :class="selectedKey?.id === item.id ? 'border-white/15 text-white hover:bg-white/10' : 'border-stone-200 bg-white text-stone-700 hover:border-stone-300 hover:text-stone-950'">
                    <Pencil class="h-3.5 w-3.5" />
                    <span>Ubah</span>
                  </button>
                  <button type="button" @click="toggleStatus(item.id)" class="inline-flex items-center gap-2 rounded-2xl px-3.5 py-2 text-xs font-semibold transition" :class="item.status === 'active' ? 'border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100' : 'border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'">
                    <Power class="h-3.5 w-3.5" />
                    <span>{{ item.status === 'active' ? 'Cabut' : 'Aktifkan' }}</span>
                  </button>
                </div>
              </article>

              <div v-if="filteredKeys.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-300 bg-stone-50 px-6 py-14 text-center text-sm leading-6 text-stone-500">
                Belum ada API key yang cocok dengan filter saat ini.
              </div>
            </div>
          </article>

          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div v-if="selectedKey" class="space-y-5">
              <div class="border-b border-stone-200 pb-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Key Inspector</p>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                      <h2 class="text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ selectedKey.name }}</h2>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="statusClass(selectedKey.status)">
                        {{ selectedKey.status }}
                      </span>
                    </div>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-stone-500">{{ selectedKey.masked_key }}</p>
                  </div>

                  <div class="rounded-[1.3rem] border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-600">
                    <p class="font-semibold text-stone-950">{{ selectedKey.rate_limit_label }}</p>
                    <p class="mt-1 text-xs text-stone-500">Kedaluwarsa {{ selectedKey.expires_label }}</p>
                  </div>
                </div>
              </div>

              <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Terakhir Dipakai</p>
                  <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedKey.last_used_label }}</p>
                </div>
                <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Kedaluwarsa</p>
                  <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedKey.expires_label }}</p>
                </div>
                <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Whitelist</p>
                  <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedKey.ip_whitelist.length }} IP</p>
                </div>
                <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Workspace</p>
                  <p class="mt-2 text-sm font-semibold text-stone-950">{{ apiKeys.workspace_name }}</p>
                </div>
              </div>

              <div class="grid gap-4 xl:grid-cols-[1.02fr_0.98fr]">
                <section class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                  <div class="flex items-center gap-2">
                    <ShieldCheck class="h-4 w-4 text-stone-500" />
                    <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-stone-500">Cakupan & Akses</h3>
                  </div>

                  <div class="mt-5">
                    <div class="flex flex-wrap gap-2">
                      <span v-for="scope in selectedKey.scope_labels" :key="scope" class="rounded-full border border-stone-200 bg-white px-3 py-1.5 text-xs font-semibold text-stone-600">
                        {{ scope }}
                      </span>
                    </div>

                    <div class="mt-5 space-y-3">
                      <article v-for="ip in selectedKey.ip_whitelist" :key="ip" class="rounded-[1.2rem] border border-stone-200 bg-white p-4 text-sm font-semibold text-stone-950">
                        {{ ip }}
                      </article>
                      <div v-if="selectedKey.ip_whitelist.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-200 bg-white p-4 text-sm text-stone-500">
                        Tidak ada IP whitelist aktif.
                      </div>
                    </div>
                  </div>
                </section>

                <section class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                  <div class="flex items-center gap-2">
                    <Activity class="h-4 w-4 text-stone-500" />
                    <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-stone-500">Pemakaian & Webhook</h3>
                  </div>

                  <div class="mt-5 space-y-3">
                    <article v-for="log in selectedKey.usage_logs" :key="log.id" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                      <p class="text-sm font-semibold text-stone-950">{{ log.label }}</p>
                      <p class="mt-1 text-sm text-stone-500">{{ log.time_label }} / {{ log.result }}</p>
                    </article>

                    <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                      <p class="text-sm font-semibold text-stone-950">Webhook Masuk</p>
                      <div class="mt-3 flex flex-wrap gap-2">
                        <span v-for="webhook in selectedKey.webhooks" :key="webhook.label" class="rounded-full border border-stone-200 bg-white px-3 py-1.5 text-xs font-semibold text-stone-600">
                          {{ webhook.label }} / {{ webhook.status }}
                        </span>
                      </div>
                    </div>
                  </div>
                </section>
              </div>
            </div>

            <div v-else class="rounded-[1.6rem] border border-dashed border-stone-300 bg-stone-50 px-6 py-16 text-center text-sm text-stone-500">
              Pilih API key dari panel kiri untuk melihat scope dan usage detailnya.
            </div>
          </article>
        </section>

        <section class="rounded-[2rem] border border-stone-200 bg-stone-950 p-5 text-white shadow-[0_20px_60px_rgba(28,25,23,0.14)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-amber-200/75">Log Gateway Terbaru</p>
          <div class="mt-4 grid gap-3 md:grid-cols-3">
            <article v-for="log in logItems" :key="log.id" class="rounded-[1.2rem] border border-white/10 bg-white/5 p-4">
              <p class="text-sm font-semibold text-white">{{ log.label }}</p>
              <p class="mt-2 text-sm text-stone-300">{{ log.time_label }}</p>
              <p class="mt-1 text-xs uppercase tracking-[0.14em] text-amber-200/80">{{ log.result }}</p>
            </article>
          </div>
        </section>
      </div>

      <div v-if="showKeyModal" class="fixed inset-0 z-[70] flex items-center justify-center bg-stone-950/40 px-4 py-8 backdrop-blur-sm">
        <div class="modal-panel max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-[2rem] border border-stone-200 bg-white p-6 shadow-[0_28px_90px_rgba(28,25,23,0.18)]">
          <div class="flex items-start justify-between gap-4 border-b border-stone-200 pb-5">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Form API Key</p>
              <h2 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ editingKeyId ? 'Ubah Key' : 'Key Baru' }}</h2>
            </div>
            <button type="button" @click="closeKeyModal" class="rounded-full border border-stone-200 p-2 text-stone-500 transition hover:border-stone-300 hover:text-stone-950">
              <X class="h-4 w-4" />
            </button>
          </div>

          <form class="mt-5 space-y-5" @submit.prevent="submitKey">
            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Nama Key</span>
                <input v-model="keyForm.name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Rate Limit</span>
                <input v-model="keyForm.rate_limit" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white" />
              </label>
            </div>

            <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Cakupan</span>
              <textarea v-model="keyForm.scopes" rows="3" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white"></textarea>
              <p class="text-xs text-stone-500">Pisahkan scope yang diizinkan dengan koma.</p>
            </label>

            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Label Kedaluwarsa</span>
                <input v-model="keyForm.expires_label" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white" />
                <p class="text-xs text-stone-500">Gunakan format label tanggal yang dipakai tim internal.</p>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Whitelist IP</span>
                <input v-model="keyForm.whitelist" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white" />
                <p class="text-xs text-stone-500">Pisahkan setiap IP dengan koma.</p>
              </label>
            </div>

            <div class="flex flex-wrap justify-end gap-3">
              <button type="button" @click="closeKeyModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900">
                Batal
              </button>
              <button type="submit" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition-all hover:-translate-y-0.5 hover:bg-stone-800">
                {{ editingKeyId ? 'Simpan Perubahan' : 'Buat Key' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </AutomationLayout>
  </WorkspaceLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { Activity, Eye, Pencil, Plus, Power, RotateCcw, ShieldCheck, X } from 'lucide-vue-next'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'
import AutomationLayout from '../../../Layouts/AutomationLayout.vue'

const props = defineProps({
  workspace: { type: Object, required: true },
  api_keys: { type: Object, required: true },
})

const apiKeys = computed(() => props.api_keys || {})
const filters = computed(() => apiKeys.value.filters || { statuses: [], scopes: [] })
const logItems = computed(() => apiKeys.value.logs || [])

const localKeys = ref(cloneItems(apiKeys.value.items || []))
const filterState = ref({
  search: '',
  status: '',
  scope: '',
})
const selectedKeyId = ref(localKeys.value[0]?.id || null)
const showKeyModal = ref(false)
const editingKeyId = ref(null)
const keyForm = ref(blankKeyForm())

const filteredKeys = computed(() => {
  return localKeys.value.filter((item) => {
    const search = filterState.value.search.trim().toLowerCase()
    const matchesSearch = !search || [item.name, item.masked_key, item.rate_limit_label].join(' ').toLowerCase().includes(search)
    const matchesStatus = !filterState.value.status || item.status === filterState.value.status
    const matchesScope = !filterState.value.scope || (item.scope_labels || []).some((scope) => scope.toLowerCase() === filterState.value.scope.toLowerCase())

    return matchesSearch && matchesStatus && matchesScope
  })
})

const selectedKey = computed(() => {
  return filteredKeys.value.find((item) => item.id === selectedKeyId.value)
    || localKeys.value.find((item) => item.id === selectedKeyId.value)
    || filteredKeys.value[0]
    || null
})

const apiKeySummary = computed(() => {
  const allItems = localKeys.value

  return {
    total_keys: allItems.length,
    active_keys: allItems.filter((item) => item.status === 'active').length,
    revoked_keys: allItems.filter((item) => item.status === 'revoked').length,
    whitelisted_keys: allItems.filter((item) => (item.ip_whitelist || []).length > 0).length,
    webhook_endpoints: allItems.reduce((sum, item) => sum + (item.webhooks?.length || 0), 0),
    rate_budget: allItems.reduce((sum, item) => sum + parseRateLimit(item.rate_limit_label), 0),
  }
})

watch(filteredKeys, (items) => {
  if (items.length === 0) {
    selectedKeyId.value = null
    return
  }

  if (!items.some((item) => item.id === selectedKeyId.value)) {
    selectedKeyId.value = items[0].id
  }
}, { immediate: true })

function blankKeyForm() {
  return {
    name: '',
    scopes: '',
    rate_limit: '500 req/hour',
    expires_label: 'Tanpa kedaluwarsa',
    whitelist: '',
  }
}

function selectKey(keyId) {
  selectedKeyId.value = keyId
}

function resetFilters() {
  filterState.value = {
    search: '',
    status: '',
    scope: '',
  }
}

function openKeyModal(item = null) {
  editingKeyId.value = item?.id || null
  showKeyModal.value = true

  if (!item) {
    keyForm.value = blankKeyForm()
    return
  }

  keyForm.value = {
    name: item.name,
    scopes: (item.scope_labels || []).join(', '),
    rate_limit: item.rate_limit_label,
    expires_label: item.expires_label,
    whitelist: (item.ip_whitelist || []).join(', '),
  }
}

function closeKeyModal() {
  showKeyModal.value = false
  editingKeyId.value = null
  keyForm.value = blankKeyForm()
}

function submitKey() {
  const payload = {
    id: editingKeyId.value || `key-${Date.now()}`,
    name: keyForm.value.name || 'Untitled Key',
    status: editingKeyId.value ? selectedKey.value?.status || 'active' : 'active',
    scope_labels: normalizeList(keyForm.value.scopes),
    last_used_label: editingKeyId.value ? selectedKey.value?.last_used_label || 'Never used' : 'Never used',
    expires_label: keyForm.value.expires_label || 'Tanpa kedaluwarsa',
    rate_limit_label: keyForm.value.rate_limit || '500 req/hour',
    masked_key: editingKeyId.value ? selectedKey.value?.masked_key || buildMaskedKey() : buildMaskedKey(),
    ip_whitelist: normalizeList(keyForm.value.whitelist),
    usage_logs: editingKeyId.value ? selectedKey.value?.usage_logs || [] : [
      { id: `usage-${Date.now()}`, label: 'Key generated', time_label: 'Now', result: 'Pending use' },
    ],
    webhooks: editingKeyId.value ? selectedKey.value?.webhooks || [] : [
      { label: 'Inbound webhook', status: 'enabled' },
    ],
  }

  if (editingKeyId.value) {
    localKeys.value = localKeys.value.map((item) => item.id === editingKeyId.value ? payload : item)
  } else {
    localKeys.value = [payload, ...localKeys.value]
  }

  selectedKeyId.value = payload.id
  closeKeyModal()
}

function toggleStatus(keyId) {
  localKeys.value = localKeys.value.map((item) => {
    if (item.id !== keyId) {
      return item
    }

    return {
      ...item,
      status: item.status === 'active' ? 'revoked' : 'active',
    }
  })
}

function statusClass(status) {
  const map = {
    active: 'bg-emerald-100 text-emerald-700',
    revoked: 'bg-rose-100 text-rose-700',
    expired: 'bg-amber-100 text-amber-700',
  }

  return map[status] || 'bg-stone-100 text-stone-600'
}

function buildMaskedKey() {
  return `vk_local_${Math.random().toString(36).slice(2, 12)}`
}

function normalizeList(value) {
  return value
    .split(',')
    .map((item) => item.trim())
    .filter(Boolean)
}

function parseRateLimit(label) {
  const value = Number.parseInt(String(label).replace(/[^0-9]/g, ''), 10)
  return Number.isNaN(value) ? 0 : value
}

function cloneItems(items) {
  return JSON.parse(JSON.stringify(items))
}
</script>

<style scoped>
.modal-panel {
  animation: modal-enter 0.24s ease;
}

@keyframes modal-enter {
  from {
    opacity: 0;
    transform: translateY(16px) scale(0.98);
  }

  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}
</style>

