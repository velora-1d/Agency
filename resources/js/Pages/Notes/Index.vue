<template>
  <WorkspaceLayout
    title="Notes / SOP"
    subtitle="Bangun knowledge base internal dengan folder, versi dokumen, template SOP, dan relasi langsung ke project atau task."
  >
    <template #actions>
      <div class="flex flex-wrap items-center gap-3">
        <button
          type="button"
          @click="openFolderModal()"
          class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-stone-300 hover:text-stone-950"
        >
          <FolderPlus class="h-4 w-4" />
          <span>New Folder</span>
        </button>
        <button
          type="button"
          @click="openNoteModal()"
          class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
        >
          <Plus class="h-4 w-4" />
          <span>New Note</span>
        </button>
      </div>
    </template>

    <div class="space-y-6">
      <section class="grid gap-4 xl:grid-cols-[1.2fr_0.8fr]">
        <article class="relative overflow-hidden rounded-[2rem] bg-stone-950 p-6 text-white shadow-[0_28px_90px_rgba(28,25,23,0.18)]">
          <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(251,191,36,0.22),transparent_28%),radial-gradient(circle_at_bottom_left,rgba(14,165,233,0.18),transparent_36%)]"></div>
          <div class="relative">
            <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-amber-200/75">Menu 12 / Notes / SOP</p>
            <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
              <div class="max-w-2xl">
                <h2 class="text-3xl font-semibold tracking-[-0.05em] text-white">Knowledge base yang hidup: note, SOP, template, versi, dan link ke task.</h2>
                <p class="mt-3 max-w-xl text-sm leading-6 text-stone-300">
                  Setiap dokumen bisa masuk folder, ditautkan ke project global atau spesifik, dibuat private atau dibagikan ke tim, lalu dipakai ulang sebagai template SOP.
                </p>
              </div>
              <div class="rounded-[1.4rem] border border-white/10 bg-white/10 px-4 py-3 backdrop-blur">
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-stone-300">Versioning</p>
                <p class="mt-2 text-3xl font-semibold tracking-[-0.05em] text-white">{{ noteSummary.total_notes }}</p>
                <p class="mt-1 text-sm text-stone-300">dokumen aktif di workspace</p>
              </div>
            </div>

            <div class="mt-6 grid gap-3 md:grid-cols-3">
              <div class="rounded-[1.4rem] border border-white/10 bg-white/10 p-4 backdrop-blur">
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-stone-300">SOP</p>
                <p class="mt-2 text-2xl font-semibold text-white">{{ noteSummary.sop_notes }}</p>
                <p class="mt-1 text-sm text-stone-300">dokumen operasional</p>
              </div>
              <div class="rounded-[1.4rem] border border-white/10 bg-white/10 p-4 backdrop-blur">
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-stone-300">Templates</p>
                <p class="mt-2 text-2xl font-semibold text-white">{{ noteSummary.template_notes }}</p>
                <p class="mt-1 text-sm text-stone-300">template reusable</p>
              </div>
              <div class="rounded-[1.4rem] border border-white/10 bg-white/10 p-4 backdrop-blur">
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-stone-300">Linked Tasks</p>
                <p class="mt-2 text-2xl font-semibold text-white">{{ noteSummary.linked_tasks }}</p>
                <p class="mt-1 text-sm text-stone-300">SOP yang sudah turun ke task</p>
              </div>
            </div>
          </div>
        </article>

        <article class="rounded-[2rem] border border-stone-200 bg-[linear-gradient(180deg,#fffdf8_0%,#ffffff_100%)] p-6 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Library posture</p>
          <div class="mt-5 space-y-4">
            <div class="rounded-[1.4rem] border border-stone-200 bg-white p-4">
              <p class="text-sm font-semibold text-stone-950">Folder-based structure</p>
              <p class="mt-2 text-sm leading-6 text-stone-600">Susun knowledge base dengan folder agar SOP, note, dan template cepat dicari.</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
              <div class="rounded-[1.4rem] border border-stone-200 bg-white p-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Private</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ noteSummary.private_notes }}</p>
              </div>
              <div class="rounded-[1.4rem] border border-stone-200 bg-white p-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Global</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ noteSummary.global_notes }}</p>
              </div>
            </div>
            <div class="rounded-[1.4rem] border border-dashed border-stone-300 bg-stone-50 px-4 py-4 text-sm leading-6 text-stone-600">
              Version history disimpan per update, dan SOP bisa langsung dipetakan ke task supaya eksekusi lapangan tetap konsisten.
            </div>
          </div>
        </article>
      </section>

      <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-4">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Filters</p>
            <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Cari dokumen berdasarkan project, folder, type, dan visibility.</h2>
          </div>
          <div class="rounded-[1.3rem] border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-600">
            <span class="font-semibold text-stone-950">{{ noteItems.length }}</span> note tampil
          </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
          <label class="space-y-2 text-sm xl:col-span-2">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Search</span>
            <input v-model="filterState.search" type="text" placeholder="Cari judul, isi, folder, atau project" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Project</span>
            <select v-model="filterState.project" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">All Projects</option>
              <option value="global">Global Only</option>
              <option v-for="project in filterOptions.projects" :key="project.id" :value="project.id">{{ project.name }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Folder</span>
            <select v-model="filterState.folder" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">All Folders</option>
              <option v-for="folder in folders" :key="folder.id" :value="folder.id">{{ folder.name }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Type</span>
            <select v-model="filterState.type" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">All Types</option>
              <option v-for="type in filterOptions.types" :key="type.value" :value="type.value">{{ type.label }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Visibility</span>
            <select v-model="filterState.visibility" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">All</option>
              <option v-for="item in filterOptions.visibility" :key="item.value" :value="item.value">{{ item.label }}</option>
            </select>
          </label>
        </div>

        <div class="mt-5 flex flex-wrap items-center gap-3">
          <button type="button" @click="applyFilters" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition-all hover:-translate-y-0.5 hover:bg-stone-800">
            <Filter class="h-4 w-4" />
            <span>Apply Filters</span>
          </button>
          <button type="button" @click="resetFilters" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900">
            <RotateCcw class="h-4 w-4" />
            <span>Reset</span>
          </button>
        </div>
      </section>

      <section class="grid gap-4 xl:grid-cols-[0.9fr_1.1fr]">
        <aside class="space-y-4">
          <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Folders</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Structure</h2>
              </div>
              <button type="button" @click="openFolderModal()" class="inline-flex items-center gap-2 rounded-full border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                <FolderPlus class="h-3.5 w-3.5" />
                <span>New</span>
              </button>
            </div>

            <div class="mt-5 space-y-3">
              <button
                type="button"
                @click="filterState.folder = ''"
                class="w-full rounded-[1.2rem] border border-stone-200 bg-stone-50 px-4 py-3 text-left transition hover:border-stone-300 hover:bg-white"
              >
                <p class="text-sm font-semibold text-stone-950">All Notes</p>
                <p class="mt-1 text-xs text-stone-500">{{ noteSummary.total_notes }} items</p>
              </button>
              <article
                v-for="folder in folders"
                :key="folder.id"
                class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4"
              >
                <div class="flex items-start justify-between gap-3">
                  <button type="button" @click="filterState.folder = folder.id" class="text-left">
                    <p class="text-sm font-semibold text-stone-950">{{ folder.name }}</p>
                    <p class="mt-1 text-xs text-stone-500">{{ folder.notes_count }} notes</p>
                  </button>
                  <div class="flex gap-2">
                    <button type="button" @click="openFolderModal(folder)" class="rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                      <Pencil class="h-3.5 w-3.5" />
                    </button>
                    <button type="button" @click="deleteFolder(folder.id)" class="rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
                      <Trash2 class="h-3.5 w-3.5" />
                    </button>
                  </div>
                </div>
              </article>
              <div v-if="folders.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-200 bg-stone-50 px-4 py-8 text-center text-sm text-stone-500">
                Belum ada folder notes.
              </div>
            </div>
          </section>

          <section class="rounded-[2rem] border border-stone-200 bg-stone-950 p-5 text-white shadow-[0_20px_60px_rgba(28,25,23,0.14)]">
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-amber-200/70">Templates</p>
            <div class="mt-4 space-y-3">
              <article v-for="template in templateNotes" :key="template.id" class="rounded-[1.2rem] border border-white/10 bg-white/5 p-4">
                <p class="text-sm font-semibold text-white">{{ template.title }}</p>
                <p class="mt-2 text-sm text-stone-300">{{ template.content_preview || 'Template tanpa konten.' }}</p>
                <button type="button" @click="cloneTemplate(template)" class="mt-4 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3 py-2 text-xs font-semibold text-white transition hover:bg-white/15">
                  <Copy class="h-3.5 w-3.5" />
                  <span>Use Template</span>
                </button>
              </article>
              <div v-if="templateNotes.length === 0" class="rounded-[1.2rem] border border-dashed border-white/10 bg-white/5 px-4 py-8 text-center text-sm text-stone-300">
                Belum ada template SOP.
              </div>
            </div>
          </section>
        </aside>

        <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Note Library</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Dokumen knowledge base workspace.</h2>
            </div>
            <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">
              {{ noteItems.length }} notes
            </span>
          </div>

          <div class="mt-5 space-y-4">
            <article v-for="note in noteItems" :key="note.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5 transition hover:border-stone-300 hover:bg-white">
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="max-w-2xl">
                  <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="openNoteDetail(note.id)" class="text-left text-base font-semibold text-stone-950 transition hover:text-amber-700">
                      {{ note.title }}
                    </button>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="noteTypeClass(note.type)">
                      {{ note.type_label }}
                    </span>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="note.is_private ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'">
                      {{ note.visibility_label }}
                    </span>
                  </div>
                  <p class="mt-3 text-sm leading-6 text-stone-600">{{ note.content_preview || 'Belum ada isi dokumen.' }}</p>
                  <div class="mt-4 flex flex-wrap gap-2 text-xs text-stone-500">
                    <span class="rounded-full bg-white px-3 py-1.5">{{ note.folder?.name || 'No folder' }}</span>
                    <span class="rounded-full bg-white px-3 py-1.5">{{ note.project?.name || 'Global note' }}</span>
                    <span class="rounded-full bg-white px-3 py-1.5">v{{ note.version }}</span>
                    <span class="rounded-full bg-white px-3 py-1.5">{{ note.counts.linked_tasks }} linked tasks</span>
                  </div>
                </div>

                <div class="flex items-center gap-2">
                  <button type="button" @click="openNoteModal(note)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                    <Pencil class="h-4 w-4" />
                  </button>
                  <button type="button" @click="openNoteDetail(note.id)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                    <Layers3 class="h-4 w-4" />
                  </button>
                  <button type="button" @click="deleteNote(note.id)" class="inline-flex items-center justify-center rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
                    <Trash2 class="h-4 w-4" />
                  </button>
                </div>
              </div>
            </article>

            <div v-if="noteItems.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-16 text-center text-sm text-stone-500">
              Belum ada notes atau SOP yang cocok dengan filter saat ini.
            </div>
          </div>
        </article>
      </section>
    </div>

    <Transition name="modal">
      <div v-if="showFolderModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="w-full max-w-xl rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Folder Form</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditingFolder ? 'Edit Folder' : 'Create Folder' }}</h3>
            </div>
            <button type="button" @click="closeFolderModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
              <X class="h-5 w-5" />
            </button>
          </div>

          <form class="mt-6 space-y-5" @submit.prevent="submitFolder">
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Folder Name</span>
              <input v-model="folderForm.name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              <p v-if="folderForm.errors.name" class="text-xs text-rose-500">{{ folderForm.errors.name }}</p>
            </label>

            <div class="flex justify-end gap-3">
              <button type="button" @click="closeFolderModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">
                Cancel
              </button>
              <button type="submit" :disabled="folderForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">
                {{ isEditingFolder ? 'Save Folder' : 'Create Folder' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showNoteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="max-h-[92vh] w-full max-w-6xl overflow-y-auto rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Note Form</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditingNote ? 'Edit Note' : 'Create Note' }}</h3>
            </div>
            <button type="button" @click="closeNoteModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
              <X class="h-5 w-5" />
            </button>
          </div>

          <form class="mt-6 space-y-6" @submit.prevent="submitNote">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Project</span>
                <select v-model="noteForm.project_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Global note</option>
                  <option v-for="project in filterOptions.projects" :key="project.id" :value="project.id">{{ project.name }}</option>
                </select>
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Folder</span>
                <select v-model="noteForm.folder_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">No folder</option>
                  <option v-for="folder in folders" :key="folder.id" :value="folder.id">{{ folder.name }}</option>
                </select>
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Type</span>
                <select v-model="noteForm.type" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option v-for="type in filterOptions.types" :key="type.value" :value="type.value">{{ type.label }}</option>
                </select>
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Visibility</span>
                <select v-model="noteVisibility" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="team">Shared to Team</option>
                  <option value="private">Private</option>
                </select>
              </label>
              <label class="space-y-2 text-sm md:col-span-2 xl:col-span-4">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Title</span>
                <input v-model="noteForm.title" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                <p v-if="noteForm.errors.title" class="text-xs text-rose-500">{{ noteForm.errors.title }}</p>
              </label>
            </div>

            <section class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Rich Content</p>
                  <p class="mt-1 text-sm text-stone-500">Editor sederhana dengan toolbar untuk heading, callout, dan checklist.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                  <button type="button" @click="wrapContent('h2')" class="rounded-full border border-stone-200 bg-white px-3 py-2 text-xs font-semibold text-stone-700">H2</button>
                  <button type="button" @click="wrapContent('callout')" class="rounded-full border border-stone-200 bg-white px-3 py-2 text-xs font-semibold text-stone-700">Callout</button>
                  <button type="button" @click="insertChecklist()" class="rounded-full border border-stone-200 bg-white px-3 py-2 text-xs font-semibold text-stone-700">Checklist</button>
                </div>
              </div>

              <textarea v-model="noteForm.content" rows="12" class="mt-4 w-full rounded-[1.4rem] border border-stone-200 bg-white px-4 py-3 font-mono text-sm text-stone-700 outline-none transition-all focus:border-stone-400"></textarea>
            </section>

            <section class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Linked Tasks</p>
                  <p class="mt-1 text-sm text-stone-500">Pilih task yang menggunakan SOP / note ini sebagai panduan kerja.</p>
                </div>
                <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">{{ noteForm.linked_task_ids.length }} selected</span>
              </div>
              <select v-model="noteForm.linked_task_ids" multiple class="mt-4 min-h-[180px] w-full rounded-[1.4rem] border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option v-for="task in filterOptions.tasks" :key="task.id" :value="task.id">{{ task.title }}</option>
              </select>
            </section>

            <div class="flex flex-wrap items-center justify-end gap-3">
              <button type="button" @click="closeNoteModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">
                Cancel
              </button>
              <button type="submit" :disabled="noteForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">
                {{ isEditingNote ? 'Save Note' : 'Create Note' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showDetailModal && selectedNote" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="max-h-[92vh] w-full max-w-5xl overflow-y-auto rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="relative overflow-hidden rounded-[1.8rem] bg-stone-950 p-6 text-white">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(251,191,36,0.2),transparent_24%),radial-gradient(circle_at_bottom_left,rgba(14,165,233,0.18),transparent_35%)]"></div>
            <div class="relative">
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="max-w-3xl">
                  <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-amber-200/70">Note Detail</p>
                  <h3 class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-white">{{ selectedNote.title }}</h3>
                  <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]" :class="noteTypeClass(selectedNote.type)">{{ selectedNote.type_label }}</span>
                    <span class="rounded-full bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-white">{{ selectedNote.visibility_label }}</span>
                    <span class="rounded-full bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-white">v{{ selectedNote.version }}</span>
                  </div>
                  <div class="mt-4 flex flex-wrap gap-3 text-sm text-stone-300">
                    <span>{{ selectedNote.project?.name || 'Global note' }}</span>
                    <span>{{ selectedNote.folder?.name || 'No folder' }}</span>
                    <span>{{ selectedNote.updated_at_label || 'No updates yet' }}</span>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <button type="button" @click="openNoteModal(selectedNote)" class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/15">
                    <Pencil class="h-4 w-4" />
                    <span>Edit</span>
                  </button>
                  <button type="button" @click="closeDetailModal" class="rounded-full border border-white/15 bg-white/10 p-2 text-stone-200 transition hover:bg-white/15 hover:text-white">
                    <X class="h-5 w-5" />
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_0.9fr]">
            <section class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Content</p>
              <div class="mt-4 rounded-[1.4rem] border border-stone-200 bg-stone-50 p-5">
                <div class="prose prose-stone max-w-none whitespace-pre-wrap text-sm leading-7 text-stone-700">{{ selectedNote.content || 'Belum ada konten.' }}</div>
              </div>
            </section>

            <section class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Version History</p>
              <div class="mt-4 space-y-3">
                <article v-for="revision in selectedNote.revisions" :key="revision.id" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <div class="flex items-start justify-between gap-3">
                    <div>
                      <p class="text-sm font-semibold text-stone-950">Version {{ revision.version }}</p>
                      <p class="mt-2 text-xs text-stone-500">{{ revision.created_at_label || '-' }} by {{ revision.creator?.name || 'System' }}</p>
                    </div>
                    <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-stone-500">Snapshot</span>
                  </div>
                  <p class="mt-3 text-sm leading-6 text-stone-600">{{ revision.content_preview || 'No preview' }}</p>
                </article>
                <div v-if="selectedNote.revisions.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-200 bg-stone-50 px-4 py-8 text-center text-sm text-stone-500">
                  Belum ada version history.
                </div>
              </div>
            </section>
          </div>

          <section class="mt-6 rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Linked Tasks</p>
                <p class="mt-1 text-sm text-stone-500">Task yang memakai SOP / note ini.</p>
              </div>
              <a :href="`/w/${workspace.slug}/tasks?search=${encodeURIComponent(selectedNote.title)}`" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                <ArrowUpRight class="h-4 w-4" />
                <span>Open Tasks</span>
              </a>
            </div>

            <div class="mt-4 space-y-3">
              <article v-for="task in selectedNote.linked_tasks" :key="task.id" class="rounded-[1.2rem] border border-stone-200 bg-white p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                  <div>
                    <p class="text-sm font-semibold text-stone-950">{{ task.title }}</p>
                    <p class="mt-2 text-xs text-stone-500">{{ task.assignee?.name || 'Unassigned' }} / {{ task.due_date_label || 'No due date' }}</p>
                  </div>
                  <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em]" :class="taskStatusClass(task.status)">{{ statusLabel(task.status) }}</span>
                </div>
              </article>
              <div v-if="selectedNote.linked_tasks.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-200 bg-white px-4 py-8 text-center text-sm text-stone-500">
                Belum ada task yang terhubung.
              </div>
            </div>
          </section>
        </div>
      </div>
    </Transition>
  </WorkspaceLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import {
  ArrowUpRight,
  Copy,
  Filter,
  FolderPlus,
  Layers3,
  Pencil,
  Plus,
  RotateCcw,
  Trash2,
  X,
} from 'lucide-vue-next'
import WorkspaceLayout from '../../Layouts/WorkspaceLayout.vue'

