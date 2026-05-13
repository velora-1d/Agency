<template>
  <WorkspaceLayout
    title="Tool AI"
    subtitle="Menu 25 difokuskan untuk katalog tool AI workspace: writing, summary, reply suggestion, report, scoring, assistant, dan prompt guardrail yang tetap mudah diaudit tim."
  >
    <template #actions>
      <button
        type="button"
        @click="openToolModal()"
        class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
      >
        <Plus class="h-4 w-4" />
        <span>Tool Baru</span>
      </button>
    </template>

    <AutomationLayout :workspace="workspace">
      <div class="space-y-6">
        <section class="rounded-[1.6rem] border border-stone-200 bg-white p-4 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="max-w-3xl">
              <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-stone-400">Menu 25 / Tool AI</p>
              <h2 class="mt-2 text-2xl font-semibold tracking-[-0.05em] text-stone-950">Tool AI yang rapi dipetakan per use case, bukan tercecer per prompt.</h2>
              <p class="mt-2 text-sm leading-6 text-stone-500">
                Writing, summary, reply, scoring, sampai assistant dibaca seperti produk internal: ada surface, guardrail, dan konteks operasionalnya.
              </p>
            </div>

            <div class="grid min-w-full gap-3 sm:min-w-[22rem] sm:grid-cols-4 xl:w-[34rem]">
              <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Aktif</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ toolSummary.active_tools }}</p>
              </div>
              <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Total Tool</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ toolSummary.total_tools }}</p>
              </div>
              <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Draft</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ toolSummary.draft_tools }}</p>
              </div>
              <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Prompt / Bulan</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ toolSummary.prompts_this_month }}</p>
              </div>
            </div>
          </div>

          <div class="mt-4 grid gap-3 md:grid-cols-3">
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Surface Asisten</p>
              <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ toolSummary.assistant_surfaces }}</p>
            </div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Guardrail</p>
              <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ toolSummary.guardrails_count }}</p>
            </div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Pustaka Preset</p>
              <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ presetItems.length }}</p>
            </div>
          </div>
        </section>

        <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <div class="filter-panel-head mb-5">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Filter</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Cari tool berdasarkan nama, jenis, status, atau surface kerja yang dilayani.</h2>
            </div>
            <div class="filter-meta-badge">
              <span class="font-semibold text-stone-950">{{ filteredTools.length }}</span> tool tampil
            </div>
          </div>

          <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <label class="space-y-2 text-sm xl:col-span-3">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Cari</span>
              <input v-model="filterState.search" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tipe</span>
              <select v-model="filterState.type" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option v-for="item in filters.types" :key="item.value || 'all-type'" :value="item.value">{{ item.label }}</option>
              </select>
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
              <select v-model="filterState.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option v-for="item in filters.statuses" :key="item.value || 'all-status'" :value="item.value">{{ item.label }}</option>
              </select>
            </label>
          </div>

          <div class="mt-5 flex flex-wrap items-center gap-3">
            <button type="button" @click="resetFilters" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900">
              <RotateCcw class="h-4 w-4" />
              <span>Atur Ulang</span>
            </button>
          </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-[0.92fr_1.08fr]">
          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Papan Tool</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Semua tool AI yang hidup di workspace ini.</h2>
              </div>
              <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">
                {{ filteredTools.length }} items
              </span>
            </div>

            <div class="mt-5 space-y-4">
              <article
                v-for="tool in filteredTools"
                :key="tool.id"
                class="rounded-[1.6rem] border p-5 transition"
                :class="selectedTool?.id === tool.id ? 'border-stone-900 bg-stone-950 text-white shadow-[0_18px_50px_rgba(28,25,23,0.18)]' : 'border-stone-200 bg-stone-50 hover:border-stone-300 hover:bg-white'"
              >
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div class="max-w-2xl">
                    <div class="flex flex-wrap items-center gap-2">
                      <button type="button" @click="selectTool(tool.id)" class="text-left text-base font-semibold transition" :class="selectedTool?.id === tool.id ? 'text-white' : 'text-stone-950 hover:text-sky-700'">
                        {{ tool.name }}
                      </button>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="statusClass(tool.status)">
                        {{ tool.status }}
                      </span>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="selectedTool?.id === tool.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">
                        {{ tool.type }}
                      </span>
                    </div>

                    <p class="mt-2 text-sm leading-6" :class="selectedTool?.id === tool.id ? 'text-stone-300' : 'text-stone-500'">
                      {{ tool.description }}
                    </p>

                    <div class="mt-4 flex flex-wrap gap-2 text-xs">
                      <span class="rounded-full px-3 py-1.5" :class="selectedTool?.id === tool.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">
                        {{ tool.surface }}
                      </span>
                      <span class="rounded-full px-3 py-1.5" :class="selectedTool?.id === tool.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">
                        {{ tool.model }}
                      </span>
                    </div>
                  </div>

                  <div class="text-right">
                    <p class="text-sm font-semibold" :class="selectedTool?.id === tool.id ? 'text-white' : 'text-stone-950'">{{ tool.usage_label }}</p>
                    <p class="mt-1 text-xs" :class="selectedTool?.id === tool.id ? 'text-stone-300' : 'text-stone-500'">{{ tool.last_run_label }}</p>
                  </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                  <button type="button" @click="selectTool(tool.id)" class="inline-flex items-center gap-2 rounded-2xl border px-3.5 py-2 text-xs font-semibold transition" :class="selectedTool?.id === tool.id ? 'border-white/15 text-white hover:bg-white/10' : 'border-stone-200 bg-white text-stone-700 hover:border-stone-300 hover:text-stone-950'">
                    <Eye class="h-3.5 w-3.5" />
                    <span>Rincian</span>
                  </button>
                  <button type="button" @click="openToolModal(tool)" class="inline-flex items-center gap-2 rounded-2xl border px-3.5 py-2 text-xs font-semibold transition" :class="selectedTool?.id === tool.id ? 'border-white/15 text-white hover:bg-white/10' : 'border-stone-200 bg-white text-stone-700 hover:border-stone-300 hover:text-stone-950'">
                    <Pencil class="h-3.5 w-3.5" />
                    <span>Ubah</span>
                  </button>
                  <button type="button" @click="toggleStatus(tool.id)" class="inline-flex items-center gap-2 rounded-2xl px-3.5 py-2 text-xs font-semibold transition" :class="tool.status === 'active' ? 'border border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100' : 'border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'">
                    <Power class="h-3.5 w-3.5" />
                    <span>{{ tool.status === 'active' ? 'Jeda' : 'Aktifkan' }}</span>
                  </button>
                </div>
              </article>

              <div v-if="filteredTools.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-300 bg-stone-50 px-6 py-14 text-center text-sm leading-6 text-stone-500">
                Belum ada tool AI yang cocok dengan filter saat ini.
              </div>
            </div>
          </article>

          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div v-if="selectedTool" class="space-y-5">
              <div class="border-b border-stone-200 pb-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Pemeriksa Tool</p>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                      <h2 class="text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ selectedTool.name }}</h2>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="statusClass(selectedTool.status)">
                        {{ selectedTool.status }}
                      </span>
                    </div>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-stone-500">{{ selectedTool.description }}</p>
                  </div>

                  <div class="rounded-[1.3rem] border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-600">
                    <p class="font-semibold text-stone-950">{{ selectedTool.usage_label }}</p>
                    <p class="mt-1 text-xs text-stone-500">{{ selectedTool.last_run_label }}</p>
                  </div>
                </div>
              </div>

              <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Tipe</p>
                  <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedTool.type }}</p>
                </div>
                <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Surface</p>
                  <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedTool.surface }}</p>
                </div>
                <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Model</p>
                  <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedTool.model }}</p>
                </div>
                <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Workspace</p>
                  <p class="mt-2 text-sm font-semibold text-stone-950">{{ aiTools.workspace_name }}</p>
                </div>
              </div>

              <div class="grid gap-4 xl:grid-cols-[1.02fr_0.98fr]">
                <section class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                  <div class="flex items-center gap-2">
                    <Sparkles class="h-4 w-4 text-stone-500" />
                    <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-stone-500">Cakupan & Guardrail</h3>
                  </div>

                  <div class="mt-5 space-y-5">
                    <div>
                      <p class="text-sm font-semibold text-stone-950">Cakupan Surface</p>
                      <div class="mt-3 flex flex-wrap gap-2">
                        <span v-for="scope in selectedTool.scopes" :key="scope" class="rounded-full border border-stone-200 bg-white px-3 py-1.5 text-xs font-semibold text-stone-600">
                          {{ scope }}
                        </span>
                      </div>
                    </div>

                    <div>
                      <p class="text-sm font-semibold text-stone-950">Guardrail</p>
                      <div class="mt-3 space-y-2">
                        <div v-for="guardrail in selectedTool.guardrails" :key="guardrail" class="rounded-[1rem] border border-stone-200 bg-white px-3 py-3 text-sm text-stone-600">
                          {{ guardrail }}
                        </div>
                      </div>
                    </div>
                  </div>
                </section>

                <section class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                  <div class="flex items-center gap-2">
                    <Bot class="h-4 w-4 text-stone-500" />
                    <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-stone-500">Ringkasan Operasional</h3>
                  </div>

                  <div class="mt-5 space-y-3">
                    <article v-for="metric in selectedTool.metrics" :key="metric" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4 text-sm font-semibold text-stone-950">
                      {{ metric }}
                    </article>
                  </div>
                </section>
              </div>
            </div>

            <div v-else class="rounded-[1.6rem] border border-dashed border-stone-300 bg-stone-50 px-6 py-16 text-center text-sm text-stone-500">
              Pilih tool AI dari panel kiri untuk melihat detailnya.
            </div>
          </article>
        </section>

        <section class="grid gap-4 xl:grid-cols-[0.9fr_1.1fr]">
          <article class="rounded-[2rem] border border-stone-200 bg-stone-950 p-5 text-white shadow-[0_20px_60px_rgba(28,25,23,0.14)]">
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-amber-200/75">Stack Preset</p>
            <div class="mt-4 flex flex-wrap gap-2">
              <span v-for="preset in presetItems" :key="preset" class="rounded-full border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold uppercase tracking-[0.14em] text-stone-200">
                {{ preset }}
              </span>
            </div>
          </article>

          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Aktivitas AI Terbaru</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Log ringkas supaya tim tahu tool mana yang paling aktif.</h2>
              </div>
            </div>

            <div class="mt-5 space-y-3">
              <article v-for="log in logItems" :key="log.id" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                <div class="flex items-center justify-between gap-3">
                  <div>
                    <p class="text-sm font-semibold text-stone-950">{{ log.label }}</p>
                    <p class="mt-1 text-sm text-stone-500">{{ log.detail }}</p>
                  </div>
                  <p class="text-xs font-semibold uppercase tracking-[0.14em] text-stone-400">{{ log.time_label }}</p>
                </div>
              </article>
            </div>
          </article>
        </section>
      </div>

      <div v-if="showToolModal" class="fixed inset-0 z-[70] flex items-center justify-center bg-stone-950/40 px-4 py-8 backdrop-blur-sm">
        <div class="modal-panel max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-[2rem] border border-stone-200 bg-white p-6 shadow-[0_28px_90px_rgba(28,25,23,0.18)]">
          <div class="flex items-start justify-between gap-4 border-b border-stone-200 pb-5">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Form Tool AI</p>
              <h2 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ editingToolId ? 'Ubah Tool' : 'Tool Baru' }}</h2>
            </div>
            <button type="button" @click="closeToolModal" class="rounded-full border border-stone-200 p-2 text-stone-500 transition hover:border-stone-300 hover:text-stone-950">
              <X class="h-4 w-4" />
            </button>
          </div>

          <form class="mt-5 space-y-5" @submit.prevent="submitTool">
            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Nama Tool</span>
                <input v-model="toolForm.name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Tipe</span>
                <select v-model="toolForm.type" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white">
                  <option v-for="item in typeOptions" :key="item.value" :value="item.value">{{ item.label }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Surface</span>
                <input v-model="toolForm.surface" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Model</span>
                <input v-model="toolForm.model" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white" />
              </label>
            </div>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Deskripsi</span>
              <textarea v-model="toolForm.description" rows="4" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white"></textarea>
            </label>

            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Cakupan</span>
                <textarea v-model="toolForm.scopes" rows="3" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white"></textarea>
                <p class="text-xs text-stone-500">Pisahkan area kerja atau tim yang boleh memakai tool ini dengan koma.</p>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Guardrail</span>
                <textarea v-model="toolForm.guardrails" rows="3" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white"></textarea>
                <p class="text-xs text-stone-500">Tulis aturan operasional yang wajib dipatuhi sebelum output dipakai.</p>
              </label>
            </div>

            <div class="flex flex-wrap justify-end gap-3">
              <button type="button" @click="closeToolModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900">
                Batal
              </button>
              <button type="submit" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition-all hover:-translate-y-0.5 hover:bg-stone-800">
                {{ editingToolId ? 'Simpan Perubahan' : 'Buat Tool' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </AutomationLayout>
  </WorkspaceLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { Bot, Eye, Pencil, Plus, Power, RotateCcw, Sparkles, X } from 'lucide-vue-next'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'
import AutomationLayout from '../../../Layouts/AutomationLayout.vue'

const props = defineProps({
  workspace: { type: Object, required: true },
  ai_tools: { type: Object, required: true },
})

const aiTools = computed(() => props.ai_tools || {})
const filters = computed(() => aiTools.value.filters || { types: [], statuses: [] })
const presetItems = computed(() => aiTools.value.presets || [])
const logItems = computed(() => aiTools.value.logs || [])

const localTools = ref(cloneItems(aiTools.value.items || []))
const filterState = ref({
  search: '',
  type: '',
  status: '',
})
const selectedToolId = ref(localTools.value[0]?.id || null)
const showToolModal = ref(false)
const editingToolId = ref(null)
const typeOptions = computed(() => (filters.value.types || []).filter((item) => item.value))
const toolForm = ref(blankToolForm())

const filteredTools = computed(() => {
  return localTools.value.filter((tool) => {
    const search = filterState.value.search.trim().toLowerCase()
    const matchesSearch = !search || [tool.name, tool.surface, tool.model, tool.description].join(' ').toLowerCase().includes(search)
    const matchesType = !filterState.value.type || tool.type === filterState.value.type
    const matchesStatus = !filterState.value.status || tool.status === filterState.value.status

    return matchesSearch && matchesType && matchesStatus
  })
})

const selectedTool = computed(() => {
  return filteredTools.value.find((tool) => tool.id === selectedToolId.value)
    || localTools.value.find((tool) => tool.id === selectedToolId.value)
    || filteredTools.value[0]
    || null
})

const toolSummary = computed(() => {
  const allTools = localTools.value
  const activeTools = allTools.filter((tool) => tool.status === 'active').length
  const draftTools = allTools.filter((tool) => tool.status === 'draft').length
  const promptsThisMonth = allTools.reduce((total, tool) => {
    const number = Number.parseInt(String(tool.usage_label).replace(/[^0-9]/g, ''), 10)
    return total + (Number.isNaN(number) ? 0 : number)
  }, 0)

  return {
    total_tools: allTools.length,
    active_tools: activeTools,
    draft_tools: draftTools,
    prompts_this_month: promptsThisMonth,
    assistant_surfaces: new Set(allTools.map((tool) => tool.surface)).size,
    guardrails_count: allTools.reduce((total, tool) => total + (tool.guardrails?.length || 0), 0),
  }
})

watch(filteredTools, (items) => {
  if (items.length === 0) {
    selectedToolId.value = null
    return
  }

  if (!items.some((item) => item.id === selectedToolId.value)) {
    selectedToolId.value = items[0].id
  }
}, { immediate: true })

function blankToolForm() {
  return {
    name: '',
    type: defaultToolType(),
    surface: '',
    model: 'Laravel AI SDK',
    description: '',
    scopes: '',
    guardrails: '',
  }
}

function defaultToolType() {
  return (filters.value.types || []).find((item) => item.value)?.value || 'writing'
}

function selectTool(toolId) {
  selectedToolId.value = toolId
}

function resetFilters() {
  filterState.value = {
    search: '',
    type: '',
    status: '',
  }
}

function openToolModal(tool = null) {
  editingToolId.value = tool?.id || null
  showToolModal.value = true

  if (!tool) {
    toolForm.value = blankToolForm()
    return
  }

  toolForm.value = {
    name: tool.name,
    type: tool.type,
    surface: tool.surface,
    model: tool.model,
    description: tool.description,
    scopes: (tool.scopes || []).join(', '),
    guardrails: (tool.guardrails || []).join(', '),
  }
}

function closeToolModal() {
  showToolModal.value = false
  editingToolId.value = null
  toolForm.value = blankToolForm()
}

function submitTool() {
  const payload = {
    id: editingToolId.value || `tool-${Date.now()}`,
    name: toolForm.value.name || 'Tool tanpa nama',
    type: toolForm.value.type || 'writing',
    surface: toolForm.value.surface || 'Surface workspace',
    model: toolForm.value.model || 'Laravel AI SDK',
    status: editingToolId.value ? selectedTool.value?.status || 'draft' : 'draft',
    usage_label: editingToolId.value ? selectedTool.value?.usage_label || '0 prompts / month' : '0 prompts / month',
    last_run_label: editingToolId.value ? selectedTool.value?.last_run_label || 'Belum pernah dijalankan' : 'Belum pernah dijalankan',
    description: toolForm.value.description || 'Tool ini belum punya deskripsi operasional.',
    scopes: normalizeList(toolForm.value.scopes),
    guardrails: normalizeList(toolForm.value.guardrails),
    metrics: editingToolId.value ? selectedTool.value?.metrics || ['Belum ada metrik'] : ['Belum ada metrik'],
  }

  if (editingToolId.value) {
    localTools.value = localTools.value.map((tool) => tool.id === editingToolId.value ? payload : tool)
  } else {
    localTools.value = [payload, ...localTools.value]
  }

  selectedToolId.value = payload.id
  closeToolModal()
}

function toggleStatus(toolId) {
  localTools.value = localTools.value.map((tool) => {
    if (tool.id !== toolId) {
      return tool
    }

    return {
      ...tool,
      status: tool.status === 'active' ? 'paused' : 'active',
    }
  })
}

function statusClass(status) {
  const map = {
    active: 'bg-emerald-100 text-emerald-700',
    draft: 'bg-amber-100 text-amber-700',
    paused: 'bg-stone-200 text-stone-600',
  }

  return map[status] || 'bg-stone-100 text-stone-600'
}

function normalizeList(value) {
  return value
    .split(',')
    .map((item) => item.trim())
    .filter(Boolean)
}

function cloneItems(items) {
  return JSON.parse(JSON.stringify(items))
}
</script>

<style scoped>
.modal-panel {
  animation: modal-enter 0.24s ease;
}

@keyframes modal-enter {
  from {
    opacity: 0;
    transform: translateY(16px) scale(0.98);
  }

  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}
</style>
