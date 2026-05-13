<template>
  <WorkspaceLayout title="Kontrak" subtitle="Panel kontrak untuk memantau draft, proses tanda tangan, nilai kerja sama, dan arsip legal project.">
    <template #actions>
      <div class="flex flex-wrap items-center justify-end gap-3">
        <button
          type="button"
          @click="router.get(`${contractsBaseUrl}/templates`)"
          class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 shadow-sm transition-all hover:border-stone-300 hover:text-stone-950"
        >
          <Settings2 class="h-4 w-4" />
          <span>Kelola Templat</span>
        </button>

        <button
          type="button"
          @click="openCreateModal"
          class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
        >
          <Plus class="h-4 w-4" />
          <span>Buat Kontrak</span>
        </button>
      </div>
    </template>

    <ProjectLayout :workspace="workspace">
      <div class="space-y-6">
      <!-- Summary Section -->
      <section class="project-hero-shell">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div class="project-hero-copy">
            <p class="project-hero-kicker">Menu 6 / Kontrak</p>
            <h2 class="project-hero-title">Panel kontrak dibuat lebih rapat supaya draft, status tanda tangan, dan nilai kerja sama cepat terbaca.</h2>
            <p class="project-hero-desc">
              Dokumen legal, template, reminder, dan histori kontrak tetap lengkap, tapi pembuka halaman dibuat ringkas agar fokus jatuh ke register kontrak.
            </p>
          </div>
        </div>

        <div class="mt-4 compact-stat-grid md:grid-cols-2 xl:grid-cols-5">
          <article class="compact-stat-card">
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Total Kontrak</p>
            <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ contracts.summary.total_contracts }}</p>
          </article>
          <article class="compact-stat-card">
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Draft</p>
            <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-400">{{ contracts.summary.draft_contracts }}</p>
          </article>
          <article class="compact-stat-card">
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Terkirim</p>
            <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-blue-600">{{ contracts.summary.sent_contracts }}</p>
          </article>
          <article class="compact-stat-card">
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Ditandatangani</p>
            <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-emerald-600">{{ contracts.summary.signed_contracts }}</p>
          </article>
          <article class="compact-stat-card">
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Total Nilai</p>
            <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ formatCurrency(contracts.summary.total_value) }}</p>
          </article>
        </div>

        <div class="mt-3 rounded-[1rem] border border-dashed border-stone-300 bg-stone-50 px-4 py-3 text-sm leading-6 text-stone-500">
          Status legal, expiry reminder, dan template kontrak tetap berada di alur yang sama tanpa menambah kepadatan sidebar.
        </div>
      </section>

      <!-- Filters Section -->
      <section class="project-panel-shell">
        <div class="filter-panel-head mb-5">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Filter Kontrak</p>
            <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Saring kontrak berdasarkan status, client, project, dan masa berlaku.</h2>
          </div>
          <div class="filter-meta-badge">
            <span class="font-semibold text-stone-950">{{ contracts.table.length }}</span> kontrak tampil
          </div>
        </div>

        <div class="filter-toolbar grid gap-4 md:grid-cols-2 xl:grid-cols-4">
          <label class="filter-tight space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
            <select v-model="filterState.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Status</option>
              <option v-for="status in filterOptions.statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
            </select>
          </label>

          <label class="filter-tight space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Klien</span>
            <select v-model="filterState.client_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Klien</option>
              <option v-for="client in filterOptions.clients" :key="client.id" :value="client.id">{{ client.name }}</option>
            </select>
          </label>

          <label class="filter-tight space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Proyek</span>
            <select v-model="filterState.project_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Proyek</option>
              <option v-for="project in filterOptions.projects" :key="project.id" :value="project.id">{{ project.name }}</option>
            </select>
          </label>

          <label class="filter-tight space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Rentang Waktu</span>
            <select v-model="filterState.date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Waktu</option>
              <option value="7d">7 Hari Terakhir</option>
              <option value="30d">30 Hari Terakhir</option>
              <option value="expired">Kedaluwarsa / Segera Berakhir</option>
            </select>
          </label>
        </div>

        <div class="filter-actions mt-5 flex flex-wrap items-center gap-3">
          <button
            type="button"
            @click="applyFilters"
            class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition-all hover:-translate-y-0.5 hover:bg-stone-800"
          >
            <Filter class="h-4 w-4" />
            <span>Terapkan Filter</span>
          </button>
          <button
            type="button"
            @click="resetFilters"
            class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900"
          >
            <RotateCcw class="h-4 w-4" />
            <span>Atur Ulang</span>
          </button>
        </div>
      </section>

      <!-- Contracts Table -->
      <section class="overflow-hidden rounded-[2rem] border border-stone-200 bg-white shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-stone-200 text-sm">
            <thead class="bg-stone-50 text-left text-[11px] font-bold uppercase tracking-[0.22em] text-stone-400">
              <tr>
                <th class="px-5 py-4">Judul Kontrak</th>
                <th class="px-5 py-4">Klien / Proyek</th>
                <th class="px-5 py-4">Status</th>
                <th class="px-5 py-4">Nilai</th>
                <th class="px-5 py-4">Masa Berlaku</th>
                <th class="px-5 py-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 bg-white">
              <tr v-for="contract in contracts.table" :key="contract.id" class="transition-colors hover:bg-stone-50/70">
                <td class="px-5 py-4 align-top">
                  <div class="flex flex-col gap-1">
                    <span class="font-semibold text-stone-950">{{ contract.title }}</span>
                    <span class="text-xs text-stone-500">Dibuat {{ contract.created_at_label }}</span>
                  </div>
                </td>
                <td class="px-5 py-4 align-top">
                  <div class="flex flex-col gap-1">
                    <span class="font-medium text-stone-800">{{ contract.client_name }}</span>
                    <span class="text-xs text-stone-500">{{ contract.project_name }}</span>
                  </div>
                </td>
                <td class="px-5 py-4 align-top">
                  <span class="inline-flex rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.15em]" :class="statusClass(contract.status)">
                    {{ contract.status }}
                  </span>
                </td>
                <td class="px-5 py-4 align-top font-medium text-stone-900">
                  {{ contract.value_label }}
                </td>
                <td class="px-5 py-4 align-top">
                  <div class="flex flex-col gap-1 text-xs text-stone-600">
                    <span>Mulai: {{ contract.start_date_label || '-' }}</span>
                    <span>Selesai: {{ contract.end_date_label || '-' }}</span>
                  </div>
                </td>
                <td class="px-5 py-4 align-top text-right">
                  <div class="flex justify-end gap-2">
                    <button
                      type="button"
                      @click="openContract(contract.id)"
                      class="inline-flex items-center gap-2 rounded-full border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-700 transition-all hover:border-stone-300 hover:text-stone-950"
                    >
                      <span>Detail</span>
                      <ArrowUpRight class="h-3.5 w-3.5" />
                    </button>
                    <button
                      type="button"
                      @click="openEditModal(contract)"
                      class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-400 transition-all hover:border-stone-300 hover:text-stone-950"
                    >
                      <Pencil class="h-3.5 w-3.5" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="contracts.table.length === 0">
                <td colspan="6" class="px-5 py-16 text-center text-sm text-stone-500">Belum ada kontrak yang ditemukan.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>

    <!-- Create/Edit Modal -->
    <Transition name="modal">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Form Kontrak</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditing ? 'Ubah Kontrak' : 'Buat Kontrak Baru' }}</h3>
            </div>
            <button type="button" @click="closeModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
              <X class="h-5 w-5" />
            </button>
          </div>

          <form class="mt-6 space-y-5" @submit.prevent="submitForm">
            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2 text-sm md:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Muat dari Templat (Opsional)</span>
                <select @change="loadTemplate($event.target.value)" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">-- Pilih Templat --</option>
                  <option v-for="template in filterOptions.templates" :key="template.id" :value="template.id">{{ template.name }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm md:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Judul Kontrak</span>
                <input v-model="form.title" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                <p v-if="form.errors.title" class="text-xs text-rose-500">{{ form.errors.title }}</p>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Klien</span>
                <select v-model="form.client_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Tanpa Klien</option>
                  <option v-for="client in filterOptions.clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Proyek</span>
                <select v-model="form.project_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Tanpa project</option>
                  <option v-for="project in filterOptions.projects" :key="project.id" :value="project.id">{{ project.name }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Nilai Kontrak</span>
                <input v-model="form.value" type="number" step="0.01" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Pengingat (Hari Sebelum)</span>
                <input v-model="form.reminder_days_before" type="number" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tanggal Mulai</span>
                <input v-model="form.start_date" type="date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tanggal Selesai</span>
                <input v-model="form.end_date" type="date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm md:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Isi / Ketentuan Kontrak</span>
                <textarea v-model="form.content" rows="6" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
              </label>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3">
              <button type="button" @click="closeModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900">
                Batal
              </button>
              <button type="submit" :disabled="form.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition-all hover:bg-stone-800 disabled:cursor-not-allowed disabled:opacity-60">
                {{ isEditing ? (form.processing ? 'Menyimpan...' : 'Simpan Perubahan') : (form.processing ? 'Membuat...' : 'Buat Kontrak') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
    </ProjectLayout>
  </WorkspaceLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { Plus, Filter, RotateCcw, ArrowUpRight, Pencil, X, Settings2 } from 'lucide-vue-next'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'
import ProjectLayout from '../../../Layouts/ProjectLayout.vue'

const props = defineProps({
  workspace: Object,
  contracts: Object,
  filters: Object,
  filterOptions: Object,
})

const workspaceBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}`
const contractsBaseUrl = `${workspaceBaseUrl}/projects/contracts`

const viewMode = ref('table')
const showModal = ref(false)
const editingContractId = ref(null)
const isEditing = computed(() => editingContractId.value !== null)

const filterState = ref({
  status: props.filters.status ?? '',
  client_id: props.filters.client_id ?? '',
  project_id: props.filters.project_id ?? '',
  date: props.filters.date ?? '',
})

const form = useForm({
  client_id: '',
  project_id: '',
  title: '',
  content: '',
  value: '',
  start_date: '',
  end_date: '',
  reminder_days_before: 30,
})

function applyFilters() {
  router.get(contractsBaseUrl, compactFilters(filterState.value), {
    preserveState: true,
    preserveScroll: true,
  })
}

function resetFilters() {
  filterState.value = {
    status: '',
    client_id: '',
    project_id: '',
    date: '',
  }
  router.get(contractsBaseUrl, {}, {
    preserveState: true,
    preserveScroll: true,
  })
}

function openCreateModal() {
  editingContractId.value = null
  form.reset()
  form.clearErrors()
  showModal.value = true
}

function openEditModal(contract) {
  editingContractId.value = contract.id
  form.reset()
  form.clearErrors()
  form.client_id = contract.client_id || ''
  form.project_id = contract.project_id || ''
  form.title = contract.title || ''
  form.content = contract.content || ''
  form.value = contract.value || ''
  form.start_date = contract.start_date || ''
  form.end_date = contract.end_date || ''
  form.reminder_days_before = contract.reminder_days_before || 30
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingContractId.value = null
  form.reset()
}

function loadTemplate(templateId) {
  if (!templateId) return
  const template = props.filterOptions.templates.find(t => t.id === templateId)
  if (template) {
    form.title = template.name
    form.content = template.content
  }
}

function submitForm() {
  const options = {
    preserveScroll: true,
    onSuccess: () => closeModal(),
  }

  if (isEditing.value) {
    form.patch(`${contractsBaseUrl}/${encodeURIComponent(editingContractId.value)}`, options)
  } else {
    form.post(contractsBaseUrl, options)
  }
}

function openContract(contractId) {
  router.get(`${contractsBaseUrl}/${encodeURIComponent(contractId)}`)
}

function statusClass(status) {
  const map = {
    draft: 'bg-stone-100 text-stone-500',
    sent: 'bg-blue-100 text-blue-700',
    signed: 'bg-emerald-100 text-emerald-700',
    expired: 'bg-rose-100 text-rose-700',
  }
  return map[status] || 'bg-stone-100 text-stone-500'
}

function formatCurrency(value) {
  const prefix = props.workspace.currency === 'IDR' ? 'Rp ' : props.workspace.currency + ' '
  return prefix + new Intl.NumberFormat('id-ID').format(value || 0)
}

function compactFilters(filters) {
  return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== '' && value !== null))
}
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.25s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(0.96); }
</style>
