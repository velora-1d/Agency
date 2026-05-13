<template>
  <WorkspaceLayout
    title="File"
    subtitle="Asset hub untuk menyimpan file, approval, share access, dan jejak versi yang terhubung ke project."
  >
    <template #actions>
      <div class="flex flex-wrap items-center gap-3">
        <button
          type="button"
          @click="openFolderModal()"
          class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-stone-300 hover:text-stone-950"
        >
          <FolderPlus class="h-4 w-4" />
          <span>Folder Baru</span>
        </button>
        <button
          type="button"
          @click="openFileModal()"
          class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
        >
          <Upload class="h-4 w-4" />
          <span>Unggah File</span>
        </button>
      </div>
    </template>

    <ProjectLayout :workspace="workspace">
      <div class="space-y-6">
      <section class="project-hero-shell">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div class="project-hero-copy">
            <p class="project-hero-kicker">Menu 13 / Manajer File</p>
            <h2 class="project-hero-title">Upload asset, rapikan per folder, preview cepat, dan kontrol approval per versi.</h2>
            <p class="project-hero-desc">
              Riwayat versi, share link, dan quota tetap lengkap, tapi header sekarang lebih ramping supaya area library lebih dominan.
            </p>
          </div>

          <div class="compact-stat-grid min-w-full gap-3 sm:min-w-[22rem] sm:grid-cols-4 xl:w-[34rem]">
            <div class="compact-stat-card">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Aset</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ fileSummary.total_files }}</p>
            </div>
            <div class="compact-stat-card">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Gambar</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ fileSummary.image_files }}</p>
            </div>
            <div class="compact-stat-card">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">PDF</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ fileSummary.pdf_files }}</p>
            </div>
            <div class="compact-stat-card">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Menunggu</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ fileSummary.pending_approvals }}</p>
            </div>
          </div>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-3">
          <div class="compact-stat-card">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Penyimpanan</p>
            <p class="mt-2 text-sm font-semibold text-stone-950">{{ fileSummary.used_label }} / {{ fileSummary.quota_label }}</p>
            <p class="mt-2 text-xs leading-5 text-stone-500">Sisa kapasitas {{ fileSummary.remaining_label }}</p>
          </div>
          <div class="compact-stat-card">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Dibagikan</p>
            <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ fileSummary.shared_files }}</p>
          </div>
          <div class="compact-stat-card">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Disetujui</p>
            <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ fileSummary.approved_files }}</p>
          </div>
        </div>
      </section>

      <section class="project-panel-shell">
        <div class="filter-panel-head mb-5">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Filter</p>
            <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Cari asset berdasarkan project, client, folder, preview, approval, dan status share.</h2>
          </div>
          <div class="filter-meta-badge">
            <span class="font-semibold text-stone-950">{{ fileItems.length }}</span> asset tampil
          </div>
        </div>

        <div class="filter-toolbar grid gap-4 md:grid-cols-2 xl:grid-cols-7">
          <label class="space-y-2 text-sm xl:col-span-2">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Cari</span>
            <input v-model="filterState.search" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Proyek</span>
            <select v-model="filterState.project" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua</option>
              <option v-for="project in filterOptions.projects" :key="project.id" :value="project.id">{{ project.name }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Klien</span>
            <select v-model="filterState.client" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua</option>
              <option v-for="client in filterOptions.clients" :key="client.id" :value="client.id">{{ client.name }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Pratinjau</span>
            <select v-model="filterState.preview" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua</option>
              <option v-for="item in filterOptions.previewKinds" :key="item.value" :value="item.value">{{ item.label }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Approval</span>
            <select v-model="filterState.approval" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua</option>
              <option v-for="item in filterOptions.approvalStatuses" :key="item.value" :value="item.value">{{ item.label }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Bagikan</span>
            <select v-model="filterState.share" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua</option>
              <option v-for="item in filterOptions.shareStates" :key="item.value" :value="item.value">{{ item.label }}</option>
            </select>
          </label>
          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Folder</span>
            <select v-model="filterState.folder" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Folder</option>
              <option v-for="folder in folders" :key="folder.id" :value="folder.id">{{ folder.name }}</option>
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
      </section>

      <section class="grid gap-4 xl:grid-cols-[0.85fr_1.15fr]">
        <aside class="space-y-4">
          <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Folder</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Struktur</h2>
              </div>
              <button type="button" @click="openFolderModal()" class="inline-flex items-center gap-2 rounded-full border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                <FolderPlus class="h-3.5 w-3.5" />
                <span>Baru</span>
              </button>
            </div>

            <div class="mt-5 space-y-3">
              <button
                type="button"
                @click="selectFolderFilter('')"
                class="w-full rounded-[1.2rem] border border-stone-200 bg-stone-50 px-4 py-3 text-left transition hover:border-stone-300 hover:bg-white"
              >
                <p class="text-sm font-semibold text-stone-950">Semua File</p>
                <p class="mt-1 text-xs text-stone-500">{{ fileSummary.total_files }} item tampil</p>
              </button>

              <article
                v-for="folder in folders"
                :key="folder.id"
                class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4"
              >
                <div class="flex items-start justify-between gap-3">
                  <button type="button" @click="selectFolderFilter(folder.id)" class="text-left">
                    <p class="text-sm font-semibold text-stone-950">{{ folder.name }}</p>
                    <p class="mt-1 text-xs text-stone-500">{{ folder.files_count }} aset</p>
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
                Belum ada folder file.
              </div>
            </div>
          </section>

          <section class="rounded-[2rem] border border-stone-200 bg-stone-950 p-5 text-white shadow-[0_20px_60px_rgba(28,25,23,0.14)]">
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-amber-200/70">Sinyal Persetujuan</p>
            <div class="mt-4 space-y-3">
              <div class="rounded-[1.2rem] border border-white/10 bg-white/5 p-4">
                <p class="text-sm font-semibold text-white">Review siap dikirim</p>
                <p class="mt-2 text-sm text-stone-300">Gunakan status approval untuk menandai aset yang menunggu respons klien atau sudah disetujui.</p>
              </div>
              <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-[1.2rem] border border-white/10 bg-white/5 p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Menunggu</p>
                  <p class="mt-2 text-2xl font-semibold text-white">{{ fileSummary.pending_approvals }}</p>
                </div>
                <div class="rounded-[1.2rem] border border-white/10 bg-white/5 p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Disetujui</p>
                  <p class="mt-2 text-2xl font-semibold text-white">{{ fileSummary.approved_files }}</p>
                </div>
              </div>
            </div>
          </section>
        </aside>

        <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Library Aset</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Manajer file workspace yang terhubung ke project dan klien.</h2>
            </div>
            <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">
              {{ fileItems.length }} aset
            </span>
          </div>

          <div class="mt-5 grid gap-4 md:grid-cols-2">
            <article v-for="file in fileItems" :key="file.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5 transition hover:border-stone-300 hover:bg-white">
              <div class="flex items-start justify-between gap-4">
                <div class="max-w-[75%]">
                  <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="openDetailModal(file.id)" class="text-left text-base font-semibold text-stone-950 transition hover:text-sky-700">
                      {{ file.name }}
                    </button>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="previewClass(file.preview_kind)">
                      {{ file.preview_kind }}
                    </span>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="approvalClass(file.approval_status)">
                      {{ file.approval_label }}
                    </span>
                  </div>
                  <p class="mt-2 text-sm text-stone-500">{{ file.original_name || 'Nama sumber belum ada' }}</p>
                  <div class="mt-4 flex flex-wrap gap-2 text-xs text-stone-500">
                    <span class="rounded-full bg-white px-3 py-1.5">{{ file.project?.name || 'Tanpa project' }}</span>
                    <span class="rounded-full bg-white px-3 py-1.5">{{ file.client?.name || 'Tanpa klien' }}</span>
                    <span class="rounded-full bg-white px-3 py-1.5">{{ file.folder?.name || 'Tanpa folder' }}</span>
                  </div>
                </div>

                <div class="flex items-center gap-2">
                  <button type="button" @click="openFileModal(file, 'edit')" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                    <Pencil class="h-4 w-4" />
                  </button>
                  <button type="button" @click="openFileModal(file, 'version')" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                    <GitBranchPlus class="h-4 w-4" />
                  </button>
                  <button type="button" @click="openShareModal(file)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                    <Share2 class="h-4 w-4" />
                  </button>
                  <button type="button" @click="deleteFile(file.id)" class="inline-flex items-center justify-center rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
                    <Trash2 class="h-4 w-4" />
                  </button>
                </div>
              </div>

              <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <div class="rounded-[1.2rem] border border-white bg-white p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Versi Aktif</p>
                  <p class="mt-2 text-lg font-semibold text-stone-950">v{{ file.version }}</p>
                  <p class="mt-1 text-xs text-stone-500">{{ file.counts.versions }} total versi</p>
                </div>
                <div class="rounded-[1.2rem] border border-white bg-white p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Ukuran File</p>
                  <p class="mt-2 text-lg font-semibold text-stone-950">{{ file.size_label }}</p>
                  <p class="mt-1 text-xs text-stone-500">{{ file.counts.total_family_size_label }} semua versi</p>
                </div>
              </div>

              <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-xs text-stone-500">
                <span>{{ file.created_at_label || 'Baru diunggah' }}</span>
                <span v-if="file.share_active" class="rounded-full bg-emerald-100 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-700">
                  Bagikan aktif sampai {{ file.share_expires_at_label }}
                </span>
              </div>
            </article>

            <div v-if="fileItems.length === 0" class="md:col-span-2 rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-16 text-center text-sm text-stone-500">
              Belum ada asset yang cocok dengan filter saat ini.
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
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Form Folder</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditingFolder ? 'Ubah Folder' : 'Buat Folder' }}</h3>
            </div>
            <button type="button" @click="closeFolderModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
              <X class="h-5 w-5" />
            </button>
          </div>

          <form class="mt-6 space-y-5" @submit.prevent="submitFolder">
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Nama Folder</span>
              <input v-model="folderForm.name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              <p v-if="folderForm.errors.name" class="text-xs text-rose-500">{{ folderForm.errors.name }}</p>
            </label>

            <div class="flex justify-end gap-3">
              <button type="button" @click="closeFolderModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">
                Batal
              </button>
              <button type="submit" :disabled="folderForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">
                {{ isEditingFolder ? 'Simpan Folder' : 'Buat Folder' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showFileModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="max-h-[92vh] w-full max-w-4xl overflow-y-auto rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Form File</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ fileModalTitle }}</h3>
            </div>
            <button type="button" @click="closeFileModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
              <X class="h-5 w-5" />
            </button>
          </div>

          <form class="mt-6 space-y-5" @submit.prevent="submitFile">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
              <label class="space-y-2 text-sm xl:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Nama Tampil</span>
                <input v-model="fileForm.name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                <p v-if="fileForm.errors.name" class="text-xs text-rose-500">{{ fileForm.errors.name }}</p>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Proyek</span>
                <select v-model="fileForm.project_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Tanpa project</option>
                  <option v-for="project in filterOptions.projects" :key="project.id" :value="project.id">{{ project.name }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Klien</span>
                <select v-model="fileForm.client_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Tanpa klien</option>
                  <option v-for="client in filterOptions.clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Folder</span>
                <select v-model="fileForm.folder_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Tanpa folder</option>
                  <option v-for="folder in folders" :key="folder.id" :value="folder.id">{{ folder.name }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Approval</span>
                <select v-model="fileForm.approval_status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option v-for="item in filterOptions.approvalStatuses" :key="item.value" :value="item.value">{{ item.label }}</option>
                </select>
              </label>
            </div>

            <section class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Unggah Biner</p>
                  <p class="mt-1 text-sm text-stone-500">Unggah gambar, PDF, video, atau file lain. Mode versi akan membuat versi baru dalam keluarga file yang sama.</p>
                </div>
                <span v-if="selectedBinaryName" class="rounded-full bg-white px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">
                  {{ selectedBinaryName }}
                </span>
              </div>

              <label class="mt-4 block rounded-[1.4rem] border border-dashed border-stone-300 bg-white px-4 py-6 text-center transition hover:border-stone-400">
                <input type="file" class="hidden" @change="handleBinaryChange" />
                <div class="space-y-2">
                  <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-stone-100 text-stone-600">
                    <Upload class="h-5 w-5" />
                  </div>
                  <p class="text-sm font-semibold text-stone-900">{{ requiresBinary ? 'Pilih file untuk diunggah' : 'Ganti file jika diperlukan' }}</p>
                  <p class="text-xs text-stone-500">Max 100 MB per upload.</p>
                </div>
              </label>
              <p v-if="fileForm.errors.binary" class="mt-2 text-xs text-rose-500">{{ fileForm.errors.binary }}</p>
            </section>

            <div class="flex flex-wrap items-center justify-end gap-3">
              <button type="button" @click="closeFileModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">
                Batal
              </button>
              <button type="submit" :disabled="fileForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">
                {{ fileModalSubmitLabel }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showShareModal && shareTargetFile" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="w-full max-w-2xl rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Link Bagikan</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ shareTargetFile.name }}</h3>
            </div>
            <button type="button" @click="closeShareModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
              <X class="h-5 w-5" />
            </button>
          </div>

          <div class="mt-5 rounded-[1.4rem] border border-stone-200 bg-stone-50 p-4">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Bagikan Saat Ini</p>
            <p class="mt-2 break-all text-sm text-stone-700">{{ shareTargetFile.share_url || 'Belum ada link aktif.' }}</p>
            <p class="mt-2 text-xs text-stone-500">
              {{ shareTargetFile.share_active ? `Aktif sampai ${shareTargetFile.share_expires_at_label}` : 'Link belum aktif atau sudah expired.' }}
            </p>
          </div>

          <form class="mt-6 space-y-5" @submit.prevent="submitShare">
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Expiry</span>
              <input v-model="shareForm.share_expires_at" type="datetime-local" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              <p v-if="shareForm.errors.share_expires_at" class="text-xs text-rose-500">{{ shareForm.errors.share_expires_at }}</p>
            </label>

            <label class="flex items-center gap-3 rounded-[1.2rem] border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700">
              <input v-model="shareForm.regenerate" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-400" />
              <span>Hasilkan token baru saat menyimpan</span>
            </label>

            <div class="flex flex-wrap items-center justify-between gap-3">
              <button type="button" @click="copyShareUrl" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                <Copy class="h-4 w-4" />
              <span>Salin Link</span>
              </button>
              <div class="flex flex-wrap items-center gap-3">
                <button type="button" @click="disableShare" class="rounded-2xl border border-rose-200 bg-white px-5 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">
                  Nonaktifkan Bagikan
                </button>
                <button type="submit" :disabled="shareForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">
                  Simpan Bagikan
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showDetailModal && selectedFile" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="max-h-[92vh] w-full max-w-6xl overflow-y-auto rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="relative overflow-hidden rounded-[1.8rem] bg-stone-950 p-6 text-white">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(251,191,36,0.2),transparent_24%),radial-gradient(circle_at_bottom_left,rgba(14,165,233,0.18),transparent_35%)]"></div>
            <div class="relative">
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="max-w-3xl">
                  <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-amber-200/70">Rincian File</p>
                  <h3 class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-white">{{ selectedFile.name }}</h3>
                  <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]" :class="previewClass(selectedFile.preview_kind)">
                      {{ selectedFile.preview_kind }}
                    </span>
                    <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]" :class="approvalClass(selectedFile.approval_status)">
                      {{ selectedFile.approval_label }}
                    </span>
                    <span class="rounded-full bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-white">
                      v{{ selectedFile.version }}
                    </span>
                  </div>
                  <div class="mt-4 flex flex-wrap gap-3 text-sm text-stone-300">
                    <span>{{ selectedFile.project?.name || 'Tanpa project' }}</span>
                    <span>{{ selectedFile.client?.name || 'Tanpa klien' }}</span>
                    <span>{{ selectedFile.folder?.name || 'Tanpa folder' }}</span>
                    <span>{{ selectedFile.size_label }}</span>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <button type="button" @click="openFileModal(selectedFile, 'edit')" class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/15">
                    <Pencil class="h-4 w-4" />
                    <span>Ubah</span>
                  </button>
                  <button type="button" @click="openFileModal(selectedFile, 'version')" class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/15">
                    <GitBranchPlus class="h-4 w-4" />
                    <span>Versi Baru</span>
                  </button>
                  <button type="button" @click="closeDetailModal" class="rounded-full border border-white/15 bg-white/10 p-2 text-stone-200 transition hover:bg-white/15 hover:text-white">
                    <X class="h-5 w-5" />
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_0.92fr]">
            <section class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Pratinjau</p>
              <div class="mt-4 min-h-[420px] rounded-[1.4rem] border border-stone-200 bg-stone-50 p-4">
                <img
                  v-if="selectedFile.preview_kind === 'image' && selectedFile.storage_url"
                  :src="selectedFile.storage_url"
                  :alt="selectedFile.name"
                  class="h-full max-h-[620px] w-full rounded-[1rem] object-contain"
                />
                <iframe
                  v-else-if="selectedFile.preview_kind === 'pdf' && selectedFile.storage_url"
                  :src="selectedFile.storage_url"
                  class="h-[620px] w-full rounded-[1rem] border border-stone-200 bg-white"
                  title="Pratinjau PDF"
                ></iframe>
                <video
                  v-else-if="selectedFile.preview_kind === 'video' && selectedFile.storage_url"
                  :src="selectedFile.storage_url"
                  controls
                  class="h-full max-h-[620px] w-full rounded-[1rem] bg-stone-950"
                ></video>
                <div v-else class="flex h-full min-h-[380px] items-center justify-center rounded-[1rem] border border-dashed border-stone-200 bg-white px-6 text-center text-sm leading-6 text-stone-500">
                  Pratinjau belum tersedia untuk tipe file ini. Tautan bagikan atau URL penyimpanan tetap bisa dipakai untuk membuka file.
                </div>
              </div>
            </section>

            <section class="space-y-6">
              <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                <div class="flex items-center justify-between gap-3">
                  <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Approval</p>
                    <p class="mt-1 text-sm text-stone-500">Ubah status review asset untuk kebutuhan approval client.</p>
                  </div>
                  <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em]" :class="approvalClass(selectedFile.approval_status)">
                    {{ selectedFile.approval_label }}
                  </span>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                  <select v-model="approvalDraft" class="min-w-[180px] rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                    <option v-for="item in filterOptions.approvalStatuses" :key="item.value" :value="item.value">{{ item.label }}</option>
                  </select>
                  <button type="button" @click="saveApproval" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800">
                    Simpan Approval
                  </button>
                </div>
              </article>

              <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                <div class="flex items-center justify-between gap-3">
                  <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tautan Bagikan</p>
                    <p class="mt-1 text-sm text-stone-500">Bagikan link dengan expiry untuk client atau stakeholder.</p>
                  </div>
                  <button type="button" @click="openShareModal(selectedFile)" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                    <Share2 class="h-4 w-4" />
                    <span>Kelola Bagikan</span>
                  </button>
                </div>
                <div class="mt-4 rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4 text-sm text-stone-600">
                  <p class="break-all font-medium text-stone-900">{{ selectedFile.share_url || 'Belum ada share link.' }}</p>
                  <p class="mt-2">{{ selectedFile.share_active ? `Aktif sampai ${selectedFile.share_expires_at_label}` : 'Link nonaktif atau expired.' }}</p>
                </div>
              </article>

              <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Version History</p>
                <div class="mt-4 space-y-3">
                  <article v-for="version in selectedFile.versions" :key="version.id" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                      <div>
                        <p class="text-sm font-semibold text-stone-950">Versi {{ version.version }}</p>
                        <p class="mt-2 text-xs text-stone-500">{{ version.original_name || version.name }}</p>
                        <p class="mt-1 text-xs text-stone-500">{{ version.size_label }} / {{ version.created_at_label || '-' }}</p>
                      </div>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em]" :class="version.is_current ? 'bg-emerald-100 text-emerald-700' : 'bg-white text-stone-500'">
                        {{ version.is_current ? 'Aktif' : 'Arsip' }}
                      </span>
                    </div>
                  </article>
                </div>
              </article>
            </section>
          </div>
        </div>
      </div>
    </Transition>
    </ProjectLayout>
  </WorkspaceLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import {
  Copy,
  Filter,
  FolderPlus,
  GitBranchPlus,
  Pencil,
  RotateCcw,
  Share2,
  Trash2,
  Upload,
  X,
} from 'lucide-vue-next'
import WorkspaceLayout from '../../Layouts/WorkspaceLayout.vue'
import ProjectLayout from '../../Layouts/ProjectLayout.vue'

