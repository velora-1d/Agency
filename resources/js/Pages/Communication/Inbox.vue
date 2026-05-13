<template>
  <WorkspaceLayout
    title="Kotak Masuk"
    subtitle="Menu 37 untuk internal chat dan WhatsApp inbox yang dibaca dalam satu workspace."
  >
    <template #actions>
      <button
        type="button"
        @click="openConversationModal()"
        class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
      >
        <Plus class="h-4 w-4" />
        <span>Percakapan Baru</span>
      </button>
    </template>

    <CommunicationLayout :workspace="workspace">
      <div class="space-y-6">
        <section class="project-hero-shell">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="project-hero-copy">
              <p class="project-hero-kicker">Menu 37 / Chat / Kotak Masuk</p>
              <h2 class="project-hero-title">Percakapan internal dan WhatsApp ditarik ke satu antrian kerja yang bisa langsung ditindak.</h2>
              <p class="project-hero-desc">
                Assign owner, label, status, dan jejak pesan terbaru dibaca di satu panel supaya follow-up client maupun tim tidak tercecer.
              </p>
            </div>

            <div class="compact-stat-grid min-w-full gap-3 sm:min-w-[22rem] sm:grid-cols-4 xl:w-[34rem]">
              <div class="compact-stat-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Semua</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ summary.total }}</p>
              </div>
              <div class="compact-stat-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Internal</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ summary.internal }}</p>
              </div>
              <div class="compact-stat-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">WhatsApp</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ summary.whatsapp }}</p>
              </div>
              <div class="compact-stat-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Belum Dibaca</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ summary.unread }}</p>
              </div>
            </div>
          </div>

          <div class="mt-4 grid gap-3 md:grid-cols-3">
            <div class="compact-stat-card">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Antrian Aktif</p>
              <p class="mt-2 text-sm leading-6 text-stone-600">{{ summary.open }} conversation masih aktif di antrian kerja.</p>
            </div>
            <div class="compact-stat-card">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Tindak Lanjut</p>
              <p class="mt-2 text-sm leading-6 text-stone-600">{{ summary.pending }} conversation menunggu respons atau handoff berikutnya.</p>
            </div>
            <div class="compact-stat-card">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Selesai</p>
              <p class="mt-2 text-sm leading-6 text-stone-600">{{ summary.resolved }} conversation sudah ditutup sebagai selesai.</p>
            </div>
          </div>
        </section>

        <section class="project-panel-shell">
          <div class="filter-panel-head mb-5">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Filter Antrian</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Cari percakapan berdasarkan nama, klien, label, tipe, atau status.</h2>
            </div>
            <div class="filter-meta-badge">
              <span class="font-semibold text-stone-950">{{ filteredConversations.length }}</span> percakapan tampil
            </div>
          </div>

          <div class="filter-toolbar grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <label class="space-y-2 text-sm xl:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Cari</span>
              <input
                v-model="filterState.search"
                type="text"
                class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
              />
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Jenis</span>
              <select v-model="filterState.type" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="">Semua</option>
                <option value="internal">Internal</option>
                <option value="whatsapp">WhatsApp</option>
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

        <section class="grid gap-4 xl:grid-cols-[0.95fr_1.05fr]">
          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Daftar Percakapan</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Antrian semua percakapan workspace.</h2>
              </div>
              <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">
                {{ filteredConversations.length }} item
              </span>
            </div>

            <div class="mt-5 space-y-4">
              <article
                v-for="conversation in filteredConversations"
                :key="conversation.id"
                class="rounded-[1.6rem] border p-5 transition"
                :class="selectedConversation?.id === conversation.id ? 'border-stone-900 bg-stone-950 text-white shadow-[0_18px_50px_rgba(28,25,23,0.18)]' : 'border-stone-200 bg-stone-50 hover:border-stone-300 hover:bg-white'"
              >
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div class="max-w-2xl">
                    <div class="flex flex-wrap items-center gap-2">
                      <button
                        type="button"
                        @click="selectedConversationId = conversation.id"
                        class="text-left text-base font-semibold transition"
                        :class="selectedConversation?.id === conversation.id ? 'text-white' : 'text-stone-950 hover:text-sky-700'"
                      >
                        {{ conversationDisplayName(conversation) }}
                      </button>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="conversationTypeClass(conversation.type)">
                        {{ formatOption(conversation.type) }}
                      </span>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="conversationStatusClass(conversation.status, selectedConversation?.id === conversation.id)">
                        {{ formatOption(conversation.status) }}
                      </span>
                      <span v-if="conversation.unread_count" class="rounded-full bg-rose-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-rose-700">
                        {{ conversation.unread_count }} belum dibaca
                      </span>
                    </div>

                    <p class="mt-2 text-sm leading-6" :class="selectedConversation?.id === conversation.id ? 'text-stone-300' : 'text-stone-500'">
                      {{ conversation.latest_preview }}
                    </p>

                    <div class="mt-4 flex flex-wrap gap-2 text-xs">
                      <span class="rounded-full px-3 py-1.5" :class="selectedConversation?.id === conversation.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">
                        {{ conversation.label || 'tanpa label' }}
                      </span>
                      <span class="rounded-full px-3 py-1.5" :class="selectedConversation?.id === conversation.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">
                        {{ conversation.assignee?.name || 'Belum ditugaskan' }}
                      </span>
                      <span class="rounded-full px-3 py-1.5" :class="selectedConversation?.id === conversation.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">
                        {{ conversation.last_message_at_label }}
                      </span>
                    </div>
                  </div>

                  <div class="text-right">
                    <p class="text-sm font-semibold" :class="selectedConversation?.id === conversation.id ? 'text-white' : 'text-stone-950'">{{ conversation.message_count }} pesan</p>
                    <p class="mt-1 text-xs" :class="selectedConversation?.id === conversation.id ? 'text-stone-300' : 'text-stone-500'">
                      {{ conversation.client?.name || conversation.lead?.name || conversation.wa_contact_phone || 'Belum ada data terkait' }}
                    </p>
                  </div>
                </div>
              </article>

              <div v-if="filteredConversations.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-300 bg-stone-50 px-6 py-14 text-center text-sm leading-6 text-stone-500">
                Belum ada percakapan yang cocok dengan filter saat ini.
              </div>
            </div>
          </article>

          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="border-b border-stone-200 pb-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Detail Percakapan</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Detail thread, penugasan, dan panel aksi.</h2>
            </div>

            <div v-if="selectedConversation" class="mt-5 space-y-5">
              <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div>
                    <div class="flex flex-wrap items-center gap-2">
                      <h3 class="text-lg font-semibold tracking-[-0.03em] text-stone-950">{{ conversationDisplayName(selectedConversation) }}</h3>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="conversationTypeClass(selectedConversation.type)">
                        {{ formatOption(selectedConversation.type) }}
                      </span>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="conversationStatusClass(selectedConversation.status)">
                        {{ formatOption(selectedConversation.status) }}
                      </span>
                    </div>
                    <p class="mt-2 text-sm leading-6 text-stone-600">
                      {{ selectedConversation.assignee?.name || 'Belum ditugaskan' }} / {{ selectedConversation.label || 'tanpa label' }} / {{ selectedConversation.last_message_at_label }}
                    </p>
                  </div>

                  <div class="flex flex-wrap gap-2">
                    <button type="button" @click="openConversationModal(selectedConversation)" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-3.5 py-2 text-xs font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                      <Pencil class="h-3.5 w-3.5" />
                      <span>Ubah</span>
                    </button>
                    <button type="button" @click="deleteConversation(selectedConversation.id)" class="inline-flex items-center gap-2 rounded-2xl border border-rose-200 bg-white px-3.5 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-50">
                      <Trash2 class="h-3.5 w-3.5" />
                      <span>Hapus</span>
                    </button>
                  </div>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-3">
                  <div class="rounded-[1.2rem] border border-stone-200 bg-white p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Klien</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedConversation.client?.name || 'Tanpa klien' }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-white p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Prospek</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedConversation.lead?.name || 'Tanpa lead' }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-white p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Kontak</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedConversation.wa_contact_phone || selectedConversation.wa_contact_name || 'Kanal internal' }}</p>
                  </div>
                </div>
              </div>

              <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Rangkaian Pesan</p>
                <div class="mt-4 space-y-3">
                  <div
                    v-for="message in selectedConversation.messages"
                    :key="message.id"
                    class="max-w-[92%] rounded-[1.3rem] border px-4 py-3"
                    :class="message.is_from_client ? 'mr-auto border-stone-200 bg-white' : 'ml-auto border-stone-900 bg-stone-950 text-white'"
                  >
                    <div class="flex items-center justify-between gap-3">
                      <p class="text-xs font-semibold uppercase tracking-[0.14em]" :class="message.is_from_client ? 'text-stone-500' : 'text-stone-300'">
                        {{ message.is_from_client ? 'Klien' : (message.sender?.name || 'Tim') }}
                      </p>
                      <p class="text-[11px]" :class="message.is_from_client ? 'text-stone-400' : 'text-stone-300'">{{ message.created_at_label }}</p>
                    </div>
                    <p class="mt-2 whitespace-pre-wrap text-sm leading-6" :class="message.is_from_client ? 'text-stone-700' : 'text-white'">
                      {{ message.content }}
                    </p>
                  </div>

                  <div v-if="selectedConversation.messages.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-300 bg-white px-5 py-12 text-center text-sm text-stone-500">
                    Belum ada pesan di conversation ini.
                  </div>
                </div>
              </div>

              <form class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5" @submit.prevent="submitMessage">
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Kirim Pesan</p>
                <div class="mt-4 space-y-4">
                  <textarea
                    v-model="messageForm.content"
                    rows="5"
                    class="w-full rounded-[1.3rem] border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
                  ></textarea>

                  <div class="flex flex-wrap items-center justify-between gap-3">
                    <label class="inline-flex items-center gap-3 rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700">
                      <input v-model="messageForm.is_from_client" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-950" />
                      <span>Tandai sebagai pesan klien</span>
                    </label>

                    <button type="submit" :disabled="messageForm.processing || !selectedConversation" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">
                      Kirim Pesan
                    </button>
                  </div>
                </div>
              </form>
            </div>

            <div v-else class="mt-5 rounded-[1.6rem] border border-dashed border-stone-300 bg-stone-50 px-6 py-16 text-center text-sm leading-6 text-stone-500">
              Pilih conversation di sisi kiri untuk melihat detail dan membalas pesan.
            </div>
          </article>
        </section>
      </div>
    </CommunicationLayout>

    <Transition name="modal">
      <div v-if="showConversationModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-stone-950/40 p-4 backdrop-blur-sm">
        <form class="w-full max-w-4xl rounded-[2rem] bg-white p-6 shadow-2xl" @submit.prevent="submitConversation">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Percakapan</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ editingConversationId ? 'Ubah Percakapan' : 'Percakapan Baru' }}</h3>
            </div>
            <button type="button" @click="closeConversationModal" class="rounded-full p-2 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700">x</button>
          </div>

          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Jenis</span>
              <select v-model="conversationForm.type" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option v-for="item in options.types" :key="item" :value="item">{{ formatOption(item) }}</option>
              </select>
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
              <select v-model="conversationForm.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option v-for="item in options.statuses" :key="item" :value="item">{{ formatOption(item) }}</option>
              </select>
            </label>

            <label class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Nama Percakapan</span>
              <input v-model="conversationForm.name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Nama WhatsApp</span>
              <input v-model="conversationForm.wa_contact_name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Nomor WhatsApp</span>
              <input v-model="conversationForm.wa_contact_phone" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Klien</span>
              <select v-model="conversationForm.client_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="">Tanpa klien</option>
                <option v-for="item in options.clients" :key="item.id" :value="item.id">{{ item.name }}</option>
              </select>
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Prospek</span>
              <select v-model="conversationForm.lead_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="">Tanpa lead</option>
                <option v-for="item in options.leads" :key="item.id" :value="item.id">{{ item.name }}</option>
              </select>
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Penanggung Jawab</span>
              <select v-model="conversationForm.assigned_to" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="">Belum ditugaskan</option>
                <option v-for="item in options.users" :key="item.id" :value="item.id">{{ item.name }}</option>
              </select>
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Label</span>
              <select v-model="conversationForm.label" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="">Tanpa label</option>
                <option v-for="item in options.labels" :key="item" :value="item">{{ formatOption(item) }}</option>
              </select>
            </label>

            <label v-if="!editingConversationId" class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Pesan Awal</span>
              <textarea v-model="conversationForm.initial_message" rows="5" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
            </label>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="closeConversationModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button>
            <button type="submit" :disabled="conversationForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">
              {{ editingConversationId ? 'Simpan Perubahan' : 'Buat Percakapan' }}
            </button>
          </div>
        </form>
      </div>
    </Transition>
  </WorkspaceLayout>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { Pencil, Plus, Trash2 } from 'lucide-vue-next'
