<template>
  <WorkspaceLayout title="Contract Detail" subtitle="Pantau isi kontrak, lampiran dokumen bertanda tangan, dan riwayat aktivitas hukum.">
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
          <span>Mark as Sent</span>
        </button>

        <button
          v-if="contract.status === 'sent'"
          type="button"
          @click="showUploadModal = true"
          class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:bg-emerald-700"
        >
          <FileUp class="h-4 w-4" />
          <span>Upload Signed PDF</span>
        </button>

        <button
          type="button"
          @click="goBack"
          class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 shadow-sm transition-all hover:border-stone-300 hover:text-stone-950"
        >
          <ArrowLeft class="h-4 w-4" />
          <span>Back to Contracts</span>
        </button>
      </div>
    </template>

    <div class="space-y-6">
      <section class="grid gap-6 xl:grid-cols-[1fr_380px]">
        <!-- Main Content -->
        <article class="rounded-[2rem] border border-stone-200 bg-white p-8 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <div class="flex flex-wrap items-start justify-between gap-6">
            <div class="space-y-4">
              <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em]" :class="statusClass(contract.status)">
                  {{ contract.status }}
                </span>
                <span v-if="contract.has_signed_document" class="flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-emerald-600">
                  <CheckCircle2 class="h-3 w-3" />
                  Signed Document Attached
                </span>
              </div>
              <h1 class="text-4xl font-semibold tracking-[-0.06em] text-stone-950">{{ contract.title }}</h1>
            </div>
            
            <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 px-6 py-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Contract Value</p>
              <p class="mt-2 text-3xl font-semibold tracking-[-0.04em] text-stone-950">{{ contract.value_label }}</p>
            </div>
          </div>

          <div class="mt-10 grid gap-6 md:grid-cols-2">
            <div class="space-y-1">
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Client Information</p>
              <p class="text-lg font-medium text-stone-900">{{ contract.client?.name || 'No Client Assigned' }}</p>
            </div>
            <div class="space-y-1">
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Related Project</p>
              <p class="text-lg font-medium text-stone-900">{{ contract.project?.name || 'No Project Linked' }}</p>
            </div>
          </div>

          <div class="mt-10 border-t border-stone-100 pt-8">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Contract Content / Summary</p>
            <div class="mt-4 prose prose-stone max-w-none text-stone-700 leading-relaxed whitespace-pre-wrap">
              {{ contract.content || 'No content provided for this contract.' }}
            </div>
          </div>

          <!-- Documents Section -->
          <div class="mt-10 space-y-4 border-t border-stone-100 pt-8">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Digital Signature & Documents</p>
            
            <div v-if="contract.status === 'sent' && !contract.signed_at" class="rounded-2xl border border-blue-100 bg-blue-50/50 p-6">
              <div class="flex items-start gap-4">
                <div class="rounded-xl bg-blue-100 p-3 text-blue-600">
                  <LinkIcon class="h-6 w-6" />
                </div>
                <div class="flex-1 space-y-3">
                  <div>
                    <p class="text-sm font-bold text-blue-900">E-Signature Link Ready</p>
                    <p class="text-xs text-blue-600">Kirim link di bawah ini ke client untuk ditandatangani secara digital.</p>
                  </div>
                  <div class="flex items-center gap-2">
                    <input readonly :value="contract.esign_url" class="flex-1 rounded-xl border border-blue-200 bg-white px-4 py-2 text-xs text-stone-600 outline-none" />
                    <button @click="copyEsignUrl" class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700">
                      Copy Link
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
                  <p class="text-sm font-bold text-emerald-900">Digitally Signed</p>
                  <div class="mt-2 grid gap-x-8 gap-y-1 text-xs text-stone-600 sm:grid-cols-2">
                    <p><span class="font-semibold">Signed By:</span> {{ contract.signed_by_name }}</p>
                    <p><span class="font-semibold">Signed At:</span> {{ contract.signed_at_label }}</p>
                    <p><span class="font-semibold">IP Address:</span> {{ contract.signed_ip || 'N/A' }}</p>
                    <p><span class="font-semibold">Security Token:</span> Verified</p>
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
                    <p class="text-sm font-semibold text-emerald-900">Signed Contract</p>
                    <p class="text-xs text-emerald-600">Verified & Uploaded</p>
                  </div>
                </div>
                <a :href="contract.signed_file_url" target="_blank" class="rounded-xl bg-white px-3 py-2 text-xs font-bold text-emerald-700 shadow-sm hover:bg-emerald-100">
                  Download
                </a>
              </div>
              
              <div v-else class="flex items-center justify-center rounded-2xl border border-dashed border-stone-200 bg-stone-50 p-4 text-sm text-stone-400">
                Belum ada dokumen tanda tangan diunggah.
              </div>
            </div>
          </div>
        </article>

        <!-- Sidebar Info -->
        <aside class="space-y-6">
          <div class="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Validity Period</p>
            <div class="mt-4 space-y-4">
              <div class="flex items-center gap-3">
                <div class="rounded-xl bg-stone-100 p-2 text-stone-500">
                  <Calendar class="h-4 w-4" />
                </div>
                <div>
                  <p class="text-xs text-stone-400 uppercase font-bold tracking-wider">Start Date</p>
                  <p class="text-sm font-medium text-stone-900">{{ contract.start_date_label || 'Not set' }}</p>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <div class="rounded-xl bg-stone-100 p-2 text-stone-500">
                  <Clock class="h-4 w-4" />
                </div>
                <div>
                  <p class="text-xs text-stone-400 uppercase font-bold tracking-wider">End Date</p>
                  <p class="text-sm font-medium text-stone-900">{{ contract.end_date_label || 'Not set' }}</p>
                </div>
              </div>
            </div>

            <div v-if="contract.end_date" class="mt-6 rounded-2xl bg-amber-50 p-4 text-xs text-amber-800">
              <p class="font-bold flex items-center gap-2">
                <Bell class="h-3 w-3" />
                Expiry Reminder
              </p>
              <p class="mt-1 leading-relaxed">Sistem akan mengirim pengingat otomatis {{ contract.reminder_days_before }} hari sebelum masa kontrak berakhir.</p>
            </div>
          </div>

          <div class="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Activity History</p>
            <div class="mt-6 space-y-6">
              <div v-for="activity in activities" :key="activity.id" class="flex gap-3">
                <div class="mt-1 h-2 w-2 flex-none rounded-full" :class="activityColorClass(activity.metadata?.color)"></div>
                <div>
                  <p class="text-sm text-stone-800">{{ activity.description }}</p>
                  <p class="mt-1 text-[10px] text-stone-400 uppercase font-bold">{{ activity.created_at }}</p>
                </div>
              </div>
            </div>
          </div>
          
          <button
            type="button"
            @click="deleteContract"
            class="w-full rounded-2xl border border-rose-100 bg-rose-50/50 px-4 py-3 text-sm font-semibold text-rose-600 transition-all hover:bg-rose-100"
          >
            Delete Contract
          </button>
        </aside>
      </section>
    </div>

    <!-- Upload Signed PDF Modal -->
    <Transition name="modal">
      <div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-[2rem] border border-white/20 bg-white p-8 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Upload Document</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">Signed Contract PDF</h3>
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
                  <p class="text-sm font-bold text-stone-900">Click to select PDF file</p>
                  <p class="mt-1 text-xs text-stone-500">Maximum file size: 10MB</p>
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
                Cancel
              </button>
              <button 
                type="button" 
                @click="submitUpload"
                :disabled="!selectedFile || isUploading"
                class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition-all hover:bg-stone-800 disabled:opacity-50"
              >
                {{ isUploading ? 'Uploading...' : 'Upload & Mark as Signed' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </WorkspaceLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { 
  ArrowLeft, Send, FileUp, FileText, CheckCircle2, 
  Calendar, Clock, Bell, X, ArrowUpRight, LinkIcon 
} from 'lucide-vue-next'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'

const props = defineProps({
  workspace: Object,
  contract: Object,
  activities: Array,
})

const workspaceBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}`
const contractsBaseUrl = `${workspaceBaseUrl}/projects/contracts`

const showUploadModal = ref(false)
const selectedFile = ref(null)
const isUploading = ref(false)

function goBack() {
  router.get(contractsBaseUrl)
}

function copyEsignUrl() {
  navigator.clipboard.writeText(props.contract.esign_url)
  alert('Link e-signature berhasil disalin!')
}

function updateStatus(status) {
  if (!confirm(`Ubah status kontrak menjadi ${status}?`)) return
  
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
