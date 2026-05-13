<template>
  <WorkspaceLayout title="Detail Prospek" subtitle="Kontak, nilai kesepakatan, catatan, dan riwayat aktivitas prospek yang sedang diproses.">
    <template #actions>
      <div class="flex flex-wrap items-center justify-end gap-3">
        <button
          v-if="!lead.converted_at"
          type="button"
          @click="convertToClient"
          class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-emerald-700"
        >
          <UserPlus class="h-4 w-4" />
          <span>Ubah Jadi Klien</span>
        </button>

        <button
          type="button"
          @click="goBack"
          class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-stone-300 hover:text-stone-950"
        >
          <ArrowLeft class="h-4 w-4" />
          <span>Kembali ke Prospek</span>
        </button>
      </div>
    </template>

    <div class="space-y-6">
      <section class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_380px]">
        <article class="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
              <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em]" :class="priorityClass(lead.priority)">
                  {{ translatePriority(lead.priority) }}
                </span>
                <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-stone-600">
                  {{ lead.stage?.name || 'Tanpa Tahap' }}
                </span>
              </div>
              <h1 class="mt-4 text-4xl font-semibold tracking-[-0.06em] text-stone-950">{{ lead.name }}</h1>
              <p class="mt-2 text-base text-stone-500">{{ lead.company || 'Prospek perorangan' }}</p>
            </div>
            <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 px-5 py-4">
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Estimasi Nilai</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ lead.estimated_value_label }}</p>
            </div>
          </div>

          <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4">
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Kontak</p>
              <div class="mt-3 space-y-2 text-sm text-stone-700">
                <p>{{ lead.email || 'Email belum diisi' }}</p>
                <p>{{ lead.phone || 'WhatsApp belum diisi' }}</p>
              </div>
            </div>
            <div class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4">
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Alur Prospek</p>
              <div class="mt-3 space-y-2 text-sm text-stone-700">
                <p>{{ lead.pipeline?.name || 'Belum ada alur' }}</p>
                <p>{{ lead.stage?.name || 'Belum ada tahap' }}</p>
              </div>
            </div>
            <div class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4">
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Penanggung Jawab</p>
              <div class="mt-3 space-y-2 text-sm text-stone-700">
                <p>{{ lead.assignee?.name || 'Belum ada petugas' }}</p>
                <p>{{ lead.source || 'Sumber belum diisi' }}</p>
              </div>
            </div>
          </div>

          <div class="mt-6 rounded-[1.6rem] border border-stone-200 bg-white p-5">
            <div class="flex flex-wrap items-center gap-3">
              <div class="rounded-2xl bg-stone-950 px-4 py-3 text-white">
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-300">Skor AI</p>
                <p class="mt-2 text-2xl font-semibold">{{ lead.ai_score ?? 'Belum dinilai' }}</p>
              </div>
              <div class="rounded-2xl bg-stone-100 px-4 py-3 text-stone-700">
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Dibuat</p>
                <p class="mt-2 text-sm font-semibold">{{ lead.created_at_label || 'Tidak diketahui' }}</p>
              </div>
              <div v-if="lead.converted_client" class="rounded-2xl bg-emerald-50 px-4 py-3 text-emerald-700">
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-emerald-500">Klien Hasil Konversi</p>
                <p class="mt-2 text-sm font-semibold">{{ lead.converted_client.company_name }}</p>
              </div>
            </div>
          </div>
        </article>

        <aside class="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-stone-400">Catatan</p>
          <form class="mt-4 space-y-3" @submit.prevent="submitNotes">
            <div class="flex items-center justify-between gap-3">
              <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Catatan Internal</p>
              <p class="text-xs text-stone-500">Hanya terlihat oleh tim internal.</p>
            </div>
            <textarea
              v-model="notesForm.notes"
              rows="10"
              class="w-full rounded-[1.6rem] border border-stone-200 bg-stone-50 px-4 py-4 text-sm leading-7 text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
            ></textarea>
            <p v-if="notesForm.errors.notes" class="text-xs text-rose-500">{{ notesForm.errors.notes }}</p>
            <div class="flex justify-end">
              <button
                type="submit"
                :disabled="notesForm.processing"
                class="rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white transition-all hover:bg-stone-800 disabled:cursor-not-allowed disabled:opacity-60"
              >
                {{ notesForm.processing ? 'Menyimpan...' : 'Simpan Catatan' }}
              </button>
            </div>
          </form>
        </aside>
      </section>

      <section class="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="flex items-center justify-between gap-3">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-stone-400">Riwayat Aktivitas</p>
            <h2 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">Perubahan dan interaksi prospek</h2>
          </div>
          <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold text-stone-600">{{ activities.length }} item</span>
        </div>

        <form class="mt-6 rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5" @submit.prevent="submitActivity">
          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tambah Aktivitas</span>
            <p class="text-xs text-stone-500">Catat tindak lanjut, rapat, atau perubahan status penting.</p>
            <textarea
              v-model="activityForm.content"
              rows="3"
              class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400"
            ></textarea>
          </label>
          <p v-if="activityForm.errors.content" class="mt-2 text-xs text-rose-500">{{ activityForm.errors.content }}</p>
          <div class="mt-4 flex justify-end">
            <button
              type="submit"
              :disabled="activityForm.processing"
              class="rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white transition-all hover:bg-stone-800 disabled:cursor-not-allowed disabled:opacity-60"
            >
              {{ activityForm.processing ? 'Menyimpan...' : 'Simpan Aktivitas' }}
            </button>
          </div>
        </form>

        <div v-if="activities.length > 0" class="mt-8 space-y-4">
          <article v-for="activity in activities" :key="activity.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div>
                <p class="text-sm font-semibold text-stone-900">{{ activity.description }}</p>
                <p class="mt-2 text-xs text-stone-500">
                  {{ activity.user?.name || 'Sistem' }} / {{ activity.created_at || '-' }}
                </p>
              </div>
              <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]" :class="activityBadgeClass(activity.metadata?.action)">
                {{ translateAction(activity.metadata?.action || activity.type) }}
              </span>
            </div>

            <div v-if="activity.comments?.length" class="mt-4 space-y-3 border-t border-stone-200 pt-4">
              <div v-for="comment in activity.comments" :key="comment.id" class="rounded-2xl bg-white px-4 py-3 text-sm text-stone-600 shadow-sm">
                <p>{{ comment.content }}</p>
                <p class="mt-2 text-xs text-stone-400">{{ comment.user?.name || 'Sistem' }} / {{ comment.created_at || '-' }}</p>
              </div>
            </div>
          </article>
        </div>

        <div v-else class="mt-8 rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-14 text-center text-sm text-stone-500">
          Belum ada riwayat aktivitas untuk prospek ini.
        </div>
      </section>
    </div>
  </WorkspaceLayout>