const props = defineProps({
  workspace: { type: Object, required: true },
  notes: { type: Object, required: true },
  folders: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  filterOptions: { type: Object, required: true },
})

const notesBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}/notes`
const foldersBaseUrl = `${notesBaseUrl}/folders`

const localNotes = ref(cloneNotes(props.notes.items || []))
const localFolders = ref(cloneFolders(props.folders || []))
const filterState = ref(buildFilterState(props.filters))
const showNoteModal = ref(false)
const showFolderModal = ref(false)
const showDetailModal = ref(false)
const editingNoteId = ref(null)
const editingFolderId = ref(null)
const selectedNoteId = ref(props.notes.selected_id || null)
const noteVisibility = ref('team')

const noteForm = useForm({
  project_id: '',
  folder_id: '',
  title: '',
  content: '',
  type: 'note',
  is_private: false,
  linked_task_ids: [],
})

const folderForm = useForm({
  name: '',
})

const isEditingNote = computed(() => Boolean(editingNoteId.value))
const isEditingFolder = computed(() => Boolean(editingFolderId.value))
const noteItems = computed(() => localNotes.value)
const folders = computed(() => localFolders.value)
const noteSummary = computed(() => props.notes.summary)
const selectedNote = computed(() => noteItems.value.find((note) => note.id === selectedNoteId.value) || null)
const templateNotes = computed(() => noteItems.value.filter((note) => note.type === 'template'))

watch(
  () => props.notes.items,
  (items) => {
    localNotes.value = cloneNotes(items || [])
  },
)

watch(
  () => props.folders,
  (items) => {
    localFolders.value = cloneFolders(items || [])
  },
)

watch(
  () => props.filters,
  (filters) => {
    filterState.value = buildFilterState(filters)
  },
  { deep: true },
)

watch(
  () => props.notes.selected_id,
  (noteId) => {
    if (!noteId) {
      return
    }

    selectedNoteId.value = noteId
    showDetailModal.value = true
  },
  { immediate: true },
)

watch(
  noteVisibility,
  (value) => {
    noteForm.is_private = value === 'private'
  },
)

function buildFilterState(filters = {}) {
  return {
    search: filters.search ?? '',
    project: filters.project ?? '',
    folder: filters.folder ?? '',
    type: filters.type ?? '',
    visibility: filters.visibility ?? '',
  }
}

function applyFilters() {
  router.get(notesBaseUrl, compactFilters(filterState.value), {
    preserveState: true,
    preserveScroll: true,
  })
}

function resetFilters() {
  filterState.value = buildFilterState()

  router.get(notesBaseUrl, {}, {
    preserveState: true,
    preserveScroll: true,
  })
}

function openNoteModal(note = null) {
  editingNoteId.value = note?.id || null
  noteForm.reset()
  noteForm.clearErrors()
  noteForm.project_id = note?.project_id || ''
  noteForm.folder_id = note?.folder_id || ''
  noteForm.title = note?.title || ''
  noteForm.content = note?.content || ''
  noteForm.type = note?.type || 'note'
  noteVisibility.value = note?.is_private ? 'private' : 'team'
  noteForm.is_private = note?.is_private ?? false
  noteForm.linked_task_ids = note?.linked_tasks?.map((task) => task.id) || []
  showNoteModal.value = true
  showFolderModal.value = false
  showDetailModal.value = false
}

function closeNoteModal() {
  showNoteModal.value = false
  editingNoteId.value = null
  noteForm.reset()
  noteForm.clearErrors()
  noteForm.type = 'note'
  noteVisibility.value = 'team'
  noteForm.is_private = false
  noteForm.linked_task_ids = []
}

function submitNote() {
  const options = {
    preserveScroll: true,
    onSuccess: () => closeNoteModal(),
  }

  if (isEditingNote.value) {
    noteForm.patch(`${notesBaseUrl}/${encodeURIComponent(editingNoteId.value)}`, options)
    return
  }

  noteForm.post(notesBaseUrl, options)
}

function deleteNote(noteId) {
  if (!confirm('Delete this note?')) {
    return
  }

  router.delete(`${notesBaseUrl}/${encodeURIComponent(noteId)}`, {
    preserveScroll: true,
    onSuccess: () => {
      if (selectedNoteId.value === noteId) {
        closeDetailModal()
      }
    },
  })
}

function openFolderModal(folder = null) {
  editingFolderId.value = folder?.id || null
  folderForm.reset()
  folderForm.clearErrors()
  folderForm.name = folder?.name || ''
  showFolderModal.value = true
  showNoteModal.value = false
  showDetailModal.value = false
}

function closeFolderModal() {
  showFolderModal.value = false
  editingFolderId.value = null
  folderForm.reset()
  folderForm.clearErrors()
}

function submitFolder() {
  const options = {
    preserveScroll: true,
    onSuccess: () => closeFolderModal(),
  }

  if (isEditingFolder.value) {
    folderForm.patch(`${foldersBaseUrl}/${encodeURIComponent(editingFolderId.value)}`, options)
    return
  }

  folderForm.post(foldersBaseUrl, options)
}

function deleteFolder(folderId) {
  if (!confirm('Delete this folder?')) {
    return
  }

  router.delete(`${foldersBaseUrl}/${encodeURIComponent(folderId)}`, {
    preserveScroll: true,
  })
}

function openNoteDetail(noteId) {
  selectedNoteId.value = noteId
  showDetailModal.value = true
}

function closeDetailModal() {
  showDetailModal.value = false
  selectedNoteId.value = null
}

function cloneTemplate(template) {
  noteVisibility.value = template.is_private ? 'private' : 'team'
  openNoteModal({
    ...template,
    type: 'sop',
    title: `${template.title} Copy`,
    content: template.content,
  })
}

function wrapContent(kind) {
  const value = noteForm.content || ''

  if (kind === 'h2') {
    noteForm.content = `## Heading\n${value}`.trim()
    return
  }

  if (kind === 'callout') {
    noteForm.content = `> Callout\n${value}`.trim()
  }
}