const props = defineProps({
  workspace: { type: Object, required: true },
  files: { type: Object, required: true },
  folders: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  filterOptions: { type: Object, required: true },
})

const filesBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}/files`
const foldersBaseUrl = `${filesBaseUrl}/folders`

const localFiles = ref(cloneFiles(props.files.items || []))
const localFolders = ref(cloneFolders(props.folders || []))
const filterState = ref(buildFilterState(props.filters))
const showFolderModal = ref(false)
const showFileModal = ref(false)
const showDetailModal = ref(false)
const showShareModal = ref(false)
const editingFolderId = ref(null)
const editingFileId = ref(null)
const selectedFileId = ref(null)
const fileMode = ref('create')
const selectedBinaryName = ref('')
const approvalDraft = ref('pending')

const folderForm = useForm({
  name: '',
})

const fileForm = useForm({
  project_id: '',
  client_id: '',
  folder_id: '',
  source_file_id: '',
  name: '',
  binary: null,
  approval_status: 'pending',
})

const shareForm = useForm({
  share_expires_at: '',
  regenerate: false,
})

const isEditingFolder = computed(() => Boolean(editingFolderId.value))
const isEditingFile = computed(() => fileMode.value === 'edit' && Boolean(editingFileId.value))
const requiresBinary = computed(() => fileMode.value !== 'edit')
const fileItems = computed(() => localFiles.value)
const folders = computed(() => localFolders.value)
const fileSummary = computed(() => props.files.summary)
const selectedFile = computed(() => fileItems.value.find((file) => file.id === selectedFileId.value) || null)
const shareTargetFile = computed(() => fileItems.value.find((file) => file.id === selectedFileId.value) || null)
const fileModalTitle = computed(() => {
  if (fileMode.value === 'version') return 'Upload Versi Baru'
  if (fileMode.value === 'edit') return 'Ubah File'
  return 'Unggah File'
})
const fileModalSubmitLabel = computed(() => {
  if (fileMode.value === 'version') return 'Buat Versi'
  if (fileMode.value === 'edit') return 'Simpan File'
  return 'Unggah File'
})

watch(
  () => props.files.items,
  (items) => {
    localFiles.value = cloneFiles(items || [])
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
  selectedFile,
  (file) => {
    if (!file) {
      approvalDraft.value = 'pending'
      return
    }

    approvalDraft.value = file.approval_status || 'pending'
  },
  { immediate: true },
)

function buildFilterState(filters = {}) {
  return {
    search: filters.search ?? '',
    project: filters.project ?? '',
    client: filters.client ?? '',
    folder: filters.folder ?? '',
    preview: filters.preview ?? '',
    approval: filters.approval ?? '',
    share: filters.share ?? '',
  }
}

function applyFilters() {
  router.get(filesBaseUrl, compactFilters(filterState.value), {
    preserveState: true,
    preserveScroll: true,
  })
}

function resetFilters() {
  filterState.value = buildFilterState()

  router.get(filesBaseUrl, {}, {
    preserveState: true,
    preserveScroll: true,
  })
}

function selectFolderFilter(folderId) {
  filterState.value.folder = folderId
  applyFilters()
}

function openFolderModal(folder = null) {
  editingFolderId.value = folder?.id || null
  folderForm.reset()
  folderForm.clearErrors()
  folderForm.name = folder?.name || ''
  showFolderModal.value = true
  showFileModal.value = false
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
  if (!confirm('Hapus folder ini?')) {
    return
  }

  router.delete(`${foldersBaseUrl}/${encodeURIComponent(folderId)}`, {
    preserveScroll: true,
  })
}

function openFileModal(file = null, mode = 'create') {
  fileMode.value = mode
  editingFileId.value = mode === 'edit' ? file?.id || null : null
  selectedBinaryName.value = ''
  fileForm.reset()
  fileForm.clearErrors()
  fileForm.project_id = file?.project_id || ''
  fileForm.client_id = file?.client_id || ''
  fileForm.folder_id = file?.folder_id || ''
  fileForm.source_file_id = mode === 'version' ? file?.id || '' : ''
  fileForm.name = mode === 'version' ? `${file?.name || ''}` : file?.name || ''
  fileForm.binary = null
  fileForm.approval_status = mode === 'version' ? 'pending' : file?.approval_status || 'pending'
  showFileModal.value = true
  showFolderModal.value = false
  showDetailModal.value = false
  showShareModal.value = false
}

function closeFileModal() {
  showFileModal.value = false
  editingFileId.value = null
  fileMode.value = 'create'
  selectedBinaryName.value = ''
  fileForm.reset()
  fileForm.clearErrors()
  fileForm.approval_status = 'pending'
}

function handleBinaryChange(event) {
  const [file] = event.target.files || []
  fileForm.binary = file || null
  selectedBinaryName.value = file?.name || ''

  if (!fileForm.name && file?.name) {
    fileForm.name = file.name.replace(/\.[^.]+$/, '')
  }
}

function submitFile() {
  const options = {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => closeFileModal(),
  }

  if (requiresBinary.value && !fileForm.binary) {
    fileForm.setError('binary', 'File wajib diunggah.')
    return
  }

  if (fileMode.value === 'edit' && editingFileId.value) {
    fileForm.patch(`${filesBaseUrl}/${encodeURIComponent(editingFileId.value)}`, options)
    return
  }

  fileForm.post(filesBaseUrl, options)
}

function deleteFile(fileId) {
  if (!confirm('Hapus file ini beserta semua versinya?')) {
    return
  }

  router.delete(`${filesBaseUrl}/${encodeURIComponent(fileId)}`, {
    preserveScroll: true,
    onSuccess: () => {
      if (selectedFileId.value === fileId) {
        closeDetailModal()
      }
    },
  })
}

function openDetailModal(fileId) {
  selectedFileId.value = fileId
  showDetailModal.value = true
}

function closeDetailModal() {
  showDetailModal.value = false
  selectedFileId.value = null
}

function openShareModal(file) {
  selectedFileId.value = file.id
  shareForm.reset()
  shareForm.clearErrors()
  shareForm.share_expires_at = toDateTimeLocal(file.share_expires_at)
  shareForm.regenerate = false
  showShareModal.value = true
  showFileModal.value = false
}

function closeShareModal() {
  showShareModal.value = false
  shareForm.reset()
  shareForm.clearErrors()
}

function submitShare() {
  if (!shareTargetFile.value) {
    return
  }

  shareForm.patch(`${filesBaseUrl}/${encodeURIComponent(shareTargetFile.value.id)}/share`, {
    preserveScroll: true,
    onSuccess: () => closeShareModal(),
  })
}

function disableShare() {
  if (!shareTargetFile.value) {
    return
  }

  shareForm.share_expires_at = ''
  shareForm.regenerate = false

  shareForm.patch(`${filesBaseUrl}/${encodeURIComponent(shareTargetFile.value.id)}/share`, {
    preserveScroll: true,
    onSuccess: () => closeShareModal(),
  })
}

async function copyShareUrl() {
  if (!shareTargetFile.value?.share_url) {
    return
  }

  try {
    await navigator.clipboard.writeText(shareTargetFile.value.share_url)
  } catch {
    // Ignore clipboard failures in unsupported browsers.
  }
}

function saveApproval() {
  if (!selectedFile.value) {
    return
  }

  router.patch(`${filesBaseUrl}/${encodeURIComponent(selectedFile.value.id)}/approval`, {
    approval_status: approvalDraft.value,
  }, {
    preserveScroll: true,
  })
}

function previewClass(kind) {
  const map = {
    image: 'bg-sky-100 text-sky-700',
    pdf: 'bg-rose-100 text-rose-700',
    video: 'bg-amber-100 text-amber-700',
    other: 'bg-stone-100 text-stone-600',
  }

  return map[kind] || 'bg-stone-100 text-stone-600'
}

function approvalClass(status) {
  const map = {
    draft: 'bg-stone-100 text-stone-600',
    pending: 'bg-amber-100 text-amber-700',
    approved: 'bg-emerald-100 text-emerald-700',
    rejected: 'bg-rose-100 text-rose-700',
  }

  return map[status] || 'bg-stone-100 text-stone-600'
}

function toDateTimeLocal(value) {
  if (!value) {
    return ''
  }

  const date = new Date(value)

  if (Number.isNaN(date.getTime())) {
    return ''
  }

  const pad = (number) => String(number).padStart(2, '0')

  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`
}

function compactFilters(filters) {
  return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== '' && value !== null && value !== undefined))
}

function cloneFiles(items) {
  return items.map((file) => ({
    ...file,
    project: file.project ? { ...file.project } : null,
    client: file.client ? { ...file.client } : null,
    folder: file.folder ? { ...file.folder } : null,
    uploader: file.uploader ? { ...file.uploader } : null,
    approver: file.approver ? { ...file.approver } : null,
    counts: file.counts ? { ...file.counts } : {},
    versions: Array.isArray(file.versions) ? file.versions.map((version) => ({
      ...version,
      uploader: version.uploader ? { ...version.uploader } : null,
    })) : [],
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
