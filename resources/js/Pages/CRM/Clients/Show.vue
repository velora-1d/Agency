<template>
  <Head :title="client.company_name" />

  <WorkspaceLayout :title="client.company_name" subtitle="Detail klien, relasi proyek, tagihan, kontrak, tiket dukungan, aktivitas, dan catatan.">
    <template #actions>
      <div class="flex gap-2">
        <button
          type="button"
          @click="router.get(clientsBaseUrl)"
          class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-600 transition hover:bg-stone-50 hover:text-stone-950"
        >
          <ArrowLeft class="h-4 w-4" />
          <span>Kembali</span>
        </button>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Client Stat Cards -->
      <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5 shadow-sm">
          <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Total Proyek</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ client.counts.projects }}</p>
        </div>
        <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5 shadow-sm">
          <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Total Tagihan</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ client.counts.invoices }}</p>
        </div>
        <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5 shadow-sm">
          <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Total Kontrak</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ client.counts.contracts }}</p>
        </div>
        <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5 shadow-sm">
          <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Tiket Dukungan</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ client.counts.tickets }}</p>
        </div>
      </section>

      <section class="rounded-[2.2rem] border border-stone-200 bg-white p-6 shadow-sm lg:p-8">
        <div class="flex flex-col gap-8 lg:flex-row">
          <!-- Side Info -->
          <aside class="w-full space-y-8 lg:w-[320px] lg:shrink-0 lg:border-r lg:border-stone-100 lg:pr-8">
            <div>
              <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-stone-400">Profil Klien</p>
              <div class="mt-4 flex h-16 w-16 items-center justify-center rounded-3xl bg-stone-100 text-2xl font-bold text-stone-400">
                {{ client.company_name.charAt(0) }}
              </div>
              <h3 class="mt-4 text-xl font-bold text-stone-950">{{ client.company_name }}</h3>
              <p class="mt-1 text-sm text-stone-500">{{ client.industry || 'Industri tidak spesifik' }}</p>
            </div>

            <div class="space-y-4 text-sm">
              <div class="flex items-center gap-3 text-stone-600">
                <User class="h-4 w-4 text-stone-400" />
                <p>{{ client.pic_name || 'Tanpa PIC' }}</p>
              </div>
              <div class="flex items-center gap-3 text-stone-600">
                <Mail class="h-4 w-4 text-stone-400" />
                <p>{{ client.email || 'Email tidak tersedia' }}</p>
              </div>
              <div class="flex items-center gap-3 text-stone-600">
                <Phone class="h-4 w-4 text-stone-400" />
                <p>{{ client.phone || 'Telepon tidak tersedia' }}</p>
              </div>
              <div class="flex items-start gap-3 text-stone-600">
                <MapPin class="h-4 w-4 shrink-0 text-stone-400" />
                <p>{{ client.address || '-' }}<br />{{ client.city || '-' }}, {{ client.province || '-' }}</p>
              </div>
            </div>

            <div class="space-y-1">
              <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-stone-400">Petugas Penanggung Jawab</p>
              <div class="mt-3 flex items-center gap-3 rounded-2xl bg-stone-50 p-3">
                <div class="h-8 w-8 rounded-full bg-stone-200"></div>
                <p class="text-sm font-semibold text-stone-700">{{ client.assigned_user?.name || 'Belum ada petugas' }}</p>
              </div>
            </div>
          </aside>

          <!-- Tabs Area -->
          <div class="flex-1 min-w-0">
            <nav class="flex flex-wrap gap-2 border-b border-stone-100 pb-4">
              <button
                v-for="tab in tabOptions"
                :key="tab.id"
                @click="activeTab = tab.id"
                class="rounded-full px-5 py-2 text-sm font-semibold transition-all"
                :class="activeTab === tab.id ? 'bg-stone-950 text-white shadow-md' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 hover:text-stone-950'"
              >
                {{ tab.label }}
              </button>
            </nav>

            <!-- Tab Content -->
            <div class="mt-6">
                <!-- Tab 1: Ringkasan -->
                <div v-if="activeTab === 'overview'" class="space-y-6">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-[1.6rem] border border-stone-200 p-5">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-stone-400">Status Klien</p>
                            <p class="mt-3 text-lg font-semibold text-stone-950 uppercase tracking-wider">{{ translateStatus(client.status) }}</p>
                        </div>
                        <div class="rounded-[1.6rem] border border-stone-200 p-5">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-stone-400">Akses Portal</p>
                            <p class="mt-3 text-lg font-semibold text-stone-950">{{ client.portal_access ? 'Aktif' : 'Nonaktif' }}</p>
                        </div>
                    </div>
                    
                    <div class="rounded-[1.8rem] border border-stone-200 bg-stone-50/50 p-6">
                        <h4 class="text-sm font-bold uppercase tracking-widest text-stone-400 mb-4">Catatan Internal</h4>
                        <p class="text-sm leading-7 text-stone-700 whitespace-pre-wrap">{{ client.notes || 'Tidak ada catatan internal.' }}</p>
                    </div>
                </div>

                <!-- Tab 2: Proyek -->
                <div v-else-if="activeTab === 'projects'" class="space-y-6">
                    <div class="flex justify-end">
                        <button
                        @click="router.get(projectsBaseUrl, { client_id: client.id, open_modal: 'project' })"
                        class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-stone-900/10 transition hover:scale-[1.02] active:scale-[0.98]"
                        >
                        <Briefcase class="h-4 w-4" />
                        <span>Buat Proyek Baru</span>
                        </button>
                    </div>
                    <div class="space-y-4">
                        <article v-for="project in tabs.projects" :key="project.id" class="flex items-center justify-between rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5 hover:border-stone-400 transition-colors">
                            <div>
                                <p class="font-bold text-stone-950">{{ project.name }}</p>
                                <p class="mt-1 text-xs text-stone-500">Mulai {{ project.start_date_label || '-' }} / Anggaran {{ project.budget_label }}</p>
                            </div>
                            <div class="text-right">
                                <span class="rounded-full bg-white px-3 py-1 text-[11px] font-bold uppercase tracking-[0.14em] text-stone-600 border border-stone-100 shadow-sm">{{ translateProjectStatus(project.status) }}</span>
                                <p class="mt-2 text-xs font-bold text-stone-900">{{ project.progress }}% Selesai</p>
                            </div>
                        </article>
                        <div v-if="tabs.projects.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-14 text-center text-sm text-stone-500">
                            Belum ada proyek aktif untuk klien ini.
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Infrastruktur -->
                <div v-else-if="activeTab === 'digital_services'" class="space-y-6">
                    <div v-for="site in tabs.digital_services" :key="site.id" class="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                        <div class="space-y-1">
                            <h4 class="text-lg font-bold text-stone-950">{{ site.name }}</h4>
                            <p class="text-sm text-amber-700 font-medium">{{ site.url }}</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest" :class="site.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-100 text-stone-500'">
                            {{ site.status === 'active' ? 'Aktif' : 'Nonaktif' }}
                        </span>
                        </div>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl bg-stone-50 p-4 border border-stone-100">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-stone-400 mb-2">Domain & SSL</p>
                                <div v-if="site.domain" class="space-y-1 text-sm text-stone-700">
                                    <p class="font-bold">{{ site.domain.name }}</p>
                                    <p class="text-xs text-stone-500">Registrar: {{ site.domain.registrar }}</p>
                                    <p class="text-xs text-stone-500">Kedaluwarsa: {{ site.domain.expiry_date || '-' }}</p>
                                </div>
                                <p v-else class="text-sm text-stone-400 italic">Data domain tidak terhubung.</p>
                            </div>
                            <div class="rounded-2xl bg-stone-50 p-4 border border-stone-100">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-stone-400 mb-2">Hosting / VPS</p>
                                <div v-if="site.server" class="space-y-1 text-sm text-stone-700">
                                    <p class="font-bold">{{ site.server.name }}</p>
                                    <p class="text-xs text-stone-500">Provider: {{ site.server.provider }}</p>
                                    <p class="text-xs text-stone-500">IP: {{ site.server.ip_address }}</p>
                                </div>
                                <p v-else class="text-sm text-stone-400 italic">Data server tidak terhubung.</p>
                            </div>
                        </div>
                    </div>
                    <div v-if="tabs.digital_services.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-14 text-center text-sm text-stone-500">
                        Tidak ada data layanan digital (domain/hosting) untuk klien ini.
                    </div>
                </div>

                <!-- Tab 4: Keuangan -->
                <div v-else-if="activeTab === 'invoices'" class="space-y-6">
                    <div class="flex justify-end">
                        <button
                        @click="router.get(invoicesBaseUrl, { client_id: client.id, open_modal: 'invoice' })"
                        class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-stone-900/10 transition hover:scale-[1.02] active:scale-[0.98]"
                        >
                        <Receipt class="h-4 w-4" />
                        <span>Buat Tagihan Baru</span>
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">
                                <tr>
                                    <th class="px-4 py-3">Nomor Tagihan</th>
                                    <th class="px-4 py-3">Jatuh Tempo</th>
                                    <th class="px-4 py-3">Total</th>
                                    <th class="px-4 py-3 text-right">Status Pakasir</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                <tr v-for="invoice in tabs.invoices" :key="invoice.id" class="hover:bg-stone-50 transition-colors">
                                    <td class="px-4 py-4 font-bold text-stone-900">{{ invoice.number }}</td>
                                    <td class="px-4 py-4 text-stone-600">{{ invoice.due_date_label || '-' }}</td>
                                    <td class="px-4 py-4 font-bold text-stone-900">{{ invoice.total_label }}</td>
                                    <td class="px-4 py-4 text-right">
                                    <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.14em]" :class="invoiceStatusClass(invoice.status)">
                                        {{ translateInvoiceStatus(invoice.status) }}
                                    </span>
                                    </td>
                                </tr>
                                <tr v-if="tabs.invoices.length === 0">
                                    <td colspan="4" class="px-4 py-14 text-center text-stone-500 italic">Belum ada riwayat tagihan.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 5: Kontrak -->
                <div v-else-if="activeTab === 'contracts'" class="space-y-6">
                    <div class="flex justify-end">
                        <button
                        @click="showContractModal = true"
                        class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-stone-900/10 transition hover:scale-[1.02] active:scale-[0.98]"
                        >
                        <Plus class="h-4 w-4" />
                        <span>Buat Kontrak Baru</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">
                                <tr>
                                    <th class="px-4 py-3 border-b border-stone-100">Judul Kontrak</th>
                                    <th class="px-4 py-3 border-b border-stone-100">Masa Berlaku</th>
                                    <th class="px-4 py-3 border-b border-stone-100">Nilai Deal</th>
                                    <th class="px-4 py-3 border-b border-stone-100 text-right">Status Legal</th>
                                    <th class="px-4 py-3 border-b border-stone-100 text-right w-[180px]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                <tr v-for="contract in tabs.contracts" :key="contract.id" class="hover:bg-stone-50 transition-colors group/row">
                                    <td class="px-4 py-4 font-bold text-stone-900">{{ contract.title }}</td>
                                    <td class="px-4 py-4 text-stone-600">{{ contract.start_date_label || '-' }} - {{ contract.end_date_label || '-' }}</td>
                                    <td class="px-4 py-4 font-bold text-stone-900">{{ contract.value_label }}</td>
                                    <td class="px-4 py-4 text-right">
                                        <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.14em]" :class="contractStatusClass(contract.status)">
                                            {{ translateContractStatus(contract.status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover/row:opacity-100 transition-opacity">
                                            <button @click="viewContract(contract)" class="p-2 text-stone-400 hover:text-stone-900 transition-colors" title="Lihat Detail"><ExternalLink class="h-4 w-4" /></button>
                                            <button @click="editContract(contract)" class="p-2 text-stone-400 hover:text-amber-600 transition-colors" title="Ubah"><Pencil class="h-4 w-4" /></button>
                                            <button @click="sendContractWA(contract)" class="p-2 text-stone-400 hover:text-sky-600 transition-colors" title="Kirim WA"><Send class="h-4 w-4" /></button>
                                            <button @click="downloadContractPdf(contract)" class="p-2 text-stone-400 hover:text-stone-900 transition-colors" title="Unduh PDF"><Download class="h-4 w-4" /></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="tabs.contracts.length === 0">
                                    <td colspan="5" class="px-4 py-14 text-center text-stone-500 italic">Belum ada dokumen kontrak.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 6: Tiket -->
                <div v-else-if="activeTab === 'tickets'" class="space-y-6">
                    <div class="flex justify-end">
                        <button
                        @click="router.get(ticketsBaseUrl, { client_id: client.id, open_modal: 'ticket' })"
                        class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-stone-900/10 transition hover:scale-[1.02] active:scale-[0.98]"
                        >
                        <Ticket class="h-4 w-4" />
                        <span>Buat Tiket Baru</span>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <article v-for="ticket in tabs.tickets" :key="ticket.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5 hover:border-stone-400 transition-colors">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="font-bold text-stone-900">#{{ ticket.id.substring(0, 8) }} - {{ ticket.title }}</p>
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider" :class="priorityClass(ticket.priority)">{{ translatePriority(ticket.priority) }}</span>
                                    </div>
                                    <p class="mt-1 text-sm text-stone-600 line-clamp-1">{{ ticket.description }}</p>
                                    <p class="mt-2 text-xs text-stone-500">Dibuat: {{ ticket.created_at_label || '-' }} / SLA: {{ ticket.sla_due_at_label || '-' }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-wider" :class="ticketStatusClass(ticket.status)">
                                        {{ translateTicketStatus(ticket.status) }}
                                    </span>
                                    <div class="mt-3 flex justify-end gap-2">
                                        <button @click="router.get(ticketsBaseUrl)" class="text-xs font-bold text-stone-900 underline decoration-amber-400 underline-offset-4">Balas</button>
                                    </div>
                                </div>
                            </div>
                        </article>
                        <div v-if="!tabs.tickets || tabs.tickets.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-14 text-center text-sm text-stone-500">
                            Belum ada tiket dukungan untuk klien ini.
                        </div>
                    </div>
                </div>

                <!-- Tab 7: Aktivitas -->
                <div v-else-if="activeTab === 'activity'" class="space-y-6">
                    <form class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5" @submit.prevent="submitActivity">
                        <textarea
                        v-model="activityForm.content"
                        rows="3"
                        placeholder="Catat aktivitas atau interaksi terbaru dengan klien ini..."
                        class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400"
                        ></textarea>
                        <div v-if="activityForm.errors.content" class="mt-2 text-xs text-rose-500">{{ activityForm.errors.content }}</div>
                        <div class="mt-3 flex justify-end">
                            <button
                                type="submit"
                                :disabled="activityForm.processing"
                                class="rounded-xl bg-stone-950 px-5 py-2 text-xs font-bold uppercase tracking-[0.18em] text-white transition hover:bg-stone-800 disabled:opacity-50"
                            >
                                {{ activityForm.processing ? 'Menyimpan...' : 'Simpan Aktivitas' }}
                            </button>
                        </div>
                    </form>

                    <div class="space-y-4">
                        <article v-for="activity in activities" :key="activity.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-stone-900">{{ activity.description }}</p>
                                    <p class="mt-2 text-xs text-stone-500">{{ activity.user?.name || 'Sistem' }} / {{ activity.created_at || '-' }}</p>
                                </div>
                                <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]" :class="activityBadgeClass(activity.metadata?.action)">
                                    {{ translateAction(activity.metadata?.action || activity.type) }}
                                </span>
                            </div>
                        </article>
                        <div v-if="activities.length === 0" class="py-10 text-center text-sm text-stone-500 italic">
                            Belum ada riwayat aktivitas untuk klien ini.
                        </div>
                    </div>
                </div>
            </div> <!-- End of Tab Content -->
          </div> <!-- End of flex-1 min-w-0 -->
        </div> <!-- End of flex flex-col gap-8 lg:flex-row -->
      </section>
    </div>

    <ContractGeneratorModal
      :show="showContractModal"
      :workspace="workspace"
      :client="client"
      :projects="tabs.projects"
      :initial-data="editingContractData"
      @close="showContractModal = false; editingContractData = null"
      @success="router.reload({ only: ['tabs'] })"
    />
  </WorkspaceLayout>
</template>

<script setup>
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import {
  ArrowLeft,
  Mail,
  MapPin,
  Phone,
  Plus,
  User,
  Send,
  Download,
  ExternalLink,
  MoreVertical,
  Pencil,
  Trash2,
  Receipt,
  Briefcase,
  Ticket
} from 'lucide-vue-next'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'
import ContractGeneratorModal from '../../../Components/domain/crm/ContractGeneratorModal.vue'

const props = defineProps({
  workspace: Object,
  client: Object,
  activities: Array,
  tabs: {
    type: Object,
    default: () => ({
        projects: [],
        invoices: [],
        contracts: [],
        tickets: [],
        digital_services: []
    })
  },
})

const workspaceBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}`
const clientsBaseUrl = `${workspaceBaseUrl}/crm/clients`
const projectsBaseUrl = `${workspaceBaseUrl}/projects`
const invoicesBaseUrl = `${workspaceBaseUrl}/finance/invoices`
const ticketsBaseUrl = `${workspaceBaseUrl}/communication/support-tickets`
const clientActivitiesUrl = `${clientsBaseUrl}/${encodeURIComponent(props.client.id)}/activities`
const contractUrl = (contractId) => `${workspaceBaseUrl}/projects/contracts/${encodeURIComponent(contractId)}`
const contractSendWaUrl = (contractId) => `${contractUrl(contractId)}/send-wa`

const activeTab = ref('overview')
const showContractModal = ref(false)
const editingContractData = ref(null)

const tabOptions = computed(() => [
  { id: 'overview', label: 'Ringkasan' },
  { id: 'projects', label: `Proyek (${props.tabs.projects.length})` },
  { id: 'digital_services', label: `Infrastruktur (${props.tabs.digital_services.length})` },
  { id: 'invoices', label: `Keuangan (${props.tabs.invoices.length})` },
  { id: 'contracts', label: `Kontrak (${props.tabs.contracts.length})` },
  { id: 'tickets', label: `Tiket (${props.tabs.tickets?.length || 0})` },
  { id: 'activity', label: `Aktivitas (${props.activities.length})` },
])

const activityForm = useForm({
  content: '',
})

function submitActivity() {
  activityForm.post(clientActivitiesUrl, {
    preserveScroll: true,
    onSuccess: () => activityForm.reset(),
  })
}

function translateStatus(status) {
    return { active: 'Aktif', inactive: 'Nonaktif', on_hold: 'Ditahan' }[status] || status
}

function translateProjectStatus(status) {
    return { active: 'Berjalan', completed: 'Selesai', on_hold: 'Ditunda', cancelled: 'Batal' }[status] || status
}

function translateInvoiceStatus(status) {
    return { paid: 'Lunas', unpaid: 'Belum Lunas', overdue: 'Telat', partial: 'Dicicil', sent: 'Terkirim', draft: 'Draf' }[status] || status
}

function translateContractStatus(status) {
    return { signed: 'Ditandatangani', draft: 'Draf', expired: 'Kedaluwarsa', cancelled: 'Batal' }[status] || status
}

function translateTicketStatus(status) {
    return { open: 'Terbuka', in_progress: 'Diproses', resolved: 'Selesai', closed: 'Ditutup' }[status] || status
}

function translatePriority(priority) {
    return { urgent: 'Sangat Mendesak', high: 'Tinggi', medium: 'Sedang', low: 'Rendah' }[priority] || priority
}

function ticketStatusClass(status) {
    if (status === 'resolved') return 'bg-emerald-100 text-emerald-700'
    if (status === 'open') return 'bg-amber-100 text-amber-700'
    return 'bg-stone-100 text-stone-600'
}

function priorityClass(priority) {
    if (priority === 'urgent') return 'bg-rose-100 text-rose-700'
    if (priority === 'high') return 'bg-orange-100 text-orange-700'
    return 'bg-stone-100 text-stone-600'
}

function translateAction(action) {
    return { create: 'Dibuat', update: 'Diperbarui', delete: 'Dihapus', converted: 'Dikonversi', note: 'Catatan' }[action] || action
}

function invoiceStatusClass(status) {
    if (status === 'paid') return 'bg-emerald-100 text-emerald-700'
    if (status === 'overdue') return 'bg-rose-100 text-rose-700'
    return 'bg-stone-100 text-stone-600'
}

function contractStatusClass(status) {
    if (status === 'signed') return 'bg-emerald-100 text-emerald-700'
    if (status === 'expired') return 'bg-rose-100 text-rose-700'
    return 'bg-stone-100 text-stone-600'
}

function viewContract(contract) {
    router.get(contractUrl(contract.id))
}

function editContract(contract) {
    editingContractData.value = contract
    showContractModal.value = true
}

function sendContractWA(contract) {
    if (!confirm('Kirim draf kontrak ini ke klien via WhatsApp?')) return
    
    router.post(contractSendWaUrl(contract.id), {}, {
        preserveScroll: true,
    })
}

function downloadContractPdf(contract) {
    window.open(`${contractUrl(contract.id)}?print=true`, '_blank')
}

function activityBadgeClass(action) {
  const map = {
    create: 'bg-emerald-100 text-emerald-700',
    update: 'bg-blue-100 text-blue-700',
    delete: 'bg-rose-100 text-rose-700',
    converted: 'bg-amber-100 text-amber-700',
  }
  return map[action] || 'bg-stone-100 text-stone-600'
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