function insertChecklist() {
  const prefix = '- [ ] '
  noteForm.content = `${noteForm.content || ''}\n${prefix}`.trimStart()
}

function noteTypeClass(type) {
  const map = {
    note: 'bg-sky-100 text-sky-700',
    sop: 'bg-amber-100 text-amber-700',
    template: 'bg-violet-100 text-violet-700',
  }

  return map[type] || 'bg-stone-100 text-stone-600'
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

function statusLabel(status) {
  const map = {
    todo: 'To Do',
    in_progress: 'In Progress',
    review: 'Review',
    done: 'Done',
  }

  return map[status] || status
}

function compactFilters(filters) {
  return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== '' && value !== null && value !== undefined))
}

function cloneNotes(items) {
  return items.map((note) => ({
    ...note,
    project: note.project ? { ...note.project } : null,
    folder: note.folder ? { ...note.folder } : null,
    creator: note.creator ? { ...note.creator } : null,
    linked_tasks: Array.isArray(note.linked_tasks) ? note.linked_tasks.map((task) => ({ ...task, assignee: task.assignee ? { ...task.assignee } : null })) : [],
    revisions: Array.isArray(note.revisions) ? note.revisions.map((revision) => ({ ...revision, creator: revision.creator ? { ...revision.creator } : null })) : [],
    counts: note.counts ? { ...note.counts } : {},
  }))
}

function cloneFolders(items) {
  return items.map((folder) => ({ ...folder }))
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
