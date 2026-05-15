<template>
  <WorkspaceLayout
    title="Pengaturan Sistem"
    subtitle="Kelola otoritas tim, konfigurasi brand, audit aktivitas, dan kebijakan keamanan workspace dalam satu pusat kendali."
  >
    <template #actions>
      <button
        v-if="activeMeta.actionLabel && canManageTeam"
        type="button"
        @click="openActiveModal()"
        class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
      >
        <Plus class="h-4 w-4" />
        <span>{{ activeMeta.actionLabel }}</span>
      </button>
    </template>

    <SystemLayout :workspace="workspace">
      <div class="space-y-6">
        <section class="project-hero-shell">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="project-hero-copy">
              <p class="project-hero-kicker">Menu {{ activeMeta.menu }} / {{ activeMeta.label }}</p>
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

          <div class="mt-4 grid gap-3 md:grid-cols-3">
            <div v-for="signal in activeSignals" :key="signal.label" class="compact-stat-card">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">{{ signal.label }}</p>
              <p class="mt-2 text-sm leading-6 text-stone-600">{{ signal.copy }}</p>
            </div>
          </div>
        </section>

        <section v-if="activeTab === 'team'">
          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-stone-100 pb-6">
              <div class="flex items-center gap-4">
                <div class="rounded-2xl bg-stone-100 p-3 text-stone-900">
                  <Users class="h-6 w-6" />
                </div>
                <div>
                  <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Tim & Akses</p>
                  <h2 class="mt-1 text-xl font-bold tracking-tight text-stone-950">Daftar Anggota Workspace</h2>
                </div>
              </div>
              <div v-if="canManageTeam" class="flex items-center gap-3">
                <button type="button" @click="showRolesManagerModal = true" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2.5 text-sm font-semibold text-stone-700 transition hover:bg-stone-50 hover:text-stone-950">
                  <Shield class="h-4 w-4 text-amber-500" />
                  <span>Matriks Peran</span>
                </button>
                <button type="button" @click="openMembershipModal()" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800">
                  <Plus class="h-4 w-4" />
                  <span>Tambah Anggota</span>
                </button>
              </div>
            </div>

            <div class="mt-6 overflow-x-auto">
              <table class="w-full border-separate border-spacing-y-2">
                <thead>
                  <tr class="text-left">
                    <th class="px-5 pb-3 text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">Anggota</th>
                    <th class="px-5 pb-3 text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">Jabatan/Peran</th>
                    <th class="px-5 pb-3 text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">Akses</th>
                    <th class="px-5 pb-3 text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">Login Terakhir</th>
                    <th v-if="canManageTeam" class="px-5 pb-3 text-right text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="member in memberships" :key="member.id" class="group bg-stone-50/50 transition-all hover:bg-white hover:shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                    <td class="rounded-l-[1.2rem] border-y border-l border-stone-100 px-5 py-4 group-hover:border-stone-200">
                      <div class="flex items-center gap-4">
                        <div class="h-10 w-10 overflow-hidden rounded-full border border-stone-200 bg-stone-100 flex items-center justify-center text-sm font-bold text-stone-500 ring-2 ring-white">
                          {{ (member.user?.name || 'U').charAt(0) }}
                        </div>
                        <div>
                          <p class="text-sm font-bold text-stone-950">{{ member.user?.name || 'Pengguna tidak dikenal' }}</p>
                          <p class="text-xs font-medium text-stone-400">{{ member.user?.email }}</p>
                        </div>
                      </div>
                    </td>
                    <td class="border-y border-stone-100 px-5 py-4 group-hover:border-stone-200">
                      <span class="inline-flex items-center rounded-lg bg-stone-100 px-2 py-1 text-[11px] font-bold text-stone-600 border border-stone-200">
                        {{ member.role?.name || 'Tanpa peran' }}
                      </span>
                    </td>
                    <td class="border-y border-stone-100 px-5 py-4 group-hover:border-stone-200">
                      <div class="flex items-center gap-2">
                        <span v-if="member.is_owner" class="inline-flex items-center gap-1.5 rounded-full bg-stone-950 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-white border border-amber-500/30 shadow-[0_0_10px_rgba(245,158,11,0.15)]">
                          <ShieldCheck class="h-3 w-3 text-amber-400" />
                          <span>Pemilik</span>
                        </span>
                        <span v-else-if="member.role?.slug === 'admin'" class="rounded-full bg-blue-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-blue-700 border border-blue-100">Admin</span>
                        <span v-else class="rounded-full bg-stone-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-stone-500 border border-stone-200">Staff</span>
                        <span v-if="member.is_expired" class="rounded-full bg-rose-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-rose-600 border border-rose-100">Kedaluwarsa</span>
                      </div>
                    </td>
                    <td class="border-y border-stone-100 px-5 py-4 group-hover:border-stone-200">
                      <p class="text-xs font-medium text-stone-500">{{ member.user?.last_login_at_label || 'Belum pernah login' }}</p>
                    </td>
                    <td v-if="canManageTeam" class="rounded-r-[1.2rem] border-y border-r border-stone-100 px-5 py-4 text-right group-hover:border-stone-200">
                      <div class="flex items-center justify-end gap-2 opacity-0 transition-opacity group-hover:opacity-100">
                        <button type="button" @click="openMembershipModal(member)" title="Ubah" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-stone-200 bg-white text-stone-600 transition hover:border-stone-400 hover:text-stone-950 hover:shadow-sm">
                          <Pencil class="h-4 w-4" />
                        </button>
                        <button type="button" @click="deleteMembership(member.id)" title="Hapus" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-rose-100 bg-white text-rose-500 transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700 hover:shadow-sm">
                          <Trash2 class="h-4 w-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </article>
        </section>

        <section v-else-if="activeTab === 'settings'" class="grid gap-4 xl:grid-cols-[1.06fr_0.94fr]">
          <form class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]" @submit.prevent="submitSettings">
            <div class="border-b border-stone-200 pb-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Pengaturan Workspace</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Brand, domain, connectors, working hours, dan storage policy.</h2>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
              <FormInput v-model="settingsForm.name" label="Nama Workspace" />
              <FormInput v-model="settingsForm.logo" label="URL Logo" />
              <FormInput v-model="settingsForm.primary_color" label="Warna Utama" />
              <FormInput v-model="settingsForm.custom_domain" label="Domain Kustom" />
              <FormInput v-model="settingsForm.timezone" label="Zona Waktu" />
              <FormInput v-model="settingsForm.currency" label="Mata Uang" />
              <FormInput v-model="settingsForm.language" label="Bahasa" />
              <FormInput v-model="settingsForm.storage_quota_gb" label="Kuota Penyimpanan GB" type="number" />
              <FormInput v-model="settingsForm.smtp_host" label="Host SMTP" />
              <FormInput v-model="settingsForm.smtp_port" label="Port SMTP" type="number" />
              <FormInput v-model="settingsForm.smtp_username" label="Username SMTP" />
              <FormInput v-model="settingsForm.smtp_password" label="Password SMTP" type="password" />
              <FormInput v-model="settingsForm.wa_api_key" label="API Key WA" />
              <FormInput v-model="settingsForm.wa_phone_number" label="Nomor WA" />
              <FormInput v-model="settingsForm.n8n_webhook_url" label="n8n Webhook URL" />
              <FormInput v-model="settingsForm.working_hours_start" label="Jam Kerja Mulai" type="time" />
              <FormInput v-model="settingsForm.working_hours_end" label="Jam Kerja Selesai" type="time" />
              <FormTextarea v-model="settingsForm.holiday_dates_text" label="Kalender Libur" rows="5" class-name="md:col-span-2" />
              <FormTextarea v-model="settingsForm.notification_templates_text" label="Template Notifikasi" rows="5" class-name="md:col-span-2" />
              <FormTextarea v-model="settingsForm.backup_snapshots_text" label="Snapshot Backup" rows="5" class-name="md:col-span-2" />
            </div>

            <div class="mt-6 flex justify-end">
              <button type="submit" :disabled="settingsForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">Simpan Pengaturan</button>
            </div>
          </form>

          <div class="space-y-4">
            <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Sinyal Terkonfigurasi</p>
              <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <div v-for="card in settingsCards" :key="card.label" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">{{ card.label }}</p>
                  <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ card.value }}</p>
                </div>
              </div>
            </article>

            <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Catatan Operasional</p>
              <div class="mt-4 space-y-3">
                <div v-for="panel in activePanels" :key="panel.title" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-sm font-semibold text-stone-950">{{ panel.title }}</p>
                  <p class="mt-2 text-sm leading-6 text-stone-600">{{ panel.copy }}</p>
                </div>
              </div>
            </article>
          </div>
        </section>

        <section v-else-if="activeTab === 'audit'" class="grid gap-4 xl:grid-cols-[1.08fr_0.92fr]">
          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Arus Audit</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Log aksi siapa, apa, dan kapan dalam workspace.</h2>
              </div>
              <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">{{ auditLogs.length }} log</span>
            </div>

            <div class="mt-5 space-y-4">
              <article v-for="log in auditLogs" :key="log.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-6 transition-all hover:border-stone-300 hover:shadow-md">
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div class="w-full max-w-2xl">
                    <div class="flex flex-wrap items-center gap-3">
                      <div class="rounded-xl bg-stone-950 px-3 py-1.5 text-xs font-bold text-white shadow-sm">
                        {{ log.action }}
                      </div>
                      <h3 class="text-lg font-bold tracking-tight text-stone-950">{{ log.summary }}</h3>
                    </div>
                    
                    <div class="mt-4 flex flex-wrap items-center gap-4 text-xs text-stone-500">
                      <div class="flex items-center gap-2">
                        <div class="h-6 w-6 rounded-full bg-stone-200 flex items-center justify-center text-[10px] font-bold text-stone-600">
                          {{ (log.user?.name || 'S').charAt(0) }}
                        </div>
                        <span class="font-semibold text-stone-700">{{ log.user?.name || 'Sistem' }}</span>
                      </div>
                      <span class="h-1 w-1 rounded-full bg-stone-300"></span>
                      <span>{{ log.created_at_label }}</span>
                      <span class="h-1 w-1 rounded-full bg-stone-300"></span>
                      <span class="rounded-md bg-white px-2 py-0.5 border border-stone-100 font-mono">{{ log.ip_address || 'Tanpa IP' }}</span>
                    </div>

                    <div class="mt-6 grid gap-3 md:grid-cols-2">
                      <div class="rounded-2xl border border-stone-100 bg-white p-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400 mb-2">Nilai Lama</p>
                        <div class="overflow-x-auto">
                          <pre class="whitespace-pre-wrap text-[11px] leading-5 text-stone-500 font-mono">{{ log.old_values_text || 'Tidak ada perubahan data sebelumnya.' }}</pre>
                        </div>
                      </div>
                      <div class="rounded-2xl border border-stone-100 bg-white p-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400 mb-2">Nilai Baru</p>
                        <div class="overflow-x-auto">
                          <pre class="whitespace-pre-wrap text-[11px] leading-5 text-stone-600 font-mono font-medium">{{ log.new_values_text || 'Tidak ada perubahan data baru.' }}</pre>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="flex items-center gap-2">
                    <button type="button" @click="openAuditModal(log)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                      <Pencil class="h-4 w-4" />
                    </button>
                    <button type="button" @click="deleteAuditLog(log.id)" class="inline-flex items-center justify-center rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </div>
              </article>
            </div>
          </article>

          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="border-b border-stone-200 pb-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Sinyal Audit</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Baca posture perubahan dan aktivitas sensitif.</h2>
            </div>

            <div class="mt-5 space-y-4">
              <div v-for="panel in activePanels" :key="panel.title" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <p class="text-sm font-semibold text-stone-950">{{ panel.title }}</p>
                <p class="mt-2 text-sm leading-6 text-stone-600">{{ panel.copy }}</p>
              </div>
            </div>
          </article>
        </section>

        <section v-else-if="activeTab === 'security'" class="grid gap-4 xl:grid-cols-[1.05fr_0.95fr]">
          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">API Key</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Cakupan, whitelist, kedaluwarsa, dan rate limit akses API.</h2>
              </div>
              <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">{{ apiKeys.length }} key</span>
            </div>

            <div class="mt-5 space-y-4">
              <article v-for="key in apiKeys" :key="key.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div>
                    <div class="flex flex-wrap items-center gap-2">
                      <h3 class="text-base font-semibold text-stone-950">{{ key.name }}</h3>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="key.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-200 text-stone-700'">{{ key.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                    <p class="mt-2 text-sm text-stone-500">{{ key.user?.name || 'Belum ditugaskan' }} / {{ key.key_preview }}</p>
                    <div class="mt-4 flex flex-wrap gap-2 text-xs text-stone-500">
                      <span class="rounded-full bg-white px-3 py-1.5">{{ key.rate_limit_per_hour }}/h</span>
                      <span class="rounded-full bg-white px-3 py-1.5">{{ key.expires_at_label }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">{{ key.last_used_at_label }}</span>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                      <span v-for="scope in key.scopes.slice(0, 6)" :key="scope" class="rounded-full bg-white px-3 py-1.5 text-[11px] text-stone-500">{{ scope }}</span>
                    </div>
                  </div>

                  <div class="flex items-center gap-2">
                    <button type="button" @click="openApiKeyModal(key)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                      <Pencil class="h-4 w-4" />
                    </button>
                    <button type="button" @click="deleteApiKey(key.id)" class="inline-flex items-center justify-center rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </div>
              </article>
            </div>
          </article>

          <div class="space-y-4">
            <form class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]" @submit.prevent="submitSecurity">
              <div class="border-b border-stone-200 pb-5">
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Kebijakan Keamanan</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">2FA, SSO, idle timeout, whitelist, dan password policy.</h2>
              </div>

              <div class="mt-5 space-y-4">
                <label class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700">
                  <input v-model="securityForm.require_two_factor" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-950" />
                  <span>Wajibkan Dua Faktor (2FA)</span>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700">
                  <input v-model="securityForm.allow_google_sso" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-950" />
                  <span>Izinkan Google SSO</span>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700">
                  <input v-model="securityForm.brute_force_protection" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-950" />
                  <span>Perlindungan Brute Force</span>
                </label>
                <FormInput v-model="securityForm.session_idle_minutes" label="Menit Sesi Menganggur" type="number" />
                <FormTextarea v-model="securityForm.allowed_ips_text" label="IP yang Diizinkan" rows="5" />
                <FormTextarea v-model="securityForm.password_policy" label="Kebijakan Kata Sandi" rows="4" />
              </div>

              <div class="mt-6 flex justify-end">
                <button type="submit" :disabled="securityForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">Simpan Keamanan</button>
              </div>
            </form>

            <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Sesi</p>
              <div class="mt-4 space-y-3">
                <div v-for="session in sessions" :key="session.id" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-sm font-semibold text-stone-950">{{ session.user_name || 'Pengguna tidak dikenal' }}</p>
                  <p class="mt-1 text-xs text-stone-500">{{ session.user_email }}</p>
                  <p class="mt-2 text-xs text-stone-500">{{ session.ip_address || 'Tanpa IP' }} / {{ session.last_activity_label }}</p>
                  <p class="mt-2 text-xs text-stone-500">{{ session.user_agent }}</p>
                </div>
              </div>
            </article>
          </div>
        </section>

        <section v-else class="grid gap-4 xl:grid-cols-[1.08fr_0.92fr]">
          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Pusat Bantuan</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Dokumentasi internal, FAQ, tutorial, dan log perubahan workspace.</h2>
              </div>
              <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">{{ helpArticles.length }} artikel</span>
            </div>

            <div class="mt-5 space-y-4">
              <article v-for="article in helpArticles" :key="article.id" class="group rounded-[1.6rem] border border-stone-200 bg-stone-50 p-6 transition-all hover:bg-white hover:shadow-xl">
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div class="max-w-2xl">
                    <div class="flex flex-wrap items-center gap-3">
                      <div class="rounded-lg bg-stone-200 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-stone-600 group-hover:bg-stone-950 group-hover:text-white transition-colors">
                        {{ article.category || 'Umum' }}
                      </div>
                      <h3 class="text-lg font-bold text-stone-950 group-hover:text-stone-900 transition-colors">{{ article.title }}</h3>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.12em]" :class="article.is_published ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-stone-100 text-stone-500 border border-stone-200'">
                        {{ article.is_published ? 'Terbit' : 'Draft' }}
                      </span>
                    </div>
                    
                    <p class="mt-3 text-sm leading-relaxed text-stone-600 line-clamp-2">{{ article.excerpt }}</p>
                    
                    <div class="mt-5 flex flex-wrap items-center gap-4 text-[11px] text-stone-400">
                      <div class="flex items-center gap-1.5 font-medium">
                        <span class="text-stone-300">#</span>
                        <span class="text-stone-500 italic">{{ article.slug }}</span>
                      </div>
                      <span class="h-1 w-1 rounded-full bg-stone-200"></span>
                      <div class="flex items-center gap-1.5">
                        <span class="font-semibold text-stone-500">{{ article.view_count }}</span>
                        <span>dilihat</span>
                      </div>
                      <span class="h-1 w-1 rounded-full bg-stone-200"></span>
                      <div class="flex items-center gap-1.5">
                        <span>Diperbarui</span>
                        <span class="font-semibold text-stone-500">{{ article.updated_at_label }}</span>
                      </div>
                    </div>
                  </div>

                  <div class="flex items-center gap-2">
                    <button type="button" @click="openHelpModal(article)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                      <Pencil class="h-4 w-4" />
                    </button>
                    <button type="button" @click="deleteHelpArticle(article.id)" class="inline-flex items-center justify-center rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </div>
              </article>
            </div>
          </article>

          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="border-b border-stone-200 pb-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Sinyal Pengetahuan</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Cara baca status penerbitan dan cakupan pengetahuan.</h2>
            </div>

            <div class="mt-5 space-y-4">
              <div v-for="panel in activePanels" :key="panel.title" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <p class="text-sm font-semibold text-stone-950">{{ panel.title }}</p>
                <p class="mt-2 text-sm leading-6 text-stone-600">{{ panel.copy }}</p>
              </div>
            </div>
          </article>
        </section>
      </div>
    </SystemLayout>

    <Transition name="modal">
      <div v-if="showRolesManagerModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-stone-950/40 p-4 backdrop-blur-sm">
        <div class="max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-[2rem] bg-white p-8 shadow-2xl">
          <div class="flex items-start justify-between gap-4 border-b border-stone-100 pb-6">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Matriks Peran & Akses</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">Kelola hierarki peran dan izin khusus.</h3>
            </div>
            <div class="flex items-center gap-3">
              <button type="button" @click="openRoleModal()" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:bg-stone-800">
                <Plus class="h-4 w-4" />
                <span>Peran Baru</span>
              </button>
              <button type="button" @click="showRolesManagerModal = false" class="rounded-full p-2 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700">x</button>
            </div>
          </div>

          <div class="mt-8 space-y-4">
            <article v-for="role in roles" :key="role.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-6 transition hover:border-stone-300 hover:bg-white">
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="max-w-2xl">
                  <div class="flex flex-wrap items-center gap-2">
                    <h3 class="text-lg font-bold text-stone-950">{{ role.name }}</h3>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="role.is_default ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-200 text-stone-700'">
                      {{ role.is_default ? 'Bawaan' : 'Kustom' }}
                    </span>
                    <span v-if="role.parent_role_name" class="rounded-full bg-white px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.18em] text-stone-500">
                      Induk: {{ role.parent_role_name }}
                    </span>
                  </div>
                  <p class="mt-2 text-sm text-stone-400 font-mono">{{ role.slug }}</p>
                  <p class="mt-3 text-sm leading-6 text-stone-600">{{ role.description || 'Belum ada deskripsi peran.' }}</p>
                  
                  <div class="mt-5 flex flex-wrap gap-2">
                    <span v-for="label in role.permission_labels.slice(0, 10)" :key="label" class="rounded-lg bg-white border border-stone-100 px-3 py-1.5 text-[11px] text-stone-500">{{ label }}</span>
                    <span v-if="role.permission_labels.length > 10" class="text-[11px] text-stone-400 self-center">+{{ role.permission_labels.length - 10 }} lainnya</span>
                  </div>
                </div>

                <div class="flex items-center gap-3">
                  <button type="button" @click="openRoleModal(role)" class="inline-flex items-center gap-2 rounded-xl border border-stone-200 bg-white px-4 py-2 text-xs font-bold uppercase tracking-wider text-stone-600 transition hover:border-stone-400 hover:text-stone-950 hover:shadow-sm">
                    <Shield class="h-3.5 w-3.5" />
                    <span>Lihat Izin</span>
                  </button>
                  <button type="button" @click="deleteRole(role.id)" title="Hapus Peran" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-rose-100 bg-white text-rose-500 transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700">
                    <Trash2 class="h-4 w-4" />
                  </button>
                </div>
              </div>
            </article>
          </div>
        </div>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showRoleModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-stone-950/40 p-4 backdrop-blur-sm">
        <form class="max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-[2.5rem] bg-white p-8 shadow-2xl" @submit.prevent="submitRole">
          <div class="flex items-start justify-between gap-4 border-b border-stone-100 pb-6">
            <div class="flex items-center gap-4">
              <div class="rounded-2xl bg-stone-100 p-3 text-stone-900">
                <ShieldCheck class="h-6 w-6" />
              </div>
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Pembangun Peran</p>
                <h3 class="mt-1 text-2xl font-bold tracking-tight text-stone-950">{{ editingRoleId ? 'Ubah Detail Peran' : 'Buat Peran Baru' }}</h3>
              </div>
            </div>
            <button type="button" @click="closeRoleModal" class="rounded-full p-2 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700">
              <Plus class="h-6 w-6 rotate-45" />
            </button>
          </div>

          <div class="mt-8 grid gap-6 md:grid-cols-2">
            <FormInput v-model="roleForm.name" label="Nama Peran (Tampilan)" placeholder="Contoh: Manajer Proyek" />
            <FormInput v-model="roleForm.slug" label="Identifier (Slug)" placeholder="contoh-manajer-proyek" />
            <FormInput v-model="roleForm.description" label="Deskripsi Tanggung Jawab" class-name="md:col-span-2" placeholder="Jelaskan cakupan kerja peran ini..." />
            
            <label class="block space-y-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Turunan Dari (Parent)</span>
              <select v-model="roleForm.parent_role_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white focus:ring-4 focus:ring-stone-100">
                <option value="">Mandiri (Tanpa Induk)</option>
                <option v-for="item in options.roleOptions" :key="item.id" :value="item.id">{{ item.name }}</option>
              </select>
            </label>

            <div class="flex items-center">
              <label class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-5 py-3.5 text-sm font-semibold text-stone-700 transition-colors hover:bg-stone-100 cursor-pointer w-full">
                <input v-model="roleForm.is_default" type="checkbox" class="h-5 w-5 rounded border-stone-300 text-stone-950 focus:ring-stone-950/20" />
                <span>Jadikan peran bawaan untuk anggota baru</span>
              </label>
            </div>
          </div>

          <div class="mt-10 space-y-6">
            <div class="flex items-center gap-3 border-b border-stone-100 pb-4">
              <Settings2 class="h-5 w-5 text-stone-400" />
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Matriks Hak Akses (Izin Spesifik)</p>
            </div>
            
            <div class="grid gap-6 md:grid-cols-2">
              <div v-for="module in permissionModules" :key="module.name" class="rounded-[1.8rem] border border-stone-200 bg-stone-50 p-6">
                <h4 class="text-sm font-bold uppercase tracking-wider text-stone-900 border-b border-stone-200 pb-3 mb-4">{{ formatOption(module.name) }}</h4>
                <div class="space-y-3">
                  <label v-for="permission in module.items" :key="permission.id" class="flex items-center gap-3 rounded-xl border border-white bg-white px-4 py-3 text-sm font-medium text-stone-600 transition hover:border-stone-200 hover:shadow-sm cursor-pointer">
                    <input v-model="roleForm.permission_ids" type="checkbox" :value="permission.id" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-950/20" />
                    <span>{{ permission.action }}</span>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-10 flex items-center justify-end gap-4 border-t border-stone-100 pt-8">
            <button type="button" @click="closeRoleModal" class="rounded-2xl px-6 py-3.5 text-sm font-bold text-stone-500 transition hover:bg-stone-100 hover:text-stone-900">Batal</button>
            <button type="submit" :disabled="roleForm.processing" class="rounded-2xl bg-stone-950 px-8 py-3.5 text-sm font-bold text-white shadow-lg shadow-stone-950/10 transition-all hover:-translate-y-0.5 hover:bg-stone-800 disabled:opacity-50">
              {{ editingRoleId ? 'Simpan Perubahan Matriks' : 'Finalisasi Peran Baru' }}
            </button>
          </div>
        </form>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showMembershipModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-stone-950/40 p-4 backdrop-blur-sm">
        <form class="w-full max-w-xl rounded-[2.5rem] bg-white p-8 shadow-2xl" @submit.prevent="submitMembership">
          <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-4">
              <div class="rounded-2xl bg-stone-100 p-3 text-stone-900">
                <UserPlus class="h-6 w-6" />
              </div>
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Tim & Akses</p>
                <h3 class="mt-1 text-2xl font-bold tracking-tight text-stone-950">{{ editingMembershipId ? 'Ubah Akses Anggota' : 'Tambah Anggota Baru' }}</h3>
              </div>
            </div>
            <button type="button" @click="closeMembershipModal" class="rounded-full p-2 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700">
              <Plus class="h-6 w-6 rotate-45" />
            </button>
          </div>

          <div class="mt-8 space-y-6">
            <!-- Mode Selector (Hanya muncul saat tambah baru) -->
            <div v-if="!editingMembershipId" class="flex p-1 bg-stone-100 rounded-2xl">
                <button 
                    type="button"
                    @click="memberMode = 'new'"
                    class="flex-1 py-2 text-[10px] font-bold uppercase tracking-widest rounded-xl transition-all"
                    :class="memberMode === 'new' ? 'bg-white text-stone-950 shadow-sm' : 'text-stone-500 hover:text-stone-700'"
                >
                    Daftar Akun Baru
                </button>
                <button 
                    type="button"
                    @click="memberMode = 'existing'"
                    class="flex-1 py-2 text-[10px] font-bold uppercase tracking-widest rounded-xl transition-all"
                    :class="memberMode === 'existing' ? 'bg-white text-stone-950 shadow-sm' : 'text-stone-500 hover:text-stone-700'"
                >
                    Akun Terdaftar
                </button>
            </div>

            <!-- New User Form -->
            <div v-if="memberMode === 'new' && !editingMembershipId" class="grid gap-4 md:grid-cols-2">
                <label class="block space-y-2 md:col-span-2">
                  <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-500">Nama Lengkap</span>
                  <input v-model="membershipForm.name" type="text" placeholder="Contoh: Budi Developer" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-5 py-4 text-sm font-medium text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                </label>
                <label class="block space-y-2">
                  <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-500">Email</span>
                  <input v-model="membershipForm.email" type="email" placeholder="budi@velora.id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-5 py-4 text-sm font-medium text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                </label>
                <label class="block space-y-2">
                  <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-500">Kata Sandi (Password)</span>
                  <input v-model="membershipForm.password" type="password" placeholder="Min. 8 karakter" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-5 py-4 text-sm font-medium text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                </label>
            </div>

            <!-- Existing User Selector -->
            <label v-if="memberMode === 'existing' || editingMembershipId" class="block space-y-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-500">Pilih Pengguna</span>
              <select v-model="membershipForm.user_id" :disabled="editingMembershipId" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-5 py-4 text-sm font-medium text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white disabled:opacity-50">
                <option value="">Pilih akun pengguna...</option>
                <option v-for="user in options.users" :key="user.id" :value="user.id">{{ user.name }} ({{ user.email }})</option>
              </select>
            </label>

            <div class="grid gap-4 md:grid-cols-2">
                <label class="block space-y-2">
                    <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-500">Penugasan Peran</span>
                    <select v-model="membershipForm.role_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-5 py-4 text-sm font-medium text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                        <option value="">Tanpa peran spesifik</option>
                        <option v-for="item in options.roleOptions" :key="item.id" :value="item.id">{{ item.name }}</option>
                    </select>
                </label>
                
                <label class="block space-y-2">
                    <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-500">Berakhir Pada (Opsional)</span>
                    <input v-model="membershipForm.expires_at" type="date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-5 py-4 text-sm font-medium text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                </label>
            </div>

            <label class="flex items-center gap-4 rounded-[1.4rem] border border-stone-100 bg-stone-50/50 p-5 transition-colors hover:bg-stone-50">
              <div class="flex h-6 items-center">
                <input v-model="membershipForm.is_owner" type="checkbox" class="h-5 w-5 rounded-lg border-stone-300 text-stone-950 focus:ring-stone-950/20" />
              </div>
              <div class="text-sm">
                <p class="font-bold text-stone-950">Akses Pemilik (Owner)</p>
                <p class="mt-0.5 text-xs text-stone-500">Berikan akses penuh untuk mengelola workspace dan penagihan.</p>
              </div>
            </label>
          </div>

          <div class="mt-10 flex items-center justify-end gap-4">
            <button type="button" @click="closeMembershipModal" class="rounded-2xl px-6 py-3.5 text-sm font-bold text-stone-500 transition hover:bg-stone-100 hover:text-stone-900">Batal</button>
            <button type="submit" :disabled="membershipForm.processing" class="rounded-2xl bg-stone-950 px-8 py-3.5 text-sm font-bold text-white shadow-lg shadow-stone-950/10 transition-all hover:-translate-y-0.5 hover:bg-stone-800 disabled:opacity-50">
              {{ editingMembershipId ? 'Simpan Perubahan' : 'Simpan & Aktifkan' }}
            </button>
          </div>
        </form>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showAuditModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-stone-950/40 p-4 backdrop-blur-sm">
        <form class="w-full max-w-4xl rounded-[2rem] bg-white p-6 shadow-2xl" @submit.prevent="submitAuditLog">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Log Audit</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ editingAuditId ? 'Ubah Log' : 'Log Baru' }}</h3>
            </div>
            <button type="button" @click="closeAuditModal" class="rounded-full p-2 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700">x</button>
          </div>

          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Pengguna</span>
              <select v-model="auditForm.user_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white">
                <option value="">Sistem</option>
                <option v-for="user in options.users" :key="user.id" :value="user.id">{{ user.name }}</option>
              </select>
            </label>
            <FormInput v-model="auditForm.module" label="Modul" />
            <FormInput v-model="auditForm.action" label="Aksi" />
            <FormInput v-model="auditForm.model_type" label="Tipe Model" />
            <FormInput v-model="auditForm.model_id" label="ID Model" />
            <FormInput v-model="auditForm.ip_address" label="Alamat IP" />
            <FormInput v-model="auditForm.user_agent" label="User Agent" class-name="md:col-span-2" />
            <FormTextarea v-model="auditForm.old_values_text" label="JSON Nilai Lama" rows="6" />
            <FormTextarea v-model="auditForm.new_values_text" label="JSON Nilai Baru" rows="6" />
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="closeAuditModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button>
            <button type="submit" :disabled="auditForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">{{ editingAuditId ? 'Simpan Perubahan' : 'Buat Log' }}</button>
          </div>
        </form>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showApiKeyModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-stone-950/40 p-4 backdrop-blur-sm">
        <form class="w-full max-w-4xl rounded-[2rem] bg-white p-6 shadow-2xl" @submit.prevent="submitApiKey">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Kunci API</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ editingApiKeyId ? 'Ubah Kunci' : 'Kunci API Baru' }}</h3>
            </div>
            <button type="button" @click="closeApiKeyModal" class="rounded-full p-2 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700">x</button>
          </div>

          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <FormInput v-model="apiKeyForm.name" label="Nama Kunci" />
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Pengguna</span>
              <select v-model="apiKeyForm.user_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white">
                <option value="">Belum ditugaskan</option>
                <option v-for="user in options.users" :key="user.id" :value="user.id">{{ user.name }}</option>
              </select>
            </label>
            <FormInput v-model="apiKeyForm.key_value" label="Nilai Kunci Asli" type="password" class-name="md:col-span-2" />
            <FormInput v-model="apiKeyForm.rate_limit_per_hour" label="Batas / Jam" type="number" />
            <FormInput v-model="apiKeyForm.expires_at" label="Berakhir Pada" type="datetime-local" />
            <FormTextarea v-model="apiKeyForm.scopes_text" label="Cakupan" rows="5" />
            <FormTextarea v-model="apiKeyForm.ip_whitelist_text" label="IP Whitelist" rows="5" />
            <label class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 md:col-span-2">
              <input v-model="apiKeyForm.is_active" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-950" />
              <span>Kunci Aktif</span>
            </label>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="closeApiKeyModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button>
            <button type="submit" :disabled="apiKeyForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">{{ editingApiKeyId ? 'Simpan Perubahan' : 'Buat Kunci API' }}</button>
          </div>
        </form>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showHelpModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-stone-950/40 p-4 backdrop-blur-sm">
        <form class="w-full max-w-4xl rounded-[2rem] bg-white p-6 shadow-2xl" @submit.prevent="submitHelpArticle">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Pusat Bantuan</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ editingHelpId ? 'Ubah Artikel' : 'Artikel Baru' }}</h3>
            </div>
            <button type="button" @click="closeHelpModal" class="rounded-full p-2 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700">x</button>
          </div>

          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <FormInput v-model="helpForm.title" label="Judul" class-name="md:col-span-2" />
            <FormInput v-model="helpForm.slug" label="Slug" />
            <FormInput v-model="helpForm.category" label="Kategori" />
            <FormTextarea v-model="helpForm.content" label="Konten" rows="10" class-name="md:col-span-2" />
            <FormInput v-model="helpForm.view_count" label="Jumlah Lihat" type="number" />
            <label class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700">
              <input v-model="helpForm.is_published" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-950" />
              <span>Terbit</span>
            </label>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="closeHelpModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button>
            <button type="submit" :disabled="helpForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">{{ editingHelpId ? 'Simpan Perubahan' : 'Buat Artikel' }}</button>
          </div>
        </form>
      </div>
    </Transition>
  </WorkspaceLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import { Pencil, Plus, Trash2, Shield, ShieldCheck, Users, UserPlus, Settings2 } from 'lucide-vue-next'
