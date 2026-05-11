<template>
  <WorkspaceLayout title="Leads / CRM" subtitle="Kanban dan tabel lead per pipeline, lengkap dengan scoring, source, assignment, dan status automation.">
    <template #actions>
      <div class="flex flex-wrap items-center justify-end gap-3">
        <button
          type="button"
          @click="openCreateModal"
          class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
        >
          <Plus class="h-4 w-4" />
          <span>New Lead</span>
        </button>

        <button
          type="button"
          @click="openPipelineManager"
          class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-stone-300 hover:text-stone-950"
        >
          <Settings2 class="h-4 w-4" />
          <span>Pipelines</span>
        </button>

        <div class="inline-flex rounded-2xl border border-stone-200 bg-white p-1 shadow-sm">
          <button
            v-for="view in views"
            :key="view.id"
            type="button"
            @click="viewMode = view.id"
            :class="[
              'rounded-xl px-4 py-2 text-sm font-semibold transition-all',
              viewMode === view.id ? 'bg-stone-950 text-white shadow-sm' : 'text-stone-500 hover:bg-stone-100 hover:text-stone-900',
            ]"
          >
            {{ view.label }}
          </button>
        </div>

        <button
          type="button"
          @click="exportCsv"
          class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-stone-300 hover:text-stone-950"
        >
          <Download class="h-4 w-4" />
          <span>Export CSV</span>
        </button>
      </div>
    </template>

    <div class="space-y-6">
      <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Visible Leads</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ crm.summary.total_leads }}</p>
          <p class="mt-2 text-sm text-stone-500">Lead setelah filter aktif diterapkan.</p>
        </article>
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Open Deal Value</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ crm.summary.open_value }}</p>
          <p class="mt-2 text-sm text-stone-500">Akumulasi estimasi nilai deal yang sedang dipantau.</p>
        </article>
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Hot Leads</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ crm.summary.high_priority }}</p>
          <p class="mt-2 text-sm text-stone-500">Lead prioritas tinggi yang butuh perhatian cepat.</p>
        </article>
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">AI Score</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ crm.summary.avg_ai_score ?? 'Belum dinilai' }}</p>
          <p class="mt-2 text-sm text-stone-500">Rata-rata skor lead scoring untuk hasil filter saat ini.</p>
        </article>
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Automation</p>
          <div class="mt-3 flex items-center gap-2">
            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.18em]" :class="crm.summary.auto_whatsapp_enabled ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-100 text-stone-500'">
              {{ crm.summary.auto_whatsapp_enabled ? 'WA ON' : 'WA OFF' }}
            </span>
            <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-amber-700">
              Forms {{ crm.summary.forms_auto_create_enabled }}
            </span>
          </div>
          <p class="mt-3 text-xs text-stone-500">{{ crm.automation.workflow_name }}</p>
          <button
            type="button"
            @click="openAutomationModal"
            class="mt-4 inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-2 text-sm font-semibold text-stone-700 transition-all hover:border-stone-300 hover:bg-white hover:text-stone-950"
          >
            <MessageSquareShare class="h-4 w-4" />
            <span>Configure WA</span>
          </button>
          <p class="mt-2 text-sm text-stone-500">Status auto-WhatsApp n8n dan jumlah form auto-create lead.</p>
        </article>
      </section>

      <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Pipeline</span>
            <select v-model="filterState.pipeline" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">All Pipelines</option>
              <option v-for="pipeline in filterOptions.pipelines" :key="pipeline.id" :value="pipeline.id">{{ pipeline.name }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Stage</span>
            <select v-model="filterState.stage" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">All Stages</option>
              <option v-for="stage in filteredStageOptions" :key="stage.id" :value="stage.id">{{ stage.name }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Source</span>
            <select v-model="filterState.source" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">All Sources</option>
              <option v-for="source in filterOptions.sources" :key="source" :value="source">{{ source }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Assignee</span>
            <select v-model="filterState.assignee" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">All Assignees</option>
              <option v-for="assignee in filterOptions.assignees" :key="assignee.id" :value="assignee.id">{{ assignee.name }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Date</span>
            <select v-model="filterState.date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">All Time</option>
              <option v-for="range in filterOptions.date_ranges" :key="range.value" :value="range.value">{{ range.label }}</option>
            </select>
          </label>

          <div class="grid grid-cols-2 gap-3">
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Min Value</span>
              <input v-model="filterState.min_value" type="number" min="0" placeholder="0" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Max Value</span>
              <input v-model="filterState.max_value" type="number" min="0" placeholder="0" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
            </label>
          </div>
        </div>

        <div class="mt-5 flex flex-wrap items-center gap-3">
          <button
            type="button"
            @click="applyFilters"
            class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition-all hover:-translate-y-0.5 hover:bg-stone-800"
          >
            <Filter class="h-4 w-4" />
            <span>Apply Filters</span>
          </button>
          <button
            type="button"
            @click="resetFilters"
            class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900"
          >
            <RotateCcw class="h-4 w-4" />
            <span>Reset</span>
          </button>
        </div>
      </section>

      <section class="rounded-[2rem] border border-stone-200 bg-white shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="border-b border-stone-200 px-5 py-4">
          <div class="flex flex-wrap items-center gap-3">
            <button
              v-for="pipeline in crm.pipelines"
              :key="pipeline.id"
              type="button"
              @click="activePipelineId = pipeline.id"
              :class="[
                'rounded-full px-4 py-2 text-sm font-semibold transition-all',
                activePipelineId === pipeline.id ? 'bg-stone-950 text-white' : 'bg-stone-100 text-stone-500 hover:bg-stone-200 hover:text-stone-900',
              ]"
            >
              {{ pipeline.name }} - {{ pipeline.lead_count }}
            </button>
          </div>
        </div>

        <div v-if="viewMode === 'kanban'" class="overflow-x-auto px-5 py-5">
          <div v-if="activePipeline" class="flex min-w-max gap-4">
            <article
              v-for="stage in activePipeline.stages"
              :key="stage.id"
              class="w-[320px] flex-none rounded-[1.6rem] border border-stone-200 bg-stone-50/70 p-4 transition-all"
              :class="dropTargetStageId === stage.id ? 'border-stone-950 bg-stone-100/90' : ''"
              @dragover.prevent="handleStageDragOver(stage.id)"
              @dragleave="handleStageDragLeave(stage.id)"
              @drop.prevent="handleStageDrop(activePipeline.id, stage.id)"
            >
              <div class="flex items-start justify-between gap-3">
                <div>
                  <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full" :style="{ backgroundColor: stage.color || '#A8A29E' }"></span>
                    <h3 class="text-sm font-semibold text-stone-900">{{ stage.name }}</h3>
                  </div>
                  <p class="mt-1 text-xs text-stone-500">{{ stage.lead_count }} leads - {{ stage.total_value }}</p>
                </div>
                <span v-if="stage.is_won || stage.is_lost" class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.2em]" :class="stage.is_won ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'">
                  {{ stage.is_won ? 'Won' : 'Lost' }}
                </span>
              </div>

              <div class="mt-4 space-y-3">
                <div
                  v-for="lead in stage.leads"
                  :key="lead.id"
                  draggable="true"
                  @dragstart="handleLeadDragStart(lead, activePipeline.id)"
                  @dragend="handleLeadDragEnd"
                  class="rounded-[1.4rem] border border-white bg-white p-4 shadow-[0_12px_30px_rgba(28,25,23,0.05)] transition-all hover:-translate-y-1 hover:border-stone-300"
                >
                  <button type="button" @click="openLead(lead.id)" class="w-full text-left">
                    <div class="flex items-start justify-between gap-3">
                      <div>
                        <h4 class="text-sm font-semibold text-stone-950">{{ lead.name }}</h4>
                        <p class="mt-1 text-xs text-stone-500">{{ lead.company || 'Individual lead' }}</p>
                      </div>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.2em]" :class="priorityClass(lead.priority)">
                        {{ lead.priority }}
                      </span>
                    </div>

                    <div class="mt-4 space-y-2 text-xs text-stone-500">
                      <p>{{ lead.estimated_value_label }}</p>
                      <p>{{ lead.source || 'Source belum diisi' }}</p>
                      <p>{{ lead.assignee?.name || 'Belum ada assignee' }}</p>
                      <p>{{ lead.email || lead.phone || 'Kontak belum lengkap' }}</p>
                    </div>
                  </button>

                  <div class="mt-4 flex items-center justify-between gap-2">
                    <span class="text-[11px] font-semibold text-stone-700">AI {{ lead.ai_score ?? 'Belum dinilai' }}</span>
                    <span class="text-[11px] text-stone-400">{{ lead.created_at_label || 'Baru dibuat' }}</span>
                  </div>

                  <div class="mt-4 grid grid-cols-2 gap-2">
                    <button
                      v-if="wonStageForPipeline(activePipeline.id)"
                      type="button"
                      @click="moveLeadStage(lead.id, activePipeline.id, wonStageForPipeline(activePipeline.id)?.id)"
                      class="rounded-2xl bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 transition-all hover:bg-emerald-100"
                    >
                      Mark Won
                    </button>
                    <button
                      v-if="lostStageForPipeline(activePipeline.id)"
                      type="button"
                      @click="moveLeadStage(lead.id, activePipeline.id, lostStageForPipeline(activePipeline.id)?.id)"
                      class="rounded-2xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition-all hover:bg-rose-100"
                    >
                      Mark Lost
                    </button>
                  </div>

                  <div class="mt-4 grid grid-cols-[minmax(0,1fr)_auto] gap-2">
                    <select
                      :value="lead.stage?.id || ''"
                      class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-3 py-2 text-xs text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
                      @change="moveLeadStage(lead.id, activePipeline.id, $event.target.value)"
                    >
                      <option value="">Unassigned</option>
                      <option v-for="option in stageOptionsByPipeline(activePipeline.id)" :key="option.id" :value="option.id">{{ option.name }}</option>
                    </select>

                    <button
                      type="button"
                      @click="openEditModal(lead)"
                      class="inline-flex items-center justify-center rounded-2xl border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-700 transition-all hover:border-stone-300 hover:text-stone-950"
                    >
                      <Pencil class="h-3.5 w-3.5" />
                    </button>
                  </div>
                </div>

                <div v-if="stage.leads.length === 0" class="rounded-[1.4rem] border border-dashed border-stone-200 bg-white/80 px-4 py-8 text-center text-sm text-stone-400">
                  Tidak ada lead di stage ini.
                </div>
              </div>
            </article>
          </div>

          <div v-else class="px-2 py-12 text-center text-sm text-stone-500">
            Belum ada pipeline atau lead yang cocok dengan filter.
          </div>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-stone-200 text-sm">
            <thead class="bg-stone-50 text-left text-[11px] font-bold uppercase tracking-[0.22em] text-stone-400">
              <tr>
                <th class="px-5 py-4">Lead</th>
                <th class="px-5 py-4">Pipeline</th>
                <th class="px-5 py-4">Source</th>
                <th class="px-5 py-4">Value</th>
                <th class="px-5 py-4">Assignee</th>
                <th class="px-5 py-4">AI Score</th>
                <th class="px-5 py-4">Created</th>
                <th class="px-5 py-4">Move</th>
                <th class="px-5 py-4"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 bg-white">
              <tr v-for="lead in crm.table" :key="lead.id" class="transition-colors hover:bg-stone-50/70">
                <td class="px-5 py-4 align-top">
                  <div class="flex flex-col gap-1">
                    <span class="font-semibold text-stone-950">{{ lead.name }}</span>
                    <span class="text-xs text-stone-500">{{ lead.company || 'Individual lead' }}</span>
                    <span class="text-xs text-stone-500">{{ lead.email || lead.phone || 'Kontak belum diisi' }}</span>
                  </div>
                </td>
                <td class="px-5 py-4 align-top">
                  <div class="flex flex-col gap-1">
                    <span class="font-medium text-stone-800">{{ lead.pipeline?.name || 'No pipeline' }}</span>
                    <span class="text-xs text-stone-500">{{ lead.stage?.name || 'Unassigned' }}</span>
                  </div>
                </td>
                <td class="px-5 py-4 align-top text-stone-600">{{ lead.source || '-' }}</td>
                <td class="px-5 py-4 align-top font-medium text-stone-900">{{ lead.estimated_value_label }}</td>
                <td class="px-5 py-4 align-top text-stone-600">{{ lead.assignee?.name || '-' }}</td>
                <td class="px-5 py-4 align-top">
                  <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold" :class="scoreClass(lead.ai_score)">
                    {{ lead.ai_score ?? 'Belum dinilai' }}
                  </span>
                </td>
                <td class="px-5 py-4 align-top text-stone-500">{{ lead.created_at_label || '-' }}</td>
                <td class="px-5 py-4 align-top">
                  <select
                    :value="lead.stage?.id || ''"
                    class="w-full min-w-[160px] rounded-2xl border border-stone-200 bg-stone-50 px-3 py-2 text-xs text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
                    @change="moveLeadStage(lead.id, lead.pipeline?.id || '', $event.target.value)"
                  >
                    <option value="">Unassigned</option>
                    <option v-for="option in stageOptionsByPipeline(lead.pipeline?.id)" :key="option.id" :value="option.id">{{ option.name }}</option>
                  </select>
                </td>
                <td class="px-5 py-4 align-top text-right">
                  <div class="flex justify-end gap-2">
                    <button
                      v-if="wonStageForPipeline(lead.pipeline?.id)"
                      type="button"
                      @click="moveLeadStage(lead.id, lead.pipeline?.id || '', wonStageForPipeline(lead.pipeline?.id)?.id)"
                      class="inline-flex items-center gap-2 rounded-full border border-emerald-200 px-3 py-2 text-xs font-semibold text-emerald-700 transition-all hover:bg-emerald-50"
                    >
                      Won
                    </button>
                    <button
                      v-if="lostStageForPipeline(lead.pipeline?.id)"
                      type="button"
                      @click="moveLeadStage(lead.id, lead.pipeline?.id || '', lostStageForPipeline(lead.pipeline?.id)?.id)"
                      class="inline-flex items-center gap-2 rounded-full border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-700 transition-all hover:bg-rose-50"
                    >
                      Lost
                    </button>
                    <button type="button" @click="openEditModal(lead)" class="inline-flex items-center gap-2 rounded-full border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-700 transition-all hover:border-stone-300 hover:text-stone-950">
                      <Pencil class="h-3.5 w-3.5" />
                    </button>
                    <button type="button" @click="openLead(lead.id)" class="inline-flex items-center gap-2 rounded-full border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-700 transition-all hover:border-stone-300 hover:text-stone-950">
                      <span>Open</span>
                      <ArrowUpRight class="h-3.5 w-3.5" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="crm.table.length === 0">
                <td colspan="9" class="px-5 py-16 text-center text-sm text-stone-500">Belum ada lead yang cocok dengan filter saat ini.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>

    <Transition name="modal">
      <div v-if="showLeadModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Lead Form</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditing ? 'Edit Lead' : 'Create New Lead' }}</h3>
            </div>
            <button type="button" @click="closeLeadModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
              <X class="h-5 w-5" />
            </button>
          </div>

          <form class="mt-6 space-y-5" @submit.prevent="submitLead">
            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Name</span>
                <input v-model="leadForm.name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                <p v-if="leadForm.errors.name" class="text-xs text-rose-500">{{ leadForm.errors.name }}</p>
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Company</span>
                <input v-model="leadForm.company" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Email</span>
                <input v-model="leadForm.email" type="email" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                <p v-if="leadForm.errors.email" class="text-xs text-rose-500">{{ leadForm.errors.email }}</p>
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">WhatsApp</span>
                <input v-model="leadForm.phone" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">City</span>
                <input v-model="leadForm.city" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Province</span>
                <input v-model="leadForm.province" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>
              <label class="space-y-2 text-sm md:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Address</span>
                <textarea v-model="leadForm.address" rows="2" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Pipeline</span>
                <select v-model="leadForm.pipeline_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">No Pipeline</option>
                  <option v-for="pipeline in filterOptions.pipelines" :key="pipeline.id" :value="pipeline.id">{{ pipeline.name }}</option>
                </select>
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Stage</span>
                <select v-model="leadForm.stage_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Unassigned</option>
                  <option v-for="stage in modalStageOptions" :key="stage.id" :value="stage.id">{{ stage.name }}</option>
                </select>
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Source</span>
                <input v-model="leadForm.source" type="text" list="lead-sources" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                <datalist id="lead-sources">
                  <option v-for="source in filterOptions.sources" :key="source" :value="source" />
                </datalist>
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Assignee</span>
                <select v-model="leadForm.assigned_to" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">No Assignee</option>
                  <option v-for="assignee in filterOptions.assignees" :key="assignee.id" :value="assignee.id">{{ assignee.name }}</option>
                </select>
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Priority</span>
                <select v-model="leadForm.priority" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                </select>
              </label>
              <label class="space-y-2 text-sm md:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Estimated Value</span>
                <input v-model="leadForm.estimated_value" type="number" min="0" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>
              <label class="space-y-2 text-sm md:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Notes</span>
                <textarea v-model="leadForm.notes" rows="4" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
              </label>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3">
              <button type="button" @click="closeLeadModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900">
                Cancel
              </button>
              <button type="submit" :disabled="leadForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition-all hover:bg-stone-800 disabled:cursor-not-allowed disabled:opacity-60">
                {{ isEditing ? (leadForm.processing ? 'Saving...' : 'Save Changes') : (leadForm.processing ? 'Creating...' : 'Create Lead') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showPipelineModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Pipeline Builder</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ editingPipelineId ? 'Edit Pipeline' : 'Create Pipeline' }}</h3>
            </div>
            <div class="flex items-center gap-2">
              <button type="button" @click="startNewPipeline" class="rounded-2xl border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-700 transition-all hover:bg-stone-100">
                New Pipeline
              </button>
              <button type="button" @click="closePipelineModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
                <X class="h-5 w-5" />
              </button>
            </div>
            </div>

          <div v-if="crm.pipelines.length" class="mt-6 flex flex-wrap gap-2">
            <button
              v-for="pipeline in crm.pipelines"
              :key="pipeline.id"
              type="button"
              @click="openPipelineModal(pipeline)"
              :class="[
                'rounded-full px-4 py-2 text-sm font-semibold transition-all',
                editingPipelineId === pipeline.id ? 'bg-stone-950 text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 hover:text-stone-950',
              ]"
            >
              {{ pipeline.name }}
            </button>
          </div>

          <form class="mt-6 space-y-6" @submit.prevent="submitPipeline">
            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Pipeline Name</span>
                <input v-model="pipelineForm.name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                <p v-if="pipelineForm.errors.name" class="text-xs text-rose-500">{{ pipelineForm.errors.name }}</p>
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Default Pipeline</span>
                <label class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700">
                  <input v-model="pipelineForm.is_default" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-400" />
                  <span>Use this as default service pipeline</span>
                </label>
              </label>
              <label class="space-y-2 text-sm md:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Description</span>
                <textarea v-model="pipelineForm.description" rows="3" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
              </label>
            </div>

            <div class="space-y-4">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Custom Stages</p>
                  <p class="mt-1 text-sm text-stone-500">Atur urutan stage khusus untuk service atau pipeline ini.</p>
                </div>
                <button type="button" @click="addStageRow" class="rounded-2xl border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-700 transition-all hover:bg-stone-100">
                  Add Stage
                </button>
              </div>

              <div class="space-y-3">
                <div v-for="(stage, index) in pipelineForm.stages" :key="stage.local_key" class="grid gap-3 rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4 md:grid-cols-[40px_minmax(0,1.4fr)_120px_90px_90px_auto] md:items-center">
                  <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-sm font-semibold text-stone-500 shadow-sm">
                    {{ index + 1 }}
                  </div>
                  <label class="space-y-2 text-sm">
                    <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">Stage Name</span>
                    <input v-model="stage.name" type="text" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400" />
                  </label>
                  <label class="space-y-2 text-sm">
                    <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">Color</span>
                    <input v-model="stage.color" type="color" class="h-[50px] w-full rounded-2xl border border-stone-200 bg-white p-2" />
                  </label>
                  <label class="space-y-2 text-sm">
                    <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">Won</span>
                    <label class="flex h-[50px] items-center justify-center rounded-2xl border border-stone-200 bg-white">
                      <input v-model="stage.is_won" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-emerald-600 focus:ring-emerald-400" />
                    </label>
                  </label>
                  <label class="space-y-2 text-sm">
                    <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">Lost</span>
                    <label class="flex h-[50px] items-center justify-center rounded-2xl border border-stone-200 bg-white">
                      <input v-model="stage.is_lost" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-rose-600 focus:ring-rose-400" />
                    </label>
                  </label>
                  <button type="button" @click="removeStageRow(index)" class="rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm font-semibold text-stone-500 transition-all hover:border-rose-200 hover:text-rose-600">
                    Remove
                  </button>
                </div>
              </div>
              <p v-if="pipelineForm.errors.stages" class="text-xs text-rose-500">{{ pipelineForm.errors.stages }}</p>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3">
              <button
                v-if="editingPipelineId"
                type="button"
                @click="deletePipeline"
                class="mr-auto rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-700 transition-all hover:bg-rose-100"
              >
                Delete Pipeline
              </button>
              <button type="button" @click="closePipelineModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900">
                Cancel
              </button>
              <button type="submit" :disabled="pipelineForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition-all hover:bg-stone-800 disabled:cursor-not-allowed disabled:opacity-60">
                {{ editingPipelineId ? (pipelineForm.processing ? 'Saving...' : 'Save Pipeline') : (pipelineForm.processing ? 'Creating...' : 'Create Pipeline') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showAutomationModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="w-full max-w-2xl rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Lead Automation</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">Auto WhatsApp via n8n</h3>
            </div>
            <button type="button" @click="closeAutomationModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
              <X class="h-5 w-5" />
            </button>
          </div>

          <form class="mt-6 space-y-5" @submit.prevent="submitAutomation">
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Workflow Name</span>
              <input v-model="automationForm.workflow_name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">n8n Webhook URL</span>
              <input v-model="automationForm.webhook_url" type="url" placeholder="https://n8n.example.com/webhook/lead-created" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              <p v-if="automationForm.errors.webhook_url" class="text-xs text-rose-500">{{ automationForm.errors.webhook_url }}</p>
            </label>

            <label class="flex items-center gap-3 rounded-[1.6rem] border border-stone-200 bg-stone-50 px-4 py-4 text-sm text-stone-700">
              <input v-model="automationForm.enabled" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-emerald-600 focus:ring-emerald-400" />
              <div>
                <p class="font-semibold text-stone-900">Enable automatic WhatsApp follow-up</p>
                <p class="mt-1 text-xs text-stone-500">Saat lead baru terbentuk dari CRM atau form submission, workflow `lead_created` akan dipicu.</p>
              </div>
            </label>

            <div class="space-y-3">
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Enabled Admins</p>
              <div class="grid gap-3 sm:grid-cols-2">
                <label
                  v-for="assignee in filterOptions.assignees"
                  :key="assignee.id"
                  class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700"
                >
                  <input
                    :checked="automationForm.enabled_user_ids.includes(assignee.id)"
                    type="checkbox"
                    class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-400"
                    @change="toggleAutomationUser(assignee.id)"
                  />
                  <span>{{ assignee.name }}</span>
                </label>
              </div>
              <p class="text-xs text-stone-500">Kalau dipilih, auto WA hanya aktif untuk lead yang di-assign ke admin tersebut.</p>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3">
              <button type="button" @click="closeAutomationModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900">
                Cancel
              </button>
              <button type="submit" :disabled="automationForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition-all hover:bg-stone-800 disabled:cursor-not-allowed disabled:opacity-60">
                {{ automationForm.processing ? 'Saving...' : 'Save Automation' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </WorkspaceLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { ArrowUpRight, Download, Filter, MessageSquareShare, Pencil, Plus, RotateCcw, Settings2, X } from 'lucide-vue-next'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'

const props = defineProps({
  workspace: {
    type: Object,
    required: true,
  },
  crm: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  filterOptions: {
    type: Object,
    required: true,
  },
})

const workspace = props.workspace
const workspaceBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}`
const leadsBaseUrl = `${workspaceBaseUrl}/crm/leads`
const views = [
  { id: 'kanban', label: 'Kanban' },
  { id: 'table', label: 'Table' },
]

const viewMode = ref('kanban')
const showLeadModal = ref(false)
const showPipelineModal = ref(false)
const showAutomationModal = ref(false)
const editingLeadId = ref(null)
const editingPipelineId = ref(null)
const dragLead = ref(null)
const dropTargetStageId = ref(null)
const filterState = ref({
  pipeline: props.filters.pipeline ?? '',
  stage: props.filters.stage ?? '',
  source: props.filters.source ?? '',
  assignee: props.filters.assignee ?? '',
  date: props.filters.date ?? '',
  min_value: props.filters.min_value ?? '',
  max_value: props.filters.max_value ?? '',
})

const leadForm = useForm({
  pipeline_id: '',
  stage_id: '',
  name: '',
  company: '',
  email: '',
  phone: '',
  address: '',
  city: '',
  province: '',
  source: '',
  estimated_value: '',
  priority: 'medium',
  assigned_to: '',
  notes: '',
})

const pipelineForm = useForm({
  name: '',
  description: '',
  is_default: false,
  stages: [],
})

const automationForm = useForm({
  enabled: Boolean(props.crm.automation?.enabled),
  workflow_name: props.crm.automation?.workflow_name || 'Lead WhatsApp Follow Up',
  webhook_url: props.crm.automation?.webhook_url || '',
  enabled_user_ids: props.crm.automation?.enabled_user_ids || [],
})

const defaultPipelineId = props.filters.pipeline
  || props.crm.pipelines.find((pipeline) => pipeline.is_default)?.id
  || props.crm.pipelines[0]?.id
  || null

const activePipelineId = ref(defaultPipelineId)

const activePipeline = computed(() => {
  return props.crm.pipelines.find((pipeline) => pipeline.id === activePipelineId.value) ?? null
})

const filteredStageOptions = computed(() => {
  if (!filterState.value.pipeline) {
    return props.filterOptions.stages
  }

  return props.filterOptions.stages.filter((stage) => stage.pipeline_id === filterState.value.pipeline)
})

const modalStageOptions = computed(() => stageOptionsByPipeline(leadForm.pipeline_id))
const isEditing = computed(() => editingLeadId.value !== null)

watch(
  () => filterState.value.pipeline,
  (pipelineId) => {
    if (!pipelineId) {
      filterState.value.stage = ''
      if (!props.crm.pipelines.some((pipeline) => pipeline.id === activePipelineId.value)) {
        activePipelineId.value = defaultPipelineId
      }
      return
    }

    if (!filteredStageOptions.value.some((stage) => stage.id === filterState.value.stage)) {
      filterState.value.stage = ''
    }

    activePipelineId.value = pipelineId
  }
)

watch(
  () => props.crm.pipelines,
  (pipelines) => {
    if (!pipelines.some((pipeline) => pipeline.id === activePipelineId.value)) {
      activePipelineId.value = pipelines.find((pipeline) => pipeline.is_default)?.id || pipelines[0]?.id || null
    }
  },
  { deep: true }
)

watch(
  () => leadForm.pipeline_id,
  (pipelineId) => {
    if (!stageOptionsByPipeline(pipelineId).some((stage) => stage.id === leadForm.stage_id)) {
      leadForm.stage_id = ''
    }
  }
)

function applyFilters() {
  router.get(leadsBaseUrl, compactFilters(filterState.value), {
    preserveState: true,
    preserveScroll: true,
  })
}

function resetFilters() {
  filterState.value = {
    pipeline: '',
    stage: '',
    source: '',
    assignee: '',
    date: '',
    min_value: '',
    max_value: '',
  }

  activePipelineId.value = props.crm.pipelines.find((pipeline) => pipeline.is_default)?.id || props.crm.pipelines[0]?.id || null

  router.get(leadsBaseUrl, {}, {
    preserveState: true,
    preserveScroll: true,
  })
}

function exportCsv() {
  const query = new URLSearchParams()

  Object.entries(compactFilters(filterState.value)).forEach(([key, value]) => {
    query.set(key, String(value))
  })

  const baseUrl = `${leadsBaseUrl}/export`
  const url = query.toString() ? `${baseUrl}?${query.toString()}` : baseUrl

  window.open(url, '_self')
}

function openLead(leadId) {
  router.get(`${leadsBaseUrl}/${encodeURIComponent(leadId)}`)
}

function openCreateModal() {
  editingLeadId.value = null
  resetLeadForm()
  leadForm.pipeline_id = activePipelineId.value || ''
  showLeadModal.value = true
}

function openPipelineManager() {
  const selectedPipeline = isPersistedPipeline(activePipeline.value) ? activePipeline.value : null
  openPipelineModal(selectedPipeline)
}

function openEditModal(lead) {
  editingLeadId.value = lead.id
  leadForm.reset()
  leadForm.clearErrors()
  leadForm.pipeline_id = lead.pipeline?.id || ''
  leadForm.stage_id = lead.stage?.id || ''
  leadForm.name = lead.name || ''
  leadForm.company = lead.company || ''
  leadForm.email = lead.email || ''
  leadForm.phone = lead.phone || ''
  leadForm.address = lead.address || ''
  leadForm.city = lead.city || ''
  leadForm.province = lead.province || ''
  leadForm.source = lead.source || ''
  leadForm.estimated_value = lead.estimated_value_raw || ''
  leadForm.priority = lead.priority || 'medium'
  leadForm.assigned_to = lead.assignee?.id || ''
  leadForm.notes = lead.notes || ''
  showLeadModal.value = true
}

function closeLeadModal() {
  showLeadModal.value = false
  editingLeadId.value = null
  resetLeadForm()
}

function openPipelineModal(pipeline = null) {
  showPipelineModal.value = true

  if (!isPersistedPipeline(pipeline)) {
    startNewPipeline()
    return
  }

  editingPipelineId.value = pipeline.id
  pipelineForm.clearErrors()
  pipelineForm.name = pipeline.name || ''
  pipelineForm.description = pipeline.description || ''
  pipelineForm.is_default = Boolean(pipeline.is_default)
  pipelineForm.stages = (pipeline.stages || []).map((stage) => ({
    local_key: stage.id,
    id: stage.id,
    name: stage.name,
    color: stage.color || '#94A3B8',
    is_won: Boolean(stage.is_won),
    is_lost: Boolean(stage.is_lost),
  }))

  if (pipelineForm.stages.length === 0) {
    addStageRow()
  }
}

function startNewPipeline() {
  editingPipelineId.value = null
  pipelineForm.reset()
  pipelineForm.clearErrors()
  pipelineForm.name = ''
  pipelineForm.description = ''
  pipelineForm.is_default = false
  pipelineForm.stages = [createStageRow('New', '#94A3B8')]
}

function closePipelineModal() {
  showPipelineModal.value = false
  editingPipelineId.value = null
  pipelineForm.reset()
  pipelineForm.clearErrors()
  pipelineForm.stages = []
}

function openAutomationModal() {
  automationForm.reset()
  automationForm.clearErrors()
  automationForm.enabled = Boolean(props.crm.automation?.enabled)
  automationForm.workflow_name = props.crm.automation?.workflow_name || 'Lead WhatsApp Follow Up'
  automationForm.webhook_url = props.crm.automation?.webhook_url || ''
  automationForm.enabled_user_ids = [...(props.crm.automation?.enabled_user_ids || [])]
  showAutomationModal.value = true
}

function closeAutomationModal() {
  showAutomationModal.value = false
  automationForm.clearErrors()
}

function submitLead() {
  const options = {
    preserveScroll: true,
    onSuccess: () => {
      closeLeadModal()
    },
  }

  if (isEditing.value) {
    leadForm.patch(`${leadsBaseUrl}/${encodeURIComponent(editingLeadId.value)}`, options)

    return
  }

  leadForm.post(leadsBaseUrl, options)
}

function moveLeadStage(leadId, pipelineId, stageId) {
  router.patch(`${leadsBaseUrl}/${encodeURIComponent(leadId)}/stage`, {
    pipeline_id: normalizePipelineId(pipelineId),
    stage_id: normalizeStageId(stageId),
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

function wonStageForPipeline(pipelineId) {
  if (!pipelineId) {
    return null
  }

  const pipeline = props.crm.pipelines.find((item) => item.id === pipelineId)
  return pipeline?.stages.find((stage) => stage.is_won) || null
}

function lostStageForPipeline(pipelineId) {
  if (!pipelineId) {
    return null
  }

  const pipeline = props.crm.pipelines.find((item) => item.id === pipelineId)
  return pipeline?.stages.find((stage) => stage.is_lost) || null
}

function handleLeadDragStart(lead, pipelineId) {
  dragLead.value = {
    id: lead.id,
    pipelineId,
  }
}

function handleLeadDragEnd() {
  dragLead.value = null
  dropTargetStageId.value = null
}

function handleStageDragOver(stageId) {
  dropTargetStageId.value = stageId
}

function handleStageDragLeave(stageId) {
  if (dropTargetStageId.value === stageId) {
    dropTargetStageId.value = null
  }
}

function handleStageDrop(pipelineId, stageId) {
  if (!dragLead.value) {
    return
  }

  moveLeadStage(dragLead.value.id, pipelineId, normalizeStageId(stageId))
  dragLead.value = null
  dropTargetStageId.value = null
}

function submitPipeline() {
  const payload = {
    ...pipelineForm.data(),
    stages: pipelineForm.stages.map((stage) => ({
      id: stage.id || null,
      name: stage.name,
      color: stage.color,
      is_won: Boolean(stage.is_won),
      is_lost: Boolean(stage.is_lost),
    })),
  }

  const options = {
    preserveScroll: true,
    onSuccess: () => {
      closePipelineModal()
    },
  }

  if (editingPipelineId.value) {
    pipelineForm.transform(() => payload).patch(`${leadsBaseUrl}/pipelines/${encodeURIComponent(editingPipelineId.value)}`, options)

    return
  }

  pipelineForm.transform(() => payload).post(`${leadsBaseUrl}/pipelines`, options)
}

function submitAutomation() {
  automationForm.patch(`${leadsBaseUrl}/automation/settings`, {
    preserveScroll: true,
    onSuccess: () => {
      closeAutomationModal()
    },
  })
}

function toggleAutomationUser(userId) {
  if (automationForm.enabled_user_ids.includes(userId)) {
    automationForm.enabled_user_ids = automationForm.enabled_user_ids.filter((id) => id !== userId)
    return
  }

  automationForm.enabled_user_ids = [...automationForm.enabled_user_ids, userId]
}

function stageOptionsByPipeline(pipelineId) {
  const resolvedPipelineId = normalizePipelineId(pipelineId)

  if (!resolvedPipelineId) {
    return []
  }

  return props.filterOptions.stages.filter((stage) => stage.pipeline_id === resolvedPipelineId)
}

function compactFilters(filters) {
  return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== '' && value !== null && value !== undefined))
}

function normalizePipelineId(pipelineId) {
  if (!pipelineId || pipelineId === 'no-pipeline') {
    return null
  }

  return pipelineId
}

function normalizeStageId(stageId) {
  if (!stageId || String(stageId).startsWith('unassigned-')) {
    return null
  }

  return stageId
}

function priorityClass(priority) {
  const map = {
    high: 'bg-rose-100 text-rose-700',
    medium: 'bg-amber-100 text-amber-700',
    low: 'bg-emerald-100 text-emerald-700',
  }

  return map[priority] || 'bg-stone-100 text-stone-600'
}

function scoreClass(score) {
  if (score === null || score === undefined) {
    return 'bg-stone-100 text-stone-500'
  }

  if (score >= 80) {
    return 'bg-emerald-100 text-emerald-700'
  }

  if (score >= 60) {
    return 'bg-amber-100 text-amber-700'
  }

  return 'bg-rose-100 text-rose-700'
}

function resetLeadForm() {
  leadForm.reset()
  leadForm.clearErrors()
  leadForm.pipeline_id = ''
  leadForm.stage_id = ''
  leadForm.address = ''
  leadForm.city = ''
  leadForm.province = ''
  leadForm.priority = 'medium'
}

function createStageRow(name = '', color = '#94A3B8') {
  return {
    local_key: `stage-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
    id: null,
    name,
    color,
    is_won: false,
    is_lost: false,
  }
}

function addStageRow() {
  pipelineForm.stages.push(createStageRow())
}

function removeStageRow(index) {
  if (pipelineForm.stages.length === 1) {
    return
  }

  pipelineForm.stages.splice(index, 1)
}

function deletePipeline() {
  if (!editingPipelineId.value || !isPersistedPipeline({ id: editingPipelineId.value }) || !confirm('Delete this pipeline? Leads under it will be kept and become unassigned.')) {
    return
  }

  router.delete(`${leadsBaseUrl}/pipelines/${encodeURIComponent(editingPipelineId.value)}`, {
    preserveScroll: true,
    onSuccess: () => {
      closePipelineModal()
    },
  })
}

function isPersistedPipeline(pipeline) {
  return Boolean(pipeline?.id && pipeline.id !== 'no-pipeline')
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
