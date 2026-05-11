<template>
  <WorkspaceLayout title="Clients" subtitle="Kelola client, PIC, status kerja sama, dan hubungan ke project, invoice, kontrak, serta ticket support.">
    <template #actions>
      <button
        type="button"
        @click="openCreateModal"
        class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
      >
        <Plus class="h-4 w-4" />
        <span>Add Client</span>
      </button>
    </template>

    <div class="space-y-6">
      <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Total Clients</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ clients.summary.total_clients }}</p>
          <p class="mt-2 text-sm text-stone-500">Jumlah client pada workspace ini.</p>
        </article>
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Active</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ clients.summary.active_clients }}</p>
          <p class="mt-2 text-sm text-stone-500">Client aktif yang sedang berjalan.</p>
        </article>
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">On Hold</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ clients.summary.on_hold_clients }}</p>
          <p class="mt-2 text-sm text-stone-500">Client yang sementara ditahan.</p>
        </article>
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Portal Access</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ clients.summary.portal_enabled }}</p>
          <p class="mt-2 text-sm text-stone-500">Client yang sudah bisa akses portal.</p>
        </article>
      </section>

      <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
            <select v-model="filterState.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">All Status</option>
              <option v-for="status in filterOptions.statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Industry</span>
            <select v-model="filterState.industry" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">All Industries</option>
              <option v-for="industry in filterOptions.industries" :key="industry" :value="industry">{{ industry }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Assignee</span>
            <select v-model="filterState.assignee" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">All Assignees</option>
              <option v-for="assignee in filterOptions.assignees" :key="assignee.id" :value="assignee.id">{{ assignee.name }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Date Joined</span>
            <select v-model="filterState.date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">All Time</option>
              <option v-for="range in filterOptions.date_ranges" :key="range.value" :value="range.value">{{ range.label }}</option>
            </select>
          </label>
        </div>

        <div class="mt-5 flex flex-wrap items-center gap-3">
          <button
            type="button"
            @click="applyFilters"
            class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition-all hover:-translate-y-0.5 hover:bg-stone-800"
          >
            <Filter class="h-4 w-4" />
            <span>Apply Filters</span>
          </button>
          <button
            type="button"
            @click="resetFilters"
            class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900"
          >
            <RotateCcw class="h-4 w-4" />
            <span>Reset</span>
          </button>
        </div>
      </section>

      <section class="overflow-hidden rounded-[2rem] border border-stone-200 bg-white shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-stone-200 text-sm">
            <thead class="bg-stone-50 text-left text-[11px] font-bold uppercase tracking-[0.22em] text-stone-400">
              <tr>
                <th class="px-5 py-4">Client</th>
                <th class="px-5 py-4">Contact</th>
                <th class="px-5 py-4">Status</th>
                <th class="px-5 py-4">Assignee</th>
                <th class="px-5 py-4">Linked Data</th>
                <th class="px-5 py-4">Joined</th>
                <th class="px-5 py-4"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 bg-white">
              <tr v-for="client in clients.table" :key="client.id" class="transition-colors hover:bg-stone-50/70">
                <td class="px-5 py-4 align-top">
                  <div class="space-y-1">
                    <div class="flex items-center gap-2">
                      <span class="font-semibold text-stone-950">{{ client.company_name }}</span>
                      <span v-if="client.portal_access" class="rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-700">Portal</span>
                    </div>
                    <p class="text-xs text-stone-500">{{ client.pic_name || 'PIC belum diisi' }}</p>
                    <p class="text-xs text-stone-500">{{ client.industry || 'Industry belum diisi' }}</p>
                    <p class="text-xs text-stone-500">{{ client.location || 'Lokasi belum diisi' }}</p>
                  </div>
                </td>
                <td class="px-5 py-4 align-top">
                  <div class="space-y-1 text-stone-600">
                    <p>{{ client.email || 'Email belum diisi' }}</p>
                    <p>{{ client.phone || 'WA/Telp belum diisi' }}</p>
                  </div>
                </td>
                <td class="px-5 py-4 align-top">
                  <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold" :class="statusClass(client.status)">
                    {{ statusLabel(client.status) }}
                  </span>
                </td>
                <td class="px-5 py-4 align-top text-stone-600">{{ client.assigned_user?.name || 'Belum ada assignee' }}</td>
                <td class="px-5 py-4 align-top">
                  <div class="grid grid-cols-2 gap-2 text-xs text-stone-600">
                    <span>Projects: {{ client.counts.projects }}</span>
                    <span>Invoices: {{ client.counts.invoices }}</span>
                    <span>Contracts: {{ client.counts.contracts }}</span>
                    <span>Tickets: {{ client.counts.tickets }}</span>
                  </div>
                </td>
                <td class="px-5 py-4 align-top text-stone-500">{{ client.created_at_label || '-' }}</td>
                <td class="px-5 py-4 align-top text-right">
                  <div class="flex justify-end gap-2">
                    <button type="button" @click="openEditModal(client)" class="inline-flex items-center gap-2 rounded-full border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-700 transition-all hover:border-stone-300 hover:text-stone-950">
                      <Pencil class="h-3.5 w-3.5" />
                    </button>
                    <button type="button" @click="openClient(client.id)" class="inline-flex items-center gap-2 rounded-full border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-700 transition-all hover:border-stone-300 hover:text-stone-950">
                      <span>Open</span>
                      <ArrowUpRight class="h-3.5 w-3.5" />
                    </button>
                    <button type="button" @click="deleteClient(client.id)" class="inline-flex items-center gap-2 rounded-full border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-700 transition-all hover:bg-rose-50">
                      <Trash2 class="h-3.5 w-3.5" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="clients.table.length === 0">
                <td colspan="7" class="px-5 py-16 text-center text-sm text-stone-500">Belum ada client yang cocok dengan filter saat ini.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>

    <Transition name="modal">
      <div v-if="showClientModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Client Form</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditing ? 'Edit Client' : 'Create Client' }}</h3>
            </div>
            <button type="button" @click="closeClientModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
              <X class="h-5 w-5" />
            </button>
          </div>

          <form class="mt-6 space-y-5" @submit.prevent="submitClient">
            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2 text-sm md:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Company Name</span>
                <input v-model="clientForm.company_name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                <p v-if="clientForm.errors.company_name" class="text-xs text-rose-500">{{ clientForm.errors.company_name }}</p>
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">PIC Name</span>
                <input v-model="clientForm.pic_name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Industry</span>
                <input v-model="clientForm.industry" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Email</span>
                <input v-model="clientForm.email" type="email" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                <p v-if="clientForm.errors.email" class="text-xs text-rose-500">{{ clientForm.errors.email }}</p>
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">WhatsApp / Phone</span>
                <input v-model="clientForm.phone" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">City</span>
                <input v-model="clientForm.city" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Province</span>
                <input v-model="clientForm.province" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
                <select v-model="clientForm.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="on_hold">On Hold</option>
                </select>
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Assignee</span>
                <select v-model="clientForm.assigned_to" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">No Assignee</option>
                  <option v-for="assignee in filterOptions.assignees" :key="assignee.id" :value="assignee.id">{{ assignee.name }}</option>
                </select>
              </label>
              <label class="space-y-2 text-sm md:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Address</span>
                <textarea v-model="clientForm.address" rows="3" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
              </label>
              <label class="space-y-2 text-sm md:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Notes</span>
                <textarea v-model="clientForm.notes" rows="4" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
              </label>
              <label class="space-y-2 text-sm md:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Portal Access</span>
                <label class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700">
                  <input v-model="clientForm.portal_access" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-400" />
                  <span>Izinkan client akses portal</span>
                </label>
              </label>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3">
              <button type="button" @click="closeClientModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900">
                Cancel
              </button>
              <button type="submit" :disabled="clientForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition-all hover:bg-stone-800 disabled:cursor-not-allowed disabled:opacity-60">
                {{ isEditing ? (clientForm.processing ? 'Saving...' : 'Save Client') : (clientForm.processing ? 'Creating...' : 'Create Client') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </WorkspaceLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { ArrowUpRight, Filter, Pencil, Plus, RotateCcw, Trash2, X } from 'lucide-vue-next'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'

const props = defineProps({
  workspace: {
    type: Object,
    required: true,
  },
  clients: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  filterOptions: {
    type: Object,
    required: true,
  },
})

const workspaceBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}`
const clientsBaseUrl = `${workspaceBaseUrl}/crm/clients`
const showClientModal = ref(false)
const editingClientId = ref(null)
const filterState = ref({
  status: props.filters.status ?? '',
  industry: props.filters.industry ?? '',
  assignee: props.filters.assignee ?? '',
  date: props.filters.date ?? '',
})

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
  portal_access: false,
  notes: '',
})

const isEditing = computed(() => editingClientId.value !== null)

function applyFilters() {
  router.get(clientsBaseUrl, compactFilters(filterState.value), {
    preserveState: true,
    preserveScroll: true,
  })
}

function resetFilters() {
  filterState.value = {
    status: '',
    industry: '',
    assignee: '',
    date: '',
  }

  router.get(clientsBaseUrl, {}, {
    preserveState: true,
    preserveScroll: true,
  })
}

function openClient(clientId) {
  router.get(`${clientsBaseUrl}/${encodeURIComponent(clientId)}`)
}

function openCreateModal() {
  editingClientId.value = null
  resetClientForm()
  showClientModal.value = true
}

function openEditModal(client) {
  editingClientId.value = client.id
  clientForm.reset()
  clientForm.clearErrors()
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
  clientForm.portal_access = Boolean(client.portal_access)
  clientForm.notes = client.notes || ''
  showClientModal.value = true
}

function closeClientModal() {
  showClientModal.value = false
  editingClientId.value = null
  resetClientForm()
}

function submitClient() {
  const options = {
    preserveScroll: true,
    onSuccess: () => {
      closeClientModal()
    },
  }

  if (isEditing.value) {
    clientForm.patch(`${clientsBaseUrl}/${encodeURIComponent(editingClientId.value)}`, options)
    return
  }

  clientForm.post(clientsBaseUrl, options)
}

function deleteClient(clientId) {
  if (!confirm('Delete this client? Data client akan dihapus dari menu ini.')) {
    return
  }

  router.delete(`${clientsBaseUrl}/${encodeURIComponent(clientId)}`, {
    preserveScroll: true,
  })
}

function resetClientForm() {
  clientForm.reset()
  clientForm.clearErrors()
  clientForm.status = 'active'
  clientForm.assigned_to = ''
  clientForm.portal_access = false
}

function compactFilters(filters) {
  return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== '' && value !== null && value !== undefined))
}

function statusLabel(status) {
  const map = {
    active: 'Active',
    inactive: 'Inactive',
    on_hold: 'On Hold',
  }

  return map[status] || status
}

function statusClass(status) {
  const map = {
    active: 'bg-emerald-100 text-emerald-700',
    inactive: 'bg-stone-100 text-stone-600',
    on_hold: 'bg-amber-100 text-amber-700',
  }

  return map[status] || 'bg-stone-100 text-stone-600'
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: all 0.25s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.96);
}
</style>
