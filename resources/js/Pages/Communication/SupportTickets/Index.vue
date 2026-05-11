<template>
  <Head :title="`Support Tickets - ${workspace.name}`" />

  <WorkspaceLayout
    title="Support Tickets"
    subtitle="Kelola tiket dukungan pelanggan, pantau SLA, dan berikan respon cepat untuk menjaga kepuasan klien."
    :workspace-name="workspace.name"
    :workspace-slug="workspace.slug"
    :navigation="navigation"
  >
    <template #actions>
      <div class="flex gap-3">
        <button 
          @click="openCreateModal"
          class="rounded-full bg-stone-950 px-6 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-amber-200 shadow-lg transition hover:scale-105 hover:bg-stone-800 active:scale-95"
        >
          Buat Tiket Baru
        </button>
      </div>
    </template>

    <div class="space-y-8">
      <!-- Filters -->
      <section class="animate-reveal">
        <div class="rounded-4xl border border-stone-200 bg-white p-6 shadow-[0_20px_60px_rgba(60,42,24,0.05)]">
          <div class="flex flex-wrap items-center gap-6">
            <div class="flex-auto min-w-[200px]">
              <label class="text-[10px] uppercase tracking-[0.2em] text-stone-500 block mb-2 px-1">Cari Tiket</label>
              <input 
                v-model="filterForm.search"
                type="text"
                placeholder="Judul atau deskripsi..."
                class="w-full rounded-2xl border-stone-200 bg-stone-50 px-4 py-2.5 text-sm text-stone-900 transition focus:border-amber-300 focus:bg-white focus:ring-0"
              />
            </div>

            <div class="flex-auto min-w-[150px]">
              <label class="text-[10px] uppercase tracking-[0.2em] text-stone-500 block mb-2 px-1">Status</label>
              <select 
                v-model="filterForm.status"
                class="w-full rounded-2xl border-stone-200 bg-stone-50 px-4 py-2.5 text-sm text-stone-900 transition focus:border-amber-300 focus:bg-white focus:ring-0"
              >
                <option value="">Semua Status</option>
                <option value="open">Open</option>
                <option value="in_progress">In Progress</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
              </select>
            </div>

            <div class="flex-auto min-w-[150px]">
              <label class="text-[10px] uppercase tracking-[0.2em] text-stone-500 block mb-2 px-1">Prioritas</label>
              <select 
                v-model="filterForm.priority"
                class="w-full rounded-2xl border-stone-200 bg-stone-50 px-4 py-2.5 text-sm text-stone-900 transition focus:border-amber-300 focus:bg-white focus:ring-0"
              >
                <option value="">Semua Prioritas</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>

            <div class="flex items-end pt-5">
              <button 
                @click="resetFilters"
                class="rounded-full bg-stone-100 px-5 py-2.5 text-[10px] font-bold uppercase tracking-[0.15em] text-stone-500 transition hover:bg-stone-200 hover:text-stone-700"
              >
                Reset
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Tickets List -->
      <section class="animate-reveal" style="animation-delay: 0.1s">
        <div class="overflow-hidden rounded-4xl border border-stone-200 bg-white shadow-[0_20px_60px_rgba(60,42,24,0.05)]">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-stone-50/50">
                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] text-stone-500">Tiket</th>
                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] text-stone-500">Klien / Sumber</th>
                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] text-stone-500">Status</th>
                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] text-stone-500">Prioritas</th>
                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] text-stone-500">SLA Due</th>
                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] text-stone-500 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
              <tr v-for="ticket in tickets.data" :key="ticket.id" class="group hover:bg-stone-50/50 transition">
                <td class="px-6 py-5">
                  <div class="flex flex-col">
                    <span class="font-semibold text-stone-900 group-hover:text-amber-700 transition line-clamp-1">{{ ticket.title }}</span>
                    <span class="text-[10px] text-stone-400 mt-1 uppercase tracking-wider">#{{ ticket.id.split('-')[0] }}</span>
                  </div>
                </td>
                <td class="px-6 py-5">
                  <div class="flex flex-col">
                    <span class="text-sm font-medium text-stone-700">{{ ticket.client?.company_name || 'No Client' }}</span>
                    <div class="flex items-center gap-1.5 mt-1">
                       <component :is="getSourceIcon(ticket.source)" class="h-3 w-3 text-stone-400" />
                       <span class="text-[10px] text-stone-400 uppercase tracking-tighter">{{ ticket.source }}</span>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-5">
                  <span 
                    class="inline-flex rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest shadow-sm"
                    :class="getStatusClass(ticket.status)"
                  >
                    {{ ticket.status.replace('_', ' ') }}
                  </span>
                </td>
                <td class="px-6 py-5">
                  <span 
                    class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-widest"
                    :class="getPriorityClass(ticket.priority)"
                  >
                    <span class="h-1.5 w-1.5 rounded-full" :class="getPriorityDotClass(ticket.priority)"></span>
                    {{ ticket.priority }}
                  </span>
                </td>
                <td class="px-6 py-5">
                  <div class="flex flex-col">
                    <span class="text-xs font-medium" :class="getSlaClass(ticket.sla_due_at, ticket.status)">
                      {{ formatDate(ticket.sla_due_at) }}
                    </span>
                    <span class="text-[10px] text-stone-400 mt-0.5">{{ getTimeAgo(ticket.sla_due_at) }}</span>
                  </div>
                </td>
                <td class="px-6 py-5 text-right">
                  <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition">
                    <button 
                      @click="editTicket(ticket)"
                      class="rounded-xl bg-white p-2 text-stone-400 border border-stone-200 shadow-sm hover:border-amber-300 hover:text-amber-600 transition"
                    >
                      <Pencil class="h-4 w-4" />
                    </button>
                    <button 
                      @click="confirmDelete(ticket)"
                      class="rounded-xl bg-white p-2 text-stone-400 border border-stone-200 shadow-sm hover:border-rose-300 hover:text-rose-600 transition"
                    >
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!tickets.data.length">
                <td colspan="6" class="px-6 py-20 text-center">
                  <div class="flex flex-col items-center">
                    <Ticket class="h-12 w-12 text-stone-200 mb-4" />
                    <h3 class="text-stone-900 font-semibold">Tidak ada tiket ditemukan</h3>
                    <p class="text-stone-400 text-sm mt-1">Coba sesuaikan filter atau buat tiket baru.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          
          <!-- Pagination -->
          <div v-if="tickets.links.length > 3" class="px-6 py-4 bg-stone-50/50 border-t border-stone-100 flex items-center justify-between">
            <div class="text-[10px] text-stone-400 uppercase tracking-widest">
              Showing {{ tickets.from }} to {{ tickets.to }} of {{ tickets.total }} results
            </div>
            <nav class="flex gap-1">
              <button 
                v-for="link in tickets.links" 
                :key="link.label"
                @click="goToPage(link.url)"
                :disabled="!link.url || link.active"
                v-html="link.label"
                class="px-3 py-1 text-[10px] font-bold uppercase rounded-lg transition"
                :class="[
                  link.active ? 'bg-stone-950 text-amber-200' : 'bg-white text-stone-500 border border-stone-200 hover:border-stone-400',
                  !link.url ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                ]"
              />
            </nav>
          </div>
        </div>
      </section>
    </div>

    <!-- Create/Edit Modal (Simplified for brevity in CLI) -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/60 backdrop-blur-sm animate-fade-in">
      <div class="w-full max-w-2xl bg-white rounded-4xl shadow-2xl overflow-hidden animate-zoom-in">
        <div class="px-8 py-6 border-b border-stone-100 flex items-center justify-between bg-stone-50/50">
          <div>
            <h3 class="text-lg font-bold text-stone-900">{{ isEditing ? 'Edit Tiket' : 'Buat Tiket Baru' }}</h3>
            <p class="text-xs text-stone-400 uppercase tracking-[0.1em] mt-0.5">Lengkapi detail informasi tiket</p>
          </div>
          <button @click="closeModal" class="p-2 rounded-full hover:bg-stone-200 transition">
            <X class="h-5 w-5 text-stone-500" />
          </button>
        </div>
        
        <form @submit.prevent="submit" class="p-8 space-y-6">
          <div class="grid grid-cols-2 gap-6">
            <div class="col-span-2">
              <label class="text-[10px] uppercase tracking-[0.2em] text-stone-500 block mb-2 px-1">Judul Tiket</label>
              <input 
                v-model="form.title"
                type="text"
                required
                class="w-full rounded-2xl border-stone-200 bg-stone-50 px-4 py-3 text-sm transition focus:border-amber-300 focus:bg-white focus:ring-0"
              />
              <div v-if="form.errors.title" class="text-rose-500 text-[10px] mt-1 px-1">{{ form.errors.title }}</div>
            </div>

            <div class="col-span-2">
              <label class="text-[10px] uppercase tracking-[0.2em] text-stone-500 block mb-2 px-1">Deskripsi Masalah</label>
              <textarea 
                v-model="form.description"
                rows="4"
                class="w-full rounded-2xl border-stone-200 bg-stone-50 px-4 py-3 text-sm transition focus:border-amber-300 focus:bg-white focus:ring-0"
              ></textarea>
            </div>

            <div>
              <label class="text-[10px] uppercase tracking-[0.2em] text-stone-500 block mb-2 px-1">Klien</label>
              <select v-model="form.client_id" class="w-full rounded-2xl border-stone-200 bg-stone-50 px-4 py-3 text-sm transition focus:border-amber-300 focus:bg-white focus:ring-0">
                <option :value="null">Pilih Klien...</option>
                <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.company_name }}</option>
              </select>
            </div>

            <div>
              <label class="text-[10px] uppercase tracking-[0.2em] text-stone-500 block mb-2 px-1">Assign Ke</label>
              <select v-model="form.assigned_to" class="w-full rounded-2xl border-stone-200 bg-stone-50 px-4 py-3 text-sm transition focus:border-amber-300 focus:bg-white focus:ring-0">
                <option :value="null">Pilih Anggota...</option>
                <option v-for="member in team" :key="member.id" :value="member.id">{{ member.name }}</option>
              </select>
            </div>

            <div>
              <label class="text-[10px] uppercase tracking-[0.2em] text-stone-500 block mb-2 px-1">Prioritas</label>
              <select v-model="form.priority" required class="w-full rounded-2xl border-stone-200 bg-stone-50 px-4 py-3 text-sm transition focus:border-amber-300 focus:bg-white focus:ring-0">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>

            <div>
              <label class="text-[10px] uppercase tracking-[0.2em] text-stone-500 block mb-2 px-1">Sumber</label>
              <select v-model="form.source" class="w-full rounded-2xl border-stone-200 bg-stone-50 px-4 py-3 text-sm transition focus:border-amber-300 focus:bg-white focus:ring-0">
                <option value="portal">Client Portal</option>
                <option value="whatsapp">WhatsApp</option>
              </select>
            </div>

            <div v-if="isEditing">
              <label class="text-[10px] uppercase tracking-[0.2em] text-stone-500 block mb-2 px-1">Status</label>
              <select v-model="form.status" class="w-full rounded-2xl border-stone-200 bg-stone-50 px-4 py-3 text-sm transition focus:border-amber-300 focus:bg-white focus:ring-0">
                <option value="open">Open</option>
                <option value="in_progress">In Progress</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
              </select>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-4">
            <button 
              type="button" 
              @click="closeModal"
              class="rounded-full px-6 py-2.5 text-xs font-bold uppercase tracking-[0.15em] text-stone-400 hover:text-stone-600 transition"
            >
              Batal
            </button>
            <button 
              type="submit"
              :disabled="form.processing"
              class="rounded-full bg-stone-950 px-8 py-2.5 text-xs font-semibold uppercase tracking-[0.2em] text-amber-200 shadow-lg hover:scale-105 active:scale-95 transition disabled:opacity-50"
            >
              {{ isEditing ? 'Simpan Perubahan' : 'Buat Tiket' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </WorkspaceLayout>
</template>

<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref, reactive, watch } from 'vue'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'
import { 
  Ticket, 
  Pencil, 
  Trash2, 
  X, 
  Globe, 
  MessageSquare,
  Clock,
  AlertCircle
} from 'lucide-vue-next'
import debounce from 'lodash/debounce'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'

dayjs.extend(relativeTime)

const props = defineProps({
  workspace: Object,
  navigation: Array,
  tickets: Object,
  clients: Array,
  team: Array,
  filters: Object,
})

const filterForm = reactive({
  search: props.filters.search || '',
  status: props.filters.status || '',
  priority: props.filters.priority || '',
})

const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)

