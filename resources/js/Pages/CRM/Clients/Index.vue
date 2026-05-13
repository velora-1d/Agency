<template>
  <Head title="Klien" />

  <WorkspaceLayout title="Klien" subtitle="Kelola klien, PIC, status kerja sama, dan hubungan ke proyek, tagihan, kontrak, serta aset digital.">
    <template #actions>
      <button
        type="button"
        @click="openCreateModal"
        class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
      >
        <UserPlus class="h-4 w-4" />
        <span>Tambah Klien Baru</span>
      </button>
    </template>

    <div class="space-y-6">
      <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5 shadow-sm">
          <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Total Klien</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ clients.summary.total_clients }}</p>
        </div>
        <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5 shadow-sm">
          <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Klien Aktif</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ clients.summary.active_clients }}</p>
        </div>
        <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5 shadow-sm">
          <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Ditahan (On Hold)</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ clients.summary.on_hold_clients }}</p>
        </div>
        <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5 shadow-sm">
          <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Portal Aktif</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ clients.summary.portal_enabled }}</p>
        </div>
      </section>

      <section class="rounded-[2.2rem] border border-stone-200 bg-white p-3 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4 px-3 py-2">
          <div class="flex items-center gap-3">
            <div class="rounded-full bg-stone-100 px-4 py-2 text-xs font-semibold text-stone-600">
              <span class="font-semibold text-stone-950">{{ clients.table.length }}</span> klien tampil
            </div>
          </div>

          <div class="grid flex-1 items-center gap-3 sm:flex sm:justify-end">
            <div class="min-w-[140px]">
              <select v-model="filterState.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="">Semua Status</option>
                <option v-for="status in filterOptions.statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
              </select>
            </div>

            <div class="min-w-[160px]">
              <select v-model="filterState.industry" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="">Semua Industri</option>
                <option v-for="industry in filterOptions.industries" :key="industry" :value="industry">{{ industry }}</option>
              </select>
            </div>

            <div class="min-w-[160px] space-y-1">
              <select v-model="filterState.assignee" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="">Semua Petugas</option>
                <option v-for="assignee in filterOptions.assignees" :key="assignee.id" :value="assignee.id">{{ assignee.name }}</option>
              </select>
            </div>

            <div class="min-w-[160px]">
              <select v-model="filterState.date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="">Semua Waktu</option>
                <option v-for="range in filterOptions.date_ranges" :key="range.value" :value="range.value">{{ range.label }}</option>
              </select>
            </div>

            <button @click="resetFilters" class="rounded-2xl p-3 text-stone-400 transition hover:bg-stone-50 hover:text-stone-900">
              <RotateCcw class="h-4 w-4" />
            </button>
          </div>
        </div>

        <div class="mt-3 overflow-hidden rounded-[1.8rem] border border-stone-100">
          <table class="w-full text-left text-sm">
            <thead class="bg-stone-50 text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">
              <tr>
                <th class="px-5 py-4">Perusahaan & PIC</th>
                <th class="px-5 py-4">Industri & Lokasi</th>
                <th class="px-5 py-4">Status</th>
                <th class="px-5 py-4">Petugas</th>
                <th class="px-5 py-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 border-t border-stone-100">
              <tr v-for="client in clients.table" :key="client.id" class="transition-colors hover:bg-stone-50/70">
                <td class="px-5 py-4 align-top">
                  <button @click="goToShow(client.id)" class="group text-left">
                    <p class="font-bold text-stone-950 transition group-hover:text-amber-700">{{ client.company_name }}</p>
                    <p class="mt-1 text-xs text-stone-500">{{ client.pic_name || 'Tanpa PIC' }}</p>
                  </button>
                </td>
                <td class="px-5 py-4 align-top">
                  <p class="font-medium text-stone-700">{{ client.industry || '-' }}</p>
                  <p class="mt-1 text-xs text-stone-500">{{ client.city || '-' }}, {{ client.province || '-' }}</p>
                </td>
                <td class="px-5 py-4 align-top">
                  <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.14em]" :class="statusBadgeClass(client.status)">
                    {{ statusLabel(client.status) }}
                  </span>
                </td>
                <td class="px-5 py-4 align-top text-stone-600">{{ client.assigned_user?.name || 'Belum ada petugas' }}</td>
                <td class="px-5 py-4 text-right align-top">
                  <div class="flex items-center justify-end gap-1">
                    <button @click="openEditModal(client)" class="rounded-full p-2 text-stone-400 transition hover:bg-white hover:text-stone-900">
                      <Pencil class="h-4 w-4" />
                    </button>
                    <button @click="deleteClient(client.id)" class="rounded-full p-2 text-stone-400 transition hover:bg-rose-50 hover:text-rose-600">
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="clients.table.length === 0">
                <td colspan="5" class="px-5 py-14 text-center">
                  <div class="flex flex-col items-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-stone-50 text-stone-300">
                      <Users class="h-6 w-6" />
                    </div>
                    <p class="mt-4 font-semibold text-stone-950">Belum ada data klien</p>
                    <p class="mt-1 text-sm text-stone-500">Coba ubah filter atau tambah klien baru untuk memulai.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>

    <!-- Modal: Client Form -->
    <Transition name="modal">
      <div v-if="showClientModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 p-4 backdrop-blur-sm">
        <div class="w-full max-w-3xl overflow-hidden rounded-[2.5rem] bg-white shadow-2xl">
          <div class="flex items-center justify-between border-b border-stone-100 bg-stone-50/50 px-8 py-6">
            <div>
              <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-stone-400">Data Klien</p>
              <h3 class="mt-2 text-2xl font-bold text-stone-900">{{ isEditing ? 'Edit Klien' : 'Tambah Klien Baru' }}</h3>
            </div>
            <button @click="closeModal" class="rounded-full p-2 text-stone-500 transition hover:bg-stone-200">
              <X class="h-5 w-5" />
            </button>
          </div>

          <form @submit.prevent="submit" class="p-8">
            <div class="max-h-[60vh] overflow-y-auto pr-4 scrollbar-none">
              <div class="space-y-8">
                <!-- Group 1: Identitas Bisnis -->
                <section class="space-y-4">
                  <div class="flex items-center gap-3">
                    <div class="h-1.5 w-1.5 rounded-full bg-amber-500"></div>
                    <h4 class="text-[11px] font-bold uppercase tracking-widest text-stone-400">Identitas Bisnis</h4>
                  </div>
                  <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1">
                      <label class="text-[10px] font-bold uppercase tracking-wider text-stone-500">Nama Perusahaan</label>
                      <input v-model="clientForm.company_name" type="text" required class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                      <p v-if="clientForm.errors.company_name" class="text-[10px] text-rose-500">{{ clientForm.errors.company_name }}</p>
                    </div>
                    <div class="space-y-1">
                      <label class="text-[10px] font-bold uppercase tracking-wider text-stone-500">Industri</label>
                      <input v-model="clientForm.industry" type="text" list="industry-list" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                      <datalist id="industry-list">
                        <option v-for="ind in filterOptions.industries" :key="ind" :value="ind" />
                      </datalist>
                    </div>
                  </div>
                </section>

                <!-- Group 2: Kontak & PIC -->
                <section class="space-y-4">
                  <div class="flex items-center gap-3">
                    <div class="h-1.5 w-1.5 rounded-full bg-amber-500"></div>
                    <h4 class="text-[11px] font-bold uppercase tracking-widest text-stone-400">Kontak & PIC</h4>
                  </div>
                  <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1">
                      <label class="text-[10px] font-bold uppercase tracking-wider text-stone-500">Nama PIC Utama</label>
                      <input v-model="clientForm.pic_name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                    </div>
                    <div class="space-y-1">
                      <label class="text-[10px] font-bold uppercase tracking-wider text-stone-500">Email Korespondensi</label>
                      <input v-model="clientForm.email" type="email" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                    </div>
                    <div class="space-y-1">
                      <label class="text-[10px] font-bold uppercase tracking-wider text-stone-500">Telepon / WhatsApp</label>
                      <input v-model="clientForm.phone" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                    </div>
                  </div>
                </section>

                <!-- Group 3: Lokasi -->
                <section class="space-y-4">
                  <div class="flex items-center gap-3">
                    <div class="h-1.5 w-1.5 rounded-full bg-amber-500"></div>
                    <h4 class="text-[11px] font-bold uppercase tracking-widest text-stone-400">Lokasi</h4>
                  </div>
                  <div class="grid gap-4">
                    <div class="space-y-1">
                      <label class="text-[10px] font-bold uppercase tracking-wider text-stone-500">Alamat Lengkap</label>
                      <textarea v-model="clientForm.address" rows="2" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                      <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-stone-500">Kota</label>
                        <input v-model="clientForm.city" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                      </div>
                      <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-stone-500">Provinsi</label>
                        <input v-model="clientForm.province" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                      </div>
                    </div>
                  </div>
                </section>

                <!-- Group 4: Manajemen Akun -->
                <section class="space-y-4">
                  <div class="flex items-center gap-3">
                    <div class="h-1.5 w-1.5 rounded-full bg-amber-500"></div>
                    <h4 class="text-[11px] font-bold uppercase tracking-widest text-stone-400">Manajemen Akun</h4>
                  </div>
                  <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1">
                      <label class="text-[10px] font-bold uppercase tracking-wider text-stone-500">Status Akun</label>
                      <select v-model="clientForm.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                        <option value="on_hold">Ditahan</option>
                      </select>
                    </div>
                    <div class="space-y-1">
                      <label class="text-[10px] font-bold uppercase tracking-wider text-stone-500">Petugas PJ (Assignee)</label>
                      <select v-model="clientForm.assigned_to" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                        <option value="">Tanpa Petugas</option>
                        <option v-for="assignee in filterOptions.assignees" :key="assignee.id" :value="assignee.id">{{ assignee.name }}</option>
                      </select>
                    </div>
                  </div>
                  <div class="space-y-1">
                    <label class="text-[10px] font-bold uppercase tracking-wider text-stone-500">Catatan Internal</label>
                    <textarea v-model="clientForm.notes" rows="3" placeholder="Gunakan untuk menyimpan info preferensi klien atau catatan khusus tim..." class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
                  </div>
                </section>
              </div>
            </div>

            <div class="flex justify-end gap-3 pt-8 border-t border-stone-100 mt-8">
              <button type="button" @click="closeModal" class="rounded-2xl border border-stone-200 bg-white px-6 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-50 hover:text-stone-950">
                Batal
              </button>
              <button type="submit" :disabled="clientForm.processing" class="rounded-2xl bg-stone-950 px-10 py-3 text-sm font-bold text-white shadow-lg shadow-stone-900/20 transition hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50">
                {{ isEditing ? 'Simpan Perubahan' : 'Tambah Klien Sekarang' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </WorkspaceLayout>
</template>

<script setup>
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, ref, watch, reactive } from 'vue'
import debounce from 'lodash/debounce'
import {
  Pencil,
  RotateCcw,
  Trash2,
  UserPlus,
  Users,
  X,
} from 'lucide-vue-next'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'

const props = defineProps({
  workspace: Object,
  navigation: Array,
  clients: {
    type: Object,
    default: () => ({ summary: {}, table: [] }),
  },
  filterOptions: Object,
  filters: Object,
})

const workspaceBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}`
const clientsBaseUrl = `${workspaceBaseUrl}/crm/clients`

const filterState = reactive({
  status: props.filters.status ?? '',
  industry: props.filters.industry ?? '',
  assignee: props.filters.assignee ?? '',
  date: props.filters.date ?? '',
})

const showClientModal = ref(false)
const editingClientId = ref(null)

const clientForm = useForm({
  company_name: '',
  pic_name: '',
  email: '',
  phone: '',
  industry: '',
  address: '',
  city: '',
  province: '',
  status: 'active',
  assigned_to: '',
  notes: '',
})

const isEditing = computed(() => editingClientId.value !== null)

watch(filterState, debounce(() => {
  router.get(clientsBaseUrl, compactFilters(filterState), {
    preserveState: true,
    preserveScroll: true,
    only: ['clients', 'filters'],
  })
}, 300), { deep: true })

function resetFilters() {
  filterState.status = ''
  filterState.industry = ''
  filterState.assignee = ''
  filterState.date = ''
}

function goToShow(clientId) {
  router.get(`${clientsBaseUrl}/${encodeURIComponent(clientId)}`)
}

function openCreateModal() {
  editingClientId.value = null
  clientForm.reset()
  showClientModal.value = true
}

function openEditModal(client) {
  editingClientId.value = client.id
  clientForm.company_name = client.company_name || ''
  clientForm.pic_name = client.pic_name || ''
  clientForm.email = client.email || ''
  clientForm.phone = client.phone || ''
  clientForm.industry = client.industry || ''
  clientForm.address = client.address || ''
  clientForm.city = client.city || ''
  clientForm.province = client.province || ''
  clientForm.status = client.status || 'active'
  clientForm.assigned_to = client.assigned_user?.id || ''
  clientForm.notes = client.notes || ''
  showClientModal.value = true
}

function closeModal() {
  showClientModal.value = false
  editingClientId.value = null
  clientForm.clearErrors()
}

function submit() {
  const options = {
    onSuccess: () => closeModal(),
  }

  if (isEditing.value) {
    clientForm.patch(`${clientsBaseUrl}/${encodeURIComponent(editingClientId.value)}`, options)
  } else {
    clientForm.post(clientsBaseUrl, options)
  }
}

function deleteClient(clientId) {
  if (!confirm('Apakah Anda yakin ingin menghapus data klien ini?')) return
  
  router.delete(`${clientsBaseUrl}/${encodeURIComponent(clientId)}`, {
    preserveScroll: true,
  })
}

function statusLabel(status) {
  return {
    active: 'Aktif',
    inactive: 'Nonaktif',
    on_hold: 'Ditahan',
  }[status] || status
}

function compactFilters(filters) {
  return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== '' && value !== null && value !== undefined))
}

function statusBadgeClass(status) {
  return {
    active: 'bg-emerald-100 text-emerald-700',
    inactive: 'bg-stone-100 text-stone-600',
    on_hold: 'bg-amber-100 text-amber-700',
  }[status] || 'bg-stone-100 text-stone-600'
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.95) translateY(10px);
}
</style>