</template>

<script setup>
import { router, useForm, Head } from '@inertiajs/vue3'
import { ArrowLeft, UserPlus } from 'lucide-vue-next'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'

const props = defineProps({
  workspace: {
    type: Object,
    required: true,
  },
  lead: {
    type: Object,
    required: true,
  },
  activities: {
    type: Array,
    default: () => [],
  },
})

const workspace = props.workspace
const lead = props.lead
const activities = props.activities
const workspaceBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}`
const leadsBaseUrl = `${workspaceBaseUrl}/crm/leads`

const notesForm = useForm({
  notes: lead.notes || '',
})

const activityForm = useForm({
  content: '',
})

function goBack() {
  router.get(leadsBaseUrl)
}

function convertToClient() {
  if (!confirm('Konversi prospek ini menjadi klien? Prospek akan ditandai sebagai "Berhasil" dan data klien baru akan dibuat.')) {
    return
  }

  router.post(`${leadsBaseUrl}/${encodeURIComponent(lead.id)}/convert`)
}

function submitNotes() {
  notesForm.patch(`${leadsBaseUrl}/${encodeURIComponent(lead.id)}/notes`, {
    preserveScroll: true,
  })
}

function submitActivity() {
  activityForm.post(`${leadsBaseUrl}/${encodeURIComponent(lead.id)}/activities`, {
    preserveScroll: true,
    onSuccess: () => {
      activityForm.reset()
    },
  })
}

function priorityClass(priority) {
  const map = {
    high: 'bg-rose-100 text-rose-700',
    medium: 'bg-amber-100 text-amber-700',
    low: 'bg-emerald-100 text-emerald-700',
  }

  return map[priority] || 'bg-stone-100 text-stone-600'
}

function translatePriority(priority) {
  return {
    high: 'Tinggi',
    medium: 'Sedang',
    low: 'Rendah',
  }[priority] || priority
}

function translateAction(action) {
  return {
    create: 'Dibuat',
    note: 'Catatan',
    update: 'Diperbarui',
    delete: 'Dihapus',
    converted: 'Dikonversi',
  }[action] || action
}

function activityBadgeClass(action) {
  const map = {
    create: 'bg-emerald-100 text-emerald-700',
    note: 'bg-amber-100 text-amber-700',
    update: 'bg-amber-100 text-amber-700',
    delete: 'bg-rose-100 text-rose-700',
    converted: 'bg-emerald-100 text-emerald-700',
  }

  return map[action] || 'bg-stone-100 text-stone-600'
}
</script>