const form = useForm({
  title: '',
  description: '',
  client_id: null,
  assigned_to: null,
  priority: 'medium',
  source: 'portal',
  status: 'open',
})

const submitFilters = debounce(() => {
  router.get(route('workspace.communication.support-tickets.index', props.workspace.slug), filterForm, {
    preserveState: true,
    preserveScroll: true,
  })
}, 300)

watch(filterForm, () => {
  submitFilters()
})

function resetFilters() {
  filterForm.search = ''
  filterForm.status = ''
  filterForm.priority = ''
}

function openCreateModal() {
  isEditing.value = false
  editingId.value = null
  form.reset()
  showModal.value = true
}

function editTicket(ticket) {
  isEditing.value = true
  editingId.value = ticket.id
  form.title = ticket.title
  form.description = ticket.description
  form.client_id = ticket.client_id
  form.assigned_to = ticket.assigned_to
  form.priority = ticket.priority
  form.source = ticket.source
  form.status = ticket.status
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  form.reset()
}

function submit() {
  if (isEditing.value) {
    form.patch(route('workspace.communication.support-tickets.update', { 
      workspace: props.workspace.slug, 
      supportTicket: editingId.value 
    }), {
      onSuccess: () => closeModal(),
    })
  } else {
    form.post(route('workspace.communication.support-tickets.store', props.workspace.slug), {
      onSuccess: () => closeModal(),
    })
  }
}

