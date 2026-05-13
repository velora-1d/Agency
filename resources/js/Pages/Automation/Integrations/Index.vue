<template>
  <WorkspaceLayout
    title="Integrasi"
    subtitle="Menu 26 difokuskan untuk koneksi WhatsApp, Google, Pakasir, Meta, AI API, webhook, dan health check yang gampang dipantau."
  >
    <template #actions>
      <button
        type="button"
        @click="openIntegrationModal()"
        class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
      >
        <Plus class="h-4 w-4" />
        <span>Integrasi Baru</span>
      </button>
    </template>

    <AutomationLayout :workspace="workspace">
      <div class="space-y-6">
        <section class="rounded-[1.6rem] border border-stone-200 bg-white p-4 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="max-w-3xl">
              <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-stone-400">Menu 26 / Integrasi</p>
              <h2 class="mt-2 text-2xl font-semibold tracking-[-0.05em] text-stone-950">Semua koneksi eksternal dibaca sebagai satu panel health, bukan daftar link acak.</h2>
              <p class="mt-2 text-sm leading-6 text-stone-500">
                WhatsApp, Google, Meta, OpenAI, webhook relay, sampai Cloudflare tampil dengan status, ping, dan scope yang jelas.
              </p>
            </div>

            <div class="grid min-w-full gap-3 sm:min-w-[22rem] sm:grid-cols-4 xl:w-[34rem]">
              <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Terhubung</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ integrationSummary.connected_integrations }}</p>
              </div>
              <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Total</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ integrationSummary.total_integrations }}</p>
              </div>
              <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Peringatan</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ integrationSummary.warning_integrations }}</p>
              </div>
              <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Health</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ integrationSummary.health_score }}%</p>
              </div>
            </div>
          </div>

          <div class="mt-4 grid gap-3 md:grid-cols-3">
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Terputus</p>
              <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ integrationSummary.disconnected_integrations }}</p>
            </div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Cek Ping</p>
              <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ integrationSummary.ping_checks }}</p>
            </div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Workspace</p>
              <p class="mt-2 text-sm font-semibold text-stone-950">{{ integrations.workspace_name }}</p>
            </div>
          </div>
        </section>

        <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <div class="filter-panel-head mb-5">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Filter</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Cari integrasi berdasarkan provider, status, atau kondisi health.</h2>
            </div>
            <div class="filter-meta-badge">
              <span class="font-semibold text-stone-950">{{ filteredIntegrations.length }}</span> koneksi tampil
            </div>
          </div>

          <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <label class="space-y-2 text-sm xl:col-span-3">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Cari</span>
              <input v-model="filterState.search" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Provider</span>
              <select v-model="filterState.provider" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option v-for="item in filters.providers" :key="item.value || 'all-provider'" :value="item.value">{{ item.label }}</option>
              </select>
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
              <select v-model="filterState.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option v-for="item in filters.statuses" :key="item.value || 'all-status'" :value="item.value">{{ item.label }}</option>
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
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Papan Koneksi</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Semua integrasi yang perlu dipantau tim.</h2>
              </div>
              <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">
                {{ filteredIntegrations.length }} items
              </span>
            </div>

            <div class="mt-5 space-y-4">
              <article
                v-for="integration in filteredIntegrations"
                :key="integration.id"
                class="rounded-[1.6rem] border p-5 transition"
                :class="selectedIntegration?.id === integration.id ? 'border-stone-900 bg-stone-950 text-white shadow-[0_18px_50px_rgba(28,25,23,0.18)]' : 'border-stone-200 bg-stone-50 hover:border-stone-300 hover:bg-white'"
              >
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div class="max-w-2xl">
                    <div class="flex flex-wrap items-center gap-2">
                      <button type="button" @click="selectIntegration(integration.id)" class="text-left text-base font-semibold transition" :class="selectedIntegration?.id === integration.id ? 'text-white' : 'text-stone-950 hover:text-sky-700'">
                        {{ integration.name }}
                      </button>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="statusClass(integration.status)">
                        {{ integration.status }}
                      </span>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="healthClass(integration.health)">
                        {{ integration.health }}
                      </span>
                    </div>

                    <p class="mt-2 text-sm leading-6" :class="selectedIntegration?.id === integration.id ? 'text-stone-300' : 'text-stone-500'">
                      {{ integration.scope }}
                    </p>

                    <div class="mt-4 flex flex-wrap gap-2 text-xs">
                      <span class="rounded-full px-3 py-1.5" :class="selectedIntegration?.id === integration.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">
                        {{ integration.provider }}
                      </span>
                      <span class="rounded-full px-3 py-1.5" :class="selectedIntegration?.id === integration.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">
                        {{ integration.endpoint }}
                      </span>
                    </div>
                  </div>

                  <div class="text-right">
                    <p class="text-lg font-semibold" :class="selectedIntegration?.id === integration.id ? 'text-white' : 'text-stone-950'">{{ integration.health_score }}%</p>
                    <p class="mt-1 text-sm" :class="selectedIntegration?.id === integration.id ? 'text-stone-300' : 'text-stone-500'">health score</p>
                  </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                  <button type="button" @click="selectIntegration(integration.id)" class="inline-flex items-center gap-2 rounded-2xl border px-3.5 py-2 text-xs font-semibold transition" :class="selectedIntegration?.id === integration.id ? 'border-white/15 text-white hover:bg-white/10' : 'border-stone-200 bg-white text-stone-700 hover:border-stone-300 hover:text-stone-950'">
                    <Eye class="h-3.5 w-3.5" />
                    <span>Rincian</span>
                  </button>
                  <button type="button" @click="openIntegrationModal(integration)" class="inline-flex items-center gap-2 rounded-2xl border px-3.5 py-2 text-xs font-semibold transition" :class="selectedIntegration?.id === integration.id ? 'border-white/15 text-white hover:bg-white/10' : 'border-stone-200 bg-white text-stone-700 hover:border-stone-300 hover:text-stone-950'">
                    <Pencil class="h-3.5 w-3.5" />
                    <span>Ubah</span>
                  </button>
                  <button type="button" @click="toggleStatus(integration.id)" class="inline-flex items-center gap-2 rounded-2xl px-3.5 py-2 text-xs font-semibold transition" :class="integration.status === 'connected' ? 'border border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100' : 'border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'">
                    <Power class="h-3.5 w-3.5" />
                    <span>{{ integration.status === 'connected' ? 'Putuskan' : 'Hubungkan' }}</span>
                  </button>
                </div>
              </article>

              <div v-if="filteredIntegrations.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-300 bg-stone-50 px-6 py-14 text-center text-sm leading-6 text-stone-500">
                Belum ada integration yang cocok dengan filter saat ini.
              </div>
            </div>
          </article>

          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div v-if="selectedIntegration" class="space-y-5">
              <div class="border-b border-stone-200 pb-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Pemeriksa Integrasi</p>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                      <h2 class="text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ selectedIntegration.name }}</h2>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="statusClass(selectedIntegration.status)">
                        {{ selectedIntegration.status }}
                      </span>
                    </div>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-stone-500">{{ selectedIntegration.scope }}</p>
                  </div>

                  <div class="rounded-[1.3rem] border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-600">
                    <p class="font-semibold text-stone-950">{{ selectedIntegration.endpoint }}</p>
                    <p class="mt-1 text-xs text-stone-500">{{ selectedIntegration.last_checked_label }}</p>
                  </div>
                </div>
              </div>

              <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Provider</p>
                  <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedIntegration.provider }}</p>
                </div>
                <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Health</p>
                  <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedIntegration.health }} ({{ selectedIntegration.health_score }}%)</p>
                </div>
                <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Modul</p>
                  <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedIntegration.modules.join(', ') }}</p>
                </div>
                <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Workspace</p>
                  <p class="mt-2 text-sm font-semibold text-stone-950">{{ integrations.workspace_name }}</p>
                </div>
              </div>

              <div class="grid gap-4 xl:grid-cols-[1.02fr_0.98fr]">
                <section class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                  <div class="flex items-center gap-2">
                    <ShieldCheck class="h-4 w-4 text-stone-500" />
                    <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-stone-500">Cakupan Operasional</h3>
                  </div>

                  <div class="mt-5 space-y-3">
                    <article v-for="module in selectedIntegration.modules" :key="module" class="rounded-[1.2rem] border border-stone-200 bg-white p-4 text-sm font-semibold text-stone-950">
                      {{ module }}
                    </article>
                  </div>
                </section>

                <section class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                  <div class="flex items-center gap-2">
                    <Activity class="h-4 w-4 text-stone-500" />
                    <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-stone-500">Pemeriksaan Health</h3>
                  </div>

                  <div class="mt-5 space-y-3">
                    <article v-for="check in checkItems" :key="check.id" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                      <div class="flex items-center justify-between gap-3">
                        <div>
                          <p class="text-sm font-semibold text-stone-950">{{ check.label }}</p>
                          <p class="mt-1 text-sm text-stone-500">{{ check.time_label }}</p>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="healthClass(check.result)">
                          {{ check.result }}
                        </span>
                      </div>
                    </article>
                  </div>
                </section>
              </div>
            </div>

            <div v-else class="rounded-[1.6rem] border border-dashed border-stone-300 bg-stone-50 px-6 py-16 text-center text-sm text-stone-500">
              Pilih integrasi dari panel kiri untuk melihat health dan scope detailnya.
            </div>
          </article>
        </section>
      </div>

      <div v-if="showIntegrationModal" class="fixed inset-0 z-[70] flex items-center justify-center bg-stone-950/40 px-4 py-8 backdrop-blur-sm">
        <div class="modal-panel max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-[2rem] border border-stone-200 bg-white p-6 shadow-[0_28px_90px_rgba(28,25,23,0.18)]">
          <div class="flex items-start justify-between gap-4 border-b border-stone-200 pb-5">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Form Integrasi</p>
              <h2 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ editingIntegrationId ? 'Ubah Integrasi' : 'Integrasi Baru' }}</h2>
            </div>
            <button type="button" @click="closeIntegrationModal" class="rounded-full border border-stone-200 p-2 text-stone-500 transition hover:border-stone-300 hover:text-stone-950">
              <X class="h-4 w-4" />
            </button>
          </div>

          <form class="mt-5 space-y-5" @submit.prevent="submitIntegration">
            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Nama</span>
                <input v-model="integrationForm.name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Provider</span>
                <input v-model="integrationForm.provider" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Endpoint</span>
                <input v-model="integrationForm.endpoint" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Health</span>
                <select v-model="integrationForm.health" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white">
                  <option value="healthy">Sehat</option>
                  <option value="warning">Peringatan</option>
                  <option value="offline">Luring</option>
                </select>
              </label>
            </div>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Cakupan</span>
              <textarea v-model="integrationForm.scope" rows="4" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white"></textarea>
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Modul</span>
              <textarea v-model="integrationForm.modules" rows="3" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white"></textarea>
              <p class="text-xs text-stone-500">Pisahkan modul terkait dengan koma.</p>
            </label>

            <div class="flex flex-wrap justify-end gap-3">
              <button type="button" @click="closeIntegrationModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900">
                Batal
              </button>
              <button type="submit" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition-all hover:-translate-y-0.5 hover:bg-stone-800">
                {{ editingIntegrationId ? 'Simpan Perubahan' : 'Buat Integrasi' }}
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
import { Activity, Cable, Eye, Pencil, Plus, Power, RotateCcw, ShieldCheck, X } from 'lucide-vue-next'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'
import AutomationLayout from '../../../Layouts/AutomationLayout.vue'

