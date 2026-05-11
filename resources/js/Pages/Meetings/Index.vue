<template>
  <WorkspaceLayout
    title="Meetings"
    subtitle="Jadwalkan meeting, simpan agenda dan notes, lalu turunkan action items menjadi task yang langsung bisa dieksekusi tim."
  >
    <template #actions>
      <div class="flex flex-wrap items-center gap-3">
        <button
          type="button"
          @click="openMeetingModal()"
          class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
        >
          <Plus class="h-4 w-4" />
          <span>Schedule Meeting</span>
        </button>
      </div>
    </template>

    <div class="space-y-6">
      <section class="grid gap-4 xl:grid-cols-[1.25fr_0.75fr]">
        <article class="relative overflow-hidden rounded-[2rem] bg-stone-950 p-6 text-white shadow-[0_28px_90px_rgba(28,25,23,0.18)]">
          <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(14,165,233,0.22),transparent_28%),radial-gradient(circle_at_bottom_left,rgba(251,191,36,0.18),transparent_36%)]"></div>
          <div class="relative">
            <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-sky-200/75">Menu 11 / Meetings</p>
            <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
              <div class="max-w-2xl">
                <h2 class="text-3xl font-semibold tracking-[-0.05em] text-white">Meeting hub untuk kickoff, client sync, dan review internal yang langsung turun jadi action items.</h2>
                <p class="mt-3 max-w-xl text-sm leading-6 text-stone-300">
                  Semua meeting bisa ditautkan ke project atau client, masuk ke Calendar, dan dipakai sebagai titik awal pembuatan task hasil diskusi.
                </p>
              </div>
              <div class="rounded-[1.4rem] border border-white/10 bg-white/10 px-4 py-3 backdrop-blur">
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-stone-300">Upcoming</p>
                <p class="mt-2 text-3xl font-semibold tracking-[-0.05em] text-white">{{ meetingSummary.upcoming_meetings }}</p>
                <p class="mt-1 text-sm text-stone-300">meeting yang masih aktif terjadwal</p>
              </div>
            </div>

            <div class="mt-6 grid gap-3 md:grid-cols-3">
              <div class="rounded-[1.4rem] border border-white/10 bg-white/10 p-4 backdrop-blur">
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-stone-300">Today</p>
                <p class="mt-2 text-2xl font-semibold text-white">{{ meetingSummary.today_meetings }}</p>
                <p class="mt-1 text-sm text-stone-300">agenda hari ini</p>
              </div>
              <div class="rounded-[1.4rem] border border-white/10 bg-white/10 p-4 backdrop-blur">
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-stone-300">Action Items</p>
                <p class="mt-2 text-2xl font-semibold text-white">{{ meetingSummary.action_items }}</p>
                <p class="mt-1 text-sm text-stone-300">task yang lahir dari meeting</p>
              </div>
              <div class="rounded-[1.4rem] border border-white/10 bg-white/10 p-4 backdrop-blur">
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-stone-300">Open Actions</p>
                <p class="mt-2 text-2xl font-semibold text-white">{{ meetingSummary.open_action_items }}</p>
                <p class="mt-1 text-sm text-stone-300">action item belum selesai</p>
              </div>
            </div>
          </div>
        </article>

        <article class="rounded-[2rem] border border-stone-200 bg-[linear-gradient(180deg,#f8fafc_0%,#ffffff_100%)] p-6 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Meeting posture</p>
          <div class="mt-5 space-y-4">
            <div class="rounded-[1.4rem] border border-stone-200 bg-white p-4">
              <p class="text-sm font-semibold text-stone-950">Calendar connected</p>
              <p class="mt-2 text-sm leading-6 text-stone-600">Semua meeting otomatis terbaca di Calendar workspace, jadi perubahan jadwal langsung tercermin di agenda utama.</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
              <div class="rounded-[1.4rem] border border-stone-200 bg-white p-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Completed</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ meetingSummary.completed_meetings }}</p>
              </div>
              <div class="rounded-[1.4rem] border border-stone-200 bg-white p-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Showing</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ meetingItems.length }}</p>
              </div>
            </div>
            <div class="rounded-[1.4rem] border border-dashed border-stone-300 bg-white px-4 py-4 text-sm leading-6 text-stone-600">
              Agenda, notes, recording link, peserta internal/external, dan action items disatukan supaya transisi dari ngobrol ke eksekusi tidak putus.
            </div>
          </div>
        </article>
      </section>

      <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-4">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Meeting Filters</p>
            <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Temukan sync yang relevan per project, client, dan status.</h2>
          </div>
          <div class="rounded-[1.3rem] border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-600">
            <span class="font-semibold text-stone-950">{{ meetingItems.length }}</span> meeting tampil
          </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
          <label class="space-y-2 text-sm xl:col-span-2">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Search</span>
            <input
              v-model="filterState.search"
              type="text"
              placeholder="Cari judul, agenda, notes, project, atau client"
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
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Client</span>
            <select v-model="filterState.client" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">All Clients</option>
              <option v-for="client in filterOptions.clients" :key="client.id" :value="client.id">{{ client.name }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
            <select v-model="filterState.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">All Status</option>
              <option v-for="status in filterOptions.statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
            </select>
          </label>
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

      <section class="grid gap-4 xl:grid-cols-[1.5fr_0.9fr]">
        <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Meeting Register</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Semua meeting terjadwal dan hasil turunannya.</h2>
            </div>
            <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">
              {{ meetingItems.length }} items
            </span>
          </div>

          <div class="mt-5 space-y-4">
            <article
              v-for="meeting in meetingItems"
              :key="meeting.id"
              class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5 transition hover:border-stone-300 hover:bg-white"
            >
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="max-w-2xl">
                  <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="openMeetingDetail(meeting.id)" class="text-left text-base font-semibold text-stone-950 transition hover:text-sky-700">
                      {{ meeting.title }}
                    </button>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="meetingStatusClass(meeting.status)">
                      {{ meeting.status_label }}
                    </span>
                    <span class="rounded-full bg-stone-200 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-stone-600">
                      Calendar linked
                    </span>
                  </div>
                  <p class="mt-3 text-sm leading-6 text-stone-600">{{ meeting.description || meeting.agenda || 'Belum ada deskripsi meeting.' }}</p>
                  <div class="mt-4 flex flex-wrap gap-2 text-xs text-stone-500">
                    <span class="rounded-full bg-white px-3 py-1.5">{{ meeting.project?.name || 'No project' }}</span>
                    <span class="rounded-full bg-white px-3 py-1.5">{{ meeting.client?.name || 'No client' }}</span>
                    <span class="rounded-full bg-white px-3 py-1.5">{{ meeting.participant_summary }}</span>
                  </div>
                </div>

                <div class="flex items-center gap-2">
                  <button type="button" @click="openMeetingModal(meeting)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                    <Pencil class="h-4 w-4" />
                  </button>
                  <button type="button" @click="openMeetingDetail(meeting.id)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                    <MessageSquareMore class="h-4 w-4" />
                  </button>
                  <button type="button" @click="deleteMeeting(meeting.id)" class="inline-flex items-center justify-center rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
                    <Trash2 class="h-4 w-4" />
                  </button>
                </div>
              </div>

              <div class="mt-5 grid gap-3 md:grid-cols-4">
                <div class="rounded-[1.1rem] border border-stone-200 bg-white p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Schedule</p>
                  <p class="mt-2 text-sm font-semibold text-stone-950">{{ meeting.scheduled_at_label || 'No schedule' }}</p>
                  <p class="mt-1 text-xs text-stone-500">{{ meeting.scheduled_at_human || 'Waiting schedule' }}</p>
                </div>
                <div class="rounded-[1.1rem] border border-stone-200 bg-white p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Duration</p>
                  <p class="mt-2 text-sm font-semibold text-stone-950">{{ meeting.duration_minutes || 0 }} min</p>
                  <p class="mt-1 text-xs text-stone-500">{{ meeting.counts.internal_attendees }} internal / {{ meeting.counts.external_attendees }} external</p>
                </div>
                <div class="rounded-[1.1rem] border border-stone-200 bg-white p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Action Items</p>
                  <p class="mt-2 text-sm font-semibold text-stone-950">{{ meeting.counts.action_items }} tasks</p>
                  <p class="mt-1 text-xs text-stone-500">{{ meeting.counts.open_action_items }} masih open</p>
                </div>
                <div class="rounded-[1.1rem] border border-stone-200 bg-white p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Links</p>
                  <div class="mt-2 space-y-1 text-xs text-stone-500">
                    <a v-if="meeting.meeting_url" :href="meeting.meeting_url" target="_blank" rel="noreferrer" class="block font-semibold text-sky-700 hover:text-sky-800">Open meeting URL</a>
                    <a v-if="meeting.recording_url" :href="meeting.recording_url" target="_blank" rel="noreferrer" class="block font-semibold text-amber-700 hover:text-amber-800">Open recording</a>
                    <span v-if="!meeting.meeting_url && !meeting.recording_url">Belum ada link meeting.</span>
                  </div>
                </div>
              </div>
            </article>

            <div v-if="meetingItems.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-16 text-center text-sm text-stone-500">
              Belum ada meeting yang cocok dengan filter saat ini.
            </div>
          </div>
        </article>

        <aside class="space-y-4">
          <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Execution signals</p>
            <div class="mt-5 space-y-4">
              <div class="rounded-[1.4rem] border border-stone-200 bg-stone-50 p-4">
                <p class="text-sm font-semibold text-stone-950">Action item pressure</p>
                <p class="mt-2 text-sm leading-6 text-stone-600">
                  {{ meetingSummary.open_action_items }} action item masih terbuka. Gunakan meeting notes untuk menjaga follow-up tetap tercatat.
                </p>
              </div>
              <div class="rounded-[1.4rem] border border-stone-200 bg-stone-50 p-4">
                <p class="text-sm font-semibold text-stone-950">Client sync coverage</p>
                <p class="mt-2 text-sm leading-6 text-stone-600">
                  {{ meetingsWithClientsCount }} meeting terkait client, jadi histori komunikasi penting tetap menempel ke account yang benar.
                </p>
              </div>
              <div class="rounded-[1.4rem] border border-stone-200 bg-stone-50 p-4">
                <p class="text-sm font-semibold text-stone-950">Project linkage</p>
                <p class="mt-2 text-sm leading-6 text-stone-600">
                  {{ meetingsWithProjectsCount }} meeting sudah tertaut ke project dan siap melahirkan task hasil diskusi.
                </p>
              </div>
            </div>
          </section>

          <section v-if="selectedMeetingPreview" class="rounded-[2rem] border border-stone-200 bg-stone-950 p-5 text-white shadow-[0_20px_60px_rgba(28,25,23,0.14)]">
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-sky-200/70">Selected preview</p>
            <h3 class="mt-3 text-lg font-semibold text-white">{{ selectedMeetingPreview.title }}</h3>
            <p class="mt-2 text-sm leading-6 text-stone-300">{{ selectedMeetingPreview.agenda || selectedMeetingPreview.description || 'Belum ada agenda meeting.' }}</p>
            <div class="mt-4 space-y-2 text-sm text-stone-300">
              <p>{{ selectedMeetingPreview.scheduled_at_label || 'No schedule' }}</p>
              <p>{{ selectedMeetingPreview.participant_summary }}</p>
              <p>{{ selectedMeetingPreview.counts.action_items }} action items</p>
            </div>
            <button type="button" @click="openMeetingDetail(selectedMeetingPreview.id)" class="mt-5 inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/15">
              <ArrowUpRight class="h-4 w-4" />
              <span>Open detail</span>
            </button>
          </section>
        </aside>
      </section>
    </div>

    <Transition name="modal">
      <div v-if="showMeetingModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="max-h-[92vh] w-full max-w-6xl overflow-y-auto rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Meeting Form</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditingMeeting ? 'Edit Meeting' : 'Schedule Meeting' }}</h3>
            </div>
            <button type="button" @click="closeMeetingModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
              <X class="h-5 w-5" />
            </button>
          </div>

          <form class="mt-6 space-y-6" @submit.prevent="submitMeeting">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Project</span>
                <select v-model="meetingForm.project_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">No project</option>
                  <option v-for="project in filterOptions.projects" :key="project.id" :value="project.id">{{ project.name }}</option>
                </select>
                <p v-if="meetingForm.errors.project_id" class="text-xs text-rose-500">{{ meetingForm.errors.project_id }}</p>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Client</span>
                <select v-model="meetingForm.client_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">No client</option>
                  <option v-for="client in filterOptions.clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm md:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Title</span>
                <input v-model="meetingForm.title" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                <p v-if="meetingForm.errors.title" class="text-xs text-rose-500">{{ meetingForm.errors.title }}</p>
              </label>

              <label class="space-y-2 text-sm md:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Description</span>
                <textarea v-model="meetingForm.description" rows="3" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Scheduled At</span>
                <input v-model="meetingForm.scheduled_at" type="datetime-local" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                <p v-if="meetingForm.errors.scheduled_at" class="text-xs text-rose-500">{{ meetingForm.errors.scheduled_at }}</p>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Duration</span>
                <input v-model="meetingForm.duration_minutes" type="number" min="15" step="15" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
                <select v-model="meetingForm.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option v-for="status in filterOptions.statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Meeting URL</span>
                <input v-model="meetingForm.meeting_url" type="url" placeholder="https://meet.google.com/..." class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm md:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Recording URL</span>
                <input v-model="meetingForm.recording_url" type="url" placeholder="https://drive.google.com/..." class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm md:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Agenda</span>
                <textarea v-model="meetingForm.agenda" rows="4" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
              </label>

              <label class="space-y-2 text-sm md:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Notes</span>
                <textarea v-model="meetingForm.notes" rows="4" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
              </label>

              <label class="space-y-2 text-sm md:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Internal Participants</span>
                <select v-model="meetingForm.internal_attendee_ids" multiple class="min-h-[140px] w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option v-for="user in filterOptions.users" :key="user.id" :value="user.id">{{ user.name }}</option>
                </select>
              </label>
            </div>

            <section class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">External Participants</p>
                  <p class="mt-1 text-sm text-stone-500">Tambahkan peserta dari client atau partner eksternal.</p>
                </div>
                <button type="button" @click="addExternalAttendee" class="inline-flex items-center gap-2 rounded-full border border-stone-200 bg-white px-3 py-2 text-xs font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                  <Plus class="h-3.5 w-3.5" />
                  <span>Add</span>
                </button>
              </div>

              <div class="mt-4 space-y-3">
                <div v-for="(attendee, index) in meetingForm.external_attendees" :key="`external-${index}`" class="grid gap-3 md:grid-cols-[1fr_1fr_auto]">
                  <input v-model="attendee.name" type="text" placeholder="Nama peserta" class="rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400" />
                  <input v-model="attendee.email" type="email" placeholder="email@client.com" class="rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400" />
                  <button type="button" @click="removeExternalAttendee(index)" class="rounded-2xl border border-rose-200 px-4 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">
                    Remove
                  </button>
                </div>
                <div v-if="meetingForm.external_attendees.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-200 bg-white px-4 py-8 text-center text-sm text-stone-500">
                  Belum ada peserta eksternal.
                </div>
              </div>
            </section>

            <section class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Action Items to Tasks</p>
                  <p class="mt-1 text-sm text-stone-500">Item di bawah ini akan dibuat sebagai task untuk project yang dipilih.</p>
                </div>
                <button type="button" @click="addActionItem" class="inline-flex items-center gap-2 rounded-full border border-stone-200 bg-stone-50 px-3 py-2 text-xs font-semibold text-stone-700 transition hover:border-stone-300 hover:bg-white hover:text-stone-950">
                  <Plus class="h-3.5 w-3.5" />
                  <span>Add Action Item</span>
                </button>
              </div>

              <div class="mt-4 space-y-3">
                <div v-for="(item, index) in meetingForm.action_items" :key="`action-${index}`" class="grid gap-3 xl:grid-cols-[1.4fr_0.8fr_0.8fr_0.8fr_auto]">
                  <input v-model="item.title" type="text" placeholder="Contoh: Kirim summary kickoff" class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                  <select v-model="item.assigned_to" class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                    <option value="">Unassigned</option>
                    <option v-for="user in filterOptions.users" :key="user.id" :value="user.id">{{ user.name }}</option>
                  </select>
                  <input v-model="item.due_date" type="datetime-local" class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                  <select v-model="item.priority" class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                    <option v-for="priority in filterOptions.priorities" :key="priority.value" :value="priority.value">{{ priority.label }}</option>
                  </select>
                  <button type="button" @click="removeActionItem(index)" class="rounded-2xl border border-rose-200 px-4 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">
                    Remove
                  </button>
                </div>
                <div v-if="meetingForm.action_items.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-200 bg-stone-50 px-4 py-8 text-center text-sm text-stone-500">
                  Belum ada action item baru.
                </div>
              </div>
            </section>

            <div class="flex flex-wrap items-center justify-end gap-3">
              <button type="button" @click="closeMeetingModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900">
                Cancel
              </button>
              <button type="submit" :disabled="meetingForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition-all hover:bg-stone-800 disabled:cursor-not-allowed disabled:opacity-60">
                {{ isEditingMeeting ? (meetingForm.processing ? 'Saving...' : 'Save Meeting') : (meetingForm.processing ? 'Scheduling...' : 'Schedule Meeting') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showDetailModal && selectedMeeting" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="max-h-[92vh] w-full max-w-5xl overflow-y-auto rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="relative overflow-hidden rounded-[1.8rem] bg-stone-950 p-6 text-white">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(14,165,233,0.22),transparent_24%),radial-gradient(circle_at_bottom_left,rgba(251,191,36,0.18),transparent_35%)]"></div>
            <div class="relative">
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="max-w-3xl">
                  <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-sky-200/70">Meeting Detail</p>
                  <h3 class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-white">{{ selectedMeeting.title }}</h3>
                  <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]" :class="meetingStatusClass(selectedMeeting.status)">
                      {{ selectedMeeting.status_label }}
                    </span>
                    <span class="rounded-full bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-white">
                      {{ selectedMeeting.duration_minutes || 0 }} min
                    </span>
                    <span class="rounded-full bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-white">
                      {{ selectedMeeting.participant_summary }}
                    </span>
                  </div>
                  <div class="mt-4 flex flex-wrap gap-3 text-sm text-stone-300">
                    <span>{{ selectedMeeting.project?.name || 'No project' }}</span>
                    <span>{{ selectedMeeting.client?.name || 'No client' }}</span>
                    <span>{{ selectedMeeting.scheduled_at_label || 'No schedule' }}</span>
                  </div>
                </div>

                <div class="flex items-center gap-2">
                  <button type="button" @click="openMeetingModal(selectedMeeting)" class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/15">
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

          <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_1fr]">
            <section class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Agenda & Notes</p>
              <div class="mt-4 space-y-4">
                <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-xs font-bold uppercase tracking-[0.18em] text-stone-400">Agenda</p>
                  <p class="mt-2 text-sm leading-6 text-stone-700">{{ selectedMeeting.agenda || 'Belum ada agenda meeting.' }}</p>
                </div>
                <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-xs font-bold uppercase tracking-[0.18em] text-stone-400">Notes</p>
                  <p class="mt-2 whitespace-pre-wrap text-sm leading-6 text-stone-700">{{ selectedMeeting.notes || 'Belum ada notes meeting.' }}</p>
                </div>
              </div>
            </section>

            <section class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Participants</p>
              <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-xs font-bold uppercase tracking-[0.18em] text-stone-400">Internal</p>
                  <div class="mt-3 space-y-2 text-sm text-stone-700">
                    <p v-for="attendee in selectedMeeting.internal_attendees" :key="attendee.id">{{ attendee.name }}</p>
                    <p v-if="selectedMeeting.internal_attendees.length === 0">Belum ada peserta internal.</p>
                  </div>
                </div>
                <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-xs font-bold uppercase tracking-[0.18em] text-stone-400">External</p>
                  <div class="mt-3 space-y-2 text-sm text-stone-700">
                    <p v-for="(attendee, index) in selectedMeeting.external_attendees" :key="`detail-external-${index}`">
                      {{ attendee.name || attendee.email }}
                      <span v-if="attendee.name && attendee.email" class="text-stone-500">({{ attendee.email }})</span>
                    </p>
                    <p v-if="selectedMeeting.external_attendees.length === 0">Belum ada peserta eksternal.</p>
                  </div>
                </div>
              </div>

              <div class="mt-4 grid gap-3 md:grid-cols-2">
                <a v-if="selectedMeeting.meeting_url" :href="selectedMeeting.meeting_url" target="_blank" rel="noreferrer" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:bg-white hover:text-stone-950">
                  <Video class="h-4 w-4" />
                  <span>Open Meeting URL</span>
                </a>
                <a v-if="selectedMeeting.recording_url" :href="selectedMeeting.recording_url" target="_blank" rel="noreferrer" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:bg-white hover:text-stone-950">
                  <Link2 class="h-4 w-4" />
                  <span>Open Recording</span>
                </a>
              </div>
            </section>
          </div>

          <section class="mt-6 rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Action Item Tasks</p>
                <p class="mt-1 text-sm text-stone-500">Task hasil meeting ini sekarang berada di alur eksekusi project.</p>
              </div>
              <a :href="`/w/${workspace.slug}/tasks`" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                <ArrowUpRight class="h-4 w-4" />
                <span>Open Tasks</span>
              </a>
            </div>

            <div class="mt-4 space-y-3">
              <article v-for="task in selectedMeeting.action_items" :key="task.id" class="rounded-[1.2rem] border border-stone-200 bg-white p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                  <div>
                    <p class="text-sm font-semibold text-stone-950">{{ task.title }}</p>
                    <p class="mt-2 text-xs text-stone-500">{{ task.assignee?.name || 'Unassigned' }} / {{ task.due_date_label || 'No due date' }}</p>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em]" :class="taskStatusClass(task.status)">
                      {{ task.status_label }}
                    </span>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em]" :class="priorityClass(task.priority)">
                      {{ task.priority_label }}
                    </span>
                  </div>
                </div>
              </article>
              <div v-if="selectedMeeting.action_items.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-200 bg-white px-4 py-10 text-center text-sm text-stone-500">
                Belum ada action item task untuk meeting ini.
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
import dayjs from 'dayjs'
import {
  ArrowUpRight,
  Filter,
  Link2,
  MessageSquareMore,
  Pencil,
  Plus,
  RotateCcw,
  Trash2,
  Video,
  X,
} from 'lucide-vue-next'
import WorkspaceLayout from '../../Layouts/WorkspaceLayout.vue'