function confirmDelete(ticket) {
  if (confirm('Apakah Anda yakin ingin menghapus tiket ini?')) {
    router.delete(route('workspace.communication.support-tickets.destroy', { 
      workspace: props.workspace.slug, 
      supportTicket: ticket.id 
    }))
  }
}

function goToPage(url) {
  if (url) router.visit(url)
}

function getStatusClass(status) {
  return {
    'bg-amber-50 text-amber-600': status === 'open',
    'bg-blue-50 text-blue-600': status === 'in_progress',
    'bg-emerald-50 text-emerald-600': status === 'resolved',
    'bg-stone-100 text-stone-500': status === 'closed',
  }
}

function getPriorityClass(priority) {
  return {
    'text-stone-400': priority === 'low',
    'text-blue-500': priority === 'medium',
    'text-amber-500': priority === 'high',
    'text-rose-600 font-black': priority === 'urgent',
  }
}

function getPriorityDotClass(priority) {
  return {
    'bg-stone-300': priority === 'low',
    'bg-blue-400': priority === 'medium',
    'bg-amber-400': priority === 'high',
    'bg-rose-500 shadow-[0_0_8px_rgba(225,29,72,0.4)]': priority === 'urgent',
  }
}

function getSlaClass(date, status) {
  if (status === 'resolved' || status === 'closed') return 'text-stone-400'
  const isOverdue = dayjs().isAfter(dayjs(date))
  return isOverdue ? 'text-rose-600 font-bold' : 'text-stone-700'
}

function getSourceIcon(source) {
  return source === 'whatsapp' ? MessageSquare : Globe
}

function formatDate(date) {
  return dayjs(date).format('DD MMM YYYY, HH:mm')
}

function getTimeAgo(date) {
  return dayjs(date).fromNow()
}
</script>

<style scoped>
.animate-reveal {
  opacity: 0;
  animation: reveal 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.animate-fade-in {
  animation: fadeIn 0.3s ease-out forwards;
}

.animate-zoom-in {
  animation: zoomIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes reveal {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes zoomIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
</style>
