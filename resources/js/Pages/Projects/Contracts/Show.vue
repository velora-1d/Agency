<template>
  <WorkspaceLayout title="Detail Kontrak" subtitle="Detail kontrak untuk membaca isi dokumen, status legal, lampiran tertandatangan, dan jejak aktivitasnya.">
    <template #actions>
      <div class="flex flex-wrap items-center justify-end gap-3">
        <!-- Status Actions -->
        <button
          v-if="contract.status === 'draft'"
          type="button"
          @click="updateStatus('sent')"
          class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700"
        >
          <Send class="h-4 w-4" />
          <span>Tandai Terkirim</span>
        </button>

        <button
          v-if="contract.status === 'sent'"
          type="button"
          @click="showUploadModal = true"
          class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:bg-emerald-700"
        >
          <FileUp class="h-4 w-4" />
          <span>Upload PDF Tertandatangan</span>
        </button>

        <button
          type="button"
          @click="sendWA"
          class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:bg-emerald-700"
        >
          <MessageCircle class="h-4 w-4" />
          <span>Kirim ke WA</span>
        </button>

        <button
          v-if="contract.status === 'signed'"
          type="button"
          @click="createInvoiceFromContract"
          class="inline-flex items-center gap-2 rounded-2xl bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:bg-amber-600"
        >
          <Receipt class="h-4 w-4" />
          <span>Buat Invoice</span>
        </button>

        <button
          type="button"
          @click="downloadPdf"
          class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 shadow-sm transition-all hover:border-stone-300 hover:text-stone-950"
        >
          <FileDown class="h-4 w-4" />
          <span>Download PDF</span>
        </button>

        <button
          type="button"
          @click="goBack"
          class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 shadow-sm transition-all hover:border-stone-300 hover:text-stone-950"
        >
          <ArrowLeft class="h-4 w-4" />
          <span>Kembali ke Kontrak</span>
        </button>
      </div>
    </template>

    <ProjectLayout :workspace="workspace">
      <div class="space-y-6">
        <section class="grid gap-6 xl:grid-cols-[1fr_380px]">
          <article class="rounded-4xl border border-stone-200 bg-white p-8 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <div class="flex flex-wrap items-start justify-between gap-6">
            <div class="space-y-4">
              <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em]" :class="statusClass(contract.status)">
                  {{ statusLabel(contract.status) }}
                </span>
                <span v-if="contract.has_signed_document" class="flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-emerald-600">
                  <CheckCircle2 class="h-3 w-3" />
                  Dokumen Tertandatangan Terlampir
                </span>
              </div>
              <h1 class="text-4xl font-semibold tracking-[-0.06em] text-stone-950">{{ contract.title }}</h1>
            </div>

            <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 px-6 py-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Nilai Kontrak</p>
              <p class="mt-2 text-3xl font-semibold tracking-[-0.04em] text-stone-950">{{ contract.value_label }}</p>
            </div>
          </div>

          <div class="mt-8 grid gap-4 md:grid-cols-3">
            <div class="rounded-3xl border border-stone-200 bg-stone-50 p-4">
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Klien</p>
              <p class="mt-3 text-sm font-semibold text-stone-950">{{ contract.client?.name || 'Belum ada klien' }}</p>
              <p class="mt-2 text-xs text-stone-500">Pihak utama yang terhubung ke dokumen ini.</p>
            </div>
            <div class="rounded-3xl border border-stone-200 bg-stone-50 p-4">
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Project Terkait</p>
              <p class="mt-3 text-sm font-semibold text-stone-950">{{ contract.project?.name || 'Belum terhubung ke project' }}</p>
              <p class="mt-2 text-xs text-stone-500">Dipakai untuk sinkron status kerja dan milestone terkait.</p>
            </div>
            <div class="rounded-3xl border border-stone-200 bg-stone-50 p-4">
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status Tanda Tangan</p>
              <p class="mt-3 text-sm font-semibold text-stone-950">{{ contract.signed_at ? 'Sudah Ditandatangani' : 'Menunggu Tanda Tangan' }}</p>
              <p class="mt-2 text-xs text-stone-500">{{ contract.signed_at_label || 'Belum ada waktu tanda tangan tercatat.' }}</p>
            </div>
          </div>

          <div class="mt-10 border-t border-stone-100 pt-8">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Dokumen Kontrak (A4 PDF)</p>
            <div class="mt-4 overflow-hidden rounded-4xl border border-stone-200 bg-stone-100 shadow-inner">
              <iframe 
                :src="previewPdfUrl" 
                class="h-[800px] w-full border-none"
                title="Kontrak PDF Preview"
              ></iframe>
            </div>
          </div>

          <div class="mt-10 space-y-4 border-t border-stone-100 pt-8">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tanda Tangan Digital & Dokumen</p>

            <div v-if="contract.status === 'sent' && !contract.signed_at" class="rounded-2xl border border-blue-100 bg-blue-50/50 p-6">
              <div class="flex items-start gap-4">
                <div class="rounded-xl bg-blue-100 p-3 text-blue-600">
                  <LinkIcon class="h-6 w-6" />
                </div>
                <div class="flex-1 space-y-3">
                  <div>
                    <p class="text-sm font-bold text-blue-900">Link E-Sign Siap Dipakai</p>
                    <p class="text-xs text-blue-600">Kirim link di bawah ini ke client untuk ditandatangani secara digital.</p>
                  </div>
                  <div class="flex items-center gap-2">
                    <input readonly :value="contract.esign_url" class="flex-1 rounded-xl border border-blue-200 bg-white px-4 py-2 text-xs text-stone-600 outline-none" />
                    <button @click="copyEsignUrl" class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700">
                      Salin Link
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="contract.signed_at" class="rounded-2xl border border-emerald-100 bg-emerald-50/50 p-6">
              <div class="flex items-start gap-4">
                <div class="rounded-xl bg-emerald-100 p-3 text-emerald-600">
                  <CheckCircle2 class="h-6 w-6" />
                </div>
                <div>
                  <p class="text-sm font-bold text-emerald-900">Tertandatangan Digital</p>
                  <div class="mt-2 grid gap-x-8 gap-y-1 text-xs text-stone-600 sm:grid-cols-2">
                    <p><span class="font-semibold">Ditandatangani oleh:</span> {{ contract.signed_by_name }}</p>
                    <p><span class="font-semibold">Waktu tanda tangan:</span> {{ contract.signed_at_label }}</p>
                    <p><span class="font-semibold">IP Address:</span> {{ contract.signed_ip || 'N/A' }}</p>
                    <p><span class="font-semibold">Token keamanan:</span> Terverifikasi</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
              <div v-if="contract.signed_file_url" class="flex items-center justify-between gap-4 rounded-2xl border border-emerald-100 bg-emerald-50/50 p-4">
                <div class="flex items-center gap-3">
                  <div class="rounded-xl bg-emerald-100 p-2 text-emerald-600">
                    <FileText class="h-5 w-5" />
                  </div>
                  <div>
                    <p class="text-sm font-semibold text-emerald-900">Kontrak Tertandatangan</p>
                    <p class="text-xs text-emerald-600">Terverifikasi dan terunggah</p>
                  </div>
                </div>
                <a :href="contract.signed_file_url" target="_blank" class="rounded-xl bg-white px-3 py-2 text-xs font-bold text-emerald-700 shadow-sm hover:bg-emerald-100">
                  Unduh
                </a>
              </div>

              <div v-else class="flex items-center justify-center rounded-2xl border border-dashed border-stone-200 bg-stone-50 p-4 text-sm text-stone-400">
                Belum ada dokumen tanda tangan diunggah.
              </div>
            </div>
          </div>
        </article>

          <aside class="space-y-6">
            <div class="rounded-4xl border border-stone-200 bg-white p-6 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Ringkasan Siklus</p>
                  <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Masa aktif dan reminder</h2>
                </div>
                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold text-stone-600">{{ statusLabel(contract.status) }}</span>
              </div>

              <div class="mt-5 space-y-4">
                <div class="rounded-[1.4rem] border border-stone-200 bg-stone-50 p-4">
                  <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-white p-2 text-stone-500 shadow-sm">
                      <Calendar class="h-4 w-4" />
                    </div>
                    <div>
                      <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Tanggal Mulai</p>
                      <p class="mt-1 text-sm font-medium text-stone-900">{{ contract.start_date_label || 'Belum diatur' }}</p>
                    </div>
                  </div>
                </div>

                <div class="rounded-[1.4rem] border border-stone-200 bg-stone-50 p-4">
                  <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-white p-2 text-stone-500 shadow-sm">
                      <Clock class="h-4 w-4" />
                    </div>
                    <div>
                      <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Tanggal Berakhir</p>
                      <p class="mt-1 text-sm font-medium text-stone-900">{{ contract.end_date_label || 'Belum diatur' }}</p>
                    </div>
                  </div>
                </div>

                <div v-if="contract.end_date" class="rounded-[1.4rem] border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-900">
                  <p class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.18em] text-amber-700">
                    <Bell class="h-3.5 w-3.5" />
                    Pengingat Berakhir
                  </p>
                  <p class="mt-2 leading-6">Sistem akan mengirim pengingat otomatis {{ contract.reminder_days_before }} hari sebelum masa kontrak berakhir.</p>
                </div>
              </div>
            </div>

            <div class="rounded-4xl border border-stone-200 bg-white p-6 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Riwayat Aktivitas</p>
                  <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Jejak perubahan kontrak</h2>
                </div>
                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold text-stone-600">{{ activities.length }} items</span>
              </div>

              <div v-if="activities.length > 0" class="mt-6 space-y-4">
                <article v-for="activity in activities" :key="activity.id" class="rounded-[1.4rem] border border-stone-200 bg-stone-50 p-4">
                  <div class="flex items-start gap-3">
                    <div class="mt-1 h-2.5 w-2.5 flex-none rounded-full" :class="activityColorClass(activity.metadata?.color)"></div>
                    <div class="min-w-0">
                      <p class="text-sm font-medium text-stone-900">{{ activity.description }}</p>
                      <p class="mt-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-stone-400">{{ activity.created_at }}</p>
                    </div>
                  </div>
                </article>
              </div>

              <div v-else class="mt-6 rounded-[1.4rem] border border-dashed border-stone-200 bg-stone-50 px-4 py-10 text-center text-sm text-stone-500">
                Belum ada aktivitas yang tercatat untuk kontrak ini.
              </div>
            </div>

            <button
              type="button"
              @click="deleteContract"
              class="w-full rounded-2xl border border-rose-100 bg-rose-50/50 px-4 py-3 text-sm font-semibold text-rose-600 transition-all hover:bg-rose-100"
            >
              Hapus Kontrak
            </button>
          </aside>
        </section>
      </div>

      <Transition name="modal">
      <div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-4xl border border-white/20 bg-white p-8 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Upload Dokumen</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">PDF Kontrak Tertandatangan</h3>
            </div>
            <button type="button" @click="showUploadModal = false" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
              <X class="h-5 w-5" />
            </button>
          </div>

          <div class="mt-8 space-y-6">
            <div class="rounded-2xl border-2 border-dashed border-stone-200 bg-stone-50 p-8 text-center transition-all hover:border-stone-400">
              <input 
                type="file" 
                id="signed-pdf" 
                class="hidden" 
                accept="application/pdf"
                @change="handleFileUpload"
              />
              <label for="signed-pdf" class="cursor-pointer space-y-4">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-stone-200 text-stone-600">
                  <FileUp class="h-8 w-8" />
                </div>
                <div>
                  <p class="text-sm font-bold text-stone-900">Klik untuk memilih file PDF</p>
                  <p class="mt-1 text-xs text-stone-500">Maksimum ukuran file: 10MB</p>
                </div>
              </label>
              <div v-if="selectedFile" class="mt-6 flex items-center justify-center gap-2 text-sm font-semibold text-emerald-600">
                <CheckCircle2 class="h-4 w-4" />
                {{ selectedFile.name }}
              </div>
            </div>

            <div class="flex items-center justify-end gap-3">
              <button 
                type="button" 
                @click="showUploadModal = false" 
                class="rounded-2xl border border-stone-200 px-5 py-3 text-sm font-semibold text-stone-600 hover:bg-stone-100"
              >
                Batal
              </button>
              <button 
                type="button" 
                @click="submitUpload"
                :disabled="!selectedFile || isUploading"
                class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition-all hover:bg-stone-800 disabled:opacity-50"
              >
                {{ isUploading ? 'Mengunggah...' : 'Upload & Tandai Ditandatangani' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
    </ProjectLayout>
  </WorkspaceLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { 
  ArrowLeft, Send, FileUp, FileText, CheckCircle2, 
  Calendar, Clock, Bell, X, ArrowUpRight, LinkIcon, FileDown,
  MessageCircle, Receipt
} from 'lucide-vue-next'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'
import ProjectLayout from '../../../Layouts/ProjectLayout.vue'

const props = defineProps({
  workspace: Object,
  contract: Object,
  activities: Array,
})

const workspaceBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}`
const contractsBaseUrl = `${workspaceBaseUrl}/projects/contracts`
const previewPdfUrl = `${contractsBaseUrl}/${encodeURIComponent(props.contract.id)}/preview-pdf`

const showUploadModal = ref(false)
const selectedFile = ref(null)
const isUploading = ref(false)

function downloadPdf() {
  window.open(previewPdfUrl, '_blank')
}

function goBack() {
  router.get(contractsBaseUrl)
}

function sendWA() {
  if (!confirm('Kirim kontrak ini ke klien via WhatsApp?')) return
  router.post(`${contractsBaseUrl}/${encodeURIComponent(props.contract.id)}/send-wa`, {}, {
    preserveScroll: true,
  })
}

function createInvoiceFromContract() {
  const invoicesBaseUrl = `${workspaceBaseUrl}/finance/invoices`
  router.get(invoicesBaseUrl, {
    from_contract: props.contract.id,
    client_id: props.contract.client_id,
    project_id: props.contract.project_id,
    open_modal: 'invoice',
  })
}

function copyEsignUrl() {
  navigator.clipboard.writeText(props.contract.esign_url)
  alert('Link e-sign berhasil disalin!')
}

function statusLabel(status) {
  const map = {
    draft: 'Draft',
    sent: 'Terkirim',
    signed: 'Ditandatangani',
    expired: 'Berakhir',
  }

  return map[status] || status
}

function updateStatus(status) {
  if (!confirm(`Ubah status kontrak menjadi ${statusLabel(status)}?`)) return
  
  router.patch(`${contractsBaseUrl}/${encodeURIComponent(props.contract.id)}/status`, {
    status: status
  }, {
    preserveScroll: true
  })
}

function handleFileUpload(event) {
  selectedFile.value = event.target.files[0]
}

function submitUpload() {
  if (!selectedFile.value) return
  
  isUploading.value = true
  const formData = new FormData()
  formData.append('file', selectedFile.value)
  
  router.post(`${contractsBaseUrl}/${encodeURIComponent(props.contract.id)}/upload-signed`, formData, {
    onSuccess: () => {
      showUploadModal.value = false
      selectedFile.value = null
      isUploading.value = false
    },
    onError: () => {
      isUploading.value = false
    }
  })
}

function deleteContract() {
  if (!confirm('Hapus kontrak ini secara permanen? Dokumen yang diunggah juga akan dihapus.')) return
  
  router.delete(`${contractsBaseUrl}/${encodeURIComponent(props.contract.id)}`)
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

function activityColorClass(color) {
  const map = {
    emerald: 'bg-emerald-500',
    amber: 'bg-amber-500',
    blue: 'bg-blue-500',
    rose: 'bg-rose-500',
  }
  return map[color] || 'bg-stone-400'
}
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.25s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(0.96); }
</style>
