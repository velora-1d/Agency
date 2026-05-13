<template>
  <Head title="Prospek / CRM" />

  <WorkspaceLayout title="Prospek / CRM" subtitle="Kanban dan tabel prospek per alur, lengkap dengan penilaian, sumber, penugasan, dan status otomasi.">
    <template #actions>
      <button
        type="button"
        @click="openCreateModal"
        class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
      >
        <UserPlus class="h-4 w-4" />
        <span>Buat Prospek Baru</span>
      </button>
      <button
        type="button"
        @click="openPipelineManager"
        class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-600 shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-50 hover:text-stone-950"
      >
        <Layers3 class="h-4 w-4" />
        <span>Kelola Alur</span>
      </button>
    </template>

    <div class="space-y-6">
      <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5 shadow-sm">
          <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Total Prospek</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ crm.summary.total_leads }}</p>
        </div>
        <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5 shadow-sm">
          <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Nilai Terbuka</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ crm.summary.open_value }}</p>
        </div>
        <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5 shadow-sm">
          <div class="flex items-center justify-between">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Otomasi n8n</p>
            <div class="flex h-2 w-2 rounded-full" :class="crm.summary.n8n_connected ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)]' : 'bg-rose-500'"></div>
          </div>
          <p class="mt-3 text-sm font-bold text-stone-950">{{ crm.summary.n8n_connected ? 'Tersambung' : 'Terputus' }}</p>
          <p class="mt-1 text-[11px] text-stone-500">
            Formulir {{ crm.summary.forms_auto_create_enabled }} aktif
          </p>
        </div>
        <div class="flex flex-col justify-center rounded-[1.6rem] border border-amber-100 bg-amber-50/50 p-5 shadow-sm">
          <div class="flex items-center gap-3 text-amber-800">
            <Zap class="h-4 w-4" />
            <p class="text-sm font-bold uppercase tracking-[0.14em]">Radar Otomasi</p>
          </div>
          <p class="mt-2 text-sm text-stone-500">Status WhatsApp otomatis n8n dan jumlah prospek dari formulir.</p>
        </div>
      </section>

      <section class="rounded-[2.2rem] border border-stone-200 bg-white p-3 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4 px-3 py-2">
          <div class="flex items-center gap-2">
            <button @click="viewMode = 'kanban'" class="rounded-xl p-2.5 transition" :class="viewMode === 'kanban' ? 'bg-stone-950 text-white shadow-md' : 'text-stone-400 hover:bg-stone-100 hover:text-stone-900'">
              <KanbanSquare class="h-4 w-4" />
            </button>
            <button @click="viewMode = 'table'" class="rounded-xl p-2.5 transition" :class="viewMode === 'table' ? 'bg-stone-950 text-white shadow-md' : 'text-stone-400 hover:bg-stone-100 hover:text-stone-900'">
              <Table2 class="h-4 w-4" />
            </button>
          </div>

          <div class="grid flex-1 items-center gap-3 sm:flex sm:justify-end">
            <div class="min-w-[140px]">
              <select v-model="filterState.pipeline" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="">Semua Alur</option>
                <option v-for="pipeline in filterOptions.pipelines" :key="pipeline.id" :value="pipeline.id">{{ pipeline.name }}</option>
              </select>
            </div>

            <div class="min-w-[140px]">
              <select v-model="filterState.stage" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="">Semua Tahap</option>
                <option v-for="stage in filteredStageOptions" :key="stage.id" :value="stage.id">{{ stage.name }}</option>
              </select>
            </div>

            <div class="min-w-[140px]">
              <select v-model="filterState.source" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="">Semua Sumber</option>
                <option v-for="source in filterOptions.sources" :key="source" :value="source">{{ source }}</option>
              </select>
            </div>

            <div class="hidden xl:block">
              <button @click="showAdvancedFilters = !showAdvancedFilters" class="rounded-2xl border border-stone-200 px-4 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-50">
                Filter Lanjutan
              </button>
            </div>

            <button @click="resetFilters" class="rounded-2xl p-3 text-stone-400 transition hover:bg-stone-50 hover:text-stone-900">
              <RotateCcw class="h-4 w-4" />
            </button>
          </div>
        </div>

        <div v-if="showAdvancedFilters" class="grid gap-4 border-t border-stone-100 p-4 sm:grid-cols-2 lg:grid-cols-4">
          <div class="space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Petugas</span>
            <select v-model="filterState.assignee" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Petugas</option>
              <option v-for="assignee in filterOptions.assignees" :key="assignee.id" :value="assignee.id">{{ assignee.name }}</option>
            </select>
          </div>
          <div class="space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Rentang Waktu</span>
            <select v-model="filterState.date_range" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Waktu</option>
              <option v-for="range in filterOptions.date_ranges" :key="range.value" :value="range.value">{{ range.label }}</option>
            </select>
          </div>
          <div class="space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Nilai Min</span>
            <input v-model="filterState.min_value" type="number" min="0" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
          </div>
          <div class="space-y-1">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Nilai Maks</span>
            <input v-model="filterState.max_value" type="number" min="0" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
          </div>
        </div>

        <!-- View: Kanban -->
        <div v-if="viewMode === 'kanban'" class="mt-4 overflow-x-auto pb-6 scrollbar-none">
          <div v-if="crm.pipelines.length" class="space-y-8 min-w-max">
            <div class="flex gap-2 px-3">
              <button
                v-for="pipeline in crm.pipelines"
                :key="pipeline.id"
                type="button"
                @click="activePipelineId = pipeline.id"
                class="rounded-full px-5 py-2 text-xs font-bold uppercase tracking-[0.14em] transition-all"
                :class="activePipelineId === pipeline.id ? 'bg-stone-950 text-white' : 'bg-stone-100 text-stone-500 hover:bg-stone-200 hover:text-stone-900'"
              >
                {{ pipeline.name }} / {{ pipeline.lead_count }}
              </button>
            </div>

            <div v-if="activePipeline" class="flex min-w-max gap-4">
              <div
                v-for="stage in activePipeline.stages"
                :key="stage.id"
                class="w-[300px] shrink-0 rounded-[2rem] border border-stone-100 bg-stone-50/50 p-2 transition-colors"
                :class="dropTargetStageId === stage.id ? 'border-stone-950 bg-stone-100/90' : ''"
                @dragover.prevent="handleStageDragOver(stage.id)"
                @dragleave="handleStageDragLeave(stage.id)"
                @drop.prevent="handleStageDrop(activePipeline.id, stage.id)"
              >
                <div class="flex items-center justify-between px-3 py-3">
                  <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full" :style="{ backgroundColor: stage.color || '#A8A29E' }"></span>
                    <h3 class="text-sm font-semibold text-stone-900">{{ stage.name }}</h3>
                  </div>
                  <p class="mt-1 text-xs text-stone-500">{{ stage.lead_count }} prospek / {{ stage.total_value }}</p>
                </div>
                <div v-if="stage.is_won || stage.is_lost" class="px-3 pb-2">
                  <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.2em]" :class="stage.is_won ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'">
                    {{ stage.is_won ? 'Berhasil' : 'Gagal' }}
                  </span>
                </div>

                <div class="mt-2 space-y-2">
                  <div
                    v-for="lead in stage.leads"
                    :key="lead.id"
                    draggable="true"
                    class="cursor-grab rounded-2xl border border-stone-200 bg-white p-4 shadow-sm transition-all hover:border-amber-200 hover:shadow-md active:cursor-grabbing"
                    @dragstart="handleLeadDragStart(lead, activePipeline.id)"
                  >
                    <div class="flex items-start justify-between gap-3">
                      <h4 class="font-bold leading-tight text-stone-950">{{ lead.company_name || lead.name }}</h4>
                      <div class="rounded-full bg-stone-100 p-1.5 text-stone-400">
                        <component :is="scoreIcon(lead.score)" class="h-3.5 w-3.5" />
                      </div>
                    </div>
                    <div class="mt-3 space-y-1.5 text-[11px] font-semibold uppercase tracking-[0.12em] text-stone-500">
                      <div class="flex items-center gap-2">
                        <Banknote class="h-3 w-3" />
                        <p>{{ lead.estimated_value_label }}</p>
                      </div>
                      <div class="flex items-center gap-2 text-stone-400">
                        <Globe class="h-3 w-3" />
                        <p>{{ lead.source || 'Sumber belum diisi' }}</p>
                      </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between gap-2 border-t border-stone-50 pt-3">
                      <div class="flex -space-x-2">
                        <div v-if="lead.assigned_user" class="h-6 w-6 rounded-full border-2 border-white bg-stone-200"></div>
                        <div v-else class="flex h-6 w-6 items-center justify-center rounded-full border-2 border-white bg-stone-100 text-[8px] font-bold text-stone-400">?</div>
                      </div>
                      <span class="text-[11px] text-stone-400">{{ lead.created_at_label || 'Baru dibuat' }}</span>
                    </div>

                    <div class="mt-3 flex gap-1 border-t border-stone-50 pt-2 opacity-0 transition-opacity hover:opacity-100 group-hover:opacity-100">
                      <button
                        v-if="wonStageForPipeline(activePipeline.id)"
                        class="flex-1 rounded-lg bg-emerald-50 px-2 py-1.5 text-[9px] font-bold uppercase tracking-[0.1em] text-emerald-700 transition hover:bg-emerald-100"
                        @click="moveLeadStage(lead.id, activePipeline.id, wonStageForPipeline(activePipeline.id)?.id)"
                      >
                        Berhasil
                      </button>
                      <button
                        v-if="lostStageForPipeline(activePipeline.id)"
                        class="flex-1 rounded-lg bg-rose-50 px-2 py-1.5 text-[9px] font-bold uppercase tracking-[0.1em] text-rose-700 transition hover:bg-rose-100"
                        @click="moveLeadStage(lead.id, activePipeline.id, lostStageForPipeline(activePipeline.id)?.id)"
                      >
                        Gagal
                      </button>
                    </div>

                    <div class="mt-2">
                      <select
                        :value="lead.stage?.id || ''"
                        class="w-full rounded-lg border-stone-100 bg-stone-50 px-2 py-1.5 text-[10px] text-stone-600 outline-none transition focus:border-stone-300"
                        @change="moveLeadStage(lead.id, activePipeline.id, $event.target.value)"
                      >
                        <option value="">Belum ditetapkan</option>
                        <option v-for="option in stageOptionsByPipeline(activePipeline.id)" :key="option.id" :value="option.id">{{ option.name }}</option>
                      </select>
                    </div>

                    <button
                      @click="openEditModal(lead)"
                      class="mt-2 w-full rounded-lg bg-stone-50 px-2 py-1.5 text-[9px] font-bold uppercase tracking-[0.1em] text-stone-500 transition hover:bg-stone-200 hover:text-stone-900"
                    >
                      Ubah Prospek
                    </button>
                  </div>
                </div>

                <div v-if="stage.leads.length === 0" class="rounded-[1.4rem] border border-dashed border-stone-200 bg-white/80 px-4 py-8 text-center text-sm text-stone-400">
                  Tidak ada prospek di tahap ini.
                </div>
              </div>
            </div>
          </div>
          <div v-else class="py-20 text-center text-stone-500">
            Belum ada alur atau prospek yang cocok dengan filter.
          </div>
        </div>

        <!-- View: Table -->
        <div v-else class="mt-4 overflow-hidden rounded-[1.8rem] border border-stone-100">
          <table class="w-full text-left text-sm">
            <thead class="bg-stone-50 text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">
              <tr>
                <th class="px-5 py-4">Klien / Nama</th>
                <th class="px-5 py-4">Alur</th>
                <th class="px-5 py-4">Sumber</th>
                <th class="px-5 py-4">Nilai</th>
                <th class="px-5 py-4">Petugas</th>
                <th class="px-5 py-4">Dibuat</th>
                <th class="px-5 py-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
              <tr v-for="lead in flatLeads" :key="lead.id" class="transition-colors hover:bg-stone-50/70">
                <td class="px-5 py-4 align-top">
                  <p class="font-bold text-stone-950">{{ lead.company_name || lead.name }}</p>
                  <p class="mt-1 text-xs text-stone-500">{{ lead.email || '-' }}</p>
                </td>
                <td class="px-5 py-4 align-top">
                  <div class="flex flex-col">
                    <span class="font-medium text-stone-800">{{ lead.pipeline?.name || 'Belum ada alur' }}</span>
                    <span class="text-xs text-stone-500">{{ lead.stage?.name || 'Belum ditetapkan' }}</span>
                  </div>
                </td>
                <td class="px-5 py-4 align-top text-stone-600">{{ lead.source || '-' }}</td>
                <td class="px-5 py-4 align-top font-medium text-stone-900">{{ lead.estimated_value_label }}</td>
                <td class="px-5 py-4 align-top">
                  <div v-if="lead.assigned_user" class="flex items-center gap-2">
                    <div class="h-6 w-6 rounded-full bg-stone-200"></div>
                    <span class="text-xs text-stone-600">{{ lead.assigned_user.name }}</span>
                  </div>
                  <span v-else class="text-xs text-stone-400 italic">Belum di-assign</span>
                </td>
                <td class="px-5 py-4 align-top text-stone-500">{{ lead.created_at_label || '-' }}</td>
                <td class="px-5 py-4 text-right align-top">
                  <div class="flex items-center justify-end gap-2">
                    <select
                      :value="lead.stage?.id || ''"
                      class="rounded-lg border-stone-100 bg-stone-100 px-2 py-1.5 text-[10px] font-bold text-stone-600 outline-none"
                      @change="moveLeadStage(lead.id, lead.pipeline?.id || '', $event.target.value)"
                    >
                      <option value="">Tahap</option>
                    <option v-for="option in stageOptionsByPipeline(lead.pipeline?.id)" :key="option.id" :value="option.id">{{ option.name }}</option>
                    </select>
                    <button
                      v-if="wonStageForPipeline(lead.pipeline?.id)"
                      @click="moveLeadStage(lead.id, lead.pipeline?.id || '', wonStageForPipeline(lead.pipeline?.id)?.id)"
                      class="rounded-lg bg-emerald-50 p-2 text-emerald-700 hover:bg-emerald-100"
                    >
                      <CheckCircle2 class="h-4 w-4" />
                    </button>
                    <button
                      v-if="lostStageForPipeline(lead.pipeline?.id)"
                      @click="moveLeadStage(lead.id, lead.pipeline?.id || '', lostStageForPipeline(lead.pipeline?.id)?.id)"
                      class="rounded-lg bg-rose-50 p-2 text-rose-700 hover:bg-rose-100"
                    >
                      <XCircle class="h-4 w-4" />
                    </button>
                    <button type="button" @click="openEditModal(lead)" class="inline-flex items-center gap-2 rounded-full border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-700 transition-all hover:border-stone-300 hover:text-stone-950">
                      <Pencil class="h-3.5 w-3.5" />
                      <span>Ubah</span>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>

    <!-- Modal: Lead Form -->
    <Transition name="modal">
      <div v-if="showLeadModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 p-4 backdrop-blur-sm">
        <div class="w-full max-w-2xl overflow-hidden rounded-[2.5rem] bg-white shadow-2xl">
          <div class="flex items-center justify-between border-b border-stone-100 bg-stone-50/50 px-8 py-6">
            <div>
              <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-stone-400">Prospek</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditing ? 'Ubah Prospek' : 'Buat Prospek Baru' }}</h3>
            </div>
            <button @click="closeModal" class="rounded-full p-2 text-stone-500 transition hover:bg-stone-200">
              <X class="h-5 w-5" />
            </button>
          </div>

          <form @submit.prevent="submit" class="grid gap-6 p-8 sm:grid-cols-2">
            <div class="col-span-full space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Nama Kontak / Perusahaan</span>
              <div class="relative">
                <input v-model="leadForm.name" type="text" placeholder="Contoh: PT. Maju Jaya atau Budi Sudarsono" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                <div class="absolute inset-y-0 right-4 flex items-center gap-2">
                  <button type="button" @click="suggestName" class="text-amber-600 hover:text-amber-700">
                    <Sparkles class="h-4 w-4" />
                  </button>
                </div>
              </div>
              <p v-if="leadForm.errors.name" class="text-[10px] text-rose-500">{{ leadForm.errors.name }}</p>
            </div>

            <div class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Email</span>
              <input v-model="leadForm.email" type="email" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
            </div>

            <div class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Telepon / WA</span>
              <input v-model="leadForm.phone" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
            </div>

            <div class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Alur</span>
              <select v-model="leadForm.pipeline_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="">Tanpa Alur</option>
                <option v-for="pipeline in filterOptions.pipelines" :key="pipeline.id" :value="pipeline.id">{{ pipeline.name }}</option>
              </select>
            </div>

            <div class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tahap</span>
              <select v-model="leadForm.stage_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="">Belum ditetapkan</option>
                <option v-for="stage in modalStageOptions" :key="stage.id" :value="stage.id">{{ stage.name }}</option>
              </select>
            </div>

            <div class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Sumber</span>
              <input v-model="leadForm.source" type="text" list="lead-sources" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              <datalist id="lead-sources">
                <option v-for="source in filterOptions.sources" :key="source" :value="source" />
              </datalist>
            </div>

            <div class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Petugas</span>
              <select v-model="leadForm.assigned_to" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="">Tanpa Petugas</option>
                <option v-for="assignee in filterOptions.assignees" :key="assignee.id" :value="assignee.id">{{ assignee.name }}</option>
              </select>
            </div>

            <div class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Suhu / Skor</span>
              <select v-model="leadForm.score" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="low">Rendah</option>
                <option value="medium">Sedang</option>
                <option value="high">Tinggi</option>
              </select>
            </div>

            <div class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Estimasi Nilai</span>
              <input v-model="leadForm.estimated_value" type="number" min="0" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
            </div>

            <div class="col-span-full flex justify-end gap-3 pt-4 border-t border-stone-100">
              <button type="button" @click="closeModal" class="rounded-2xl border border-stone-200 bg-white px-6 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-50 hover:text-stone-900">
                Batal
              </button>
              <button
                type="submit"
                :disabled="leadForm.processing"
                class="rounded-2xl bg-stone-950 px-8 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-50"
              >
                {{ isEditing ? (leadForm.processing ? 'Menyimpan...' : 'Simpan Perubahan') : (leadForm.processing ? 'Memproses...' : 'Buat Prospek') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- Modal: Pipeline Manager -->
    <Transition name="modal">
      <div v-if="showPipelineModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="w-full max-w-4xl max-h-full overflow-hidden flex flex-col rounded-[2.5rem] bg-white shadow-2xl">
          <div class="flex items-center justify-between border-b border-stone-100 bg-stone-50/50 px-8 py-6">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Pengaturan Alur</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">Kelola Alur & Tahap</h3>
            </div>
            <button @click="closePipelineModal" class="rounded-full p-2 text-stone-500 transition hover:bg-stone-200">
              <X class="h-5 w-5" />
            </button>
          </div>
          <div class="flex-1 overflow-y-auto p-8">
            <p class="text-stone-500 italic">Antarmuka manajemen alur sedang dalam pengembangan.</p>
          </div>
        </div>
      </div>
    </Transition>
</WorkspaceLayout>
</template>

<script setup>
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import debounce from 'lodash/debounce'
import {
  Banknote,
  CheckCircle2,
  Flame,
  Globe,
  Layers3,
  KanbanSquare,
  Pencil,
  RotateCcw,
  Sparkles,
  Table2,
  Trash2,
  UserPlus,
  X,
  XCircle,
  Zap,
} from 'lucide-vue-next'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'

const props = defineProps({
  workspace: Object,
  crm: Object,
  filterOptions: Object,
  filters: Object,
})

const workspaceBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}`
const crmBaseUrl = `${workspaceBaseUrl}/crm/leads`

const viewMode = ref('kanban')
const showAdvancedFilters = ref(false)
const showLeadModal = ref(false)
const showPipelineModal = ref(false)
const editingLeadId = ref(null)
const activePipelineId = ref(props.crm.pipelines?.[0]?.id || null)

const filterState = ref({
  pipeline: props.filters.pipeline ?? '',
  stage: props.filters.stage ?? '',
  source: props.filters.source ?? '',
  assignee: props.filters.assignee ?? '',
  date_range: props.filters.date_range ?? '',
  min_value: props.filters.min_value ?? '',
  max_value: props.filters.max_value ?? '',
})

const leadForm = useForm({
  name: '',
  email: '',
  phone: '',
  pipeline_id: '',
  stage_id: '',
  source: '',
  assigned_to: '',
  score: 'medium',
  estimated_value: 0,
})

const isEditing = computed(() => editingLeadId.value !== null)

const activePipeline = computed(() => props.crm.pipelines.find((p) => p.id === activePipelineId.value))

const flatLeads = computed(() => {
  const leads = []
  props.crm.pipelines.forEach((p) => {
    p.stages.forEach((s) => {
      s.leads.forEach((l) => {
        leads.push({
          ...l,
          pipeline: { id: p.id, name: p.name },
          stage: { id: s.id, name: s.name },
        })
      })
    })
  })
  return leads
})

const filteredStageOptions = computed(() => {
  if (!filterState.value.pipeline) return props.filterOptions.stages
  const pipeline = props.crm.pipelines.find((p) => p.id === filterState.value.pipeline)
  return pipeline ? pipeline.stages : []
})

const modalStageOptions = computed(() => {
  if (!leadForm.pipeline_id) return []
  const pipeline = props.crm.pipelines.find((p) => p.id === leadForm.pipeline_id)
  return pipeline ? pipeline.stages : []
})

watch(filterState, debounce(() => {
  router.get(crmBaseUrl, compactFilters(filterState.value), {
    preserveState: true,
    preserveScroll: true,
    only: ['crm', 'filters'],
  })
}, 300), { deep: true })

function resetFilters() {
  filterState.value = {
    pipeline: '',
    stage: '',
    source: '',
    assignee: '',
    date_range: '',
    min_value: '',
    max_value: '',
  }
}

function compactFilters(filters) {
  return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== '' && value !== null && value !== undefined))
}

function openCreateModal() {
  editingLeadId.value = null
  leadForm.reset()
  leadForm.pipeline_id = activePipelineId.value || ''
  showLeadModal.value = true
}

function openEditModal(lead) {
  editingLeadId.value = lead.id
  leadForm.name = lead.name || ''
  leadForm.email = lead.email || ''
  leadForm.phone = lead.phone || ''
  leadForm.pipeline_id = lead.pipeline_id || ''
  leadForm.stage_id = lead.stage_id || ''
  leadForm.source = lead.source || ''
  leadForm.assigned_to = lead.assigned_to || ''
  leadForm.score = lead.score || 'medium'
  leadForm.estimated_value = lead.estimated_value || 0
  showLeadModal.value = true
}

function closeModal() {
  showLeadModal.value = false
  editingLeadId.value = null
  leadForm.clearErrors()
}

function openPipelineManager() {
  showPipelineModal.value = true
}

function closePipelineModal() {
  showPipelineModal.value = false
}

function submit() {
  const options = {
    onSuccess: () => closeModal(),
  }
  if (isEditing.value) {
    leadForm.patch(`${crmBaseUrl}/${encodeURIComponent(editingLeadId.value)}`, options)
  } else {
    leadForm.post(crmBaseUrl, options)
  }
}

function moveLeadStage(leadId, pipelineId, stageId) {
  router.patch(`${crmBaseUrl}/${encodeURIComponent(leadId)}/stage`, {
    pipeline_id: pipelineId,
    stage_id: stageId,
  }, {
    preserveScroll: true,
  })
}

function stageOptionsByPipeline(pipelineId) {
  const pipeline = props.crm.pipelines.find((p) => p.id === pipelineId)
  return pipeline ? pipeline.stages : []
}

function wonStageForPipeline(pipelineId) {
  const pipeline = props.crm.pipelines.find((p) => p.id === pipelineId)
  return pipeline ? pipeline.stages.find((s) => s.is_won) : null
}

function lostStageForPipeline(pipelineId) {
  const pipeline = props.crm.pipelines.find((p) => p.id === pipelineId)
  return pipeline ? pipeline.stages.find((s) => s.is_lost) : null
}

function scoreIcon(score) {
  return {
    high: Flame,
    medium: Zap,
    low: Flame,
  }[score] || Zap
}

// Drag & Drop
const draggedLeadId = ref(null)
const draggedFromPipelineId = ref(null)
const dropTargetStageId = ref(null)

function handleLeadDragStart(lead, pipelineId) {
  draggedLeadId.value = lead.id
  draggedFromPipelineId.value = pipelineId
}

function handleStageDragOver(stageId) {
  dropTargetStageId.value = stageId
}

function handleStageDragLeave() {
  dropTargetStageId.value = null
}

function handleStageDrop(pipelineId, stageId) {
  if (draggedLeadId.value && stageId) {
    moveLeadStage(draggedLeadId.value, pipelineId, stageId)
  }
  draggedLeadId.value = null
  draggedFromPipelineId.value = null
  dropTargetStageId.value = null
}

function suggestName() {
  // AI suggestion placeholder
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: all 0.28s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.98);
}
</style>