const props = defineProps({
  workspace: { type: Object, required: true },
  integrations: { type: Object, required: true },
})

const integrationData = computed(() => props.integrations || {})
const filters = computed(() => integrationData.value.filters || { providers: [], statuses: [] })
const checkItems = computed(() => integrationData.value.checks || [])

const localIntegrations = ref(cloneItems(integrationData.value.items || []))
const filterState = ref({
  search: '',
  provider: '',
  status: '',
})
const selectedIntegrationId = ref(localIntegrations.value[0]?.id || null)
const showIntegrationModal = ref(false)
const editingIntegrationId = ref(null)
const integrationForm = ref(blankIntegrationForm())

const filteredIntegrations = computed(() => {
  return localIntegrations.value.filter((integration) => {
    const search = filterState.value.search.trim().toLowerCase()
    const matchesSearch = !search || [integration.name, integration.provider, integration.endpoint, integration.scope].join(' ').toLowerCase().includes(search)
    const matchesProvider = !filterState.value.provider || integration.provider === filterState.value.provider
    const matchesStatus = !filterState.value.status || integration.status === filterState.value.status

    return matchesSearch && matchesProvider && matchesStatus
  })
})

const selectedIntegration = computed(() => {
  return filteredIntegrations.value.find((integration) => integration.id === selectedIntegrationId.value)
    || localIntegrations.value.find((integration) => integration.id === selectedIntegrationId.value)
    || filteredIntegrations.value[0]
    || null
})

