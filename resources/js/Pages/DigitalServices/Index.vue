<template>
  <WorkspaceLayout
    title="Infrastruktur Digital"
    subtitle="Pusat kontrol aset teknis klien: situs, domain, server, dan formulir otomatis."
  >
    <template #actions>
      <button
        type="button"
        @click="openActiveModal()"
        class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
      >
        <Plus class="h-4 w-4" />
        <span>{{ activeMeta.actionLabel }}</span>
      </button>
    </template>

    <DigitalServicesLayout :workspace="workspace">
      <div class="space-y-6 pb-12">
        <section class="project-hero-shell">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="project-hero-copy">
              <p class="project-hero-kicker">{{ activeMeta.label }}</p>
              <h2 class="project-hero-title">{{ activeMeta.headline }}</h2>
              <p class="project-hero-desc">{{ activeMeta.copy }}</p>
            </div>

            <div class="compact-stat-grid min-w-full gap-3 sm:min-w-[22rem] sm:grid-cols-4 xl:w-[34rem]">
              <div v-for="card in activeStatCards" :key="card.label" class="compact-stat-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">{{ card.label }}</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ card.value }}</p>
                <p v-if="card.note" class="mt-1 text-xs leading-5 text-stone-500">{{ card.note }}</p>
              </div>
            </div>
          </div>
        </section>

        <section class="rounded-[2.2rem] border border-stone-200 bg-white p-6 shadow-sm">
          <div class="grid gap-4 lg:grid-cols-[1fr_220px_180px]">
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Cari Data</span>
              <div class="relative">
                <Search class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" />
                <input
                  v-model="filterState.search"
                  type="text"
                  class="w-full rounded-2xl border border-stone-200 bg-stone-50 py-3 pl-11 pr-4 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
                />
              </div>
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
              <select
                v-model="filterState.status"
                class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
              >
                <option value="">Semua Status</option>
                <option v-for="item in statusOptions" :key="item" :value="item">{{ formatOption(item) }}</option>
              </select>
            </label>

            <div class="flex items-end">
              <button
                type="button"
                @click="resetFilters"
                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-50 hover:text-stone-950"
              >
                <RotateCcw class="h-4 w-4" />
                <span>Atur Ulang</span>
              </button>
            </div>
          </div>
        </section>

        <section class="grid gap-6">
          <div v-if="activeTab === 'websites'" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <article
              v-for="website in filteredWebsites"
              :key="website.id"
              class="group rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm transition-all hover:border-amber-300 hover:shadow-xl hover:shadow-amber-900/5"
            >
              <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                  <div class="flex items-center gap-2">
                    <h4 class="max-w-[210px] truncate font-bold text-stone-950">{{ website.name }}</h4>
                    <span class="rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-widest" :class="websiteStatusClass(website.status)">
                      {{ formatOption(website.status) }}
                    </span>
                  </div>
                  <a :href="website.url" target="_blank" class="text-xs font-medium text-amber-700 hover:underline">{{ website.url }}</a>
                </div>
                <div class="flex gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                  <button type="button" @click="openWebsiteModal(website)" class="p-2 text-stone-400 hover:text-stone-900">
                    <Pencil class="h-4 w-4" />
                  </button>
                  <button type="button" @click="deleteWebsite(website.id)" class="p-2 text-stone-400 hover:text-rose-600">
                    <Trash2 class="h-4 w-4" />
                  </button>
                </div>
              </div>

              <div class="mt-6 grid grid-cols-2 gap-3">
                <div class="rounded-2xl border border-stone-100 bg-stone-50 p-3">
                  <p class="mb-1 text-[9px] font-bold uppercase tracking-wider text-stone-400">Stack</p>
                  <p class="text-xs font-bold text-stone-800">{{ website.cms || '-' }}</p>
                  <p class="text-[10px] text-stone-500">PHP {{ website.php_version || '-' }}</p>
                </div>
                <div class="rounded-2xl border border-stone-100 bg-stone-50 p-3">
                  <p class="mb-1 text-[9px] font-bold uppercase tracking-wider text-stone-400">SSL</p>
                  <p class="text-xs font-bold" :class="website.ssl_enabled ? 'text-emerald-600' : 'text-rose-600'">
                    {{ website.ssl_enabled ? 'Aktif' : 'Nonaktif' }}
                  </p>
                  <p class="text-[10px] text-stone-500">{{ website.ssl_expiry_label }}</p>
                </div>
              </div>

              <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <div class="rounded-2xl border border-stone-100 bg-white p-3">
                  <p class="text-[9px] font-bold uppercase tracking-wider text-stone-400">Klien</p>
                  <p class="mt-1 text-sm font-semibold text-stone-900">{{ website.client?.name || '-' }}</p>
                </div>
                <div class="rounded-2xl border border-stone-100 bg-white p-3">
                  <p class="text-[9px] font-bold uppercase tracking-wider text-stone-400">Server</p>
                  <p class="mt-1 text-sm font-semibold text-stone-900">{{ website.server?.name || '-' }}</p>
                </div>
              </div>

              <div class="mt-4 flex items-center justify-between border-t border-stone-100 pt-4">
                <div class="flex items-center gap-2">
                  <div class="h-1.5 w-1.5 rounded-full bg-emerald-500"></div>
                  <span class="text-[11px] font-bold uppercase tracking-widest text-stone-500">Uptime {{ website.uptime_percentage || 0 }}%</span>
                </div>
                <span class="text-[10px] italic text-stone-400">Update {{ website.updated_at_label }}</span>
              </div>
            </article>

            <EmptyState v-if="filteredWebsites.length === 0" copy="Belum ada website yang terdaftar." />
          </div>

          <div v-else-if="activeTab === 'domains'" class="space-y-3">
            <article
              v-for="domain in filteredDomains"
              :key="domain.id"
              class="group rounded-[1.8rem] border border-stone-200 bg-white p-5 transition-all hover:border-stone-300 hover:bg-stone-50 lg:grid lg:grid-cols-[1.2fr_180px_180px_1fr_100px] lg:items-start lg:gap-6"
            >
              <div class="space-y-2">
                <div class="flex items-center gap-2">
                  <p class="text-lg font-bold text-stone-950">{{ domain.domain_name }}</p>
                  <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest" :class="domainStatusClass(domain.status)">
                    {{ formatOption(domain.status) }}
                  </span>
                </div>
                <p class="text-xs text-stone-500">{{ domain.registrar || '-' }}</p>
                <div v-if="domain.dns_records && domain.dns_records.length" class="flex flex-wrap gap-2">
                  <span
                    v-for="record in domain.dns_records.slice(0, 3)"
                    :key="record"
                    class="rounded-full border border-stone-200 bg-stone-50 px-2.5 py-1 text-[10px] font-semibold text-stone-600"
                  >
                    {{ record }}
                  </span>
                </div>
              </div>

              <div>
                <p class="mb-1 text-[10px] font-bold uppercase tracking-widest text-stone-400">Tanggal Daftar</p>
                <p class="text-sm font-semibold text-stone-900">{{ domain.registration_date_label }}</p>
              </div>

              <div>
                <p class="mb-1 text-[10px] font-bold uppercase tracking-widest text-stone-400">Kedaluwarsa</p>
                <p class="text-sm font-semibold" :class="domain.status === 'expiring' || domain.status === 'expired' ? 'text-rose-600' : 'text-stone-900'">
                  {{ domain.expiry_date_label }}
                </p>
              </div>

              <div class="space-y-2">
                <div>
                  <p class="mb-1 text-[10px] font-bold uppercase tracking-widest text-stone-400">Perpanjangan</p>
                  <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest" :class="domain.auto_renew ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'">
                    {{ domain.auto_renew ? 'Otomatis' : 'Manual' }}
                  </span>
                </div>
                <p class="text-xs text-stone-500">{{ domain.websites_count }} situs terhubung</p>
              </div>

              <div class="flex justify-end gap-2">
                <button type="button" @click="openDomainModal(domain)" class="p-2 text-stone-400 hover:text-stone-900">
                  <Pencil class="h-4 w-4" />
                </button>
                <button type="button" @click="deleteDomain(domain.id)" class="p-2 text-stone-400 hover:text-rose-600">
                  <Trash2 class="h-4 w-4" />
                </button>
              </div>
            </article>

            <EmptyState v-if="filteredDomains.length === 0" copy="Belum ada domain yang terdaftar." />
          </div>

          <div v-else-if="activeTab === 'servers'" class="grid gap-6 md:grid-cols-2">
            <article
              v-for="server in filteredServers"
              :key="server.id"
              class="group overflow-hidden rounded-[2.4rem] border border-stone-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl"
            >
              <div class="bg-stone-950 p-6 text-white">
                <div class="flex items-start justify-between gap-4">
                  <div class="space-y-1">
                    <div class="flex items-center gap-3">
                      <Server class="h-5 w-5 text-amber-300" />
                      <h4 class="text-xl font-bold tracking-tight">{{ server.name }}</h4>
                    </div>
                    <p class="text-xs text-stone-400">{{ server.provider || '-' }} | {{ server.location || '-' }}</p>
                  </div>
                  <div class="flex flex-col items-end gap-2">
                    <span class="rounded-full bg-white/10 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-white">
                      {{ formatOption(server.type) }}
                    </span>
                    <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest" :class="serverStatusClass(server.status)">
                      {{ formatOption(server.status) }}
                    </span>
                  </div>
                </div>

                <div class="mt-8 grid grid-cols-2 gap-6">
                  <div class="space-y-1">
                    <p class="text-[9px] font-bold uppercase tracking-[0.2em] text-stone-500">Alamat IP</p>
                    <div class="flex items-center gap-2 group/ip">
                      <p class="text-base font-mono font-bold text-amber-200">{{ server.ip_address || '-' }}:{{ server.ssh_port || '-' }}</p>
                      <button type="button" @click="copyToClipboard(server.ip_address)" class="opacity-0 transition-opacity group-hover/ip:opacity-100">
                        <Copy class="h-3 w-3 text-stone-400 hover:text-white" />
                      </button>
                    </div>
                  </div>
                  <div class="space-y-1">
                    <p class="text-[9px] font-bold uppercase tracking-[0.2em] text-stone-500">Sistem Operasi</p>
                    <p class="text-base font-bold text-white">{{ server.os || 'Linux' }}</p>
                  </div>
                </div>
              </div>

              <div class="space-y-6 p-6">
                <div class="rounded-3xl border border-stone-100 bg-stone-50 p-5">
                  <div class="mb-4 flex items-center justify-between">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-stone-400">Kredensial Utama</p>
                    <button type="button" @click="toggleServerCreds(server.id)" class="text-[10px] font-bold uppercase tracking-widest text-amber-700 transition-colors hover:text-amber-900">
                      {{ visibleCreds === server.id ? 'Sembunyikan' : 'Lihat Password' }}
                    </button>
                  </div>
                  <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1">
                      <p class="text-[9px] font-medium text-stone-400">User SSH</p>
                      <p class="text-sm font-mono font-bold text-stone-800">{{ server.ssh_user || '-' }}</p>
                    </div>
                    <div class="space-y-1">
                      <p class="text-[9px] font-medium text-stone-400">Password SSH</p>
                      <p class="text-sm font-mono font-bold text-stone-800">
                        {{ visibleCreds === server.id ? (server.ssh_password || 'n/a') : '************' }}
                      </p>
                    </div>
                  </div>
                </div>

                <div v-if="server.control_panel_url" class="rounded-3xl border border-amber-100 bg-amber-50/60 p-5">
                  <div class="mb-4 flex items-center justify-between">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-amber-700">Akses Panel</p>
                    <a
                      :href="server.control_panel_url"
                      target="_blank"
                      class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-white px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-amber-700 transition hover:bg-amber-100"
                    >
                      <ExternalLink class="h-3 w-3" />
                      <span>Buka Panel</span>
                    </a>
                  </div>
                  <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1">
                      <p class="text-[9px] font-medium text-amber-700/70">Email</p>
                      <p class="text-sm font-semibold text-stone-800">{{ server.dokploy_email || '-' }}</p>
                    </div>
                    <div class="space-y-1">
                      <p class="text-[9px] font-medium text-amber-700/70">Username</p>
                      <p class="text-sm font-mono font-bold text-stone-800">{{ server.dokploy_username || '-' }}</p>
                    </div>
                    <div class="space-y-1 sm:col-span-2">
                      <p class="text-[9px] font-medium text-amber-700/70">Password</p>
                      <p class="text-sm font-mono font-bold text-stone-800">
                        {{ visibleCreds === server.id ? (server.dokploy_password || 'n/a') : '********' }}
                      </p>
                    </div>
                  </div>
                </div>

                <div v-if="server.credentials && server.credentials.length" class="space-y-3">
                  <p class="px-1 text-[10px] font-bold uppercase tracking-widest text-stone-400">Kredensial Tambahan</p>
                  <div v-for="cred in server.credentials" :key="cred.id" class="rounded-2xl border border-stone-100 p-4">
                    <p class="mb-2 text-[10px] font-bold text-stone-400">{{ cred.service_name }}</p>
                    <div class="grid gap-4 sm:grid-cols-2">
                      <p class="truncate text-xs font-mono font-bold text-stone-800">{{ cred.username || '-' }}</p>
                      <p class="text-xs font-mono font-bold text-stone-800">
                        {{ visibleCreds === server.id ? (cred.password || 'n/a') : '********' }}
                      </p>
                    </div>
                  </div>
                </div>

                <div>
                  <p class="mb-4 px-1 text-[10px] font-bold uppercase tracking-widest text-stone-400">Spesifikasi Hardware</p>
                  <div class="grid gap-3 sm:grid-cols-3">
                    <div v-for="(value, key) in server.specs" :key="key" class="rounded-2xl border border-stone-100 bg-white p-3 text-center">
                      <p class="mb-1 text-[9px] font-bold uppercase tracking-wider text-stone-400">{{ formatSpecKey(key) }}</p>
                      <p class="text-xs font-bold text-stone-900">{{ value }}</p>
                    </div>
                    <div v-if="!Object.keys(server.specs || {}).length" class="rounded-2xl bg-stone-50 py-4 text-center text-xs italic text-stone-400 sm:col-span-3">
                      Belum ada detail spesifikasi.
                    </div>
                  </div>
                </div>

                <div class="flex items-center justify-between border-t border-stone-100 pt-4">
                  <div class="flex items-center gap-2">
                    <component :is="server.websites_count > 0 ? AppWindowMac : Info" class="h-3 w-3 text-stone-400" />
                    <span class="text-[11px] font-bold uppercase tracking-wider text-stone-500">{{ server.websites_count }} situs terhubung</span>
                  </div>
                  <div class="flex gap-1">
                    <button type="button" @click="openServerModal(server)" class="p-2 text-stone-400 hover:text-stone-900">
                      <Pencil class="h-4 w-4" />
                    </button>
                    <button type="button" @click="deleteServer(server.id)" class="p-2 text-stone-400 hover:text-rose-600">
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </div>
              </div>
            </article>

            <EmptyState v-if="filteredServers.length === 0" copy="Belum ada inventaris server." />
          </div>

          <div v-else-if="activeTab === 'forms'" class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <div class="grid gap-6 lg:grid-cols-2">
              <article
                v-for="form in filteredForms"
                :key="form.id"
                class="flex flex-col overflow-hidden rounded-[2rem] border border-stone-200 bg-white shadow-sm"
              >
                <div class="flex-1 p-6">
                  <div class="mb-6 flex items-start justify-between gap-4">
                    <div class="space-y-1">
                      <h4 class="text-xl font-bold text-stone-950">{{ form.name }}</h4>
                      <p class="text-xs text-stone-500">Redirect: {{ form.success_redirect_url || '-' }}</p>
                    </div>
                    <div class="flex gap-1">
                      <button type="button" @click="openFormModal(form)" class="p-2 text-stone-400 hover:text-stone-900">
                        <Pencil class="h-4 w-4" />
                      </button>
                      <button type="button" @click="deleteForm(form.id)" class="p-2 text-stone-400 hover:text-rose-600">
                        <Trash2 class="h-4 w-4" />
                      </button>
                    </div>
                  </div>

                  <div class="space-y-4">
                    <div class="rounded-3xl border border-stone-100 bg-stone-50 p-4">
                      <p class="mb-3 text-[10px] font-bold uppercase tracking-widest text-stone-400">Struktur Field</p>
                      <div class="flex flex-wrap gap-2">
                        <span
                          v-for="field in form.fields"
                          :key="field.name"
                          class="rounded-full border border-stone-200 bg-white px-3 py-1 text-[10px] font-bold text-stone-600"
                        >
                          {{ field.label }}
                        </span>
                      </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                      <div class="rounded-3xl border border-stone-100 bg-stone-50 p-4 text-center">
                        <p class="mb-1 text-[9px] font-bold uppercase tracking-widest text-stone-400">Total Kiriman</p>
                        <p class="text-2xl font-bold text-stone-950">{{ form.submission_count }}</p>
                      </div>
                      <div class="rounded-3xl border border-stone-100 bg-stone-50 p-4 text-center">
                        <p class="mb-1 text-[9px] font-bold uppercase tracking-widest text-stone-400">Prospek Otomatis</p>
                        <p class="text-sm font-bold" :class="form.auto_create_lead ? 'text-emerald-600' : 'text-stone-400'">
                          {{ form.auto_create_lead ? 'Aktif' : 'Nonaktif' }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="border-t border-stone-100 bg-stone-50 p-4">
                  <p class="mb-2 px-2 text-[10px] font-bold uppercase tracking-widest text-stone-400">Embed Code</p>
                  <div class="group/embed relative">
                    <pre class="scrollbar-none overflow-x-auto rounded-2xl bg-stone-900 p-4 font-mono text-[10px] text-amber-200">{{ form.embed_code || 'Belum ada kode embed' }}</pre>
                    <button
                      type="button"
                      @click="copyToClipboard(form.embed_code)"
                      class="absolute right-4 top-4 rounded-xl bg-white/10 p-2 text-white opacity-0 transition-opacity hover:bg-white/20 group-hover/embed:opacity-100"
                    >
                      <Copy class="h-3.5 w-3.5" />
                    </button>
                  </div>
                </div>
              </article>

              <EmptyState v-if="filteredForms.length === 0" copy="Belum ada formulir otomatis." />
            </div>

            <aside class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-sm">
              <div class="border-b border-stone-100 pb-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Kiriman Terbaru</p>
                <h3 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Alur masuk prospek</h3>
              </div>

              <div class="mt-5 space-y-3">
                <article
                  v-for="submission in formSubmissions"
                  :key="submission.id"
                  class="rounded-[1.3rem] border border-stone-200 bg-stone-50 p-4"
                >
                  <div class="flex items-start justify-between gap-3">
                    <div>
                      <p class="text-sm font-semibold text-stone-900">{{ submission.lead?.name || 'Prospek baru' }}</p>
                      <p class="mt-1 text-xs text-stone-500">{{ submission.data_preview || '-' }}</p>
                    </div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-stone-400">{{ submission.submitted_at_label }}</p>
                  </div>
                </article>

                <div v-if="!formSubmissions.length" class="rounded-[1.3rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-10 text-center text-sm italic text-stone-500">
                  Belum ada kiriman formulir.
                </div>
              </div>
            </aside>
          </div>
        </section>
      </div>

      <Transition name="modal">
        <div v-if="showWebsiteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 p-4 backdrop-blur-sm">
          <div class="scrollbar-none max-h-[92vh] w-full max-w-4xl overflow-y-auto rounded-[2.5rem] bg-white p-6 shadow-2xl">
            <ModalHeader :title="editingWebsiteId ? 'Ubah Website' : 'Daftarkan Website'" @close="closeWebsiteModal" />
            <form class="mt-8 space-y-8" @submit.prevent="submitWebsite">
              <section class="space-y-4">
                <SectionTitle title="Identitas Situs" />
                <div class="grid gap-4 md:grid-cols-2">
                  <FormField label="Nama Website">
                    <input v-model="websiteForm.name" type="text" class="form-control" />
                  </FormField>
                  <FormField label="URL Website">
                    <input v-model="websiteForm.url" type="text" class="form-control" />
                  </FormField>
                </div>
              </section>

              <section class="space-y-4">
                <SectionTitle title="Relasi Infrastruktur" />
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                  <FormField label="Klien">
                    <select v-model="websiteForm.client_id" class="form-control">
                      <option value="">Pilih Klien</option>
                      <option v-for="item in options.clients" :key="item.id" :value="item.id">{{ item.name }}</option>
                    </select>
                  </FormField>
                  <FormField label="Proyek">
                    <select v-model="websiteForm.project_id" class="form-control">
                      <option value="">Pilih Proyek</option>
                      <option v-for="item in options.projects" :key="item.id" :value="item.id">{{ item.name }}</option>
                    </select>
                  </FormField>
                  <FormField label="Server">
                    <select v-model="websiteForm.server_id" class="form-control">
                      <option value="">Pilih Server</option>
                      <option v-for="item in options.servers" :key="item.id" :value="item.id">{{ item.name }}</option>
                    </select>
                  </FormField>
                  <FormField label="Domain">
                    <select v-model="websiteForm.domain_id" class="form-control">
                      <option value="">Pilih Domain</option>
                      <option v-for="item in options.domains" :key="item.id" :value="item.id">{{ item.name }}</option>
                    </select>
                  </FormField>
                </div>
              </section>

              <section class="space-y-4">
                <SectionTitle title="Teknis dan Keamanan" />
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                  <FormField label="CMS">
                    <select v-model="websiteForm.cms" class="form-control">
                      <option value="">Pilih CMS</option>
                      <option v-for="item in options.cms || []" :key="item" :value="item">{{ item }}</option>
                    </select>
                  </FormField>
                  <FormField label="Versi PHP">
                    <select v-model="websiteForm.php_version" class="form-control">
                      <option value="">Pilih Versi</option>
                      <option v-for="item in options.phpVersions || []" :key="item" :value="item">{{ item }}</option>
                    </select>
                  </FormField>
                  <FormField label="Status">
                    <select v-model="websiteForm.status" class="form-control">
                      <option v-for="item in ['live', 'staging', 'maintenance', 'down']" :key="item" :value="item">{{ formatOption(item) }}</option>
                    </select>
                  </FormField>
                  <FormField label="Kedaluwarsa SSL">
                    <input v-model="websiteForm.ssl_expiry_date" type="date" class="form-control" />
                  </FormField>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                  <ToggleCard
                    v-model="websiteForm.ssl_enabled"
                    title="SSL Aktif"
                    copy="Nyalakan jika sertifikat keamanan situs sudah aktif."
                  />
                  <FormField label="Uptime (%)">
                    <input v-model="websiteForm.uptime_percentage" type="number" min="0" max="100" class="form-control" />
                  </FormField>
                </div>
              </section>

              <ModalActions :processing="websiteForm.processing" :editing="Boolean(editingWebsiteId)" @cancel="closeWebsiteModal" />
            </form>
          </div>
        </div>
      </Transition>

      <Transition name="modal">
        <div v-if="showDomainModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 p-4 backdrop-blur-sm">
          <div class="scrollbar-none max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-[2.5rem] bg-white p-6 shadow-2xl">
            <ModalHeader :title="editingDomainId ? 'Ubah Data Domain' : 'Daftarkan Domain'" @close="closeDomainModal" />
            <form class="mt-8 space-y-8" @submit.prevent="submitDomain">
              <section class="space-y-4">
                <SectionTitle title="Data Dasar Domain" />
                <div class="grid gap-4 md:grid-cols-2">
                  <FormField label="Nama Domain">
                    <input v-model="domainForm.domain_name" type="text" class="form-control" />
                  </FormField>
                  <FormField label="Registrar">
                    <select v-model="domainForm.registrar" class="form-control">
                      <option value="">Pilih Registrar</option>
                      <option v-for="item in options.registrars || []" :key="item" :value="item">{{ item }}</option>
                    </select>
                  </FormField>
                  <FormField label="Tanggal Daftar">
                    <input v-model="domainForm.registration_date" type="date" class="form-control" />
                  </FormField>
                  <FormField label="Tanggal Kedaluwarsa">
                    <input v-model="domainForm.expiry_date" type="date" class="form-control" />
                  </FormField>
                  <FormField label="Status Domain">
                    <select v-model="domainForm.status" class="form-control">
                      <option v-for="item in ['active', 'expiring', 'expired']" :key="item" :value="item">{{ formatOption(item) }}</option>
                    </select>
                  </FormField>
                  <ToggleCard
                    v-model="domainForm.auto_renew"
                    title="Perpanjangan Otomatis"
                    copy="Aktifkan jika registrar memperpanjang domain otomatis."
                  />
                </div>
              </section>

              <section class="space-y-4">
                <SectionTitle title="Catatan DNS" />
                <FormField label="Record DNS">
                  <textarea v-model="domainForm.dns_records_text" rows="5" class="form-control area-control"></textarea>
                </FormField>
              </section>

              <ModalActions :processing="domainForm.processing" :editing="Boolean(editingDomainId)" @cancel="closeDomainModal" />
            </form>
          </div>
        </div>
      </Transition>

      <Transition name="modal">
        <div v-if="showServerModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 p-4 backdrop-blur-sm">
          <div class="scrollbar-none max-h-[92vh] w-full max-w-4xl overflow-y-auto rounded-[2.5rem] bg-white p-6 shadow-2xl">
            <ModalHeader :title="editingServerId ? 'Ubah Data Server' : 'Tambah Server'" @close="closeServerModal" />
            <form class="mt-8 space-y-8" @submit.prevent="submitServer">
              <section class="space-y-4">
                <SectionTitle title="Informasi Dasar" />
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                  <FormField label="Nama Server">
                    <input v-model="serverForm.name" type="text" class="form-control" />
                  </FormField>
                  <FormField label="Alamat IP">
                    <input v-model="serverForm.ip_address" type="text" class="form-control" />
                  </FormField>
                  <FormField label="Provider">
                    <select v-model="serverForm.provider" class="form-control">
                      <option value="">Pilih Provider</option>
                      <option v-for="item in options.providers || []" :key="item" :value="item">{{ item }}</option>
                    </select>
                  </FormField>
                  <FormField label="Tipe Infrastruktur">
                    <select v-model="serverForm.type" class="form-control">
                      <option v-for="item in ['vps', 'shared', 'dedicated']" :key="item" :value="item">{{ formatOption(item) }}</option>
                    </select>
                  </FormField>
                  <FormField label="Lokasi Data Center">
                    <input v-model="serverForm.location" type="text" class="form-control" />
                  </FormField>
                  <FormField label="Sistem Operasi">
                    <input v-model="serverForm.os" type="text" class="form-control" />
                  </FormField>
                  <FormField label="Status Server">
                    <select v-model="serverForm.status" class="form-control">
                      <option v-for="item in ['active', 'maintenance', 'down']" :key="item" :value="item">{{ formatOption(item) }}</option>
                    </select>
                  </FormField>
                </div>
              </section>

              <section class="space-y-4">
                <SectionTitle title="Akses SSH dan Panel" />
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                  <FormField label="User SSH">
                    <input v-model="serverForm.ssh_user" type="text" class="form-control" />
                  </FormField>
                  <FormField label="Password SSH">
                    <input v-model="serverForm.ssh_password" type="text" class="form-control" />
                  </FormField>
                  <FormField label="Port SSH">
                    <input v-model="serverForm.ssh_port" type="number" class="form-control" />
                  </FormField>
                  <FormField label="URL Panel">
                    <input v-model="serverForm.control_panel_url" type="text" class="form-control" />
                  </FormField>
                </div>

                <div v-if="serverForm.control_panel_url" class="grid gap-4 rounded-3xl border border-amber-100 bg-amber-50/60 p-4 md:grid-cols-3">
                  <FormField label="Email Panel">
                    <input v-model="serverForm.dokploy_email" type="email" class="form-control" />
                  </FormField>
                  <FormField label="Username Panel">
                    <input v-model="serverForm.dokploy_username" type="text" class="form-control" />
                  </FormField>
                  <FormField label="Password Panel">
                    <input v-model="serverForm.dokploy_password" type="text" class="form-control" />
                  </FormField>
                </div>
              </section>

              <section class="space-y-4">
                <SectionTitle title="Spesifikasi Hardware" />
                <FormField label="Spesifikasi per Baris">
                  <textarea v-model="serverForm.specs_text" rows="6" class="form-control area-control"></textarea>
                </FormField>
              </section>

              <ModalActions :processing="serverForm.processing" :editing="Boolean(editingServerId)" @cancel="closeServerModal" />
            </form>
          </div>
        </div>
      </Transition>

      <Transition name="modal">
        <div v-if="showFormModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 p-4 backdrop-blur-sm">
          <div class="scrollbar-none max-h-[92vh] w-full max-w-4xl overflow-y-auto rounded-[2.5rem] bg-white p-6 shadow-2xl">
            <ModalHeader :title="editingFormId ? 'Ubah Formulir' : 'Buat Formulir'" @close="closeFormModal" />
            <form class="mt-8 space-y-8" @submit.prevent="submitForm">
              <section class="space-y-4">
                <SectionTitle title="Identitas Formulir" />
                <div class="grid gap-4 md:grid-cols-2">
                  <FormField label="Nama Formulir">
                    <input v-model="formBuilderForm.name" type="text" class="form-control" />
                  </FormField>
                  <FormField label="URL Redirect Berhasil">
                    <input v-model="formBuilderForm.success_redirect_url" type="text" class="form-control" />
                  </FormField>
                </div>
              </section>

              <section class="space-y-4">
                <SectionTitle title="Struktur Field" />
                <FormField label="Field per Baris">
                  <textarea v-model="formBuilderForm.fields_text" rows="8" class="form-control area-control"></textarea>
                </FormField>
                <div class="rounded-3xl border border-stone-200 bg-stone-50 p-5">
                  <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Template Field Cepat</p>
                  <div class="mt-3 flex flex-wrap gap-2">
                    <button
                      v-for="item in options.fieldTemplates || []"
                      :key="item"
                      type="button"
                      @click="appendFieldTemplate(item)"
                      class="rounded-full border border-stone-200 bg-white px-3 py-1.5 text-xs font-semibold text-stone-600 transition hover:border-stone-300 hover:text-stone-950"
                    >
                      {{ item }}
                    </button>
                  </div>
                </div>
              </section>

              <section class="space-y-4">
                <SectionTitle title="Otomasi dan Publikasi" />
                <div class="grid gap-4 md:grid-cols-2">
                  <ToggleCard
                    v-model="formBuilderForm.auto_create_lead"
                    title="Buat Prospek Otomatis"
                    copy="Setiap kiriman baru langsung dibuat sebagai prospek CRM."
                  />
                  <ToggleCard
                    v-model="formBuilderForm.captcha_enabled"
                    title="Captcha Aktif"
                    copy="Tambahkan proteksi spam untuk formulir publik."
                  />
                </div>
                <FormField label="Embed Code">
                  <textarea v-model="formBuilderForm.embed_code" rows="5" class="form-control area-control font-mono"></textarea>
                </FormField>
              </section>

              <ModalActions :processing="formBuilderForm.processing" :editing="Boolean(editingFormId)" @cancel="closeFormModal" />
            </form>
          </div>
        </div>
      </Transition>
    </DigitalServicesLayout>
  </WorkspaceLayout>
</template>

<script setup>
import { computed, defineComponent, h, reactive, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import {
  AppWindowMac,
  Copy,
  ExternalLink,
  Info,
  Pencil,
  Plus,
  RotateCcw,
  Search,
  Server,
  Trash2,
  X,
} from 'lucide-vue-next'
import WorkspaceLayout from '../../Layouts/WorkspaceLayout.vue'
import DigitalServicesLayout from '../../Layouts/DigitalServicesLayout.vue'

const props = defineProps({
  workspace: Object,
  activeTab: String,
  websites: Array,
  deployments: Array,
  domains: Array,
  servers: Array,
  forms: Array,
  formSubmissions: Array,
  options: Object,
  summary: Object,
})

const digitalBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}/digital-services`
const visibleCreds = ref(null)

const filterState = reactive({
  search: '',
  status: '',
})

const tabMeta = {
  websites: {
    label: 'Situs dan Rilis',
    actionLabel: 'Daftar Situs',
    headline: 'Monitoring situs klien dibuat lebih rapi dan ringkas.',
    copy: 'Pantau uptime, SSL, stack teknologi, dan relasi infrastruktur dari satu papan kerja.',
  },
  domains: {
    label: 'Radar Domain',
    actionLabel: 'Daftar Domain',
    headline: 'Aset domain lebih mudah dipantau sebelum jatuh tempo.',
    copy: 'Lihat registrar, auto-renew, catatan DNS, dan relasi domain ke situs aktif.',
  },
  servers: {
    label: 'Inventaris Server',
    actionLabel: 'Tambah Server',
    headline: 'Inventaris server kini lebih jelas untuk operasional harian.',
    copy: 'Data akses, panel, dan spesifikasi hardware ditampilkan lebih padat tanpa bikin form berantakan.',
  },
  forms: {
    label: 'Formulir Otomatis',
    actionLabel: 'Buat Formulir',
    headline: 'Form prospek sekarang bisa dibuat langsung dari halaman ini.',
    copy: 'Struktur field, embed code, dan pengaturan otomatisasi sudah terhubung dalam satu alur.',
  },
}

const activeTab = computed(() => props.activeTab || 'websites')
const activeMeta = computed(() => tabMeta[activeTab.value] || tabMeta.websites)

const activeStatCards = computed(() => {
  if (activeTab.value === 'websites') {
    return [
      { label: 'Total', value: props.summary?.websites?.total || 0 },
      { label: 'Aktif', value: props.summary?.websites?.live || 0 },
      { label: 'Gangguan', value: props.summary?.websites?.down || 0 },
    ]
  }

  if (activeTab.value === 'domains') {
    return [
      { label: 'Total', value: props.summary?.domains?.total || 0 },
      { label: 'Perlu Cek', value: props.summary?.domains?.expiring || 0, note: 'Mendekati jatuh tempo' },
      { label: 'Auto Renew', value: props.summary?.domains?.auto_renew || 0 },
    ]
  }

  if (activeTab.value === 'servers') {
    return [
      { label: 'Total Server', value: props.summary?.servers?.total || 0 },
      { label: 'Aktif', value: props.summary?.servers?.active || 0 },
      { label: 'Situs Terhubung', value: props.summary?.servers?.websites || 0 },
    ]
  }

  return [
    { label: 'Total Form', value: props.summary?.forms?.total || 0 },
    { label: 'Prospek Otomatis', value: props.summary?.forms?.auto_lead || 0 },
    { label: 'Total Kiriman', value: props.summary?.forms?.submissions || 0 },
  ]
})

const statusOptions = computed(() => {
  if (activeTab.value === 'websites') return ['live', 'staging', 'maintenance', 'down']
  if (activeTab.value === 'domains') return ['active', 'expiring', 'expired']
  if (activeTab.value === 'servers') return ['active', 'maintenance', 'down']
  return []
})

const filteredWebsites = computed(() => {
  return (props.websites || []).filter((item) => {
    const search = safeLower(filterState.search)
    const haystack = [item.name, item.url, item.client?.name, item.cms].map(safeLower).join(' ')

    return (!search || haystack.includes(search))
      && (!filterState.status || item.status === filterState.status)
  })
})

const filteredDomains = computed(() => {
  return (props.domains || []).filter((item) => {
    const search = safeLower(filterState.search)
    const haystack = [item.domain_name, item.registrar, ...(item.dns_records || [])].map(safeLower).join(' ')

    return (!search || haystack.includes(search))
      && (!filterState.status || item.status === filterState.status)
  })
})

const filteredServers = computed(() => {
  return (props.servers || []).filter((item) => {
    const search = safeLower(filterState.search)
    const haystack = [item.name, item.ip_address, item.provider, item.location].map(safeLower).join(' ')

    return (!search || haystack.includes(search))
      && (!filterState.status || item.status === filterState.status)
  })
})

const filteredForms = computed(() => {
  return (props.forms || []).filter((item) => {
    const search = safeLower(filterState.search)
    const haystack = [
      item.name,
      item.success_redirect_url,
      ...(item.fields || []).map((field) => field.label),
    ].map(safeLower).join(' ')

    return !search || haystack.includes(search)
  })
})

function safeLower(value) {
  return String(value || '').toLowerCase()
}

function formatOption(value) {
  const map = {
    live: 'Live',
    staging: 'Staging',
    maintenance: 'Perawatan',
    down: 'Gangguan',
    active: 'Aktif',
    expiring: 'Hampir Habis',
    expired: 'Kedaluwarsa',
    shared: 'Shared',
    dedicated: 'Dedicated',
    vps: 'VPS',
  }

  return map[value] || String(value || '-').replaceAll('_', ' ')
}

function formatSpecKey(value) {
  return String(value || '').replaceAll('_', ' ').toUpperCase()
}

function websiteStatusClass(status) {
  return {
    live: 'bg-emerald-100 text-emerald-700',
    staging: 'bg-sky-100 text-sky-700',
    maintenance: 'bg-amber-100 text-amber-700',
    down: 'bg-rose-100 text-rose-700',
  }[status] || 'bg-stone-100 text-stone-600'
}

function domainStatusClass(status) {
  return {
    active: 'bg-emerald-100 text-emerald-700',
    expiring: 'bg-amber-100 text-amber-700',
    expired: 'bg-rose-100 text-rose-700',
  }[status] || 'bg-stone-100 text-stone-600'
}

function serverStatusClass(status) {
  return {
    active: 'bg-emerald-100 text-emerald-700',
    maintenance: 'bg-amber-100 text-amber-700',
    down: 'bg-rose-100 text-rose-700',
  }[status] || 'bg-stone-100 text-stone-600'
}

function toggleServerCreds(id) {
  visibleCreds.value = visibleCreds.value === id ? null : id
}

function copyToClipboard(text) {
  if (!text || !navigator?.clipboard) return
  navigator.clipboard.writeText(text)
}

function resetFilters() {
  filterState.search = ''
  filterState.status = ''
}

const showWebsiteModal = ref(false)
const editingWebsiteId = ref(null)
const websiteForm = useForm({
  client_id: '',
  project_id: '',
  server_id: '',
  domain_id: '',
  name: '',
  url: '',
  cms: '',
  php_version: '',
  status: 'live',
  ssl_enabled: true,
  ssl_expiry_date: '',
  uptime_percentage: '',
})

const showDomainModal = ref(false)
const editingDomainId = ref(null)
const domainForm = useForm({
  domain_name: '',
  registrar: '',
  registration_date: '',
  expiry_date: '',
  status: 'active',
  auto_renew: false,
  dns_records_text: '',
})

const showServerModal = ref(false)
const editingServerId = ref(null)
const serverForm = useForm({
  name: '',
  ip_address: '',
  ssh_port: 22,
  ssh_user: 'root',
  ssh_password: '',
  dokploy_email: '',
  dokploy_username: '',
  dokploy_password: '',
  provider: '',
  type: 'vps',
  location: '',
  os: '',
  control_panel_url: '',
  status: 'active',
  specs_text: '',
})

const showFormModal = ref(false)
const editingFormId = ref(null)
const formBuilderForm = useForm({
  name: '',
  fields_text: '',
  success_redirect_url: '',
  captcha_enabled: false,
  embed_code: '',
  auto_create_lead: true,
})

function openWebsiteModal(item = null) {
  editingWebsiteId.value = item?.id || null
  websiteForm.reset()

  if (item) {
    Object.assign(websiteForm, {
      client_id: item.client_id || '',
      project_id: item.project_id || '',
      server_id: item.server_id || '',
      domain_id: item.domain_id || '',
      name: item.name || '',
      url: item.url || '',
      cms: item.cms || '',
      php_version: item.php_version || '',
      status: item.status || 'live',
      ssl_enabled: Boolean(item.ssl_enabled),
      ssl_expiry_date: item.ssl_expiry_date || '',
      uptime_percentage: item.uptime_percentage || '',
    })
  }

  showWebsiteModal.value = true
}

function closeWebsiteModal() {
  showWebsiteModal.value = false
  editingWebsiteId.value = null
}

function submitWebsite() {
  const target = editingWebsiteId.value
    ? `${digitalBaseUrl}/websites/${editingWebsiteId.value}`
    : `${digitalBaseUrl}/websites`

  if (editingWebsiteId.value) {
    websiteForm.patch(target, { onSuccess: () => closeWebsiteModal() })
    return
  }

  websiteForm.post(target, { onSuccess: () => closeWebsiteModal() })
}

function deleteWebsite(id) {
  if (confirm('Hapus website ini?')) {
    router.delete(`${digitalBaseUrl}/websites/${id}`)
  }
}

function openDomainModal(item = null) {
  editingDomainId.value = item?.id || null
  domainForm.reset()

  if (item) {
    Object.assign(domainForm, {
      domain_name: item.domain_name || '',
      registrar: item.registrar || '',
      registration_date: item.registration_date || '',
      expiry_date: item.expiry_date || '',
      status: item.status || 'active',
      auto_renew: Boolean(item.auto_renew),
      dns_records_text: item.dns_records_text || '',
    })
  }

  showDomainModal.value = true
}

function closeDomainModal() {
  showDomainModal.value = false
  editingDomainId.value = null
}

function submitDomain() {
  const target = editingDomainId.value
    ? `${digitalBaseUrl}/domains/${editingDomainId.value}`
    : `${digitalBaseUrl}/domains`

  if (editingDomainId.value) {
    domainForm.patch(target, { onSuccess: () => closeDomainModal() })
    return
  }

  domainForm.post(target, { onSuccess: () => closeDomainModal() })
}

function deleteDomain(id) {
  if (confirm('Hapus domain ini?')) {
    router.delete(`${digitalBaseUrl}/domains/${id}`)
  }
}

function openServerModal(item = null) {
  editingServerId.value = item?.id || null
  serverForm.reset()

  if (item) {
    Object.assign(serverForm, {
      name: item.name || '',
      ip_address: item.ip_address || '',
      ssh_port: item.ssh_port || 22,
      ssh_user: item.ssh_user || 'root',
      ssh_password: item.ssh_password || '',
      dokploy_email: item.dokploy_email || '',
      dokploy_username: item.dokploy_username || '',
      dokploy_password: item.dokploy_password || '',
      provider: item.provider || '',
      type: item.type || 'vps',
      location: item.location || '',
      os: item.os || '',
      control_panel_url: item.control_panel_url || '',
      status: item.status || 'active',
      specs_text: item.specs_text || '',
    })
  }

  showServerModal.value = true
}

function closeServerModal() {
  showServerModal.value = false
  editingServerId.value = null
}

function submitServer() {
  const target = editingServerId.value
    ? `${digitalBaseUrl}/servers/${editingServerId.value}`
    : `${digitalBaseUrl}/servers`

  if (editingServerId.value) {
    serverForm.patch(target, { onSuccess: () => closeServerModal() })
    return
  }

  serverForm.post(target, { onSuccess: () => closeServerModal() })
}

function deleteServer(id) {
  if (confirm('Hapus server ini?')) {
    router.delete(`${digitalBaseUrl}/servers/${id}`)
  }
}

function openFormModal(item = null) {
  editingFormId.value = item?.id || null
  formBuilderForm.reset()

  if (item) {
    Object.assign(formBuilderForm, {
      name: item.name || '',
      fields_text: item.fields_text || '',
      success_redirect_url: item.success_redirect_url || '',
      captcha_enabled: Boolean(item.captcha_enabled),
      embed_code: item.embed_code || '',
      auto_create_lead: Boolean(item.auto_create_lead),
    })
  }

  showFormModal.value = true
}

function closeFormModal() {
  showFormModal.value = false
  editingFormId.value = null
}

function appendFieldTemplate(field) {
  const currentValue = String(formBuilderForm.fields_text || '').trim()
  formBuilderForm.fields_text = currentValue ? `${currentValue}\n${field}` : field
}

function submitForm() {
  const target = editingFormId.value
    ? `${digitalBaseUrl}/forms/${editingFormId.value}`
    : `${digitalBaseUrl}/forms`

  if (editingFormId.value) {
    formBuilderForm.patch(target, { onSuccess: () => closeFormModal() })
    return
  }

  formBuilderForm.post(target, { onSuccess: () => closeFormModal() })
}

function deleteForm(id) {
  if (confirm('Hapus formulir ini?')) {
    router.delete(`${digitalBaseUrl}/forms/${id}`)
  }
}

function openActiveModal() {
  if (activeTab.value === 'websites') {
    openWebsiteModal()
    return
  }

  if (activeTab.value === 'domains') {
    openDomainModal()
    return
  }

  if (activeTab.value === 'servers') {
    openServerModal()
    return
  }

  openFormModal()
}

const EmptyState = defineComponent({
  props: {
    copy: {
      type: String,
      required: true,
    },
  },
  setup(componentProps) {
    return () => h('div', {
      class: 'rounded-[2rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-24 text-center text-sm italic text-stone-500',
    }, componentProps.copy)
  },
})

const ModalHeader = defineComponent({
  props: {
    title: {
      type: String,
      required: true,
    },
  },
  emits: ['close'],
  setup(componentProps, { emit }) {
    return () => h('div', { class: 'flex items-start justify-between gap-4' }, [
      h('div', {}, [
        h('p', { class: 'text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400' }, 'Form Infrastruktur'),
        h('h3', { class: 'mt-2 text-2xl font-bold tracking-tight text-stone-900' }, componentProps.title),
      ]),
      h('button', {
        type: 'button',
        class: 'rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700',
        onClick: () => emit('close'),
      }, [h(X, { class: 'h-5 w-5' })]),
    ])
  },
})

const SectionTitle = defineComponent({
  props: {
    title: {
      type: String,
      required: true,
    },
  },
  setup(componentProps) {
    return () => h('div', { class: 'flex items-center gap-3' }, [
      h('div', { class: 'h-1.5 w-1.5 rounded-full bg-amber-500' }),
      h('h4', { class: 'text-[11px] font-bold uppercase tracking-widest text-stone-400' }, componentProps.title),
    ])
  },
})

const FormField = defineComponent({
  props: {
    label: {
      type: String,
      required: true,
    },
  },
  setup(componentProps, { slots }) {
    return () => h('label', { class: 'space-y-2 text-sm' }, [
      h('span', { class: 'text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400' }, componentProps.label),
      ...(slots.default ? slots.default() : []),
    ])
  },
})

const ToggleCard = defineComponent({
  props: {
    modelValue: {
      type: Boolean,
      default: false,
    },
    title: {
      type: String,
      required: true,
    },
    copy: {
      type: String,
      required: true,
    },
  },
  emits: ['update:modelValue'],
  setup(componentProps, { emit }) {
    return () => h('label', {
      class: 'flex items-center gap-3 rounded-3xl border border-stone-200 bg-stone-50 px-5 py-4',
    }, [
      h('input', {
        type: 'checkbox',
        checked: componentProps.modelValue,
        class: 'h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-400',
        onChange: (event) => emit('update:modelValue', event.target.checked),
      }),
      h('div', {}, [
        h('p', { class: 'text-sm font-semibold text-stone-900' }, componentProps.title),
        h('p', { class: 'text-xs text-stone-500' }, componentProps.copy),
      ]),
    ])
  },
})

const ModalActions = defineComponent({
  props: {
    processing: {
      type: Boolean,
      default: false,
    },
    editing: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['cancel'],
  setup(componentProps, { emit }) {
    return () => h('div', { class: 'flex justify-end gap-3 border-t border-stone-100 pt-8' }, [
      h('button', {
        type: 'button',
        class: 'rounded-2xl border border-stone-200 bg-white px-6 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-50 hover:text-stone-900',
        onClick: () => emit('cancel'),
      }, 'Batal'),
      h('button', {
        type: 'submit',
        disabled: componentProps.processing,
        class: 'rounded-2xl bg-stone-950 px-10 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02] active:scale-[0.98] disabled:opacity-60',
      }, componentProps.editing ? 'Simpan Perubahan' : 'Buat Sekarang'),
    ])
  },
})
</script>

<style scoped>
.form-control {
  width: 100%;
  border-radius: 1rem;
  border: 1px solid rgb(231 229 228);
  background: rgb(250 250 249);
  padding: 0.75rem 1rem;
  font-size: 0.875rem;
  color: rgb(68 64 60);
  outline: none;
  transition: all 0.2s ease;
}

.form-control:focus {
  border-color: rgb(168 162 158);
  background: white;
}

.area-control {
  min-height: 8rem;
  resize: vertical;
}

.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.95) translateY(10px);
}
</style>