import WorkspaceLayout from '../../Layouts/WorkspaceLayout.vue'
import SystemLayout from '../../Layouts/SystemLayout.vue'

const props = defineProps({
  workspace: Object,
  workspaceSettings: Object,
  activeTab: String,
  roles: Array,
  permissions: Array,
  memberships: Array,
  auditLogs: Array,
  apiKeys: Array,
  helpArticles: Array,
  sessions: Array,
  options: Object,
  summary: Object,
})

const page = usePage()
const currentMembership = computed(() => page.props.auth.current_membership)
const canManageTeam = computed(() => currentMembership.value?.is_owner || currentMembership.value?.role?.slug === 'admin')

const systemBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}/system`
const activeTab = ref(props.activeTab === 'roles' ? 'team' : (props.activeTab || 'team'))

watch(() => props.activeTab, (newTab) => {
  if (newTab) {
    activeTab.value = newTab === 'roles' ? 'team' : newTab
  }
})

const tabMeta = {
  team: {
    menu: 39,
    label: 'Tim & Akses',
    actionLabel: 'Tambah Anggota',
    headline: 'Kelola kolaborasi tim, penugasan peran, dan kontrol akses dalam satu panel terpadu.',
    copy: 'Undang anggota tim baru, atur matriks peran, dan pantau aktivitas akses untuk memastikan keamanan operasional workspace.',
  },
  settings: {
    menu: 40,
    label: 'Pengaturan Workspace',
    actionLabel: '',
    headline: 'Konfigurasi brand, domain, SMTP, WA, n8n, dan jam kerja dalam satu panel operasional.',
    copy: 'Pengaturan inti disimpan dan dibaca dari workspace yang aktif untuk memastikan konsistensi konfigurasi sistem.',
  },
  audit: {
    menu: 41,
    label: 'Log Audit',
    actionLabel: 'Log Audit Baru',
    headline: 'Pantau jejak aktivitas siapa mengubah apa, kapan, dan dari mana perubahan berasal.',
    copy: 'Log otomatis dan catatan manual digunakan untuk mengaudit aktivitas sensitif serta melakukan investigasi internal.',
  },
  security: {
    menu: 42,
    label: 'Keamanan',
    actionLabel: 'Kunci API Baru',
    headline: 'Kelola postur keamanan melalui kebijakan akses, kunci API, dan pemantauan sesi aktif.',
    copy: 'Kontrol terpusat untuk 2FA, SSO, idle timeout, dan whitelist IP guna mengamankan akses ke workspace.',
  },
  help: {
    menu: 43,
    label: 'Pusat Bantuan',
    actionLabel: 'Artikel Baru',
    headline: 'Basis pengetahuan internal untuk dokumentasi, FAQ, tutorial, dan log perubahan.',
    copy: 'Dokumentasi yang dapat diterbitkan dan dilacak secara internal untuk mempermudah orientasi tim.',
  },
}

const activeMeta = computed(() => tabMeta[activeTab.value] ?? tabMeta.team)

const activeStatCards = computed(() => {
  const cards = {
    team: [
      { label: 'Anggota', value: props.memberships.length },
      { label: 'Peran', value: props.summary.roles.total },
      { label: 'Pemilik', value: props.memberships.filter(m => m.is_owner).length },
      { label: 'Kedaluwarsa', value: props.summary.roles.temporary_members },
    ],
    settings: [
      { label: 'Integrasi', value: props.summary.settings.configured_integrations },
      { label: 'Libur', value: props.summary.settings.holiday_count },
      { label: 'Template', value: props.summary.settings.template_count },
      { label: 'Pencadangan', value: props.summary.settings.backup_count },
    ],
    audit: [
      { label: 'Log', value: props.summary.audit.total },
      { label: 'Hari Ini', value: props.summary.audit.today },
      { label: 'Hapus', value: props.summary.audit.delete_actions },
      { label: 'Aktor', value: props.summary.audit.users },
    ],
    security: [
      { label: 'Kunci API', value: props.summary.security.api_keys },
      { label: 'Kunci Aktif', value: props.summary.security.active_keys },
      { label: 'Sesi', value: props.summary.security.active_sessions },
      { label: 'User 2FA', value: props.summary.security.two_factor_users },
    ],
    help: [
      { label: 'Artikel', value: props.summary.help.articles },
      { label: 'Terbit', value: props.summary.help.published },
      { label: 'Draft', value: props.summary.help.drafts },
      { label: 'Kategori', value: props.summary.help.categories },
    ],
  }

  return cards[activeTab.value] ?? cards.team
})

const activeSignals = computed(() => {
  const signals = {
    team: [
      { label: 'Total Anggota', copy: `${props.memberships.length} anggota tim aktif di workspace ini.` },
      { label: 'Matriks Peran', copy: `${props.summary.roles.total} peran tersedia untuk penugasan akses.` },
      { label: 'Akses Terbatas', copy: `${props.summary.roles.temporary_members} anggota memiliki akses dengan batas waktu.` },
    ],
    settings: [
      { label: 'Layanan terhubung', copy: `${props.summary.settings.configured_integrations} konektor inti sudah dikonfigurasi.` },
      { label: 'Posisi cadangan', copy: `${props.summary.settings.backup_count} snapshot cadangan tercatat di pengaturan.` },
      { label: 'Kebijakan kalender', copy: `${props.summary.settings.holiday_count} hari libur tersimpan untuk workspace.` },
    ],
    audit: [
      { label: 'Volume log', copy: `${props.summary.audit.total} log audit terbaru sedang terbaca di panel ini.` },
      { label: 'Aksi sensitif', copy: `${props.summary.audit.delete_actions} aksi hapus muncul di aliran audit.` },
      { label: 'Aktor aktif', copy: `${props.summary.audit.users} pengguna berbeda muncul di jejak audit terbaru.` },
    ],
    security: [
      { label: 'Postur kunci', copy: `${props.summary.security.active_keys} kunci API aktif masih bisa dipakai.` },
      { label: 'Beban sesi', copy: `${props.summary.security.active_sessions} sesi pengguna masih aktif.` },
      { label: 'Postur 2FA', copy: `${props.summary.security.two_factor_users} keanggotaan memakai akun dengan 2FA aktif.` },
    ],
    help: [
      { label: 'Cakupan materi', copy: `${props.summary.help.articles} artikel sudah tersimpan di pusat bantuan.` },
      { label: 'Status terbit', copy: `${props.summary.help.published} artikel sudah terbit dan ${props.summary.help.drafts} masih draft.` },
      { label: 'Taksonomi', copy: `${props.summary.help.categories} kategori aktif dipakai untuk dokumentasi.` },
    ],
  }

  return signals[activeTab.value] ?? signals.team
})

const activePanels = computed(() => {
  const panels = {
    settings: [
      { title: 'Identitas workspace', copy: 'Nama, warna brand, logo, dan domain kustom dipakai sebagai identitas utama tenant ini.' },
      { title: 'Konektor operasional', copy: 'SMTP, WA, dan n8n webhook dirapikan dalam satu panel supaya tidak tercecer di file konfigurasi terpisah.' },
      { title: 'Penyimpanan dan cadangan', copy: 'Kuota penyimpanan, kalender libur, template notifikasi, dan snapshot cadangan disimpan di pengaturan workspace.' },
    ],
    audit: [
      { title: 'Entri manual', copy: 'Selain log otomatis dari model yang dapat diaudit, admin juga bisa menambah log manual untuk catatan insiden.' },
      { title: 'Visibilitas perubahan', copy: 'Nilai lama dan nilai baru diringkas untuk mempermudah investigasi perubahan data.' },
      { title: 'Jejak aktor', copy: 'Alamat IP, user agent, dan stempel waktu membantu memastikan jejak aksi tetap bisa ditelusuri.' },
    ],
    help: [
      { title: 'Kontrol terbit', copy: 'Setiap artikel bisa disimpan sebagai draft dulu atau langsung diterbitkan ke pusat bantuan internal.' },
      { title: 'Kebutuhan orientasi', copy: 'Kategori dan slug membuat artikel lebih mudah dipakai untuk orientasi maupun log perubahan.' },
      { title: 'Sinyal penggunaan', copy: 'Jumlah lihat memberi indikasi artikel mana yang paling sering dipakai tim.' },
    ],
  }

  return panels[activeTab.value] ?? []
})

const settingsCards = computed(() => [
  { label: 'Penyimpanan', value: `${props.workspaceSettings.storage_quota_gb} GB` },
  { label: 'Zona Waktu', value: props.workspaceSettings.timezone },
  { label: 'Mata Uang', value: props.workspaceSettings.currency },
  { label: 'Jam Kerja', value: `${props.workspaceSettings.working_hours_start} - ${props.workspaceSettings.working_hours_end}` },
])

const permissionModules = computed(() =>
  Object.entries(props.options.permissionModules || {}).map(([name, items]) => ({ name, items })),
)

function formatOption(value) {
  return String(value || '').replaceAll('-', ' ').replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase())
}

function submitDelete(url, message) {
  if (!confirm(message)) return
  router.delete(url, { preserveScroll: true })
}

const settingsForm = useForm({
  name: props.workspaceSettings.name || '',
  logo: props.workspaceSettings.logo || '',
  primary_color: props.workspaceSettings.primary_color || '',
  timezone: props.workspaceSettings.timezone || 'Asia/Jakarta',
  currency: props.workspaceSettings.currency || 'IDR',
  language: props.workspaceSettings.language || 'id',
  custom_domain: props.workspaceSettings.custom_domain || '',
  smtp_host: props.workspaceSettings.smtp_host || '',
  smtp_port: props.workspaceSettings.smtp_port || '',
  smtp_username: props.workspaceSettings.smtp_username || '',
  smtp_password: '',
  wa_api_key: props.workspaceSettings.wa_api_key || '',
  wa_phone_number: props.workspaceSettings.wa_phone_number || '',
  n8n_webhook_url: props.workspaceSettings.n8n_webhook_url || '',
  working_hours_start: props.workspaceSettings.working_hours_start || '08:00',
  working_hours_end: props.workspaceSettings.working_hours_end || '17:00',
  storage_quota_gb: props.workspaceSettings.storage_quota_gb || 50,
  holiday_dates_text: props.workspaceSettings.holiday_dates_text || '',
  notification_templates_text: props.workspaceSettings.notification_templates_text || '',
  backup_snapshots_text: props.workspaceSettings.backup_snapshots_text || '',
})

function submitSettings() {
  settingsForm.patch(`${systemBaseUrl}/settings`, { preserveScroll: true })
}

const securityForm = useForm({
  require_two_factor: props.workspaceSettings.require_two_factor ?? false,
  allow_google_sso: props.workspaceSettings.allow_google_sso ?? true,
  brute_force_protection: props.workspaceSettings.brute_force_protection ?? true,
  session_idle_minutes: props.workspaceSettings.session_idle_minutes || 30,
  allowed_ips_text: props.workspaceSettings.allowed_ips_text || '',
  password_policy: props.workspaceSettings.password_policy || '',
})

function submitSecurity() {
  securityForm.patch(`${systemBaseUrl}/security`, { preserveScroll: true })
}

const showRoleModal = ref(false)
const editingRoleId = ref(null)
const roleForm = useForm({
  name: '',
  slug: '',
  description: '',
  is_default: false,
  parent_role_id: '',
  permission_ids: [],
})

function openRoleModal(item = null) {
  editingRoleId.value = item?.id || null
  roleForm.reset()
  roleForm.clearErrors()
  roleForm.name = item?.name || ''
  roleForm.slug = item?.slug || ''
  roleForm.description = item?.description || ''
  roleForm.is_default = item?.is_default ?? false
  roleForm.parent_role_id = item?.parent_role_id || ''
  roleForm.permission_ids = Array.isArray(item?.permission_ids) ? [...item.permission_ids] : []
  showRoleModal.value = true
}

function closeRoleModal() {
  showRoleModal.value = false
  editingRoleId.value = null
}

function submitRole() {
  const url = editingRoleId.value ? `${systemBaseUrl}/roles/${encodeURIComponent(editingRoleId.value)}` : `${systemBaseUrl}/roles`
  const method = editingRoleId.value ? roleForm.patch : roleForm.post
  method(url, { preserveScroll: true, onSuccess: () => closeRoleModal() })
}

function deleteRole(id) {
  submitDelete(`${systemBaseUrl}/roles/${encodeURIComponent(id)}`, 'Hapus role ini?')
}

const showMembershipModal = ref(false)
const editingMembershipId = ref(null)
const memberMode = ref('new')

const membershipForm = useForm({
  user_id: '',
  name: '',
  email: '',
  password: '',
  role_id: '',
  is_owner: false,
  joined_at: '',
  expires_at: '',
})

function openMembershipModal(item = null) {
  editingMembershipId.value = item?.id || null
  membershipForm.reset()
  membershipForm.clearErrors()
  
  if (item) {
      memberMode.value = 'existing'
      membershipForm.user_id = item.user_id || ''
      membershipForm.role_id = item.role_id || ''
      membershipForm.is_owner = item.is_owner ?? false
      membershipForm.joined_at = item.joined_at || ''
      membershipForm.expires_at = item.expires_at || ''
  } else {
      memberMode.value = 'new'
  }
  
  showMembershipModal.value = true
}

function closeMembershipModal() {
  showMembershipModal.value = false
  editingMembershipId.value = null
}

function submitMembership() {
  const url = editingMembershipId.value ? `${systemBaseUrl}/memberships/${encodeURIComponent(editingMembershipId.value)}` : `${systemBaseUrl}/memberships`
  const method = editingMembershipId.value ? membershipForm.patch : membershipForm.post
  method(url, { preserveScroll: true, onSuccess: () => closeMembershipModal() })
}

function deleteMembership(id) {
  submitDelete(`${systemBaseUrl}/memberships/${encodeURIComponent(id)}`, 'Hapus membership ini?')
}

const showAuditModal = ref(false)
const editingAuditId = ref(null)
const auditForm = useForm({
  user_id: '',
  module: '',
  action: '',
  model_type: '',
  model_id: '',
  ip_address: '',
  user_agent: '',
  old_values_text: '',
  new_values_text: '',
})

function openAuditModal(item = null) {
  editingAuditId.value = item?.id || null
  auditForm.reset()
  auditForm.clearErrors()
  auditForm.user_id = item?.user_id || ''
  auditForm.module = item?.module || ''
  auditForm.action = item?.action || ''
  auditForm.model_type = item?.model_type || ''
  auditForm.model_id = item?.model_id || ''
  auditForm.ip_address = item?.ip_address || ''
  auditForm.user_agent = item?.user_agent || ''
  auditForm.old_values_text = item?.old_values_text || ''
  auditForm.new_values_text = item?.new_values_text || ''
  showAuditModal.value = true
}

function closeAuditModal() {
  showAuditModal.value = false
  editingAuditId.value = null
}

function submitAuditLog() {
  const url = editingAuditId.value ? `${systemBaseUrl}/audit-logs/${encodeURIComponent(editingAuditId.value)}` : `${systemBaseUrl}/audit-logs`
  const method = editingAuditId.value ? auditForm.patch : auditForm.post
  method(url, { preserveScroll: true, onSuccess: () => closeAuditModal() })
}

function deleteAuditLog(id) {
  submitDelete(`${systemBaseUrl}/audit-logs/${encodeURIComponent(id)}`, 'Hapus log audit ini?')
}

const showApiKeyModal = ref(false)
const editingApiKeyId = ref(null)
const apiKeyForm = useForm({
  user_id: '',
  name: '',
  key_value: '',
  scopes_text: '',
  ip_whitelist_text: '',
  rate_limit_per_hour: 1000,
  expires_at: '',
  is_active: true,
})

function openApiKeyModal(item = null) {
  editingApiKeyId.value = item?.id || null
  apiKeyForm.reset()
  apiKeyForm.clearErrors()
  apiKeyForm.user_id = item?.user_id || ''
  apiKeyForm.name = item?.name || ''
  apiKeyForm.key_value = ''
  apiKeyForm.scopes_text = item?.scopes_text || ''
  apiKeyForm.ip_whitelist_text = item?.ip_whitelist_text || ''
  apiKeyForm.rate_limit_per_hour = item?.rate_limit_per_hour || 1000
  apiKeyForm.expires_at = item?.expires_at || ''
  apiKeyForm.is_active = item?.is_active ?? true
  showApiKeyModal.value = true
}

function closeApiKeyModal() {
  showApiKeyModal.value = false
  editingApiKeyId.value = null
}

function submitApiKey() {
  const url = editingApiKeyId.value ? `${systemBaseUrl}/api-keys/${encodeURIComponent(editingApiKeyId.value)}` : `${systemBaseUrl}/api-keys`
  const method = editingApiKeyId.value ? apiKeyForm.patch : apiKeyForm.post
  method(url, { preserveScroll: true, onSuccess: () => closeApiKeyModal() })
}

function deleteApiKey(id) {
  submitDelete(`${systemBaseUrl}/api-keys/${encodeURIComponent(id)}`, 'Hapus API key ini?')
}

const showHelpModal = ref(false)
const editingHelpId = ref(null)
const showRolesManagerModal = ref(false)

const helpForm = useForm({
  title: '',
  slug: '',
  category: '',
  content: '',
  is_published: true,
  view_count: 0,
})

function openHelpModal(item = null) {
  editingHelpId.value = item?.id || null
  helpForm.reset()
  helpForm.clearErrors()
  helpForm.title = item?.title || ''
  helpForm.slug = item?.slug || ''
  helpForm.category = item?.category || ''
  helpForm.content = item?.content || ''
  helpForm.is_published = item?.is_published ?? true
  helpForm.view_count = item?.view_count ?? 0
  showHelpModal.value = true
}

function closeHelpModal() {
  showHelpModal.value = false
  editingHelpId.value = null
}

function submitHelpArticle() {
  const url = editingHelpId.value ? `${systemBaseUrl}/help-articles/${encodeURIComponent(editingHelpId.value)}` : `${systemBaseUrl}/help-articles`
  const method = editingHelpId.value ? helpForm.patch : helpForm.post
  method(url, { preserveScroll: true, onSuccess: () => closeHelpModal() })
}

function deleteHelpArticle(id) {
  submitDelete(`${systemBaseUrl}/help-articles/${encodeURIComponent(id)}`, 'Hapus artikel ini?')
}

function openActiveModal() {
  if (activeTab.value === 'team') openMembershipModal()
  else if (activeTab.value === 'audit') openAuditModal()
  else if (activeTab.value === 'security') openApiKeyModal()
  else if (activeTab.value === 'help') openHelpModal()
}
</script>

<script>
export default {
  components: {
    FormInput: {
      props: ['modelValue', 'label', 'type', 'className'],
      emits: ['update:modelValue'],
      template: `
        <label :class="['space-y-2 text-sm', className]">
          <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">{{ label }}</span>
          <input
            :value="modelValue"
            :type="type || 'text'"
            @input="$emit('update:modelValue', $event.target.value)"
            class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
          />
        </label>`,
    },
    FormTextarea: {
      props: ['modelValue', 'label', 'rows', 'className'],
      emits: ['update:modelValue'],
      template: `
        <label :class="['space-y-2 text-sm', className]">
          <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">{{ label }}</span>
          <textarea
            :value="modelValue"
            :rows="rows || 4"
            @input="$emit('update:modelValue', $event.target.value)"
            class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
          ></textarea>
        </label>`,
    },
  },
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