const integrationSummary = computed(() => {
  const allItems = localIntegrations.value

  return {
    total_integrations: allItems.length,
    connected_integrations: allItems.filter((item) => item.status === 'connected').length,
    warning_integrations: allItems.filter((item) => item.health === 'warning').length,
    disconnected_integrations: allItems.filter((item) => item.status === 'disconnected').length,
    health_score: Math.round(allItems.reduce((sum, item) => sum + item.health_score, 0) / Math.max(allItems.length, 1)),
    ping_checks: integrationData.value.summary?.ping_checks || 0,
  }
})

watch(filteredIntegrations, (items) => {
  if (items.length === 0) {
    selectedIntegrationId.value = null
    return
  }

  if (!items.some((item) => item.id === selectedIntegrationId.value)) {
    selectedIntegrationId.value = items[0].id
  }
}, { immediate: true })

function blankIntegrationForm() {
  return {
    name: '',
    provider: 'WhatsApp',
    endpoint: '',
    health: 'healthy',
    scope: '',
    modules: '',
  }
}

function selectIntegration(integrationId) {
  selectedIntegrationId.value = integrationId
}

function resetFilters() {
  filterState.value = {
    search: '',
    provider: '',
    status: '',
  }
}

function openIntegrationModal(integration = null) {
  editingIntegrationId.value = integration?.id || null
  showIntegrationModal.value = true

  if (!integration) {
    integrationForm.value = blankIntegrationForm()
    return
  }

  integrationForm.value = {
    name: integration.name,
    provider: integration.provider,
    endpoint: integration.endpoint,
    health: integration.health,
    scope: integration.scope,
    modules: (integration.modules || []).join(', '),
  }
}

