<template>
  <WorkspaceLayout
    title="Operasional"
    subtitle="Pusat eksekusi proyek untuk memantau tahap kerja, kapasitas tim, anggaran, dan deliverable dalam satu alur."
  >
    <template #actions>
      <div class="flex flex-wrap items-center gap-3">
        <button
          type="button"
          @click="openTemplateModal()"
          class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-stone-300 hover:text-stone-950"
        >
          <LayoutTemplate class="h-4 w-4" />
          <span>Templat Proyek</span>
        </button>
        <button
          type="button"
          @click="openProjectModal()"
          class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
        >
          <Plus class="h-4 w-4" />
          <span>Buat Proyek</span>
        </button>
      </div>
    </template>

    <ProjectLayout :workspace="workspace">
      <div class="space-y-6">
      <section class="project-hero-shell">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div class="project-hero-copy">
            <p class="project-hero-kicker">Menu 8 / Proyek</p>
            <h2 class="project-hero-title">Papan proyek dibuat lebih padat supaya linimasa, beban kerja, dan persetujuan lebih cepat terbaca.</h2>
            <p class="project-hero-desc">
              Tabel, kanban, dan grid tetap lengkap, tapi area pembuka dipadatkan agar fokus layar langsung jatuh ke daftar proyek dan progresnya.
            </p>
          </div>

          <div class="compact-stat-grid compact-stat-grid-3 min-w-full sm:min-w-[22rem] xl:w-[26rem]">
            <div class="compact-stat-card">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Aktif</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ projectSummary.active_projects }}</p>
            </div>
            <div class="compact-stat-card">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Selesai</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ projectSummary.completed_projects }}</p>
            </div>
            <div class="compact-stat-card">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Terlambat</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ projectSummary.overdue_projects }}</p>
            </div>
          </div>
        </div>

        <div class="mt-4 compact-stat-grid compact-stat-grid-4 md:grid-cols-2">
          <div class="compact-stat-card">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Total Proyek</p>
            <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ projectSummary.total_projects }}</p>
            <p class="mt-1 text-xs leading-5 text-stone-500">Semua proyek aktif dan arsip kerja ruang kerja.</p>
          </div>
          <div class="compact-stat-card">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Budget Portofolio</p>
            <p class="mt-2 text-sm font-semibold text-stone-950">{{ projectSummary.total_budget_label }}</p>
            <p class="mt-1 text-xs leading-5 text-stone-500">Biaya aktual {{ projectSummary.total_actual_cost_label }}</p>
          </div>
          <div class="compact-stat-card">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Fokus Delivery</p>
            <p class="mt-2 text-sm leading-6 text-stone-600">Pantau status, budget, owner, dan deadline dalam satu alur kerja yang lebih rapat.</p>
          </div>
          <div class="compact-stat-card">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Persetujuan</p>
            <p class="mt-2 text-sm leading-6 text-stone-600">Kanban tetap bisa dipakai untuk memindahkan stage dan cek approval menunggu lebih cepat.</p>
          </div>
        </div>

        <div class="mt-3 rounded-[1rem] border border-dashed border-stone-300 bg-stone-50 px-4 py-3 text-sm leading-6 text-stone-500">
          Ringkasan atas diperkecil supaya halaman tidak terasa sesak, terutama saat lanjut ke table, kanban, dan panel detail project.
        </div>
      </section>

      <section class="project-panel-shell">
        <div class="filter-panel-head mb-5">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Filter Proyek</p>
            <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Cari client, member, deadline, dan budget tanpa memenuhi area kerja utama.</h2>
          </div>
          <div class="filter-meta-badge">
            <span class="font-semibold text-stone-950">{{ projectItems.length }}</span> project tampil dengan
            <span class="font-semibold text-stone-950"> {{ activeFilterChips.length }}</span> filter aktif
          </div>
        </div>

        <div class="filter-toolbar grid gap-4 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
          <label class="space-y-2 text-sm xl:col-span-2">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Cari</span>
            <input
              v-model="filterState.search"
              type="text"
             
              class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
            />
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Klien</span>
            <select v-model="filterState.client" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Klien</option>
              <option v-for="client in filterOptions.clients" :key="client.id" :value="client.id">{{ client.name }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
            <select v-model="filterState.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Status</option>
              <option v-for="status in filterOptions.statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Anggota Tim</span>
            <select v-model="filterState.assignee" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Anggota</option>
              <option v-for="assignee in filterOptions.assignees" :key="assignee.id" :value="assignee.id">{{ assignee.name }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Deadline</span>
            <select v-model="filterState.deadline" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Tenggat</option>
              <option v-for="deadline in filterOptions.deadlines" :key="deadline.value" :value="deadline.value">{{ deadline.label }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Budget</span>
            <select v-model="filterState.budget" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Budget</option>
              <option v-for="budget in filterOptions.budgets" :key="budget.value" :value="budget.value">{{ budget.label }}</option>
            </select>
          </label>
        </div>

        <div class="filter-actions mt-5 flex flex-wrap items-center justify-between gap-3">
          <div class="flex flex-wrap items-center gap-3">
            <button
              type="button"
              @click="applyFilters"
              class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition-all hover:-translate-y-0.5 hover:bg-stone-800"
            >
              <Filter class="h-4 w-4" />
              <span>Terapkan Filter</span>
            </button>
            <button
              type="button"
              @click="resetFilters"
              class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900"
            >
              <RotateCcw class="h-4 w-4" />
              <span>Atur Ulang</span>
            </button>
          </div>

          <div class="segmented-control">
            <button
              v-for="view in viewModes"
              :key="view.id"
              type="button"
              @click="viewMode = view.id"
              :class="[
                'inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition-all',
                viewMode === view.id ? 'bg-stone-950 text-white shadow-sm' : 'text-stone-600 hover:text-stone-950',
              ]"
            >
              <component :is="view.icon" class="h-4 w-4" />
              <span>{{ view.label }}</span>
            </button>
          </div>
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

      <section class="grid gap-4 xl:grid-cols-[1.55fr_0.95fr]">
        <article class="overflow-hidden rounded-[2rem] border border-stone-200 bg-white shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <div class="flex items-center justify-between border-b border-stone-200 px-6 py-5">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Tampilan Proyek</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ currentViewTitle }}</h2>
            </div>
            <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">
              {{ projectItems.length }} project
            </span>
          </div>

          <div v-if="viewMode === 'kanban'" class="overflow-x-auto p-4">
            <div class="grid min-w-[1120px] gap-4 xl:grid-cols-4">
              <article
                v-for="column in kanbanColumns"
                :key="column.id"
                class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-4 transition-all"
                :class="dragColumnClass(column.id)"
                @dragover.prevent="handleColumnDragOver(column.id)"
                @dragleave="handleColumnDragLeave(column.id)"
                @drop.prevent="handleDrop(column.id)"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm font-semibold text-stone-950">{{ column.label }}</p>
                    <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">{{ column.items.length }} cards</p>
                  </div>
                  <span class="h-2.5 w-2.5 rounded-full" :class="column.dotClass"></span>
                </div>

                <div
                  v-if="dragOverColumn === column.id && draggingProjectId"
                  class="mt-4 rounded-[1.2rem] border border-dashed border-amber-300 bg-amber-50 px-4 py-3 text-[11px] font-bold uppercase tracking-[0.18em] text-amber-700"
                >
                  Lepas di sini untuk memindahkan project
                </div>

                <div class="mt-4 space-y-3">
                  <button
                    v-for="project in column.items"
                    :key="project.id"
                    type="button"
                    draggable="true"
                    @click="openProject(project.id)"
                    @dragstart="handleDragStart($event, project.id)"
                    @dragend="handleDragEnd"
                    class="w-full rounded-[1.4rem] border border-white bg-white p-4 text-left shadow-[0_12px_30px_rgba(28,25,23,0.05)] transition-all hover:-translate-y-1 hover:border-stone-200"
                    :class="{
                      'cursor-grabbing opacity-60': draggingProjectId === project.id,
                      'cursor-grab': draggingProjectId !== project.id,
                    }"
                  >
                    <div class="flex items-start justify-between gap-3">
                      <div>
                        <h3 class="text-sm font-semibold text-stone-950">{{ project.name }}</h3>
                        <p class="mt-2 text-xs text-stone-500">{{ project.client?.company_name || 'Belum terhubung ke klien' }}</p>
                      </div>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="statusClass(project.status)">
                        {{ project.status_label }}
                      </span>
                    </div>

                    <p class="mt-3 line-clamp-2 text-sm text-stone-600">{{ project.description || 'Belum ada deskripsi scope project.' }}</p>

                    <div class="mt-4">
                      <div class="flex items-center justify-between text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">
                        <span>Progress</span>
                        <span>{{ project.progress }}%</span>
                      </div>
                      <div class="mt-2 h-2 rounded-full bg-stone-200">
                        <div class="h-2 rounded-full transition-all" :class="progressBarClass(project.progress)" :style="{ width: `${project.progress}%` }"></div>
                      </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                      <div class="flex -space-x-2">
                        <span
                          v-for="member in project.members.slice(0, 3)"
                          :key="`${project.id}-${member.id}`"
                          class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-white bg-stone-200 text-[10px] font-bold uppercase text-stone-700"
                        >
                          {{ member.initials }}
                        </span>
                        <span
                          v-if="project.members.length > 3"
                          class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-white bg-stone-950 text-[10px] font-bold uppercase text-white"
                        >
                          +{{ project.members.length - 3 }}
                        </span>
                      </div>
                      <div class="text-right text-xs text-stone-500">
                        <p>{{ project.timeline_label }}</p>
                        <p :class="timelineTextClass(project.timeline_state)">{{ project.pending_approvals }} approval menunggu</p>
                      </div>
                    </div>
                  </button>

                  <div
                    v-if="column.items.length === 0"
                    class="rounded-[1.4rem] border border-dashed border-stone-200 bg-white/70 px-4 py-8 text-center text-sm text-stone-500"
                  >
                    Belum ada project di kolom ini.
                  </div>
                </div>
              </article>
            </div>
          </div>

          <div v-else-if="viewMode === 'table'" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200 text-sm">
              <thead class="bg-stone-50 text-left text-[11px] font-bold uppercase tracking-[0.22em] text-stone-400">
                <tr>
                  <th class="px-5 py-4">Proyek</th>
                  <th class="px-5 py-4">Klien</th>
                  <th class="px-5 py-4">Timeline</th>
                  <th class="px-5 py-4">Budget</th>
                  <th class="px-5 py-4">Team</th>
                  <th class="px-5 py-4">Delivery</th>
                  <th class="px-5 py-4"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-stone-100 bg-white">
                <tr v-for="project in projectItems" :key="project.id" class="transition-colors hover:bg-stone-50/70">
                  <td class="px-5 py-4 align-top">
                    <div class="space-y-2">
                      <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="openProject(project.id)" class="text-left text-sm font-semibold text-stone-950 transition hover:text-amber-700">
                          {{ project.name }}
                        </button>
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="statusClass(project.status)">
                          {{ project.status_label }}
                        </span>
                      </div>
                      <p class="line-clamp-2 text-sm text-stone-500">{{ project.description || 'Belum ada deskripsi scope project.' }}</p>
                      <div class="flex flex-wrap gap-2">
                        <span
                          v-for="tag in project.tags"
                          :key="`${project.id}-${tag}`"
                          class="rounded-full bg-stone-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-stone-500"
                        >
                          {{ tag }}
                        </span>
                        <span v-if="project.template" class="rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-amber-700">
                          {{ project.template.name }}
                        </span>
                      </div>
                    </div>
                  </td>
                  <td class="px-5 py-4 align-top">
                    <div class="space-y-2 text-stone-600">
                      <p class="font-medium text-stone-800">{{ project.client?.company_name || 'Tanpa klien' }}</p>
                      <span v-if="project.portal_enabled" class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-700">
                        Portal Aktif
                      </span>
                    </div>
                  </td>
                  <td class="px-5 py-4 align-top">
                    <div class="space-y-2 text-stone-600">
                      <p>{{ project.timeline_label }}</p>
                      <p class="text-xs" :class="timelineTextClass(project.timeline_state)">{{ timelineCaption(project.timeline_state) }}</p>
                    </div>
                  </td>
                  <td class="px-5 py-4 align-top">
                    <div class="space-y-2 text-stone-600">
                      <p>{{ project.budget_label }}</p>
                      <p class="text-xs text-stone-500">Actual {{ project.actual_cost_label }}</p>
                      <p class="text-xs text-stone-500">Remaining {{ project.remaining_budget_label }}</p>
                    </div>
                  </td>
                  <td class="px-5 py-4 align-top">
                    <div class="space-y-2">
                      <p class="text-sm text-stone-700">{{ project.members.length ? project.members.map((member) => member.name).join(', ') : 'Belum ada team member' }}</p>
                      <p class="text-xs text-stone-500">Tugas {{ project.counts.completed_tasks }}/{{ project.counts.tasks }} selesai</p>
                    </div>
                  </td>
                  <td class="px-5 py-4 align-top">
                    <div class="space-y-2 text-stone-600">
                      <p>{{ project.pending_approvals }} approval menunggu</p>
                      <p class="text-xs text-stone-500">{{ project.counts.files }} files, {{ project.counts.meetings }} meetings</p>
                      <div class="mt-2 h-2 rounded-full bg-stone-200">
                        <div class="h-2 rounded-full transition-all" :class="progressBarClass(project.progress)" :style="{ width: `${project.progress}%` }"></div>
                      </div>
                    </div>
                  </td>
                  <td class="px-5 py-4 align-top text-right">
                    <div class="flex justify-end gap-2">
                      <button type="button" @click="openEditProjectModal(project)" class="inline-flex items-center gap-2 rounded-full border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-700 transition-all hover:border-stone-300 hover:text-stone-950">
                        <Pencil class="h-3.5 w-3.5" />
                      </button>
                      <button type="button" @click="openProject(project.id)" class="inline-flex items-center gap-2 rounded-full border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-700 transition-all hover:border-stone-300 hover:text-stone-950">
                        <ArrowUpRight class="h-3.5 w-3.5" />
                      </button>
                      <button type="button" @click="deleteProject(project.id)" class="inline-flex items-center gap-2 rounded-full border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-700 transition-all hover:bg-rose-50">
                        <Trash2 class="h-3.5 w-3.5" />
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="projectItems.length === 0">
                  <td colspan="7" class="px-5 py-16 text-center text-sm text-stone-500">Belum ada project yang cocok dengan filter saat ini.</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="grid gap-4 p-4 md:grid-cols-2 2xl:grid-cols-3">
            <article
              v-for="project in projectItems"
              :key="project.id"
              class="group rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_40px_rgba(28,25,23,0.05)] transition-all hover:-translate-y-1 hover:shadow-[0_22px_50px_rgba(28,25,23,0.08)]"
            >
              <div class="flex items-start justify-between gap-3">
                <div>
                  <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-stone-400">{{ project.client?.company_name || 'Proyek Internal' }}</p>
                  <h3 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ project.name }}</h3>
                </div>
                <div class="flex items-center gap-2">
                  <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-[9px] font-bold uppercase tracking-widest text-amber-700 border border-amber-100">
                    {{ project.brand }}
                  </span>
                  <span class="rounded-full px-2.5 py-0.5 text-[9px] font-bold uppercase tracking-widest" :class="project.members.length > 1 ? 'bg-sky-50 text-sky-700 border border-sky-100' : 'bg-stone-50 text-stone-600 border border-stone-100'">
                    {{ project.members.length > 1 ? 'Tim' : 'Solo' }}
                  </span>
                  <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="statusClass(project.status)">
                    {{ project.status_label }}
                  </span>
                </div>
              </div>

              <p class="mt-4 line-clamp-3 text-sm leading-6 text-stone-600">{{ project.description || 'Belum ada deskripsi scope project.' }}</p>

              <div class="mt-4 flex flex-wrap gap-2">
                <span
                  v-for="tag in project.tags"
                  :key="`${project.id}-grid-${tag}`"
                  class="rounded-full bg-stone-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-stone-500"
                >
                  {{ tag }}
                </span>
                <span v-if="project.template" class="rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-amber-700">
                  {{ project.template.name }}
                </span>
              </div>

              <div class="mt-6 grid gap-3 sm:grid-cols-2">
                <div class="rounded-[1.3rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Budget</p>
                  <p class="mt-2 text-sm font-semibold text-stone-900">{{ project.budget_label }}</p>
                  <p class="mt-1 text-xs text-stone-500">Actual {{ project.actual_cost_label }}</p>
                </div>
                <div class="rounded-[1.3rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Timeline</p>
                  <p class="mt-2 text-sm font-semibold text-stone-900">{{ project.timeline_label }}</p>
                  <p class="mt-1 text-xs" :class="timelineTextClass(project.timeline_state)">{{ timelineCaption(project.timeline_state) }}</p>
                </div>
              </div>

              <div class="mt-5">
                <div class="flex items-center justify-between text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">
                  <span>Progress</span>
                  <span>{{ project.progress }}%</span>
                </div>
                <div class="mt-2 h-2 rounded-full bg-stone-200">
                  <div class="h-2 rounded-full transition-all" :class="progressBarClass(project.progress)" :style="{ width: `${project.progress}%` }"></div>
                </div>
              </div>

              <div class="mt-5 flex items-center justify-between">
                <div class="flex -space-x-2">
                  <span
                    v-for="member in project.members.slice(0, 4)"
                    :key="`${project.id}-avatar-${member.id}`"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-white bg-stone-200 text-[10px] font-bold uppercase text-stone-700"
                  >
                    {{ member.initials }}
                  </span>
                </div>
                <p class="text-xs text-stone-500">{{ project.pending_approvals }} approval menunggu</p>
              </div>

              <div class="mt-5 flex items-center justify-between border-t border-stone-100 pt-4">
                <p class="text-xs text-stone-500">{{ project.counts.tasks }} tasks | {{ project.counts.meetings }} meetings | {{ project.counts.invoices }} invoices</p>
                <div class="flex gap-2">
                  <button type="button" @click="openEditProjectModal(project)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                    <Pencil class="h-4 w-4" />
                  </button>
                  <button type="button" @click="openProject(project.id)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                    <ArrowUpRight class="h-4 w-4" />
                  </button>
                </div>
              </div>
            </article>

            <div
              v-if="projectItems.length === 0"
              class="md:col-span-2 2xl:col-span-3 rounded-[1.8rem] border border-dashed border-stone-200 bg-white px-5 py-20 text-center text-sm text-stone-500"
            >
              Belum ada project yang cocok dengan filter saat ini.
            </div>
          </div>
        </article>

        <aside class="space-y-4">
          <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-4">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Templat Proyek</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Blueprint delivery yang bisa dipakai ulang</h2>
              </div>
              <button type="button" @click="openTemplateModal()" class="inline-flex items-center gap-2 rounded-full border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-700 transition-all hover:border-stone-300 hover:text-stone-950">
                <Plus class="h-3.5 w-3.5" />
                <span>Baru</span>
              </button>
            </div>

            <div class="mt-5 space-y-3">
              <article
                v-for="template in projectTemplates"
                :key="template.id"
                class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4"
              >
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <h3 class="text-sm font-semibold text-stone-950">{{ template.name }}</h3>
                    <p class="mt-2 text-sm text-stone-600">{{ template.description || 'Templat tanpa deskripsi.' }}</p>
                  </div>
                  <div class="flex gap-2">
                    <button type="button" @click="openTemplateModal(template)" class="rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                      <Pencil class="h-3.5 w-3.5" />
                    </button>
                    <button type="button" @click="deleteTemplate(template.id)" class="rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
                      <Trash2 class="h-3.5 w-3.5" />
                    </button>
                  </div>
                </div>

                <div class="mt-4 rounded-[1.2rem] border border-white bg-white/80 p-3">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Tugas Default</p>
                  <ul class="mt-3 space-y-2 text-sm text-stone-600">
                    <li v-for="task in template.default_tasks.slice(0, 4)" :key="`${template.id}-${task}`" class="flex items-start gap-2">
                      <span class="mt-1.5 h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                      <span>{{ task }}</span>
                    </li>
                    <li v-if="template.default_tasks.length === 0" class="text-stone-500">Belum ada default task.</li>
                  </ul>
                </div>
              </article>

              <div
                v-if="projectTemplates.length === 0"
                class="rounded-[1.5rem] border border-dashed border-stone-200 bg-stone-50 px-4 py-10 text-center text-sm text-stone-500"
              >
                Belum ada template. Buat template untuk web dev, social media, atau retainer project.
              </div>
            </div>
          </section>

          <section class="rounded-[2rem] border border-stone-200 bg-stone-950 p-5 text-white shadow-[0_20px_60px_rgba(28,25,23,0.14)]">
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-amber-200/70">Sinyal Proyek</p>
            <div class="mt-5 space-y-4">
              <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-4">
                <p class="text-sm font-semibold text-white">Kesiapan portal klien</p>
                <p class="mt-2 text-sm text-stone-300">
                  {{ projectItems.filter((project) => project.portal_enabled).length }} project sudah terhubung ke portal klien baca-saja untuk approval dan visibilitas invoice.
                </p>
              </div>
              <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-4">
                <p class="text-sm font-semibold text-white">Antrean approval</p>
                <p class="mt-2 text-sm text-stone-300">
                  {{ totalPendingApprovals }} deliverable masih menunggu approval klien dari menu project ini.
                </p>
              </div>
              <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-4">
                <p class="text-sm font-semibold text-white">Pemakaian template</p>
                <p class="mt-2 text-sm text-stone-300">
                  {{ projectItems.filter((project) => project.template).length }} project sudah memakai template agar kickoff dan task setup lebih cepat.
                </p>
              </div>
            </div>
          </section>
        </aside>
      </section>
    </div>

    <Transition name="modal">
      <div v-if="showProjectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Formulir Proyek</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditingProject ? 'Ubah Proyek' : 'Buat Proyek' }}</h3>
            </div>
            <button type="button" @click="closeProjectModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
              <X class="h-5 w-5" />
            </button>
          </div>

          <form class="mt-6 space-y-5" @submit.prevent="submitProject">
            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2 text-sm md:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Nama Proyek</span>
                <input v-model="projectForm.name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                <p v-if="projectForm.errors.name" class="text-xs text-rose-500">{{ projectForm.errors.name }}</p>
              </label>

              <label class="space-y-2 text-sm md:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Deskripsi</span>
                <textarea v-model="projectForm.description" rows="4" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Brand / Agensi</span>
                <select v-model="projectForm.brand" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="Kantor Digital">Kantor Digital</option>
                  <option value="Maven Forge">Maven Forge</option>
                  <option value="Velora ID">Velora ID</option>                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Klien</span>
                <select v-model="projectForm.client_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Tanpa Klien</option>
                  <option v-for="client in filterOptions.clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                </select>
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Templat</span>
                <select v-model="projectForm.template_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Tanpa Templat</option>
                  <option v-for="template in projectTemplates" :key="template.id" :value="template.id">{{ template.name }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
                <select v-model="projectForm.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option v-for="status in filterOptions.statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Label</span>
                <input v-model="projectTagInput" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tanggal Mulai</span>
                <input v-model="projectForm.start_date" type="date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tanggal Selesai</span>
                <input v-model="projectForm.end_date" type="date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                <p v-if="projectForm.errors.end_date" class="text-xs text-rose-500">{{ projectForm.errors.end_date }}</p>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Budget</span>
                <input v-model="projectForm.budget" type="number" min="0" step="0.01" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Biaya Aktual</span>
                <input v-model="projectForm.actual_cost" type="number" min="0" step="0.01" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>
            </div>

            <section class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-4">
              <div class="flex items-center justify-between gap-4">
                <div>
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Anggota Tim</p>
                  <p class="mt-1 text-sm text-stone-500">Assign team dan role yang terlibat di project ini.</p>
                </div>
                <button type="button" @click="addMemberRow" class="inline-flex items-center gap-2 rounded-full border border-stone-200 bg-white px-3 py-2 text-xs font-semibold text-stone-700 transition-all hover:border-stone-300 hover:text-stone-950">
                  <Plus class="h-3.5 w-3.5" />
                  <span>Tambah Anggota</span>
                </button>
              </div>

              <div class="mt-4 space-y-3">
                <div v-for="(member, index) in projectForm.members" :key="`member-${index}`" class="grid gap-3 rounded-[1.3rem] border border-stone-200 bg-white p-4 md:grid-cols-[1fr_0.8fr_auto]">
                  <label class="space-y-2 text-sm">
                    <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">User</span>
                    <select v-model="member.user_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                      <option value="">Pilih anggota tim</option>
                      <option v-for="assignee in filterOptions.assignees" :key="assignee.id" :value="assignee.id">{{ assignee.name }}</option>
                    </select>
                  </label>
                  <label class="space-y-2 text-sm">
                    <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Role</span>
                    <input v-model="member.role" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                  </label>
                  <div class="flex items-end">
                    <button type="button" @click="removeMemberRow(index)" class="inline-flex items-center gap-2 rounded-full border border-rose-200 px-3 py-3 text-xs font-semibold text-rose-700 transition-all hover:bg-rose-50">
                      <Trash2 class="h-3.5 w-3.5" />
                    </button>
                  </div>
                </div>

                <p v-if="projectForm.errors.members" class="text-xs text-rose-500">{{ projectForm.errors.members }}</p>
              </div>
            </section>

            <div class="flex flex-wrap items-center justify-end gap-3">
              <button type="button" @click="closeProjectModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900">
                Batal
              </button>
              <button type="submit" :disabled="projectForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition-all hover:bg-stone-800 disabled:cursor-not-allowed disabled:opacity-60">
                {{ isEditingProject ? (projectForm.processing ? 'Menyimpan...' : 'Simpan Proyek') : (projectForm.processing ? 'Membuat...' : 'Buat Proyek') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showTemplateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Form Templat</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditingTemplate ? 'Ubah Templat' : 'Buat Templat' }}</h3>
            </div>
            <button type="button" @click="closeTemplateModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
              <X class="h-5 w-5" />
            </button>
          </div>

          <form class="mt-6 space-y-5" @submit.prevent="submitTemplate">
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Nama Templat</span>
              <input v-model="templateForm.name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              <p v-if="templateForm.errors.name" class="text-xs text-rose-500">{{ templateForm.errors.name }}</p>
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Deskripsi</span>
              <textarea v-model="templateForm.description" rows="3" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tugas Default</span>
              <textarea
                v-model="templateForm.default_tasks_text"
                rows="7"
               
                class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm leading-7 text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
              ></textarea>
              <p class="text-xs text-stone-500">Satu baris untuk satu default task.</p>
            </label>

            <div class="flex flex-wrap items-center justify-end gap-3">
              <button type="button" @click="closeTemplateModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900">
                Batal
              </button>
              <button type="submit" :disabled="templateForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition-all hover:bg-stone-800 disabled:cursor-not-allowed disabled:opacity-60">
                {{ isEditingTemplate ? (templateForm.processing ? 'Menyimpan...' : 'Simpan Templat') : (templateForm.processing ? 'Membuat...' : 'Buat Templat') }}
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
import { computed, onMounted, ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import {
  ArrowUpRight,
  Filter,
  KanbanSquare,
  LayoutGrid,
  LayoutList,
  LayoutTemplate,
  Pencil,
  Plus,
  RotateCcw,
  Trash2,
  X,
} from 'lucide-vue-next'
import WorkspaceLayout from '../../Layouts/WorkspaceLayout.vue'
import ProjectLayout from '../../Layouts/ProjectLayout.vue'

const props = defineProps({
  workspace: {
    type: Object,
    required: true,
  },
  projects: {
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
  projectTemplates: {
    type: Array,
    default: () => [],
  },
})

const workspaceBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}`
const projectsBaseUrl = `${workspaceBaseUrl}/projects`
const templatesBaseUrl = `${projectsBaseUrl}/templates`

const localProjects = ref(cloneProjects(props.projects.items || []))

const viewModes = [
  { id: 'kanban', label: 'Kanban', icon: KanbanSquare },
  { id: 'table', label: 'Tabel', icon: LayoutList },
  { id: 'grid', label: 'Kartu', icon: LayoutGrid },
]

const filterState = ref(buildFilterState(props.filters))

const viewMode = ref('kanban')
const showProjectModal = ref(false)
const showTemplateModal = ref(false)
const editingProjectId = ref(null)
const editingTemplateId = ref(null)
const projectTagInput = ref('')
const draggingProjectId = ref(null)
const dragOverColumn = ref(null)
const movingProjectId = ref(null)

const projectForm = useForm({
  client_id: '',
  brand: 'Kantor Digital',
  template_id: '',
  name: '',
  description: '',
  status: 'planning',
  start_date: '',
  end_date: '',
  budget: '',
  actual_cost: '',
  tags: [],
  members: [emptyMemberRow()],
})

const templateForm = useForm({
  name: '',
  description: '',
  default_tasks_text: '',
})

onMounted(() => {
  const query = new URLSearchParams(window.location.search)
  const openModal = query.get('open_modal')
  const clientId = query.get('client_id')

  if (openModal === 'project') {
    openProjectModal()
    if (clientId) {
      projectForm.client_id = clientId
    }
  }
})

const currentViewTitle = computed(() => {
  if (viewMode.value === 'table') return 'Tabel project untuk cek detail cepat'
  if (viewMode.value === 'grid') return 'Kartu proyek untuk baca konteks lebih santai'
  return 'Kanban proyek untuk geser status lebih cepat'
})

const isEditingProject = computed(() => editingProjectId.value !== null)
const isEditingTemplate = computed(() => editingTemplateId.value !== null)

const projectItems = computed(() => localProjects.value)

const projectSummary = computed(() => ({
  ...props.projects.summary,
  total_projects: projectItems.value.length,
  active_projects: projectItems.value.filter((project) => project.status === 'active').length,
  completed_projects: projectItems.value.filter((project) => project.status === 'completed').length,
  overdue_projects: projectItems.value.filter((project) => project.timeline_state === 'overdue' && project.status !== 'completed').length,
}))

const kanbanColumns = computed(() => {
  const groups = {
    planning: [],
    active: [],
    on_hold: [],
    completed: [],
  }

  projectItems.value.forEach((project) => {
    if (groups[project.status]) {
      groups[project.status].push(project)
    }
  })

  return [
    { id: 'planning', label: 'Perencanaan', items: groups.planning, dotClass: 'bg-slate-400' },
    { id: 'active', label: 'Aktif', items: groups.active, dotClass: 'bg-emerald-500' },
    { id: 'on_hold', label: 'Ditahan', items: groups.on_hold, dotClass: 'bg-amber-500' },
    { id: 'completed', label: 'Selesai', items: groups.completed, dotClass: 'bg-stone-500' },
  ]
})

const totalPendingApprovals = computed(() => projectItems.value.reduce((total, project) => total + (project.pending_approvals || 0), 0))
const activeFilterChips = computed(() => {
  const chips = []

  if (filterState.value.search) {
    chips.push({ key: 'search', label: `Cari: ${filterState.value.search}` })
  }

  if (filterState.value.client) {
    const client = props.filterOptions.clients.find((item) => String(item.id) === String(filterState.value.client))
    chips.push({ key: 'client', label: `Klien: ${client?.name || filterState.value.client}` })
  }

  if (filterState.value.status) {
    const status = props.filterOptions.statuses.find((item) => item.value === filterState.value.status)
    chips.push({ key: 'status', label: `Status: ${status?.label || filterState.value.status}` })
  }

  if (filterState.value.assignee) {
    const assignee = props.filterOptions.assignees.find((item) => String(item.id) === String(filterState.value.assignee))
    chips.push({ key: 'assignee', label: `Anggota: ${assignee?.name || filterState.value.assignee}` })
  }

  if (filterState.value.deadline) {
    const deadline = props.filterOptions.deadlines.find((item) => item.value === filterState.value.deadline)
    chips.push({ key: 'deadline', label: `Tenggat: ${deadline?.label || filterState.value.deadline}` })
  }

  if (filterState.value.budget) {
    const budget = props.filterOptions.budgets.find((item) => item.value === filterState.value.budget)
    chips.push({ key: 'budget', label: `Budget: ${budget?.label || filterState.value.budget}` })
  }

  return chips
})

watch(
  () => props.projects.items,
  (items) => {
    localProjects.value = cloneProjects(items || [])
  },
)

watch(
  () => props.filters,
  (filters) => {
    filterState.value = buildFilterState(filters || {})
  },
  { deep: true },
)

function applyFilters() {
  router.get(projectsBaseUrl, compactFilters(filterState.value), {
    preserveState: true,
    preserveScroll: true,
  })
}

function resetFilters() {
  filterState.value = buildFilterState()

  router.get(projectsBaseUrl, {}, {
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

function openProject(projectId) {
  if (draggingProjectId.value) {
    return
  }

  router.get(`${projectsBaseUrl}/${encodeURIComponent(projectId)}`)
}

function openProjectModal() {
  editingProjectId.value = null
  resetProjectForm()
  showProjectModal.value = true
}

function openEditProjectModal(project) {
  editingProjectId.value = project.id
  projectForm.reset()
  projectForm.clearErrors()
  projectForm.client_id = project.client?.id || ''
  projectForm.brand = project.brand || 'Kantor Digital'
  projectForm.template_id = project.template?.id || ''
  projectForm.name = project.name || ''
  projectForm.description = project.description || ''
  projectForm.status = project.status || 'planning'
  projectForm.start_date = project.start_date || ''
  projectForm.end_date = project.end_date || ''
  projectForm.budget = project.budget || ''
  projectForm.actual_cost = project.actual_cost || ''
  projectForm.tags = project.tags || []
  projectForm.members = project.members.length
    ? project.members.map((member) => ({
        user_id: member.id || '',
        role: member.role || '',
      }))
    : [emptyMemberRow()]
  projectTagInput.value = (project.tags || []).join(', ')
  showProjectModal.value = true
}

function closeProjectModal() {
  showProjectModal.value = false
  editingProjectId.value = null
  resetProjectForm()
}

function submitProject() {
  projectForm.clearErrors()
  projectForm.tags = parseTags(projectTagInput.value)
  projectForm.members = sanitizeMembers(projectForm.members)

  const options = {
    preserveScroll: true,
    onSuccess: () => {
      closeProjectModal()
    },
  }

  if (isEditingProject.value) {
    projectForm.patch(`${projectsBaseUrl}/${encodeURIComponent(editingProjectId.value)}`, options)
    return
  }

  projectForm.post(projectsBaseUrl, options)
}

function deleteProject(projectId) {
  if (!confirm('Hapus project ini? Semua data relasi project di menu ini bisa ikut terhapus.')) {
    return
  }

  router.delete(`${projectsBaseUrl}/${encodeURIComponent(projectId)}`, {
    preserveScroll: true,
  })
}

function handleDragStart(event, projectId) {
  if (movingProjectId.value) {
    return
  }

  if (event?.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move'
    event.dataTransfer.setData('text/plain', projectId)
  }

  draggingProjectId.value = projectId
}

function handleDragEnd() {
  draggingProjectId.value = null
  dragOverColumn.value = null
}

function handleColumnDragOver(columnId) {
  if (!draggingProjectId.value || movingProjectId.value) {
    return
  }

  dragOverColumn.value = columnId
}

function handleColumnDragLeave(columnId) {
  if (dragOverColumn.value === columnId) {
    dragOverColumn.value = null
  }
}

function handleDrop(nextStatus) {
  const projectId = draggingProjectId.value

  if (!projectId || movingProjectId.value) {
    return
  }

  const project = localProjects.value.find((item) => item.id === projectId)

  if (!project) {
    handleDragEnd()
    return
  }

  const previousStatus = project.status

  if (previousStatus === nextStatus) {
    handleDragEnd()
    return
  }

  movingProjectId.value = projectId
  updateProjectStatusLocally(projectId, nextStatus)

  router.patch(
    `${projectsBaseUrl}/${encodeURIComponent(projectId)}/status`,
    { status: nextStatus },
    {
      preserveScroll: true,
      preserveState: true,
      onError: () => {
        updateProjectStatusLocally(projectId, previousStatus)
      },
      onFinish: () => {
        movingProjectId.value = null
        handleDragEnd()
      },
    },
  )
}

function updateProjectStatusLocally(projectId, status) {
  localProjects.value = localProjects.value.map((project) => {
    if (project.id !== projectId) {
      return project
    }

    return {
      ...project,
      status,
      status_label: statusLabel(status),
      timeline_state: status === 'completed' ? 'completed' : project.timeline_state,
    }
  })
}

function dragColumnClass(columnId) {
  if (dragOverColumn.value !== columnId) {
    return ''
  }

  return 'border-amber-300 bg-amber-50/70 shadow-[inset_0_0_0_1px_rgba(245,158,11,0.12)]'
}

function openTemplateModal(template = null) {
  editingTemplateId.value = template?.id || null
  templateForm.reset()
  templateForm.clearErrors()
  templateForm.name = template?.name || ''
  templateForm.description = template?.description || ''
  templateForm.default_tasks_text = template?.default_tasks_text || ''
  showTemplateModal.value = true
}

function closeTemplateModal() {
  showTemplateModal.value = false
  editingTemplateId.value = null
  templateForm.reset()
  templateForm.clearErrors()
}

function submitTemplate() {
  const options = {
    preserveScroll: true,
    onSuccess: () => {
      closeTemplateModal()
    },
  }

  if (isEditingTemplate.value) {
    templateForm.patch(`${templatesBaseUrl}/${encodeURIComponent(editingTemplateId.value)}`, options)
    return
  }

  templateForm.post(templatesBaseUrl, options)
}

function deleteTemplate(templateId) {
  if (!confirm('Hapus template ini?')) {
    return
  }

  router.delete(`${templatesBaseUrl}/${encodeURIComponent(templateId)}`, {
    preserveScroll: true,
  })
}

function addMemberRow() {
  projectForm.members.push(emptyMemberRow())
}

function removeMemberRow(index) {
  if (projectForm.members.length === 1) {
    projectForm.members = [emptyMemberRow()]
    return
  }

  projectForm.members.splice(index, 1)
}

function resetProjectForm() {
  projectForm.reset()
  projectForm.clearErrors()
  projectForm.client_id = ''
  projectForm.brand = 'Kantor Digital'
  projectForm.template_id = ''
  projectForm.status = 'planning'
  projectForm.budget = ''
  projectForm.actual_cost = ''
  projectForm.members = [emptyMemberRow()]
  projectTagInput.value = ''
}

function buildFilterState(filters = {}) {
  return {
    search: filters.search ?? '',
    client: filters.client ?? '',
    status: filters.status ?? '',
    assignee: filters.assignee ?? '',
    deadline: filters.deadline ?? '',
    budget: filters.budget ?? '',
  }
}

function emptyMemberRow() {
  return {
    user_id: '',
    role: '',
  }
}

function sanitizeMembers(members) {
  return members
    .map((member) => ({
      user_id: member.user_id || '',
      role: member.role || '',
    }))
    .filter((member) => member.user_id)
}

function parseTags(input) {
  return input
    .split(',')
    .map((tag) => tag.trim())
    .filter(Boolean)
}

function compactFilters(filters) {
  return Object.fromEntries(
    Object.entries(filters).filter(([, value]) => value !== '' && value !== null && value !== undefined),
  )
}

function cloneProjects(projects) {
  return projects.map((project) => ({
    ...project,
    tags: Array.isArray(project.tags) ? [...project.tags] : [],
    members: Array.isArray(project.members) ? project.members.map((member) => ({ ...member })) : [],
    client: project.client ? { ...project.client } : null,
    template: project.template ? { ...project.template } : null,
    counts: project.counts ? { ...project.counts } : {},
  }))
}

function statusLabel(status) {
  const map = {
    planning: 'Perencanaan',
    active: 'Aktif',
    on_hold: 'Ditahan',
    completed: 'Selesai',
  }

  return map[status] || status
}

function statusClass(status) {
  const map = {
    planning: 'bg-slate-100 text-slate-700',
    active: 'bg-emerald-100 text-emerald-700',
    on_hold: 'bg-amber-100 text-amber-700',
    completed: 'bg-stone-200 text-stone-700',
  }

  return map[status] || 'bg-stone-100 text-stone-600'
}

function progressBarClass(progress) {
  if (progress >= 100) return 'bg-stone-950'
  if (progress >= 70) return 'bg-emerald-500'
  if (progress >= 35) return 'bg-amber-500'
  return 'bg-slate-400'
}

function timelineTextClass(state) {
  const map = {
    overdue: 'text-rose-600',
    today: 'text-amber-600',
    completed: 'text-emerald-600',
    unscheduled: 'text-stone-500',
    scheduled: 'text-stone-500',
  }

  return map[state] || 'text-stone-500'
}

function timelineCaption(state) {
  const map = {
    overdue: 'Butuh recovery plan',
    today: 'Deadline hari ini',
    completed: 'Sudah selesai',
    unscheduled: 'Belum ada deadline',
    scheduled: 'On track',
  }

  return map[state] || 'Timeline update'
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
