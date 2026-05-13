<template>
  <WorkspaceLayout
    title="Notifications"
    subtitle="Menu 38 untuk alert, reminder, escalation, dan notification digest workspace."
  >
    <template #actions>
      <button
        type="button"
        @click="openNotificationModal()"
        class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
      >
        <Plus class="h-4 w-4" />
        <span>Notifikasi Baru</span>
      </button>
    </template>

    <CommunicationLayout :workspace="workspace">
      <div class="space-y-6">
        <section class="project-hero-shell">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="project-hero-copy">
              <p class="project-hero-kicker">Menu 38 / Notifikasi</p>
              <h2 class="project-hero-title">Alert penting diringkas supaya tim cepat tahu mana yang harus direspons lebih dulu.</h2>
              <p class="project-hero-desc">
                Mention, reminder, due item, dan escalation bisa disimpan sebagai notifikasi workspace yang punya kategori, tone, status, dan due date.
              </p>
            </div>

            <div class="compact-stat-grid min-w-full gap-3 sm:min-w-[22rem] sm:grid-cols-4 xl:w-[34rem]">
              <div class="compact-stat-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Belum Dibaca</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ summary.unread }}</p>
              </div>
              <div class="compact-stat-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Kritikal</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ summary.critical }}</p>
              </div>
              <div class="compact-stat-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Hari Ini</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ summary.due_today }}</p>
              </div>
              <div class="compact-stat-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Semua</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ summary.total }}</p>
              </div>
            </div>
          </div>

          <div class="mt-4 grid gap-3 md:grid-cols-3">
            <div class="compact-stat-card">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Antrian</p>
              <p class="mt-2 text-sm leading-6 text-stone-600">{{ filteredNotifications.length }} notifikasi tampil setelah filter aktif.</p>
            </div>
            <div class="compact-stat-card">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Campuran Status</p>
              <p class="mt-2 text-sm leading-6 text-stone-600">{{ summary.read }} sudah dibaca dan {{ summary.unread }} masih belum dibaca.</p>
            </div>
            <div class="compact-stat-card">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Escalation</p>
              <p class="mt-2 text-sm leading-6 text-stone-600">{{ summary.critical }} alert kritikal sedang ditandai untuk perhatian lebih cepat.</p>
            </div>
          </div>
        </section>

        <section class="project-panel-shell">
          <div class="filter-panel-head mb-5">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Filter</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Cari notifikasi berdasarkan judul, kategori, tone, status, atau owner.</h2>
            </div>
            <div class="filter-meta-badge">
              <span class="font-semibold text-stone-950">{{ filteredNotifications.length }}</span> alert tampil
            </div>
          </div>

          <div class="filter-toolbar grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <label class="space-y-2 text-sm xl:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Cari</span>
              <input v-model="filterState.search" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Kategori</span>
              <select v-model="filterState.category" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="">Semua</option>
                <option v-for="item in options.categories" :key="item" :value="item">{{ formatOption(item) }}</option>
              </select>
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
              <select v-model="filterState.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="">Semua</option>
                <option v-for="item in options.statuses" :key="item" :value="item">{{ formatOption(item) }}</option>
              </select>
            </label>
          </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-[1.02fr_0.98fr]">
          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Arus Notifikasi</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Semua alert, reminder, dan signal operasional.</h2>
              </div>
              <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">{{ filteredNotifications.length }} item</span>
            </div>

            <div class="mt-5 space-y-4">
              <article v-for="notification in filteredNotifications" :key="notification.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5 transition hover:border-stone-300 hover:bg-white">
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div class="max-w-2xl">
                    <div class="flex flex-wrap items-center gap-2">
                      <h3 class="text-base font-semibold text-stone-950">{{ notification.title }}</h3>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="toneClass(notification.tone)">{{ formatOption(notification.tone) }}</span>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="statusClass(notification.status)">{{ formatOption(notification.status) }}</span>
                    </div>
                    <p class="mt-2 text-sm text-stone-500">{{ formatOption(notification.category) }} / {{ notification.user?.name || 'Workspace' }}</p>
                    <p class="mt-3 text-sm leading-6 text-stone-600">{{ notification.message || 'Belum ada isi pesan.' }}</p>
                    <div class="mt-4 flex flex-wrap gap-2 text-xs text-stone-500">
                      <span class="rounded-full bg-white px-3 py-1.5">{{ notification.due_at_label }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">{{ notification.read_at_label }}</span>
                      <span v-if="notification.source_type" class="rounded-full bg-white px-3 py-1.5">{{ notification.source_type }}</span>
                    </div>
                  </div>

                  <div class="flex items-center gap-2">
                    <button type="button" @click="quickStatus(notification, notification.status === 'read' ? 'unread' : 'read')" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                      <Check class="h-4 w-4" />
                    </button>
                    <button type="button" @click="openNotificationModal(notification)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                      <Pencil class="h-4 w-4" />
                    </button>
                    <button type="button" @click="deleteNotification(notification.id)" class="inline-flex items-center justify-center rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </div>
              </article>

              <div v-if="filteredNotifications.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-300 bg-stone-50 px-6 py-14 text-center text-sm leading-6 text-stone-500">
                Belum ada notifikasi yang cocok dengan filter saat ini.
              </div>
            </div>
          </article>

          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="border-b border-stone-200 pb-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Aturan Respons</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Cara membaca urgency dan state alert.</h2>
            </div>

            <div class="mt-5 space-y-4">
              <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <p class="text-sm font-semibold text-stone-950">Kritikal</p>
                <p class="mt-2 text-sm leading-6 text-stone-600">Gunakan untuk alert yang perlu respon cepat seperti payment issue, escalation client, atau automation failure penting.</p>
              </div>
              <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <p class="text-sm font-semibold text-stone-950">Belum Dibaca vs Dibaca</p>
                <p class="mt-2 text-sm leading-6 text-stone-600">Belum dibaca menandai alert yang belum disentuh. Dibaca dipakai saat notifikasi sudah ditinjau walau action lanjutannya belum selesai.</p>
              </div>
              <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <p class="text-sm font-semibold text-stone-950">Jatuh Tempo</p>
                <p class="mt-2 text-sm leading-6 text-stone-600">Jatuh tempo membantu memisahkan notifikasi yang hanya informatif dari yang punya deadline tindak lanjut.</p>
              </div>
            </div>
          </article>
        </section>
      </div>
    </CommunicationLayout>

    <Transition name="modal">
      <div v-if="showNotificationModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-stone-950/40 p-4 backdrop-blur-sm">
        <form class="w-full max-w-4xl rounded-[2rem] bg-white p-6 shadow-2xl" @submit.prevent="submitNotification">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Notifikasi</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ editingNotificationId ? 'Ubah Notifikasi' : 'Notifikasi Baru' }}</h3>
            </div>
            <button type="button" @click="closeNotificationModal" class="rounded-full p-2 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700">x</button>
          </div>

          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <label class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Judul</span>
              <input v-model="notificationForm.title" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
            </label>

            <label class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Pesan</span>
              <textarea v-model="notificationForm.message" rows="5" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Kategori</span>
              <select v-model="notificationForm.category" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option v-for="item in options.categories" :key="item" :value="item">{{ formatOption(item) }}</option>
              </select>
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tone</span>
              <select v-model="notificationForm.tone" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option v-for="item in options.tones" :key="item" :value="item">{{ formatOption(item) }}</option>
              </select>
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
              <select v-model="notificationForm.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option v-for="item in options.statuses" :key="item" :value="item">{{ formatOption(item) }}</option>
              </select>
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Pemilik</span>
              <select v-model="notificationForm.user_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="">Workspace</option>
                <option v-for="user in options.users" :key="user.id" :value="user.id">{{ user.name }}</option>
              </select>
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Jatuh Tempo</span>
              <input v-model="notificationForm.due_at" type="datetime-local" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Dibaca Pada</span>
              <input v-model="notificationForm.read_at" type="datetime-local" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tipe Sumber</span>
              <input v-model="notificationForm.source_type" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">ID Sumber</span>
              <input v-model="notificationForm.source_id" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
            </label>

            <label class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Action URL</span>
              <input v-model="notificationForm.action_url" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
            </label>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="closeNotificationModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button>
            <button type="submit" :disabled="notificationForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">
              {{ editingNotificationId ? 'Simpan Perubahan' : 'Buat Item' }}
            </button>
          </div>
        </form>
      </div>
    </Transition>
  </WorkspaceLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { Check, Pencil, Plus, Trash2 } from 'lucide-vue-next'