function closeIntegrationModal() {
  showIntegrationModal.value = false
  editingIntegrationId.value = null
  integrationForm.value = blankIntegrationForm()
}

function submitIntegration() {
  const payload = {
    id: editingIntegrationId.value || `integration-${Date.now()}`,
    name: integrationForm.value.name || 'Integrasi tanpa nama',
    provider: integrationForm.value.provider || 'Provider',
    endpoint: integrationForm.value.endpoint || 'Endpoint belum diatur',
    health: integrationForm.value.health || 'healthy',
    status: integrationForm.value.health === 'offline' ? 'disconnected' : 'connected',
    health_score: integrationForm.value.health === 'healthy' ? 96 : integrationForm.value.health === 'warning' ? 72 : 28,
    last_checked_label: editingIntegrationId.value ? selectedIntegration.value?.last_checked_label || 'Now' : 'Now',
    scope: integrationForm.value.scope || 'Integrasi workspace',
    modules: normalizeList(integrationForm.value.modules),
  }

  if (editingIntegrationId.value) {
    localIntegrations.value = localIntegrations.value.map((integration) => integration.id === editingIntegrationId.value ? payload : integration)
  } else {
    localIntegrations.value = [payload, ...localIntegrations.value]
  }

  selectedIntegrationId.value = payload.id
  closeIntegrationModal()
}

function toggleStatus(integrationId) {
  localIntegrations.value = localIntegrations.value.map((integration) => {
    if (integration.id !== integrationId) {
      return integration
    }

    const nextStatus = integration.status === 'connected' ? 'disconnected' : 'connected'
    return {
      ...integration,
      status: nextStatus,
      health: nextStatus === 'connected' ? 'healthy' : 'offline',
    }
  })
}

function statusClass(status) {
  const map = {
    connected: 'bg-emerald-100 text-emerald-700',
    pending: 'bg-amber-100 text-amber-700',
    disconnected: 'bg-stone-200 text-stone-600',
  }

  return map[status] || 'bg-stone-100 text-stone-600'
}

function healthClass(health) {
  const map = {
    healthy: 'bg-emerald-100 text-emerald-700',
    warning: 'bg-amber-100 text-amber-700',
    offline: 'bg-rose-100 text-rose-700',
  }

  return map[health] || 'bg-stone-100 text-stone-600'
}

function normalizeList(value) {
  return value
    .split(',')
    .map((item) => item.trim())
    .filter(Boolean)
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
