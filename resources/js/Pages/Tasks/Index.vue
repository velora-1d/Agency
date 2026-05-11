<template>
  <WorkspaceLayout
    title="Tasks"
    subtitle="Kelola eksekusi task lintas project dengan nested task, dependency, time tracking, recurring rule, comments, dan empat mode tampilan kerja."
  >
    <template #actions>
      <div class="flex flex-wrap items-center gap-3">
        <button
          type="button"
          @click="openTemplateModal()"
          class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-stone-300 hover:text-stone-950"
        >
          <LayoutTemplate class="h-4 w-4" />
          <span>Task Templates</span>
        </button>
        <button
          type="button"
          @click="openTaskModal()"
          class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
        >
          <Plus class="h-4 w-4" />
          <span>Create Task</span>
        </button>
      </div>
    </template>

    <div class="space-y-6">
      <section class="grid gap-4 xl:grid-cols-[1.3fr_0.7fr]">
        <article class="relative overflow-hidden rounded-[2rem] bg-stone-950 p-6 text-white shadow-[0_28px_90px_rgba(28,25,23,0.18)]">
          <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(251,191,36,0.28),transparent_28%),radial-gradient(circle_at_bottom_left,rgba(255,255,255,0.1),transparent_35%)]"></div>
          <div class="relative">
            <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-amber-200/75">Menu 9 / Tasks</p>
            <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
              <div class="max-w-2xl">
                <h2 class="text-3xl font-semibold tracking-[-0.05em] text-white">Workspace execution board yang lebih rapi, detail, dan enak dipakai harian.</h2>
                <p class="mt-3 max-w-xl text-sm leading-6 text-stone-300">
                  Semua mode kerja task sekarang kebaca lebih jelas: list untuk kontrol detail, kanban untuk flow, calendar untuk deadline, dan gantt untuk sequencing.
                </p>
              </div>
              <div class="rounded-[1.4rem] border border-white/10 bg-white/10 px-4 py-3 backdrop-blur">
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-stone-300">Delivery posture</p>
                <p class="mt-2 text-3xl font-semibold tracking-[-0.05em] text-white">{{ completionRate }}%</p>
                <p class="mt-1 text-sm text-stone-300">task selesai dari hasil filter aktif</p>
              </div>
            </div>

            <div class="mt-6 grid gap-3 md:grid-cols-3">
              <div class="rounded-[1.4rem] border border-white/10 bg-white/10 p-4 backdrop-blur">
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-stone-300">Open Queue</p>
                <p class="mt-2 text-2xl font-semibold text-white">{{ openQueueCount }}</p>
                <p class="mt-1 text-sm text-stone-300">task belum selesai di workspace ini</p>
              </div>
              <div class="rounded-[1.4rem] border border-white/10 bg-white/10 p-4 backdrop-blur">
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-stone-300">Due Soon</p>
                <p class="mt-2 text-2xl font-semibold text-white">{{ dueSoonCount }}</p>
                <p class="mt-1 text-sm text-stone-300">deadline dalam 7 hari ke depan</p>
              </div>
              <div class="rounded-[1.4rem] border border-white/10 bg-white/10 p-4 backdrop-blur">
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-stone-300">Discussion Traffic</p>
                <p class="mt-2 text-2xl font-semibold text-white">{{ commentTrafficCount }}</p>
                <p class="mt-1 text-sm text-stone-300">komentar dan mention tercatat</p>
              </div>
            </div>
          </div>
        </article>

        <article class="rounded-[2rem] border border-stone-200 bg-[linear-gradient(180deg,#fffdf8_0%,#ffffff_100%)] p-6 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Operational posture</p>
          <div class="mt-5 space-y-4">
            <div class="rounded-[1.4rem] border border-stone-200 bg-white p-4">
              <p class="text-sm font-semibold text-stone-950">View aktif</p>
              <p class="mt-2 text-sm leading-6 text-stone-600">{{ currentViewDescription }}</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
              <div class="rounded-[1.4rem] border border-stone-200 bg-white p-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Filtered tasks</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ filteredTaskCount }}</p>
              </div>
              <div class="rounded-[1.4rem] border border-stone-200 bg-white p-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Templates</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ taskTemplates.length }}</p>
              </div>
            </div>
            <div class="rounded-[1.4rem] border border-dashed border-stone-300 bg-stone-50 px-4 py-4 text-sm leading-6 text-stone-600">
              Nested task, dependency, recurring rule, comment, dan time log sudah dipusatkan di satu halaman supaya kontrol eksekusi project lebih cepat.
            </div>
          </div>
        </article>
      </section>

      <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Total Tasks</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ taskSummary.total_tasks }}</p>
          <p class="mt-2 text-sm text-stone-500">Semua task dan subtask pada workspace ini.</p>
        </article>
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">To Do</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ taskSummary.todo_tasks }}</p>
          <p class="mt-2 text-sm text-stone-500">Task belum mulai dikerjakan.</p>
        </article>
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">In Progress</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ taskSummary.in_progress_tasks }}</p>
          <p class="mt-2 text-sm text-stone-500">Task yang sedang aktif dikerjakan.</p>
        </article>
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Review</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ taskSummary.review_tasks }}</p>
          <p class="mt-2 text-sm text-stone-500">Task menunggu pengecekan atau approval.</p>
        </article>
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Done</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ taskSummary.done_tasks }}</p>
          <p class="mt-2 text-sm text-stone-500">Task selesai dan tercatat di progress project.</p>
        </article>
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Tracked Hours</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ taskSummary.tracked_hours }}</p>
          <p class="mt-2 text-sm text-stone-500">{{ taskSummary.overdue_tasks }} overdue tasks</p>
        </article>
      </section>

      <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-4">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Task Filters</p>
            <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Cari bottleneck, assignee, dan prioritas tanpa pindah halaman.</h2>
          </div>
          <div class="rounded-[1.3rem] border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-600">
            <span class="font-semibold text-stone-950">{{ filteredTaskCount }}</span> task tampil dengan
            <span class="font-semibold text-stone-950"> {{ activeFilterChips.length }}</span> filter aktif
          </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
          <label class="space-y-2 text-sm xl:col-span-2">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Search</span>
            <input
              v-model="filterState.search"
              type="text"
              placeholder="Cari task, project, atau konteks kerja"
              class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
            />
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Project</span>
            <select v-model="filterState.project" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">All Projects</option>
              <option v-for="project in filterOptions.projects" :key="project.id" :value="project.id">{{ project.name }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
            <select v-model="filterState.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">All Status</option>
              <option v-for="status in filterOptions.statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
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
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Priority</span>
            <select v-model="filterState.priority" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">All Priorities</option>
              <option v-for="priority in filterOptions.priorities" :key="priority.value" :value="priority.value">{{ priority.label }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Recurring</span>
            <select v-model="filterState.recurring" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">All</option>
              <option value="yes">Recurring</option>
              <option value="no">One-time</option>
            </select>
          </label>
        </div>

        <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
          <div class="flex flex-wrap items-center gap-3">
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

          <div class="inline-flex rounded-full border border-stone-200 bg-stone-50 p-1">
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

        <div v-if="activeFilterChips.length" class="mt-5 flex flex-wrap gap-2">
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
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Task View</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ currentViewTitle }}</h2>
              <p class="mt-2 text-sm text-stone-500">{{ currentViewDescription }}</p>
            </div>
            <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">
              {{ taskItems.length }} tasks
            </span>
          </div>

          <div v-if="viewMode === 'list'" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200 text-sm">
              <thead class="bg-stone-50 text-left text-[11px] font-bold uppercase tracking-[0.22em] text-stone-400">
                <tr>
                  <th class="px-5 py-4">Task</th>
                  <th class="px-5 py-4">Project</th>
                  <th class="px-5 py-4">Assignee</th>
                  <th class="px-5 py-4">Due Date</th>
                  <th class="px-5 py-4">Progress Signals</th>
                  <th class="px-5 py-4"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-stone-100 bg-white">
                <template v-for="task in hierarchicalTasks" :key="task.id">
                  <tr class="transition-colors hover:bg-stone-50/70">
                    <td class="px-5 py-4 align-top">
                      <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                          <button type="button" @click="openTaskDetail(task.id)" class="text-left text-sm font-semibold text-stone-950 transition hover:text-amber-700">
                            {{ task.title }}
                          </button>
                          <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="taskStatusClass(task.status)">
                            {{ task.status_label }}
                          </span>
                          <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="priorityClass(task.priority)">
                            {{ task.priority_label }}
                          </span>
                          <span v-if="task.is_recurring" class="rounded-full bg-violet-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-violet-700">
                            {{ task.recurrence_rule }}
                          </span>
                        </div>
                        <p class="line-clamp-2 text-sm text-stone-500">{{ task.description || 'Belum ada deskripsi task.' }}</p>
                        <div class="flex flex-wrap gap-2">
                          <span
                            v-for="tag in task.tags"
                            :key="`${task.id}-${tag}`"
                            class="rounded-full bg-stone-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-stone-500"
                          >
                            {{ tag }}
                          </span>
                        </div>
                      </div>
                    </td>
                    <td class="px-5 py-4 align-top text-stone-700">
                      <p>{{ task.project?.name || 'No project' }}</p>
                      <p v-if="task.parent_task" class="mt-2 text-xs text-stone-500">Subtask dari {{ task.parent_task.title }}</p>
                    </td>
                    <td class="px-5 py-4 align-top text-stone-700">
                      <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-stone-100 text-xs font-bold uppercase tracking-[0.16em] text-stone-600">
                          {{ task.assignee?.initials || 'NA' }}
                        </div>
                        <div>
                          <p>{{ task.assignee?.name || 'Unassigned' }}</p>
                          <p class="mt-1 text-xs text-stone-500">{{ task.template?.title || 'Manual task' }}</p>
                        </div>
                      </div>
                    </td>
                    <td class="px-5 py-4 align-top">
                      <p class="text-stone-700">{{ task.due_date_label || 'No due date' }}</p>
                      <p class="mt-2 text-xs" :class="dueToneClass(task)">{{ dueTone(task) }}</p>
                    </td>
                    <td class="px-5 py-4 align-top text-stone-700">
                      <p>{{ task.counts.subtasks }} subtasks / {{ task.counts.dependencies }} dependencies</p>
                      <p class="mt-2 text-xs text-stone-500">{{ trackedHoursLabel(task) }} / {{ task.counts.comments }} comments</p>
                      <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-stone-100">
                        <div class="h-full rounded-full bg-stone-900" :style="{ width: taskEffortWidth(task) }"></div>
                      </div>
                      <p class="mt-2 text-xs text-stone-500">{{ taskEffortSummary(task) }}</p>
                    </td>
                    <td class="px-5 py-4 align-top text-right">
                      <div class="flex justify-end gap-2">
                        <button type="button" @click="openTaskModal(task)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                          <Pencil class="h-4 w-4" />
                        </button>
                        <button type="button" @click="openTaskDetail(task.id, 'discussion')" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                          <MessageSquare class="h-4 w-4" />
                        </button>
                        <button type="button" @click="deleteTask(task.id)" class="inline-flex items-center justify-center rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
                          <Trash2 class="h-4 w-4" />
                        </button>
                      </div>
                    </td>
                  </tr>

                  <tr
                    v-for="subtask in childTasksByParent[task.id] || []"
                    :key="subtask.id"
                    class="bg-stone-50/60 transition-colors hover:bg-stone-50"
                  >
                    <td class="px-5 py-4 align-top">
                      <div class="flex items-start gap-3 pl-6">
                        <span class="mt-2 h-px w-5 bg-stone-300"></span>
                        <div class="space-y-2">
                          <div class="flex flex-wrap items-center gap-2">
                            <button type="button" @click="openTaskDetail(subtask.id)" class="text-left text-sm font-semibold text-stone-900 transition hover:text-amber-700">
                              {{ subtask.title }}
                            </button>
                            <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="taskStatusClass(subtask.status)">
                              {{ subtask.status_label }}
                            </span>
                          </div>
                          <p class="text-sm text-stone-500">{{ subtask.description || 'Subtask tanpa deskripsi.' }}</p>
                        </div>
                      </div>
                    </td>
                    <td class="px-5 py-4 align-top text-stone-700">{{ subtask.project?.name || 'No project' }}</td>
                    <td class="px-5 py-4 align-top text-stone-700">{{ subtask.assignee?.name || 'Unassigned' }}</td>
                    <td class="px-5 py-4 align-top text-stone-700">{{ subtask.due_date_label || 'No due date' }}</td>
                    <td class="px-5 py-4 align-top text-stone-700">
                      <p>{{ subtask.counts.dependencies }} dependencies</p>
                    </td>
                    <td class="px-5 py-4 align-top text-right">
                      <div class="flex justify-end gap-2">
                        <button type="button" @click="openTaskModal(subtask)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                          <Pencil class="h-4 w-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                </template>

                <tr v-if="topLevelTasks.length === 0">
                  <td colspan="6" class="px-5 py-16 text-center text-sm text-stone-500">Belum ada task yang cocok dengan filter saat ini.</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else-if="viewMode === 'kanban'" class="grid gap-4 p-4 xl:grid-cols-4">
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
                v-if="dragOverColumn === column.id && draggingTaskId"
                class="mt-4 rounded-[1.2rem] border border-dashed border-amber-300 bg-amber-50 px-4 py-3 text-[11px] font-bold uppercase tracking-[0.18em] text-amber-700"
              >
                Drop here to move task
              </div>

              <div class="mt-4 space-y-3">
                <button
                  v-for="task in column.items"
                  :key="task.id"
                  type="button"
                  draggable="true"
                  @click="openTaskDetail(task.id)"
                  @dragstart="handleDragStart($event, task.id)"
                  @dragend="handleDragEnd"
                  class="w-full rounded-[1.4rem] border border-white bg-white p-4 text-left shadow-[0_12px_30px_rgba(28,25,23,0.05)] transition-all hover:-translate-y-1 hover:border-stone-200"
                  :class="{
                    'cursor-grabbing opacity-60': draggingTaskId === task.id,
                    'cursor-grab': draggingTaskId !== task.id,
                  }"
                >
                  <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                      <div class="flex items-center gap-2 text-stone-400">
                        <GripVertical class="h-4 w-4" />
                        <span class="rounded-full bg-stone-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-stone-500">
                          {{ task.project?.name || 'No project' }}
                        </span>
                      </div>
                      <h3 class="mt-3 text-sm font-semibold text-stone-950">{{ task.title }}</h3>
                      <p class="mt-2 text-xs text-stone-500">{{ task.assignee?.name || 'Unassigned' }}</p>
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="priorityClass(task.priority)">
                      {{ task.priority_label }}
                    </span>
                  </div>
                  <p class="mt-3 line-clamp-2 text-sm text-stone-600">{{ task.description || 'Belum ada deskripsi task.' }}</p>
                  <div class="mt-4 flex flex-wrap gap-2">
                    <span class="rounded-full bg-stone-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-stone-500">
                      {{ task.counts.subtasks }} subtasks
                    </span>
                    <span class="rounded-full bg-stone-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-stone-500">
                      {{ task.counts.dependencies }} dependencies
                    </span>
                    <span class="rounded-full bg-stone-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-stone-500">
                      {{ task.counts.comments }} comments
                    </span>
                  </div>
                  <div class="mt-4 flex items-center justify-between text-xs text-stone-500">
                    <span>{{ trackedHoursLabel(task) }}</span>
                    <span :class="dueToneClass(task)">{{ task.due_date_label || 'No due date' }}</span>
                  </div>
                </button>

                <div
                  v-if="column.items.length === 0"
                  class="rounded-[1.4rem] border border-dashed border-stone-200 bg-white/70 px-4 py-8 text-center text-sm text-stone-500"
                >
                  Belum ada task di kolom ini.
                </div>
              </div>
            </article>
          </div>

          <div v-else-if="viewMode === 'calendar'" class="p-4">
            <div class="mb-4 flex items-center justify-between gap-3">
              <button type="button" @click="currentMonth = currentMonth.subtract(1, 'month')" class="rounded-full border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-600 transition hover:bg-stone-50">
                Prev
              </button>
              <div class="text-center">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Task Calendar</p>
                <h3 class="mt-1 text-lg font-semibold text-stone-950">{{ currentMonth.format('MMMM YYYY') }}</h3>
              </div>
              <button type="button" @click="currentMonth = currentMonth.add(1, 'month')" class="rounded-full border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-600 transition hover:bg-stone-50">
                Next
              </button>
            </div>

            <div class="grid grid-cols-7 gap-3 text-center text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">
              <div v-for="day in weekdays" :key="day">{{ day }}</div>
            </div>

            <div class="mt-3 grid grid-cols-7 gap-3">
              <article
                v-for="day in calendarDays"
                :key="day.dateKey"
                class="min-h-[150px] rounded-[1.4rem] border p-3"
                :class="day.isCurrentMonth ? 'border-stone-200 bg-white' : 'border-stone-100 bg-stone-50/70'"
              >
                <div class="flex items-center justify-between">
                  <span class="text-sm font-semibold" :class="day.isToday ? 'text-amber-700' : 'text-stone-900'">{{ day.label }}</span>
                  <span v-if="day.tasks.length" class="rounded-full bg-stone-100 px-2 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-stone-500">
                    {{ day.tasks.length }}
                  </span>
                </div>

                <div class="mt-3 space-y-2">
                  <button
                    v-for="task in day.tasks"
                    :key="task.id"
                    type="button"
                    @click="openTaskDetail(task.id)"
                    class="w-full rounded-[1rem] border border-stone-200 px-3 py-2 text-left text-xs transition hover:border-stone-300"
                    :class="task.is_overdue ? 'bg-rose-50 text-rose-700' : 'bg-stone-50 text-stone-700'"
                  >
                    <p class="font-semibold">{{ task.title }}</p>
                    <p class="mt-1">{{ task.project?.name || 'No project' }}</p>
                  </button>
                </div>
              </article>
            </div>
          </div>

          <div v-else class="p-4">
            <div class="mb-4 flex items-center justify-between gap-3">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Gantt View</p>
                <h3 class="mt-1 text-lg font-semibold text-stone-950">{{ currentMonth.format('MMMM YYYY') }}</h3>
              </div>
              <div class="flex gap-2">
                <button type="button" @click="currentMonth = currentMonth.subtract(1, 'month')" class="rounded-full border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-600 transition hover:bg-stone-50">
                  Prev
                </button>
                <button type="button" @click="currentMonth = currentMonth.add(1, 'month')" class="rounded-full border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-600 transition hover:bg-stone-50">
                  Next
                </button>
              </div>
            </div>

            <div class="overflow-x-auto">
              <div class="min-w-[960px]">
                <div class="grid grid-cols-[260px_repeat(31,minmax(0,1fr))] gap-2 border-b border-stone-200 pb-3 text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">
                  <div>Task</div>
                  <div v-for="day in ganttDays" :key="day">{{ day }}</div>
                </div>

                <div class="space-y-3 pt-3">
                  <div
                    v-for="task in ganttTasks"
                    :key="task.id"
                    class="grid grid-cols-[260px_repeat(31,minmax(0,1fr))] items-center gap-2"
                  >
                    <button type="button" @click="openTaskDetail(task.id)" class="rounded-[1rem] border border-stone-200 bg-stone-50 px-4 py-3 text-left transition hover:border-stone-300">
                      <p class="text-sm font-semibold text-stone-950">{{ task.title }}</p>
                      <p class="mt-1 text-xs text-stone-500">{{ task.project?.name || 'No project' }}</p>
                    </button>

                    <div class="relative col-span-31 h-12 rounded-[1rem] bg-stone-50">
                      <div
                        v-if="task.ganttSpan"
                        class="absolute top-2 h-8 rounded-full px-3 py-2 text-xs font-semibold text-white"
                        :class="ganttBarClass(task.status)"
                        :style="task.ganttStyle"
                      >
                        {{ task.status_label }}
                      </div>
                      <div v-else class="absolute inset-0 flex items-center justify-center text-xs text-stone-400">
                        No due window
                      </div>
                    </div>
                  </div>

                  <div v-if="ganttTasks.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-14 text-center text-sm text-stone-500">
                    Belum ada task dengan data yang cocok untuk gantt bulan ini.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </article>

        <aside class="space-y-4">
          <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-4">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Task Templates</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Reusable execution blocks</h2>
              </div>
              <button type="button" @click="openTemplateModal()" class="inline-flex items-center gap-2 rounded-full border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-700 transition-all hover:border-stone-300 hover:text-stone-950">
                <Plus class="h-3.5 w-3.5" />
                <span>New</span>
              </button>
            </div>

            <div class="mt-5 space-y-3">
              <article
                v-for="template in taskTemplates"
                :key="template.id"
                class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4"
              >
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <h3 class="text-sm font-semibold text-stone-950">{{ template.title }}</h3>
                    <p class="mt-2 text-sm text-stone-600">{{ template.description || 'Template tanpa deskripsi.' }}</p>
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
              </article>

              <div v-if="taskTemplates.length === 0" class="rounded-[1.5rem] border border-dashed border-stone-200 bg-stone-50 px-4 py-10 text-center text-sm text-stone-500">
                Belum ada template task. Simpan task berulang sebagai blok kerja reusable.
              </div>
            </div>
          </section>

          <section class="rounded-[2rem] border border-stone-200 bg-stone-950 p-5 text-white shadow-[0_20px_60px_rgba(28,25,23,0.14)]">
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-amber-200/70">Task Signals</p>
            <div class="mt-5 space-y-4">
              <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-4">
                <p class="text-sm font-semibold text-white">Recurring schedule</p>
                <p class="mt-2 text-sm text-stone-300">
                  {{ recurringTasksCount }} task sedang memakai recurrence rule harian, mingguan, atau bulanan.
                </p>
              </div>
              <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-4">
                <p class="text-sm font-semibold text-white">Dependency web</p>
                <p class="mt-2 text-sm text-stone-300">
                  {{ dependentTasksCount }} task memiliki dependency aktif, jadi urutan pengerjaan lebih terjaga.
                </p>
              </div>
              <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-4">
                <p class="text-sm font-semibold text-white">Comment traffic</p>
                <p class="mt-2 text-sm text-stone-300">
                  {{ commentTrafficCount }} komentar task sudah tercatat, termasuk mention manual lewat format `@nama`.
                </p>
              </div>
            </div>
          </section>
        </aside>
      </section>
    </div>

    <Transition name="modal">
      <div v-if="showTaskModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="max-h-[92vh] w-full max-w-5xl overflow-y-auto rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Task Form</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditingTask ? 'Edit Task' : 'Create Task' }}</h3>
            </div>
            <button type="button" @click="closeTaskModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
              <X class="h-5 w-5" />
            </button>
          </div>

          <form class="mt-6 space-y-5" @submit.prevent="submitTask">
            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Project</span>
                <select v-model="taskForm.project_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Select project</option>
                  <option v-for="project in filterOptions.projects" :key="project.id" :value="project.id">{{ project.name }}</option>
                </select>
                <p v-if="taskForm.errors.project_id" class="text-xs text-rose-500">{{ taskForm.errors.project_id }}</p>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Template</span>
                <select v-model="taskForm.template_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">No Template</option>
                  <option v-for="template in taskTemplates" :key="template.id" :value="template.id">{{ template.title }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm md:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Title</span>
                <input v-model="taskForm.title" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                <p v-if="taskForm.errors.title" class="text-xs text-rose-500">{{ taskForm.errors.title }}</p>
              </label>

              <label class="space-y-2 text-sm md:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Description</span>
                <textarea v-model="taskForm.description" rows="4" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Parent Task</span>
                <select v-model="taskForm.parent_task_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Top-level task</option>
                  <option v-for="task in filterOptions.parentTasks" :key="task.id" :value="task.id">{{ task.title }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Assignee</span>
                <select v-model="taskForm.assigned_to" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Unassigned</option>
                  <option v-for="assignee in filterOptions.assignees" :key="assignee.id" :value="assignee.id">{{ assignee.name }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
                <select v-model="taskForm.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option v-for="status in filterOptions.statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Priority</span>
                <select v-model="taskForm.priority" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option v-for="priority in filterOptions.priorities" :key="priority.value" :value="priority.value">{{ priority.label }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tags</span>
                <input v-model="taskTagsInput" type="text" placeholder="frontend, urgent, qa" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Due Date</span>
                <input v-model="taskForm.due_date" type="datetime-local" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Estimated Hours</span>
                <input v-model="taskForm.estimated_hours" type="number" min="0" step="0.25" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Actual Hours</span>
                <input v-model="taskForm.actual_hours" type="number" min="0" step="0.25" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm md:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Dependencies</span>
                <select v-model="taskForm.dependency_ids" multiple class="min-h-[140px] w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option v-for="task in dependencyOptions" :key="task.id" :value="task.id">{{ task.title }}</option>
                </select>
                <p class="text-xs text-stone-500">Pilih task yang harus selesai lebih dulu.</p>
              </label>

              <label class="space-y-2 text-sm md:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Recurring</span>
                <div class="grid gap-3 md:grid-cols-[auto_1fr]">
                  <label class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700">
                    <input v-model="taskForm.is_recurring" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-400" />
                    <span>Recurring task</span>
                  </label>
                  <select v-model="taskForm.recurrence_rule" :disabled="!taskForm.is_recurring" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all disabled:cursor-not-allowed disabled:opacity-60 focus:border-stone-400 focus:bg-white">
                    <option value="">Select recurrence</option>
                    <option v-for="rule in filterOptions.recurrenceRules" :key="rule.value" :value="rule.value">{{ rule.label }}</option>
                  </select>
                </div>
              </label>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3">
              <button type="button" @click="closeTaskModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900">
                Cancel
              </button>
              <button type="submit" :disabled="taskForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition-all hover:bg-stone-800 disabled:cursor-not-allowed disabled:opacity-60">
                {{ isEditingTask ? (taskForm.processing ? 'Saving...' : 'Save Task') : (taskForm.processing ? 'Creating...' : 'Create Task') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showTemplateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="w-full max-w-2xl rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Task Template</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditingTemplate ? 'Edit Template' : 'Create Template' }}</h3>
            </div>
            <button type="button" @click="closeTemplateModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
              <X class="h-5 w-5" />
            </button>
          </div>

          <form class="mt-6 space-y-5" @submit.prevent="submitTemplate">
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Title</span>
              <input v-model="templateForm.title" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              <p v-if="templateForm.errors.title" class="text-xs text-rose-500">{{ templateForm.errors.title }}</p>
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Description</span>
              <textarea v-model="templateForm.description" rows="4" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
            </label>

            <div class="flex flex-wrap items-center justify-end gap-3">
              <button type="button" @click="closeTemplateModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900">
                Cancel
              </button>
              <button type="submit" :disabled="templateForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition-all hover:bg-stone-800 disabled:cursor-not-allowed disabled:opacity-60">
                {{ isEditingTemplate ? (templateForm.processing ? 'Saving...' : 'Save Template') : (templateForm.processing ? 'Creating...' : 'Create Template') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showDetailModal && selectedTask" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="max-h-[92vh] w-full max-w-6xl overflow-y-auto rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="relative overflow-hidden rounded-[1.8rem] bg-stone-950 p-6 text-white">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(251,191,36,0.28),transparent_24%),radial-gradient(circle_at_bottom_left,rgba(255,255,255,0.12),transparent_35%)]"></div>
            <div class="relative">
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="max-w-3xl">
                  <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-amber-200/70">Task Detail</p>
                  <h3 class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-white">{{ selectedTask.title }}</h3>
                  <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]" :class="taskStatusClass(selectedTask.status)">
                      {{ selectedTask.status_label }}
                    </span>
                    <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]" :class="priorityClass(selectedTask.priority)">
                      {{ selectedTask.priority_label }}
                    </span>
                    <span v-if="selectedTask.is_recurring" class="rounded-full bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-amber-100">
                      {{ selectedTask.recurrence_rule }}
                    </span>
                  </div>
                  <div class="mt-4 flex flex-wrap gap-3 text-sm text-stone-300">
                    <span>{{ selectedTask.project?.name || 'No project' }}</span>
                    <span>{{ selectedTask.assignee?.name || 'Unassigned' }}</span>
                    <span :class="dueToneClass(selectedTask)">{{ dueTone(selectedTask) }}</span>
                  </div>
                </div>

                <div class="flex items-center gap-2">
                  <button type="button" @click="editSelectedTask" class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/15">
                    <Pencil class="h-4 w-4" />
                    <span>Edit Task</span>
                  </button>
                  <button type="button" @click="closeDetailModal" class="rounded-full border border-white/15 bg-white/10 p-2 text-stone-200 transition hover:bg-white/15 hover:text-white">
                    <X class="h-5 w-5" />
                  </button>
                </div>
              </div>

              <div class="mt-6 grid gap-3 md:grid-cols-4">
                <div class="rounded-[1.4rem] border border-white/10 bg-white/10 p-4 backdrop-blur">
                  <div class="flex items-center gap-2 text-amber-100">
                    <AlarmClock class="h-4 w-4" />
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em]">Due Signal</p>
                  </div>
                  <p class="mt-3 text-sm font-semibold text-white">{{ selectedTask.due_date_label || 'No due date' }}</p>
                  <p class="mt-1 text-sm text-stone-300">{{ dueTone(selectedTask) }}</p>
                </div>
                <div class="rounded-[1.4rem] border border-white/10 bg-white/10 p-4 backdrop-blur">
                  <div class="flex items-center gap-2 text-amber-100">
                    <Clock3 class="h-4 w-4" />
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em]">Time Footprint</p>
                  </div>
                  <p class="mt-3 text-sm font-semibold text-white">{{ taskEffortSummary(selectedTask) }}</p>
                  <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-white/10">
                    <div class="h-full rounded-full bg-white" :style="{ width: taskEffortWidth(selectedTask) }"></div>
                  </div>
                </div>
                <div class="rounded-[1.4rem] border border-white/10 bg-white/10 p-4 backdrop-blur">
                  <div class="flex items-center gap-2 text-amber-100">
                    <Layers3 class="h-4 w-4" />
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em]">Structure</p>
                  </div>
                  <p class="mt-3 text-sm font-semibold text-white">{{ selectedTask.subtasks.length }} subtasks</p>
                  <p class="mt-1 text-sm text-stone-300">{{ selectedTask.dependencies.length }} dependency aktif</p>
                </div>
                <div class="rounded-[1.4rem] border border-white/10 bg-white/10 p-4 backdrop-blur">
                  <div class="flex items-center gap-2 text-amber-100">
                    <Sparkles class="h-4 w-4" />
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em]">Discussion</p>
                  </div>
                  <p class="mt-3 text-sm font-semibold text-white">{{ selectedTask.comments.length }} comments</p>
                  <p class="mt-1 text-sm text-stone-300">{{ selectedTask.time_logs.length }} time logs sudah tercatat</p>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-6 flex flex-wrap items-center gap-2 rounded-full border border-stone-200 bg-stone-50 p-1">
            <button
              v-for="tab in detailTabs"
              :key="tab.id"
              type="button"
              @click="detailTab = tab.id"
              :class="[
                'rounded-full px-4 py-2 text-sm font-semibold transition-all',
                detailTab === tab.id ? 'bg-stone-950 text-white shadow-sm' : 'text-stone-600 hover:text-stone-950',
              ]"
            >
              {{ tab.label }}
            </button>
          </div>

          <div v-if="detailTab === 'overview'" class="mt-6 grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <div class="space-y-4">
              <section class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                <div class="flex items-center gap-2">
                  <Workflow class="h-4 w-4 text-stone-400" />
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Overview</p>
                </div>
                <p class="mt-4 text-sm leading-7 text-stone-600">
                  {{ selectedTask.description || 'Belum ada deskripsi task. Gunakan area ini untuk menjelaskan konteks, output, dan handoff task.' }}
                </p>

                <div class="mt-6 grid gap-3 md:grid-cols-2">
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <div class="flex items-center gap-2 text-stone-500">
                      <KanbanSquare class="h-4 w-4" />
                      <p class="text-[11px] font-bold uppercase tracking-[0.18em]">Project</p>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-stone-950">{{ selectedTask.project?.name || 'No project' }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <div class="flex items-center gap-2 text-stone-500">
                      <UserRound class="h-4 w-4" />
                      <p class="text-[11px] font-bold uppercase tracking-[0.18em]">Assignee</p>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-stone-950">{{ selectedTask.assignee?.name || 'Unassigned' }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <div class="flex items-center gap-2 text-stone-500">
                      <Layers3 class="h-4 w-4" />
                      <p class="text-[11px] font-bold uppercase tracking-[0.18em]">Parent Task</p>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-stone-950">{{ selectedTask.parent_task?.title || 'Top-level task' }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <div class="flex items-center gap-2 text-stone-500">
                      <LayoutTemplate class="h-4 w-4" />
                      <p class="text-[11px] font-bold uppercase tracking-[0.18em]">Template</p>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-stone-950">{{ selectedTask.template?.title || 'No template' }}</p>
                  </div>
                </div>
              </section>

              <section class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <div class="flex items-center gap-2">
                  <Repeat2 class="h-4 w-4 text-stone-400" />
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Signals</p>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                  <span
                    v-for="tag in selectedTask.tags"
                    :key="`detail-tag-${tag}`"
                    class="rounded-full bg-white px-3 py-2 text-[11px] font-bold uppercase tracking-[0.16em] text-stone-600 shadow-sm"
                  >
                    {{ tag }}
                  </span>
                  <span v-if="selectedTask.tags.length === 0" class="rounded-full border border-dashed border-stone-300 px-3 py-2 text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">
                    No tags
                  </span>
                </div>
                <div class="mt-5 grid gap-3 md:grid-cols-3">
                  <div class="rounded-[1.2rem] border border-stone-200 bg-white p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Recurring</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedTask.is_recurring ? selectedTask.recurrence_rule : 'One-time task' }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-white p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Estimate</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedTask.estimated_hours ?? 0 }}h</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-white p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Actual</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedTask.actual_hours ?? 0 }}h</p>
                  </div>
                </div>
              </section>
            </div>

            <div class="space-y-4">
              <section class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                <div class="flex items-center gap-2">
                  <GitBranch class="h-4 w-4 text-stone-400" />
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Dependencies</p>
                </div>
                <div class="mt-4 space-y-3">
                  <article v-for="dependency in selectedTask.dependencies" :key="dependency.id" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4 text-sm text-stone-700">
                    {{ dependency.title }}
                  </article>
                  <div v-if="selectedTask.dependencies.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-200 bg-stone-50 px-4 py-8 text-center text-sm text-stone-500">
                    Tidak ada dependency aktif.
                  </div>
                </div>
              </section>

              <section class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                <div class="flex items-center gap-2">
                  <CheckCircle2 class="h-4 w-4 text-stone-400" />
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Subtasks</p>
                </div>
                <div class="mt-4 space-y-3">
                  <article v-for="subtask in selectedTask.subtasks" :key="subtask.id" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <div class="flex items-center justify-between gap-3">
                      <p class="text-sm font-semibold text-stone-950">{{ subtask.title }}</p>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em]" :class="taskStatusClass(subtask.status)">
                        {{ statusLabel(subtask.status) }}
                      </span>
                    </div>
                  </article>
                  <div v-if="selectedTask.subtasks.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-200 bg-stone-50 px-4 py-8 text-center text-sm text-stone-500">
                    Belum ada subtask turunan.
                  </div>
                </div>
              </section>
            </div>
          </div>

          <div v-else-if="detailTab === 'discussion'" class="mt-6 grid gap-6 xl:grid-cols-[0.88fr_1.12fr]">
            <section class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
              <div class="flex items-center gap-2">
                <MessageSquare class="h-4 w-4 text-stone-400" />
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Comment Composer</p>
              </div>
              <p class="mt-3 text-sm leading-6 text-stone-500">Gunakan format `@nama` untuk mention manual, handoff, atau request review.</p>

              <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <div class="rounded-[1.2rem] border border-stone-200 bg-white p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Comments</p>
                  <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ selectedTask.comments.length }}</p>
                </div>
                <div class="rounded-[1.2rem] border border-stone-200 bg-white p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Mentions</p>
                  <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ selectedTaskMentionCount }}</p>
                </div>
              </div>

              <form class="mt-5 space-y-3" @submit.prevent="submitComment">
                <textarea v-model="commentForm.content" rows="6" placeholder="Contoh: Tolong cek final copy @rani sebelum review." class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400"></textarea>
                <div class="flex justify-end">
                  <button type="submit" :disabled="commentForm.processing" class="rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white transition-all hover:bg-stone-800 disabled:opacity-60">
                    {{ commentForm.processing ? 'Saving...' : 'Add Comment' }}
                  </button>
                </div>
              </form>
            </section>

            <section class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
              <div class="flex items-center gap-2">
                <Sparkles class="h-4 w-4 text-stone-400" />
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Discussion Log</p>
              </div>
              <div class="mt-5 space-y-3">
                <article v-for="comment in selectedTask.comments" :key="comment.id" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <div class="flex items-start justify-between gap-3">
                    <div>
                      <p class="text-sm font-semibold text-stone-950">{{ comment.user?.name || 'System' }}</p>
                      <p class="mt-2 whitespace-pre-wrap text-sm leading-6 text-stone-700">{{ comment.description }}</p>
                      <div v-if="commentMentions(comment).length" class="mt-3 flex flex-wrap gap-2">
                        <span
                          v-for="mention in commentMentions(comment)"
                          :key="`${comment.id}-${mention}`"
                          class="rounded-full bg-white px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-stone-500"
                        >
                          @{{ mention }}
                        </span>
                      </div>
                    </div>
                    <span class="text-xs text-stone-400">{{ comment.created_at || '-' }}</span>
                  </div>
                </article>
                <div v-if="selectedTask.comments.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-200 bg-stone-50 px-4 py-10 text-center text-sm text-stone-500">
                  Belum ada komentar task.
                </div>
              </div>
            </section>
          </div>

          <div v-else-if="detailTab === 'time'" class="mt-6 grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <section class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
              <div class="flex items-center gap-2">
                <Clock3 class="h-4 w-4 text-stone-400" />
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Time Tracking</p>
              </div>
              <div class="mt-5 grid gap-3 sm:grid-cols-3">
                <div class="rounded-[1.2rem] border border-stone-200 bg-white p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Estimate</p>
                  <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ selectedTask.estimated_hours ?? 0 }}h</p>
                </div>
                <div class="rounded-[1.2rem] border border-stone-200 bg-white p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Actual</p>
                  <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ selectedTask.actual_hours ?? 0 }}h</p>
                </div>
                <div class="rounded-[1.2rem] border border-stone-200 bg-white p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Logs</p>
                  <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ selectedTask.time_logs.length }}</p>
                </div>
              </div>

              <div class="mt-5 h-2.5 overflow-hidden rounded-full bg-white">
                <div class="h-full rounded-full bg-stone-950" :style="{ width: taskEffortWidth(selectedTask) }"></div>
              </div>
              <p class="mt-3 text-sm text-stone-500">{{ taskEffortSummary(selectedTask) }}</p>

              <form class="mt-6 grid gap-3 md:grid-cols-2" @submit.prevent="submitTimeLog">
                <label class="space-y-2 text-sm">
                  <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Started At</span>
                  <input v-model="timeLogForm.started_at" type="datetime-local" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400" />
                </label>
                <label class="space-y-2 text-sm">
                  <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Ended At</span>
                  <input v-model="timeLogForm.ended_at" type="datetime-local" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400" />
                </label>
                <label class="space-y-2 text-sm">
                  <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Hours</span>
                  <input v-model="timeLogForm.hours" type="number" min="0" step="0.25" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400" />
                </label>
                <label class="space-y-2 text-sm md:col-span-2">
                  <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Notes</span>
                  <textarea v-model="timeLogForm.notes" rows="3" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400"></textarea>
                </label>
                <div class="md:col-span-2 flex justify-end">
                  <button type="submit" :disabled="timeLogForm.processing" class="rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white transition-all hover:bg-stone-800 disabled:opacity-60">
                    {{ timeLogForm.processing ? 'Saving...' : 'Add Time Log' }}
                  </button>
                </div>
              </form>
            </section>

            <section class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
              <div class="flex items-center gap-2">
                <AlarmClock class="h-4 w-4 text-stone-400" />
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Log History</p>
              </div>
              <div class="mt-5 space-y-3">
                <article v-for="log in selectedTask.time_logs" :key="log.id" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <div class="flex items-start justify-between gap-3">
                    <div>
                      <p class="text-sm font-semibold text-stone-950">{{ log.hours ?? 0 }}h tracked</p>
                      <p class="mt-2 text-xs text-stone-500">{{ log.started_at_label || '-' }} -> {{ log.ended_at_label || '-' }}</p>
                    </div>
                    <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-stone-500">Time log</span>
                  </div>
                  <p class="mt-3 text-sm leading-6 text-stone-600">{{ log.notes || 'Tanpa catatan' }}</p>
                </article>
                <div v-if="selectedTask.time_logs.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-200 bg-stone-50 px-4 py-10 text-center text-sm text-stone-500">
                  Belum ada time log.
                </div>
              </div>
            </section>
          </div>

          <div v-else class="mt-6 grid gap-4 md:grid-cols-2">
            <section class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
              <div class="flex items-center gap-2">
                <Layers3 class="h-4 w-4 text-stone-400" />
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Parent Context</p>
              </div>
              <p class="mt-4 text-sm font-semibold text-stone-950">{{ selectedTask.parent_task?.title || 'Top-level task tanpa parent.' }}</p>
              <p class="mt-2 text-sm leading-6 text-stone-500">Gunakan parent task untuk membentuk nested task yang lebih mudah dikelola per milestone.</p>
            </section>

            <section class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
              <div class="flex items-center gap-2">
                <GitBranch class="h-4 w-4 text-stone-400" />
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Dependency Map</p>
              </div>
              <div class="mt-4 space-y-3">
                <article v-for="dependency in selectedTask.dependencies" :key="`structure-${dependency.id}`" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4 text-sm text-stone-700">
                  {{ dependency.title }}
                </article>
                <div v-if="selectedTask.dependencies.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-200 bg-stone-50 px-4 py-8 text-center text-sm text-stone-500">
                  Belum ada dependency.
                </div>
              </div>
            </section>

            <section class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
              <div class="flex items-center gap-2">
                <CheckCircle2 class="h-4 w-4 text-stone-400" />
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Subtask Breakdown</p>
              </div>
              <div class="mt-4 space-y-3">
                <article v-for="subtask in selectedTask.subtasks" :key="`structure-subtask-${subtask.id}`" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-stone-950">{{ subtask.title }}</p>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em]" :class="taskStatusClass(subtask.status)">
                      {{ statusLabel(subtask.status) }}
                    </span>
                  </div>
                </article>
                <div v-if="selectedTask.subtasks.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-200 bg-stone-50 px-4 py-8 text-center text-sm text-stone-500">
                  Belum ada subtask.
                </div>
              </div>
            </section>

            <section class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
              <div class="flex items-center gap-2">
                <Repeat2 class="h-4 w-4 text-stone-400" />
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Rules & Template</p>
              </div>
              <div class="mt-4 grid gap-3">
                <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Recurring Rule</p>
                  <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedTask.is_recurring ? selectedTask.recurrence_rule : 'One-time task' }}</p>
                </div>
                <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Template Source</p>
                  <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedTask.template?.title || 'Tidak memakai template' }}</p>
                </div>
              </div>
            </section>
          </div>
        </div>
      </div>
    </Transition>
  </WorkspaceLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import dayjs from 'dayjs'
