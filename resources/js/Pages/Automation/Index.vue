<template>
  <WorkspaceLayout
    title="Otomasi"
    subtitle="Menu 24 difokuskan untuk otomasi alur kerja (workflow) lintas workspace: pemicu, langkah-langkah aksi, logika kondisi, coba ulang, log eksekusi, dan integrasi n8n yang tetap gampang dibaca tim."
  >
    <template #actions>
      <button
        type="button"
        @click="openWorkflowModal()"
        class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
      >
        <Plus class="h-4 w-4" />
        <span>Alur Kerja Baru</span>
      </button>
    </template>

    <AutomationLayout :workspace="workspace">
      <div class="space-y-6">
      <section class="rounded-[1.6rem] border border-stone-200 bg-white p-4 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div class="max-w-3xl">
            <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-stone-400">Menu 24 / Otomasi</p>
            <h2 class="mt-2 text-2xl font-semibold tracking-[-0.05em] text-stone-950">Otomasi alur kerja yang ringkas, tapi cukup kuat untuk lead, invoice, task, payment, form, dan support flow.</h2>
            <p class="mt-2 text-sm leading-6 text-stone-500">
              Pemicu, rantai aksi, logika kondisi, dan log eksekusi tetap ada, cuma area pembukanya dipadatkan supaya builder lebih mudah dibaca.
            </p>
          </div>

          <div class="grid min-w-full gap-3 sm:min-w-[22rem] sm:grid-cols-4 xl:w-[34rem]">
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Kesehatan Alur</p><p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ summary.success_rate }}%</p></div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Alur Kerja</p><p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ summary.total_workflows }}</p></div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Aktif</p><p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ summary.active_workflows }}</p></div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Eksekusi</p><p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ summary.total_runs }}</p></div>
          </div>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-3">
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Terjadwal</p><p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ summary.scheduled_workflows }}</p></div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Terhubung n8n</p><p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ summary.n8n_linked_workflows }}</p></div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Gagal</p><p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ summary.failed_runs }}</p></div>
        </div>
      </section>

      <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="filter-panel-head mb-5">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Filter</p>
            <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Cari alur kerja berdasarkan nama, status, jenis pemicu, atau templat asalnya.</h2>
          </div>
          <div class="filter-meta-badge">
            <span class="font-semibold text-stone-950">{{ workflowItems.length }}</span> alur kerja tampil
          </div>
        </div>

        <div class="filter-toolbar grid gap-4 md:grid-cols-2 xl:grid-cols-4">
          <label class="space-y-2 text-sm xl:col-span-2">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Cari</span>
            <input
              v-model="filterState.search"
              type="text"
              placeholder="Cari alur kerja..."
              class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
            />
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
            <select v-model="filterState.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Status</option>
              <option v-for="status in automation.options.statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Jenis Pemicu</span>
            <select v-model="filterState.trigger_type" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Tipe</option>
              <option v-for="type in automation.options.trigger_types" :key="type.value" :value="type.value">{{ type.label }}</option>
            </select>
          </label>
          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Templat</span>
            <select v-model="filterState.template" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Templat</option>
              <option v-for="template in templateItems" :key="template.key" :value="template.key">{{ template.name }}</option>
            </select>
          </label>
        </div>

        <div class="filter-actions mt-5 flex flex-wrap items-center gap-3">
          <button type="button" @click="applyFilters" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition-all hover:-translate-y-0.5 hover:bg-stone-800">
            <Filter class="h-4 w-4" />
            <span>Terapkan Filter</span>
          </button>
          <button type="button" @click="resetFilters" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900">
            <RotateCcw class="h-4 w-4" />
            <span>Atur Ulang</span>
          </button>
        </div>

        <div v-if="activeFilterChips.length" class="filter-chip-row mt-5">
          <button
            v-for="chip in activeFilterChips"
            :key="chip.key"
            type="button"
            @click="clearFilter(chip.key)"
            class="inline-flex items-center gap-2 rounded-full border border-stone-200 bg-stone-50 px-3 py-2 text-xs font-semibold text-stone-600 transition hover:border-stone-300 hover:bg-white hover:text-stone-950"
          >
            <span>{{ chip.label }}</span>
            <X class="h-3.5 w-3.5" />
          </button>
        </div>
      </section>

      <section class="grid gap-4 xl:grid-cols-[0.95fr_1.05fr]">
        <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Pustaka Alur Kerja</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Daftar workflow yang aktif dan yang masih perlu dirapikan.</h2>
            </div>
            <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">
              {{ workflowItems.length }} alur
            </span>
          </div>

          <div class="mt-5 space-y-4">
            <article
              v-for="workflow in workflowItems"
              :key="workflow.id"
              class="rounded-[1.6rem] border p-5 transition"
              :class="selectedWorkflow?.id === workflow.id ? 'border-stone-900 bg-stone-950 text-white shadow-[0_18px_50px_rgba(28,25,23,0.18)]' : 'border-stone-200 bg-stone-50 hover:border-stone-300 hover:bg-white'"
            >
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="max-w-2xl">
                  <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="selectWorkflow(workflow.id)" class="text-left text-base font-semibold transition" :class="selectedWorkflow?.id === workflow.id ? 'text-white' : 'text-stone-950 hover:text-emerald-700'">
                      {{ workflow.name }}
                    </button>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="workflow.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-200 text-stone-600'">
                      {{ workflow.is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="selectedWorkflow?.id === workflow.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">
                      {{ workflow.trigger_label }}
                    </span>
                  </div>

                  <p class="mt-2 text-sm leading-6" :class="selectedWorkflow?.id === workflow.id ? 'text-stone-300' : 'text-stone-500'">
                    {{ workflow.config.description || 'Alur kerja ini belum punya deskripsi operasional.' }}
                  </p>

                  <div class="mt-4 flex flex-wrap gap-2 text-xs">
                    <span class="rounded-full px-3 py-1.5" :class="selectedWorkflow?.id === workflow.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">
                      {{ workflow.config.trigger_type }}
                    </span>
                    <span class="rounded-full px-3 py-1.5" :class="selectedWorkflow?.id === workflow.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">
                      {{ workflow.counts.steps }} langkah
                    </span>
                    <span class="rounded-full px-3 py-1.5" :class="selectedWorkflow?.id === workflow.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">
                      {{ workflow.counts.conditions }} kondisi
                    </span>
                  </div>
                </div>

                <div class="text-right">
                  <p class="text-sm font-semibold" :class="selectedWorkflow?.id === workflow.id ? 'text-white' : 'text-stone-950'">{{ workflow.counts.runs }} eksekusi</p>
                  <p class="mt-1 text-xs" :class="selectedWorkflow?.id === workflow.id ? 'text-stone-300' : 'text-stone-500'">
                    {{ workflow.last_run?.started_at_label || 'Belum ada log eksekusi' }}
                  </p>
                </div>
              </div>

              <div class="mt-5 flex flex-wrap gap-2">
                <button
                  type="button"
                  @click="toggleWorkflow(workflow)"
                  class="inline-flex items-center gap-2 rounded-2xl px-3.5 py-2 text-xs font-semibold transition"
                  :class="workflow.is_active ? 'border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100' : 'border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                >
                  <Workflow class="h-3.5 w-3.5" />
                  <span>{{ workflow.is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                </button>
                <button type="button" @click="runWorkflowTest(workflow.id)" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-3.5 py-2 text-xs font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                  <Play class="h-3.5 w-3.5" />
                  <span>Uji Eksekusi</span>
                </button>
                <button type="button" @click="openWorkflowModal(workflow)" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-3.5 py-2 text-xs font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                  <Pencil class="h-3.5 w-3.5" />
                  <span>Ubah</span>
                </button>
                <button type="button" @click="deleteWorkflow(workflow.id)" class="inline-flex items-center gap-2 rounded-2xl border border-rose-200 bg-white px-3.5 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-50">
                  <Trash2 class="h-3.5 w-3.5" />
                  <span>Hapus</span>
                </button>
              </div>
            </article>

            <div v-if="workflowItems.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-300 bg-stone-50 px-6 py-14 text-center text-sm leading-6 text-stone-500">
              Belum ada alur kerja otomasi yang cocok dengan filter saat ini.
            </div>
          </div>
        </article>

        <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <div class="border-b border-stone-200 pb-5">
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Inspektur Alur Kerja</p>
            <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Detail alur kerja, rantai aksi, dan log eksekusi terbaru.</h2>
          </div>

          <div v-if="selectedWorkflow" class="mt-5 space-y-5">
            <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                  <div class="flex flex-wrap items-center gap-2">
                    <h3 class="text-lg font-semibold tracking-[-0.03em] text-stone-950">{{ selectedWorkflow.name }}</h3>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="selectedWorkflow.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-200 text-stone-600'">
                      {{ selectedWorkflow.is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                  </div>
                  <p class="mt-2 max-w-2xl text-sm leading-6 text-stone-600">
                    {{ selectedWorkflow.config.description || 'Belum ada deskripsi alur kerja.' }}
                  </p>
                </div>
                <div class="rounded-[1.3rem] border border-stone-200 bg-white px-4 py-3 text-sm text-stone-600">
                      <p class="font-semibold text-stone-950">{{ selectedWorkflow.last_run?.status_label || 'Belum Pernah Dieksekusi' }}</p>
                  <p class="mt-1 text-xs text-stone-500">{{ selectedWorkflow.last_run?.started_at_label || 'Alur kerja ini belum pernah dijalankan.' }}</p>
                </div>
              </div>

              <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-[1.2rem] border border-stone-200 bg-white p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Pemicu</p>
                  <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedWorkflow.trigger_label }}</p>
                  <p class="mt-1 text-xs text-stone-500">{{ selectedWorkflow.config.trigger_type }}</p>
                </div>
                <div class="rounded-[1.2rem] border border-stone-200 bg-white p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Coba Ulang</p>
                  <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedWorkflow.config.retry_enabled ? 'Aktif' : 'Nonaktif' }}</p>
                  <p class="mt-1 text-xs text-stone-500">maks {{ selectedWorkflow.config.retry_limit }} coba ulang</p>
                </div>
                <div class="rounded-[1.2rem] border border-stone-200 bg-white p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Tautan n8n</p>
                  <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedWorkflow.n8n_workflow_id || 'Belum terhubung' }}</p>
                  <p class="mt-1 text-xs text-stone-500 line-clamp-1">{{ selectedWorkflow.n8n_webhook_url || 'Webhook belum diisi' }}</p>
                </div>
                <div class="rounded-[1.2rem] border border-stone-200 bg-white p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Cakupan Workspace</p>
                  <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedWorkflow.config.scope }}</p>
                  <p class="mt-1 text-xs text-stone-500">{{ selectedWorkflow.counts.runs }} total log</p>
                </div>
              </div>
            </div>

            <div class="grid gap-4 xl:grid-cols-[1.05fr_0.95fr]">
              <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                <div class="flex items-center gap-2">
                  <Layers3 class="h-4 w-4 text-stone-500" />
                  <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-stone-500">Rantai Aksi</h3>
                </div>

                <div class="mt-5 space-y-4">
                  <div
                    v-for="(step, index) in selectedWorkflow.config.steps"
                    :key="`${selectedWorkflow.id}-step-${index}`"
                    class="rounded-[1.4rem] border border-stone-200 bg-stone-50 p-4"
                  >
                    <div class="flex items-center gap-3">
                      <div class="flex h-9 w-9 items-center justify-center rounded-full bg-stone-950 text-xs font-bold uppercase tracking-[0.18em] text-white">
                        {{ index + 1 }}
                      </div>
                      <div>
                        <p class="text-sm font-semibold text-stone-950">{{ step.label }}</p>
                        <p class="mt-1 text-xs uppercase tracking-[0.18em] text-stone-400">{{ stepTypeLabel(step.type) }}</p>
                      </div>
                    </div>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                      <div class="rounded-[1rem] border border-stone-200 bg-white px-3 py-3 text-sm text-stone-600">
                        <span class="font-semibold text-stone-950">Target:</span> {{ step.target || 'Belum ada target' }}
                      </div>
                      <div class="rounded-[1rem] border border-stone-200 bg-white px-3 py-3 text-sm text-stone-600">
                        <span class="font-semibold text-stone-950">Pesan:</span> {{ step.message || 'Belum ada pesan' }}
                      </div>
                    </div>
                  </div>

                  <div v-if="selectedWorkflow.config.steps.length === 0" class="rounded-[1.4rem] border border-dashed border-stone-300 bg-stone-50 px-4 py-10 text-center text-sm text-stone-500">
                    Belum ada langkah aksi di alur kerja ini.
                  </div>
                </div>
              </div>

              <div class="space-y-4">
                <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                  <div class="flex items-center gap-2">
                    <GitBranch class="h-4 w-4 text-stone-500" />
                    <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-stone-500">Logika Kondisi</h3>
                  </div>

                  <div class="mt-5 space-y-3">
                    <div
                      v-for="(condition, index) in selectedWorkflow.config.conditions"
                      :key="`${selectedWorkflow.id}-condition-${index}`"
                      class="rounded-[1.2rem] border border-stone-200 bg-stone-50 px-4 py-4"
                    >
                      <p class="text-sm font-semibold text-stone-950">Kondisi {{ index + 1 }}</p>
                      <p class="mt-2 text-sm leading-6 text-stone-600">
                        <span class="font-semibold text-stone-950">{{ condition.field || 'field' }}</span>
                        {{ operatorLabel(condition.operator) }}
                        <span class="font-semibold text-stone-950">{{ condition.value || 'value' }}</span>
                      </p>
                    </div>

                    <div v-if="selectedWorkflow.config.conditions.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-300 bg-stone-50 px-4 py-8 text-center text-sm text-stone-500">
                      Alur kerja ini belum punya logika kondisi. Semua aksi akan jalan linear.
                    </div>
                  </div>
                </div>

                <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                  <div class="flex items-center gap-2">
                    <Clock3 class="h-4 w-4 text-stone-500" />
                    <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-stone-500">Log Terpilih</h3>
                  </div>

                  <div class="mt-5 space-y-3">
                    <div
                      v-for="log in selectedWorkflowLogs"
                      :key="log.id"
                      class="rounded-[1.2rem] border border-stone-200 bg-stone-50 px-4 py-4"
                    >
                      <div class="flex flex-wrap items-center justify-between gap-3">
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="logStatusClass(log.status)">
                          {{ log.status_label }}
                        </span>
                        <span class="text-xs text-stone-500">{{ log.started_at_human }}</span>
                      </div>
                      <p class="mt-3 text-sm font-semibold text-stone-950">{{ log.message || 'Log eksekusi tercatat tanpa pesan tambahan.' }}</p>
                      <p class="mt-2 text-xs uppercase tracking-[0.18em] text-stone-400">{{ log.trigger_label }} • Upaya {{ log.attempt }}</p>
                    </div>

                    <div v-if="selectedWorkflowLogs.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-300 bg-stone-50 px-4 py-8 text-center text-sm text-stone-500">
                      Belum ada log eksekusi untuk alur kerja terpilih ini.
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="mt-8 rounded-[1.8rem] border border-dashed border-stone-300 bg-stone-50 px-6 py-16 text-center text-sm leading-6 text-stone-500">
            Pilih alur kerja di panel kiri untuk melihat rantai aksi, kondisi, dan log eksekusi.
          </div>
        </article>
      </section>

      <section class="grid gap-4 xl:grid-cols-[1.05fr_0.95fr]">
        <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Templat</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Preset alur kerja untuk ngebut setup awal tanpa mulai dari kosong.</h2>
            </div>
            <Sparkles class="h-5 w-5 text-amber-500" />
          </div>

          <div class="mt-5 grid gap-4 lg:grid-cols-2">
            <article
              v-for="template in templateItems"
              :key="template.key"
              class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5"
            >
              <div class="flex items-start justify-between gap-3">
                <div>
                  <p class="text-sm font-semibold text-stone-950">{{ template.name }}</p>
                  <p class="mt-2 text-sm leading-6 text-stone-600">{{ template.description }}</p>
                </div>
                <span class="rounded-full bg-white px-3 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-stone-500">
                  {{ template.trigger_label }}
                </span>
              </div>

              <div class="mt-4 flex flex-wrap gap-2 text-xs">
                <span class="rounded-full bg-white px-3 py-1.5 text-stone-500">{{ template.step_count }} langkah</span>
                <span class="rounded-full bg-white px-3 py-1.5 text-stone-500">{{ template.condition_count }} kondisi</span>
                <span class="rounded-full bg-white px-3 py-1.5 text-stone-500">{{ template.retry_limit }} coba ulang</span>
              </div>

              <button
                type="button"
                @click="openFromTemplate(template)"
                class="mt-5 inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white transition-all hover:-translate-y-0.5 hover:bg-stone-800"
              >
                <Plus class="h-4 w-4" />
                <span>Pakai Templat</span>
              </button>
            </article>
          </div>
        </article>

        <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Log Eksekusi Terbaru</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Pantau apakah otomasi berhasil, gagal, atau perlu coba ulang.</h2>
            </div>
            <Clock3 class="h-5 w-5 text-stone-500" />
          </div>

          <div class="mt-5 space-y-3">
            <article
              v-for="log in recentLogs"
              :key="log.id"
              class="rounded-[1.4rem] border border-stone-200 bg-stone-50 p-4"
            >
              <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <div class="flex flex-wrap items-center gap-2">
                    <p class="text-sm font-semibold text-stone-950">{{ log.workflow_name || 'Alur kerja tidak dikenal' }}</p>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="logStatusClass(log.status)">
                      {{ log.status_label }}
                    </span>
                  </div>
                  <p class="mt-2 text-sm leading-6 text-stone-600">{{ log.message || 'Log eksekusi tanpa pesan.' }}</p>
                </div>
                <div class="text-right">
                  <p class="text-xs uppercase tracking-[0.18em] text-stone-400">{{ log.trigger_label }}</p>
                  <p class="mt-1 text-xs text-stone-500">{{ log.started_at_label }}</p>
                </div>
              </div>
            </article>

            <div v-if="recentLogs.length === 0" class="rounded-[1.4rem] border border-dashed border-stone-300 bg-stone-50 px-4 py-12 text-center text-sm text-stone-500">
              Belum ada log eksekusi terbaru untuk otomasi.
            </div>
          </div>
        </article>
      </section>
      </div>
    </AutomationLayout>

    <div v-if="showWorkflowModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
      <div class="modal-panel max-h-[92vh] w-full max-w-6xl overflow-y-auto rounded-[2rem] border border-stone-200 bg-white p-6 shadow-[0_28px_90px_rgba(28,25,23,0.18)]">
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">{{ isEditingWorkflow ? 'Ubah Alur Kerja' : 'Buat Alur Kerja' }}</p>
            <h2 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditingWorkflow ? 'Rapikan logika otomasi yang sudah ada.' : 'Susun alur kerja otomasi baru.' }}</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-stone-500">Builder ini fokus ke struktur yang mudah dibaca tim: pemicu, logika kondisi, rantai aksi, coba ulang, dan endpoint n8n.</p>
          </div>
          <button type="button" @click="closeWorkflowModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
            <X class="h-5 w-5" />
          </button>
        </div>

        <form class="mt-6 space-y-6" @submit.prevent="submitWorkflow">
          <section class="grid gap-4 xl:grid-cols-[1.1fr_0.9fr]">
            <div class="space-y-4">
              <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <p class="text-sm font-semibold text-stone-950">Dasar Alur Kerja</p>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                  <label class="space-y-2 text-sm md:col-span-2">
                    <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Nama Alur Kerja</span>
                    <input v-model="workflowForm.name" type="text" placeholder="Contoh: Notifikasi Invoice Baru" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400" />
                    <p v-if="workflowForm.errors.name" class="text-xs text-rose-600">{{ workflowForm.errors.name }}</p>
                  </label>

                  <label class="space-y-2 text-sm md:col-span-2">
                    <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Deskripsi</span>
                    <textarea v-model="workflowForm.description" rows="3" placeholder="Jelaskan tujuan alur kerja ini..." class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400"></textarea>
                    <p v-if="workflowForm.errors.description" class="text-xs text-rose-600">{{ workflowForm.errors.description }}</p>
                  </label>

                  <label class="space-y-2 text-sm">
                    <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Templat</span>
                    <select v-model="workflowForm.template_key" @change="applyTemplateSelection" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400">
                      <option value="">Alur Kerja Kustom</option>
                      <option v-for="template in templateItems" :key="template.key" :value="template.key">{{ template.name }}</option>
                    </select>
                  </label>

                  <label class="space-y-2 text-sm">
                    <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Status Alur Kerja</span>
                    <select v-model="workflowForm.is_active" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400">
                      <option :value="true">Aktif</option>
                      <option :value="false">Nonaktif</option>
                    </select>
                  </label>
                </div>
              </div>

              <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <p class="text-sm font-semibold text-stone-950">Pemicu & n8n</p>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                  <label class="space-y-2 text-sm">
                    <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Kejadian Pemicu (Event)</span>
                    <select v-model="workflowForm.trigger_event" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400">
                      <option v-for="event in automation.options.trigger_events" :key="event.value" :value="event.value">{{ event.label }}</option>
                    </select>
                    <p v-if="workflowForm.errors.trigger_event" class="text-xs text-rose-600">{{ workflowForm.errors.trigger_event }}</p>
                  </label>

                  <label class="space-y-2 text-sm">
                    <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Jenis Pemicu</span>
                    <select v-model="workflowForm.trigger_type" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400">
                      <option v-for="type in automation.options.trigger_types" :key="type.value" :value="type.value">{{ type.label }}</option>
                    </select>
                    <p v-if="workflowForm.errors.trigger_type" class="text-xs text-rose-600">{{ workflowForm.errors.trigger_type }}</p>
                  </label>

                  <label v-if="workflowForm.trigger_type === 'schedule'" class="space-y-2 text-sm md:col-span-2">
                    <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Ekspresi Jadwal</span>
                    <input v-model="workflowForm.schedule_expression" type="text" placeholder="* * * * *" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400" />
                    <p class="text-xs text-stone-500">Gunakan format cron standar untuk jadwal otomatis.</p>
                    <p v-if="workflowForm.errors.schedule_expression" class="text-xs text-rose-600">{{ workflowForm.errors.schedule_expression }}</p>
                  </label>

                  <label class="space-y-2 text-sm">
                    <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">n8n Workflow ID</span>
                    <input v-model="workflowForm.n8n_workflow_id" type="text" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400" />
                    <p class="text-xs text-stone-500">Samakan dengan ID alur kerja yang dipakai di panel n8n.</p>
                    <p v-if="workflowForm.errors.n8n_workflow_id" class="text-xs text-rose-600">{{ workflowForm.errors.n8n_workflow_id }}</p>
                  </label>

                  <label class="space-y-2 text-sm">
                    <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Tautan Webhook n8n</span>
                    <input v-model="workflowForm.n8n_webhook_url" type="url" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400" />
                    <p class="text-xs text-stone-500">Masukkan Production URL dari Node Webhook di n8n lu (Metode POST).</p>
                    <p v-if="workflowForm.errors.n8n_webhook_url" class="text-xs text-rose-600">{{ workflowForm.errors.n8n_webhook_url }}</p>
                  </label>
                </div>
              </div>
            </div>

            <div class="space-y-4">
              <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <p class="text-sm font-semibold text-stone-950">Coba Ulang & Cakupan</p>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                  <label class="space-y-2 text-sm">
                    <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Aktifkan Coba Ulang</span>
                    <select v-model="workflowForm.retry_enabled" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400">
                      <option :value="true">Aktif</option>
                      <option :value="false">Nonaktif</option>
                    </select>
                  </label>

                  <label class="space-y-2 text-sm">
                    <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Batas Coba Ulang</span>
                    <input v-model.number="workflowForm.retry_limit" type="number" min="0" max="10" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400" />
                    <p v-if="workflowForm.errors.retry_limit" class="text-xs text-rose-600">{{ workflowForm.errors.retry_limit }}</p>
                  </label>
                </div>

                <div class="mt-4 rounded-[1.2rem] border border-dashed border-stone-300 bg-white px-4 py-4 text-sm leading-6 text-stone-600">
                  Cakupan alur kerja otomatis berada di level workspace aktif. Jadi form ini fokus ke pemicu, logika, dan rantai aksi tanpa perlu mapping ulang tenant secara manual.
                </div>
              </div>
            </div>
          </section>

          <section class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="text-sm font-semibold text-stone-950">Logika Kondisi</p>
                <p class="mt-1 text-sm text-stone-500">Tambah aturan kalau alur kerja hanya boleh jalan pada kondisi tertentu.</p>
              </div>
              <button type="button" @click="addCondition" class="rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                Tambah Kondisi
              </button>
            </div>

            <div class="mt-5 space-y-4">
              <div class="rounded-[1.2rem] border border-dashed border-stone-300 bg-white px-4 py-4 text-sm leading-6 text-stone-600">
                Gunakan path data event yang konsisten, misalnya <span class="font-semibold text-stone-800">invoice.status</span> atau <span class="font-semibold text-stone-800">client.segment</span>, lalu isi nilai akhir yang ingin dibandingkan.
              </div>
              <div
                v-for="(condition, index) in workflowForm.conditions"
                :key="`condition-${index}`"
                class="rounded-[1.4rem] border border-stone-200 bg-white p-4"
              >
                <div class="grid gap-4 md:grid-cols-[1fr_0.8fr_1fr_auto]">
                  <label class="space-y-2 text-sm">
                    <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Kolom (Field)</span>
                    <input v-model="condition.field" type="text" placeholder="Contoh: status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white" />
                  </label>

                  <label class="space-y-2 text-sm">
                    <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Operator</span>
                    <select v-model="condition.operator" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white">
                      <option v-for="operator in automation.options.operators" :key="operator.value" :value="operator.value">{{ operator.label }}</option>
                    </select>
                  </label>

                  <label class="space-y-2 text-sm">
                    <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Nilai (Value)</span>
                    <input v-model="condition.value" type="text" placeholder="Contoh: paid" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white" />
                  </label>

                  <div class="flex items-end">
                    <button type="button" @click="removeCondition(index)" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">
                      Hapus
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <section class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="text-sm font-semibold text-stone-950">Langkah-langkah Aksi</p>
                <p class="mt-1 text-sm text-stone-500">Urutkan aksi yang harus dijalankan setelah pemicu terpenuhi.</p>
              </div>
              <button type="button" @click="addStep" class="rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                Tambah Langkah
              </button>
            </div>

            <div class="mt-5 space-y-4">
              <div
                v-for="(step, index) in workflowForm.steps"
                :key="`step-${index}`"
                class="rounded-[1.4rem] border border-stone-200 bg-white p-4"
              >
                <div class="flex items-center justify-between gap-3">
                  <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-stone-950 text-xs font-bold uppercase tracking-[0.18em] text-white">
                      {{ index + 1 }}
                    </div>
                    <p class="text-sm font-semibold text-stone-950">Langkah {{ index + 1 }}</p>
                  </div>
                  <button type="button" @click="removeStep(index)" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">
                    Hapus
                  </button>
                </div>

                <div class="mt-4 grid gap-4 md:grid-cols-2">
                  <label class="space-y-2 text-sm">
                    <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Jenis Aksi</span>
                    <select v-model="step.type" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white">
                      <option v-for="type in automation.options.step_types" :key="type.value" :value="type.value">{{ type.label }}</option>
                    </select>
                  </label>

                  <label class="space-y-2 text-sm">
                    <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Label Langkah</span>
                    <input v-model="step.label" type="text" placeholder="Contoh: Kirim WA ke Tim" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white" />
                    <p class="text-xs text-stone-500">Nama singkat aksi untuk tim internal.</p>
                  </label>

                  <label class="space-y-2 text-sm">
                    <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Target</span>
                    <input v-model="step.target" type="text" placeholder="Contoh: Finance Team" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white" />
                    <p class="text-xs text-stone-500">Isi target objek, tim, atau endpoint yang menerima aksi.</p>
                  </label>

                  <label class="space-y-2 text-sm">
                    <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Pesan / Payload</span>
                    <textarea v-model="step.message" rows="3" placeholder="Isi pesan atau data JSON..." class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition focus:border-stone-400 focus:bg-white"></textarea>
                  </label>
                </div>
              </div>
            </div>
          </section>

          <div class="flex flex-wrap justify-end gap-3">
            <button type="button" @click="closeWorkflowModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900">
              Batal
            </button>
            <button type="submit" :disabled="workflowForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition-all hover:-translate-y-0.5 hover:bg-stone-800 disabled:cursor-not-allowed disabled:opacity-60">
              {{ workflowForm.processing ? 'Menyimpan...' : isEditingWorkflow ? 'Perbarui Alur Kerja' : 'Buat Alur Kerja' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </WorkspaceLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import {
  Clock3,
  Filter,
  GitBranch,
  Layers3,
  Pencil,
  Play,
  Plus,
  RotateCcw,
  Sparkles,
  Trash2,
  Workflow,
  X,
} from 'lucide-vue-next'
import WorkspaceLayout from '../../Layouts/WorkspaceLayout.vue'
import AutomationLayout from '../../Layouts/AutomationLayout.vue'

const props = defineProps({
  workspace: { type: Object, required: true },
  automation: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const workspaceBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}`
const automationBaseUrl = `${workspaceBaseUrl}/automation`

const filterState = ref(buildFilterState(props.filters))
const showWorkflowModal = ref(false)
const editingWorkflowId = ref(null)
const selectedWorkflowId = ref(props.automation.selected_id || props.automation.items?.[0]?.id || null)

const workflowForm = useForm(buildWorkflowPayload())

const summary = computed(() => props.automation.summary || {})
const workflowItems = computed(() => props.automation.items || [])
const recentLogs = computed(() => props.automation.logs || [])
const templateItems = computed(() => props.automation.templates || [])

const selectedWorkflow = computed(() => workflowItems.value.find((workflow) => workflow.id === selectedWorkflowId.value) || workflowItems.value[0] || null)
const selectedWorkflowLogs = computed(() => recentLogs.value.filter((log) => log.workflow_id === selectedWorkflow.value?.id))
const isEditingWorkflow = computed(() => Boolean(editingWorkflowId.value))

const activeFilterChips = computed(() => {
  const chips = []

  if (filterState.value.search) {
    chips.push({ key: 'search', label: `Cari: ${filterState.value.search}` })
  }

  if (filterState.value.status) {
    const status = props.automation.options.statuses.find((item) => item.value === filterState.value.status)
    if (status) {
      chips.push({ key: 'status', label: `Status: ${status.label}` })
    }
  }

  if (filterState.value.trigger_type) {
    const type = props.automation.options.trigger_types.find((item) => item.value === filterState.value.trigger_type)
    if (type) {
      chips.push({ key: 'trigger_type', label: `Pemicu: ${type.label}` })
    }
  }

  if (filterState.value.template) {
    const template = templateItems.value.find((item) => item.key === filterState.value.template)
    if (template) {
      chips.push({ key: 'template', label: `Templat: ${template.name}` })
    }
  }

  return chips
})

watch(
  () => props.filters,
  (filters) => {
    filterState.value = buildFilterState(filters)
  },
  { deep: true },
)

watch(
  () => props.automation.items,
  (items) => {
    if (!Array.isArray(items) || items.length === 0) {
      selectedWorkflowId.value = null
      return
    }

    if (!items.some((item) => item.id === selectedWorkflowId.value)) {
      selectedWorkflowId.value = props.automation.selected_id || items[0].id
    }
  },
)

function buildFilterState(filters = {}) {
  return {
    search: filters.search ?? '',
    status: filters.status ?? '',
    trigger_type: filters.trigger_type ?? '',
    template: filters.template ?? '',
  }
}

function buildWorkflowPayload() {
  return {
    name: '',
    description: '',
    trigger_event: 'lead_created',
    trigger_type: 'event',
    schedule_expression: '',
    n8n_workflow_id: '',
    n8n_webhook_url: '',
    template_key: '',
    retry_enabled: true,
    retry_limit: 3,
    is_active: true,
    conditions: [blankCondition()],
    steps: [blankStep()],
  }
}

function blankCondition() {
  return {
    field: '',
    operator: 'equals',
    value: '',
  }
}

function blankStep() {
  return {
    type: 'send_whatsapp',
    label: '',
    target: '',
    message: '',
  }
}

function selectWorkflow(workflowId) {
  selectedWorkflowId.value = workflowId
}

function applyFilters() {
  router.get(automationBaseUrl, compactFilters(filterState.value), {
    preserveState: true,
    preserveScroll: true,
  })
}

function resetFilters() {
  filterState.value = buildFilterState()

  router.get(automationBaseUrl, {}, {
    preserveState: true,
    preserveScroll: true,
  })
}

function clearFilter(key) {
  if (!(key in filterState.value)) {
    return
  }

  filterState.value[key] = ''
  applyFilters()
}

function openWorkflowModal(workflow = null) {
  editingWorkflowId.value = workflow?.id || null
  workflowForm.clearErrors()

  if (!workflow) {
    hydrateWorkflowForm(buildWorkflowPayload())
    showWorkflowModal.value = true
    return
  }

  hydrateWorkflowForm({
    name: workflow.name || '',
    description: workflow.config?.description || '',
    trigger_event: workflow.trigger_event || 'lead_created',
    trigger_type: workflow.config?.trigger_type || 'event',
    schedule_expression: workflow.config?.schedule_expression || '',
    n8n_workflow_id: workflow.n8n_workflow_id || '',
    n8n_webhook_url: workflow.n8n_webhook_url || '',
    template_key: workflow.config?.template_key || '',
    retry_enabled: Boolean(workflow.config?.retry_enabled),
    retry_limit: workflow.config?.retry_limit ?? 0,
    is_active: Boolean(workflow.is_active),
    conditions: cloneEntries(workflow.config?.conditions, blankCondition),
    steps: cloneEntries(workflow.config?.steps, blankStep),
  })

  showWorkflowModal.value = true
}

function openFromTemplate(template) {
  editingWorkflowId.value = null
  workflowForm.clearErrors()
  hydrateWorkflowForm({
    ...buildWorkflowPayload(),
    name: template.name,
    description: template.description,
    trigger_event: template.trigger_event,
    trigger_type: template.trigger_type,
    template_key: template.key,
    retry_enabled: template.retry_enabled,
    retry_limit: template.retry_limit,
    conditions: cloneEntries(template.conditions, blankCondition),
    steps: cloneEntries(template.steps, blankStep),
  })
  showWorkflowModal.value = true
}

function applyTemplateSelection() {
  if (!workflowForm.template_key) {
    return
  }

  const template = templateItems.value.find((item) => item.key === workflowForm.template_key)

  if (!template) {
    return
  }

  workflowForm.name = workflowForm.name || template.name
  workflowForm.description = template.description
  workflowForm.trigger_event = template.trigger_event
  workflowForm.trigger_type = template.trigger_type
  workflowForm.retry_enabled = template.retry_enabled
  workflowForm.retry_limit = template.retry_limit
  workflowForm.conditions = cloneEntries(template.conditions, blankCondition)
  workflowForm.steps = cloneEntries(template.steps, blankStep)
}

function closeWorkflowModal() {
  showWorkflowModal.value = false
  editingWorkflowId.value = null
  workflowForm.clearErrors()
  hydrateWorkflowForm(buildWorkflowPayload())
}

function hydrateWorkflowForm(payload) {
  workflowForm.name = payload.name
  workflowForm.description = payload.description
  workflowForm.trigger_event = payload.trigger_event
  workflowForm.trigger_type = payload.trigger_type
  workflowForm.schedule_expression = payload.schedule_expression
  workflowForm.n8n_workflow_id = payload.n8n_workflow_id
  workflowForm.n8n_webhook_url = payload.n8n_webhook_url
  workflowForm.template_key = payload.template_key
  workflowForm.retry_enabled = payload.retry_enabled
  workflowForm.retry_limit = payload.retry_limit
  workflowForm.is_active = payload.is_active
  workflowForm.conditions = cloneEntries(payload.conditions, blankCondition)
  workflowForm.steps = cloneEntries(payload.steps, blankStep)
}

function addCondition() {
  workflowForm.conditions.push(blankCondition())
}

function removeCondition(index) {
  if (workflowForm.conditions.length === 1) {
    workflowForm.conditions = [blankCondition()]
    return
  }

  workflowForm.conditions.splice(index, 1)
}

function addStep() {
  workflowForm.steps.push(blankStep())
}

function removeStep(index) {
  if (workflowForm.steps.length === 1) {
    workflowForm.steps = [blankStep()]
    return
  }

  workflowForm.steps.splice(index, 1)
}

function submitWorkflow() {
  const options = {
    preserveScroll: true,
    onSuccess: () => closeWorkflowModal(),
  }

  if (editingWorkflowId.value) {
    workflowForm.patch(`${automationBaseUrl}/${encodeURIComponent(editingWorkflowId.value)}`, options)
    return
  }

  workflowForm.post(automationBaseUrl, options)
}

function toggleWorkflow(workflow) {
  router.patch(
    `${automationBaseUrl}/${encodeURIComponent(workflow.id)}/toggle`,
    { is_active: !workflow.is_active },
    {
      preserveScroll: true,
    },
  )
}

function runWorkflowTest(workflowId) {
  router.post(
    `${automationBaseUrl}/${encodeURIComponent(workflowId)}/run-test`,
    {},
    {
      preserveScroll: true,
    },
  )
}

function deleteWorkflow(workflowId) {
  if (!confirm('Hapus workflow otomasi ini?')) {
    return
  }

  router.delete(`${automationBaseUrl}/${encodeURIComponent(workflowId)}`, {
    preserveScroll: true,
  })
}

function compactFilters(filters) {
  return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== '' && value !== null && value !== undefined))
}

function cloneEntries(items, fallbackFactory) {
  if (!Array.isArray(items) || items.length === 0) {
    return [fallbackFactory()]
  }

  return items.map((item) => ({ ...item }))
}

function stepTypeLabel(value) {
  const match = props.automation.options.step_types.find((item) => item.value === value)
  return match?.label || value
}

function operatorLabel(value) {
  const match = props.automation.options.operators.find((item) => item.value === value)
  return match?.label || value
}

function logStatusClass(status) {
  const map = {
    success: 'bg-emerald-100 text-emerald-700',
    failed: 'bg-rose-100 text-rose-700',
    skipped: 'bg-amber-100 text-amber-700',
  }

  return map[status] || 'bg-stone-100 text-stone-600'
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