import WorkspaceLayout from '../../Layouts/WorkspaceLayout.vue'
import CommunicationLayout from '../../Layouts/CommunicationLayout.vue'

const props = defineProps({
  workspace: Object,
  conversations: Array,
  options: Object,
  summary: Object,
})

const inboxBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}/communication/inbox`

const filterState = reactive({
  search: '',
  type: '',
  status: '',
})

const filteredConversations = computed(() => props.conversations.filter((item) => {
  const search = filterState.search.toLowerCase()
  const matchesSearch = !search || [
    item.name,
    item.client?.name,
    item.lead?.name,
    item.assignee?.name,
    item.latest_preview,
    item.label,
    item.wa_contact_phone,
    item.wa_contact_name,
  ].filter(Boolean).some((value) => String(value).toLowerCase().includes(search))
  const matchesType = !filterState.type || item.type === filterState.type
  const matchesStatus = !filterState.status || item.status === filterState.status
  return matchesSearch && matchesType && matchesStatus
}))

const selectedConversationId = ref(props.conversations[0]?.id || null)

const selectedConversation = computed(() => {
  return filteredConversations.value.find((item) => item.id === selectedConversationId.value) ?? filteredConversations.value[0] ?? null
})

watch(filteredConversations, (items) => {
  if (!items.length) {
    selectedConversationId.value = null
    return
  }

  if (!items.find((item) => item.id === selectedConversationId.value)) {
    selectedConversationId.value = items[0].id
  }
}, { immediate: true })

function formatOption(value) {
  return String(value || '').replaceAll('-', ' ').replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase())
}

function conversationDisplayName(conversation) {
  return conversation.name || conversation.wa_contact_name || conversation.client?.name || conversation.lead?.name || conversation.wa_contact_phone || 'Untitled conversation'
}

function conversationTypeClass(type) {
  return {
    internal: 'bg-sky-100 text-sky-700',
    whatsapp: 'bg-emerald-100 text-emerald-700',
  }[type] || 'bg-stone-100 text-stone-600'
}

function conversationStatusClass(status, dark = false) {
  if (dark) {
    return {
      open: 'bg-white/10 text-stone-200',
      pending: 'bg-amber-200/20 text-amber-200',
      resolved: 'bg-emerald-200/20 text-emerald-200',
    }[status] || 'bg-white/10 text-stone-200'
  }

  return {
    open: 'bg-sky-100 text-sky-700',
    pending: 'bg-amber-100 text-amber-700',
    resolved: 'bg-emerald-100 text-emerald-700',
  }[status] || 'bg-stone-100 text-stone-600'
}

const showConversationModal = ref(false)
const editingConversationId = ref(null)
const conversationForm = useForm({
  type: 'internal',
  name: '',
  wa_contact_phone: '',
  wa_contact_name: '',
  client_id: '',
  lead_id: '',
  assigned_to: '',
  status: 'open',
  label: '',
  initial_message: '',
})

function openConversationModal(item = null) {
  editingConversationId.value = item?.id || null
  conversationForm.reset()
  conversationForm.clearErrors()
  conversationForm.type = item?.type || 'internal'
  conversationForm.name = item?.name || ''
  conversationForm.wa_contact_phone = item?.wa_contact_phone || ''
  conversationForm.wa_contact_name = item?.wa_contact_name || ''
  conversationForm.client_id = item?.client_id || ''
  conversationForm.lead_id = item?.lead_id || ''
  conversationForm.assigned_to = item?.assigned_to || ''
  conversationForm.status = item?.status || 'open'
  conversationForm.label = item?.label || ''
  conversationForm.initial_message = ''
  showConversationModal.value = true
}

function closeConversationModal() {
  showConversationModal.value = false
  editingConversationId.value = null
}

function submitConversation() {
  const url = editingConversationId.value
    ? `${inboxBaseUrl}/${encodeURIComponent(editingConversationId.value)}`
    : `${inboxBaseUrl}`

  const method = editingConversationId.value ? conversationForm.patch : conversationForm.post
  method(url, {
    preserveScroll: true,
    onSuccess: () => closeConversationModal(),
  })
}

function deleteConversation(id) {
  if (!confirm('Hapus percakapan ini?')) return
  router.delete(`${inboxBaseUrl}/${encodeURIComponent(id)}`, { preserveScroll: true })
}

const messageForm = useForm({
  content: '',
  type: 'text',
  is_from_client: false,
})

function submitMessage() {
  if (!selectedConversation.value) return

  messageForm.post(`${inboxBaseUrl}/${encodeURIComponent(selectedConversation.value.id)}/messages`, {
    preserveScroll: true,
    onSuccess: () => {
      messageForm.reset()
      messageForm.type = 'text'
      messageForm.is_from_client = false
    },
  })
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