const props = defineProps({
  workspace: { type: Object, required: true },
  meetings: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  filterOptions: { type: Object, required: true },
})

const meetingsBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}/meetings`

const localMeetings = ref(cloneMeetings(props.meetings.items || []))
const filterState = ref(buildFilterState(props.filters))
const showMeetingModal = ref(false)
const showDetailModal = ref(false)
const editingMeetingId = ref(null)
const selectedMeetingId = ref(props.meetings.selected_id || null)

const meetingForm = useForm({
  project_id: '',
  client_id: '',
  title: '',
  description: '',
  agenda: '',
  notes: '',
  meeting_url: '',
  recording_url: '',
  scheduled_at: '',
  duration_minutes: 60,
  status: 'scheduled',
  internal_attendee_ids: [],
  external_attendees: [],
  action_items: [],
})

const isEditingMeeting = computed(() => Boolean(editingMeetingId.value))
const meetingItems = computed(() => localMeetings.value)
const meetingSummary = computed(() => props.meetings.summary)
const selectedMeeting = computed(() => meetingItems.value.find((meeting) => meeting.id === selectedMeetingId.value) || null)
const selectedMeetingPreview = computed(() => selectedMeeting.value || meetingItems.value[0] || null)
const meetingsWithClientsCount = computed(() => meetingItems.value.filter((meeting) => meeting.client).length)
const meetingsWithProjectsCount = computed(() => meetingItems.value.filter((meeting) => meeting.project).length)

watch(
  () => props.meetings.items,
  (items) => {
    localMeetings.value = cloneMeetings(items || [])
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
  () => props.meetings.selected_id,
  (meetingId) => {
    if (!meetingId) {
      return
    }

    selectedMeetingId.value = meetingId
    showDetailModal.value = true
  },
  { immediate: true },
)

function buildFilterState(filters = {}) {
  return {
    search: filters.search ?? '',
    project: filters.project ?? '',
    client: filters.client ?? '',
    status: filters.status ?? '',
  }
}

function applyFilters() {
  router.get(meetingsBaseUrl, compactFilters(filterState.value), {
    preserveState: true,
    preserveScroll: true,
  })
}

function resetFilters() {
  filterState.value = buildFilterState()

  router.get(meetingsBaseUrl, {}, {
    preserveState: true,
    preserveScroll: true,
  })
}

function openMeetingModal(meeting = null) {
  editingMeetingId.value = meeting?.id || null
  meetingForm.reset()
  meetingForm.clearErrors()
  meetingForm.project_id = meeting?.project_id || ''
  meetingForm.client_id = meeting?.client_id || ''
  meetingForm.title = meeting?.title || ''
  meetingForm.description = meeting?.description || ''
  meetingForm.agenda = meeting?.agenda || ''
  meetingForm.notes = meeting?.notes || ''
  meetingForm.meeting_url = meeting?.meeting_url || ''
  meetingForm.recording_url = meeting?.recording_url || ''
  meetingForm.scheduled_at = meeting?.scheduled_at ? toDateTimeLocal(meeting.scheduled_at) : ''
  meetingForm.duration_minutes = meeting?.duration_minutes || 60
  meetingForm.status = meeting?.status || 'scheduled'
  meetingForm.internal_attendee_ids = meeting?.internal_attendees?.map((attendee) => attendee.id) || []
  meetingForm.external_attendees = meeting?.external_attendees?.length ? cloneExternalAttendees(meeting.external_attendees) : []
  meetingForm.action_items = []
  showDetailModal.value = false
  showMeetingModal.value = true
}

function closeMeetingModal() {
  showMeetingModal.value = false
  editingMeetingId.value = null
  meetingForm.reset()
  meetingForm.clearErrors()
  meetingForm.duration_minutes = 60
  meetingForm.status = 'scheduled'
  meetingForm.internal_attendee_ids = []
  meetingForm.external_attendees = []
  meetingForm.action_items = []
}

function submitMeeting() {
  const options = {
    preserveScroll: true,
    onSuccess: () => {
      closeMeetingModal()
    },
  }

  if (isEditingMeeting.value) {
    meetingForm.patch(`${meetingsBaseUrl}/${encodeURIComponent(editingMeetingId.value)}`, options)
    return
  }

  meetingForm.post(meetingsBaseUrl, options)
}

function deleteMeeting(meetingId) {
  if (!confirm('Delete this meeting?')) {
    return
  }

  router.delete(`${meetingsBaseUrl}/${encodeURIComponent(meetingId)}`, {
    preserveScroll: true,
    onSuccess: () => {
      if (selectedMeetingId.value === meetingId) {
        closeDetailModal()
      }
    },
  })
}

function openMeetingDetail(meetingId) {
  selectedMeetingId.value = meetingId
  showDetailModal.value = true
}

function closeDetailModal() {
  showDetailModal.value = false
  selectedMeetingId.value = null
}

function addExternalAttendee() {
  meetingForm.external_attendees.push({
    name: '',
    email: '',
  })
}

function removeExternalAttendee(index) {
  meetingForm.external_attendees.splice(index, 1)
}

function addActionItem() {
  meetingForm.action_items.push({
    title: '',
    assigned_to: '',
    due_date: '',
    priority: 'medium',
  })
}

function removeActionItem(index) {
  meetingForm.action_items.splice(index, 1)
}

function meetingStatusClass(status) {
  const map = {
    scheduled: 'bg-sky-100 text-sky-700',
    completed: 'bg-emerald-100 text-emerald-700',
    cancelled: 'bg-rose-100 text-rose-700',
  }

  return map[status] || 'bg-stone-100 text-stone-600'
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

function compactFilters(filters) {
  return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== '' && value !== null && value !== undefined))
}

function cloneMeetings(items) {
  return items.map((meeting) => ({
    ...meeting,
    internal_attendees: Array.isArray(meeting.internal_attendees) ? meeting.internal_attendees.map((attendee) => ({ ...attendee })) : [],
    external_attendees: Array.isArray(meeting.external_attendees) ? cloneExternalAttendees(meeting.external_attendees) : [],
    action_items: Array.isArray(meeting.action_items) ? meeting.action_items.map((task) => ({ ...task, assignee: task.assignee ? { ...task.assignee } : null })) : [],
    counts: meeting.counts ? { ...meeting.counts } : {},
    project: meeting.project ? { ...meeting.project } : null,
    client: meeting.client ? { ...meeting.client } : null,
  }))
}

function cloneExternalAttendees(items) {
  return items.map((attendee) => ({
    name: attendee.name || '',
    email: attendee.email || '',
  }))
}

function toDateTimeLocal(value) {
  return dayjs(value).format('YYYY-MM-DDTHH:mm')
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