import WorkspaceLayout from '../../Layouts/WorkspaceLayout.vue'
import CommunicationLayout from '../../Layouts/CommunicationLayout.vue'

const props = defineProps({
  workspace: Object,
  notifications: Array,
  options: Object,
  summary: Object,
})

const notificationsBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}/communication/notifications`

const filterState = reactive({
  search: '',
  category: '',
  status: '',
})

const filteredNotifications = computed(() => props.notifications.filter((item) => {
  const search = filterState.search.toLowerCase()
  const matchesSearch = !search || [item.title, item.message, item.category, item.user?.name, item.source_type].filter(Boolean).some((value) => String(value).toLowerCase().includes(search))
  const matchesCategory = !filterState.category || item.category === filterState.category
  const matchesStatus = !filterState.status || item.status === filterState.status
  return matchesSearch && matchesCategory && matchesStatus
}))

function formatOption(value) {
  return String(value || '').replaceAll('-', ' ').replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase())
}

function toneClass(tone) {
  return {
    neutral: 'bg-stone-200 text-stone-700',
    info: 'bg-sky-100 text-sky-700',
    warning: 'bg-amber-100 text-amber-700',
    critical: 'bg-rose-100 text-rose-700',
  }[tone] || 'bg-stone-100 text-stone-600'
}

function statusClass(status) {
  return {
    unread: 'bg-sky-100 text-sky-700',
    read: 'bg-emerald-100 text-emerald-700',
    archived: 'bg-stone-200 text-stone-700',
  }[status] || 'bg-stone-100 text-stone-600'
}

const showNotificationModal = ref(false)
const editingNotificationId = ref(null)
const notificationForm = useForm({
  user_id: '',
  title: '',
  message: '',
  category: 'reminder',
  tone: 'neutral',
  status: 'unread',
  source_type: '',
  source_id: '',
  action_url: '',
  due_at: '',
  read_at: '',
})

function openNotificationModal(item = null) {
  editingNotificationId.value = item?.id || null
  notificationForm.reset()
  notificationForm.clearErrors()
  notificationForm.user_id = item?.user_id || ''
  notificationForm.title = item?.title || ''
  notificationForm.message = item?.message || ''
  notificationForm.category = item?.category || 'reminder'
  notificationForm.tone = item?.tone || 'neutral'
  notificationForm.status = item?.status || 'unread'
  notificationForm.source_type = item?.source_type || ''
  notificationForm.source_id = item?.source_id || ''
  notificationForm.action_url = item?.action_url || ''
  notificationForm.due_at = item?.due_at || ''
  notificationForm.read_at = item?.read_at || ''
  showNotificationModal.value = true
}

function closeNotificationModal() {
  showNotificationModal.value = false
  editingNotificationId.value = null
}

function submitNotification() {
  const url = editingNotificationId.value ? `${notificationsBaseUrl}/${encodeURIComponent(editingNotificationId.value)}` : `${notificationsBaseUrl}`
  const method = editingNotificationId.value ? notificationForm.patch : notificationForm.post
  method(url, { preserveScroll: true, onSuccess: () => closeNotificationModal() })
}

function quickStatus(item, status) {
  const form = useForm({
    user_id: item.user_id || '',
    title: item.title,
    message: item.message || '',
    category: item.category,
    tone: item.tone,
    status,
    source_type: item.source_type || '',
    source_id: item.source_id || '',
    action_url: item.action_url || '',
    due_at: item.due_at || '',
    read_at: status === 'read' ? (item.read_at || new Date().toISOString().slice(0, 16)) : '',
  })

  form.patch(`${notificationsBaseUrl}/${encodeURIComponent(item.id)}`, { preserveScroll: true })
}

function deleteNotification(id) {
  if (!confirm('Hapus notifikasi ini?')) return
  router.delete(`${notificationsBaseUrl}/${encodeURIComponent(id)}`, { preserveScroll: true })
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
