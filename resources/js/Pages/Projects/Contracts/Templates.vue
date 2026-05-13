<template>
  <WorkspaceLayout title="Template Kontrak" subtitle="Pustaka template kontrak untuk mempercepat pembuatan dokumen legal yang konsisten.">
    <template #actions>
      <div class="flex gap-3">
        <button
          type="button"
          @click="goBack"
          class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 shadow-sm transition-all hover:border-stone-300 hover:text-stone-950"
        >
          <ArrowLeft class="h-4 w-4" />
          <span>Kembali ke Kontrak</span>
        </button>
        <button
          type="button"
          @click="openCreateModal"
          class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
        >
          <Plus class="h-4 w-4" />
          <span>Tambah Template</span>
        </button>
      </div>
    </template>

    <ProjectLayout :workspace="workspace">
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
      <article 
        v-for="template in templates" 
        :key="template.id" 
        class="group relative flex flex-col justify-between overflow-hidden rounded-[2rem] border border-stone-200 bg-white p-6 shadow-[0_15px_40px_rgba(28,25,23,0.04)] transition-all hover:border-stone-300 hover:shadow-xl"
      >
        <div class="space-y-4">
          <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-stone-100 text-stone-600 transition-colors group-hover:bg-stone-950 group-hover:text-white">
            <FileText class="h-6 w-6" />
          </div>
          <div>
            <h3 class="text-lg font-bold text-stone-950">{{ template.name }}</h3>
            <p class="mt-2 line-clamp-3 text-sm text-stone-500 leading-relaxed">
              {{ truncateContent(template.content) }}
            </p>
          </div>
        </div>

        <div class="mt-8 flex items-center justify-between border-t border-stone-100 pt-5">
          <span class="text-[10px] font-bold uppercase tracking-widest text-stone-400">Template Siap Pakai</span>
          <div class="flex gap-2">
            <button 
              @click="openEditModal(template)"
              class="rounded-full p-2 text-stone-400 hover:bg-stone-100 hover:text-stone-900 transition-all"
            >
              <Pencil class="h-4 w-4" />
            </button>
            <button 
              @click="deleteTemplate(template)"
              class="rounded-full p-2 text-stone-400 hover:bg-rose-50 hover:text-rose-600 transition-all"
            >
              <Trash2 class="h-4 w-4" />
            </button>
          </div>
        </div>
      </article>

      <div v-if="templates.length === 0" class="col-span-full rounded-[2rem] border-2 border-dashed border-stone-200 p-20 text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-stone-50 text-stone-400">
          <FileText class="h-8 w-8" />
        </div>
        <h3 class="mt-4 text-lg font-semibold text-stone-900">Belum ada template.</h3>
        <p class="mt-1 text-sm text-stone-500">Buat template pertama Anda untuk menghemat waktu pembuatan kontrak.</p>
        <button @click="openCreateModal" class="mt-6 font-bold text-stone-950 underline underline-offset-4">Buat Template Sekarang</button>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Transition name="modal">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-[2.5rem] border border-white/20 bg-white p-8 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Pengaturan</p>
              <h3 class="mt-2 text-3xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditing ? 'Ubah Template' : 'Buat Template' }}</h3>
            </div>
            <button type="button" @click="closeModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
              <X class="h-6 w-6" />
            </button>
          </div>

          <form class="mt-8 space-y-6" @submit.prevent="submitForm">
            <div class="space-y-4">
              <label class="block space-y-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400 ml-1">Nama Template</span>
                <input 
                  v-model="form.name" 
                  type="text" 
                  
                  class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-5 py-4 text-sm text-stone-950 outline-none transition-all focus:border-stone-400 focus:bg-white" 
                />
                <p v-if="form.errors.name" class="text-xs text-rose-500">{{ form.errors.name }}</p>
              </label>

              <label class="block space-y-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400 ml-1">Konten Standar</span>
                <textarea 
                  v-model="form.content" 
                  rows="12" 
                 
                  class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-5 py-4 text-sm text-stone-950 outline-none transition-all focus:border-stone-400 focus:bg-white"
                ></textarea>
                <p v-if="form.errors.content" class="text-xs text-rose-500">{{ form.errors.content }}</p>
              </label>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 pt-4">
              <button type="button" @click="closeModal" class="rounded-2xl border border-stone-200 bg-white px-6 py-4 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900">
                Batal
              </button>
              <button 
                type="submit" 
                :disabled="form.processing" 
                class="rounded-2xl bg-stone-950 px-8 py-4 text-sm font-bold text-white transition-all hover:bg-stone-800 disabled:opacity-50"
              >
                {{ isEditing ? (form.processing ? 'Menyimpan...' : 'Simpan Template') : (form.processing ? 'Membuat...' : 'Buat Template') }}
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
import { Plus, ArrowLeft, FileText, Pencil, Trash2, X } from 'lucide-vue-next'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'
import ProjectLayout from '../../../Layouts/ProjectLayout.vue'

const props = defineProps({
  workspace: Object,
  templates: Array,
})

const workspaceBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}`
const templatesBaseUrl = `${workspaceBaseUrl}/projects/contracts/templates`

const showModal = ref(false)
const editingTemplateId = ref(null)
const isEditing = computed(() => editingTemplateId.value !== null)

const form = useForm({
  name: '',
  content: '',
})

function goBack() {
  router.get(`${workspaceBaseUrl}/projects/contracts`)
}

function openCreateModal() {
  editingTemplateId.value = null
  form.reset()
  form.clearErrors()
  showModal.value = true
}

function openEditModal(template) {
  editingTemplateId.value = template.id
  form.name = template.name
  form.content = template.content
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingTemplateId.value = null
  form.reset()
}

function submitForm() {
  const options = {
    preserveScroll: true,
    onSuccess: () => closeModal(),
  }

  if (isEditing.value) {
    form.patch(`${templatesBaseUrl}/${encodeURIComponent(editingTemplateId.value)}`, options)
  } else {
    form.post(templatesBaseUrl, options)
  }
}

function deleteTemplate(template) {
  if (!confirm(`Hapus template "${template.name}"? Tindakan ini tidak bisa dibatalkan.`)) return
  
  router.delete(`${templatesBaseUrl}/${encodeURIComponent(template.id)}`, {
    preserveScroll: true,
  })
}

function truncateContent(text) {
  if (!text) return ''
  return text.length > 150 ? text.substring(0, 150) + '...' : text
}
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.25s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(0.96); }
</style>