import {
  AlarmClock,
  CalendarDays,
  CheckCircle2,
  Clock3,
  Filter,
  GanttChartSquare,
  GitBranch,
  GripVertical,
  KanbanSquare,
  LayoutList,
  LayoutTemplate,
  Layers3,
  MessageSquare,
  Pencil,
  Plus,
  Repeat2,
  RotateCcw,
  Sparkles,
  Trash2,
  UserRound,
  Workflow,
  X,
} from 'lucide-vue-next'
import WorkspaceLayout from '../../Layouts/WorkspaceLayout.vue'

const props = defineProps({
  workspace: { type: Object, required: true },
  tasks: { type: Object, required: true },
  taskTemplates: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  filterOptions: { type: Object, required: true },
})

const workspaceBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}`
const tasksBaseUrl = `${workspaceBaseUrl}/tasks`
const templatesBaseUrl = `${tasksBaseUrl}/templates`

const viewModes = [
  { id: 'list', label: 'List', icon: LayoutList },
  { id: 'kanban', label: 'Kanban', icon: KanbanSquare },
  { id: 'calendar', label: 'Calendar', icon: CalendarDays },
  { id: 'gantt', label: 'Gantt', icon: GanttChartSquare },
]

const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
const localTasks = ref(cloneTasks(props.tasks.items || []))
const currentMonth = ref(dayjs().startOf('month'))

const filterState = ref(buildFilterState(props.filters))

const viewMode = ref('list')
const showTaskModal = ref(false)
const showTemplateModal = ref(false)
const showDetailModal = ref(false)
const editingTaskId = ref(null)
const editingTemplateId = ref(null)
const selectedTaskId = ref(null)
const detailTab = ref('overview')
const draggingTaskId = ref(null)
const dragOverColumn = ref(null)
const movingTaskId = ref(null)
const taskTagsInput = ref('')

const detailTabs = [
  { id: 'overview', label: 'Overview' },
  { id: 'discussion', label: 'Discussion' },
  { id: 'time', label: 'Time Log' },
  { id: 'structure', label: 'Structure' },
]

const taskForm = useForm({
  project_id: '',
  parent_task_id: '',
  assigned_to: '',
  title: '',
  description: '',
  status: 'todo',
  priority: 'medium',
  tags: [],
  due_date: '',
  estimated_hours: '',
  actual_hours: '',
  is_recurring: false,
  recurrence_rule: '',
  template_id: '',
  sop_note_id: '',
  dependency_ids: [],
})

const templateForm = useForm({
  title: '',
  description: '',
})

const timeLogForm = useForm({
  started_at: '',
  ended_at: '',
  hours: '',
  notes: '',
})

const commentForm = useForm({
  content: '',
})

const isEditingTask = computed(() => Boolean(editingTaskId.value))
const isEditingTemplate = computed(() => Boolean(editingTemplateId.value))
const taskItems = computed(() => localTasks.value)

const taskSummary = computed(() => ({
  ...props.tasks.summary,
  total_tasks: taskItems.value.length,
  todo_tasks: taskItems.value.filter((task) => task.status === 'todo').length,
  in_progress_tasks: taskItems.value.filter((task) => task.status === 'in_progress').length,
  review_tasks: taskItems.value.filter((task) => task.status === 'review').length,
  done_tasks: taskItems.value.filter((task) => task.status === 'done').length,
  overdue_tasks: taskItems.value.filter((task) => task.is_overdue).length,
  tracked_hours: taskItems.value.reduce((total, task) => total + Number(task.actual_hours || 0), 0).toFixed(1),
}))

const filteredTaskCount = computed(() => taskItems.value.length)
const openQueueCount = computed(() => taskItems.value.filter((task) => task.status !== 'done').length)
const completionRate = computed(() => {
  if (!filteredTaskCount.value) {
    return 0
  }

  return Math.round((taskSummary.value.done_tasks / filteredTaskCount.value) * 100)
})
const dueSoonCount = computed(() =>
  taskItems.value.filter((task) => {
    if (!task.due_date || task.status === 'done' || task.is_overdue) {
      return false
    }

    const daysUntilDue = dayjs(task.due_date).diff(dayjs(), 'day')
    return daysUntilDue >= 0 && daysUntilDue <= 7
  }).length,
)

const topLevelTasks = computed(() => taskItems.value.filter((task) => !task.parent_task_id))
const childTasksByParent = computed(() =>
  taskItems.value.reduce((carry, task) => {
    if (!task.parent_task_id) {
      return carry
    }

    if (!carry[task.parent_task_id]) {
      carry[task.parent_task_id] = []
    }

    carry[task.parent_task_id].push(task)
    return carry
  }, {}),
)
const hierarchicalTasks = computed(() => topLevelTasks.value)

const kanbanColumns = computed(() => {
  const groups = { todo: [], in_progress: [], review: [], done: [] }

  taskItems.value.forEach((task) => {
    if (groups[task.status]) {
      groups[task.status].push(task)
    }
  })

  return [
    { id: 'todo', label: 'To Do', items: groups.todo, dotClass: 'bg-slate-400' },
    { id: 'in_progress', label: 'In Progress', items: groups.in_progress, dotClass: 'bg-blue-500' },
    { id: 'review', label: 'Review', items: groups.review, dotClass: 'bg-amber-500' },
    { id: 'done', label: 'Done', items: groups.done, dotClass: 'bg-emerald-500' },
  ]
})

const currentViewTitle = computed(() => {
  if (viewMode.value === 'kanban') return 'Execution flow board'
  if (viewMode.value === 'calendar') return 'Due date calendar'
  if (viewMode.value === 'gantt') return 'Timeline sequencing map'
  return 'Structured task register'
})

const currentViewDescription = computed(() => {
  if (viewMode.value === 'kanban') return 'Pindahkan task antar status untuk menjaga flow delivery tetap rapi.'
  if (viewMode.value === 'calendar') return 'Lihat beban due date per hari supaya bentrok deadline cepat kelihatan.'
  if (viewMode.value === 'gantt') return 'Pantau sequencing task terhadap jendela waktu kerja selama bulan berjalan.'
  return 'Daftar kerja utama dengan nested task, dependency, dan sinyal progress paling lengkap.'
})

const recurringTasksCount = computed(() => taskItems.value.filter((task) => task.is_recurring).length)
const dependentTasksCount = computed(() => taskItems.value.filter((task) => task.counts.dependencies > 0).length)
const commentTrafficCount = computed(() => taskItems.value.reduce((total, task) => total + (task.counts.comments || 0), 0))

const selectedTask = computed(() => taskItems.value.find((task) => task.id === selectedTaskId.value) || null)
const selectedTaskMentionCount = computed(() => {
  if (!selectedTask.value) {
    return 0
  }

  return selectedTask.value.comments.reduce((total, comment) => total + commentMentions(comment).length, 0)
})

const activeFilterChips = computed(() => {
  const chips = []

  if (filterState.value.search) {
    chips.push({ key: 'search', label: `Search: ${filterState.value.search}` })
  }

  if (filterState.value.project) {
    const project = props.filterOptions.projects.find((item) => String(item.id) === String(filterState.value.project))
    if (project) {
      chips.push({ key: 'project', label: `Project: ${project.name}` })
    }
  }

  if (filterState.value.status) {
    const status = props.filterOptions.statuses.find((item) => item.value === filterState.value.status)
    if (status) {
      chips.push({ key: 'status', label: `Status: ${status.label}` })
    }
  }

  if (filterState.value.assignee) {
    const assignee = props.filterOptions.assignees.find((item) => String(item.id) === String(filterState.value.assignee))
    if (assignee) {
      chips.push({ key: 'assignee', label: `Assignee: ${assignee.name}` })
    }
  }

  if (filterState.value.priority) {
    const priority = props.filterOptions.priorities.find((item) => item.value === filterState.value.priority)
    if (priority) {
      chips.push({ key: 'priority', label: `Priority: ${priority.label}` })
    }
  }

  if (filterState.value.recurring) {
    chips.push({ key: 'recurring', label: filterState.value.recurring === 'yes' ? 'Recurring only' : 'One-time only' })
  }

  return chips
})

const dependencyOptions = computed(() =>
  taskItems.value
    .filter((task) => task.id !== editingTaskId.value)
    .map((task) => ({ id: task.id, title: `${task.project?.name || 'No project'} / ${task.title}` })),
)

const calendarDays = computed(() => {
  const start = currentMonth.value.startOf('month').startOf('week')
  const end = currentMonth.value.endOf('month').endOf('week')
  const days = []
  let cursor = start

  while (cursor.isBefore(end) || cursor.isSame(end, 'day')) {
    const dateKey = cursor.format('YYYY-MM-DD')
    days.push({
      dateKey,
      label: cursor.date(),
      isCurrentMonth: cursor.isSame(currentMonth.value, 'month'),
      isToday: cursor.isSame(dayjs(), 'day'),
      tasks: taskItems.value.filter((task) => task.due_date && dayjs(task.due_date).format('YYYY-MM-DD') === dateKey),
    })

    cursor = cursor.add(1, 'day')
  }

  return days
})

const ganttDays = computed(() => {
  const total = currentMonth.value.daysInMonth()
  return Array.from({ length: total }, (_, index) => index + 1)
})

const ganttTasks = computed(() =>
  taskItems.value.map((task) => {
    const ganttStart = task.gantt?.start ? dayjs(task.gantt.start) : null
    const ganttEnd = task.gantt?.end ? dayjs(task.gantt.end) : null
    const monthStart = currentMonth.value.startOf('month')
    const monthEnd = currentMonth.value.endOf('month')

    if (!ganttStart || !ganttEnd || ganttEnd.isBefore(monthStart) || ganttStart.isAfter(monthEnd)) {
      return { ...task, ganttSpan: false, ganttStyle: {} }
    }

    const clampedStart = ganttStart.isBefore(monthStart) ? monthStart : ganttStart
    const clampedEnd = ganttEnd.isAfter(monthEnd) ? monthEnd : ganttEnd
    const totalDays = monthEnd.diff(monthStart, 'day') + 1
    const offset = clampedStart.diff(monthStart, 'day')
    const span = Math.max(clampedEnd.diff(clampedStart, 'day') + 1, 1)

    return {
      ...task,
      ganttSpan: true,
      ganttStyle: {
        left: `${(offset / totalDays) * 100}%`,
        width: `${(span / totalDays) * 100}%`,
      },
    }
  }),
)

watch(
  () => props.tasks.items,
  (items) => {
    localTasks.value = cloneTasks(items || [])
  },
)

watch(
  () => props.filters,
  (filters) => {
    filterState.value = buildFilterState(filters)
  },
  { deep: true },
)

function buildFilterState(filters = {}) {
  return {
    search: filters.search ?? '',
    project: filters.project ?? '',
    status: filters.status ?? '',
    assignee: filters.assignee ?? '',
    priority: filters.priority ?? '',
    recurring: filters.recurring ?? '',
  }
}

function applyFilters() {
  router.get(tasksBaseUrl, compactFilters(filterState.value), {
    preserveState: true,
    preserveScroll: true,
  })
}

function resetFilters() {
  filterState.value = buildFilterState()

  router.get(tasksBaseUrl, {}, {
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

function openTaskModal(task = null) {
  editingTaskId.value = task?.id || null
  taskForm.reset()
  taskForm.clearErrors()
  taskForm.project_id = task?.project_id || ''
  taskForm.parent_task_id = task?.parent_task_id || ''
  taskForm.assigned_to = task?.assigned_to || ''
  taskForm.title = task?.title || ''
  taskForm.description = task?.description || ''
  taskForm.status = task?.status || 'todo'
  taskForm.priority = task?.priority || 'medium'
  taskForm.due_date = task?.due_date ? toDateTimeLocal(task.due_date) : ''
  taskForm.estimated_hours = task?.estimated_hours ?? ''
  taskForm.actual_hours = task?.actual_hours ?? ''
  taskForm.is_recurring = Boolean(task?.is_recurring)
  taskForm.recurrence_rule = task?.recurrence_rule || ''
  taskForm.template_id = task?.template_id || ''
  taskForm.sop_note_id = ''
  taskForm.dependency_ids = task?.dependencies?.map((dependency) => dependency.id) || []
  taskTagsInput.value = (task?.tags || []).join(', ')
  showTaskModal.value = true
}

function closeTaskModal() {
  showTaskModal.value = false
  editingTaskId.value = null
  taskForm.reset()
  taskForm.clearErrors()
  taskForm.status = 'todo'
  taskForm.priority = 'medium'
  taskForm.is_recurring = false
  taskForm.dependency_ids = []
  taskTagsInput.value = ''
}

function submitTask() {
  taskForm.tags = parseTags(taskTagsInput.value)
  if (!taskForm.is_recurring) {
    taskForm.recurrence_rule = ''
  }

  const options = {
    preserveScroll: true,
    onSuccess: () => closeTaskModal(),
  }

  if (editingTaskId.value) {
    taskForm.patch(`${tasksBaseUrl}/${encodeURIComponent(editingTaskId.value)}`, options)
    return
  }

  taskForm.post(tasksBaseUrl, options)
}

function deleteTask(taskId) {
  if (!confirm('Delete this task?')) {
    return
  }

  router.delete(`${tasksBaseUrl}/${encodeURIComponent(taskId)}`, {
    preserveScroll: true,
  })
}

function openTemplateModal(template = null) {
  editingTemplateId.value = template?.id || null
  templateForm.reset()
  templateForm.clearErrors()
  templateForm.title = template?.title || ''
  templateForm.description = template?.description || ''
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
    onSuccess: () => closeTemplateModal(),
  }

  if (editingTemplateId.value) {
    templateForm.patch(`${templatesBaseUrl}/${encodeURIComponent(editingTemplateId.value)}`, options)
    return
  }

  templateForm.post(templatesBaseUrl, options)
}

function deleteTemplate(templateId) {
  if (!confirm('Delete this task template?')) {
    return
  }

  router.delete(`${templatesBaseUrl}/${encodeURIComponent(templateId)}`, {
    preserveScroll: true,
  })
}

function openTaskDetail(taskId, tab = 'overview') {
  selectedTaskId.value = taskId
  detailTab.value = tab
  resetDetailForms()
  showDetailModal.value = true
}

function closeDetailModal() {
  selectedTaskId.value = null
  detailTab.value = 'overview'
  showDetailModal.value = false
  resetDetailForms()
}

function editSelectedTask() {
  if (!selectedTask.value) {
    return
  }

  const task = { ...selectedTask.value }
  closeDetailModal()
  openTaskModal(task)
}

function submitTimeLog() {
  if (!selectedTask.value) {
    return
  }

  timeLogForm.post(`${tasksBaseUrl}/${encodeURIComponent(selectedTask.value.id)}/time-logs`, {
    preserveScroll: true,
    onSuccess: () => {
      timeLogForm.reset()
    },
  })
}

function submitComment() {
  if (!selectedTask.value) {
    return
  }

  commentForm.post(`${tasksBaseUrl}/${encodeURIComponent(selectedTask.value.id)}/comments`, {
    preserveScroll: true,
    onSuccess: () => {
      commentForm.reset()
    },
  })
}

function handleDragStart(event, taskId) {
  if (movingTaskId.value) {
    return
  }

  if (event?.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move'
    event.dataTransfer.setData('text/plain', taskId)
  }

  draggingTaskId.value = taskId
}

function handleDragEnd() {
  draggingTaskId.value = null
  dragOverColumn.value = null
}

function handleColumnDragOver(columnId) {
  if (!draggingTaskId.value || movingTaskId.value) {
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
  const taskId = draggingTaskId.value

  if (!taskId || movingTaskId.value) {
    return
  }

  const task = localTasks.value.find((item) => item.id === taskId)

  if (!task) {
    handleDragEnd()
    return
  }

  const previousStatus = task.status

  if (previousStatus === nextStatus) {
    handleDragEnd()
    return
  }

  movingTaskId.value = taskId
  updateTaskStatusLocally(taskId, nextStatus)

  router.patch(
    `${tasksBaseUrl}/${encodeURIComponent(taskId)}/status`,
    { status: nextStatus },
    {
      preserveScroll: true,
      preserveState: true,
      onError: () => {
        updateTaskStatusLocally(taskId, previousStatus)
      },
      onFinish: () => {
        movingTaskId.value = null
        handleDragEnd()
      },
    },
  )
}

function updateTaskStatusLocally(taskId, status) {
  localTasks.value = localTasks.value.map((task) => {
    if (task.id !== taskId) {
      return task
    }

    return {
      ...task,
      status,
      status_label: statusLabel(status),
      is_overdue: status === 'done' ? false : task.is_overdue,
    }
  })
}

function dragColumnClass(columnId) {
  if (dragOverColumn.value !== columnId) {
    return ''
  }

  return 'border-amber-300 bg-amber-50/70 shadow-[inset_0_0_0_1px_rgba(245,158,11,0.12)]'
}

function resetDetailForms() {
  timeLogForm.reset()
  timeLogForm.clearErrors()
  commentForm.reset()
  commentForm.clearErrors()
}

function trackedHoursLabel(task) {
  return `${Number(task.actual_hours || 0).toFixed(1)}h tracked`
}

function taskEffortSummary(task) {
  const estimated = Number(task?.estimated_hours || 0)
  const actual = Number(task?.actual_hours || 0)

  if (!estimated) {
    return `${actual.toFixed(1)}h tracked / no estimate`
  }

  return `${actual.toFixed(1)}h of ${estimated.toFixed(1)}h`
}

function taskEffortWidth(task) {
  const estimated = Number(task?.estimated_hours || 0)
  const actual = Number(task?.actual_hours || 0)

  if (!estimated) {
    return '12%'
  }

  return `${Math.min((actual / estimated) * 100, 100)}%`
}

function dueTone(task) {
  if (!task?.due_date) {
    return 'No due date set'
  }

  if (task.is_overdue) {
    return `Overdue ${task.due_date_human || ''}`.trim()
  }

  return task.due_date_human || 'Due date scheduled'
}

function dueToneClass(task) {
  if (!task?.due_date) {
    return 'text-stone-500'
  }

  return task.is_overdue ? 'text-rose-600' : 'text-emerald-600'
}

function commentMentions(comment) {
  return Array.isArray(comment?.metadata?.mentions) ? comment.metadata.mentions : []
}

function compactFilters(filters) {
  return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== '' && value !== null && value !== undefined))
}

function cloneTasks(tasks) {
  return tasks.map((task) => ({
    ...task,
    tags: Array.isArray(task.tags) ? [...task.tags] : [],
    dependencies: Array.isArray(task.dependencies) ? task.dependencies.map((dependency) => ({ ...dependency })) : [],
    subtasks: Array.isArray(task.subtasks) ? task.subtasks.map((subtask) => ({ ...subtask })) : [],
    time_logs: Array.isArray(task.time_logs) ? task.time_logs.map((log) => ({ ...log })) : [],
    comments: Array.isArray(task.comments) ? task.comments.map((comment) => ({ ...comment })) : [],
    counts: task.counts ? { ...task.counts } : {},
    project: task.project ? { ...task.project } : null,
    assignee: task.assignee ? { ...task.assignee } : null,
    parent_task: task.parent_task ? { ...task.parent_task } : null,
    template: task.template ? { ...task.template } : null,
    gantt: task.gantt ? { ...task.gantt } : null,
  }))
}

function parseTags(input) {
  return input
    .split(',')
    .map((tag) => tag.trim())
    .filter(Boolean)
}

function toDateTimeLocal(value) {
  return dayjs(value).format('YYYY-MM-DDTHH:mm')
}

function statusLabel(status) {
  const map = {
    todo: 'To Do',
    in_progress: 'In Progress',
    review: 'Review',
    done: 'Done',
  }

  return map[status] || status
}

function taskStatusClass(status) {
  const map = {
    todo: 'bg-slate-100 text-slate-700',
    in_progress: 'bg-blue-100 text-blue-700',
    review: 'bg-amber-100 text-amber-700',
    done: 'bg-emerald-100 text-emerald-700',
  }

  return map[status] || 'bg-stone-100 text-stone-600'
}

function priorityClass(priority) {
  const map = {
    low: 'bg-stone-100 text-stone-600',
    medium: 'bg-sky-100 text-sky-700',
    high: 'bg-amber-100 text-amber-700',
    urgent: 'bg-rose-100 text-rose-700',
  }

  return map[priority] || 'bg-stone-100 text-stone-600'
}

function ganttBarClass(status) {
  const map = {
    todo: 'bg-slate-500',
    in_progress: 'bg-blue-500',
    review: 'bg-amber-500',
    done: 'bg-emerald-500',
  }

  return map[status] || 'bg-stone-500'
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
